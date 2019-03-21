<?php

include 'utils.php';

ofReseller();

/*
 * Function to check if the user is from a reseller. 
 *   return = json with true and false and if true then the reseller id 
 * curl -vvv -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/ofReseller.php
 */
function ofReseller() {
	global $jPost;
	global $config;

	$ret['method'] = "ofReseller";	
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

			if ($res['parent_client_id'] > 0) {
				$ret['return'] = "true"; 
				$ret['reseller_id'] = $res['parent_client_id'];
			}
  		} catch(SoapFault $e) {		
			$ret['error'] = "ERR GC:\t".$jPost->customer."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	print json_encode($ret);	
}

