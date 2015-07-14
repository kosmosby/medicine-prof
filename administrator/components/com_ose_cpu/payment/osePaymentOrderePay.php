<?php
defined('_JEXEC') or die(";)");
class osePaymentOrderePay extends osePaymentOrder
{
	var $table= null;
	function __construct($table= '#__osemsc_order')
	{
		$this->table= $table;
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
			$return['content']= JText :: _("ePay Customer ID or Username is not setup properly, please contact administrators for this issue.");
			break;
			case '0002':
			$return['content']= JText :: _("Please check your membership setting. Membership Price cannot be empty.");
			break;
			case '0003':
			$return['content']= JText :: _("ePay Payment Processor is not enabled, please enable it through OSE backend.");
			break;
			case '0004':
			$return['content']= JText :: _("Your order is activated, but the automatic billing has not been created. The error reported from our payment gatePay is: <br />");
			$return['content'].=$message;
			break;

		}
		return $return;
	}

	private function parseResponse($content, $rebill = false) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		if ($rebill==false)
		{
		$resArray['ePayTrxnNumber']= OSECONNECTOR :: substring_between($content, '<ePayTrxnNumber>', '</ePayTrxnNumber>');
		$resArray['ePayTrxnStatus']= OSECONNECTOR :: substring_between($content, '<ePayTrxnStatus>', '</ePayTrxnStatus>');
		$resArray['ePayTrxnReference']= OSECONNECTOR :: substring_between($content, '<ePayTrxnReference>', '</ePayTrxnReference>');
		$resArray['ePayAuthCode']= OSECONNECTOR :: substring_between($content, '<ePayAuthCode>', '</ePayAuthCode>');
		$resArray['ePayReturnAmount']= OSECONNECTOR :: substring_between($content, '<ePayReturnAmount>', '</ePayReturnAmount>');
		$resArray['ePayTrxnError']= OSECONNECTOR :: substring_between($content, '<ePayTrxnError>', '</ePayTrxnError>');
		}
		else
		{
		$resArray['Result']= OSECONNECTOR :: substring_between($content, '<Result>', '</Result>');
		$resArray['ErrorSeverity']= OSECONNECTOR :: substring_between($content, '<ErrorSeverity>', '</ErrorSeverity>');
		$resArray['ErrorDetails']= OSECONNECTOR :: substring_between($content, '<ErrorDetails>', '</ErrorDetails>');
		}
		return $resArray;
	}
	
	function ePayCreateProfile($isSub = 1,$params)
	{
		$merchantnumber = $params['merchantnumber'];
		$amount = $params['amount'];
		$currency = $this->get_iso_code($params['currency']);
		$addFee = 0;
		$orderId = $params['order_number'];
		$md5 = $params['md5'];
		$acceptURL = $params['accept_url'];
		$MD5key = md5( $currency . $amount . $orderId . $md5 );
		
		$form = new ePayForm;
		
		$link = OSEMSC_F_URL;//.'/index.php?option=com_osemsc';
		
		$hiddenMerchantnumber = $form->hidden('merchantnumber',$merchantnumber);
		$hiddenAmount = $form->hidden('amount',$amount*100);
		$hiddenCurrency = $form->hidden('currency',$currency);
		$hiddenAddFee = $form->hidden('addfee',$addFee);
		$hiddenOrderId = $form->hidden('orderid',$orderId);
		$hiddenSubscription = $form->hidden('subscription',$isSub);
		$hiddenAcceptURL = $form->hidden('accepturl',$acceptURL);
		$hiddenDeclineURL = $form->hidden('declineurl',JRoute::_(JURI::root().'index.php?option=com_osemsc&view=confirm'));
		$hiddenCallbackURL = $form->hidden('callbackurl',$link.'/ipn/epay_notify.php');
		$hiddenMD5key = $form->hidden('MD5key',$MD5key);
		$hiddenState = $form->hidden('windowstate','2');
		$hiddenLanguage = $form->hidden('language','2');
		$hiddenInstantCallback = $form->hidden('instantcallback','1');
		$hiddenInstantCapture = $form->hidden('instantcapture',$params['instantcapture']);
		$submit = $form->submit('epay_button','submit','epay_button');
		
		$form->append('<script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script> ');
		$form->createForm('https://ssl.ditonlinebetalingssystem.dk/popup/default.asp',null,null,'POST','ePay_window');
		$form->addLevel();
		$form->sc('et');
		$form->append($hiddenState);
		$form->sc('et');
		$form->append($hiddenMerchantnumber);
		$form->sc('et');
		$form->append($hiddenAmount);
		$form->sc('et');
		$form->append($hiddenCurrency);
		$form->sc('et');
		$form->append($hiddenAddFee);
		$form->sc('et');
		$form->append($hiddenOrderId);
		$form->sc('et');
		$form->append($hiddenSubscription);
		$form->sc('et');
		$form->append($hiddenAcceptURL);
		$form->sc('et');
		$form->append($hiddenDeclineURL);
		$form->sc('et');
		$form->append($hiddenCallbackURL);
		$form->sc('et');
		$form->append($hiddenMD5key);
		$form->sc('et');
		$form->append($hiddenLanguage);
		$form->sc('et');
		$form->append($hiddenInstantCallback);
		$form->sc('et');
		$form->append($hiddenInstantCapture);
		$form->sc('et');
		$form->append($submit);
		$form->sc('et');
		$form->subLevel();
		$form->sc('et');
		$form->endForm();
		
		$html = $form->output();
		
		return $html;
	}
	
	function ePayOneOffPay($orderInfo, $credit_info, $params= array(), $TransactionType='AUTH_CAPTURE', $trialPayment=false)
	{
		ini_set('max_execution_time','180');
		$config= oseMscConfig :: getConfig('', 'obj');
		$cc_methods= explode(',', $config->cc_methods);
		
		if(!in_array('ePay', $cc_methods) || $config->enable_cc == false) {
			//return self::getErrorMessage('cc', '0003', null);
		}
		
		$pConfig = $config;//oseRegistry::call('msc')->getConfig('payment', 'obj');

		if(!isset($pConfig->ePay_testmode) || $pConfig->ePay_testmode == true) {
			$test_mode= true;
			$ePayCustomerID = '87654321';
			$ePayUsername = null;
		} else {
			$test_mode= false;
			$ePayCustomerID= $pConfig->ePayCustomerID;
			$ePayUsername= $pConfig->ePayUsername;
			
			if(empty($ePayCustomerID)) {
				return self::getErrorMessage('cc', '0001', null);
			}
		}
		
		
		if(empty($orderInfo->payment_price)) {
			return self::getErrorMessage('cc', '0002', null);
		}
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');

		$result= array();
		$db= oseDB :: instance();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$desc = parent::generateDesc($order_id);
		$billingInfo= parent::getBillingInfo($orderInfo->user_id);
		$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$currency= $orderInfo->payment_currency;
		$user= & JFactory :: getUser($orderInfo->user_id);
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$Itemid= parent :: getItemid();

		$amount= $orderInfo->payment_price;

		$postVar= array();
		/* $totalAmount in cents, as required by ePay:
		The total amount in cents for the transaction, eg $1.00 = 100
		*/
		$postVar['ePayTotalAmount']= intval(($taxRate / 100 * $amount+ $amount)*100);
		$postVar['ePayCustomerFirstName']= substr($billingInfo->firstname, 0, 50);
		$postVar['ePayCustomerLastName']= substr($billingInfo->lastname, 0, 50);
		$postVar['ePayCustomerEmail']= $billingInfo->email;
		$postVar['ePayCustomerAddress']= substr($billingInfo->addr1, 0, 60);
		$postVar['ePayCustomerPostcode']= substr(str_replace(" ", "", $billingInfo->postcode), 0, 6);

		$creditCardExpiryDate= $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate= explode("-", strval($creditCardExpiryDate));
		$postVar['ePayCardHoldersName']= $credit_info["creditcard_name"];
		$postVar['ePayCardNumber']= $credit_info["creditcard_number"];
		$postVar['ePayCardExpiryMonth']= $creditCardExpiryDate[1];
		$postVar['ePayCardExpiryYear']= $creditCardExpiryDate[0];
		$postVar['ePayCVN']= $credit_info["creditcard_cvv"];

		$postVar['ePayCustomerInvoiceDescription']= $desc;
		$postVar['ePayCustomerInvoiceRef']= $order_number;
		$postVar['ePayTrxnNumber']= '';
		$postVar['ePayOption1']= '';
		$postVar['ePayOption2']= '';
		$postVar['ePayOption3']= '';
		
		
		
		$resArray= self :: ePayAPIConnect($ePayCustomerID,$ePayUsername,$test_mode,$postVar, false);
		if($resArray['ePayTrxnStatus']==true) {
			if ($TransactionType=='AUTH_CAPTURE')
			{
				if ($trialPayment==false)
				{
					$params['payment_serial_number'] = $resArray['ePayTrxnNumber'];
					$return = parent:: confirmOrder($order_id, $params, 0, $user->id, 'ePay');
				}
				else
				{
					$return = $resArray;
				}
			}
			elseif ($TransactionType=='AUTH_ONLY')
			{
				$return = $resArray;
			}
			return $return;
		} else {
			return self::getErrorMessage('cc', '0000', $resArray['ePayTrxnError']);
		}
	}

	private function ePayAPIConnect($ePayCustomerID,$ePayUsername = null,$test_mode = true,$postVar, $rebill = false) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		//$pConfig= oseMscConfig :: getConfig('payment', 'obj');

		//if(!isset($pConfig->ePay_testmode) || $pConfig->ePay_testmode == true) {
		//	$test_mode= true;
		//} else {
		//	$test_mode= false;
		//}
		//$ePayCustomerID= $pConfig->ePayCustomerID;
		//$ePayUsername= $pConfig->ePayUsername;
		// Double check, not quite necessary, just in case;
		//if($test_mode == false && empty($ePayCustomerID)) {
		//	return false;
		//}
		
		$API_Endpoint= 'www.ePay.com.au';
		if($test_mode == true) {
			//$ePayCustomerID = '87654321';
			$API_Path = '/gatePay_cvn/xmltest/testpage.asp';
		}
		else
		{
			//$ePayCustomerID= $pConfig->ePayCustomerID;
			//$ePayUsername= $pConfig->ePayUsername;
			$API_Path = '/gatePay_cvn/xmlpayment.asp';
		}
		$xmlRequest= "<ePaygatePay><ePayCustomerID>".$ePayCustomerID."</ePayCustomerID>";
		foreach($postVar as $key => $value)
		{
			$xmlRequest .= "<$key>$value</$key>";
		}
		$xmlRequest .= "</ePaygatePay>";
		$response= OSECONNECTOR :: send_request_via_fsockopen($API_Endpoint, $API_Path, $xmlRequest);
		$resArray= self :: parseResponse($response, $rebill);
		return $resArray;
	}

	private function ePayRebillAPIConnect($ePayCustomerID,$ePayUsername,$ePayPassword = null ,$test_mode = true,$postVar, $rebill=true) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		//$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		//if(!isset($pConfig->cc_testmode) || $pConfig->cc_testmode == true) {
		//	$test_mode= true;
		//} else {
		//	$test_mode= false;
		//}
		//$ePayCustomerID= $pConfig->ePayCustomerID;
		//$ePayUsername= $pConfig->ePayUsername;
		//$ePayPassword= $pConfig->ePayPassword;
		// Double check, not quite necessary, just in case;
		//if($test_mode == false && empty($ePayCustomerID) || empty($ePayUsername)) {
		//	return false;
		//}
		$API_Endpoint= 'www.ePay.com.au';
		if($test_mode == true) {
			//$ePayCustomerID = '87654321';
			//$ePayUsername = 'test@ePay.com.au';
			//$ePayPassword = 'test';
			$API_Path = '/gatePay/rebill/test/Upload_test.aspx';
		}
		else
		{
			$API_Path = '/gatePay/rebill/upload.aspx';
		}
		$xmlRequest= "<RebillUpload>\n" .
						"<NewRebill>\n" .
						"<ePayCustomerID>".$ePayCustomerID."</ePayCustomerID>\n" .
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


	function TranslateInterval($t, $p) {
			$results= array();
			if (empty($t)||empty($p))
			{
				return $results;
			}
			$t= strtolower($t);
			switch($t) {
				case "year" :
					$results['length']= $p * 12;
					$results['unit']= 'months';
					$results['unit2']= 'month';
					$results['IntervalType']= '4';
					break;
				case "month" :
					$results['length']= $p;
					$results['unit']= 'months';
					$results['unit2']= 'month';
					$results['IntervalType']= '3';
					break;
				case "week" :
					$results['length']= $p;
					$results['unit']= 'weeks';
					$results['unit2']= 'week';
					$results['IntervalType']= '2';
					break;
				case "day" :
					$results['length']= $p;
					$results['unit']= 'days';
					$results['unit2']= 'day';
					$results['IntervalType']= '1';
					break;
			}
			return $results;
		}
	function ePayCreateProfile1($orderInfo, $credit_info, $params= array())
	{
		ini_set('max_execution_time','180');
		// Now proceed the Recurring payment plan creation;
		// Load Basic Information;
		$result= array();
		$db= oseDB :: instance();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$desc = parent::generateDesc($order_id);
		$billingInfo= parent::getBillingInfo($orderInfo->user_id);
		$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$currency= $orderInfo->payment_currency;
		$user= & JFactory :: getUser($orderInfo->user_id);
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$Itemid= parent :: getItemid();
		
		$config= oseMscConfig :: getConfig('', 'obj');
		
		$cc_methods= explode(',', $config->cc_methods);
		if(!in_array('ePay', $cc_methods) || $config->enable_cc == false) {
			return self::getErrorMessage('cc', '0003', null);
		}
		
		$pConfig = $config;//oseRegistry::call('msc')->getConfig('payment', 'obj');

		if(!isset($pConfig->ePay_testmode) || $pConfig->ePay_testmode == true) {
			$test_mode= true;
			$ePayCustomerID = '87654321';
			$ePayUsername = 'test@ePay.com.au';
			$ePayPassword = 'test';
		} else {
			$test_mode= false;
			$ePayCustomerID= $pConfig->ePayCustomerID;
			$ePayUsername= $pConfig->ePayUsername;
			$ePayPassword= $pConfig->ePayPassword;
			
			if(empty($ePayCustomerID) || empty($ePayUsername)) {
				return self::getErrorMessage('cc', '0001', null);
			}
		}

		// Assign Values;
		$refID= substr($order_number, 0, 19)."A";
		$invoice= substr($order_number, 0, 19)."A";
		$name= "MEM{$msc_id}UID{$user_id}_".date("Ymdhis");

		// Credit Card Informaiton;
		$creditcard= $credit_info["creditcard_number"];
		$cardCode= $credit_info["creditcard_cvv"];
		$expiration= $credit_info["creditcard_expirationdate"];
		$expiration= strval($expiration);

		// Recurring payment setting;
		$msc= oseRegistry :: call('msc');

		
		//oseExit($mscTrialRecurrence);
		$initStartDate = date("d/m/Y", strtotime("+ 1 day"));
		$initAmount= $orderInfo->payment_price;
		$recurAmount = $orderInfoParams->next_total;
		$totalOccurrences= 9999;
		/// Finished getting all necessary Information;///

		/// Start Creating Subscription Plans ;
		// Check if Price is set correctly;
		if(empty($recurAmount)) {
				return self::getErrorMessage('cc', '0002');
		}

 		// First time charge for those does not support initial payments;
 		// SUITABLE HERE - ePay;
 		$result = self::ePayOneOffPay($orderInfo, $credit_info, $params, $TransactionType='AUTH_CAPTURE', true);
		if ($result['ePayTrxnStatus']==false)
		{
			return self::getErrorMessage('cc', '0000', $result['ePayTrxnError']);
		}
		
		if (!empty($orderInfoParams->has_trial))
		{
			$trialOccurrences =  "1";
			$mscTrialRecurrence = self::TranslateInterval($orderInfoParams->t1, $orderInfoParams->p1);
			$recurStartDate = date("d/m/Y", strtotime("+ {$mscTrialRecurrence['length']} {$mscTrialRecurrence['unit']}"));
			$mscRegRecurrence= self::TranslateInterval($orderInfoParams->t3, $orderInfoParams->p3);
		}
		else
		{
			$trialOccurrences =  "0";
			$mscRegRecurrence= self::TranslateInterval($orderInfoParams->t3, $orderInfoParams->p3);
			$recurStartDate = date("d/m/Y", strtotime("+ {$mscRegRecurrence['length']} {$mscRegRecurrence['unit']}"));
		}

		// Start creating profiles;
		$postVar= array();
		$postVar['CustomerRef'] = $user->id;
		//$postVar['ePayTotalAmount']= $taxRate / 100 * $recurAmount+ $recurAmount;
		//oseExit($billingInfo);
		if (!empty($billingInfo->firstname))
		{
			$postVar['CustomerFirstName']= substr($billingInfo->firstname, 0, 50);
			$postVar['CustomerLastName']= substr($billingInfo->lastname, 0, 50);
		}
		else
		{
			$customerName = explode(' ', $credit_info["creditcard_name"]);
			$postVar['CustomerFirstName']= substr($customerName[0], 0, 50);
			$postVar['CustomerLastName']= substr($customerName[count($customerName)-1], 0, 50);
		}
		$postVar['CustomerEmail']= $billingInfo->email;
		$postVar['CustomerAddress']= substr($billingInfo->addr1, 0, 50);
		$postVar['CustomerPostcode']= substr(str_replace(" ", "", $billingInfo->postcode), 0, 6);
		$postVar['CustomerState']= substr($billingInfo->state, 0, 50);
		$postVar['CustomerCountry']= substr($billingInfo->country, 0, 20);

		$creditCardExpiryDate= $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate= explode("-", strval($creditCardExpiryDate));
		$postVar['RebillCCName']= substr($credit_info["creditcard_name"], 0,50);
		$postVar['RebillCCNumber']= substr($credit_info["creditcard_number"], 0,19);
		$postVar['RebillCCExpMonth']= substr($creditCardExpiryDate[1], 0,2);
		$postVar['RebillCCExpYear']= substr($creditCardExpiryDate[0], 0,4);

		$postVar['RebillInvRef']= $refID;
		$postVar['RebillInvDesc']= '';
		$postVar['RebillInitAmt']= 0;
		$postVar['RebillInitDate']= $initStartDate;
		$postVar['RebillRecurAmt']= intval(($taxRate / 100 * $recurAmount+ $recurAmount)*100);
		$postVar['RebillStartDate']= $recurStartDate;
		$postVar['RebillInterval']= $mscRegRecurrence['length'];;
		$postVar['RebillIntervalType']= $mscRegRecurrence['IntervalType'];
		$postVar['RebillEndDate']= date("d/m/Y", strtotime("+ 3 years"));

		$resArray= self :: ePayRebillAPIConnect($ePayCustomerID,$ePayUsername,$ePayPassword,$test_mode,$postVar, true);
		if($resArray['Result']=='Success') {
			$params['payment_serial_number'] = $refID;
			$params['payment_method'] = 'ePay';
			$return = self:: confirmOrder($order_id, $params);
			return $return;
		}
		else {
			$params['payment_serial_number'] = $refID;
			$params['payment_mode'] = 'm';
			$params['payment_method'] = 'ePay';
			//$return = self:: confirmOrder($order_id, $params );
			return self::getErrorMessage('cc', '0004', $resArray['ErrorDetails']);
		}
	}

	function ePayDeleteProfile($payment_serial_number, $refID, $user_id, $msc_id = 0) {
		// To do; Currently no solution; It requires using SOAP to cancel it;
	}
	
	function ePayTransInterval($t, $p) {
		$results= array();
		$t= strtolower($t);
		switch($t) {
			case "year" :
				$results['length']= $p * 12;
				$results['unit']= 'months';
				$results['unit2']= 'month';
				$results['IntervalType']= 'month';
				break;
			case "month" :
				$results['length']= $p;
				$results['unit']= 'months';
				$results['unit2']= 'month';
				$results['IntervalType']= 'month';
				break;
			case "week" :
				$results['length']= $p * 7;
				$results['unit']= 'weeks';
				$results['unit2']= 'week';
				$results['IntervalType']= 'day';
				break;
			case "day" :
				if($p) {
					$results['length']= $p;
					$results['unit']= 'days';
					$results['unit2']= 'day';
					$results['IntervalType']= 'day';
				}
				break;
		}
		return $results;
	}

	function randStr($length= 32, $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length=(strlen($chars) - 1);
		// Start our string
		$string= $chars {
			rand(0, $chars_length)
			};
		// Generate random string
		for($i= 1; $i < $length; $i= strlen($string)) {
			// Grab a random character from our list
			$r= $chars {
				rand(0, $chars_length)
				};
			// Make sure the same two characters don't appear next to each other
			if($r != $string {
				$i -1 })
			$string .= $r;
		}
		// Return the string
		return $string;
	}
	function updateOrder($order_id, $status, $params= array()) {
		$db= oseDB :: instance();
		$params['order_status']= $status;
		if(isset($params['params'])) {
			$params['params']= $this->autoOrderParams($params['payment_mode'], $params['params'], false, $status);
		} else {
			$orderInfo= $this->getOrder(array('order_id' => $order_id));
			$params['params']= $this->autoOrderParams($orderInfo['payment_mode'], $orderInfo['params'], false, $status);
		}
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= '`'.$key.'`='.$db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " UPDATE `{$this->table}` "." SET {$values}"." WHERE order_id = {$order_id}";
		$db->setQuery($query);
		if(oseDB :: query()) {
			return true;
		} else {
			return false;
		}
	}
	function updateMembership($msc_id, $user_id, $order_id, $payment_mode)
	{
		$db= oseDB :: instance();
		$params['order_id'] = $order_id;
		$params['payment_mode'] = $payment_mode;
		$params = oseJSON::encode($params);
		$query= " UPDATE `#__osemsc_member` SET `params`='$params' WHERE `msc_id` = '{$msc_id}' AND `member_id` = '$user_id'";
		$db->setQuery($query);
		if(oseDB :: query()) {
			return true;
		} else {
			return false;
		}
	}
	
	 function get_iso_code($code) {
      switch ($code) {
      	case 'ADP': return '020'; break;
				case 'AED': return '784'; break;
				case 'AFA': return '004'; break;
				case 'ALL': return '008'; break;
				case 'AMD': return '051'; break;
				case 'ANG': return '532'; break;
				case 'AOA': return '973'; break;
				case 'ARS': return '032'; break;
				case 'AUD': return '036'; break;
				case 'AWG': return '533'; break;
				case 'AZM': return '031'; break;
				case 'BAM': return '977'; break;
				case 'BBD': return '052'; break;
				case 'BDT': return '050'; break;
				case 'BGL': return '100'; break;
				case 'BGN': return '975'; break;
				case 'BHD': return '048'; break;
				case 'BIF': return '108'; break;
				case 'BMD': return '060'; break;
				case 'BND': return '096'; break;
				case 'BOB': return '068'; break;
				case 'BOV': return '984'; break;
				case 'BRL': return '986'; break;
				case 'BSD': return '044'; break;
				case 'BTN': return '064'; break;
				case 'BWP': return '072'; break;
				case 'BYR': return '974'; break;
				case 'BZD': return '084'; break;
				case 'CAD': return '124'; break;
				case 'CDF': return '976'; break;
				case 'CHF': return '756'; break;
				case 'CLF': return '990'; break;
				case 'CLP': return '152'; break;
				case 'CNY': return '156'; break;
				case 'COP': return '170'; break;
				case 'CRC': return '188'; break;
				case 'CUP': return '192'; break;
				case 'CVE': return '132'; break;
				case 'CYP': return '196'; break;
				case 'CZK': return '203'; break;
				case 'DJF': return '262'; break;
				case 'DKK': return '208'; break;
				case 'DOP': return '214'; break;
				case 'DZD': return '012'; break;
				case 'ECS': return '218'; break;
				case 'ECV': return '983'; break;
				case 'EEK': return '233'; break;
				case 'EGP': return '818'; break;
				case 'ERN': return '232'; break;
				case 'ETB': return '230'; break;
				case 'EUR': return '978'; break;
				case 'FJD': return '242'; break;
				case 'FKP': return '238'; break;
				case 'GBP': return '826'; break;
				case 'GEL': return '981'; break;
				case 'GHC': return '288'; break;
				case 'GIP': return '292'; break;
				case 'GMD': return '270'; break;
				case 'GNF': return '324'; break;
				case 'GTQ': return '320'; break;
				case 'GWP': return '624'; break;
				case 'GYD': return '328'; break;
				case 'HKD': return '344'; break;
				case 'HNL': return '340'; break;
				case 'HRK': return '191'; break;
				case 'HTG': return '332'; break;
				case 'HUF': return '348'; break;
				case 'IDR': return '360'; break;
				case 'ILS': return '376'; break;
				case 'INR': return '356'; break;
				case 'IQD': return '368'; break;
				case 'IRR': return '364'; break;
				case 'ISK': return '352'; break;
				case 'JMD': return '388'; break;
				case 'JOD': return '400'; break;
				case 'JPY': return '392'; break;
				case 'KES': return '404'; break;
				case 'KGS': return '417'; break;
				case 'KHR': return '116'; break;
				case 'KMF': return '174'; break;
				case 'KPW': return '408'; break;
				case 'KRW': return '410'; break;
				case 'KWD': return '414'; break;
				case 'KYD': return '136'; break;
				case 'KZT': return '398'; break;
				case 'LAK': return '418'; break;
				case 'LBP': return '422'; break;
				case 'LKR': return '144'; break;
				case 'LRD': return '430'; break;
				case 'LSL': return '426'; break;
				case 'LTL': return '440'; break;
				case 'LVL': return '428'; break;
				case 'LYD': return '434'; break;
				case 'MAD': return '504'; break;
				case 'MDL': return '498'; break;
				case 'MGF': return '450'; break;
				case 'MKD': return '807'; break;
				case 'MMK': return '104'; break;
				case 'MNT': return '496'; break;
				case 'MOP': return '446'; break;
				case 'MRO': return '478'; break;
				case 'MTL': return '470'; break;
				case 'MUR': return '480'; break;
				case 'MVR': return '462'; break;
				case 'MWK': return '454'; break;
				case 'MXN': return '484'; break;
				case 'MXV': return '979'; break;
				case 'MYR': return '458'; break;
				case 'MZM': return '508'; break;
				case 'NAD': return '516'; break;
				case 'NGN': return '566'; break;
				case 'NIO': return '558'; break;
				case 'NOK': return '578'; break;
				case 'NPR': return '524'; break;
				case 'NZD': return '554'; break;
				case 'OMR': return '512'; break;
				case 'PAB': return '590'; break;
				case 'PEN': return '604'; break;
				case 'PGK': return '598'; break;
				case 'PHP': return '608'; break;
				case 'PKR': return '586'; break;
				case 'PLN': return '985'; break;
				case 'PYG': return '600'; break;
				case 'QAR': return '634'; break;
				case 'ROL': return '642'; break;
				case 'RUB': return '643'; break;
				case 'RUR': return '810'; break;
				case 'RWF': return '646'; break;
				case 'SAR': return '682'; break;
				case 'SBD': return '090'; break;
				case 'SCR': return '690'; break;
				case 'SDD': return '736'; break;
				case 'SEK': return '752'; break;
				case 'SGD': return '702'; break;
				case 'SHP': return '654'; break;
				case 'SIT': return '705'; break;
				case 'SKK': return '703'; break;
				case 'SLL': return '694'; break;
				case 'SOS': return '706'; break;
				case 'SRG': return '740'; break;
				case 'STD': return '678'; break;
				case 'SVC': return '222'; break;
				case 'SYP': return '760'; break;
				case 'SZL': return '748'; break;
				case 'THB': return '764'; break;
				case 'TJS': return '972'; break;
				case 'TMM': return '795'; break;
				case 'TND': return '788'; break;
				case 'TOP': return '776'; break;
				case 'TPE': return '626'; break;
				case 'TRL': return '792'; break;
				case 'TRY': return '949'; break;
				case 'TTD': return '780'; break;
				case 'TWD': return '901'; break;
				case 'TZS': return '834'; break;
				case 'UAH': return '980'; break;
				case 'UGX': return '800'; break;
				case 'USD': return '840'; break;
				case 'UYU': return '858'; break;
				case 'UZS': return '860'; break;
				case 'VEB': return '862'; break;
				case 'VND': return '704'; break;
				case 'VUV': return '548'; break;
				case 'XAF': return '950'; break;
				case 'XCD': return '951'; break;
				case 'XOF': return '952'; break;
				case 'XPF': return '953'; break;
				case 'YER': return '886'; break;
				case 'YUM': return '891'; break;
				case 'ZAR': return '710'; break;
				case 'ZMK': return '894'; break;
				case 'ZWD': return '716'; break;
      }
      //
      // As default return 208 for Danish Kroner
      //
      return '208';
    }
}

