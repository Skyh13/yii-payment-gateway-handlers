<?php

namespace com\PaymentGatewayHandlers\Handlers;

class AuthorizeNetTransMethods
{
	const NORMAL_TRANSACTION = "CC";
}

class AuthorizeNetTransTypes
{
	const SALE = "AUTH_CAPTURE";
}

class AuthorizeNetRequestParams
{
	const LOGIN = 				"x_login";
	const TRANS_KEY = 			"x_tran_key";
	const AMOUNT = 				"x_amount";
	const CARD_NUMBER = 		"x_card_num";
	const CSC = 				"x_card_code";
	const DESCRIPTION = 		"x_description";
	const DELIM_CHAR = 			"x_delim_char";
	const DELIM_DATA = 			"x_delim_data";
	const BILLING_FIRST_NAME = 	"x_first_name";
	const BILLING_LAST_NAME = 	"x_last_name";
	const BILLING_ADDRESS1 = 	"x_address";
	const BILLING_ADDRESS2 = 	"x_address_2";
	const BILLING_STATE = 		"x_state";
	const BILLING_ZIP = 		"x_zip";
	const BILLING_CITY = 		"x_city";
	const BILLING_COUNTRY = 	"x_country";
	const EMAIL = 				"x_email";
	const EXP_DATE = 			"x_exp_date";
	const METHOD = 				"x_method";
	const RELAY_RESPONSE = 		"x_relay_response";
	const TRANS_TYPE = 			"x_type";// Sale
	const TEST = 				"x_test_request";
	const VERSION = 			"x_version";	
}

class AuthorizeNetResponseParams
{
	const RESPONSE_CODE = 		0;
	const RESPONSE_SUBCODE = 	1;
	const REASON_CODE = 		2;
	const REASON_TEXT = 		3;
	const AUTH_CODE = 			4;
	const AVS_RESPONSE = 		5;
}

class AuthorizeNetResponseCodes
{
	const APPROVED = 	1;
	const DECLINED = 	2;
	const ERROR = 		3;
	const HELD = 		4;
}

class AuthorizeNetAVSResponseCodes
{
	const NO_MATCH = "N";
}

class AuthorizeNet implements com\PaymentGatewayHandlers\IPaymentGatewayHandler
{	
	private $header; = array("MIME-Version: 1.0","Content-type: application/x-www-form-urlencoded","Contenttransfer-encoding: text");
	private $url = "https://secure.authorize.net/gateway/transact.dll";
	public $requestData = array();
	
	public function requiredParams()
	{
		return array(
			AuthorizeNetRequestParams::LOGIN,
			AuthorizeNetRequestParams::TRANS_KEY,
			AuthorizeNetRequestParams::AMOUNT,
			AuthorizeNetRequestParams::CARD_NUMBER,
			AuthorizeNetRequestParams::CSC,
			AuthorizeNetRequestParams::DESCRIPTION,
			AuthorizeNetRequestParams::DELIM_CHAR,
			AuthorizeNetRequestParams::DELIM_DATA,
			AuthorizeNetRequestParams::BILLING_FIRST_NAME,
			AuthorizeNetRequestParams::BILLING_LAST_NAME,
			AuthorizeNetRequestParams::BILLING_ADDRESS1,
			AuthorizeNetRequestParams::BILLING_ADDRESS2,
			AuthorizeNetRequestParams::BILLING_STATE,
			AuthorizeNetRequestParams::BILLING_ZIP,
			AuthorizeNetRequestParams::BILLING_CITY,
			AuthorizeNetRequestParams::BILLING_COUNTRY,
			AuthorizeNetRequestParams::EMAIL,
			AuthorizeNetRequestParams::EXP_DATE,
			AuthorizeNetRequestParams::METHOD,
			AuthorizeNetRequestParams::RELAY_RESPONSE,
			AuthorizeNetRequestParams::TRANS_TYPE,
			AuthorizeNetRequestParams::TEST,
			AuthorizeNetRequestParams::VERSION						
		);
	}
	
	public function parseResponse(array $res)
	{
		// This line takes the response and breaks it into an array using the specified delimiting character
		return explode($this->requestData[AuthorizeNetRequestParams::DELIM_CHAR], $res);
	}
	
	private function sendRequest(array $request)
	{
		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $request as $key => $value )
			{ $post_string .= "$key=" . urlencode( $value ) . "&"; }
		$post_string = rtrim( $post_string, "& " );
		
		// This sample code uses the CURL library for php to establish a connection,
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$curl_request = curl_init($this->url); // initiate curl object
		curl_setopt($curl_request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($curl_request); // execute curl post and store results in $post_response
		// additional options may be required depending upon your server configuration
		// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ($curl_request); // close curl object
		
		return $post_response;
	}
	
	public function validate(array $parsedResponseData)
	{
		if($parsedResponseData != null)
		{
			switch ($parsedResponseData[AuthorizeNetResponseParams::RESPONSE_CODE])
			{
				case AuthorizeNetResponseCodes::APPROVED:
				break;
				
				case AuthorizeNetResponseCodes::DECLINED:
					throw new CException('The Credit Transaction was NOT Approved, with the following error: ' . $parsedResponseData[AuthorizeNetResponseParams::REASON_TEXT]);
				break;
				
				case AuthorizeNetResponseCodes::HELD:
					throw new CException('Transaction has been held for review, per the following error: ' . $parsedResponseData[AuthorizeNetResponseParams::REASON_TEXT]);
				break;
				
				case AuthorizeNetResponseCodes::ERROR:
				default:
					throw new CException('Transaction was not successful per the following error: ' . $parsedResponseData[AuthorizeNetResponseParams::REASON_TEXT]);
				break;
				
			}
		}
		else 
		{
			throw new CException('An error occurred: There was no response from the payment system.');
		}
		
		return true;
	}
}
?>
