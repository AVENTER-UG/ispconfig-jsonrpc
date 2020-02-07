<?php

include 'utils.php';

dnsZoneAdd();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"dnsZoneAdd","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function dnsZoneAdd() {
	global $jPost;
	global $config;

	$res['method'] = "dnsZoneAdd";	

	// Check if the token is valid and the owner is a admin
	$token = checkToken();
	if (!isUser($token)) {
		$res["error"] = "unauthorized";
		echo json_encode($res);	
		return;
	}
		
	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {
			$user = $soap->client_get($sessionId, htmlentities($token->client_id));

			try {
				$params = array();
				
				$domain = filter_input(INPUT_GET,"domain",FILTER_SANITIZE_STRING);

				if (!$domain) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I cannot add a domain\n"; 	
				} else {					

					$res["domain"] = htmlspecialchars($domain);

					// Primary
					$params = array(
						'server_id' => $config["dns"]["server_id"],
						'origin' => htmlspecialchars($domain),
						'ns' => $config["dns"]["ns"],
						'mbox' => $config["dns"]["mbox"],
						'serial' => '1',
						'refresh' => '28800',
						'retry' => '7200',
						'expire' => '604800',
						'minimum' => '3600',
						'ttl' => '3600',
						'active' => 'y',
						'xfer' => $config["dns"]["xfer"],
						'also_notify' => $config["dns"]["notify"],
						'update_acl' => '',
					);

					$req = $soap->dns_zone_add($sessionId, $user["client_id"], $params);
					$res["dns_primary"] = $req;

					// Secondary
					$params = array(
						'server_id' => $config["dns"]["server_id_second"],
						'origin' => htmlspecialchars($domain),
						'ns' => $config["dns"]["ns_second"],
						'xfer' => '',
						'active' => 'y'
					);

					$req = $soap->dns_slave_add($sessionId, $user["client_id"], $params);					
					$res["dns_secondary"] = $req;
				}
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
				$res['msg'] = $e->getMessage();
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			$res['msg'] = $e->getMessage();			
		}
	}

	echo json_encode($res);	
}
