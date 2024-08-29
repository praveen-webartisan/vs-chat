<?php
	session_start();

	require 'common.php';

	$chatUrl = $baseUrl . "/user/chat.php";

	if(isset($_SESSION["user"]) || isset($_COOKIE["user"])){
		header("Location: $chatUrl");
	}

	$_SESSION["baseUrl"] = $baseUrl;
	$loginValidationMsg = [];
	$signUpValidationMsg = [];
	$message = "";
	$loginInput = [];
	$signUpInput = [];

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

	if(isset($_SESSION["oldLoginInput"])){
		$loginInput = $_SESSION["oldLoginInput"];
		unset($_SESSION["oldLoginInput"]);
	}elseif(isset($_SESSION["oldSignUpInput"])){
		$signUpInput = $_SESSION["oldSignUpInput"];
		unset($_SESSION["oldSignUpInput"]);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?=APP_TITLE;?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="text/css" href="<?=$baseUrl;?>/assets/logo.png">

	<?php
		include 'assets/styles.php';
	?>

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
			<a class="navbar-brand ml-2 mr-2" href=""><?=APP_TITLE;?></a>
		</section>
	</header>

	<!-- Content -->
	<div class="bg-secondary main-content">

		<div class="col-3 col-lg-10 center-in-parent">
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
								<input type="text" class="form-input" id="txtUsr" name="txtUsr" placeholder="Username" value="<?=(isset($loginInput['txtUsr']) ? $loginInput['txtUsr'] : '')?>">
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
							<label class="form-checkbox">
								<input type="checkbox" name="loginPermanent" value="yes">
								<i class="form-icon"></i> Remember Me
							</label>
						</div>
						<div class="form-group">
							<button type="submit" class="btn p-centered">Login</button>
						</div>
					</form>
				</div>
				<div class="tab-pane" id="sectionSignup">
					<form action="<?=$baseUrl . '/user/auth.php?action=signup';?>" method="post" class="col-12" autocomplete="off">
						<div class="form-group <?=isset($signUpValidationMsg['txtRegUsr']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="text" class="form-input" id="txtRegUsr" name="txtRegUsr" placeholder="Username" value="<?=(isset($signUpInput['txtRegUsr']) ? $signUpInput['txtRegUsr'] : '')?>">
								<i class="fas fa-user form-icon"></i>
							</div>
							<?php if(isset($signUpValidationMsg['txtRegUsr'])): ?>
								<p class="form-input-hint"><?=$signUpValidationMsg['txtRegUsr'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group <?=isset($signUpValidationMsg['txtRegEmail']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="text" class="form-input" id="txtRegEmail" name="txtRegEmail" placeholder="Email" value="<?=(isset($signUpInput['txtRegEmail']) ? $signUpInput['txtRegEmail'] : '')?>">
								<i class="fas fa-envelope form-icon"></i>
							</div>
							<?php if(isset($signUpValidationMsg['txtRegEmail'])): ?>
								<p class="form-input-hint"><?=$signUpValidationMsg['txtRegEmail'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group <?=isset($signUpValidationMsg['txtRegPwd']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="password" class="form-input password-field" id="txtRegPwd" name="txtRegPwd" placeholder="Password" autocomplete="new-password" value="<?=(isset($signUpInput['txtRegPwd']) ? $signUpInput['txtRegPwd'] : '')?>">
								<i class="fas fa-key form-icon"></i>
							</div>
							<?php if(isset($signUpValidationMsg['txtRegPwd'])): ?>
								<p class="form-input-hint"><?=$signUpValidationMsg['txtRegPwd'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group <?=isset($signUpValidationMsg['txtRegCnfPwd']) ? 'has-error' : '';?>">
							<div class="has-icon-left">
								<input type="password" class="form-input password-field" id="txtRegCnfPwd" name="txtRegCnfPwd" placeholder="Confirm Password" autocomplete="new-password" value="<?=(isset($signUpInput['txtRegCnfPwd']) ? $signUpInput['txtRegCnfPwd'] : '')?>">
								<i class="fas fa-key form-icon"></i>
							</div>
							<?php if(isset($signUpValidationMsg['txtRegCnfPwd'])): ?>
								<p class="form-input-hint"><?=$signUpValidationMsg['txtRegCnfPwd'];?></p>
							<?php endif; ?>
						</div>
						<div class="form-group">
							<label class="form-checkbox">
								<input type="checkbox" class="toggle-pwd-fields">
								<i class="form-icon"></i> Show Password
							</label>
						</div>
						<div class="form-group">
							<button type="submit" class="btn p-centered">Signup</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div>

	<?php
		include 'assets/scripts.php';
	?>
	<script type="text/javascript">
		<?php if(!empty($message)): ?>
			const MSG_SHO_ON_LOAD = '<?=$message; ?>';
		<?php endif; ?>
	</script>
</body>
</html>