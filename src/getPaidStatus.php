<?php

include 'utils.php';

getPaidStatus();

function getPaidStatus() {
	global $jPost;
	global $config;

	$res['method'] = "getPaidStatus";	

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
			$res['invoice_id'] = $jPost->invoice_id;
			
			$user = $soap->client_get($sessionId, htmlentities($token->client_id));
			try {
				$req = $soap->billing_invoice_get($sessionId, $jPost->invoice_id);			
				$res["invoicedata"] = $req;
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	echo json_encode($res);	
}
