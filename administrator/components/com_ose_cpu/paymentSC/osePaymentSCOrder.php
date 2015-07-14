<?php
defined('_JEXEC') or die(";)");
class osePaymentSCOrder extends osePaymentOrder
{
	protected $table= '#__osemsc_order';
	function __construct($table= null)
	{
		if(!empty($table))
		{
			$this->table= $table;
		}
	}

	private function joinMsc($order_id, $order_item_id, $msc_id, $user_id)
	{
		$member= oseRegistry :: call('member');
		$member->instance($user_id);

		$params= $member->getAddonParams($msc_id, $user_id, $order_id,array('order_item_id'=>$order_item_id));

		// is Member, renew... else join.
		$memMscInfo= $member->getMembership($msc_id,'obj');

		if(empty($memMscInfo))
		{
			//oseExit('haha');
			$msc= oseRegistry :: call('msc');
			$updated= $msc->runAddonAction('member.msc.joinMsc', $params,true,false);
		}
		else
		{
			// renew
			if($memMscInfo->status)
			{
				//oseExit('renew');
				//$memInfo= oseRegistry :: call('member')->getMemberInfo($msc_id, 'obj');
				$memParams= oseJson :: decode($memMscInfo->params);

				if(!empty($memParams->order_id))
				{
					oseRegistry :: call('payment')->updateOrder($memParams->order_id, 'expired');
				}

				$msc= oseRegistry :: call('msc');
				$updated= $msc->runAddonAction('member.msc.renewMsc', $params,true,false);

			}
			else
			{
				//oseExit('activate');
				$msc= oseRegistry :: call('msc');
				$updated= $msc->runAddonAction('member.msc.activateMsc', $params,true,false);
			}
		}

		// Set Redirect Link
		$mscInfo = oseRegistry::call('msc')->getInfo($msc_id,'obj');
		$mscInfoParams = oseJson::decode($mscInfo->params);
/*
		$session = JFactory::getSession();
		$db = oseDB::instance();
		if(empty($mscInfoParams->after_payment_menuid))
		{
			$query = " SELECT * FROM `#__menu`"
					." WHERE `link` LIKE 'index.php?option=com_osemsc&view=member'"
					;
			$db->setQuery($query);
			$item = oseDB::loadItem('obj');

			if(empty($item))
			{
				$link = "index.php?option=com_osemsc&view=member";
			}
			else
			{
				$link = "index.php?option=com_osemsc&view=member&Itemid={$item->id}";
			}
			$session->set('oseReturnUrl',$link);
		}
		else
		{
			$query = " SELECT * FROM `#__menu`"
					." WHERE id = '{$mscInfoParams->after_payment_menuid}'"
					;
			$db->setQuery($query);
			$item = oseDB::loadItem('obj');
			$link = $item->link."&Itemid={$item->id}";
			$session->set('oseReturnUrl',$item->link."&Itemid={$item->id}");
		}

		$updated ['return_url'] = $link;
*/
		return $updated;
	}

	function generateOrder($msc_id, $user_id, $params= array())
	{
		$db = oseDB :: instance();
		//$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		//$params['payment_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$params['create_date']=(empty($params['create_date'])) ? oseHTML :: getDateTime() : $params['create_date'];
		$keys= array_keys($params);
		$keys= '`'.implode('`,`', $keys).'`';
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= $db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " INSERT INTO `{$this->table}` "." (`user_id`,`entry_id`,{$keys}) "." VALUES "." ( '{$user_id}', '{$msc_id}', {$values})";
		$db->setQuery($query);

		if(oseDB :: query()) {
			$order_id= $db->insertid();
			$orderParams= $this->autoOrderParams($params['payment_mode'], $params['params']);

			$this->updateOrder($order_id, 'pending', array('params' => $orderParams, 'payment_mode' => $params['payment_mode']));
			return $order_id;
		} else {
			return false;
		}
	}

	function generateOrderItem($order_id,$entry_id, $params= array()) {
		$db = oseDB :: instance();
		//$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		//$params['payment_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$params['create_date']=(empty($params['create_date'])) ? oseHTML :: getDateTime() : $params['create_date'];
		$keys= array_keys($params);
		$keys= '`'.implode('`,`', $keys).'`';
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= $db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " INSERT INTO `{$this->table}_item` "." (`order_id`,`entry_id`,{$keys}) "." VALUES "." ('{$order_id}' , '{$entry_id}', {$values})";
		$db->setQuery($query);

		if(oseDB :: query()) {
			$order_id= $db->insertid();
			//$orderParams= $this->autoOrderParams($params['payment_mode'], $params['params']);
			//$this->updateOrder($order_id, 'pending', array('params' => $orderParams, 'payment_mode' => $params['payment_mode']));
			return $order_id;
		} else {
			return false;
		}
	}


	function generateOrderNumber($user_id) {
		$order_number= $user_id."_".self :: randStr(28, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890");
		return $order_number;
	}

	function generateOrderParams($msc_id, $price, $payment_mode, $msc_option) {
		$params= array();
		$params['msc_id']= $msc_id;
		if($payment_mode == 'a') {
			$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'array');
			$payment=$payment[$msc_option];
			if(oseObject::getValue($payment,'has_trial')) {
				$params['has_trial']= 1;
				$params['a1']= $price;
				$params['p1']= oseObject::getValue($payment,'p1');
				$params['t1']= oseObject::getValue($payment,'t1');
				$params['a3']= oseObject::getValue($payment,'a3');
				$params['p3']= oseObject::getValue($payment,'p3');
				$params['t3']= oseObject::getValue($payment,'t3');
			} else {
				$params['has_trial']= 0;
				$params['a3']= $price;
				$params['p3']= oseObject::getValue($payment,'p3');
				$params['t3']= oseObject::getValue($payment,'t3');
			}
		}

		if($payment_mode == 'm') {
			$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'array');
			$payment=$payment[$msc_option];

			$params['recurrence_mode'] = 'period';
			$params['a3']= $price;
			$params['p3']= oseObject::getValue($payment,'p3');
			$params['t3']= oseObject::getValue($payment,'t3');
			$params['eternal']= oseObject::getValue($payment,'eternal');
		}

		$params['msc_option'] = $msc_option;
		return $params;
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
			$return['content']= JText :: _("This transaction utilizes Authorize.net as the payment processor, which does not support non-USD currency. Please choose USD as your payment currency.");
			break;
			case '0002':
			$return['content']= JText :: _("Please check your membership setting. Membership Price cannot be empty.");
			break;
			case '0003':
			$return['content']= JText :: _("Authorize.net is not enabled, please enable it through OSE backend.");
			break;

		}
		return $return;
	}
	function getItemid() {
		$db= oseDB :: instance();
		$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=confirm%'";
		$db->setQuery($query);
		$Itemid= $db->loadResult();
		if(empty($Itemid)) {
			$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=register%'";
			$db->setQuery($query);
			$Itemid= $db->loadResult();
		}
		return $Itemid;
	}
	function getProfileID($order_number)
	{
		$db= oseDB :: instance();
		$query = "SELECT `payment_serial_number` FROM `#__osemsc_order` WHERE `order_number`= '{$order_number}'";
		$db->setQuery($query);
		$ProfileID = $db->loadResult();
		return $ProfileID;
	}
	function getOrder($where= array(), $type= 'array') {
		$db= oseDB :: instance();
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT * FROM `{$this->table}` ".$where.' ORDER BY create_date DESC'.' LIMIT 1';
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}

	function PaypalAPICCPay($orderInfo, $credit_info, $params= array()) {
		$db= oseDB :: instance();
		$result= array();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= $member->getBillingInfo('obj');

		//$node= oseMscTree :: getNode($orderInfo->entry_id, 'obj');

		$desc = self::generateDesc($order_id);

		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');
		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		*/

		$taxRate= 0;//$payment->tax_rate;
		$msc_name= $desc;//$node->title;
		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$user= & JFactory :: getUser($orderInfo->user_id);
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$Itemid= self :: getItemid();
		$postVar= array();
		if($orderInfo->payment_mode == 'm') {
			$postVar['ADDRESSOVERRIDE']= 0;
			$postVar['PAYMENTACTION']= 'Sale';
			$postVar['CURRENCYCODE']= $currency;
			$postVar['TAXAMT']= $taxRate / 100 * $amount;
			$postVar['ITEMAMT']= $amount;
			$postVar['AMT']= $amount + $postVar['TAXAMT'];
			/*
			$postVar['RETURNURL']= JURI :: root()."index.php?option=com_osemsc&view=confirm&mode=m&Itemid=".$Itemid;
			$postVar['CANCELURL']= JURI :: root()."index.php";
			*/
			$postString= 'METHOD='.urlencode('doDirectPayment');
		} else {

			$orderInfoParams= oseJson :: decode($orderInfo->params);

			//jimport('joomla.utilities.date');
			$curDate= oseHTML::getDateTime();
			if($orderInfoParams->has_trial) {
				$a1= $orderInfoParams->total;
				$p1= $orderInfoParams->p1;
				$t1= $orderInfoParams->t1;
				$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$postVar['TRIALBILLINGPERIOD']= $t1;
				$postVar['TRIALBILLINGFREQUENCY']= $p1;
				$postVar['TRIALTOTALBILLINGCYCLES']= 1;
				$postVar['TRIALAMT']= $a1;
			}
			$a3= $orderInfoParams->next_total;
			$p3= $orderInfoParams->p3;
			$t3= $orderInfoParams->t3;
			$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			$postVar['L_BILLINGTYPE0']= 'RecurringPayments';
			$postVar['L_BILLINGAGREEMENTDESCRIPTION0']= JText :: _('Order ID:')." ".$order_id;
			$postVar['DESC']= JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name;
			$postVar['BILLINGPERIOD']= $t3;
			$postVar['BILLINGFREQUENCY']= $p3;
			$postVar['TOTALBILLINGCYCLES']= 0;
			$postVar['TAXAMT']= $taxRate / 100 * $a3;
			$postVar['AMT']= $a3 + $postVar['TAXAMT'];
			$postVar['PROFILESTARTDATE']= date("Y-m-d h:i:s", strtotime($curDate));
			/*
			$postVar['RETURNURL']= JURI :: root()."index.php?option=com_osemsc&view=confirm&mode=a&orderID=".$order_id."&Itemid=".$Itemid;
			$postVar['CANCELURL']= JURI :: root()."index.php";
			*/
			// Post String;
			$postString= 'METHOD='.urlencode('CreateRecurringPaymentsProfile');
		}
		// Credit Card Information;
		$postVar['CREDITCARDTYPE']= $credit_info["creditcard_type"];
		$postVar['ACCT']= $credit_info["creditcard_number"];
		$creditCardExpiryDate= $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate= explode("-", strval($creditCardExpiryDate));
		$creditCardExpiryDate= $creditCardExpiryDate[1].$creditCardExpiryDate[0];
		$postVar['EXPDATE']= $creditCardExpiryDate;
		$postVar['CVV2']= $credit_info["creditcard_cvv"];
		// Billing Information;
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo= $member->getBillingInfo('obj');
		$postVar['FIRSTNAME']= $billingInfo->firstname;
		$postVar['LASTNAME']= $billingInfo->lastname;
		$postVar['STREET']= $billingInfo->addr1;
		$postVar['CITY']= $billingInfo->city;
		$postVar['STATE']= $billingInfo->state;
		$postVar['ZIP']= $billingInfo->postcode;
		$postVar['CURRENCYCODE']= $currency;
		foreach($postVar AS $key => $val) {
			$postString .= "&".urlencode($key)."=".urlencode($val);
		}
		$resArray= self :: PaypalAPIConnect($postString);
		// Return if empty;
		if(empty($resArray)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}
		if($resArray['ACK'] == 'Success') {
			// Update Order Number to Paypal Transaction ID
			if (!empty($resArray['PROFILEID']) && $resArray['PROFILESTATUS']=='ActiveProfile')
			{
				$params['payment_serial_number'] = $resArray['PROFILEID'];

			}
			elseif (!empty($resArray['TRANSACTIONID']))
			{
				$params['payment_serial_number'] = $resArray['TRANSACTIONID'];
			}
			else
			{
				$params = array ();
			}
			$return = self::confirmOrder($order_id, $params, $msc_id, $user_id, 'paypal_cc');
		} else {
			return self::getErrorMessage('paypal_cc', '0000', $resArray['L_LONGMESSAGE0']);
		}
		return $return;
	}
