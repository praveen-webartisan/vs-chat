<?php

session_start();

require 'db.php';

$req = $_REQUEST;
$action = isset($req["action"]) ? $req["action"] : null;

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
	}elseif(!preg_match("/^[a-z0-9\.]+@[a-z]+\.[a-z]+$/", $txtRegEmail)){
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
		$db = new DB();
		// Store
		$insertId = $db->storeUser($txtRegUsr, $txtRegEmail, $txtRegPwd);

		if($insertId){
			$user = $db->fetchUser(["username" => $txtRegUsr, "pwd" => $txtRegPwd]);

			if($user && !empty($user)){
				$_SESSION["user"] = $user[0]->username;
				header("Location: chat.php");
			}else{
				$_SESSION["message"] = "Unable to login user. Please contact support";
			}
		}else{
			$_SESSION["message"] = "Unable to store user information. Please contact support";
		}
	}else{
		$_SESSION["validationMsg"] = $message;
		$_SESSION["prevAction"] = "signup";
	}

	$_SESSION["oldSignUpInput"] = $req;

	header("Location: ../#sectionSignup");
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

		default:
			abort(400);
			break;
	}
}else{
	abort(400);
}