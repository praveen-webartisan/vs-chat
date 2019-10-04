<?php

require('db.php');

$response = [];
$req = file_get_contents("php://input");
$req = empty($req) ? (isset($_REQUEST) ? $_REQUEST : []) : $req;
$req = is_string($req) ? (array) json_decode($req) : $req;

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

if(isset($req["action"]) && !empty($req["action"])){
	$action = $req["action"];

	switch ($action) {
		case 'send-message':
			sendChatMessage();
			break;

		case 'collect-message':
			collectChatMessages();
			break;
	}
}

echo json_encode($response);