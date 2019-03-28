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
				'username' => $jPost->username,
				'contact_firstname' => $jPost->contact_firstname,
				'contact_name' => $jPost->contact_name,
				'street' => $jPost->street,
				'zip' => $jPost->zip,
				'city' => $jPost->city,
				'language' => $jPost->language,
				'email' => $jPost->email,
				'telephone' => $jPost->telephone,
				'ssh_chroot' => 'no',
				'web_php_options' => 'no',				
				);

			$soap->client_update($sessionId, $token->client_id, $config["company"]["reseller_id"], $params);
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

