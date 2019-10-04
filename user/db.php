<?php

/**
 * DB Helper
*/

class DB
{
	private $DATABASE = "test";
	private $CHAT_TBL = "chat";
	private $USERS_TBL = "users";
	private $USER = "root";
	private $PWD = "letmein1!";
	private $activeYes = 1;
	private $activeNo = 1;

	public function __construct()
	{
		//
	}

	private function conn()
	{
		$conn = new mysqli("localhost", $this->USER, $this->PWD, $this->DATABASE);

		if($conn->connect_error){
			die("Connection error" . $conn->connect_error);
		}

		return $conn;
	}

	private function encryptPwd($pwd)
	{
		return md5($pwd);
	}

	public function fetchUser($userName, $pwd = '')
	{
		$sql = "SELECT
					*
				FROM
					{$this->USERS_TBL}
				WHERE
					is_active = {$this->activeYes}
				AND LOWER(username) = LOWER('{$userName}')";

		if(!empty($pwd)){
			$sql .= " AND password = '" . $this->encryptPwd($pwd) . "'";
		}

		$rows = [];
		$conn = $this->conn();
		$res = $conn->query($sql);

		if($res->num_rows > 0){
			while($row = $res->fetch_assoc()){
				$rows[] = (object) 	[
										"name" => $row["name"],
										"id" => $row["id"],
										"username" => $row["username"],
										"email" => $row["email"],
									];
			}
		}

		return $rows;
	}

	public function fetchChat($to)
	{
		$sql = "SELECT
					IF(
						message_from = '{$to}',
						'me',
						message_from
					) AS message_from,
					message_to,
					message as message,
					sent_at as at
				FROM
					{$this->CHAT_TBL}
				WHERE
					active_status = {$this->activeYes}
				AND (LOWER(message_to) = LOWER('$to') OR LOWER(message_from) = LOWER('$to'))";

		$rows = [];
		$conn = $this->conn();
		$res = $conn->query($sql);

		if($res->num_rows > 0){
			while($row = $res->fetch_assoc()){
				$rows[] = (object) 	[
										"from" => $row["message_from"],
										"to" => $row["message_to"],
										"message" => $row["message"],
										"at" => $row["at"],
										"senderIcon" => substr($row["message_from"], 0, 1)
									];
			}
		}

		return $rows;
	}

	public function storeChat($from, $to, $message)
	{
		$datetime = date("Y-m-d H:i:s");
		$sql = "INSERT INTO {$this->CHAT_TBL}
					(message_from, message_to, message, sent_at)
				VALUES
					(
						'{$from}',
						'{$to}',
						'{$message}',
						'{$datetime}'
					)";
		$conn = self::conn();

		return $conn->query($sql) === true;
	}

}