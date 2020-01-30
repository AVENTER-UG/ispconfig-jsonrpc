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
				'contact_firstname' => htmlentities($jPost->contact_firstname),
				'contact_name' => htmlentities($jPost->contact_name),
				'username' => htmlentities($jPost->username),
				'ssh_chroot' => 'no',
				'web_php_options' => 'no',
				'password' => htmlentities($jPost->password),
				'usertheme' => 'default',
				'email' => htmlentities($jPost->email),
				'limit_client' => 0, // If this value is > 0, then the client is a reseller
				'limit_cron_type' => 'chrooted',
				'language' => 'de',
				'country' => 'DE',
				'created_at' => 0,
				'payment_gateway' => 'Auto',
				'invoice_company_id' => $config["company"]["id"]
			);
				
			$clientId = $soap->client_add($sessionId, $config["company"]["reseller_id"], $params);
			$res['client_id'] = $clientId;

			$params = array(        
				'payment_gateway' => 'Auto',
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