function PaypalAPICreateProfile($order_id, $token) {
		$PaypalorderInfo= self :: PaypalAPIGetOrderDetails($token);
		$orderInfo= $this->getOrder(array('order_id' => $order_id), 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		//jimport('joomla.utilities.date');
		$curDate= oseHTML::getDateTime();
		if($orderInfoParams->has_trial)
		{
			$a1= $orderInfoParams->total;
			$p1= $orderInfoParams->p1;
			$t1= $orderInfoParams->t1;
			$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
			$postVar['TRIALBILLINGPERIOD']= $t1;
			$postVar['TRIALBILLINGFREQUENCY']= $p1;
			$postVar['TRIALTOTALBILLINGCYCLES']= 1;
			$postVar['TRIALAMT']= $a1;
		}
		$a3= $orderInfoParams->next_total;
		$p3= $orderInfoParams->p3;
		$t3= $orderInfoParams->t3;
		$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
		$Itemid= self :: getItemid();
		$postVar['L_BILLINGTYPE0']= 'RecurringPayments';
		$postVar['L_BILLINGAGREEMENTDESCRIPTION0']= JText :: _('Order ID:')." ".$order_id;
		$postVar['DESC']= JText :: _('Order ID:')." ".$order_id;
		$postVar['TOKEN']= $token;
		$postVar['PAYERID']= $PaypalorderInfo['PAYERID'];
		$postVar['PAYMENTACTION']= urlencode('sale');
		$postVar['BILLINGPERIOD']= $t3;
		$postVar['BILLINGFREQUENCY']= $p3;
		$postVar['TOTALBILLINGCYCLES']= 0;
		$postVar['TAXAMT']= $taxRate / 100 * $a3;
		$postVar['AMT']= $a3 + $postVar['TAXAMT'];
		$postVar['PROFILESTARTDATE']= date("Y-m-d h:i:s", strtotime($curDate));
		// Post String;
		$postString= 'METHOD='.urlencode('CreateRecurringPaymentsProfile');
		foreach($postVar AS $key => $val) {
			$postString .= "&".urlencode($key)."=".urlencode($val);
		}
		$resArray= self :: PaypalAPIConnect($postString);
		if ($resArray['PROFILESTATUS']=='ActiveProfile')
		{
			$user_id= $orderInfo->user_id;
			$msc_id= $orderInfo->entry_id;
			$order_id= $orderInfo->order_id;
			$params['payment_serial_number'] = $resArray['PROFILEID'];
			self::confirmOrder($order_id, $params, $msc_id, $user_id,'paypal');
		}
		return $resArray;
	}
	function PaypalAPIDeleteProfile($ProfileID, $refID, $user_id, $msc_id) {
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."libraries".DS."class.connection.php");
		//$ProfileID = self:: GetProfileID($order_number);
		$postVar['PROFILEID']= urldecode($ProfileID);
		$postVar['ACTION']= 'Cancel';
		$postString= 'METHOD='.urlencode('ManageRecurringPaymentsProfileStatus');
		foreach($postVar AS $key => $val) {
			$postString .= "&".urlencode($key)."=".urlencode($val);
		}
		$resArray= self :: PaypalAPIConnect($postString);
		$result= array();
		if ($resArray['ACK']=='Success')
		{
		$result['code']= '';
		$result['text']= '';
		$result['subscrId']= $ProfileID;
		$result['success']= true;
		}
		else
		{
		$result['code']= $resArray['L_ERRORCODE0'];
		$result['text']= $resArray['L_LONGMESSAGE0'];
		$result['subscrId']= $ProfileID;
		$result['success']= false;
		}
		return $result;
	}

	function PaypalAPIGetOrderDetails($token) {
		$html= array();
		$postVar= array();
		$postVar['TOKEN']= $token;
		$postString= 'METHOD='.urlencode('GetExpressCheckoutDetails');
		foreach($postVar AS $key => $val) {
			$postString .= "&".urlencode($key)."=".urlencode($val);
		}
		$resArray= self :: PaypalAPIConnect($postString);
		// Return if empty;
		if(empty($resArray)) {
			return false;
		}
		elseif(strtoupper($resArray['ACK']) == 'SUCCESS' || strtoupper($resArray['ACK']) == 'SUCCESSWITHWARNING') {
			return $resArray;
		}
	}

	function PaypalAPIPay($order_id, $token) {
		$PaypalorderInfo= self :: PaypalAPIGetOrderDetails($token);
		$orderInfo= $this->getOrder(array('order_id' => $order_id), 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);

		$postVar= array();
		$postVar['TOKEN']= $token;
		$postVar['PAYERID']= $PaypalorderInfo['PAYERID'];
		$postVar['PAYMENTACTION']= urlencode('sale');
		$postVar['AMT']= $PaypalorderInfo['AMT'];
		$postVar['CURRENCYCODE']= $PaypalorderInfo['CURRENCYCODE'];
		$postVar['IPADDRESS']= urlencode($_SERVER['SERVER_NAME']);
		$postVar['VERSION']= VERSION;
		$postString= 'METHOD='.urlencode('DoExpressCheckoutPayment');
		foreach($postVar AS $key => $val) {
			$postString .= "&".urlencode($key)."=".$val;
		}
		$resArray= self :: PaypalAPIConnect($postString);
		if ($resArray['PAYMENTSTATUS']=='Completed')
		{
			$user_id= $orderInfo->user_id;
			$msc_id= $orderInfo->entry_id;
			$order_id= $orderInfo->order_id;
			$params['payment_serial_number'] = $resArray['TRANSACTIONID'];
			self::confirmOrder($order_id, $params);
		}
		return $resArray;
	}

	function PaypalAPIPostForm($orderInfo)
	{
		$html= array();
		$db= oseDB :: instance();

		$taxRate= 0;//$payment->tax_rate;

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$user= & JFactory :: getUser($orderInfo->user_id);
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();

		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= $member->getBillingInfo('obj');
		//oseExit($billinginfo);
		//$node= oseMscTree :: getNode($orderInfo->entry_id, 'obj');

		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');

		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		*/

		$desc = self::generateDesc($order_id);
		$msc_name = $desc;

		$Itemid= self :: getItemid();
		$postVar= array();
		if($orderInfo->payment_mode == 'm') {
			$postVar['ADDRESSOVERRIDE']= 0;
			$postVar['PAYMENTACTION']= 'Sale';
			$postVar['CURRENCYCODE']= $currency;
			$postVar['L_NAME0']= JText :: _('Order ID:')." ".$order_id;
			$postVar['L_DESC0']= JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name;
			$postVar['L_AMT0']= $amount;
			$postVar['L_QTY0']= 1;
			$postVar['TAXAMT']= $taxRate / 100 * $amount * $postVar['L_QTY0'];
			$postVar['ITEMAMT']= $postVar['L_AMT0'] * $postVar['L_QTY0'];
			$postVar['AMT']= $amount * $postVar['L_QTY0'] + $postVar['TAXAMT'];
			$postVar['RETURNURL']= JURI :: root()."index.php?option=com_osemsc&view=confirm&mode=m&orderID=".$order_id."&Itemid=".$Itemid;
			$postVar['CANCELURL']= JURI :: root()."index.php";
			$postString= 'METHOD='.urlencode('SetExpressCheckout');
			foreach($postVar AS $key => $val) {
				$postString .= "&".urlencode($key)."=".urlencode($val);
			}
		} else {
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			//jimport('joomla.utilities.date');
			$curDate= oseHTML::getDateTime();
			if($orderInfoParams->has_trial) {
				$a1= $orderInfoParams->total;
				$p1= $orderInfoParams->p1;
				$t1= $orderInfoParams->t1;
				$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$postVar['TRIALBILLINGPERIOD']= $t1;
				$postVar['TRIALBILLINGFREQUENCY']= $p1;
				$postVar['TRIALTOTALBILLINGCYCLES']= 1;
				$postVar['TRIALAMT']= $a1;
			}
			$a3= $orderInfoParams->next_total;
			$p3= $orderInfoParams->p3;
			$t3= $orderInfoParams->t3;
			$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			$postVar['L_BILLINGTYPE0']= 'RecurringPayments';
			$postVar['L_BILLINGAGREEMENTDESCRIPTION0']= JText :: _('Order ID:')." ".$order_id;
			$postVar['DESC']= JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name;
			$postVar['BILLINGPERIOD']= $t3;
			$postVar['BILLINGFREQUENCY']= $p3;
			$postVar['TOTALBILLINGCYCLES']= 0;
			$postVar['TAXAMT']= $taxRate / 100 * $a3;
			$postVar['AMT']= $a3 + $postVar['TAXAMT'];
			$postVar['PROFILESTARTDATE']= date("Y-m-d Th:i:sZ", strtotime($curDate));
			$postVar['RETURNURL']= JURI :: root()."index.php?option=com_osemsc&view=confirm&mode=a&orderID=".$order_id."&Itemid=".$Itemid;
			$postVar['CANCELURL']= JURI :: root()."index.php";
			// Post String;
			$postString= 'METHOD='.urlencode('SetExpressCheckout');
			foreach($postVar AS $key => $val) {
				$postString .= "&".urlencode($key)."=".urlencode($val);
			}
		}
		$resArray= self :: PaypalAPIConnect($postString);

		// Return if empty;
		if(empty($resArray)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}
		$_SESSION['reshash']= $resArray;
		$ack= strtoupper($resArray["ACK"]);
		$html= array();
		if($ack == "SUCCESS") {
			$token= urldecode($resArray["TOKEN"]);
			$url= $resArray["Paypal_URL"].$token;
		} else {
			$errorcode= $resArray["L_ERRORCODE0"];
			$message= $resArray["L_LONGMESSAGE0"];
			$url= JURI::root().'index.php';
		}
		$html['form']= '<form action="'.$url.'" method="post" target="_self">';
		$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
		$html['form'] .= '</form>';
		return $html;
	}
	function PaypalExpPostForm($orderInfo,$params) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$paypal_email= $pConfig->paypal_email;
		$html= array();
		$test_mode= $pConfig->paypal_testmode;
		if(empty($paypal_email)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}
		if($test_mode == true) {
			$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url= "https://www.paypal.com/cgi-bin/webscr";
		}

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= self::getBillingInfo($orderInfo->user_id);

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		//$billinginfo = OSEPAYMENTS::get_billinginfo($user_id);
		$user= & JFactory :: getUser($orderInfo->user_id);

		//oseExit($billinginfo);
		//$node= oseMscTree :: getNode($orderInfo->entry_id, 'obj');

		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');

		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		*/

		$desc = self::generateDesc($order_id);
		$msc_name = $desc;
		//$msc_name= $node->title;
		//echo $a3;
		//oseExit($orderInfo);
		//$session = JFactory::getSession();
		//$returnUrl = $session->get('oseReturnUrl',false);
		//$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$session = JFactory::getSession();
		$return_url = (isset($orderInfoParams->returnUrl))?urldecode($orderInfoParams->returnUrl):"index.php";
		
		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$html['form']= '<form action="'.$url.'" method="post" target="_self">';
		$html['form'] .= '<input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		if($orderInfo->payment_mode == 'm') {
			$post_variables= array("cmd" => "_ext-enter", "redirect_cmd" => "_xclick", "upload" => "1", "business" => $paypal_email, "receiver_email" => $paypal_email, "item_name" => JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name, "order_id" => $order_id, "invoice" => $order_number, "amount" => round($amount, 2), "shipping" => '0.00', "currency_code" => $currency, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "cpp_header_image" => $vendor_image_url, "return" => $return_url, "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "undefined_quantity" => "0", "test_ipn" => 0, "pal" => "NRUBJXESJTY24", "no_shipping" => "1", "no_note" => "1");
			$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
		} else {
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			if(!$orderInfoParams->has_trial) {
				$a3= $orderInfoParams->total;
				$p3= $orderInfoParams->p3;
				$t3= $orderInfoParams->t3;
				$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				$post_variables= array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "item_name" => JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name, "order_id" => $order_id, "item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => $return_url, "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "a3" => round($a3, 2), "p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency, "cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
			} else {
				$a1= $orderInfoParams->total;
				$p1= $orderInfoParams->p1;
				$t1= $orderInfoParams->t1;

				$a3= $orderInfoParams->next_total;
				$p3= $orderInfoParams->p3;
				$t3= $orderInfoParams->t3;

				$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);

				$post_variables= array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "item_name" => JText :: _('Order ID: ').$order_id." - ".JText :: _('Payment for Membership Type: ').$msc_name, "order_id" => $order_id, "item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => $return_url, "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "a1" => $a1, "p1" => $p1, "t1" => $t1, "a3" => $a3, "p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency, "cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
			}
		}
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	private function PaypalAPIConnect($postString) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$test_mode= $pConfig->paypal_testmode;
		$API_UserName= $pConfig->paypal_api_username;
		$API_Password= $pConfig->paypal_api_passwd;
		$API_Signature= $pConfig->paypal_api_signature;
		$subject= '';
		if(empty($API_UserName) || empty($API_Password) || empty($API_Signature)) {
			return false;
		}
		define('VERSION', '64.0');
		define('ACK_SUCCESS', 'SUCCESS');
		define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');
		if($test_mode == true) {
			$API_Endpoint= 'api-3t.sandbox.paypal.com';
			$Paypal_URL= 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
		} else {
			$API_Endpoint= 'api-3t.paypal.com';
			$Paypal_URL= 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
		}
		$postVar['PWD']= $API_Password;
		$postVar['USER']= $API_UserName;
		$postVar['SIGNATURE']= $API_Signature;
		$postVar['VERSION']= VERSION;
		$postHead= '';
		foreach($postVar AS $key => $val) {
			$postHead .= "&".urlencode($key)."=".$val;
		}
		$postString= $postString.$postHead;
		$response= OSECONNECTOR :: send_request_via_fsockopen($API_Endpoint, '/nvp', $postString);
		$resArray= self :: parseResults($response);
		$resArray["Paypal_URL"]= $Paypal_URL;
		return $resArray;
	}
	private function parseResults($response) {
		$c_mccomb= "\n";
		$return= array();
		$var= explode($c_mccomb, $response);
		$lastrow= $var[count($var) - 1];
		$lastrow= explode("&", $lastrow);
		foreach($lastrow as $row) {
			$row= explode("=", $row);
			$return[$row[0]]= $row[1];
		}
		return $return;
	}

	function VpcashOneOffPostForm($orderInfo,$params) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$vpcash_account= $pConfig->vpcash_account;
		$vpcash_email= $pConfig->vpcash_email;
		$store_id = $pConfig->vpcash_storeid;
		$html= array();
		$test_mode= $pConfig->vpcash_testmode;
		if(empty($vpcash_account)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}
		if($test_mode == true) {
			$url= "https://www.virtualpaycash.net/sandbox/handle.php";
		} else {
			$url= "https://www.virtualpaycash.net/handle.php";
		}

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= self::getBillingInfo($orderInfo->user_id);

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;

		$user= & JFactory :: getUser($orderInfo->user_id);

		$desc = self::generateDesc($order_id);
		$msc_name = $desc;


		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$html['form']= '<form action="'.$url.'" method="post">';
		//$html['form'] .= '<input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		if($orderInfo->payment_mode == 'm') {
			$post_variables= array("merchantAccount" => $vpcash_account, "store_id"=>$store_id, "receiver_email"=>$vpcash_email, "amount" => round($amount, 2), "vpc_currency" => $currency, "lang"=>"en", "item_id" => $order_number, "return_url" => JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}", "notify_url" => JURI :: base()."components/com_osemsc/ipn/vpcash_notify.php", "cancel_url" => JURI :: base()."index.php", "SUGGESTED_MEMO"=>"ADDITIONAL-INFO");
			$html['form'] .= '<input type="image" id="vpcash_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with VirtualPayCash - it is fast, free and secure!').'" />';
		}
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		return $html;
	}

	function get_gcoform($orderInfo) {
		$parameters= & JComponentHelper :: getParams('com_osemsc');
		$google_checkout_id= $parameters->get('google_checkout_id');
		$html= array();
		if(empty($google_checkout_id)) {
			$html['form']= "";
			return $html;
		}
		/*
		$db = &JFactory::getDBO();
		require_once (JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "warehouse" . DS . "public.php");
		$query = "SELECT name FROM `#__osemsc_acl` WHERE id = '{$msc_id}'";
		$db->setQuery($query);
		$msc_name = $db->loadResult();
		$query = "SELECT * FROM `#__osemsc_ext` WHERE id = '{$msc_id}' AND type='msc'";
		$db->setQuery($query);
		$msc_data = $db->loadObject();
		$msc_data = publicTools::parseParams($msc_data);
		*/
		$msc_id= $orderInfo->entry_id;
		$node= oseMscTree :: getNode($msc_id, 'obj');
		$msc_name= $node->title;
		$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		$price= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		//$renewal_discounts = $msc_data->renewal_discounts;
		//$promotion_code = $msc_data->promotion_code;
		//$promotion_discounts = $msc_data->promotion_discounts;
		//if ($user_promotion_code == $promotion_code)
		//{
		//	$amount = $amount * (1-$promotion_discounts/100);
		//}
		// Check if the user is a member of the membership
		//require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."warehouse".DS."api.php");
		//$api=new OSEMSCAPI();
		////if ($api->is_member($msc_id, $user_id)==true)
		//{
		//	if (!empty($renewal_discounts))
		//	{
		//		$amount = $amount * (1-$renewal_discounts/100);
		//		$a1= $a1 * (1-$renewal_discounts/100);
		//	}
		//}
		// Renewal discounts ends
		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		if($currency == "GBP") {
			$country_code= "UK";
		}
		elseif($currency == "USD") {
			$country_code= "US";
		}
		$url= "https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/".$google_checkout_id;
		$post_variables= array("item_name_1" => JText :: _('Order ID: ').$order_id, "item_description_1" => JText :: _('Payment for Membership Type: ').$msc_name."||".$order_number, "item_merchant_id_1" => $order_id, "item_quantity_1" => "1", "item_price_1" => $price, "item_currency_1" => $currency, "continue_url" => JURI :: base()."index.php?option=com_osemsc&view=member&result=success");
		$html['form']= '<form action="'.$url.'" method="post" target="_self" id="google" name="google"><input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		$html['form'] .= '<input id="gco-image" type="image" name="Google Checkout" alt="Fast checkout through Google"
				src="components/com_osemsc/assets/images/checkout.png?merchant_id='.$google_checkout_id.'&style=white&variant=text&loc=en_US"/>';
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function getBillingInfo($user_id) {
		$result= new stdClass;
		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($user_id);
		$item= $member->getBillingInfo('obj');
		if(empty($result)) {
			$result->user_id= $user_id;
			$result->email= '';
			$result->company= '';
			$result->firstname= "";
			$result->lastname= "";
			$result->addr1= "";
			$result->addr2= "";
			$result->postcode= "";
			$result->city= "";
			$result->state= "";
			$result->country= "";
			$result->phone = $result->telephone= "";
			$result->fax= "";
		} else {
			$result->user_id= $user_id;
			$result->email= $item->user_email;
			$result->company= $item->company;
			$result->firstname= $item->firstname;
			$result->lastname= $item->lastname;
			$result->addr1= $item->addr1;
			$result->addr2= (isset($item->addr2))?$item->addr2:'';
			$result->postcode= $item->postcode;
			$result->city= $item->city;
			$result->state= $item->state;
			$result->country= (isset($item->country))?$item->country:'';
			$result->phone = $result->telephone= $item->telephone;
			$result->fax= '';
		}
		$result = self::getBillingInfoCleaned($result);
		return $result;
	}
	function getBillingInfoCleaned($billingInfo) {
		$billingInfo->firstname= str_replace("&", " ", $billingInfo->firstname);
		$billingInfo->lastname= str_replace("&", " ", $billingInfo->lastname);
		$billingInfo->company= str_replace("&", " ", $billingInfo->company);
		$billingInfo->addr1= str_replace("&", " ", $billingInfo->addr1);
		$billingInfo->city= str_replace("&", " ", $billingInfo->city);
		$billingInfo->state= str_replace("&", " ", $billingInfo->state);
		return $billingInfo;
	}

	function eWayOneOffPay($orderInfo,$credit_info, $params = array() )
	{
		$config= oseMscConfig :: getConfig('', 'obj');
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo,$config,$config->eway_testmode);
		$cc_methods= explode(',', $config->cc_methods);
		if(!in_array('eway', $cc_methods) || $config->enable_cc == false) {
			return $eway->getErrorMessage('cc', '0003', null);
		}
		$eway->setCreditCardInfo($credit_info);
		$results = $eway->OneOffPay();
		return $results;
	}

	function eWayBillAuthorize($orderInfo,$type )
	{
		$config= oseMscConfig :: getConfig('', 'obj');

		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo,$config,$config->eway_testmode);

		return $eway->queryRebill($type);
	}

	function eWayCreateProfile($orderInfo,$credit_info, $params = array() )
	{
		$config= oseMscConfig :: getConfig('', 'obj');

		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo,$config,$config->eway_testmode);

		$cc_methods= explode(',', $config->cc_methods);

		if(!in_array('eway', $cc_methods) || $config->enable_cc == false) {
			return $eway->getErrorMessage('cc', '0003', null);
		}

		$eway->setCreditCardInfo($credit_info);
		return $eway->CreateProfile();
	}

	function eWayDeleteProfile($orderInfo, $params = array() )
	{
		$config= oseMscConfig :: getConfig('', 'obj');

		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo,$config,$config->eway_testmode);


		return $eway->DeleteProfile();
	}

	function ePayOneOffPay($orderInfo, $params = array() )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderePay.php');

		$ePayParams = array();
		$orderInfoParams = oseJson::decode($orderInfo->params);

		$pConfig = oseRegistry::call('msc')->getConfig('payment','obj');

		$ePayParams['order_number'] = oseObject::getValue($orderInfo,'order_number');
		$ePayParams['order_number'] = substr($ePayParams['order_number'],0,20);
		$ePayParams['amount'] = $orderInfoParams->next_total;
		$ePayParams['currency'] = oseObject::getValue($orderInfo,'payment_currency');
		$ePayParams['merchantnumber'] = $pConfig->epay_merchantnumber;
		$ePayParams['md5'] = $pConfig->epay_md5;
		$epay = new osePaymentOrderePay;
		$html = $epay->ePayCreateProfile(0, $ePayParams );

		return $html;
		//return array('html'=>$html);
	}

	function ePayCreateProfile($orderInfo, $params = array() )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderePay.php');

		$ePayParams = array();
		$orderInfoParams = oseJson::decode($orderInfo->params);

		$pConfig = oseRegistry::call('msc')->getConfig('payment','obj');

		$ePayParams['order_number'] = oseObject::getValue($orderInfo,'order_number');
		$ePayParams['order_number'] = substr($ePayParams['order_number'],0,20);
		$ePayParams['amount'] = $orderInfoParams->total;
		$ePayParams['currency'] = oseObject::getValue($orderInfo,'payment_currency');
		$ePayParams['merchantnumber'] = $pConfig->epay_merchantnumber;
		$ePayParams['md5'] = $pConfig->epay_md5;

		// if free trial
		if($orderInfo->payment_price == 0)
		{
			//$this->confirmOrder($orderInfo);
			return false;
		}
		else
		{
			$epay = new osePaymentOrderePay;
			$html = $epay->ePayCreateProfile(1, $ePayParams );
			return $html;
		}
		//return array('html'=>$html);
	}

	function ePayDeleteProfile($orderInfo,$credit_info, $params )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderePay.php');
		return osePaymentOrdereWay::ePayDeleteProfile();
	}

	function BeanStreamOneOffPay($orderInfo,$credit_info, $isSub = false, $params = array() )
	{
		ini_set('max_execution_time','180');
		$config= oseMscConfig :: getConfig('', 'obj');
		$cc_methods= explode(',', $config->cc_methods);

		if(!in_array('beanstream', $cc_methods) || $config->enable_cc == false) {
			//return self::getErrorMessage('cc', '0003', null);
		}

		$pConfig = $config;//oseRegistry::call('msc')->getConfig('payment', 'obj');

		$merchant_id = $pConfig->beanstream_merchant_id;
		$username = $pConfig->beanstream_username;
		$password = $pConfig->beanstream_password;
		//$pubKey = $pConfig->beanstream_public_key;

		if(empty($merchant_id)) {
			return self::getErrorMessage('cc', '0001', null);
		}

		if(empty($orderInfo->payment_price)) {
			return self::getErrorMessage('cc', '0002', null);
		}

		//require_once(OSEMSC_B_LIB.DS.'beanstreamsoap.php');

		$result= array();
		$db = oseDB :: instance();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$desc = $this->generateDesc($order_id);
		$billingInfo= $this->getBillingInfo($orderInfo->user_id);
		$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$currency= $orderInfo->payment_currency;

		$user= & JFactory :: getUser($orderInfo->user_id);
		$app= & JFactory :: getApplication();
		$Itemid= $this->getItemid();

		$amount= $orderInfo->payment_price;

		if(empty($amount))
		{
			$updataParams = array();
			//$updataParams['payment_serial_number'] = $post['trnId'];
			return $this->confirmOrder($order_id,$updataParams);
		}

		$postVar= array();
		/* $totalAmount in cents, as required by ePay:
		The total amount in cents for the transaction, eg $1.00 = 100
		*/

		// General
		//$postVar['public_key']= $pubKey;
		$postVar['requestType']= 'BACKEND';
		$postVar['merchant_id']= $merchant_id;
		$postVar['username']= $username;
		$postVar['password']= $password;
		$postVar['trnAmount']= $amount;
		$postVar['trnOrderNumber']= substr($order_number, 0,20);
		$postVar['trnType']= 'P';
		$postVar['trnRecurring']= 0;
		$postVar['paymentMethod'] = 'CC';
		$postVar['ref1'] = $order_id;
		$postVar['ref2'] = $orderInfoParams->timestamp;
		//$postVar['adjId']= $amount;
		//$postVar['trnId']= '232111111';

		// Card Info
		$postVar['trnCardOwner']= $credit_info["creditcard_name"];
		$postVar['trnCardNumber']= $credit_info["creditcard_number"];
		$postVar['trnExpMonth']=substr($credit_info["creditcard_month"],-2);
		$postVar['trnExpYear']= substr($credit_info["creditcard_year"],-2);
		$postVar['trnCardCvd'] = $credit_info["creditcard_cvv"];

		// Billing
		$postVar['ordName'] = $billingInfo->firstname.' '.$billingInfo->lastname;
		$postVar['ordEmailAddress'] = $billingInfo->email;
		$postVar['ordPhoneNumber'] = $billingInfo->telephone;
		$postVar['ordAddress1'] = $billingInfo->addr1;
		$postVar['ordCity'] = $billingInfo->city;
		$postVar['ordProvince'] = $billingInfo->state;
		$postVar['ordPostalCode'] = $billingInfo->postcode;
		$postVar['ordCountry'] = $billingInfo->country;

		// Recurring
		$postVar['rbBillingPeriod'] = $this->BeanStreamTransInterval($orderInfoParams->t3);
		$postVar['rbBillingIncrement'] = $orderInfoParams->p3;

		// URL
		$postVar['errorPage'] = urlencode('http://dev2.opensource-excellence.net');
		$postVar['approvedPage'] = urlencode('http://www.lucas.ose-host.com/mscv5/administrator/index.php');
		$postVar['declinedPage'] = urlencode('http://dev2.opensource-excellence.net');

		//$postVar['beanstreamKeyId'] = "0x38180389";
		//$postVar['hash'] = $MD5key = md5( $currency . $amount . $orderId . $md5 );
		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);

		$uri= "/scripts/process_transaction.asp";




		require_once(OSEMSC_B_LIB.DS.'class.connection.php');
		$res = OSECONNECTOR::send_request_via_fsockopen($hostname,$uri,$workstring,'urlencoded');
		$res = stristr($res,"\r\n\r\n");
		$res = trim($res);
		$post = array();
		parse_str($res,$post);
		//oseExit($post);

		if($post['trnApproved'])
		{

			$updataParams = array();
			$updataParams['payment_serial_number'] = $post['trnId'];

			if($post['authCode'] == 'TEST1' )
			{
				if(empty($pConfig->beanstream_testmode))
				{
					return self::getErrorMessage('cc', '0000', 'Warning: It would be recorded as Invalid!');
				}
				else
				{
					return self::getErrorMessage('cc', '0000', 'Error: Test Successfully');
				}

			}
			else
			{
				$updated = $this->confirmOrder($order_id,$updataParams);

				if($isSub)
				{
					$orderInfo->payment_serial_number = $post['trnId'];
					$updated['orderInfo'] = $orderInfo;
				}

				return $updated;
			}
		}
		else
		{
			return self::getErrorMessage('cc', '0000', 'Error: '.$post['messageText']);
		}
	}

	function BeanStreamTransInterval($t) {
		$results= array();
		$t= strtolower($t);
		$result = null;
		switch($t) {
			case "year" :
				$result = 'Y';
				break;
			case "month" :
				$result = 'M';
				break;
			case "week" :
				$result = 'W';
				break;
			case "day" :
				$result = 'D';
				break;
		}
		return $result;
	}

	function BeanStreamCreateProfile($orderInfo,$credit_info, $params = array() )
	{
		$updated = $this->BeanStreamOneOffPay($orderInfo,$credit_info, true,$params);
		//$updated = array();$updated['success'] = true;
		if($updated['success'])
		{
			if(isset($updated['orderInfo']))
			{
				$orderInfo = $updated['orderInfo'];
				unset($updated['orderInfo']);
			}

			$config= oseMscConfig :: getConfig('', 'obj');

			$pConfig = $config;//oseRegistry::call('msc')->getConfig('payment', 'obj');

			$user= & JFactory :: getUser($orderInfo->user_id);
			$app= & JFactory :: getApplication();
			$Itemid= $this->getItemid();

			$merchant_id = $pConfig->beanstream_merchant_id;
			$username = $pConfig->beanstream_username;
			$password = $pConfig->beanstream_password;

			if(empty($merchant_id)) {
				return self::getErrorMessage('cc', '0001', null);
			}



			//require_once(OSEMSC_B_LIB.DS.'beanstreamsoap.php');

			$result= array();
			$db= oseDB :: instance();
			$user_id= $orderInfo->user_id;
			$msc_id= $orderInfo->entry_id;
			$order_id= $orderInfo->order_id;
			$order_number = $orderInfo->order_number;
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			$desc = $this->generateDesc($order_id);
			$billingInfo= $this->getBillingInfo($orderInfo->user_id);
			$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
			$currency= $orderInfo->payment_currency;

			$user= & JFactory :: getUser($orderInfo->user_id);

			$amount= $orderInfoParams->next_total;

			if(empty($amount)) {
				return self::getErrorMessage('cc', '0002', null);
			}

			$postVar= array();
			/* $totalAmount in cents, as required by ePay:
			The total amount in cents for the transaction, eg $1.00 = 100
			*/

			// General
			$postVar['requestType']= 'BACKEND';
			$postVar['merchant_id']= $merchant_id;
			$postVar['username']= $username;
			$postVar['password']= $password;
			$postVar['trnAmount']= $amount;
			$postVar['trnOrderNumber']= substr($order_number, 0,20);
			$postVar['trnType']= 'P';
			$postVar['trnRecurring']= 1;
			$postVar['paymentMethod'] = 'CC';
			$postVar['ref1'] = $order_id;
			$postVar['ref2'] = $orderInfoParams->timestamp;
			//$postVar['adjId']= $amount;

			// Card Info
			$postVar['trnCardOwner']= $credit_info["creditcard_name"];
			$postVar['trnCardNumber']= $credit_info["creditcard_number"];
			$postVar['trnExpMonth']=substr($credit_info["creditcard_month"],-2);
			$postVar['trnExpYear']= substr($credit_info["creditcard_year"],-2);
			$postVar['trnCardCvd'] = $credit_info["creditcard_cvv"];

			// Billing
			$postVar['ordName'] = $billingInfo->firstname.' '.$billingInfo->lastname;
			$postVar['ordEmailAddress'] = $billingInfo->email;
			$postVar['ordPhoneNumber'] = $billingInfo->telephone;
			$postVar['ordAddress1'] = $billingInfo->addr1;
			$postVar['ordCity'] = $billingInfo->city;
			$postVar['ordProvince'] = $billingInfo->state;
			$postVar['ordPostalCode'] = $billingInfo->postcode;
			$postVar['ordCountry'] = $billingInfo->country;

			// Recurring
			$curDate= oseHTML::getDateTime();

			/*
			if($orderInfoParams->has_trial)
            {
            	$dateAdd = '+
'.$orderInfoParams->p1.''.$orderInfoParams->t1;
            	$startDate = strtotime($dateAdd,strtotime($curDate));
            	$startDate = date('mdY',$startDate);
            }
            else
            {
            	$dateAdd = '+
'.$orderInfoParams->p3.''.$orderInfoParams->t3;
            	$startDate = strtotime($dateAdd,strtotime($curDate));
            	$startDate = date('mdY',$startDate);

           	}
           	*/


			if($orderInfoParams->has_trial)
			{
				$nT1 = $this->BeanStreamTransInterval($orderInfoParams->t1);

				$query = " SELECT DATE_ADD('{$curDate}',INTERVAL {$orderInfoParams->p1} {$orderInfoParams->t1})";

			}
			else
			{
				$nT3 = $this->BeanStreamTransInterval($orderInfoParams->t3);
				$query = " SELECT DATE_ADD('{$curDate}',INTERVAL {$orderInfoParams->p3} {$orderInfoParams->t3})";
			}

			$db->setQuery($query);
			$date = new DateTime($db->loadResult());
			$startDate = $date->format("mdY");


			$postVar['rbCharge'] = 0;
			$postVar['rbFirstBilling'] = $startDate;
			$postVar['rbBillingPeriod'] = $this->BeanStreamTransInterval($orderInfoParams->t3);;
			$postVar['rbBillingIncrement'] = $orderInfoParams->p3;

			// URL
			$postVar['errorPage'] = urlencode(JURI::root());
			$postVar['approvedPage'] = urlencode(JURI::root());
			$postVar['declinedPage'] = urlencode(JURI::root());

			$hostname = 'www.beanstream.com';
			$workstring = http_build_query($postVar);

			$uri= "/scripts/process_transaction.asp";

			require_once(OSEMSC_B_LIB.DS.'class.connection.php');
			$res = OSECONNECTOR::send_request_via_fsockopen($hostname,$uri,$workstring,'urlencoded');
			$res = stristr($res,"\r\n\r\n");
			$res = trim($res);
			$post = array();
			parse_str($res,$post);

			if($post['trnApproved'])
			{
				$updataParams = array();
				$updataParams['payment_serial_number'] = $post['rbAccountId'];
				//$updataParams['beanstream_rbAccountId'] = $post['rbAccountId'];
				$orderInfoParams->oneoff_transactionid = $orderInfo->payment_serial_number;
				$updataParams['params'] = oseJson::encode($orderInfoParams);

				$result = $this->updateOrder($order_id, 'confirmed', $updataParams);
				return $updated;//$this->confirmOrder($order_id,$updataParams);
			}
			else
			{
				return self::getErrorMessage('cc', '0000', JText::_('Joined Membership, but subscription fails creating. Error: '.$post['messageText']));
			}
		}
		else
		{
			return $updated;
		}
	}

	function BeanStreamDeleteProfile($orderInfo, $params = array() )
	{
		require_once(OSEMSC_B_LIB.DS.'class.connection.php');

		//$orderInfo = oseRegistry::call('payment')->getOrder(array('order_id'=>319),'obj');
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$merchant_id = $pConfig->beanstream_merchant_id;
		$passcode = $pConfig->beanstream_passcode;


		$orderInfoParams= oseJson :: decode($orderInfo->params);

		$postVar = array();
		$postVar['serviceVersion']= '1.0';
		$postVar['operationType']= 'M';
		$postVar['merchantId']= $merchant_id;
		$postVar['passcode']= $passcode;
		$postVar['rbAccountID']= $orderInfo->payment_serial_number;
		$postVar['rbBillingState']= 'C';
		$postVar['processBackPayments']= '0';
		$postVar['ref5']= '';

		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);

		$uri= "/scripts/recurring_billing.asp";

		$res = OSECONNECTOR::send_request_via_fsockopen($hostname,$uri,$workstring,'urlencoded');
		$res = stristr($res,"\r\n\r\n");
		$res = trim($res);
		//$post = array();
		//parse_str($res,$post);

		$result = array();

		$code = OSECONNECTOR :: substring_between($res, '<code>', '</code>');
		$message = OSECONNECTOR :: substring_between($res, '<message>', '</message>');

		$result['success'] = ($code == 1)?true:false;
		$result['title'] = JText::_('Cancel');
		$result['content'] = ($code == 1)?JText::_('Your membership subscription is cancelled.'):JText::_('Error').':'.$message;
		return $result;
	}

	function PNWOneOffPay($orderInfo, $params = array() )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderPnw.php');

		$orderInfoParams = oseJson::decode($orderInfo->params);

		$pConfig = oseRegistry::call('msc')->getConfig('payment','obj');

		$PNWParams = array();

		$PNWParams['project_id'] = $pConfig->pnw_project_id;
		$PNWParams['project_password'] = $pConfig->pnw_project_password;
		$PNWParams['user_id'] = $pConfig->pnw_user_id;
		$PNWParams['language_id'] = oseObject::getValue($pConfig,'language_id','EN');

		$PNWParams['reason_1'] = oseObject::getValue($orderInfo,'order_id');
		$PNWParams['order_number'] = oseObject::getValue($orderInfo,'order_number');
		//$PNWParams['order_number'] = substr($PNWParams['order_number'],0,20);
		$PNWParams['amount'] = $orderInfoParams->next_total;
		$PNWParams['currency_id'] = oseObject::getValue($orderInfo,'payment_currency');

		if(oseObject::getValue($orderInfoParams,'eternal',false))
		{
			$PNWParams['expires'] = '';
		}
		else
		{
			$PNWParams['expires'] = oseObject::getValue($orderInfoParams,'p3');
		}

		$PNWParams['recurrence_unit'] = oseObject::getValue($orderInfoParams,'t3');
		$PNWParams['max_usage'] = 1;

		$PNW = new osePaymentOrderPNW;
		$html = $PNW->PNWCreateProfile(0, $PNWParams );

		return $html;
		//return array('html'=>$html);
	}

	function PNWCreateProfile($orderInfo, $params = array() )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderPnw.php');

		$orderInfoParams = oseJson::decode(oseObject::getValue($orderInfo,'params'));

		$pConfig = oseRegistry::call('msc')->getConfig('payment','obj');

		$PNWParams = array();

		$PNWParams['project_id'] = $pConfig->pnw_project_id;
		$PNWParams['project_password'] = $pConfig->pnw_project_password;
		$PNWParams['user_id'] = $pConfig->pnw_user_id;
		$PNWParams['language_id'] = oseObject::getValue($pConfig,'language_id','EN');

		$PNWParams['order_number'] = oseObject::getValue($orderInfo,'order_number');
		$PNWParams['order_number'] = substr($PNWParams['order_number'],0,20);
		$PNWParams['amount'] = $orderInfoParams->next_total;
		$PNWParams['currency_id'] = oseObject::getValue($orderInfo,'payment_currency');

		if(oseObject::getValue($orderInfoParams,'eternal',false))
		{
			$PNWParams['expires'] = '';
		}
		else
		{
			$PNWParams['expires'] = oseObject::getValue($orderInfoParams,'p3');
		}

		$PNWParams['recurrence_unit'] = oseObject::getValue($orderInfoParams,'t3');

		// if free trial
		if($orderInfo->payment_price == 0)
		{
			//$this->confirmOrder($orderInfo);
			return false;
		}
		else
		{
			$PNW = new osePaymentOrderPNW;
			$html = $PNW->PNWCreateProfile(1, $PNWParams );
			return $html;
		}
		//return array('html'=>$html);
	}

	function PNWDeleteProfile($orderInfo,$credit_info, $params )
	{
		$curPath = dirname (__FILE__);
		require_once($curPath. DS. 'osePaymentOrderePay.php');
		return osePaymentOrdereWay::ePayDeleteProfile();
	}

	function AuthorizeAIMPay($orderInfo, $credit_info, $params= array(), $TransactionType='AUTH_CAPTURE', $ARBtrialPayment=false)
	{
		ini_set('max_execution_time','180');
		$resArray = array();

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'Authnet.class.php');
		$db= oseDB :: instance();
		$result= array();
		$user_id= $orderInfo->user_id;
		//$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;

		$desc = $desc = $this->generateDesc($order_id);
		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');

		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		*/
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// Get User billing information;

		$billingInfo= $this->getBillingInfo($orderInfo->user_id);
		// Get Authorize.net Setting if the mode is OSE;
		$config= oseMscConfig :: getConfig('', 'obj');
		$cc_methods= explode(',', $config->cc_methods);
		if(!in_array('authorize', $cc_methods) || $config->enable_cc == false) {
			return self::getErrorMessage('cc', '0003', null);
		}
		if(empty($orderInfo->payment_price)) {
			return self::getErrorMessage('cc', '0002', null);
		}
		if ($orderInfo->payment_currency!='USD')
		{
			return self::getErrorMessage('cc', '0001', null);
		}

		if($orderInfoParams->next_total <= 0)
		{
			return self::getErrorMessage('cc', '0002', null);
			//$resArray['isApproved'] = true;//$cc_payment->isApproved();
			//$resArray['TransactionID'] = 'FreeJoin';//$cc_payment->getTransactionID();
			//$resArray['content']= 'free join';//$cc_payment->getResponseText();
			//$return = self:: confirmOrder($order_id, $params, 0, $user_id, 'authorize');
		}
		else
		{
			$refID= substr($order_number, 0, 19)."M";
			$invoice= substr($order_number, 0, 19)."M";
			//$name= "MEM{$msc_id}UID{$user_id}_".date("Ymdhis");

			$taxRate= 0;//$payment->tax_rate;
			// Credit Card Informaiton;
			$creditcard= $credit_info["creditcard_number"];
			$cardCode= $credit_info["creditcard_cvv"];
			$expiration= $credit_info["creditcard_expirationdate"];
			$expiration= strval($expiration);
			// Process Payments;
			$cc_payment= Authnet :: instance();
			$cc_payment->setParameter('refID'  , $refID);
			$cc_payment->setParameter('x_first_name', substr($billingInfo->firstname, 0, 50));
			$cc_payment->setParameter('x_last_name', substr($billingInfo->lastname, 0, 50));
			$cc_payment->setParameter('x_company', substr($billingInfo->company, 0, 50));
			$cc_payment->setParameter('x_address', substr($billingInfo->addr1, 0, 60));
			$cc_payment->setParameter('x_city', substr($billingInfo->city, 0, 40));
			$cc_payment->setParameter('x_state', substr($billingInfo->state, 0, 40));
			$cc_payment->setParameter('x_zip', substr($billingInfo->postcode, 0, 20));
			$cc_payment->setParameter('x_country', substr($billingInfo->country, 0, 60));
			$cc_payment->setParameter('x_phone', substr($billingInfo->telephone, 0, 25));
			$cc_payment->setParameter('x_fax', substr($billingInfo->fax, 0, 25));
			$cc_payment->setParameter('x_cust_id', $user_id);
			$cc_payment->setParameter('x_customer_ip', $_SERVER["REMOTE_ADDR"]);
			$cc_payment->setParameter('x_email', $billingInfo->email);
			$cc_payment->setParameter('x_description', $desc);

			// Process Payments;
			if ($ARBtrialPayment==false)
			{
				$amount= $orderInfoParams->next_total;
			}
			else
			{
				if($TransactionType == 'AUTH_ONLY')
				{
					$amount= $orderInfoParams->next_total;
				}
				else
				{
					$amount= $orderInfoParams->total;
				}

			}
			//oseExit($ARBtrialPayment.' '.$TransactionType.' '.$amount);
			$tax = $amount * $taxRate;

			$cc_payment->setTransactionType($TransactionType);
			$cc_payment->setParameter('x_email_customer', true);
			$cc_payment->setTransaction($creditcard, $expiration, $amount, $cardCode, $invoice, $tax);

			$pConfig= oseMscConfig :: getConfig('payment', 'obj');
			if(empty($pConfig->an_loginid) || empty($pConfig->an_transkey)) {
				return false;
			}
			if(!isset($pConfig->cc_testmode) || $pConfig->cc_testmode == true) {
				$test_mode= true;
			} else {
				$test_mode= false;
			}

			$subdomain=($test_mode) ? 'test' : 'secure';
			$cc_payment->url= $subdomain.".authorize.net";
			$cc_payment->setParameter('x_login', $pConfig->an_loginid);
			$cc_payment->setParameter('x_tran_key', $pConfig->an_transkey);
			$cc_payment->setParameter('x_email_customer', $pConfig->an_email_customer);
			$cc_payment->setParameter('x_merchant_email', $pConfig->an_merchant_email);
			$cc_payment->setParameter('x_email_merchant', $pConfig->an_email_merchant);

			$cc_payment->process();

			$resArray['isApproved'] = $cc_payment->isApproved();
			$resArray['TransactionID'] = $cc_payment->getTransactionID();
			$resArray['content']= $cc_payment->getResponseText();
		}

		$subdomain=($test_mode) ? 'test' : 'secure';
		$cc_payment->url= $subdomain.".authorize.net";
		$cc_payment->setParameter('x_login', $pConfig->an_loginid);
		$cc_payment->setParameter('x_tran_key', $pConfig->an_transkey);
		$cc_payment->setParameter('x_email_customer', $pConfig->an_email_customer);
		$cc_payment->setParameter('x_merchant_email', $pConfig->an_merchant_email);
		$cc_payment->setParameter('x_email_merchant', $pConfig->an_email_merchant);
		$cc_payment->process();
		$resArray['isApproved'] = $cc_payment->isApproved();
		$resArray['TransactionID'] = $cc_payment->getTransactionID();
		$resArray['content']= $cc_payment->getResponseText();

		if($resArray['isApproved']==true) {
			if ($TransactionType=='AUTH_CAPTURE')
			{
				if ($ARBtrialPayment==false)
				{
					$params['payment_serial_number'] = $resArray['TransactionID'];
					$return = self:: confirmOrder($order_id, $params, 0, $user_id, 'authorize');
					return $return;
				}
				else
				{
					return $resArray;
				}
			}
			elseif ($TransactionType=='AUTH_ONLY')
			{
				$return = $resArray;
			}

			return $return;
		} else {
			return self::getErrorMessage('cc', '0000', $cc_payment->getResponseText());
		}

	}
	function AuthorizeAIMVoid($TransactionID)
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'Authnet.class.php');
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		if(empty($pConfig->an_loginid) || empty($pConfig->an_transkey)) {
			return false;
		}
		if(!isset($pConfig->cc_testmode) || $pConfig->cc_testmode == true) {
			$test_mode= true;
		} else {
			$test_mode= false;
		}
		$subdomain=($test_mode) ? 'test' : 'secure';
		$cc_payment= Authnet :: instance();
		$cc_payment->url= $subdomain.".authorize.net";
		$cc_payment->setParameter('x_login', $pConfig->an_loginid);
		$cc_payment->setParameter('x_tran_key', $pConfig->an_transkey);
		$cc_payment->setParameter('x_email_customer', $pConfig->an_email_customer);
		$cc_payment->setParameter('x_merchant_email', $pConfig->an_merchant_email);
		$cc_payment->setParameter('x_email_merchant', $pConfig->an_email_merchant);
		$cc_payment->setTransactionType('VOID');
		$cc_payment->setParameter('x_trans_id', $TransactionID);
		$cc_payment->process();
		$return['isApproved'] = $cc_payment->isApproved();
		$return['ResponseText'] = $cc_payment->getResponseText();
		return $return;
	}

	function AuthorizeARBCreateProfile($orderInfo, $credit_info, $params= array())
	{
		ini_set('max_execution_time','180');
		// Test if the User's credit card has enough funding, if so, void the order;
		$result = self::AuthorizeAIMPay($orderInfo, $credit_info, $params, $TransactionType='AUTH_ONLY');
		if ($result['isApproved']==true)
		{
			$voidResult = self::AuthorizeAIMVoid($result['TransactionID']);
			if ($voidResult['isApproved']==false)
			{
				return self::getErrorMessage('cc', '0000', $voidResult['ResponseText']);
			}
		}
		else
		{
			return self::getErrorMessage('cc', '0000', $result['content']);
		}


		// Now proceed the Recurring payment plan creation;
		$db= oseDB :: instance();
		$result= array();
		$user_id= $orderInfo->user_id;
		$msc_id= $orderInfo->entry_id;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		// Get User billing information;

		$desc = $this->generateDesc($order_id);

		//$node= oseRegistry :: call('msc')->getInfo($msc_id, 'obj');
		$billingInfo= $this->getBillingInfo($orderInfo->user_id);

		// Payments
		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');
		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		*/
		$taxRate=(isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;

		// Reference ID;
		$refID= substr($order_number, 0, 19)."A";
		$invoice= substr($order_number, 0, 19)."A";
		$name= "MEM{$msc_id}UID{$user_id}_".date("Ymdhis");
		$taxRate= $payment->tax_rate;

		// Credit Card Informaiton;
		$creditcard= $credit_info["creditcard_number"];
		$cardCode= $credit_info["creditcard_cvv"];
		$expiration= $credit_info["creditcard_expirationdate"];
		$expiration= strval($expiration);

		// Recurring payment setting;
		$msc= oseRegistry :: call('msc');
		$ext= $orderInfoParams;//msc->getExtInfo($msc_id, 'payment', 'obj');
		$mscRegRecurrence= $this->AuthorizeAPITransInterval($orderInfoParams->t3, $orderInfoParams->p3);
		$total = $orderInfoParams->next_total;//round($orderInfoParams->a3 * (1+$taxRate), 2);
		$totalOccurrences= 9999;

		// Check if Price is set correctly;
		if(empty($total)) {
				return self::getErrorMessage('cc', '0002');
		}

		// Trial payment setting;
		$trialOccurrences = (!empty($orderInfoParams->next_price)) ? "1" : "0";

		if($ext->has_trial)
		{
			$mscTrialRecurrence= $this->AuthorizeAPITransInterval($orderInfoParams->t1, $orderInfoParams->p1);
	 		if($ext->total > 0)
	 		{
	 			$result = self::AuthorizeAIMPay($orderInfo, $credit_info, $params, $TransactionType='AUTH_CAPTURE', true);
				if ($result['isApproved']==false)
				{
					return self::getErrorMessage('cc', '0000', $result['content']);
				}
	 		}

			$startDate = date("Y-m-d", strtotime("+ {$mscTrialRecurrence['length']} {$mscTrialRecurrence['unit']}"));
		}
		else
		{
			jimport('joomla.utilities.date');
			$curDate= date_create(oseHtml::getDateTime());
			$startDate= date_format($curDate,"Y-m-d" );
		}
		//oseExit($startDate);
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'AuthnetARB.class.php');
		$config= oseMscConfig :: getConfig('payment', 'obj');
		$test_mode= $config->cc_testmode;
		$arbsubdomain=($test_mode) ? 'apitest' : 'api';

		$arb = new AuthnetARB();
		$arb->url= $arbsubdomain.".authorize.net";
		$arb->setParameter('startDate', $startDate);
		$arb->setParameter('interval_length', $mscRegRecurrence['length']);
		$arb->setParameter('interval_unit', $mscRegRecurrence['unit']);
		$arb->setParameter('totalOccurrences', 9999);
		$arb->setParameter('amount', $total);
		$arb->setParameter('trialOccurrences', 0);
		$arb->setParameter('trialAmount', 0.00);
		$arb->setParameter('orderInvoiceNumber', $refID);
		$arb->setParameter('orderDescription', $desc);
		$arb->setParameter('customerId', $user_id);
		$arb->setParameter('customerEmail', $billingInfo->email);
		$arb->setParameter('customerPhoneNumber', substr($billingInfo->telephone, 0, 25));
		$arb->setParameter('customerFaxNumber', substr($billingInfo->fax, 0, 25));
		$arb->setParameter('refID', $refID);
		$arb->setParameter('cardNumber', $creditcard);
		$arb->setParameter('expirationDate', $expiration);
		$arb->setParameter('cardCode', $cardCode);
		$arb->setParameter('firstName', substr($billingInfo->firstname, 0, 50));
		$arb->setParameter('lastName', substr($billingInfo->lastname, 0, 50));
		$arb->setParameter('address', substr($billingInfo->addr1, 0, 60));
		$arb->setParameter('city', substr($billingInfo->city, 0, 60));
		$arb->setParameter('state', substr($billingInfo->state, 0, 40));
		$arb->setParameter('zip', substr($billingInfo->postcode, 0, 20));
		$arb->setParameter('email', $billingInfo->email);
		$arb->setParameter('subscrName', $name);
		// Assgin login credentials
		$arb->setParameter('login', $config->an_loginid);
		$arb->setParameter('transkey', $config->an_transkey);
        $arb->setParameter('refID', $refID);
        $arb->setParameter('subscrId', $order_number);

		// Create the recurring billing subscription
		$arb->createAccount();
		$return =array();
		if ($arb->isSuccessful()==true)
		{
			$params['payment_serial_number'] = $arb->getSubscriberID();
			$return = self:: confirmOrder($order_id, $params, $msc_id, $user_id, 'authorize');
		}
		else {
			return self::getErrorMessage('cc', '0000', $arb->getResponse());
		}
		return $return;
	}

	function AuthorizeARBDeleteProfile($payment_serial_number, $refID, $user_id, $msc_id = 0) {
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."libraries".DS."class.connection.php");
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."libraries".DS."AuthnetARB.class.php");
		$arb= new AuthnetARB();
		$config= oseMscConfig :: getConfig('payment', 'obj');
		$test_mode= $config->cc_testmode;
		$an_loginid= $config->an_loginid;
		$an_transkey= $config->an_transkey;
		$an_email_customer= $config->an_email_customer;
		$an_email_merchant= $config->an_email_merchant;
		$an_merchant_email= $config->an_merchant_email;
		$ProfileID = $payment_serial_number;//self:: GetProfileID($order_number);
		$arbsubdomain=($test_mode) ? 'apitest' : 'api';
		$arb->url= $arbsubdomain.".authorize.net";
		$arb->setParameter('login', $an_loginid);
		$arb->setParameter('transkey', $an_transkey);
		$arb->setParameter('refID', $refID);
		$arb->setParameter('subscrId', $ProfileID);
		$arb->deleteAccount();
		$result= array();
		//$result['resultCode'] = $arb->resultCode;
		$result['code']= $arb->getResponseCode();
		$result['text']= $arb->getResponse();
		$result['subscrId']= $arb->getSubscriberID();
		$result['success']= $arb->isSuccessful();
		return $result;
	}
	function AuthorizeAPITransInterval($t, $p) {
		$results= array();
		$t= strtolower($t);
		switch($t) {
			case "year" :
				$results['length']= $p * 12;
				$results['unit']= 'months';
				$results['unit2']= 'month';
				break;
			case "month" :
				$results['length']= $p;
				$results['unit']= 'months';
				$results['unit2']= 'month';
				break;
			case "week" :
				$results['length']= $p * 7;
				$results['unit']= 'days';
				$results['unit2']= 'day';
				break;
			case "day" :
				if($p) {
					$results['length']= $p;
					$results['unit']= 'days';
					$results['unit2']= 'day';
				}
				break;
		}
		return $results;
	}

	function AuthorizeARBCheckStatus($orderId)
	{
		$db = &JFactory::getDBO();
	  	$query = " SELECT `order_number` FROM `#__osemsc_order` "
	  			." WHERE `order-id`= '{$orderId}'"
	  			;
	  	$db->setQuery($query);
	  	$ProfileID = $db->loadResult();
	  	if (!empty($ProfileID))
	  	{
	  		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'AuthnetARB.class.php');
	  		$arb = new AuthnetARB();
	  		$arb->setParameter('subscrId', $ProfileID);
	  		$arb->getSubscriptionStatus();
	  		return $arb->status;
	  	}
	}


	function confirmOrder($order_id,$params = array())
	{
		$db= oseDB::instance();

		$where = array();
		$where[] = '`order_id` = '.$db->Quote($order_id);
		$orderInfo = $this->getOrder($where,'obj');

		if(!isset($params['params']))
		{
			$params['params'] = oseJson::decode($orderInfo->params);
			$params['params']->recurrence_times = 1+oseObject::getValue($params['params'],'recurrence_times',0);
			$params['params'] = oseJson::encode($params['params']);
		}

		$this->updateOrder($order_id, "confirmed", $params);

		$user_id = $orderInfo->user_id;
		$payment_mode = $orderInfo->payment_mode;
		$payment_method = $orderInfo->payment_method;

		$user = new JUser($user_id);
		$email = $user->get('email');

		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$orderInfo->order_id}'"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj');

		foreach($items as $item)
		{
			switch($item->entry_type)
			{
				case('license'):
					$license = oseRegistry::call('lic')->getInstance(0);
					$licenseInfo = $license->getKeyInfo($item->entry_id,'obj');

					$licenseInfoParams = oseJson::decode($licenseInfo->params);

					$msc_id = $licenseInfoParams->msc_id;
					$updated= $this->joinMsc($order_id,$item->order_item_id, $msc_id, $user_id);
				break;

				default:
				case('msc'):
					$updated = $this->joinMsc($order_id,$item->order_item_id, $item->entry_id, $user_id);
				break;
			}

			if(!$updated['success'])
			{
				return $updated;
			}
		}

		//Auto reucrring email control
		$emailConfig = oseMscConfig::getConfig('email','obj');
		$send=true;
		$orderparams = oseJson::decode($params['params']);
		$recurrence_times = oseObject::getValue($orderparams,'recurrence_times',1);
		if($recurrence_times > 1 && oseObject::getValue($emailConfig,'sendReceiptOnlyOneTime',false))
		{
			if($orderparams->has_trial)
			{
				$send=false;

			}
			else
			{
				if($recurrence_times>2)
				{
					$send=false;
				}
			}
		}

		if($send)
		{
			$memEmail = oseRegistry::call('member')->getInstance('Email');
			$receipt = $memEmail->getReceipt($orderInfo);
			$memEmail->sendEmail($receipt,$email);

			if(!empty($emailConfig->sendReceipt2Admin))
			{
				$memEmail->sendToAdminGroup($receipt,$emailConfig->admin_group);
			}
		}
		/*
		$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=member'";
		$db->setQuery($query);
		$result= $db->loadResult();
		
		if(empty($result))
		{
			$return_url= "index.php?option=com_osemsc&view=member";
		}
		else
		{
			$return_url= "index.php?option=com_osemsc&view=member&Itemid=".$result;
		}
		*/
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$session = JFactory::getSession();
		$return_url = (isset($orderInfoParams->returnUrl))?urldecode($orderInfoParams->returnUrl):"index.php";
		//$session = JFactory::getSession();
		//$return_url = $session->get('oseReturnUrl');
		$return['success']= true;
		$return['payment']= $payment_method;
		$return['title']= JText :: _('Success');
		$return['content']= JText :: _(' Your membership is activated successfully. Please click the OK button to continue');
		$return['url']= $return_url;
		$return['returnUrl']= $return_url;

		$this->updateOrder($order_id, "confirmed");

		//osePayment::getInstance('Cart')->init();
		return $return;
	}


	/*
	function check_crdate($expiration, $startDate) {
		$result= array();
		$db= oseDB :: instance();
		$date= new DateTime($expiration);
		$expiration= $date->format('Y-m-d');
		$query= " SELECT 1 FROM `#_osemsc_acl`"." WHERE {$expiration} > {$startDate}"." LIMIT 1";
		$db->setQuery($query);
		$isValid= $db->loadResult();
		if(empty($query)) {
			$result['success']= false;
			$result['title']= JText :: _('Payment Error');
			$result['content']= JText :: _("This credit card expires before the subscription is created. Please try another credit card. There are no payments made and your credit card will not be charged for this transaction.");
			$result= oseJson :: encode($result);
			//oseExit($result);
		}
		return true;
	}

	function get_order_id($order_number) {
		$db= & JFactory :: getDBO();
		$query= "SELECT order_id FROM `#__osemsc_orders` WHERE `order_number` = '".$order_number."'";
		$db->setQuery($query);
		$result= $db->loadResult();
		return $result;
	}




	function get_discounted_price($msc_id, $amount, $user_id, $user_promotion_code= null, $type= null) {
		$mscinfo= osePaymentOrder :: get_mscinfo($msc_id);
		$parameters= & JComponentHelper :: getParams('com_osemsc');
		$test_mode= $parameters->get('test_mode');
		$coupon_code= $parameters->get('coupon_code');
		$enable_coupon= $parameters->get('enable_coupon');
		$coupon_discounts= $parameters->get('coupon_discounts');
		$renewal_discounts= $mscinfo->renewal_discounts;
		//$user_promotion_code = empty ($user_promotion_code) ? $_SESSION['promotion_code'] : $user_promotion_code;
		if($enable_coupon) {
			if(!empty($coupon_code)) {
				$promotion_code= $coupon_code;
				$promotion_discounts= $coupon_discounts;
			} else {
				$promotion_code= $mscinfo->promotion_code;
				$promotion_discounts= $mscinfo->promotion_discounts;
			}
			if($user_promotion_code == $promotion_code) {
				$amount= $amount *(1 - $promotion_discounts / 100);
			}
		}
		$donation= JRequest :: getVar('donation', array(), 'post', 'array');
		$donation= !isset($donation[$msc_id]) ? 0 : $donation[$msc_id];
		// Check if the user adds donations or not;
		//print_r( $amount );exit;
		// Check if the user is a member of the membership
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."warehouse".DS."api.php");
		$api= new OSEMSCAPI();
		if($api->is_member($msc_id, $user_id) == true) {
			if(!empty($renewal_discounts)) {
				$amount= $amount *(1 - $renewal_discounts / 100);
			}
		}
		// Renewal discounts ends
		if($type != 'a3') {
			if($mscinfo->donation == '0') {
				$amount= $amount;
			}
			elseif($mscinfo->donation == '1') {
				$amount= $amount + $donation;
			} else {
				$amount= $donation;
			}
		}
		return $amount;
	}
	*/
	function addVmOrder($msc_id, $member_id, $params, $order_number) {
		if(empty($member_id)) {
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
			return $result;
		}
		// Get the IP Address
		if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip= $_SERVER['REMOTE_ADDR'];
		} else {
			$ip= 'unknown';
		}
		$post= JRequest :: get('post');
		$payment_mode= $params['payment_mode'];
		$payment_method= $params['payment_method'];
		//Insert the vm order table(#__vm_orders)
		$order= array();
		//get membership price
		$payment= oseRegistry :: call('payment');
		$paymentInfo= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		if($payment_mode == 'm') {
			$order_subtotal= $paymentInfo->price;
		} else {
			$order_subtotal=(empty($paymentInfo->has_trial)) ? $paymentInfo->a3 : $paymentInfo->a1;
		}
		$order['order_subtotal']= $params['payment_price'];
		$order_total= $params['payment_price'];
		$order['order_total']= $order_total;
		$db= oseDB :: instance();
		//$order['order_tax'] = '0.00';
		$query= "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = '".(int) $member_id."'  AND (`address_type` = 'BT' OR `address_type` IS NULL)";
		$db->setQuery($query);
		$result= $db->loadResult();
		$hash_secret= "VirtueMartIsCool";
		$user_info_id= empty($result) ? md5(uniqid($hash_secret)) : $result;
		$vendor_id= '1';
		$order['user_id']= $member_id;
		$order['vendor_id']= $vendor_id;
		$order['user_info_id']= $user_info_id;
		$order['order_number']= $order_number;
		$order['order_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$order['order_status']= 'C';
		$order['cdate']= time();
		$order['ip_address']= $ip;
		$keys= array_keys($order);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($order as $key => $value) {
			$order[$key]= $db->Quote($value);
		}
		$values= implode(',', $order);
		$query= "INSERT INTO `#__vm_orders` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the #__vm_order_history table
		$order_id= $db->insertid();
		$history= array();
		$history['order_id']= $order_id;
		$history['order_status_code']= 'C';
		$history['date_added']= date("Y-m-d G:i:s", time());
		$history['customer_notified']= '1';
		$keys= array_keys($history);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($history as $key => $value) {
			$history[$key]= $db->Quote($value);
		}
		$values= implode(',', $history);
		$query= "INSERT INTO `#__vm_order_history` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the Order payment info
		$payment= array();
		$payment['order_id']= $order_id;
		$payment['payment_method_id']= $payment_method;
		if($payment_method == 'authorize') {}
		//Insert the User Bill
		$bill= array();
		$query= " SELECT * FROM `#__osemsc_billinginfo`"." WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$billInfo= oseDB :: loadItem();
		if(isset($billInfo)) {
			$bill['company']= $billInfo['company'];
			$bill['address_1']= $billInfo['addr1'];
			$bill['address_2']= $billInfo['addr2'];
			$bill['city']= $billInfo['city'];
			$bill['state']= $billInfo['state'];
			$bill['country']= $billInfo['country'];
			//get vm country code
			$query= " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$bill['country']}' ";
			$db->setQuery($query);
			$country_code= $db->loadResult();
			$bill['country']= empty($country_code) ? $bill['country'] : $country_code;
			//get vm state code
			$query= " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$bill['state']}' ";
			$db->setQuery($query);
			$state_code= $db->loadResult();
			$bill['state']= empty($state_code) ? $bill['state'] : $state_code;
			$bill['zip']= $billInfo['postcode'];
			$bill['phone_1']= $billInfo['telephone'];
		}
		$query= " SELECT * FROM `#__osemsc_userinfo_view`"." WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$userInfo= oseDB :: loadItem();
		$bill['order_id']= $order_id;
		$bill['user_id']= $member_id;
		$bill['address_type']= 'BT';
		$bill['address_type_name']= '-default-';
		$bill['last_name']= $userInfo['lastname'];
		$bill['first_name']= $userInfo['firstname'];
		$bill['user_email']= $userInfo['email'];
		$keys= array_keys($bill);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($bill as $key => $value) {
			$bill[$key]= $db->Quote($value);
		}
		$values= implode(',', $bill);
		$query= "INSERT INTO `#__vm_order_user_info` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the itme table(#__vm_order_item)
		$item= array();
		$item['order_id']= $order_id;
		$item['user_info_id']= $user_info_id;
		$item['vendor_id']= $vendor_id;
		//get the product info
		$vm= oseMscAddon :: getExtInfo($msc_id, 'vm', 'obj');
		$query= " SELECT * FROM `#__vm_product` WHERE `product_id` = '{$vm->product_id}' ";
		$db->setQuery($query);
		$product= $db->loadObject();
		$item['product_id']= $vm->product_id;
		$item['order_item_sku']= $product->product_sku;
		$item['order_item_name']= $product->product_name;
		$item['product_quantity']= '1';
		$item['product_item_price']= $order_subtotal;
		$item['product_final_price']= $order_total;
		$item['order_item_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$item['order_status']= 'C';
		$item['cdate']= time();
		;
		$keys= array_keys($item);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($item as $key => $value) {
			$item[$key]= $db->Quote($value);
		}
		$values= implode(',', $item);
		$query= "INSERT INTO `#__vm_order_item` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= JText :: _('Done');
		return $result;
	}

	function autoOrderParams($payment_mode= 'a', $orderParams, $isNew= true, $status= 'confirmed')
	{
		$params= array();
		$orderParams= oseJson :: decode($orderParams);
		if($payment_mode == 'a') {
			if($isNew || $status == 'confirmed') {
				if(!isset($orderParams->recurrence_times))
				{
					if($orderParams->has_trial) {
						$recurrence_times= 0;
					} else {
						$recurrence_times= 1;
					}
					$orderParams->recurrence_times= $recurrence_times;
				} else {
					$orderParams->recurrence_times += 1;
				}
			}
		} else {}
		return oseJson :: encode($orderParams);
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

	function updateOrder($order_id, $status, $params= array())
	{
		$db= oseDB :: instance();
		$params['order_status']= $status;

		/*
		if(isset($params['params'])) {
			$params['params']= $this->autoOrderParams($params['payment_mode'], $params['params'], false, $status);
		} else {
			$orderInfo= $this->getOrder(array('order_id' => $order_id));
			$params['params']= $this->autoOrderParams($orderInfo['payment_mode'], $orderInfo['params'], false, $status);
		}
		*/
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= '`'.$key.'`='.$db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " UPDATE `{$this->table}` "." SET {$values}"." WHERE order_id = {$order_id}";
		$db->setQuery($query);
		//oseExit($db->getQuery());
		if(oseDB :: query())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function updateOrderParams($orderInfo,$params)
	{
		$orderInfoParams = oseObject::getValue($orderInfo,'params');
		$orderInfoParams = oseJson::decode($orderInfoParams);


		if(!is_Array($params))
		{
			$params = (array)$params;
		}

		foreach($params as $key => $value)
		{
			$orderInfoParams = oseObject::setValue($orderInfoParams,$key,$value);
		}

		$orderInfoParams = oseJson::encode($orderInfoParams);

		$orderInfo = oseObject::setValue($orderInfo,'params',$orderInfoParams);

		return $orderInfo;

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

	function getOrderItem($where = array(),$type = 'array')
	{
		$db= oseDB :: instance();
		$where= oseDB :: implodeWhere($where);

		$where = str_replace('order_id','order_item_id',$where);
		$query= " SELECT * FROM `#__osemsc_order_item` "
				. $where
				.' ORDER BY create_date DESC'.' LIMIT 1'
				;
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}

	function getOrderItems($order_id,$type = 'array')
	{
		$db= oseDB :: instance();
		$where = array();
		$where[] = '`order_id`='.$db->Quote($order_id);
		$where= oseDB :: implodeWhere($where);

		$query= " SELECT * FROM `#__osemsc_order_item` "
				. $where
				.' ORDER BY create_date ASC'
				;
		$db->setQuery($query);
		$items= oseDB :: loadList($type);
		return $items;
	}

	function generateDesc($order_id)
	{
		return JText::_('Payment for Order :'.$order_id);
		/*
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$order_id}'"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj');

		$desc = array();
		foreach($items as $item)
		{
			switch($item->entry_type)
			{
				case('msc'):
					$msc_id = $item->entry_id;
				break;

				case('license');
					$itemParams = oseJson::decode($item->params);
					$msc_id = $itemParams->msc_id;
				break;
			}
			$node= oseRegistry :: call('msc')->getInfo($msc_id, 'obj');
			$desc[] = $node->title;
		}
		$desc = implode(',',$desc);

		return $desc;
		*/
	}

	function refundOrder($order_id,$params = array())
	{
		$db= oseDB::instance();

		$this->updateOrder($order_id, "refunded", $params);


		$where = array();
		$where[] = '`order_id` = '.$db->Quote($order_id);
		$orderInfo = $this->getOrder($where,'obj');

		$user_id =  $orderInfo->user_id;

		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$orderInfo->order_id}'"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj');

		foreach($items as $item)
		{
			switch($item->entry_type)
			{
				case('license'):
					$license = oseRegistry::call('lic')->getInstance(0);
					$licenseInfo = $license->getKeyInfo($item->entry_id,'obj');

					$licenseInfoParams = oseJson::decode($licenseInfo->params);

					$msc_id = $licenseInfoParams->msc_id;
					$updated= $this->cancelMsc($order_id,$item->order_item_id, $msc_id, $user_id);
				break;

				default:
				case('msc'):
					$updated = $this->cancelMsc($order_id,$item->order_item_id, $item->entry_id, $user_id);
				break;
			}

			if(!$updated['success'])
			{
				return $updated;
			}
		}

		$return['success']= true;
		$return['title']= JText :: _('Success');
		$return['content']= JText :: _('Refunded Successfully');

		$session = JFactory::getSession();
		$session->set('osecart',array());
		return $return;
	}

	private function cancelMsc($order_id,$order_item_id, $msc_id, $user_id)
	{
		$params= oseRegistry :: call('member')->getAddonParams($msc_id, $user_id, $order_id,array('order_item_id'=>$order_item_id));

		$member= oseRegistry :: call('member');
		$member->instance($user_id);

		$msc= oseRegistry :: call('msc');

		$ext = $msc->getExtInfo($msc_id,'msc','obj');

		$updated = $msc->runAddonAction('member.msc.cancelMsc', $params,true,false);
		/*
		if($ext->cancel_email)
		{

			$email = $member->getInstance('email');

			$emailTempDetail = $email->getDoc($ext->cancel_email,'obj');

			$variables = $email->getEmailVariablesCancel($user_id,$msc_id);

			$emailParams = $email->buildEmailParams($emailTempDetail->type);

			$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
		}

		if($ext->cancel_email)
		{
			$email->sendEmail($emailDetail,$userInfo->email);

			$emailConfig = oseMscConfig::getConfig('email','obj');
			if($emailConfig->sendCancel2Admin)
			{
				$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
			}
		}
		*/
		return $updated;
	}
}
?>