<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'lib/Exception.php';
require 'lib/PHPMailer.php';
require 'lib/SMTP.php';

include 'utils.php';

sendEMail();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"sendEMail","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function sendEMail() {
	global $jPost;
	global $config;

	$res['method'] = "sendEMail";	

	// Check if the token is valid and the owner is a admin
	$token = checkToken();
	if (!isUser($token)) {
		$res["error"] = "unauthorized";
		echo json_encode($res);	
		return;
	}
		
	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$user = $soap->client_get($sessionId, htmlentities($token->client_id));

			try {
				$res["user"] = $user;
				$res["mail"] = $jPost;
				$res["mail_send"] = sendMail($user, $jPost);
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
				$res['msg'] = $e->getMessage();
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			$res['msg'] = $e->getMessage();
		}
	}

	echo json_encode($res);	
}


function sendMail($user) {
	global $jPost;
	global $config;

	$recipient=$user["email"];
	$subject=$jPost->mail_subject;
	$header="From: ".$config["email"]["sender"]."\n";
	$mail_body = $jPost->mail_body."\n\n";	

	$mail = new PHPMailer();
	
	$mail->isSMTP();                   
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->Host = $config["email"]["host"];
	$mail->SMTPAuth = true;              
	$mail->Username = $config["email"]["username"];
	$mail->Password = $config["email"]["password"];   
	$mail->Port     = 25;	
	$mail->SMTPAutoTLS = false;
	
	$mail->From = $config["email"]["sender"];   
	$mail->addAddress($user["email"], $user["username"]);  
	
	$mail->WordWrap = 100;                                 
	$mail->isHTML(false);                                 
	
	$mail->Subject = $jPost->mail_subject;
	$mail->Body    = $jPost->mail_body;
	
	if(!$mail->send()) {
		return $mail->ErrorInfo;
	} else {
		return true;
	}
  }