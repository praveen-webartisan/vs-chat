<?php
//mail.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

define('SIGNUP_TEMPLATE_FILE', "mail-templates/signup.html");


function sendEmail($to, $subject, $content)
{
	$fromMail = getenv("SMTP_USERNAME");
	$fromMailPwd = getenv("SMTP_PASSWORD");

	$isSent = false;
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = getenv("SMTP_HOST");
	$mail->Port = getenv("SMTP_PORT");
	$mail->SMTPSecure = getenv("SMTP_ENCRYPTION");
	$mail->SMTPAuth = true;
	$mail->Username = $fromMail;
	$mail->setFrom($fromMail, APP_TITLE);
	$mail->Password = $fromMailPwd;
	$mail->addAddress($to);
	$mail->Subject = $subject;
	$mail->msgHTML($content);
	$isSent = $mail->send();

	return (object) ["isSent" => $isSent, "error" => $mail->ErrorInfo];
}