<?php

include 'utils.php';

createInvoice();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"createInvoice","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function createInvoice() {
	global $jPost;
	global $config;

	$res['method'] = "createInvoice";	

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
			$user = $soap->client_get($sessionId, htmlentities($jPost->tenant_id));

			try {
				$params = array();
				// Rechnung erstellen
				$invoiceId = $soap->billing_invoice_add($sessionId, $user["client_id"], $params);

				if (!$invoiceId) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I could not create a Invoice\n"; 	
				} else {					

					$res["invoice_id"] = $invoiceId;

					$paymentDate = date("Y-m-d",mktime(0, 0, 0, date("m")+1, date("d"),   date("Y")));

					// Box hinzufuegen
					$params = array(
						'invoice_company_id' => $config["company"]["id"],
						'client_id' => $user["client_id"],
						'payment_terms' => $config["company"]["payment_terms"],
						'payment_gateway' => 'auto',
						'status_printed' => 'n',
						'country' => strtoupper($user['country']),
						'status_sent' => 'n',
						'status_paid' => 'n',
						'name' => 'MulinBox',
						'quantity' => '1',
						'price' => preg_replace("/,/i",".",htmlentities($jPost->price)),
						'advance_payment' => 'y',
						'active' => 'y',
						'type' => 'clienttemplate',
						'start_date' => $paymentDate,
						'next_payment_date' => $paymentDate,
						'invoice_item_template_id' => $config["company"]["default_invoice_template"],
						'description' => htmlentities($jPost->description),
						'recur_months' => '1',
						'vat' => 0,
						'add_to_invoice' => 'y'					
					); 

					$req = $soap->billing_invoice_item_add($sessionId, $invoiceId, $params);				
					$req = $soap->billing_invoice_recurring_item_add($sessionId, $invoiceId, $params);				

					// Provision hinzufuegen
					$params = array(
						'invoice_company_id' => $config["company"]["id"],
						'client_id' => $user["client_id"],
						'payment_terms' => $config["company"]["payment_terms"],
						'payment_gateway' => 'auto',
						'status_printed' => 'n',
						'country' => strtoupper($user['country']),
						'status_sent' => 'n',
						'status_paid' => 'n',
						'name' => 'MulinBox Service Fee',
						'quantity' => '1',
						'price' => ((preg_replace("/,/i",".",htmlentities($jPost->price)) / 100) * $config['company']['provision_percent']) / 2,
						'advance_payment' => 'y',
						'active' => 'y',
						'type' => 'clienttemplate',
						'start_date' => $paymentDate,
						'next_payment_date' => $paymentDate,
						'invoice_item_template_id' => $config["company"]["default_invoice_template"],						
						'description' => "MulinBox Service Fee",
						'recur_months' => '1',
						'invoice_vat_rate_id' => $config["company"]["vat_id"],
						'vat' => $config["company"]["vat"],
						'add_to_invoice' => 'y'					
					); 

					$req = $soap->billing_invoice_item_add($sessionId, $invoiceId, $params);
					$req = $soap->billing_invoice_recurring_item_add($sessionId, $invoiceId, $params);
		
					// Invoice finalizing and send it out
					$soap->billing_invoice_finalize($sessionId, $invoiceId);
					//$soap->billing_invoice_send($sessionId, $invoiceId, $config["company"]["default_invoice_email_template"]);
					$req = $soap->billing_invoice_get($sessionId, $invoiceId);

					$res["invoicedata"] = $req;
				}
			} catch (SoapFault $e) {
				$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
		}
	}

	echo json_encode($res);	
}
