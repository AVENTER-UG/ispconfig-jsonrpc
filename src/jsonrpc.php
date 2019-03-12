<?php

$config = parse_ini_file("config.ini", true);

$jPost = json_decode(file_get_contents("php://input"));


switch (htmlentities($jPost->func)) {
	case "getInvoicesOfClient": getInvoicesOfClient(); break;
	case "createInvoice": createInvoice(); break;
	case "checkAuth": checkAuth(); break;
	case "test": test(); break;
}

function test() {
	echo "testA";
}

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"getInvoicesOfClient","customer":"customername"}'
function getInvoicesOfClient() {
	global $jPost;

	$res['method'] = "getInvoicesOfClient";	
		
	list($soap, $sessionId) = ispLogin();
	
	if($sessionId) {
		try {
			$client = $soap->client_get_by_username($sessionId, htmlentities($jPost->customer));
			$invoices = $soap->billing_invoice_get_by_client($sessionId, $client["client_id"], 0);
			$res['data'] = $invoices;
		} catch(SoapFault $e) {
			$res['error'] = "ERR GC:\t".htmlentities($jPost->customer)."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		} 
		
		// logout user
		$soap->logout($sessionId);	
	} else {
		$res['error'] = "ERR GC:\t".htmlentities($jPost->login)."\tcould not connect to the backend\n";
	}	
	echo json_encode($res);		
}	

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"createInvoice","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function createInvoice() {
	global $jPost;
	global $config;

	$res['method'] = "createInvoice";	
		
	list($soap, $sessionId) = ispLogin();

	if ($sessionId) {
		try {			
			$client = $soap->client_get_by_username($sessionId, htmlentities($jPost->customer));
			$params = array(
				'invoice_type' => 'invoice',
		  		'invoice_company_id' => $config["company"]["id"],
		  		'client_id' => $client["client_id"],
		  		'payment_terms' => $config["company"]["payment_terms"],
		  		'payment_gateway' => 'auto',
		  		'status_printed' => 'n',
		  		'status_sent' => 'n',
		  		'status_paid' => 'n'
	  		); 

			// Create invoice
      		$invoiceId = $soap->billing_invoice_add($sessionId, $client["client_id"], $params);
			if (!$invoiceId) {
				$res['error'] = "ERR GC:\t".htmlentities($jPost->customer)."\t Unknown Error, but it looks like I could not create a Invoice\n"; 	
			} else {
				// Now we have to add the items into the invoice				
	  			$params = array( 
					'client_id' => $client["client_id"],
					'name' => $jPost->description,
					'quantity' => $jPost->quantity,
					'price' => preg_replace("/,/i",".",$jPost->unitprice),
					'vat' => $config["company"]["vat"],
					'advance_payment' => 'y',
					'active' => 'y',
					'type' => 'clienttemplate',
					'start_date' => date("d.m.Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y"))),
					'invoice_item_template_id' => $config["company"]["default_invoice_template"],
					'description' => $jPost->description
				);
				
				// if its a monthly paiment
				if ($jPost->period == "months") {
					$paramAdd = array(
						'recur_months' => '1',
					);

					array_push($params, $paramAdd);
					
					$req = $soap->billing_invoice_recurring_item_add($sessionId, $invoiceId, $params);
				}

				// if its a one time paiment
				if ($jPost->period == "once") {
					$req = $soap->billing_invoice_item_add($sessionId, $invoiceId, $params);
				}

	  			// Invoice finalizing and send it out
	  			//soap->billing_invoice_finalize($sessionId, $invoiceId);
				//$soap->billing_invoice_send($sessionId, $invoiceId, $config["company"]["default_invoice_email_template"]);
				$res["data"] = $invoiceId;
			}
		} catch (SoapFailt $e) {
			$res['error'] = "ERR GC:\t".htmlentities($jPost->customer)."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	echo json_encode($res);	
}

/*
 * Function to authentication the user via the rex_com_user table
 *   return = true (user auth correct) or false (user auth incorrect)
 */
function checkAuth() {
	global $jPost;
	global $config;

	$res['method'] = "checkAuth";
		
	list($soap, $sessionId) = ispLogin();	

	list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

	if (empty($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="DNS"');
	    	header('HTTP/1.0 401 Unauthorized');
	    	die("Authentication could not finish");
	} 
	
	$cusUser = htmlspecialchars($_SERVER['PHP_AUTH_USER'], ENT_QUOTES, 'UTF-8');
	$cusPassword = htmlspecialchars($_SERVER['PHP_AUTH_PW'], ENT_QUOTES, 'UTF-8');

	$sessionId = $soap->login($cusUser,$cusPassword, true);

	if ($sessionId) {
		$res['auth'] = true;
		return json_encode($res);
	}
}

// ISPConfig Login
function ispLogin() {
	global $config;


	$soap = new SoapClient(null, array('location' => $config["ispconfig"]["location"], 'uri' => $config["ispconfig"]["uri"]));
	$soapBilling = new SoapClient(null, array('location' => $config["ispconfig"]["billing"], 'uri' => $config["ispconfig"]["uri"]));	

	$sessionId = $soap->login($config["ispconfig"]["username"],$config["ispconfig"]["password"]);

	return array($soap, $sessionId);
}
