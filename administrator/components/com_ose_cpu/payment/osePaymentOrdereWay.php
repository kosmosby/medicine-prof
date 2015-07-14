<?php
defined('_JEXEC') or die(";)");

class osePaymentOrdereWay extends osePaymentGateWay
{
	protected $eWayCustomerID= null;
	protected $eWayUsername= null;
	protected $eWayPassword= null;
	protected $soap_link= null;
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;
	protected $test = true;

	function __construct($orderInfo,$pgwParams,$testMode = false)
	{
		parent::__construct();

		$this->orderInfo = $orderInfo;
		$this->test = $testMode;

		if($testMode)
		{
			$this->eWayCustomerID = '87654321';
			$this->eWayUsername = 'test@eway.com.au';
			$this->eWayPassword = 'test123';
			$this->soap_link = 'https://www.eway.com.au/gateway/rebill/test/managerebill_test.asmx';
		}
		else
		{
			$currency = oseObject::getValue($this->orderInfo,'payment_currency');
			$this->eWayCustomerID= oseObject::getValue($pgwParams,"eWayCustomerID_{$currency}");
			$this->eWayUsername= oseObject::getValue($pgwParams,"eWayUsername_{$currency}");
			$this->eWayPassword=oseObject::getValue($pgwParams,"eWayPassword_{$currency}");
			$this->soap_link = 'https://www.eway.com.au/gateway/rebill/managerebill.asmx';
		}

		if(empty($this->eWayCustomerID))
		{
			oseExit(getErrorMessage('cc', 0000,'Since the currency you are going to pay is not supported, the transaction will be aborted!'));
		}
	}

	function setCreditCardInfo($credit_info)
	{
		$postVar = array();
		$creditCardExpiryDate= $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate= explode("-", strval($creditCardExpiryDate));
		$postVar['ewayCardHoldersName']= $credit_info["creditcard_name"];
		$postVar['ewayCardNumber']= $credit_info["creditcard_number"];
		$postVar['ewayCardExpiryMonth']= $creditCardExpiryDate[1];
		$postVar['ewayCardExpiryYear']= substr($creditCardExpiryDate[0], 2,2);
		$postVar['ewayCVN']= $credit_info["creditcard_cvv"];

		$this->ccInfo = array_merge($this->ccInfo,$postVar);
	}

	private function createBillCustomer($billingInfo)
	{
		$postVar= array();
		$postVar['CustomerTitle']= '';

		//$postVar['ewayTotalAmount']= $taxRate / 100 * $recurAmount+ $recurAmount;
		//oseExit($billingInfo);
		if (!empty($billingInfo->firstname))
		{
			$postVar['CustomerFirstName']= substr($billingInfo->firstname, 0, 50);
			$postVar['CustomerLastName']= substr($billingInfo->lastname, 0, 50);
		}
		else
		{
			$customerName = explode(' ', $this->ccInfo["ewayCardHoldersName"]);
			$postVar['CustomerFirstName']= substr($customerName[0], 0, 50);
			$postVar['CustomerLastName']= substr($customerName[count($customerName)-1], 0, 50);
		}

		$postVar['CustomerAddress']= substr($billingInfo->addr1, 0, 50);
		$postVar['CustomerSuburb']= '';
		$postVar['CustomerState']= substr($billingInfo->state, 0, 50);
		$postVar['CustomerCompany']= '';
		$postVar['CustomerPostCode']= substr(str_replace(" ", "", $billingInfo->postcode), 0, 6);
		$postVar['CustomerCountry']= substr($billingInfo->country, 0, 20);
		$postVar['CustomerEmail']= $billingInfo->email;
		$postVar['CustomerFax'] = '';
		$postVar['CustomerPhone1'] = '';
		$postVar['CustomerPhone2'] = '';
		$postVar['CustomerRef'] = oseObject::getValue($this->orderInfo,'user_id');
		$postVar['CustomerJobDesc'] = '';
		$postVar['CustomerComments'] = '';
		$postVar['CustomerURL'] = '';

		$arr = array();
		foreach($postVar as $key => $value)
		{
			$key{0} =  strtolower($key{0});
			$arr[$key] = $value;
		}
		$postVar = $arr;
		//oseExit($postVar);
		$result = $this->_soapcall('CreateRebillCustomer', $postVar, true);

		return $result['CreateRebillCustomerResult'];
	}

