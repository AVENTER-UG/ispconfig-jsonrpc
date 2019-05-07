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
			$tmp = $soap->client_get($sessionId, $token->client_id);

			$res = array(
				'contact_firstname' => $tmp['contact_firstname'],
				'contact_name' => $tmp['contact_name'],
				'username' => $tmp['username'],
				'street' => $tmp['street'],
				'zip' => $tmp['zip'],
				'city' => $tmp['city'],
				'language' => $tmp['language'],
				'email' => $tmp['email'],
				'country' => $tmp['country'],
				'telephone' => $tmp['telephone'],
				'group_master' => $tmp['template_master'],
				'group_additional' => $tmp['template_additional']
			);

  		} catch(SoapFault $e) {		
			$res['error'] = "ERR GC:\t".$tmp['username']."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 			
	}

	print json_encode($res);	
}

