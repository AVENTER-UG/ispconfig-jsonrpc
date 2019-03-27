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
				'street' => $jPost->street,
				'zip' => $jPost->zip,
				'city' => $jPost->city,
				'ssh_chroot' => 'no',
				'web_php_options' => 'no',
				'password' => $jPost->password,
				'language' => $jPost->language,
				'usertheme' => 'default',
				'email' => $jPost->email,
				'country' => $jPost->country,
				'active' => 'y',
				'template_additional' => "/".$jPost->addon."/"				
				);
				
			$clientId = $soap->client_add($sessionId, $config["company"]["reseller_id"], $params);
			$res['client_id'] = $clientId;
		  
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

