<?php

include 'utils.php';

getVat();

/*
 * Function to get out the VAT parameter
 *   return = json with VAT parameter
 * curl -vvv -d -H "Content-Type: application/json"  -H "Authorization: Bearer <TOKEN>" -X GET localhost:8777/getVat.php
 */
function getVat() {
	global $config;

	$res['method'] = "getVat";	

	$res['de'] = $config['company']['vat']; 

	print json_encode($res);	
}

