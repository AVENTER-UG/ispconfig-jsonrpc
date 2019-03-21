<?php

include 'utils.php';

getInvoices();

// getInvoices give out all invoices of the customer
// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"getInvoices"}'
function getInvoices() {
	global $jPost;

	$res['method'] = "getInvoices";	

	// Check if the token is valid and the owner is a user
	$token = checkToken();
	if (!isUser($token)) {
		$res["error"] = "unauthorized";
		echo json_encode($res);	
		return;
	}	
	
	list($soap, $sessionId) = ispLogin();
	
	if($sessionId) {
		try {
			$invoices = $soap->billing_invoice_get_by_client($sessionId, $token["client_id"], 0);
			$res['data'] = $invoices;
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".htmlentities($token["client_id"])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
		
		// logout user
		$soap->logout($sessionId);	
	} else {
		$res['error'] = "ERR GC:\t".htmlentities($client["client_id"])."\tcould not connect to the backend\n";
	}	
	echo json_encode($res);		
}	

