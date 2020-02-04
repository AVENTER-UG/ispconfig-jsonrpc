<?php

include 'utils.php';

addInvoiceItem();

// curl -H "Content-Type: application/json" -X POST localhost:8888/jsonrpc.php -d '{"func":"addInvoiceItem","customer":"customername","unitprice":"100","quantity":"5", "period":"once|months","description":"Leistung fuer Zeitraum"}'
function addInvoiceItem() {
	global $jPost;
	global $config;

	$res['method'] = "addInvoiceItem";	

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
				// Rechnung erstellen
				$invoiceId = filter_input(INPUT_GET,"id",FILTER_SANITIZE_STRING);

				if (!$invoiceId) {
					$res['error'] = "ERR GC:\t".htmlentities($user['client_id'])."\t Unknown Error, but it looks like I could not create a Invoice\n"; 	
				} else {					

					$res["invoice_id"] = $invoiceId;

					$paymentDate = date("Y-m-d",mktime(0, 0, 0, date("m")+1, date("d"),   date("Y")));

					$params = array(
						'invoice_company_id' => $config["company"]["id"],
						'client_id' => $user["client_id"],
						'payment_terms' => $config["company"]["payment_terms"],
						'payment_gateway' => 'Auto',
						'status_printed' => 'n',
						'country' => strtoupper($user['country']),
						'status_sent' => 'n',
						'status_paid' => 'n',
						'name' => htmlentities($jPost->shop_title),
						'quantity' => htmlentities($jPost->quantity),
						'price' => preg_replace("/,/i",".",htmlentities($jPost->price)),
						'advance_payment' => 'y',
						'active' => 'y',
						'type' => 'clienttemplate',
						'start_date' => $paymentDate,
						'next_payment_date' => $paymentDate,
						'invoice_item_template_id' => $jPost->invoice_item_template_id,
						'description' => htmlentities($jPost->description),
						'recur_months' => htmlentities($jPost->recur_months),
						'invoice_vat_rate_id' => htmlentities($jPost->invoice_vat_rate_id),
						'vat' => "nicht_auto",
						'add_to_invoice' => 'y'					
					); 

					$req = $soap->billing_invoice_item_add($sessionId, $invoiceId, $params);

					$res["invoicedata"] = $req;
					$res["params"] = $params;
				}
			} catch (SoapFault $e) {
				$res['error'] = "ERR 1 GC:\t".htmlentities($user['client_id']).htmlentities($user['sys_userid'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
				$res['msg'] = $e->getMessage();
			}
		
		} catch (SoapFault $e) {
			$res['error'] = "ERR 2 GC:\t".htmlentities($user['client_id'])."\t".$e->getMessage()."\t".$soap->__getLastResponse()."\n"; 
			$res['msg'] = $e->getMessage();			
		}
	}

	echo json_encode($res);	
}
