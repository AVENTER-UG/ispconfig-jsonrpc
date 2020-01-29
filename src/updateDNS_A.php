<?php

include 'utils.php';

updateDNS_A();

/*
 * Function to update the dns 
 *   return = 
 * curl -vvv -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/updateDNS_A.php
 */
function updateDNS_A() {
	global $jPost;
	global $config;

	$res['method'] = "updateDNS_A";	

	// the token have to be valid and should be from a user or a admin
	$token = checkToken();
	if (!isUser($token) && !isAdmin($token) ) {
		$res["error"] = "unauthorized";
		echo json_encode($res);	
		return;
	}		

	list($soap, $sessionId) = ispLogin();

	
	if($sessionId) {
		try {
			$params = $soap->dns_a_get($sessionId, $jPost->dns_id);
			$params["name"] = $jPost->dns_name;
			$params["data"] = $jPost->dns_ip;
			$params["zone"] = $jPost->dns_zone;
			$res["dns"] = $soap->dns_a_update($sessionId, $token->client_id, $jPost->dns_id, $params);
			$params = $soap->dns_zone_get($sessionId, $jPost->dns_zone);
			$params["serial"] = $params["serial"]+rand(0,100); 
			$res["zone"] = $soap->dns_zone_update($sessionId, $token->client_id, $jPost->dns_zone, $params);
				$updateDns = new rex_sql();
				$updateDns->setTable("rex_com_user_dns");
       				$updateDns->setValue("ip",$myIp);
		    		$updateDns->setValue("time",time());
         				$updateDns->setWhere(sprintf("dns_id = '%s'",$dnsId));
         				$updateDns->update();	      			
		
  		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".$jPost->dns_name.$tmp['username']."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
	}
	

	print json_encode($res);	
}

