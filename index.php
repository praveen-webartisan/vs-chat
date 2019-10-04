<?php
	session_start();

	$baseUrl = "http://" . $_SERVER["SERVER_NAME"] . "/" . basename(__DIR__);

	$_SESSION["baseUrl"] = $baseUrl;
	$loginValidationMsg = [];
	$signUpValidationMsg = [];
	$message = "";
	$input = [];

	if(isset($_SESSION["validationMsg"])){
		$validationMsg = $_SESSION["validationMsg"];

		if($_SESSION["prevAction"] == "login"){
			$loginValidationMsg = $validationMsg;
		}else{
			$signUpValidationMsg = $validationMsg;
		}

		unset($_SESSION["validationMsg"]);
		unset($_SESSION["prevAction"]);
		unset($validationMsg);
	}

	if(isset($_SESSION["message"])){
		$message = $_SESSION["message"];
		unset($_SESSION["message"]);
	}

	// Get INPUT Array and USE in fields to show OLD data
	if(isset($_SESSION["oldInput"])){
		$input = $_SESSION["oldInput"];
		unset($_SESSION["oldInput"]);
	}
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
	<link rel="stylesheet" type="text/css" href="assets/css/style.css" />

	<!-- Custom CSS -->
	<style type="text/css">
		/**/
	</style>
</head>
<body>

	<div class="toast" id="toast" style="display: none;">
		<button class="btn btn-clear float-right" v-on:click.prevent="onToastClose(this)"></button>
		<span></span>
	</div>

	<!-- Nav -->
	<header class="navbar">
		<section class="navbar-section">
			<a class="navbar-brand ml-2 mr-2" href="">VS Chat</a>
		</section>
	</header>

	<!-- Content -->
	<div class="bg-secondary main-content">

		<div class="col-3 center-in-parent">
			<ul class="tab tab-block">
				<li class="tab-item active">
					<a href="#sectionLogin">Login</a>
				</li>
				<li class="tab-item">
					<a href="#sectionSignup">Signup</a>
				</li>
			</ul>

			<div class="tab-content p-2">
				<div class="tab-pane active" id="sectionLogin">
					<form action="<?=$baseUrl . '/user/auth.php?action=login';?>" method="post" class="col-12" autocomplete="off">
						<div class="form-group <?=isset($loginValidationMsg['txtUsr']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="text" class="form-input" id="txtUsr" name="txtUsr" placeholder="Username">
								<i class="fas fa-user form-icon"></i>
							</div>
							<?php if(isset($loginValidationMsg['txtUsr'])): ?>
								<p class="form-input-hint"><?=$loginValidationMsg['txtUsr'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group <?=isset($loginValidationMsg['txtPwd']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="password" class="form-input" id="txtPwd" name="txtPwd" placeholder="Password" autocomplete="new-password">
								<i class="fas fa-key form-icon"></i>
							</div>
							<?php if(isset($loginValidationMsg['txtPwd'])): ?>
								<p class="form-input-hint"><?=$loginValidationMsg['txtPwd'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group">
							<button type="submit" class="btn p-centered">Login</button>
						</div>
					</form>
				</div>
				<div class="tab-pane" id="sectionSignup"></div>
			</div>
		</div>

	</div>

	<script type="text/javascript" src="assets/js/script.js"></script>
	<script type="text/javascript">
		<?php if(!empty($message)): ?>
			const MSG_SHO_ON_LOAD = '<?=$message; ?>';
		<?php endif; ?>
	</script>
</body>
</html>