<?php

$config = parse_ini_file(".config.ini", true);

$jPost = json_decode(file_get_contents("php://input"));


// Function to check if the token is from a user
//	return true if its a users token
function isUser($token) {
	if ($token->type == "user") {
		return true;
	}
	return false;
}

// Function to check if the token is from a admin
//	return true if its a users token
function isAdmin($token) {
	if ($token->type == "admin") {
		return true;
	}
	return false;
}

// checkToken will verify the token
// return = array of (auth: true or false, client_id)
function checkToken() {
	global $config;

	$authToken = substr($_SERVER['HTTP_AUTHORIZATION'], 7);

	if (empty($authToken)) {
			header('Authorization: Bearer');
			header('HTTP/1.0 401 Unauthorized');
			die("Authentication could not finish");
	}

	// Setup cURL
	$ch = curl_init($config["auth"]["auth_server"]."/api/v0/CheckUserToken");
	curl_setopt_array($ch, array(
	    CURLOPT_POST => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION, 1,
	    CURLOPT_HTTPHEADER => array(
	        'Authorization: Bearer '.$authToken,
	        'Content-Type: application/json'
	    )
	));

	$response = curl_exec($ch);

	if($response === false) {
		echo 'Curl error: ' . curl_error($ch);
	} else {
		curl_close($ch);
	}
	$responseData = json_decode($response);

	return $responseData;
}

// ISPConfig Login
function ispLogin() {
	global $config;

	$context = stream_context_create([
		'ssl' => [
			// set some SSL/TLS specific options
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => $config["ispconfig"]["allow_self_signed"]
		]
	]);

	$soap = new SoapClient(null, array('location' => $config["ispconfig"]["location"], 'uri' => $config["ispconfig"]["uri"], 'stream_context' => $context));
	$soapBilling = new SoapClient(null, array('location' => $config["ispconfig"]["billing"], 'uri' => $config["ispconfig"]["uri"], 'stream_context' => $context));

	$sessionId = $soap->login($config["ispconfig"]["username"],$config["ispconfig"]["password"]);

	return array($soap, $sessionId);
}
