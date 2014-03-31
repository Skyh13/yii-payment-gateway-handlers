<?php

namespace com\PaymentGatewayHandlers\Handlers;

class PayTraceTransMethods
{
	const NORMAL_TRANSACTION = "ProcessTranx";
	const EMAIL_RECEIPT = "EmailReceipt";
}

class PayTraceTransTypes
{
	const SALE = "Sale";
}

class PayTraceRequestParms
{
	const USERNAME = "UN";
	const PASSWORD = "PSWD";
	const AMOUNT = "AMOUNT";
	const CC_NUMBER = "CC";
	const CSC ="CSC";
	CONST DESC = "DESCRIPTION";
	const BILLING_NAME = "BNAME";
	const BILLING_ADDRESS = "BADDRESS";
	const BILLING_ADDRESS_2 = "BADDRESS2";
	const BILLING_STATE = "BSTATE";
	const BILLING_ZIP = "BZIP";
	const BILLING_CITY = "BCITY";
	const BILLING_COUNTRY = "BCOUNTRY";
	const EMAIL = "EMAIL";
	const EXP_MONTH = "EXPMNTH";
	const EXP_YEAR = "EXPYR";
	const METHOD = "METHOD"; // ProcessTranx
	const TERMS = "TERMS";
	const TRANS_TYPE = "TRANXTYPE";// Sale
	const TEST = "TEST";
}

class PayTraceResponseParms
{
	const RESPONSE = "RESPONSE";
	const TRANS_ID = "TRANSACTIONID";
	const APP_CODE = "APPCODE";
	const APP_MSG = "APPMSG";
	const AVS_RESPONSE = "AVSRESPONSE";
	const CSC_RESPONSE = "CSCRESPONSE";
	const ERROR = "ERROR"; 
}

class PayTrace implements com\PaymentGatewayHandlers\IPaymentGatewayHandler
{	
	private $header = array("MIME-Version: 1.0","Content-type: application/x-www-form-urlencoded","Contenttransfer-encoding: text");
	private $url = "https://paytrace.com/api/default.pay";
	public $requestData = array();

	public function requiredParams()
	{
		return array(
		 PayTraceRequestParms::USERNAME, 
		 PayTraceRequestParms::PASSWORD,
		 PayTraceRequestParms::AMOUNT, 
		 PayTraceRequestParms::CC_NUMBER,
		 PayTraceRequestParms::CSC, 
		 PayTraceRequestParms::DESC,
		 PayTraceRequestParms::BILLING_NAME, 
		 PayTraceRequestParms::BILLING_ADDRESS, 
		 PayTraceRequestParms::BILLING_ADDRESS_2, 
		 PayTraceRequestParms::BILLING_STATE,
		 PayTraceRequestParms::BILLING_ZIP,
		 PayTraceRequestParms::BILLING_CITY,
		 PayTraceRequestParms::BILLING_COUNTRY, 
		 PayTraceRequestParms::EMAIL,
		 PayTraceRequestParms::EXP_MONTH, 
		 PayTraceRequestParms::EXP_YEAR,
		 PayTraceRequestParms::METHOD, // ProcessTranx
		 PayTraceRequestParms::TERMS,
		 PayTraceRequestParms::TRANS_TYPE, // Sale
		 PayTraceRequestParms::TEST		
		);
	}
	
	public function parseResponse($res)
	{
		$responseArr = explode('|', $res);
		foreach ($responseArr as $pair ){
			$tmp = explode('~',$pair);
			if($tmp != false &&
				isset($tmp[1]))
			{
				$this->responseData[$tmp[0]] = $tmp[1];
			}
		}
	}
	
	public function sendRequest(array $request)
	{
		$str_request = "parmlist=";
		$tmp = "";
		foreach($this->requestData as $key=>$value)
		{
			$tmp .= $key . '~' . $value . '|';
		}
		
		$str_request .= urlencode($tmp);
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		//Depending on your PHP Host, you may need to specify their proxy server
		//curl_setopt ($ch, CURLOPT_PROXY, "http://proxyaddress:port");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str_request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		// grab URL and pass it to the browser
		$response = curl_exec($ch);
		// close curl resource, and free up system resources
		curl_close($ch);
		
		return $response;
	}
	
	public function validate(array $parsedResponseData)
	{
		if($parsedResponseData != null)
		{
			if(isset($parsedResponseData[PayTraceResponseParams::ERROR]))
			{
				throw new CException('Transaction was not successful per the following error: ' . $parsedResponseData[PayTraceResponseParams::ERROR]);
			}
			else 
			{
				if(isset($parsedResponseData[PayTraceResponseParams::APP_CODE]))
				{
					return $parsedResponseData[PayTraceResponseParams::RESPONSE];
				}
				else
				{
					throw new CException('The Credit Transaction was NOT Approved, with the following error: ' . $parsedResponseData[PayTraceResponseParams::RESPONSE]);
				}
			}
		}
		else 
		{
			throw new CException('An error occurred: There was no response from the payment gateway.');
		}
	}
}
?>
