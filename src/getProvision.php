<?php

include 'utils.php';

getProvision();

/*
 * Function to get out the Provision parameter
 *   return = json with Provision parameter
 * curl -vvv -d -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/getProvision.php
 */
function getProvision() {
	global $config;

	$res['method'] = "getProvision";	

	$res['privat'] = $config['company']['provision_percent']; 

	print json_encode($res);	
}

