<?php

session_start();

$reqUri = $_SERVER["REQUEST_URI"];

require 'db.php';
require '../constants.php';

$req = $_REQUEST;
$action = isset($req["action"]) ? $req["action"] : null;

function generateVerificationToken($email)
{
	$token = "A user with the email address: " . $email . " is being signed up in our application";
	$token = sha1($token);
	return $token;
}

function verifyUserEmail()
{
	$req = $GLOBALS["req"];
	$token = isset($req["_token"]) ? $req["_token"] : null;
	$message = "";

	if(!empty($token)){
		$db = new DB();
		$user = $db->fetchUser(["verificationToken" => $token, "inclInactive" => true]);
		$user = isset($user[0]) ? $user[0] : null;

		if(!empty($user) && isset($user->verificationToken)){
			if(empty($user->tokenVerifiedOn)){
				if($db->updateUserVerification($user->id)){
					$message = "Your email address has been verified successfully. Please login to continue";
				}else{
					$message = "Unable to verify your email address. Please contact support!";
				}
			}else{
				$message = "You have already verified your email address.";
			}
		}else{
			$message = "Your verification link is invalid or it has been expired";
		}
	}else{
		$message = "Your verification link is invalid or it has been expired";
	}

	$_SESSION["message"] = $message;
	header("Location: ../");
}

function authUser()
{
	$req = $GLOBALS["req"];
	$txtUsr = isset($req["txtUsr"]) ? $req["txtUsr"] : null;
	$txtPwd = isset($req["txtPwd"]) ? $req["txtPwd"] : null;
	$isUsrValid = false;
	$isPwdValid = false;
	$message = [];

	if(empty($txtUsr)){
		$message["txtUsr"] = "Required";
	}else{
		$isUsrValid = true;
	}

	if(empty($txtPwd)){
		$message["txtPwd"] = "Required";
	}else{
		$isPwdValid = true;
	}

	if($isUsrValid && $isPwdValid){
		$db = new DB();
		$user = $db->fetchUser(["userOrEmail" => $txtUsr, "pwd" => $txtPwd]);

		if($user && !empty($user)){
			$username = $user[0]->username;

			if(isset($req["loginPermanent"])){
				setcookie("user", $username, time() + (86400 * 365), "/");
			}else{
				$_SESSION["user"] = $username;
			}

			header("Location: chat.php");
		}else{
			$_SESSION["message"] = "Username and password are not match with the system";
		}
	}else{
		$_SESSION["validationMsg"] = $message;
		$_SESSION["prevAction"] = "login";
	}

	$_SESSION["oldLoginInput"] = $req;

	header("Location: ../");
}

function logoutUser()
{
	if(isset($_SESSION["user"])){
		unset($_SESSION["user"]);
	}elseif(isset($_COOKIE["user"])){
		unset($_COOKIE["user"]);
		setcookie("user", null, time() - 3600, "/");
	}

	header("Location: ../");
}

function registerUser()
{
	$req = $GLOBALS["req"];

	$txtRegUsr = isset($req["txtRegUsr"]) ? $req["txtRegUsr"] : null;
	$txtRegEmail = isset($req["txtRegEmail"]) ? $req["txtRegEmail"] : null;
	$txtRegPwd = isset($req["txtRegPwd"]) ? $req["txtRegPwd"] : null;
	$txtRegCnfPwd = isset($req["txtRegCnfPwd"]) ? $req["txtRegCnfPwd"] : null;
	$isUsrValid = false;
	$isEmailValid = false;
	$isPwdValid = false;
	$isConfPwdValid = false;
	$message = [];
	$showTab = "#sectionSignup";

	if(empty($txtRegUsr)){
		$message["txtRegUsr"] = "Required";
	}else{
		$db = new DB();
		$user = $db->fetchUser(["username" => $txtRegUsr, "inclInactive" => true]);
		$isUsrValid = (count($user) == 0);

		if(!$isUsrValid){
			$message["txtRegUsr"] = "Username not available";
		}
	}

	if(empty($txtRegEmail)){
		$message["txtRegEmail"] = "Required";
	}elseif(!preg_match("/^[a-z0-9\-\.]+@[a-z0-9\-]+\.[a-z]+$/", $txtRegEmail)){
		$message["txtRegEmail"] = "Invalid email";
	}else{
		$db = new DB();
		$user = $db->fetchUser(["email" => $txtRegEmail, "inclInactive" => true]);
		$isEmailValid = (count($user) == 0);

		if(!$isEmailValid){
			$message["txtRegEmail"] = "Email address already used";
		}
	}

	if(empty($txtRegPwd)){
		$message["txtRegPwd"] = "Required";
	}else{
		$isPwdValid = true;
	}

	if(empty($txtRegCnfPwd)){
		$message["txtRegCnfPwd"] = "Required";
	}else{
		$isConfPwdValid = true;
	}

	if($isPwdValid && $isConfPwdValid){
		if($txtRegPwd !== $txtRegCnfPwd){
			$message["txtRegPwd"] = "Password and Confirmation password should be same";
			$isPwdValid = false;
			$isConfPwdValid = false;
		}
	}

	if($isUsrValid && $isEmailValid && $isPwdValid && $isConfPwdValid){
		$token = generateVerificationToken($txtRegEmail);
		$verifyUrl = $GLOBALS["baseUrl"] . "/user/auth.php?action=verify-email&_token=" . $token;
		$db = new DB();
		// Store
		$insertId = $db->storeUser($txtRegUsr, $txtRegEmail, $txtRegPwd, $token);

		// Verification email

		require 'mail.php';
		$mailContent = file_get_contents(SIGNUP_TEMPLATE_FILE);
		$mailContent = str_replace("USER", $txtRegUsr, $mailContent);
		$mailContent = str_replace("APP_TITLE", APP_TITLE, $mailContent);
		$mailContent = str_replace("DATE_TIME", date("d-m-Y H:i"), $mailContent);
		$mailContent = str_replace("VERIFICATION_LINK", $verifyUrl, $mailContent);

		$mail = sendEmail($txtRegEmail, "Please verify your e-mail address", $mailContent);

		if($insertId){
			$_SESSION["message"] = "Signup success. Please check your email inbox to verify your email address. You can able to login only after your email address is being verified";
			$req = [];
			$showTab = "";
		}else{
			$_SESSION["message"] = "Unable to signup. Please contact support";
		}
	}else{
		$_SESSION["validationMsg"] = $message;
		$_SESSION["prevAction"] = "signup";
	}

	$_SESSION["oldSignUpInput"] = $req;

	header("Location: ../" . $showTab);
}

function abort($errCode)
{
	header($_SERVER["SERVER_PROTOCOL"] . " " . $errCode);
}

if(!empty($action)){
	switch ($action) {
		case 'login':
			authUser();
			break;

		case 'logout':
			logoutUser();
			break;

		case 'signup':
			registerUser();
			break;

		case 'verify-email':
			verifyUserEmail();
			break;

		default:
			abort(400);
			break;
	}
}else{
	abort(400);
}