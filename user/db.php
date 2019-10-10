<?php

/**
 * DB Helper
*/

class DB
{
	private $DATABASE = "test";
	private $CHAT_TBL = "chat";
	private $USERS_TBL = "users";
	private $EMOJI_TBL = "emoji";
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

	public function fetchUser($filter = [])
	{
		$sql = "SELECT
					*
				FROM
					{$this->USERS_TBL}
				WHERE";
		$activeSql = " is_active = {$this->activeYes}";
		$whereCond = "";

		if(isset($filter["inclInactive"]) && $filter["inclInactive"] === true){
			$activeSql = "";
		}

		if(isset($filter["username"]) && !empty($filter["username"])){
			$username = $filter["username"];
			$whereCond = (!empty($activeSql) ? " AND" : "") . " LOWER(username) = LOWER('$username')";
		}

		if(isset($filter["email"]) && !empty($filter["email"])){
			$email = $filter["email"];
			$whereCond .= (!empty($activeSql) ? " AND" : "") . " LOWER(email) = LOWER('$email')";
		}

		if(isset($filter["userOrEmail"]) && !empty($filter["userOrEmail"])){
			$userOrEmail = $filter["userOrEmail"];
			$whereCond .= (!empty($activeSql) ? " AND" : "") . " (LOWER(username) = LOWER('$userOrEmail') OR LOWER(email) = LOWER('$userOrEmail'))";
		}

		if(isset($filter["pwd"]) && !empty($filter["pwd"])){
			$whereCond .= (!empty($activeSql) ? " AND" : "") . " password = '" . $this->encryptPwd($filter["pwd"]) . "'";
		}

		$sql .= $activeSql . $whereCond;

		$rows = [];

		if(count($filter) > 0 && !empty($whereCond)){
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
					DATE_FORMAT(sent_at, '%d-%m-%Y on %H:%i %p') AS sentDateTime
				FROM
					{$this->CHAT_TBL}
				WHERE
					active_status = {$this->activeYes}
				AND (LOWER(message_to) = LOWER('$to') OR message_to = 'all' OR LOWER(message_from) = LOWER('$to'))";

		$rows = [];
		$conn = $this->conn();
		$res = $conn->query($sql);

		if($res->num_rows > 0){
			while($row = $res->fetch_assoc()){
				$rows[] = (object) 	[
										"from" => $row["message_from"],
										"to" => $row["message_to"],
										"message" => $row["message"],
										"at" => $row["sentDateTime"],
										"senderIcon" => substr($row["message_from"], 0, 1)
									];
			}
		}

		return $rows;
	}

	public function storeUser($username, $email, $pwd)
	{
		$datetime = date("Y-m-d H:i:s");
		$pwd = $this->encryptPwd($pwd);
		$name = ucwords($username);
		$sql = "INSERT INTO {$this->USERS_TBL}
					(name, username, email, password, is_active, date_created)
				VALUES
					(
						'{$name}',
						'{$username}',
						'{$email}',
						'{$pwd}',
						'{$this->activeYes}',
						'{$datetime}'
					)";
		$conn = self::conn();

		return $conn->query($sql) === true ? $conn->insert_id : false;
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

	public function fetchEmojis()
	{
		$sql = "SELECT
					*
				FROM
					{$this->EMOJI_TBL}";

		$icons = null;
		$conn = $this->conn();
		$res = $conn->query($sql);

		if($res->num_rows > 0){
			while($row = $res->fetch_assoc()){
				$icons = $row["icons"];
			}
		}

		return $icons;
	}

}