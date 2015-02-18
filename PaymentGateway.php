<?php

class Gateways {
	const PAYTRACE = "paytrace.php";
	const AUTHORIZENET = "authorizenet.php";
}

class GatewayParams {
	public $creditCardNumber;
	public $zipcode;
	public $address1;
	public $address2;
	public $city;
	public $country;
	public $state;
	public $expMon;
	public $expYear;
	public $amount;
	public $login;
	public $key;
	public $sandbox;
	public $test;
	
	public $transactionID;
	public $errorMsg;	
}

class PaymentGateway {
	
	public $params;
	private $gatewayObj;
	
	function __construct ($gateway) {
		
		$params = new GatewayParams();
		
		if($gateway == Gateways::PAYTRACE) {
			$gatewayObj = require Gateways::PAYTRACE;
		}
		else if ($gateway == Gateways::AUTHORIZENET) {
			$gatewayObj = require Gateways::AUTHORIZENET;
		}
		
	}
	
	function authorize() {
		$gatewayObj->authorize($params);
	}
	
	function capture() {
		$gatewayObj->capture($params);
	}
	
}

class AuthorizeNET {
	
	// autoload script
	function __construct() {
		require "authorizenet/autoload.php";
	}
	
	function authorize($params) {
		define("AUTHORIZENET_API_LOGIN_ID", $params->login);
		define("AUTHORIZENET_TRANSACTION_KEY", $params->key);
		define("AUTHORIZENET_SANDBOX", $params->sandbox);
		
		$sale = new AuthorizeNetAIM;
		$sale->amount = $params->amount;
		$sale->card_num = $params->creditCardNumber;
	}
	
}

class PayTrace {
	
	
	function authorize($params) {
		
	}
}

?>