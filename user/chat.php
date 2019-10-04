<?php
	session_start();

	$baseUrl = "http://" . $_SERVER["SERVER_NAME"] . strrev(substr(strrev(dirname($_SERVER['PHP_SELF'])), strpos(strrev(dirname($_SERVER['PHP_SELF'])), "/")));

	if(!isset($_SESSION["user"])){
		header("Location: $baseUrl");
	}

	$currUser = $_SESSION["user"];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Chat App using Vue.js</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Spectre CSS -->
	<link rel="stylesheet" type="text/css" href="assets/css/fa/css/all.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/spectre/spectre.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/spectre/spectre-exp.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/spectre/spectre-icons.min.css" />

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
						<button class="btn p-absolute r-0" id="btnSendMessage">
							<i class="fas fa-paper-plane"></i>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Vue.js -->
	<script type="text/javascript" src="assets/js/vue.min.js"></script>
	<!-- Axios js -->
	<script type="text/javascript" src="assets/js/axios.min.js"></script>
	<!-- Custom JS -->
	<script type="text/javascript" src="assets/js/script.js"></script>
	<script type="text/javascript">
		const CURR_USER = '<?=$currUser; ?>';
		Vue.component('chat-message', {
			props: ['chat'],
			template: 	'<div class="container">' + 
							'<div class="columns chat-message by-others" v-if="chat.from != \'me\'">' + 
								'<div class="column col-6"></div>' + 
								'<div class="column col-6">' + 
									'<div class="column col-11 has-message">' + 
										'{{ chat.message }}' + 
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
										'{{ chat.message }}' + 
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
							url: '<?=$baseUrl . "/processChat.php";?>',
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

		window.collectChatMessages = (() => {
			axios
				.get('<?=$baseUrl . "/processChat.php?action=collect-message&for=" . $currUser;?>')
				.then(response => {
					var data = response.data;

					if(typeof(data.data) != 'undefined'){
						chatBox.chatMessages = data.data;
					}
				})
				.catch(error => {
					chatBox.errorOccured = true
					console.log(error);
				})
				.finally(() => {
					chatBox.loading = false;
				})
		});

		window.scrollDownChatBox = (() => {
			var chatBoxElement = document.getElementById('chatBox');
				chatBoxElement.scrollTo({left: 0, top: chatBoxElement.scrollHeight, behavior: 'smooth'});
		});
	</script>
</body>
</html>