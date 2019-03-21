<?php

include 'utils.php';

isReseller();

/*
 * Function to check if the user is a reseller
 *   return = json with true or false
 * curl -vvv -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/isReseller.php
 */
function isReseller() {
	global $jPost;
	global $config;

	$ret['method'] = "isReseller";	
	$ret['return'] = "false";

	// the token have to be valid and should be from a user or a admin
	$token = checkToken();
	if (!isUser($token) && !isAdmin($token) ) {
		$ret['error'] = "unauthorized";
		echo json_encode($res);	
		return;
	}		

	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$res = $soap->client_get($sessionId, $token->client_id);		

			if ($res['limit_client'] > 0) {
				$ret['return'] = "true"; 
			}
  		} catch(SoapFault $e) {		
			$ret['error'] = "ERR GC:\t".$jPost->customer."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	print json_encode($ret);	
}

