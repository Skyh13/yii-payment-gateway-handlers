<?php

class PayTraceTransMethods
{
	const NORMAL_TRANSACTION = "ProcessTranx";
	const EMAIL_RECEIPT = "EmailReceipt";
}

class PayTraceTransTypes
{
	const SALE = "Sale";
}

class PayTraceRequestParams
{
	const USERNAME = "UN";
	const PASSWORD = "PSWD";
	const AMOUNT = "AMOUNT";
	const CARD_NUMBER = "CC";
	const CSC = "CSC";
	const DESCRIPTION = "DESCRIPTION";
	const BILLING_NAME = "BNAME";
	const BILLING_ADDRESS1 = "BADDRESS";
	const BILLING_ADDRESS2 = "BADDRESS2";
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

class PayTraceResponseParams
{
	const RESPONSE = "RESPONSE";
	const TRANS_ID = "TRANSACTIONID";
	const APP_CODE = "APPCODE";
	const APP_MSG = "APPMSG";
	const AVS_RESPONSE = "AVSRESPONSE";
	const CSC_RESPONSE = "CSCRESPONSE";
	const ERROR = "ERROR"; 
}

class PayTraceHandler extends CComponent
{	
	private $header = array("MIME-Version: 1.0","Content-type: application/x-www-form-urlencoded","Contenttransfer-encoding: text");
	private $request = array();
	private $response = array();
	
	public $url = "https://paytrace.com/api/default.pay";
	
	public function setRequestData(array $data)
	{
		foreach($data as $key=>$value)
		{
			$this->request[$key] = $value;
		}
	}
	
	public function setRequestParameter($parameter, $value)
	{
		$this->request[$parameter] = $value;
	}
	
	public function getResponseParameter($parameter)
	{
		if(isset($this->response[$parameter]))
		{
			return $this->response[$parameter];
		}
		else 
		{
			return false;
		}
	}

	private function parseResponse($res)
	{
		$responseArr = explode('|', $res);
		foreach ($responseArr as $pair ){
			$tmp = explode('~',$pair);
			if($tmp != false &&
				isset($tmp[1]))
			{
				$this->response[$tmp[0]] = $tmp[1];
			}
		}
	}
	
	private function SendPayTraceAPIRequest(array $request)
	{
		$str_request = "parmlist=";
		$tmp = "";
		foreach($request as $key=>$value)
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
	
	public function execute()
	{
		$response = $this->SendPayTraceAPIRequest($this->request);
		$this->parseResponse($response);
		return $this->response;
	}
	
	public function validate()
	{
		if($this->response != null)
		{
			if(isset($this->response[PayTraceResponseParams::ERROR]))
			{
				throw new CException('Transaction was not successful per the following error: ' . $this->response[PayTraceResponseParams::ERROR]);
			}
			else 
			{
				if(isset($this->response[PayTraceResponseParams::APP_CODE]))
				{
					return $this->response[PayTraceResponseParams::RESPONSE];
				}
				else
				{
					throw new CException('The Credit Transaction was NOT Approved, with the following error: ' . $this->response[PayTraceResponseParams::RESPONSE]);
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