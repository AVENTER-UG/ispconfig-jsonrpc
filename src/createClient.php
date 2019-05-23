<?php

include 'utils.php';

createClient();

/*
 * Function to get out all client information
 *   return = json with client information
 * curl -vvv -H "Content-Type: application/json" -d '{"contact_fistname":"", contact_name":"", "username":"", "street":"", "zip":"", "city":"", "password":"", "language":"de", "email":"", "country":"DE", "addon":""}' -X GET localhost:8777/createClient.php
 */
function createClient() {
	global $jPost;
	global $config;

	$res['method'] = "createClient";

	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$params = array(        
				'contact_firstname' => $jPost->contact_firstname,
				'contact_name' => $jPost->contact_name,
				'username' => $jPost->username,
				'ssh_chroot' => 'no',
				'web_php_options' => 'no',
				'password' => $jPost->password,
				'usertheme' => 'default',
				'email' => $jPost->email,
				'parent_client_id' => $config["company"]["reseller_id"],
				'limit_client' => 0, // If this value is > 0, then the client is a reseller
				'language' => 'en',
				'created_at' => 0
				);
				
			$clientId = $soap->client_add($sessionId, $config["company"]["reseller_id"], $params);
			$res['client_id'] = $clientId;
		  
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

