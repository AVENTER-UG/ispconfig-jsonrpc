<?php

include 'utils.php';

getInvoice();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"getInvoice","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function getInvoice() {
	global $jPost;
	global $config;

	$res['method'] = "getInvoice";	

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
				$invoiceId = filter_input(INPUT_GET,"id",FILTER_SANITIZE_STRING);

				if (!$invoiceId) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I could not create a Invoice\n"; 	
				} else {					

					$res["invoice_id"] = $invoiceId;

					$req = $soap->billing_invoice_get($sessionId, $invoiceId);

					$res["invoicedata"] = $req;
				}
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id']).htmlentities($user['sys_userid'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	echo json_encode($res);	
}