	private function CreateRebill($customerId)
	{
		$db = oseDB::instance();
		$ccInfo = $this->get('ccInfo');
		$orderInfo = $this->get('orderInfo');
		$orderInfoParams = oseJson::decode(oseObject::getValue($orderInfo,'params'));
		//$postVar['ewayCVN']= $credit_info["creditcard_cvv"];

		// Date Time
		$curDate = oseHTML::getDateTime();
		$date = new DateTime($curDate);
		$RebillInitDate = $date->format('d/m/Y');

		if (!empty($orderInfoParams->has_trial))
		{
			$trialOccurrences =  "1";
			$string = "+{$orderInfoParams->p1} {$orderInfoParams->t1}";

		}
		else
		{
			$trialOccurrences =  "0";
			$string = "+{$orderInfoParams->p3} {$orderInfoParams->t3}";
		}

		$RebillStartDate = date('d/m/Y',strtotime($string,strtotime(oseHTML::getDateTime())));
		$RebillEndDate = date('d/m/Y',strtotime('+10 year',strtotime(oseHTML::getDateTime())));

		$postVar= array();
		$postVar['RebillCustomerID']= $customerId;
		$postVar['RebillInvRef']= oseObject::getValue($orderInfo,'order_number');
		$postVar['RebillInvDes']= '';

		$postVar['RebillCCName']= $ccInfo['ewayCardHoldersName'];
		$postVar['RebillCCNumber']= $ccInfo['ewayCardNumber'];
		$postVar['RebillCCExpMonth']= $ccInfo['ewayCardExpiryMonth'];
		$postVar['RebillCCExpYear']= $ccInfo['ewayCardExpiryYear'];

		$postVar['RebillInitAmt']= 0;//$orderInfo->payment_price * 100;
		$postVar['RebillInitDate']= $RebillInitDate;
		$postVar['RebillRecurAmt']= $orderInfoParams->next_total * 100;
		$postVar['RebillStartDate']= $RebillStartDate;
		$postVar['RebillInterval']= $orderInfoParams->p3;
		$postVar['RebillIntervalType']= $this->transIntervlUnit($orderInfoParams->t3);
		$postVar['RebillEndDate']= $RebillEndDate;//date("d/m/Y", strtotime("+ 3 years"));



		$result = $this->_soapcall('CreateRebillEvent', $postVar, true);

		return $result['CreateRebillEventResult'];
	}

	function getErrorMessage($paymentMethod, $code, $message = null)
	{
		$return = array();
		$return['payment']= $paymentMethod;
		$return['success']= false;
		$return['title']= JText :: _('Error');
		switch($code)
		{
			case '0000':
			$return['content']= $message;
			break;
			case '0001':
			$return['content']= JText :: _("eWay Customer ID or Username is not setup properly, please contact administrators for this issue.");
			break;
			case '0002':
			$return['content']= JText :: _("Please check your membership setting. Membership Price cannot be empty.");
			break;
			case '0003':
			$return['content']= JText :: _("eWay Payment Processor is not enabled, please enable it through OSE backend.");
			break;
			case '0004':
			$return['content']= JText :: _("Your order is activated, but the automatic billing has not been created. The error reported from our payment gateway is: <br />");
			$return['content'].=$message;
			break;

		}
		return $return;
	}

