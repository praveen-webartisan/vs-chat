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
	$fromMail = "iampraveen017@gmail.com";
	$fromMailPwd = "17-05-1998";

	$isSent = false;
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 587;
	$mail->SMTPSecure = 'tls';
	$mail->SMTPAuth = true;
	$mail->Username = $fromMail;
	$mail->setFrom($fromMail, "Praveen");
	$mail->Password = $fromMailPwd;
	$mail->addAddress($to);
	$mail->Subject = $subject;
	$mail->msgHTML($content);
	$isSent = $mail->send();

	return (object) ["isSent" => $isSent, "error" => $mail->ErrorInfo];
}