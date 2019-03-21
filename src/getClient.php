<?php

include 'utils.php';

getClient();

/*
 * Function to get out all client information
 *   return = json with client information
 * curl -vvv -d '{"func":"getCient"}' -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/getClient.php
 */
function getClient() {
	global $jPost;
	global $config;

	$res['method'] = "getClient";	

	// the token have to be valid and should be from a user or a admin
	$token = checkToken();
	if (!isUser($token) && !isAdmin($token) ) {
		$res["error"] = "unauthorized";
		echo json_encode($res);	
		return;
	}		

	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$res = $soap->client_get($sessionId, $token->client_id);		
  		} catch(SoapFault $e) {		
			$res['error'] = "ERR GC:\t".$jPost->customer."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 			
	}

	print json_encode($res);	
}

