<?php

include 'utils.php';

getInvoicesItemTemplates();

// getInvoices give out all invoices of the customer
// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"getInvoices"}'
function getInvoicesItemTemplates() {
	global $jPost;

	$res['method'] = "getInvoicesItemTemplates";	
	
	list($soap, $sessionId) = ispLogin();
	
	if($sessionId) {
		try {
			if (isset($_GET['id'])) {
				$templateId = filter_input(INPUT_GET,"id",FILTER_SANITIZE_STRING);
				$res["template_id"] = $templateId;
				$invoices = $soap->billing_invoice_item_template_get($sessionId, $templateId);
			} else {
				$invoices = $soap->billing_invoice_item_template_get($sessionId, -1);
			}
			$res['data'] = $invoices;
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t"."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
		
		// logout user
		$soap->logout($sessionId);	
	} else {
		$res['error'] = "ERR GC:\t"."\tcould not connect to the backend\n";
	}	
	echo json_encode($res);		
}	