	private function parseResponse($content, $rebill = false) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		if ($rebill==false)
		{
		$resArray['ewayTrxnNumber']= OSECONNECTOR :: substring_between($content, '<ewayTrxnNumber>', '</ewayTrxnNumber>');
		$resArray['ewayTrxnStatus']= OSECONNECTOR :: substring_between($content, '<ewayTrxnStatus>', '</ewayTrxnStatus>');
		$resArray['ewayTrxnReference']= OSECONNECTOR :: substring_between($content, '<ewayTrxnReference>', '</ewayTrxnReference>');
		$resArray['ewayAuthCode']= OSECONNECTOR :: substring_between($content, '<ewayAuthCode>', '</ewayAuthCode>');
		$resArray['ewayReturnAmount']= OSECONNECTOR :: substring_between($content, '<ewayReturnAmount>', '</ewayReturnAmount>');
		$resArray['ewayTrxnError']= OSECONNECTOR :: substring_between($content, '<ewayTrxnError>', '</ewayTrxnError>');
		}
		else
		{
		$resArray['Result']= OSECONNECTOR :: substring_between($content, '<Result>', '</Result>');
		$resArray['ErrorSeverity']= OSECONNECTOR :: substring_between($content, '<ErrorSeverity>', '</ErrorSeverity>');
		$resArray['ErrorDetails']= OSECONNECTOR :: substring_between($content, '<ErrorDetails>', '</ErrorDetails>');
		}
		return $resArray;
	}



	function OneOffPay($TransactionType='AUTH_CAPTURE', $trialPayment=false)
	{
		ini_set('max_execution_time','180');

		$orderInfo = $this->orderInfo;
		$credit_info = $this->get('ccInfo');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		
		if(empty($orderInfoParams->next_total) || $orderInfoParams->next_total <=0) {
			return self::getErrorMessage('cc', '0002', null);
		}

		oseRegistry::call('remote')->getClientBridge('fsock');

		$result= array();
		$db= oseDB :: instance();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$orderInfoParams= oseJson :: decode($orderInfo->params);

		$desc =  $this->generateDesc($order_id);
		$billingInfo =  $this->getBillingInfo($orderInfo->user_id);

		$currency= $orderInfo->payment_currency;
		$user= & JFactory :: getUser($orderInfo->user_id);

		$app= & JFactory :: getApplication();

		$Itemid = $this->getItemid();
		
		// if trial is free, return;
		$amount= $orderInfo->payment_price;
		if((empty($amount) || $amount <=0) && $trialPayment)
		{
			$return = array();
			$return['success'] = true;
			return $return;
		}
		
		$postVar= array();
		/* $totalAmount in cents, as required by eWay:
		The total amount in cents for the transaction, eg $1.00 = 100
		*/

		$postVar['ewayTotalAmount']= intval(strval($amount*100));
		$postVar['ewayCustomerFirstName']= substr($billingInfo->firstname, 0, 50);
		$postVar['ewayCustomerLastName']= substr($billingInfo->lastname, 0, 50);
		$postVar['ewayCustomerEmail']= $billingInfo->email;
		$postVar['ewayCustomerAddress']= substr($billingInfo->addr1, 0, 60);
		$postVar['ewayCustomerPostcode']= substr(str_replace(" ", "", $billingInfo->postcode), 0, 6);
		$postVar['ewayCustomerInvoiceDescription']= $desc;
		$postVar['ewayCustomerInvoiceRef']= "Order ID: ".$order_id;

		$postVar = array_merge($postVar,$credit_info);

		$postVar['ewayTrxnNumber']= '';
		$postVar['ewayOption1']= $order_id;
		$postVar['ewayOption2']= '';
		$postVar['ewayOption3']= '';

		$resArray = $this->eWayAPIConnect($postVar, false);

		if($resArray['ewayTrxnStatus']=="True")
		{
			if ($trialPayment==false)
			{
				$params['payment_serial_number'] = $resArray['ewayTrxnNumber'];
				$return = parent:: confirmOrder($order_id, $params, 0, $user->id, 'eway');
			}
			else
			{
				$return = array();
				$return['success'] = true;
			}

			return $return;
		} else {
			return self::getErrorMessage('cc', '0000', $resArray['ewayTrxnError']);
		}
	}

	private function eWayAPIConnect($postVar, $rebill = false) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');

		$API_Endpoint= 'www.eway.com.au';
		if($this->test == true) {
			$API_Path = '/gateway_cvn/xmltest/testpage.asp';
		}
		else
		{
			$API_Path = '/gateway_cvn/xmlpayment.asp';
		}
		$xmlRequest= "<ewaygateway><ewayCustomerID>".$this->eWayCustomerID."</ewayCustomerID>";
		foreach($postVar as $key => $value)
		{
			$xmlRequest .= "<$key>".htmlentities($value)."</$key>";
		}
		$xmlRequest .= "</ewaygateway>";
		$response= OSECONNECTOR :: send_request_via_fsockopen($API_Endpoint, $API_Path, $xmlRequest);
		$resArray= self :: parseResponse($response, $rebill);
		return $resArray;
	}

	private function eWayRebillAPIConnect($eWayCustomerID,$eWayUsername,$eWayPassword = null ,$test_mode = true,$postVar, $rebill=true) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');

		$API_Endpoint= 'www.eway.com.au';
		if($test_mode == true) {
			$API_Path = '/gateway/rebill/test/Upload_test.aspx';
		}
		else
		{
			$API_Path = '/gateway/rebill/upload.aspx';
		}
		$xmlRequest= "<RebillUpload>\n" .
						"<NewRebill>\n" .
						"<eWayCustomerID>".$eWayCustomerID."</eWayCustomerID>\n" .
								"<Customer>\n" .
								"<CustomerRef>".$postVar['CustomerRef']."</CustomerRef>\n" .
								"<CustomerTitle></CustomerTitle>\n" .
								"<CustomerFirstName>".$postVar['CustomerFirstName']."</CustomerFirstName>\n" .
								"<CustomerLastName>".$postVar['CustomerLastName']."</CustomerLastName>\n" .
								"<CustomerCompany></CustomerCompany>\n" .
								"<CustomerJobDesc></CustomerJobDesc>\n" .
								"<CustomerEmail>".$postVar['CustomerEmail']."</CustomerEmail>\n" .
								"<CustomerAddress>".$postVar['CustomerAddress']." St</CustomerAddress>\n" .
								"<CustomerSuburb></CustomerSuburb>\n" .
								"<CustomerState>".$postVar['CustomerState']."</CustomerState>\n" .
								"<CustomerPostCode>".$postVar['CustomerPostCode']."</CustomerPostCode>\n" .
								"<CustomerCountry>".$postVar['CustomerCountry']."</CustomerCountry>\n" .
								"<CustomerPhone1></CustomerPhone1>\n" .
								"<CustomerPhone2></CustomerPhone2>\n" .
								"<CustomerFax></CustomerFax>\n" .
								"<CustomerURL></CustomerURL>\n" .
								"<CustomerComments></CustomerComments>\n" .
								"</Customer>\n" .
								"<RebillEvent>\n" .
								"<RebillInvRef>".$postVar['RebillInvRef']."</RebillInvRef>\n" .
								"<RebillInvDesc>".$postVar['RebillInvDesc']."</RebillInvDesc>\n" .
								"<RebillCCName>".$postVar['RebillCCName']."</RebillCCName>\n" .
								"<RebillCCNumber>".$postVar['RebillCCNumber']."</RebillCCNumber>\n" .
								"<RebillCCExpMonth>".$postVar['RebillCCExpMonth']."</RebillCCExpMonth>\n" .
								"<RebillCCExpYear>".$postVar['RebillCCExpYear']."</RebillCCExpYear>\n" .
								"<RebillInitAmt>".$postVar['RebillInitAmt']."</RebillInitAmt>\n" .
								"<RebillInitDate>".$postVar['RebillInitDate']."</RebillInitDate>" .
								"<RebillRecurAmt>".$postVar['RebillRecurAmt']."</RebillRecurAmt>\n" .
								"<RebillStartDate>".$postVar['RebillStartDate']."</RebillStartDate>\n" .
								"<RebillInterval>".$postVar['RebillInterval']."</RebillInterval>\n" .
								"<RebillIntervalType>".$postVar['RebillIntervalType']."</RebillIntervalType>\n" .
								"<RebillEndDate>".$postVar['RebillEndDate']."</RebillEndDate>\n" .
								"</RebillEvent>\n" .
						"</NewRebill>\n" .
					"</RebillUpload>";
		$response= OSECONNECTOR :: send_request_via_fsockopen($API_Endpoint, $API_Path, $xmlRequest);
		$resArray= self :: parseResponse($response, $rebill);
		return $resArray;
	}

	function CreateProfile($params= array())
	{
		ini_set('max_execution_time','180');
		$orderInfo = $this->orderInfo;
		$billingInfo= parent::getBillingInfo($orderInfo->user_id);

 		// First time charge for those does not support initial payments;
 		// SUITABLE HERE - eWay;
 		$result = $this->OneOffPay($TransactionType='AUTH_CAPTURE', true);

		if($result['success'])
		{
			$result = $this->createBillCustomer($billingInfo);

			if($result['Result'] == 'Success')
			{
				$RebillCustomerID = $result['RebillCustomerID'];
				$this->orderInfo = $this->updateOrderParams($orderInfo,array('RebillCustomerID'=>$RebillCustomerID));
				$resArray = $this->createRebill($RebillCustomerID);

				if($resArray['Result'] == 'Success')
				{
					$RebillID = $resArray['RebillID'];

					return $this->confirmOrder($orderInfo->order_id,array('payment_serial_number'=>$RebillID,'params'=>oseObject::getValue($this->orderInfo,'params')));
				}
				else
				{
					return $this->getErrorMessage('cc', '0004', $resArray['ErrorDetails']);
				}
			}
			else
			{
				return $this->getErrorMessage('cc', '0004', $result['ErrorDetails']);
			}
		}
		else
		{
			return $result;
		}

	}

	function DeleteProfile()//($payment_serial_number, $refID, $user_id, $msc_id = 0)
	{
		$orderInfo = $this->get('orderInfo');
		$orderInfoParams = oseJson::decode(oseObject::getValue($orderInfo,'params'));

		$postVar = array();
		$postVar['RebillID'] = oseObject::getValue($orderInfo,'payment_serial_number');
		$postVar['RebillCustomerID'] = $orderInfoParams->RebillCustomerID;

		$resArray = $this->_soapcall('DeleteRebillEvent', $postVar, true);
		$resArray = $resArray['DeleteRebillEventResult'];
		if($resArray['Result'] == 'Success')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Cancel');
			$result['content'] = JText::_('Your membership subscription is cancelled.');

			return $result;
		}
		else
		{
			return $this->getErrorMessage('cc', '0004', $resArray['ErrorDetails']);
		}
	}

	function queryRebill($type)
	{
		$orderInfo = $this->get('orderInfo');
		$orderInfoParams = oseJson::decode(oseObject::getValue($orderInfo,'params'));

		$postVar = array();
		$postVar['RebillID'] = oseObject::getValue($orderInfo,'payment_serial_number');
		$postVar['RebillCustomerID'] = $orderInfoParams->RebillCustomerID;

		switch($type)
		{
			case('today'):
				$postVar['startDate'] = date('Y-m-d',strtotime('-1 day',strtotime(oseHTML::getDateTime())));
				$postVar['endDate'] = date('Y-m-d',strtotime('+1 day',strtotime(oseHTML::getDateTime())));
				$postVar['status'] = 'Successful';
				$action = 'QueryTransactions';
				$resultKey = 'QueryTransactionsResult';
			break;

			case('next'):
				$action = 'QueryNextTransaction';
				$resultKey = 'QueryNextTransactionResult';
			break;

			case('bill'):
				$action = 'QueryRebillEvent';
				$resultKey = 'RebillEventDetails';
			break;
		}


		$resArray = $this->_soapcall($action, $postVar, true);

		$resArray = $resArray[$resultKey];

		if(!empty($resArray))
		{
			$resArray['success'] = true;

			return $resArray;
		}
		else
		{
			return $this->getErrorMessage('cc', '0004', $resArray['ErrorDetails']);
		}
	}

	private function _soapcall($call, $params)
	{
		oseRegistry::call('remote')->getClientBridge('soap');


		$headers = '<eWAYHeader xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">'."\r\n"
					   	.'<eWAYCustomerID>'.$this->eWayCustomerID.'</eWAYCustomerID>'."\r\n"
					   	.'<Username>'.$this->eWayUsername.'</Username>'."\r\n"
					   	.'<Password>'.$this->eWayPassword.'</Password>'."\r\n"
					  	.'</eWAYHeader>'."\r\n"
					;

		$soapclient = new nusoap_client($this->soap_link.'?wsdl','WSDL');

		$soapclient->setHeaders($headers);
		$result = $soapclient->call($call, array('parameters' => $params));

		/* Debug */
		//echo '<h2>Request</h2><pre>' . htmlspecialchars($soapclient->request, ENT_QUOTES) . '</pre>';
		//echo '<h2>Response</h2><pre>' . htmlspecialchars($soapclient->response, ENT_QUOTES) . '</pre>';
		//echo '<h2>Debug</h2><pre>' . htmlspecialchars($soapclient->debug_str, ENT_QUOTES) . '</pre>';
		//echo '<pre>'; print_r($result); echo '</pre>';

		// Check for a fault
		if ($soapclient->fault) {
			echo '<div class="alert error"><h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre></div>';
		} else {
			// Check for errors
			$err = $soapclient->getError();
			if ($err) {
				// Display the error
				echo '<div class="alert error"><h2>Error</h2><pre>' . $err . '</pre></div>';
			} else {
				return $result;
			}
		}

		return false;
	}

	protected  function transIntervlUnit($t3)
	{
		switch(strtolower($t3))
		{
			case('day'):
				$unit = 1;
			break;

			case('week'):
				$unit = 2;
			break;

			case('month'):
				$unit = 3;
			break;

			case('year'):
				$unit = 4;
			break;
		}
		return $unit;
	}
}
?>