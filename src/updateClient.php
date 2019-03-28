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

	$res['method'] = "updateClient";

	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$soap->client_update($sessionId, $jPost->client_id, $config["company"]["reseller_id"], json_decode(json_encode($jPost), true));		  
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

