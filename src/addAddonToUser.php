<?php

include 'utils.php';

addAddonToUser();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"addAddonToUser","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function addAddonToUser() {
	global $jPost;
	global $config;

	$res['method'] = "addAddonToUser";	

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
				
				$addonId = filter_input(INPUT_GET,"id",FILTER_SANITIZE_STRING);

				if (!$addonId) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I could not create a Invoice\n"; 	
				} else {					

					$res["addon_id"] = $addonId;

					$req = $soap->client_template_additional_add($sessionId, $user['client_id'], $addonId);


					$res["user_update"] = $req;
				}
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id']).htmlentities($user['sys_userid'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
				$res['msg'] = $e->getMessage();
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			$res['msg'] = $e->getMessage();			
		}
	}

	echo json_encode($res);	
}
