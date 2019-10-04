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
		$user = $db->fetchUser($txtUsr, $txtPwd);

		if($user && !empty($user)){
			$_SESSION["user"] = $user->username;
			header("Location: chat.php");
		}else{
			$_SESSION["message"] = "User/password does match with the system";
		}
	}else{
		$_SESSION["validationMsg"] = $message;
		$_SESSION["prevAction"] = "login";
	}

	$_SESSION["oldInput"] = $req;

	header("Location: ../");
}

function registerUser()
{
	$req = $GLOBALS["req"];
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