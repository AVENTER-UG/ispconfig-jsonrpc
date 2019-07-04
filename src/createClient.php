<?php

include 'utils.php';

createClient();

/*
 * Function to create a client
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
				'language' => 'de',
				'country' => 'DE',
				'created_at' => 0,
				'payment_gateway' => 'auto',
				'invoice_company_id' => $config["company"]["id"]
			);
				
			$clientId = $soap->client_add($sessionId, $config["company"]["reseller_id"], $params);
			$res['client_id'] = $clientId;

			$params = array(        
				'payment_gateway' => 'auto',
				'invoice_company_id' => $config["company"]["id"]
			);			
			
			$bill = $soap->billing_invoice_client_settings_update($sessionId, $clientId, $params);
			$res['billing'] = $bill;

		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->username."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}

	print json_encode($res);	
}

