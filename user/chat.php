<?php

	session_start();

	$baseUrl = "http://" . $_SERVER["SERVER_NAME"] . strrev(substr(strrev(dirname($_SERVER['PHP_SELF'])), strpos(strrev(dirname($_SERVER['PHP_SELF'])), "/")));

	$currUser = null;

	if(isset($_SESSION["user"])){
		$currUser = $_SESSION["user"];
	}elseif(isset($_COOKIE["user"])){
		$currUser = $_COOKIE["user"];
	}

	if(empty($currUser)){
		header("Location: $baseUrl");
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Chat App using Vue.js</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="text/css" href="<?=$baseUrl;?>/assets/logo.png">

	<?php
		include '../assets/styles.php';
	?>

	<!-- Custom CSS -->
	<style type="text/css">
		/**/
	</style>
</head>
<body>

	<div class="toast" id="toast" style="display: none;">
		<button class="btn btn-clear float-right"></button>
		<span></span>
	</div>

	<!-- Nav -->
	<header class="navbar">
		<section class="navbar-section">
			<a class="navbar-brand ml-2 mr-2" href="">VS Chat</a>
		</section>
		<section class="navbar-section">
			<div class="dropdown dropdown-right mr-2">
				<a class="btn btn-link dropdown-toggle" tabindex="0">
					<?=$currUser;?>
				</a>
				<ul class="menu">
					<li class="menu-item">
						<a href="<?=$baseUrl;?>user/auth.php?action=logout">Logout</a>
					</li>
				</ul>
			</div>
		</section>
	</header>

	<div class="bg-secondary" id="chatBox">
		<div class="center-in-parent" v-if="loading">
			Loading...
		</div>
		<div class="center-in-parent" v-else-if="errorOccured">
			Some error has occured. Please contact support!
		</div>
		<chat-message
			v-else
			v-for="chat in chatMessages"
			v-bind:chat="chat"/>
	</div>

	<div class="container mt-2">
		<div class="columns">
			<form class="col-12" id="frmChat" v-on:submit.prevent="onChatSent">
				<div class="columns col-gapless ">
					<div class="column col-12 col-lg-12 cont-message-box-btn">
						<textarea class="form-input" id="txtMessage" placeholder="Type your message here" rows="3" v-on:keydown="onPress"></textarea>
						<div class="btns-container p-absolute">
							<button class="btn" id="btnSendMessage">
								<i class="fas fa-paper-plane"></i>
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<?php
		include '../assets/scripts.php';
	?>
	<script type="text/javascript">
		const CURR_USER = '<?=$currUser; ?>';
		Vue.component('chat-message', {
			props: ['chat'],
			template: 	'<div class="container">' + 
							'<div class="columns chat-message by-others" v-if="chat.from != \'me\'">' + 
								'<div class="column col-6"></div>' + 
								'<div class="column col-6">' + 
									'<div class="column col-11 has-message">' + 
										'<span v-html="formatDispMsg(chat.message)"></span>' + 
										'<span class="sent-date-time">{{ chat.at }}</span>' + 
									'</div>' + 
									'<div class="column col-1">' + 
										'<figure class="avatar avatar-sm ml-1 tooltip" v-bind:data-tooltip="chat.from">' + 
											'{{ chat.senderIcon }}' + 
											'<i class="avatar-presence online"></i>' + 
										'</figure>' + 
									'</div>' + 
								'</div>' + 
							'</div>' + 
							'<div class="columns chat-message" v-if="chat.from == \'me\'">' + 
								'<div class="column col-6">' + 
									'<div class="column col-1">' + 
									'</div>' + 
									'<div class="column col-11 has-message">' + 
										'<span v-html="formatDispMsg(chat.message)"></span>' + 
										'<span class="sent-date-time">{{ chat.at }}</span>' + 
									'</div>' + 
								'</div>' + 
								'<div class="column col-6"></div>' + 
							'</div>' + 
						'</div>'
		});

		var chatBox = new Vue({
			el: '#chatBox',
			data: {
				chatMessages: null,
				loading: true,
				errorOccured: false
			},
			beforeUpdate: function(){
				var chatBoxElement = document.getElementById('chatBox');	
				var scrolledDis = chatBoxElement.offsetHeight + chatBoxElement.scrollTop;

				if(chatBoxElement.scrollHeight == scrolledDis){
					window.dataFirstTimeCollection = true;
				}
			},
			updated: function(){
				if(typeof(window.dataFirstTimeCollection) == 'undefined' || window.dataFirstTimeCollection === true){
					scrollDownChatBox();
					window.dataFirstTimeCollection = false;
				}
			},
			mounted() {
				this.intChatReload = setInterval(() => {
					collectChatMessages();
				}, 2000);
			}
		});

		var chatForm = new Vue({
			el: '#frmChat',
			methods: {
				onPress: function(e){
					if(e.ctrlKey && e.keyCode == 13){
						document.getElementById('btnSendMessage').click();
					}
				},
				onChatSent: function(){
					toggleToast(false);
					var txtMessage = document.getElementById('txtMessage');
					var message = document.getElementById('txtMessage').value;

					if(message && message.length > 0 && message.replace(/\s/g, '').length > 0){
						message = message.trim();

						axios({
							method: 'post',
							url: '<?=$baseUrl . "user/processChat.php";?>',
							data: {
								action: 'send-message',
								from: CURR_USER,
								message: message
							}
						})
						.then(response => {
							var data = response.data;

							if(typeof(data.response) != 'undefined'){
								if(data.response.code == 200){
									collectChatMessages();
								}else{
									console.log(data.response.message);
									toggleToast(true, data.response.message);
								}
							}
						})
						.catch(error => {
							console.log(error);
						})
						.finally(() => {
							txtMessage.value = '';
						})
					}
				}
			}
		});

		function notifyWhenNewMsg(data)
		{
			var currMsgCount = data.length;
			var lastMsgCount = window.lastMsgCount === undefined ? currMsgCount : window.lastMsgCount;

			if((currMsgCount > lastMsgCount) && document[documentHidden]){
				if(window.canNotify){
					var lastMsg = data[data.length - 1];

					if(lastMsg.from != 'me'){
						var message = lastMsg.message.replace(/\n/g, ' ').substr(0, 20);
						showNotification(lastMsg.from + ' says ' + message + '...');
					}
				}else{
					console.error("User has not given the permission to show notification!");
				}
			}

			window.lastMsgCount = currMsgCount;
		}

		window.collectChatMessages = (() => {
			axios
				.get('<?=$baseUrl . "user/processChat.php?action=collect-message&for=" . $currUser;?>')
				.then(response => {
					var data = response.data;

					if(typeof(data.data) != 'undefined'){
						data = data.data;
						notifyWhenNewMsg(data);
						chatBox.chatMessages = data;
					}
				})
				.catch(error => {
					chatBox.errorOccured = true
					toggleToast(true, error);
					clearInterval(chatBox.intChatReload);
				})
				.finally(() => {
					chatBox.loading = false;
				})
		});

		window.scrollDownChatBox = (() => {
			var chatBoxElement = document.getElementById('chatBox');
				chatBoxElement.scrollTo({left: 0, top: chatBoxElement.scrollHeight, behavior: 'smooth'});
		});

		function formatDispMsg(message)
		{
			return message.replace(/\n/g, '<br>');
		}
	</script>
</body>
</html>