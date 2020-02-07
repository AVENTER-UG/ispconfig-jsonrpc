<?php

include 'utils.php';

domainAdd();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"domainAdd","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function domainAdd() {
	global $jPost;
	global $config;

	$res['method'] = "domainAdd";	

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
				$params = array();
				
				$domain = filter_input(INPUT_GET,"domain",FILTER_SANITIZE_STRING);

				if (!$domain) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I cannot add a domain\n"; 	
				} else {					

					$res["domain"] = htmlspecialchars($domain);


					$params = array(
						'domain' => htmlspecialchars($domain)
					); 

					$req = $soap->domains_domain_add($sessionId, $user["client_id"], $params);

					$res["domain"] = $req;
				}
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
