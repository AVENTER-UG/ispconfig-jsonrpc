<?php

$config = parse_ini_file("config.ini", true);

$jPost = json_decode(file_get_contents("php://input"));


switch (htmlentities($jPost->func)) {
	case "getInvoicesOfClient": getInvoicesOfClient(); break;
	case "test": test(); break;
}

function test() {
	echo "testA";
}

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"getInvoicesOfClient","customer":"andpeters2"}'
function getInvoicesOfClient() {
	global $jPost;

	$res['method'] = "getInvoicesOfClient";	
		
	list($soap, $sessionId) = ispLogin();
	
	if($sessionId) {
		try {
			$client = $soap->client_get_by_username($sessionId, htmlentities($jPost->customer));
			$invoices = $soap->billing_invoice_get_by_client($sessionId, $client["client_id"], 0);
			$res['data'] = $invoices;
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".htmlentities($jPost->login)."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
		
		// logout user
		$soap->logout($sessionId);	
	} else {
		$res['error'] = "ERR GC:\t".htmlentities($jPost->login)."\tcould not connect to the backend\n";
	}	
	echo json_encode($res);		
}	

// ISPConfig Login
function ispLogin() {
	global $config;


	$soap = new SoapClient(null, array('location' => $config["ispconfig"]["location"], 'uri' => $config["ispconfig"]["uri"]));
	$soapBilling = new SoapClient(null, array('location' => $config["ispconfig"]["billing"], 'uri' => $config["ispconfig"]["uri"]));	

	$sessionId = $soap->login($config["ispconfig"]["username"],$config["ispconfig"]["password"]);

	return array($soap, $sessionId);
}
