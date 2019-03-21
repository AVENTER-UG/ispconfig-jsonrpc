<?php

include 'utils.php';

checkAuth();

/*
 * Function to authentication the user via the rex_com_user table
 *   return = true (user auth correct) or false (user auth incorrect)
 * 
 * curl -vvv -H "Content-Type: application/json" -u <username>:<password> -X POST localhost:8777/checkAuth.php
 */
function checkAuth() {
	global $jPost;
	global $config;

	$res['method'] = "checkAuth";
		
	list($soap, $sessionId) = ispLogin();	

	if ($sessionId) {
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

		if (empty($_SERVER['PHP_AUTH_USER'])) {
				header('WWW-Authenticate: Basic realm="DNS"');
		    	header('HTTP/1.0 401 Unauthorized');
		    	die("Authentication could not finish");
		} 

		$cusUser = htmlspecialchars($_SERVER['PHP_AUTH_USER'], ENT_QUOTES, 'UTF-8');
		$cusPassword = htmlspecialchars($_SERVER['PHP_AUTH_PW'], ENT_QUOTES, 'UTF-8');
		try {
			$res['client'] = $soap->client_login_get($sessionId, $cusUser, $cusPassword);
			$res['auth'] = true;
		} catch(SoapFault $e) {
			$res['error'] = $e;
			$res['auth'] = false;
		}
	}
	
	print json_encode($res);
}
