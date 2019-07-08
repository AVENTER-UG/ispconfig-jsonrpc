<?php

include 'utils.php';

updateClient();

/*
 * Function to get out all client information
 *   return = json with client information
 * curl -vvv -H "Content-Type: application/json" -d '{"contact_fistname":"", contact_name":"", "username":"", "street":"", "zip":"", "city":"", "password":"", "language":"de", "email":"", "country":"DE", "addon":""}' -X GET localhost:8777/updateClient.php
 */
function updateClient() {
	global $jPost;
	global $config;

	$token = checkToken();

	$res['method'] = "updateClient";

	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$params = array(
				'contact_firstname' => htmlentities($jPost->contact_firstname),
				'contact_name' => htmlentities($jPost->contact_name),
				'street' => htmlentities($jPost->street),
				'zip' => htmlentities($jPost->zip),
				'city' => htmlentities($jPost->city),
				'language' => htmlentities($jPost->language),
				'email' => htmlentities($jPost->email),
				'telephone' => htmlentities($jPost->telephone),
				'notes' => htmlentities($jPost->aboutme),
				'password' => ''
			);

			$soap->client_update($sessionId, $token->client_id, $config["company"]["reseller_id"], $params);
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