class ePayForm
{
	protected $html = null;
	protected $level = 0;
	
	function append($html)
	{
		$this->html .= $html;
	}
	
	function addBreak()
	{
		$this->append("\r\n");
	}
	
	function addLevel()
	{
		$this->level += 1; 
	}
	
	function subLevel()
	{
		$this->level -= 1; 
		
		if($this->level < 0)
		{
			$this->level = 0;
		}
	}
	
	function setLevel($level)
	{
		$this->level = $level; 
	}
	
	function addTab($num = 1)
	{
		$string = str_repeat("\t",$num);
		$this->append("\t");
	}
	
	// shortcut
	function sc($event)
	{
		switch($event)
		{
			case('e'):
				$this->addBreak();
			break;
			
			case('t'):	
				$this->addTab($this->level);
			break;
			
			case('et'):	
				$this->sc('e');
				$this->sc('t');
			break;
		}
	}
	
	function createForm($action,$id=null, $name = null,$method = 'POST',$target = null)
	{
		$html = '<form';
		
		if(!empty($name))
		{
			$html .= ' name="'.$name.'"';
		}
		
		if(!empty($id))
		{
			$html .= ' id="'.$id.'"';
		}
		
		if(!empty($id))
		{
			$html .= ' target="'.$target.'"';
		}
		
		$html .= ' method="'.$method.'"';
		$html .= ' action="'.$action.'"';
		$html .= '>';
		
		$this->append($html);
	}
	
	function endForm()
	{
		$this->append('</form>');
	}
	
	function textfield($name,$value = null,$id = null,$class = null)
	{
		
	}
	
	function hidden($name,$value = null,$id = null,$class = null)
	{
		$html = '<input type="hidden"';
		$html .= ' name="'.$name.'"';
		$html .= ' value="'.$value.'"';
		$html .= '>';
		
		return $html;
	}
	
	function submit($name,$value = null,$id = null,$class = null)
	{
		$html = '<button type="submit"';
		$html .= ' name="'.$name.'"';
		
		if(!empty($id))
		{
			$html .= ' id="'.$id.'"';
		}
		
		$html .= '>';
		$html .= $value;
		$html .= '</button>';
		return $html;
	}
	
	function output()
	{
		return $this->html;
	}
	
	
}
?>