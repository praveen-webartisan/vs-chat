<?php

require '../common.php';

initCommonMethods();

require('db.php');

session_start();

$response = [];
$req = file_get_contents("php://input");
$req = empty($req) ? (isset($_REQUEST) ? $_REQUEST : []) : $req;
$req = is_string($req) ? (array) json_decode($req) : $req;
$currUser = null;

// Allow request without authentication
$allowWithoutAuth = [
	'collect-emojis'
];

if(isset($_SESSION["user"])){
	$currUser = $_SESSION["user"];
}elseif(isset($_COOKIE["user"])){
	$currUser = $_COOKIE["user"];
}

if(isset($req["action"]) && in_array($req["action"], $allowWithoutAuth)){
	$currUser = 'SYSTEM';
}

/**
 * Chat
*/

class Chat
{

	public function __construct()
	{
		$this->db = new DB();
	}

	public function send($from, $to, $message)
	{
		return $this->db->storeChat($from, $to, $message);
	}

	public function get($to)
	{
		return $this->db->fetchChat($to);
	}

	public function getEmojis()
	{
		$data = $this->db->fetchEmojis();
		return $data;
	}

}

function collectEmojiIcons()
{
	$chat = new Chat();
	$data = $chat->getEmojis();
	$GLOBALS["response"]["data"] = $data ? base64_encode($data) : null;
}

function collectChatMessages()
{
	$req = $GLOBALS["req"];
	$for = isset($req["for"]) ? $req["for"] : null;

	if(!empty($for)){
		$chat = new Chat();
		$data = $chat->get($for);
		$GLOBALS["response"]["data"] = $data;
	}else{
		$GLOBALS["response"]["message"] = "Collect for which user is not specified";
	}
}

function sendChatMessage()
{
	$req = $GLOBALS["req"];
	$response = [];
	$from = isset($req["from"]) ? $req["from"] : null;
	$to = isset($req["to"]) ? $req["to"] : "all";
	$message = isset($req["message"]) ? $req["message"] : null;

	if(!(empty($from) || empty($to) || empty($message))){
		$message = addslashes($message);
		$chat = new Chat();
		$isSent = $chat->send($from, $to, $message);

		if($isSent){
			$response["response"]["code"] = 200;
		}else{
			$response["response"]["code"] = 500;
			$response["response"]["message"] = "Unable to send message";
		}
	}else{
		$response["response"]["code"] = 400;
		$response["response"]["message"] = "Invalid input";
	}

	$GLOBALS["response"] = $response;
}

function abort($errCode)
{
	header($_SERVER["SERVER_PROTOCOL"] . " " . $errCode);
}

if(!empty($currUser)){
	if(isset($req["action"]) && !empty($req["action"])){
		$action = $req["action"];

		switch ($action) {
			case 'send-message':
				sendChatMessage();
				break;

			case 'collect-message':
				collectChatMessages();
				break;

			case 'collect-emojis':
				collectEmojiIcons();
				break;
		}
	}
}else{
	abort(401);
}

echo json_encode($response);