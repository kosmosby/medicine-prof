<?php
defined('_JEXEC') or die(";)");
class osePaymentOrder extends oseObject {
	protected $table = '#__osemsc_order';
	protected $tableOrder = '#__ose_payment_order';
	protected $tableOrderItem = '#__ose_payment_order_item';
	function __construct($table = null) {
		if (!empty($table)) {
			$this->table = $table;
		}
	}
	private function joinMsc($order_id, $order_item_id, $msc_id, $user_id) {
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$params = $member->getAddonParams($msc_id, $user_id, $order_id, array('order_item_id' => $order_item_id));
		// is Member, renew... else join.
		$memMscInfo = $member->getMembership($msc_id, 'obj');
		if (empty($memMscInfo)) {
			//oseExit('haha');
			$msc = oseRegistry::call('msc');
			$updated = $msc->getInstance('Addon')->runAction('member.msc.joinMsc', $params, true, false);
		} else {
			// renew
			if ($memMscInfo->status) {
				//oseExit('renew');
				//$memInfo= oseRegistry :: call('member')->getMemberInfo($msc_id, 'obj');
				$memParams = oseJson::decode($memMscInfo->params);
				if (!empty($memParams->order_id)) {
					//oseRegistry :: call('payment')->updateOrder($memParams->order_id, 'expired');
				}
				$msc = oseRegistry::call('msc');
				$updated = $msc->getInstance('Addon')->runAction('member.msc.renewMsc', $params, true, false);
			} else {
				//oseExit('activate');
				$msc = oseRegistry::call('msc');
				$updated = $msc->getInstance('Addon')->runAction('member.msc.activateMsc', $params, true, false);
			}
		}
		return $updated;
	}
	function generateOrder($msc_id, $user_id, $params = array()) {
		$db = oseDB::instance();
		$params['create_date'] = (empty($params['create_date'])) ? oseHTML::getDateTime() : $params['create_date'];
		$keys = array_keys($params);
		$keys = '`' . implode('`,`', $keys) . '`';
		$values = array();
		foreach ($params as $key => $value) {
			$values[$key] = $db->Quote($value);
		}
		$msc_id = (empty($msc_id)) ? 0 : $msc_id;
		$values = implode(',', $values);
		$query = " INSERT INTO `{$this->table}` " . " (`user_id`,`entry_id`,{$keys}) " . " VALUES " . " ( '{$user_id}', " . (int) $msc_id . ", {$values})";
		$db->setQuery($query);
		if (oseDB::query()) {
			$order_id = $db->insertid();
			$orderParams = $this->autoOrderParams($params['payment_mode'], $params['params']);
			$this->updateOrder($order_id, 'pending', array('params' => $orderParams, 'payment_mode' => $params['payment_mode']));
			return $order_id;
		} else {
			return false;
		}
	}
	function generateOrderItem($order_id, $entry_id, $params = array()) {
		$db = oseDB::instance();
		$params['create_date'] = (empty($params['create_date'])) ? oseHTML::getDateTime() : $params['create_date'];
		$keys = array_keys($params);
		$keys = '`' . implode('`,`', $keys) . '`';
		$values = array();
		foreach ($params as $key => $value) {
			$values[$key] = $db->Quote($value);
		}
		$values = implode(',', $values);
		$query = " INSERT INTO `{$this->table}_item` " . " (`order_id`,`entry_id`,{$keys}) " . " VALUES " . " ('{$order_id}' , '{$entry_id}', {$values})";
		$db->setQuery($query);
		if (oseDB::query()) {
			$order_id = $db->insertid();
			return $order_id;
		} else {
			return false;
		}
	}
	public static function generateOrderNumber($user_id) {
		$length = 31 - strlen($user_id);
		$order_number = $user_id . "_" . self::randStr($length, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890");
		$db = oseDB::instance();
		$query = "SELECT COUNT(*) FROM `#__osemsc_order` WHERE `order_number` = " . $db->Quote($order_number);
		$db->setQuery($query);
		$result = $db->loadResult();
		if (empty($result)) {
			return $order_number;
		} else {
			$order_number = self::generateOrderNumber($user_id);
			return $order_number;
		}
	}
	function generateOrderParams($msc_id, $price, $payment_mode, $msc_option) {
		$params = array();
		$params['msc_id'] = $msc_id;
		if ($payment_mode == 'a') {
			$payment = oseMscAddon::getExtInfo($msc_id, 'payment', 'array');
			$payment = $payment[$msc_option];
			if (oseObject::getValue($payment, 'has_trial')) {
				$params['has_trial'] = 1;
				$params['a1'] = $price;
				$params['p1'] = oseObject::getValue($payment, 'p1');
				$params['t1'] = oseObject::getValue($payment, 't1');
				$params['a3'] = oseObject::getValue($payment, 'a3');
				$params['p3'] = oseObject::getValue($payment, 'p3');
				$params['t3'] = oseObject::getValue($payment, 't3');
			} else {
				$params['has_trial'] = 0;
				$params['a3'] = $price;
				$params['p3'] = oseObject::getValue($payment, 'p3');
				$params['t3'] = oseObject::getValue($payment, 't3');
			}
		}
		if ($payment_mode == 'm') {
			$payment = oseMscAddon::getExtInfo($msc_id, 'payment', 'array');
			$payment = $payment[$msc_option];
			$params['recurrence_mode'] = 'period';
			$params['a3'] = $price;
			$params['p3'] = oseObject::getValue($payment, 'p3');
			$params['t3'] = oseObject::getValue($payment, 't3');
			$params['eternal'] = oseObject::getValue($payment, 'eternal');
		}
		$params['msc_option'] = $msc_option;
		return $params;
	}
	function getErrorMessage($paymentMethod, $code, $message = null) {
		$return = array();
		$return['payment'] = $paymentMethod;
		$return['success'] = false;
		$return['title'] = JText::_('Error');
		switch ($code) {
		case '0000':
			$return['content'] = $message;
			break;
		case '0001':
			$return['content'] = JText::_(
					"This transaction utilizes Authorize.net as the payment processor, which does not support non-USD currency. Please choose USD as your payment currency.");
			break;
		case '0002':
			$return['content'] = JText::_("Please check your membership setting. Membership Price cannot be empty.");
			break;
		case '0003':
			$return['content'] = JText::_("Authorize.net is not enabled, please enable it through OSE backend.");
			break;
		}
		return $return;
	}
	function getItemid() {
		$db = oseDB::instance();
		$query = "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=confirm%'";
		$db->setQuery($query);
		$Itemid = $db->loadResult();
		if (empty($Itemid)) {
			$query = "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=register%'";
			$db->setQuery($query);
			$Itemid = $db->loadResult();
		}
		return $Itemid;
	}
	function getProfileID($order_number) {
		$db = oseDB::instance();
		$query = "SELECT `payment_serial_number` FROM `#__osemsc_order` WHERE `order_number`= '{$order_number}'";
		$db->setQuery($query);
		$ProfileID = $db->loadResult();
		return $ProfileID;
	}
	function getOrder($where = array(), $type = 'array') {
		$db = oseDB::instance();
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `{$this->table}` " . $where . ' ORDER BY create_date DESC' . ' LIMIT 1';
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	function PaypalAPIUpdateCreditCard($orderInfo, $credit_info) {
		require_once(JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "libraries" . DS . "class.connection.php");
		$postVar = array();
		$postVar['PROFILEID'] = $orderInfo->payment_serial_number;//urldecode($ProfileID);
		switch ($credit_info["creditcard_type"]) {
		case "VISA":
			$postVar['CREDITCARDTYPE'] = "Visa";
			break;
		case "MC":
			$postVar['CREDITCARDTYPE'] = "MasterCard";
			break;
		}
		$postVar['ACCT'] = $credit_info["creditcard_number"];
		$creditCardExpiryDate = $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate = explode("-", strval($creditCardExpiryDate));
		$creditCardExpiryDate = $creditCardExpiryDate[1] . $creditCardExpiryDate[0];
		$postVar['EXPDATE'] = $creditCardExpiryDate;
		$postVar['CVV2'] = $credit_info["creditcard_cvv"];
		$postString = 'METHOD=' . urlencode('UpdateRecurringPaymentsProfile');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		$result = array();
		if ($resArray['ACK'] == 'Success') {
			$result['content'] = '';
			$result['success'] = true;
		} else {
			$result['content'] = urldecode($resArray['L_ERRORCODE0']) . " - " . urldecode($resArray['L_LONGMESSAGE0']);
			$result['success'] = false;
		}
		return $result;
	}
	function PaypalAPICCPay2($orderInfo, $credit_info, $params = array()) {
		$db = oseDB::instance();
		$result = array();
		$user_id = $orderInfo->user_id;
		$msc_id = $orderInfo->entry_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$taxRate = 0;
		$msc_name = $desc;
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = &JFactory::getUser($orderInfo->user_id);
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$Itemid = self::getItemid();
		$postVar = array();
		if ($orderInfo->payment_mode == 'm') {
			$postVar['ADDRESSOVERRIDE'] = 0;
			$postVar['PAYMENTACTION'] = 'Sale';
			$postVar['CURRENCYCODE'] = $currency;
			$postVar['TAXAMT'] = $taxRate / 100 * $amount;
			$postVar['ITEMAMT'] = $amount;
			$postVar['AMT'] = $amount + $postVar['TAXAMT'];
			$postString = 'METHOD=' . urlencode('doDirectPayment');
		} else {
			$orderInfoParams = oseJson::decode($orderInfo->params);
			$curDate = oseHTML::getDateTime();
			$a3 = $orderInfoParams->next_total;
			$p3 = $orderInfoParams->p3;
			$t3 = $orderInfoParams->t3;
			$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			if ($orderInfoParams->has_trial) {
				$a1 = $orderInfoParams->total;
				$p1 = $orderInfoParams->p1;
				$t1 = $orderInfoParams->t1;
				$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$postVar['INITAMT'] = $orderInfoParams->total;
				$startDate = date("Y-m-d h:i:s", strtotime("+ {$p1} {$t1}", strtotime($curDate)));
			} else {
				$postVar['INITAMT'] = $orderInfoParams->total;
				$startDate = date("Y-m-d h:i:s", strtotime("+ {$p3} {$t3}", strtotime($curDate)));
			}
			$postVar['L_BILLINGTYPE0'] = 'RecurringPayments';
			$postVar['L_BILLINGAGREEMENTDESCRIPTION0'] = JText::_('ORDER_ID') . " " . $order_id;
			$postVar['DESC'] = JText::_('ORDER_ID') . " " . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . " " . $msc_name;
			$postVar['BILLINGPERIOD'] = $t3;
			$postVar['BILLINGFREQUENCY'] = $p3;
			$postVar['TOTALBILLINGCYCLES'] = 0;
			$postVar['TAXAMT'] = 0;//$taxRate / 100 * $a3;
			$postVar['AMT'] = $a3 + $postVar['TAXAMT'];
			$postVar['MAXFAILEDPAYMENTS'] = 2;
			$postVar['PROFILESTARTDATE'] = date("Y-m-d h:i:s", strtotime($startDate));
			$postString = 'METHOD=' . urlencode('CreateRecurringPaymentsProfile');
		}
		// Credit Card Information;
		switch ($credit_info["creditcard_type"]) {
		case "VISA":
			$postVar['CREDITCARDTYPE'] = "Visa";
			break;
		case "MC":
			$postVar['CREDITCARDTYPE'] = "MasterCard";
			break;
		}
		$postVar['ACCT'] = $credit_info["creditcard_number"];
		$creditCardExpiryDate = $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate = explode("-", strval($creditCardExpiryDate));
		$creditCardExpiryDate = $creditCardExpiryDate[1] . $creditCardExpiryDate[0];
		$postVar['EXPDATE'] = $creditCardExpiryDate;
		$postVar['CVV2'] = $credit_info["creditcard_cvv"];
		// Billing Information;
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$postVar['FIRSTNAME'] = $billingInfo->firstname;
		$postVar['LASTNAME'] = $billingInfo->lastname;
		$postVar['STREET'] = $billingInfo->addr1;
		$postVar['CITY'] = $billingInfo->city;
		$postVar['STATE'] = $billingInfo->state;
		$postVar['ZIP'] = $billingInfo->postcode;
		$postVar['COUNTRY'] = $billingInfo->country;
		$postVar['COUNTRYCODE'] = $billingInfo->countrycode;
		$postVar['CURRENCYCODE'] = $currency;
		$postVar['EMAIL'] = $user->email;
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		// Return if empty;
		if (empty($resArray)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		$return = array();
		if ($resArray['ACK'] == 'Success') {
			// Update Order Number to Paypal Transaction ID
			$orderInfoParams = oseJson::decode($orderInfo->params);
			if (!empty($resArray['PROFILEID'])) {
				$oseMscConfig = oseMscConfig::getConfig('payment', 'obj');
				if (oseObject::getValue($oseMscConfig, 'paypal_pro_access', 'instant') == 'instant') {
					// confirm
					$this->confirmOrder($order_id, $params);
					$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
					$this->updateOrder($order_id, 'confirmed', $params);
				} elseif (empty($orderInfoParams->total) || $orderInfoParams->total == '0.00') {
					$this->confirmOrder($order_id, $params);
					$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
					$this->updateOrder($order_id, 'confirmed', $params);
				} else {
					// confirm until paid
					$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
					$this->updateOrder($order_id, 'pending', $params);
				}
				$return['success'] = true;
				$return['title'] = JText::_('SUCCESSFUL_ACTIVATION');
				$return['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE') . ' It will activate in a few minutes later';
				$return_url = (isset($orderInfoParams->returnUrl)) ? urldecode($orderInfoParams->returnUrl) : "index.php";
				$return['url'] = $return_url;
				$return['returnUrl'] = $return_url;
			} elseif (!empty($resArray['TRANSACTIONID'])) {
				$params['payment_serial_number'] = $resArray['TRANSACTIONID'];
				$return = self::confirmOrder($order_id, $params);
			} else {
				$return['success'] = false;
				$return['title'] = JTExt::_('ERROR');
				$return['success'] = JTExt::_('ERROR');
			}
			return $return;
		} else {
			return self::getErrorMessage('paypal_cc', '0000', urldecode($resArray['L_LONGMESSAGE0']));
		}
	}
	function PaypalAPICCPay($orderInfo, $credit_info, $params = array()) {
		$oseMscConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		if ($oseMscConfig->paypal_pro_mode == 'aimcap_arb') {
			return $this->PaypalAPICCPay2($orderInfo, $credit_info, $params);
		} else {
			return $this->PaypalAPICCPay1($orderInfo, $credit_info, $params);
		}
	}
	function PaypalAPICCPay1($orderInfo, $credit_info, $params = array()) {
		$db = oseDB::instance();
		$result = array();
		$user_id = $orderInfo->user_id;
		$msc_id = $orderInfo->entry_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$taxRate = 0;//$payment->tax_rate;
		$msc_name = $desc;//$node->title;
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = &JFactory::getUser($orderInfo->user_id);
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$Itemid = self::getItemid();
		$postVar = array();
		if ($orderInfo->payment_mode == 'm') {
			$postVar['ADDRESSOVERRIDE'] = 0;
			$postVar['PAYMENTACTION'] = 'Sale';
			$postVar['CURRENCYCODE'] = $currency;
			$postVar['TAXAMT'] = $taxRate / 100 * $amount;
			$postVar['ITEMAMT'] = $amount;
			$postVar['AMT'] = $amount + $postVar['TAXAMT'];
			$postString = 'METHOD=' . urlencode('doDirectPayment');
		} else {
			$orderInfoParams = oseJson::decode($orderInfo->params);
			//jimport('joomla.utilities.date');
			$curDate = oseHTML::getDateTime();
			if ($orderInfoParams->has_trial) {
				$a1 = $orderInfoParams->total;
				$p1 = $orderInfoParams->p1;
				$t1 = $orderInfoParams->t1;
				$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$postVar['TRIALBILLINGPERIOD'] = $t1;
				$postVar['TRIALBILLINGFREQUENCY'] = $p1;
				$postVar['TRIALTOTALBILLINGCYCLES'] = 1;
				$postVar['TRIALAMT'] = $a1;
			}
			$a3 = $orderInfoParams->next_total;
			$p3 = $orderInfoParams->p3;
			$t3 = $orderInfoParams->t3;
			$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			$postVar['L_BILLINGTYPE0'] = 'RecurringPayments';
			$postVar['L_BILLINGAGREEMENTDESCRIPTION0'] = JText::_('ORDER_ID') . " " . $order_id;
			$postVar['DESC'] = JText::_('ORDER_ID') . " " . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . " " . $msc_name;
			$postVar['BILLINGPERIOD'] = $t3;
			$postVar['BILLINGFREQUENCY'] = $p3;
			$postVar['TOTALBILLINGCYCLES'] = 0;
			$postVar['TAXAMT'] = $taxRate / 100 * $a3;
			$postVar['AMT'] = $a3 + $postVar['TAXAMT'];
			$postVar['PROFILESTARTDATE'] = date("Y-m-d h:i:s", strtotime($curDate));
			// Post String;
			$postString = 'METHOD=' . urlencode('CreateRecurringPaymentsProfile');
		}
		// Credit Card Information;
		switch ($credit_info["creditcard_type"]) {
		case "VISA":
			$postVar['CREDITCARDTYPE'] = "Visa";
			break;
		case "MC":
			$postVar['CREDITCARDTYPE'] = "MasterCard";
			break;
		}
		$postVar['ACCT'] = $credit_info["creditcard_number"];
		$creditCardExpiryDate = $credit_info["creditcard_expirationdate"];
		$creditCardExpiryDate = explode("-", strval($creditCardExpiryDate));
		$creditCardExpiryDate = $creditCardExpiryDate[1] . $creditCardExpiryDate[0];
		$postVar['EXPDATE'] = $creditCardExpiryDate;
		$postVar['CVV2'] = $credit_info["creditcard_cvv"];
		// Billing Information;
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$postVar['FIRSTNAME'] = $billingInfo->firstname;
		$postVar['LASTNAME'] = $billingInfo->lastname;
		$postVar['STREET'] = $billingInfo->addr1;
		$postVar['CITY'] = $billingInfo->city;
		$postVar['STATE'] = $billingInfo->state;
		$postVar['ZIP'] = $billingInfo->postcode;
		$postVar['COUNTRY'] = $billingInfo->country;
		$postVar['COUNTRYCODE'] = $billingInfo->countrycode;
		$postVar['CURRENCYCODE'] = $currency;
		$postVar['EMAIL'] = $user->email;
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		// Return if empty;
		if (empty($resArray)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		if ($resArray['ACK'] == 'Success') {
			// Update Order Number to Paypal Transaction ID
			if (!empty($resArray['PROFILEID']) && $resArray['PROFILESTATUS'] == 'ActiveProfile') {
				$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
			} elseif (!empty($resArray['TRANSACTIONID'])) {
				$params['payment_serial_number'] = $resArray['TRANSACTIONID'];
			} else {
				$params = array();
			}
			$return = self::confirmOrder($order_id, $params, $msc_id, $user_id, 'paypal_cc');
		} else {
			return self::getErrorMessage('paypal_cc', '0000', urldecode($resArray['L_LONGMESSAGE0']));
		}
		return $return;
	}
	function PaypalAPICreateProfile2($order_id, $token) {
		$db = oseDB::instance();
		$PaypalorderInfo = self::PaypalAPIGetOrderDetails($token);
		$orderInfo = $this->getOrder(array('`order_id`=' . $db->Quote($order_id)), 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$curDate = oseHTML::getDateTime();
		$a3 = $orderInfoParams->next_total;
		$p3 = $orderInfoParams->p3;
		$t3 = $orderInfoParams->t3;
		$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
		if ($orderInfoParams->has_trial) {
			$a1 = $orderInfoParams->total;
			$p1 = $orderInfoParams->p1;
			$t1 = $orderInfoParams->t1;
			$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
			$postVar['INITAMT'] = $orderInfoParams->total;
			$startDate = date("Y-m-d h:i:s", strtotime("+ {$p1} {$t1}", strtotime($curDate)));
		} else {
			$postVar['INITAMT'] = $orderInfoParams->total;
			$startDate = date("Y-m-d h:i:s", strtotime("+ {$p3} {$t3}", strtotime($curDate)));
		}
		$Itemid = self::getItemid();
		$postVar['L_BILLINGTYPE0'] = 'RecurringPayments';
		$postVar['L_BILLINGAGREEMENTDESCRIPTION0'] = JText::_('ORDER_ID') . " " . $order_id;
		$postVar['DESC'] = JText::_('ORDER_ID') . " " . $order_id;
		$postVar['TOKEN'] = $token;
		$postVar['PAYERID'] = $PaypalorderInfo['PAYERID'];
		$postVar['PAYMENTACTION'] = urlencode('sale');
		$postVar['BILLINGPERIOD'] = $t3;
		$postVar['BILLINGFREQUENCY'] = $p3;
		$postVar['TOTALBILLINGCYCLES'] = 0;
		$postVar['TAXAMT'] = 0;//$taxRate / 100 * $a3;
		$postVar['AMT'] = $a3 + $postVar['TAXAMT'];
		$postVar['PROFILESTARTDATE'] = date("Y-m-d h:i:s", strtotime($startDate));
		$postVar['MAXFAILEDPAYMENTS'] = 2;
		$postVar['CURRENCYCODE'] = $orderInfo->payment_currency;
		// Post String;
		$postString = 'METHOD=' . urlencode('CreateRecurringPaymentsProfile');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		if (!empty($resArray['PROFILEID'])) {
			$oseMscConfig = oseMscConfig::getConfig('payment', 'obj');
			if (oseObject::getValue($oseMscConfig, 'paypal_pro_access', 'instant') == 'instant') {
				// confirm
				$this->confirmOrder($order_id);
				$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
				$this->updateOrder($order_id, 'confirmed', $params);
			} else {
				// confirm until paid
				$user_id = $orderInfo->user_id;
				$msc_id = $orderInfo->entry_id;
				$order_id = $orderInfo->order_id;
				$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
				//$update = self::confirmOrder($order_id, $params);
				$this->updateOrder($order_id, 'pending', $params);
			}
			$resArray['success'] = true;
			//$resArray = array_merge($resArray,$update);
		} else {
			$resArray['success'] = false;
		}
		return $resArray;
	}
	function PaypalAPICreateProfile($order_id, $token) {
		$oseMscConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$oseMscConfig->paypal_pro_mode = (isset($oseMscConfig->paypal_pro_mode)) ? $oseMscConfig->paypal_pro_mode : 'aimcap_arb';
		if ($oseMscConfig->paypal_pro_mode == 'aimcap_arb') {
			return $this->PaypalAPICreateProfile2($order_id, $token);
		} else {
			return $this->PaypalAPICreateProfile1($order_id, $token);
		}
	}
	function PaypalAPICreateProfile1($order_id, $token) {
		$db = oseDB::instance();
		$PaypalorderInfo = self::PaypalAPIGetOrderDetails($token);
		$orderInfo = $this->getOrder(array('`order_id`=' . $db->Quote($order_id)), 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$curDate = oseHTML::getDateTime();
		if ($orderInfoParams->has_trial) {
			$a1 = $orderInfoParams->total;
			$p1 = $orderInfoParams->p1;
			$t1 = $orderInfoParams->t1;
			$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
			$postVar['TRIALBILLINGPERIOD'] = $t1;
			$postVar['TRIALBILLINGFREQUENCY'] = $p1;
			$postVar['TRIALTOTALBILLINGCYCLES'] = 1;
			$postVar['TRIALAMT'] = $a1;
		}
		$a3 = $orderInfoParams->next_total;
		$p3 = $orderInfoParams->p3;
		$t3 = $orderInfoParams->t3;
		$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
		$Itemid = self::getItemid();
		$postVar['L_BILLINGTYPE0'] = 'RecurringPayments';
		$postVar['L_BILLINGAGREEMENTDESCRIPTION0'] = JText::_('ORDER_ID') . " " . $order_id;
		$postVar['DESC'] = JText::_('ORDER_ID') . " " . $order_id;
		$postVar['TOKEN'] = $token;
		$postVar['PAYERID'] = $PaypalorderInfo['PAYERID'];
		$postVar['PAYMENTACTION'] = urlencode('sale');
		$postVar['BILLINGPERIOD'] = $t3;
		$postVar['BILLINGFREQUENCY'] = $p3;
		$postVar['TOTALBILLINGCYCLES'] = 0;
		$postVar['TAXAMT'] = 0;//$taxRate / 100 * $a3;
		$postVar['AMT'] = $a3 + $postVar['TAXAMT'];
		$postVar['MAXFAILEDPAYMENTS'] = 2;
		$postVar['PROFILESTARTDATE'] = date("Y-m-d h:i:s", strtotime($curDate));
		$postVar['CURRENCYCODE'] = $orderInfo->payment_currency;
		// Post String;
		$postString = 'METHOD=' . urlencode('CreateRecurringPaymentsProfile');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		if ($resArray['PROFILESTATUS'] == 'ActiveProfile') {
			$user_id = $orderInfo->user_id;
			$msc_id = $orderInfo->entry_id;
			$order_id = $orderInfo->order_id;
			$params['payment_serial_number'] = urldecode($resArray['PROFILEID']);
			$update = self::confirmOrder($order_id, $params);
			$resArray = array_merge($resArray, $update);
		} else {
			$resArray['success'] = false;
		}
		return $resArray;
	}
	function PaypalAPIDeleteProfile($ProfileID, $refID, $user_id, $msc_id = null) {
		require_once(JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "libraries" . DS . "class.connection.php");
		$postVar['PROFILEID'] = $ProfileID;
		$postVar['ACTION'] = 'Cancel';
		$postString = 'METHOD=' . urlencode('ManageRecurringPaymentsProfileStatus');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		$result = array();
		if ($resArray['ACK'] == 'Success') {
			$result['code'] = '';
			$result['text'] = '';
			$result['subscrId'] = $ProfileID;
			$result['success'] = true;
		} else {
			$result['code'] = urldecode($resArray['L_ERRORCODE0']);
			$result['text'] = urldecode($resArray['L_LONGMESSAGE0']);
			$result['subscrId'] = $ProfileID;
			$result['success'] = false;
		}
		return $result;
	}
	function PaypalAPIGetOrderDetails($token) {
		$html = array();
		$postVar = array();
		$postVar['TOKEN'] = $token;
		$postString = 'METHOD=' . urlencode('GetExpressCheckoutDetails');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . urlencode($val);
		}
		$resArray = self::PaypalAPIConnect($postString);
		// Return if empty;
		if (empty($resArray)) {
			return false;
		} elseif (strtoupper($resArray['ACK']) == 'SUCCESS' || strtoupper($resArray['ACK']) == 'SUCCESSWITHWARNING') {
			return $resArray;
		}
	}
	function PaypalAPIPay($order_id, $token) {
		$db = oseDB::instance();
		$PaypalorderInfo = self::PaypalAPIGetOrderDetails($token);
		$orderInfo = $this->getOrder(array('`order_id`=' . $db->Quote($order_id)), 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$postVar = array();
		$postVar['TOKEN'] = $token;
		$postVar['PAYERID'] = $PaypalorderInfo['PAYERID'];
		$postVar['PAYMENTACTION'] = urlencode('sale');
		$postVar['AMT'] = $PaypalorderInfo['AMT'];
		$postVar['CURRENCYCODE'] = $PaypalorderInfo['CURRENCYCODE'];
		$postVar['IPADDRESS'] = urlencode($_SERVER['SERVER_NAME']);
		$postVar['VERSION'] = VERSION;
		$postString = 'METHOD=' . urlencode('DoExpressCheckoutPayment');
		foreach ($postVar AS $key => $val) {
			$postString .= "&" . urlencode($key) . "=" . $val;
		}
		$resArray = self::PaypalAPIConnect($postString);
		if ($resArray['PAYMENTSTATUS'] == 'Completed') {
			$user_id = $orderInfo->user_id;
			$msc_id = $orderInfo->entry_id;
			$order_id = $orderInfo->order_id;
			$params['payment_serial_number'] = urldecode($resArray['TRANSACTIONID']);
			$updated = self::confirmOrder($order_id, $params);
			$resArray['success'] = true;
		} else {
			$resArray['success'] = false;
		}
		return $resArray;
	}
	function PaypalAPIPostForm($orderInfo) {
		$html = array();
		$db = oseDB::instance();
		$taxRate = 0;//$payment->tax_rate;
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = &JFactory::getUser($orderInfo->user_id);
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$msc_name = $desc;
		$Itemid = self::getItemid();
		$postVar = array();
		if ($orderInfo->payment_mode == 'm') {
			$postVar['ADDRESSOVERRIDE'] = 0;
			$postVar['PAYMENTACTION'] = 'Sale';
			$postVar['CURRENCYCODE'] = $currency;
			$postVar['L_NAME0'] = JText::_('ORDER_ID') . " " . $order_id;
			$postVar['L_DESC0'] = $msc_name;
			$postVar['L_AMT0'] = $amount;
			$postVar['L_QTY0'] = 1;
			$postVar['TAXAMT'] = $taxRate / 100 * $amount * $postVar['L_QTY0'];
			$postVar['ITEMAMT'] = $postVar['L_AMT0'] * $postVar['L_QTY0'];
			$postVar['L_DESC0'] = $msc_name . '-' . $postVar['ITEMAMT'];
			$postVar['AMT'] = $amount * $postVar['L_QTY0'] + $postVar['TAXAMT'];
			$postVar['RETURNURL'] = JURI::root() . "index.php?option=com_osemsc&view=confirm&mode=m&orderID=" . $order_id . "&Itemid=" . $Itemid;
			$postVar['CANCELURL'] = JURI::root() . "index.php";
			$postString = 'METHOD=' . urlencode('SetExpressCheckout');
			foreach ($postVar AS $key => $val) {
				$postString .= "&" . urlencode($key) . "=" . urlencode($val);
			}
		} else {
			$orderItems = $this->getOrderItems($order_id, 'obj');
			$oseMscConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
			if ($oseMscConfig->paypal_pro_mode == 'aimcap_arb') {
				$orderInfoParams = oseJson::decode($orderInfo->params);
				//jimport('joomla.utilities.date');
				$curDate = oseHTML::getDateTime();
				$a3 = $orderInfoParams->next_total;
				$p3 = $orderInfoParams->p3;
				$t3 = $orderInfoParams->t3;
				$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				if ($orderInfoParams->has_trial) {
					$a1 = $orderInfoParams->total;
					$p1 = $orderInfoParams->p1;
					$t1 = $orderInfoParams->t1;
					$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
					$postVar['INITAMT'] = $orderInfoParams->total;
					$startDate = date("Y-m-d Th:i:sZ", strtotime("+ {$p1} {$t1}", strtotime($curDate)));
				} else {
					$postVar['INITAMT'] = $orderInfoParams->total;
					$startDate = date("Y-m-d Th:i:sZ", strtotime("+ {$p3} {$t3}", strtotime($curDate)));
				}
				$i = 0;
				foreach ($orderItems as $key => $orderItem) {
					$membership = oseRegistry::call('msc')->getInfo($orderItem->entry_id, 'obj');
					$postVar['L_BILLINGTYPE' . $i] = 'RecurringPayments';
					$postVar['L_BILLINGAGREEMENTDESCRIPTION' . $i] = JText::_('ORDER_ID') . " " . $order_id;
					$postVar['L_DESC0'] = JText::_('ORDER_ID') . " " . $order_id . ' First Charge:' . $amount;
					$postVar['L_NAME0'] = $membership->title;//JText :: _('ORDER_ID')." ".$order_id." - ".JText :: _('PAYMENT_FOR_MEMBERSHIP_TYPE')." ".$msc_name;
					$postVar['L_AMT0'] = $amount;
					$postVar['L_QTY0'] = 1;
					$i++;
				}
				$postVar['BILLINGPERIOD'] = $t3;
				$postVar['BILLINGFREQUENCY'] = $p3;
				$postVar['CURRENCYCODE'] = $currency;
				$postVar['TOTALBILLINGCYCLES'] = 0;
				$postVar['TAXAMT'] = 0;//$taxRate / 100 * $a3;
				$postVar['AMT'] = $amount + $postVar['TAXAMT'];
				$postVar['PROFILESTARTDATE'] = $startDate;//date("Y-m-d Th:i:sZ", strtotime($curDate));
				$postVar['RETURNURL'] = JURI::root() . "index.php?option=com_osemsc&view=confirm&mode=a&orderID=" . $order_id . "&Itemid=" . $Itemid;
				$postVar['CANCELURL'] = JURI::root() . "index.php";
				// Post String;
				$postString = 'METHOD=' . urlencode('SetExpressCheckout');
			} else {
				$orderInfoParams = oseJson::decode($orderInfo->params);
				$curDate = oseHTML::getDateTime();
				if ($orderInfoParams->has_trial) {
					$a1 = $orderInfoParams->total;
					$p1 = $orderInfoParams->p1;
					$t1 = $orderInfoParams->t1;
					$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
					$postVar['TRIALBILLINGPERIOD'] = $t1;
					$postVar['TRIALBILLINGFREQUENCY'] = $p1;
					$postVar['TRIALTOTALBILLINGCYCLES'] = 1;
					$postVar['TRIALAMT'] = $a1;
					$a3 = $orderInfoParams->next_total;
				} else {
					$a3 = $orderInfoParams->next_total;
				}
				$i = 0;
				foreach ($orderItems as $key => $orderItem) {
					$membership = oseRegistry::call('msc')->getInfo($orderItem->entry_id, 'obj');
					$postVar['L_BILLINGTYPE' . $i] = 'RecurringPayments';
					$postVar['L_BILLINGAGREEMENTDESCRIPTION' . $i] = JText::_('ORDER_ID') . " " . $order_id;
					$postVar['L_DESC0'] = JText::_('ORDER_ID') . " " . $order_id;
					$postVar['L_NAME0'] = $membership->title;//JText :: _('ORDER_ID')." ".$order_id." - ".JText :: _('PAYMENT_FOR_MEMBERSHIP_TYPE')." ".$msc_name;
					$postVar['L_AMT0'] = $amount;
					$postVar['L_QTY0'] = 1;
					$i++;
				}
				$p3 = $orderInfoParams->p3;
				$t3 = $orderInfoParams->t3;
				$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				$postVar['CURRENCYCODE'] = $currency;
				$postVar['L_BILLINGTYPE0'] = 'RecurringPayments';
				$postVar['L_BILLINGAGREEMENTDESCRIPTION0'] = JText::_('ORDER_ID') . " " . $order_id;
				$postVar['DESC'] = JText::_('ORDER_ID') . " " . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . " " . $msc_name;
				$postVar['BILLINGPERIOD'] = $t3;
				$postVar['BILLINGFREQUENCY'] = $p3;
				$postVar['TOTALBILLINGCYCLES'] = 0;
				$postVar['TAXAMT'] = 0;//$taxRate / 100 * $a3;
				$postVar['AMT'] = $a3 + $postVar['TAXAMT'];
				$postVar['PROFILESTARTDATE'] = date("Y-m-d Th:i:sZ", strtotime($curDate));
				$postVar['RETURNURL'] = JURI::root() . "index.php?option=com_osemsc&view=confirm&mode=a&orderID=" . $order_id . "&Itemid=" . $Itemid;
				$postVar['CANCELURL'] = JURI::root() . "index.php";
				// Post String;
				$postString = 'METHOD=' . urlencode('SetExpressCheckout');
			}
			foreach ($postVar AS $key => $val) {
				$postString .= "&" . urlencode($key) . "=" . urlencode($val);
			}
		}
		$resArray = self::PaypalAPIConnect($postString);
		// Return if empty;
		if (empty($resArray)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		$_SESSION['reshash'] = $resArray;
		$ack = strtoupper($resArray["ACK"]);
		$html = array();
		if ($ack == "SUCCESS") {
			$token = urldecode($resArray["TOKEN"]);
			$url = $resArray["Paypal_URL"] . $token;
		} else {
			$errorcode = $resArray["L_ERRORCODE0"];
			$message = $resArray["L_LONGMESSAGE0"];
			$url = JURI::root() . 'index.php';
		}
		$html['form'] = '<form action="' . $url . '" method="post" target="_self">';
		$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
				. JText::_('Click to pay with PayPal - it is fast, free and secure!') . '" />';
		$html['form'] .= '</form>';
		return $html;
	}
	function PaypalExpPostForm($orderInfo, $params) {
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$paypal_email = $pConfig->paypal_email;
		$html = array();
		$test_mode = $pConfig->paypal_testmode;
		if (empty($paypal_email)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		if ($test_mode == true) {
			$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url = "https://www.paypal.com/cgi-bin/webscr";
		}
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo = $this->getBillingInfo($orderInfo->user_id);
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = &JFactory::getUser($orderInfo->user_id);
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$desc = self::generateDesc($order_id);
		$msc_name = $desc;
		$session = JFactory::getSession();
		$returnUrl = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $order_id));
		$returnUrl = $returnUrl ? $returnUrl : JURI::base() . "index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$vendor_image_url = "";
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$html['form'] = '<form action="' . $url . '" method="post" target="_self">';
		$html['form'] .= '<input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		if ($orderInfo->payment_mode == 'm') {
			$post_variables = array("cmd" => "_ext-enter", "redirect_cmd" => "_xclick", "charset" => "UTF-8", "upload" => "1", "business" => $paypal_email,
					"receiver_email" => $paypal_email, "item_name" => JText::_('ORDER_ID') . " " . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . " " . $msc_name,
					"order_id" => $order_id, "invoice" => $order_number, "amount" => round($amount, 2), "shipping" => '0.00', "currency_code" => $currency,
					"address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1,
					"address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email,
					"night_phone_b" => $billinginfo->telephone, "cpp_header_image" => $vendor_image_url, "return" => $returnUrl,
					"notify_url" => JURI::base() . "components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI::base() . "index.php", "undefined_quantity" => "0",
					"test_ipn" => 0, "pal" => "NRUBJXESJTY24", "no_shipping" => "1", "no_note" => "1");
			$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
					. JText::_('Click to pay with PayPal - it is fast, free and secure!') . '" />';
		} else {
			$orderInfoParams = oseJson::decode($orderInfo->params);
			if (!$orderInfoParams->has_trial) {
				$a3 = $orderInfoParams->total;
				$p3 = $orderInfoParams->p3;
				$t3 = $orderInfoParams->t3;
				$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				$post_variables = array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "charset" => "UTF-8",
						"item_name" => JText::_('ORDER_ID') . " " . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . " " . $msc_name, "order_id" => $order_id,
						"item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname,
						"last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode,
						"city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => $returnUrl,
						"notify_url" => JURI::base() . "components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI::base() . "index.php", "a3" => round($a3, 2),
						"p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency,
						"cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
						. JText::_('Click to pay with PayPal - it is fast, free and secure!') . '" />';
			} else {
				$a1 = $orderInfoParams->total;
				$p1 = $orderInfoParams->p1;
				$t1 = $orderInfoParams->t1;
				$a3 = $orderInfoParams->next_total;
				$p3 = $orderInfoParams->p3;
				$t3 = $orderInfoParams->t3;
				$t1 = str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$t3 = str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				$post_variables = array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "charset" => "UTF-8",
						"item_name" => JText::_('ORDER_ID') . ' ' . $order_id . " - " . JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE ') . $msc_name, "order_id" => $order_id,
						"item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname,
						"last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode,
						"city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => $returnUrl,
						"notify_url" => JURI::base() . "components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI::base() . "index.php", "a1" => $a1, "p1" => $p1,
						"t1" => $t1, "a3" => $a3, "p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency,
						"cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
						. JText::_('Click to pay with PayPal - it is fast, free and secure!') . '" />';
			}
		}
		// Process payment variables;
		$html['url'] = $url . "?";
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
			$html['url'] .= $name . "=" . urlencode($value) . "&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	private function PaypalAPIConnect($postString) {
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'class.connection.php');
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$test_mode = $pConfig->paypal_testmode;
		$API_UserName = $pConfig->paypal_api_username;
		$API_Password = $pConfig->paypal_api_passwd;
		$API_Signature = $pConfig->paypal_api_signature;
		$subject = '';
		if (empty($API_UserName) || empty($API_Password) || empty($API_Signature)) {
			return false;
		}
		define('VERSION', '64.0');
		define('ACK_SUCCESS', 'SUCCESS');
		define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');
		if ($test_mode == true) {
			$API_Endpoint = 'api-3t.sandbox.paypal.com';
			$Paypal_URL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
		} else {
			$API_Endpoint = 'api-3t.paypal.com';
			$Paypal_URL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
		}
		$postVar['PWD'] = $API_Password;
		$postVar['USER'] = $API_UserName;
		$postVar['SIGNATURE'] = $API_Signature;
		$postVar['VERSION'] = VERSION;
		$postHead = '';
		foreach ($postVar AS $key => $val) {
			$postHead .= "&" . urlencode($key) . "=" . $val;
		}
		$postString = $postString . $postHead;
		$response = OSECONNECTOR::send_request_via_fsockopen($API_Endpoint, '/nvp', $postString, 'urlencoded');
		$resArray = self::parseResults($response);
		$resArray["Paypal_URL"] = $Paypal_URL;
		return $resArray;
	}
	private function parseResults($response) {
		$c_mccomb = "\n";
		$return = array();
		$var = explode($c_mccomb, $response);
		$lastrow = $var[count($var) - 1];
		$lastrow = explode("&", $lastrow);
		foreach ($lastrow as $row) {
			$row = explode("=", $row);
			$return[$row[0]] = $row[1];
		}
		return $return;
	}
	function VpcashOneOffPostForm($orderInfo, $payment_method = 'vpcash') {
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$vpcash_account = $pConfig->vpcash_account;
		$vpcash_email = $pConfig->vpcash_email;
		$store_id = $pConfig->vpcash_storeid;
		$html = array();
		$test_mode = $pConfig->vpcash_testmode;
		if (empty($vpcash_account)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		if ($test_mode == true) {
			$url = "https://www.virtualpaycash.net/sandbox/handle.php";
		} else {
			$url = "https://www.virtualpaycash.net/handle.php";
		}
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = &JFactory::getUser($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$msc_name = $desc;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $orderInfo->order_id));
		$returnUrl = $returnUrl ? $returnUrl : JURI::base() . "index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$vendor_image_url = "";
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$html['form'] = '<form action="' . $url . '" method="post">';
		if ($payment_method == 'vpcash_cc') {
			$html['form'] .= '<input type="hidden" name="method" value="cc" />';
		}
		if ($orderInfo->payment_mode == 'm') {
			$post_variables = array("merchantAccount" => $vpcash_account, "store_id" => $store_id, "receiver_email" => $vpcash_email, "amount" => round($amount, 2),
					"vpc_currency" => $currency, "lang" => "en", "item_id" => $order_number, "return_url" => $returnUrl,
					"notify_url" => JURI::base() . "components/com_osemsc/ipn/vpcash_notify.php", "cancel_url" => JURI::base() . "index.php", "SUGGESTED_MEMO" => "ADDITIONAL-INFO");
			$html['form'] .= '<input type="image" id="vpcash_image" name="cartImage" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
					. JText::_('Click to pay with VirtualPayCash - it is fast, free and secure!') . '" />';
		}
		// Process payment variables;
		$html['url'] = $url . "?";
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
			$html['url'] .= $name . "=" . urlencode($value) . "&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function BBVAOneOffPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderbbva.php');
		$bbva = new osePaymentOrderbbva();
		$html = $bbva->BBVAOneOffPostForm($orderInfo);
		return $html;
	}
	function PayFastOneOffPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderpayfast.php');
		$payfast = new osePaymentOrderPayFast();
		$html = $payfast->PayFastOneOffPostForm($orderInfo);
		return $html;
	}
	function eWaySharedHostingPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymenteWaySH.php');
		$eWay = new osePaymentOrdereWaySH();
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$html = $eWay->eWaySharedHostingPostForm($orderInfo, $billingInfo);
		return $html;
	}
	function eWaySharedHostingGetResponse($AccessPaymentCode) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymenteWaySH.php');
		$eWay = new osePaymentOrdereWaySH();
		$response = $eWay->eWaySharedHostingGetResponse($AccessPaymentCode);
		return $response;
	}
	function GCOOneOffPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdergco.php');
		$gco = new osePaymentOrdergco();
		$html = $gco->GCOOneOffPostForm($orderInfo);
		return $html;
	}
	function GCOrecurringPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdergco.php');
		$gco = new osePaymentOrdergco();
		$html = $gco->GCOrecurringPostForm($orderInfo);
		return $html;
	}
	function get_gcoform($orderInfo) {
		$parameters = &JComponentHelper::getParams('com_osemsc');
		$google_checkout_id = $parameters->get('google_checkout_id');
		$html = array();
		if (empty($google_checkout_id)) {
			$html['form'] = "";
			return $html;
		}
		$msc_id = $orderInfo->entry_id;
		$node = oseMscTree::getNode($msc_id, 'obj');
		$msc_name = $node->title;
		$payment = oseMscAddon::getExtInfo($msc_id, 'payment', 'obj');
		$price = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$vendor_image_url = "";
		$app = &JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		if ($currency == "GBP") {
			$country_code = "UK";
		} elseif ($currency == "USD") {
			$country_code = "US";
		}
		$url = "https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/" . $google_checkout_id;
		$post_variables = array("item_name_1" => JText::_('ORDER_ID') . ' ' . $order_id,
				"item_description_1" => JText::_('PAYMENT_FOR_MEMBERSHIP_TYPE') . ' ' . $msc_name . "||" . $order_number, "item_merchant_id_1" => $order_id,
				"item_quantity_1" => "1", "item_price_1" => $price, "item_currency_1" => $currency,
				"continue_url" => JURI::base() . "index.php?option=com_osemsc&view=member&result=success");
		$html['form'] = '<form action="' . $url
				. '" method="post" target="_self" id="google" name="google"><input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		$html['form'] .= '<input id="gco-image" type="image" name="Google Checkout" alt="Fast checkout through Google"
				src="components/com_osemsc/assets/images/checkout.png?merchant_id=' . $google_checkout_id . '&style=white&variant=text&loc=en_US"/>';
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function getBillingInfo($user_id) {
		$result = new stdClass;
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$item = $member->getBillingInfo('obj');
		if (empty($result)) {
			$result->user_id = $user_id;
			$result->email = '';
			$result->company = '';
			$result->firstname = "";
			$result->lastname = "";
			$result->addr1 = "";
			$result->addr2 = "";
			$result->postcode = "";
			$result->city = "";
			$result->state = "";
			$result->country = "";
			$result->phone = $result->telephone = "";
			$result->fax = "";
			$result->countrycode = "";
		} else {
			$result->user_id = $user_id;
			$result->email = $item->user_email;
			$result->company = $item->company;
			$result->firstname = $item->firstname;
			$result->lastname = $item->lastname;
			$result->addr1 = $item->addr1;
			$result->addr2 = (isset($item->addr2)) ? $item->addr2 : '';
			$result->postcode = $item->postcode;
			$result->city = $item->city;
			$result->state = $item->state;
			$result->country = (isset($item->country)) ? $item->country : '';
			if (!empty($result->country)) {
				$result->countrycode = self::getShortCountrycode($result->country);
			}
			$result->phone = $result->telephone = $item->telephone;
			$result->fax = '';
		}
		$result = self::getBillingInfoCleaned($result);
		return $result;
	}
	function getBillingInfoCleaned($billingInfo) {
		$billingInfo->firstname = str_replace("&", " ", $billingInfo->firstname);
		$billingInfo->lastname = str_replace("&", " ", $billingInfo->lastname);
		$billingInfo->company = str_replace("&", " ", $billingInfo->company);
		$billingInfo->addr1 = str_replace("&", " ", $billingInfo->addr1);
		$billingInfo->city = str_replace("&", " ", $billingInfo->city);
		$billingInfo->state = str_replace("&", " ", $billingInfo->state);
		return $billingInfo;
	}
	function getShortCountrycode($country_3_code) {
		$db = &JFactory::getDBO();
		$query = "SELECT `country_2_code` FROM `#__osemsc_country` WHERE `country_3_code` = " . $db->Quote($country_3_code);
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
	function eWayOneOffPay($orderInfo, $credit_info, $params = array()) {
		$config = oseMscConfig::getConfig('', 'obj');
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo, $config, $config->eway_testmode);
		$cc_methods = explode(',', $config->cc_methods);
		if (!in_array('eway', $cc_methods) || $config->enable_cc == false) {
			return $eway->getErrorMessage('cc', '0003', null);
		}
		$eway->setCreditCardInfo($credit_info);
		$results = $eway->OneOffPay();
		return $results;
	}
	function eWayBillAuthorize($orderInfo, $type) {
		$config = oseMscConfig::getConfig('', 'obj');
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo, $config, $config->eway_testmode);
		return $eway->queryRebill($type);
	}
	function eWayCreateProfile($orderInfo, $credit_info, $params = array()) {
		$config = oseMscConfig::getConfig('', 'obj');
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo, $config, $config->eway_testmode);
		$cc_methods = explode(',', $config->cc_methods);
		if (!in_array('eway', $cc_methods) || $config->enable_cc == false) {
			return $eway->getErrorMessage('cc', '0003', null);
		}
		$eway->setCreditCardInfo($credit_info);
		return $eway->CreateProfile();
	}
	function eWayDeleteProfile($orderInfo, $params = array()) {
		$config = oseMscConfig::getConfig('', 'obj');
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdereWay.php');
		$eway = new osePaymentOrdereWay($orderInfo, $config, $config->eway_testmode);
		return $eway->DeleteProfile();
	}
	function epayInstance() {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderePay.php');
		$epay = new osePaymentOrderePay;
		return $epay;
	}
	function ePayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderePay.php');
		$ePayParams = array();
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$ePayParams['accept_url'] = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $orderInfo->order_id));
		$ePayParams['order_number'] = oseObject::getValue($orderInfo, 'order_number');
		$ePayParams['order_number'] = substr($ePayParams['order_number'], 0, 20);
		$ePayParams['amount'] = $orderInfoParams->next_total;
		$ePayParams['currency'] = oseObject::getValue($orderInfo, 'payment_currency');
		$ePayParams['merchantnumber'] = $pConfig->epay_merchantnumber;
		$ePayParams['md5'] = $pConfig->epay_md5;
		$ePayParams['instantcapture'] = $pConfig->epay_instantcapture;
		$epay = new osePaymentOrderePay;
		$html = $epay->ePayCreateProfile(0, $ePayParams);
		return $html;
		//return array('html'=>$html);
	}
	function ePayCreateProfile($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderePay.php');
		$ePayParams = array();
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$ePayParams['accept_url'] = (isset($orderInfoParams->returnUrl)) ? urldecode($orderInfoParams->returnUrl) : JURI::root() . "index.php";
		$ePayParams['order_number'] = oseObject::getValue($orderInfo, 'order_number');
		$ePayParams['order_number'] = substr($ePayParams['order_number'], 0, 20);
		$ePayParams['amount'] = $orderInfoParams->total;
		$ePayParams['currency'] = oseObject::getValue($orderInfo, 'payment_currency');
		$ePayParams['merchantnumber'] = $pConfig->epay_merchantnumber;
		$ePayParams['md5'] = $pConfig->epay_md5;
		$ePayParams['instantcapture'] = $pConfig->epay_instantcapture;
		// if free trial
		if ($orderInfo->payment_price == 0) {
			return false;
		} else {
			$epay = new osePaymentOrderePay;
			$html = $epay->ePayCreateProfile(1, $ePayParams);
			return $html;
		}
	}
	function ePayDeleteProfile($orderInfo, $credit_info, $params) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderePay.php');
		return osePaymentOrdereWay::ePayDeleteProfile();
	}
	function BeanStreamModify($orderInfo, $params = array()) {
		require_once(OSEMSC_B_LIB . DS . 'class.connection.php');
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$merchant_id = $pConfig->beanstream_merchant_id;
		$passcode = $pConfig->beanstream_passcode;
		$username = $pConfig->beanstream_username;
		$password = $pConfig->beanstream_password;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$postVar = array();
		$postVar['requestType'] = 'BACKEND';
		$postVar['merchantId'] = $merchant_id;
		$postVar['serviceVersion'] = '1.0';
		$postVar['operationType'] = 'M';
		$postVar['passcode'] = $passcode;
		$postVar['rbAccountID'] = $orderInfo->payment_serial_number;
		$postVar['trnCardOwner'] = $params['creditcard_name'];
		$postVar['trnCardNumber'] = $params['creditcard_number'];
		$postVar['trnExpMonth'] = $params['creditcard_month'];
		$postVar['trnExpYear'] = $params['creditcard_year'];
		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);
		$uri = "/scripts/recurring_billing.asp";
		$res = OSECONNECTOR::send_request_via_fsockopen($hostname, $uri, $workstring, 'urlencoded');
		$code = null;
		if (!empty($res)) {
			$code = OSECONNECTOR::substring_between($res, '<code>', '</code>');
			$msg = OSECONNECTOR::substring_between($res, '<message>', '</message>');
		} else {
			$msg = JText::_('ERROR');
		}
		$code = (!empty($code)) ? $code : 0;
		$result = array();
		if ($code == 1) {
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			;
			$result['content'] = JText::_('Updated!');
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			;
			$result['content'] = $msg;
		}
		return $result;
	}
	function BeanStreamOneOffPay($orderInfo, $credit_info, $isSub = false, $params = array()) {
		ini_set('max_execution_time', '180');
		$config = oseMscConfig::getConfig('', 'obj');
		$cc_methods = explode(',', $config->cc_methods);
		if (!in_array('beanstream', $cc_methods) || $config->enable_cc == false) {
			//return self::getErrorMessage('cc', '0003', null);
		}
		$pConfig = $config;//oseRegistry::call('msc')->getConfig('payment', 'obj');
		$merchant_id = $pConfig->beanstream_merchant_id;
		$username = $pConfig->beanstream_username;
		$password = $pConfig->beanstream_password;
		if (empty($merchant_id)) {
			return self::getErrorMessage('cc', '0001', null);
		}
		$orderInfoParams = oseJson::decode($orderInfo->params);
		if (empty($orderInfoParams->next_total) || $orderInfoParams->next_total <= 0) {
			return self::getErrorMessage('cc', '0002', null);
		}
		$result = array();
		$db = oseDB::instance();
		$user_id = $orderInfo->user_id;
		$msc_id = $orderInfo->entry_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$desc = $this->generateDesc($order_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		$currency = $orderInfo->payment_currency;
		$user = &JFactory::getUser($orderInfo->user_id);
		$app = &JFactory::getApplication();
		$Itemid = $this->getItemid();
		$amount = $orderInfo->payment_price;
		if (empty($amount)) {
			$updataParams = array();
			//$updataParams['payment_serial_number'] = $post['trnId'];
			return $this->confirmOrder($order_id, $updataParams);
		}
		$postVar = array();
		// General
		$postVar['requestType'] = 'BACKEND';
		$postVar['merchant_id'] = $merchant_id;
		$postVar['username'] = $username;
		$postVar['password'] = $password;
		$postVar['trnAmount'] = $amount;
		$postVar['trnOrderNumber'] = substr($order_number, 0, 20);
		$postVar['trnType'] = 'P';
		$postVar['trnRecurring'] = 0;
		$postVar['paymentMethod'] = 'CC';
		$postVar['ref1'] = $order_id;
		$postVar['ref2'] = $orderInfoParams->timestamp;
		// Card Info
		$postVar['trnCardOwner'] = $credit_info["creditcard_name"];
		$postVar['trnCardNumber'] = $credit_info["creditcard_number"];
		$postVar['trnExpMonth'] = substr($credit_info["creditcard_month"], -2);
		$postVar['trnExpYear'] = substr($credit_info["creditcard_year"], -2);
		$postVar['trnCardCvd'] = $credit_info["creditcard_cvv"];
		// Billing
		$postVar['ordName'] = $billingInfo->firstname . ' ' . $billingInfo->lastname;
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
		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);
		$uri = "/scripts/process_transaction.asp";
		require_once(OSEMSC_B_LIB . DS . 'class.connection.php');
		$res = OSECONNECTOR::send_request_via_fsockopen($hostname, $uri, $workstring, 'urlencoded');
		$res = stristr($res, "\r\n\r\n");
		$res = trim($res);
		$post = array();
		parse_str($res, $post);
		if ($post['trnApproved']) {
			$updataParams = array();
			$updataParams['payment_serial_number'] = $post['trnId'];
			if ($post['authCode'] == 'TEST1') {
				if (empty($pConfig->beanstream_testmode)) {
					return self::getErrorMessage('cc', '0000', 'Warning: It would be recorded as Invalid!');
				} else {
					return self::getErrorMessage('cc', '0000', 'Error: Test Successfully');
				}
			} else {
				$updated = $this->confirmOrder($order_id, $updataParams);
				if ($isSub) {
					$orderInfo->payment_serial_number = $post['trnId'];
					$updated['orderInfo'] = $orderInfo;
				}
				return $updated;
			}
		} else {
			return self::getErrorMessage('cc', '0000', 'Error: ' . $post['messageText']);
		}
	}
	function BeanStreamTransInterval($t) {
		$results = array();
		$t = strtolower($t);
		$result = null;
		switch ($t) {
		case "year":
			$result = 'Y';
			break;
		case "month":
			$result = 'M';
			break;
		case "week":
			$result = 'W';
			break;
		case "day":
			$result = 'D';
			break;
		}
		return $result;
	}
	function BeanStreamCreateProfile($orderInfo, $credit_info, $params = array()) {
		$updated = $this->BeanStreamOneOffPay($orderInfo, $credit_info, true, $params);
		if ($updated['success']) {
			if (isset($updated['orderInfo'])) {
				$orderInfo = $updated['orderInfo'];
				unset($updated['orderInfo']);
			}
			$config = oseMscConfig::getConfig('', 'obj');
			$pConfig = $config;
			$user = &JFactory::getUser($orderInfo->user_id);
			$app = &JFactory::getApplication();
			$Itemid = $this->getItemid();
			$merchant_id = $pConfig->beanstream_merchant_id;
			$username = $pConfig->beanstream_username;
			$password = $pConfig->beanstream_password;
			if (empty($merchant_id)) {
				return self::getErrorMessage('cc', '0001', null);
			}
			$result = array();
			$db = oseDB::instance();
			$user_id = $orderInfo->user_id;
			$msc_id = $orderInfo->entry_id;
			$order_id = $orderInfo->order_id;
			$order_number = $orderInfo->order_number;
			$orderInfoParams = oseJson::decode($orderInfo->params);
			$desc = $this->generateDesc($order_id);
			$billingInfo = $this->getBillingInfo($orderInfo->user_id);
			$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
			$currency = $orderInfo->payment_currency;
			$user = &JFactory::getUser($orderInfo->user_id);
			$amount = $orderInfoParams->next_total;
			if (empty($amount)) {
				return self::getErrorMessage('cc', '0002', null);
			}
			$postVar = array();
			// General
			$postVar['requestType'] = 'BACKEND';
			$postVar['merchant_id'] = $merchant_id;
			$postVar['username'] = $username;
			$postVar['password'] = $password;
			$postVar['trnAmount'] = $amount;
			$postVar['trnOrderNumber'] = $orderInfo->payment_serial_number;//substr($order_number, 0,20);
			$postVar['trnType'] = 'P';
			$postVar['trnRecurring'] = 1;
			$postVar['paymentMethod'] = 'CC';
			$postVar['ref1'] = $order_id;
			$postVar['ref2'] = $orderInfoParams->timestamp;
			// Card Info
			$postVar['trnCardOwner'] = $credit_info["creditcard_name"];
			$postVar['trnCardNumber'] = $credit_info["creditcard_number"];
			$postVar['trnExpMonth'] = substr($credit_info["creditcard_month"], -2);
			$postVar['trnExpYear'] = substr($credit_info["creditcard_year"], -2);
			$postVar['trnCardCvd'] = $credit_info["creditcard_cvv"];
			// Billing
			$postVar['ordName'] = $billingInfo->firstname . ' ' . $billingInfo->lastname;
			$postVar['ordEmailAddress'] = $billingInfo->email;
			$postVar['ordPhoneNumber'] = $billingInfo->telephone;
			$postVar['ordAddress1'] = $billingInfo->addr1;
			$postVar['ordCity'] = $billingInfo->city;
			$postVar['ordProvince'] = $billingInfo->state;
			$postVar['ordPostalCode'] = $billingInfo->postcode;
			$postVar['ordCountry'] = $billingInfo->country;
			// Recurring
			$curDate = oseHTML::getDateTime();
			if ($orderInfoParams->has_trial) {
				$nT1 = $this->BeanStreamTransInterval($orderInfoParams->t1);
				$query = " SELECT DATE_ADD('{$curDate}',INTERVAL {$orderInfoParams->p1} {$orderInfoParams->t1})";
			} else {
				$nT3 = $this->BeanStreamTransInterval($orderInfoParams->t3);
				$query = " SELECT DATE_ADD('{$curDate}',INTERVAL {$orderInfoParams->p3} {$orderInfoParams->t3})";
			}
			$db->setQuery($query);
			$date = new DateTime($db->loadResult());
			$startDate = $date->format("mdY");
			$postVar['rbCharge'] = 0;
			$postVar['rbFirstBilling'] = $startDate;
			$postVar['rbBillingPeriod'] = $this->BeanStreamTransInterval($orderInfoParams->t3);
			;
			$postVar['rbBillingIncrement'] = $orderInfoParams->p3;
			// URL
			$postVar['errorPage'] = urlencode(JURI::root());
			$postVar['approvedPage'] = urlencode(JURI::root());
			$postVar['declinedPage'] = urlencode(JURI::root());
			$hostname = 'www.beanstream.com';
			$workstring = http_build_query($postVar);
			$uri = "/scripts/process_transaction.asp";
			require_once(OSEMSC_B_LIB . DS . 'class.connection.php');
			$res = OSECONNECTOR::send_request_via_fsockopen($hostname, $uri, $workstring, 'urlencoded');
			$res = stristr($res, "\r\n\r\n");
			$res = trim($res);
			$post = array();
			parse_str($res, $post);
			if ($post['trnApproved']) {
				$updataParams = array();
				$updataParams['payment_serial_number'] = $post['rbAccountId'];
				$updataParams['order_number'] = $orderInfo->payment_serial_number;
				$orderInfoParams->oneoff_transactionid = $orderInfo->payment_serial_number;
				$updataParams['params'] = oseJson::encode($orderInfoParams);
				$result = $this->updateOrder($order_id, 'confirmed', $updataParams);
				return $updated;
			} else {
				return self::getErrorMessage('cc', '0000', JText::_('Joined Membership, but subscription fails creating. Error: ' . $post['messageText']));
			}
		} else {
			return $updated;
		}
	}
	function BeanStreamQueryTransaction($orderInfo, $params = array()) {
		require_once(OSEMSC_B_LIB . DS . 'class.connection.php');
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$merchant_id = $pConfig->beanstream_merchant_id;
		$passcode = $pConfig->beanstream_passcode;
		$username = $pConfig->beanstream_username;
		$password = $pConfig->beanstream_password;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$postVar = array();
		$postVar['requestType'] = 'BACKEND';
		$postVar['trnType'] = 'Q';
		$postVar['merchantId'] = $merchant_id;
		$postVar['trnOrderNumber'] = $orderInfo->payment_serial_number;
		$postVar['username'] = $username;
		$postVar['password'] = $password;
		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);
		$uri = "/scripts/process_transaction.asp";
		$res = OSECONNECTOR::send_request_via_fsockopen($hostname, $uri, $workstring, 'urlencoded');
		$res = stristr($res, "\r\n\r\n");
		$res = trim($res);
		$post = array();
		parse_str($res, $post);
		$result = array();
		$result['trnApproved'] = $post['trnApproved'];
		$result['messageText'] = strtolower($post['messageText']);
		$result['authCode'] = $post['authCode'];
		$result['trnType'] = $post['trnType'];
		return $result;
	}
	function BeanStreamDeleteProfile($orderInfo, $params = array()) {
		require_once(OSEMSC_B_LIB . DS . 'class.connection.php');
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$merchant_id = $pConfig->beanstream_merchant_id;
		$passcode = $pConfig->beanstream_passcode;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$postVar = array();
		$postVar['serviceVersion'] = '1.0';
		$postVar['operationType'] = 'M';
		$postVar['merchantId'] = $merchant_id;
		$postVar['passcode'] = $passcode;
		$postVar['rbAccountID'] = $orderInfo->payment_serial_number;
		$postVar['rbBillingState'] = 'C';
		$postVar['processBackPayments'] = '0';
		$postVar['ref5'] = '';
		$hostname = 'www.beanstream.com';
		$workstring = http_build_query($postVar);
		$uri = "/scripts/recurring_billing.asp";
		$res = OSECONNECTOR::send_request_via_fsockopen($hostname, $uri, $workstring, 'urlencoded');
		$res = stristr($res, "\r\n\r\n");
		$res = trim($res);
		$result = array();
		$code = OSECONNECTOR::substring_between($res, '<code>', '</code>');
		$message = OSECONNECTOR::substring_between($res, '<message>', '</message>');
		$result['success'] = ($code == 1) ? true : false;
		$result['title'] = JText::_('Cancel');
		$result['content'] = ($code == 1) ? JText::_('Your membership subscription is cancelled.') : JText::_('Error') . ':' . $message;
		return $result;
	}
	function PNWOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderPnw.php');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$PNWParams = array();
		$PNWParams['project_id'] = $pConfig->pnw_project_id;
		$PNWParams['project_password'] = $pConfig->pnw_project_password;
		$PNWParams['user_id'] = $pConfig->pnw_user_id;
		$PNWParams['language_id'] = oseObject::getValue($pConfig, 'language_id', 'EN');
		$PNWParams['reason_1'] = oseObject::getValue($orderInfo, 'order_id');
		$PNWParams['order_number'] = oseObject::getValue($orderInfo, 'order_number');
		$PNWParams['amount'] = $orderInfoParams->next_total;
		$PNWParams['currency_id'] = oseObject::getValue($orderInfo, 'payment_currency');
		if (oseObject::getValue($orderInfoParams, 'eternal', false)) {
			$PNWParams['expires'] = '';
		} else {
			$PNWParams['expires'] = oseObject::getValue($orderInfoParams, 'p3');
		}
		$PNWParams['recurrence_unit'] = oseObject::getValue($orderInfoParams, 't3');
		$PNWParams['max_usage'] = 1;
		$PNW = new osePaymentOrderPNW;
		$html = $PNW->PNWCreateProfile(0, $PNWParams);
		return $html;
	}
	function PNWCreateProfile($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderPnw.php');
		$orderInfoParams = oseJson::decode(oseObject::getValue($orderInfo, 'params'));
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$PNWParams = array();
		$PNWParams['project_id'] = $pConfig->pnw_project_id;
		$PNWParams['project_password'] = $pConfig->pnw_project_password;
		$PNWParams['user_id'] = $pConfig->pnw_user_id;
		$PNWParams['language_id'] = oseObject::getValue($pConfig, 'language_id', 'EN');
		$PNWParams['order_number'] = oseObject::getValue($orderInfo, 'order_number');
		$PNWParams['order_number'] = substr($PNWParams['order_number'], 0, 20);
		$PNWParams['amount'] = $orderInfoParams->next_total;
		$PNWParams['currency_id'] = oseObject::getValue($orderInfo, 'payment_currency');
		if (oseObject::getValue($orderInfoParams, 'eternal', false)) {
			$PNWParams['expires'] = '';
		} else {
			$PNWParams['expires'] = oseObject::getValue($orderInfoParams, 'p3');
		}
		$PNWParams['recurrence_unit'] = oseObject::getValue($orderInfoParams, 't3');
		// if free trial
		if ($orderInfo->payment_price == 0) {
			return false;
		} else {
			$PNW = new osePaymentOrderPNW;
			$html = $PNW->PNWCreateProfile(1, $PNWParams);
			return $html;
		}
	}
	function PNWDeleteProfile($orderInfo, $credit_info, $params) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderePay.php');
		return osePaymentOrdereWay::ePayDeleteProfile();
	}
	function AuthorizeARBUpdateProfile($orderInfo, $credit_info, $params = array()) {
		ini_set('max_execution_time', '180');
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'class.connection.php');
		// Now proceed the Recurring payment plan creation;
		$db = oseDB::instance();
		$result = array();
		$user_id = $orderInfo->user_id;
		$msc_id = $orderInfo->entry_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// Get User billing information;
		$desc = $this->generateDesc($order_id);
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		// Reference ID;
		$refID = substr($order_number, 0, 19) . "A";
		$invoice = substr($order_number, 0, 19) . "A";
		$name = "MEM{$msc_id}UID{$user_id}_" . date("Ymdhis");
		// Credit Card Informaiton;
		$creditcard = $credit_info["creditcard_number"];
		$cardCode = $credit_info["creditcard_cvv"];
		$expiration = $credit_info["creditcard_expirationdate"];
		$expiration = strval($expiration);
		// Recurring payment setting;
		$msc = oseRegistry::call('msc');
		$ext = $orderInfoParams;//msc->getExtInfo($msc_id, 'payment', 'obj');
		$mscRegRecurrence = $this->AuthorizeAPITransInterval($orderInfoParams->t3, $orderInfoParams->p3);
		$totalOccurrences = 9999;
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'AuthnetARB.class.php');
		$config = oseMscConfig::getConfig('payment', 'obj');
		$test_mode = $config->cc_testmode;
		$arbsubdomain = ($test_mode) ? 'apitest' : 'api';
		$arb = new AuthnetARB();
		$arb->url = $arbsubdomain . ".authorize.net";
		$arb->setParameter('cardNumber', $creditcard);
		$arb->setParameter('expirationDate', $expiration);
		$arb->setParameter('cardCode', $cardCode);
		$arb->setParameter('firstName', substr($billingInfo->firstname, 0, 50));
		$arb->setParameter('lastName', substr($billingInfo->lastname, 0, 50));
		$arb->setParameter('address', substr($billingInfo->addr1, 0, 60));
		$arb->setParameter('city', substr($billingInfo->city, 0, 60));
		$arb->setParameter('state', substr($billingInfo->state, 0, 40));
		$arb->setParameter('zip', substr($billingInfo->postcode, 0, 20));
		$arb->setParameter('subscrName', $name);
		// Assgin login credentials
		$arb->setParameter('login', $config->an_loginid);
		$arb->setParameter('transkey', $config->an_transkey);
		$arb->setParameter('refID', $refID);
		$arb->setParameter('subscrId', $orderInfo->payment_serial_number);
		// Create the recurring billing subscription
		$arb->updateCreditCard();
		$return = array();
		if ($arb->getResponseCode() == 'I00001') {
			return array('success' => true);
		} else {
			return array('success' => false, 'content' => $arb->getResponse());
		}
	}
	function AuthorizeAIMPay($orderInfo, $credit_info, $params = array(), $TransactionType = 'AUTH_CAPTURE', $ARBtrialPayment = false) {
		ini_set('max_execution_time', '180');
		$resArray = array();
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'class.connection.php');
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'Authnet.class.php');
		$db = oseDB::instance();
		$result = array();
		$user_id = $orderInfo->user_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$desc = $desc = $this->generateDesc($order_id);
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// Get User billing information;
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		// Get Authorize.net Setting if the mode is OSE;
		$config = oseMscConfig::getConfig('', 'obj');
		$cc_methods = explode(',', $config->cc_methods);
		if (!in_array('authorize', $cc_methods) || $config->enable_cc == false) {
			return self::getErrorMessage('cc', '0003', null);
		}
		if (empty($orderInfo->payment_price)) {
			//return self::getErrorMessage('cc', '0002', null);
		}
		if ($orderInfo->payment_currency != 'USD') {
			return self::getErrorMessage('cc', '0001', null);
		}
		if ($orderInfoParams->next_total <= 0) {
			return self::getErrorMessage('cc', '0002', null);
		} else {
			$refID = substr($order_number, 0, 19) . "M";
			$invoice = substr($order_number, 0, 19) . "M";
			$taxRate = 0;
			// Credit Card Informaiton;
			$creditcard = $credit_info["creditcard_number"];
			$cardCode = $credit_info["creditcard_cvv"];
			$expiration = $credit_info["creditcard_expirationdate"];
			$expiration = strval($expiration);
			// Process Payments;
			$cc_payment = Authnet::instance();
			$cc_payment->setParameter('refID', $refID);
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
			$amount = $orderInfoParams->total;
			$tax = $amount * $taxRate;
			$cc_payment->setTransactionType($TransactionType);
			$cc_payment->setParameter('x_email_customer', true);
			$cc_payment->setTransaction($creditcard, $expiration, $amount, $cardCode, $invoice, $tax);
			$pConfig = oseMscConfig::getConfig('payment', 'obj');
			if (empty($pConfig->an_loginid) || empty($pConfig->an_transkey)) {
				return false;
			}
			if (!isset($pConfig->cc_testmode) || $pConfig->cc_testmode == true) {
				$test_mode = true;
			} else {
				$test_mode = false;
			}
			$subdomain = ($test_mode) ? 'test' : 'secure';
			$cc_payment->url = $subdomain . ".authorize.net";
			$cc_payment->setParameter('x_login', $pConfig->an_loginid);
			$cc_payment->setParameter('x_tran_key', $pConfig->an_transkey);
			$cc_payment->setParameter('x_email_customer', $pConfig->an_email_customer);
			$cc_payment->setParameter('x_merchant_email', $pConfig->an_merchant_email);
			$cc_payment->setParameter('x_email_merchant', $pConfig->an_email_merchant);
			$cc_payment->process();
			$resArray['isApproved'] = $cc_payment->isApproved();
			$resArray['TransactionID'] = $cc_payment->getTransactionID();
			$resArray['content'] = $cc_payment->getResponseText();
		}
		if ($resArray['isApproved'] == true) {
			if ($TransactionType == 'AUTH_CAPTURE') {
				if ($ARBtrialPayment == false) {
					$params['payment_serial_number'] = $resArray['TransactionID'];
					$return = self::confirmOrder($order_id, $params, 0, $user_id, 'authorize');
					return $return;
				} else {
					return $resArray;
				}
			} elseif ($TransactionType == 'AUTH_ONLY') {
				$return = $resArray;
			}
			return $return;
		} else {
			return self::getErrorMessage('cc', '0000', $cc_payment->getResponseText());
		}
	}
	function AuthorizeAIMVoid($TransactionID) {
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'class.connection.php');
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'Authnet.class.php');
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		if (empty($pConfig->an_loginid) || empty($pConfig->an_transkey)) {
			return false;
		}
		if (!isset($pConfig->cc_testmode) || $pConfig->cc_testmode == true) {
			$test_mode = true;
		} else {
			$test_mode = false;
		}
		$subdomain = ($test_mode) ? 'test' : 'secure';
		$cc_payment = Authnet::instance();
		$cc_payment->url = $subdomain . ".authorize.net";
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
	function AuthorizeARBCreateProfile($orderInfo, $credit_info, $params = array()) {
		ini_set('max_execution_time', '180');
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'class.connection.php');
		$config = oseMscConfig::getConfig('payment', 'obj');
		$an_mode = oseObject::getValue($config, 'an_mode', 'an_aim_arb');
		if ($an_mode == 'an_aim_arb') {
			// Test if the User's credit card has enough funding, if so, void the order;
			$result = self::AuthorizeAIMPay($orderInfo, $credit_info, $params, $TransactionType = 'AUTH_ONLY');
			if ($result['isApproved'] == true) {
				$voidResult = self::AuthorizeAIMVoid($result['TransactionID']);
				if ($voidResult['isApproved'] == false) {
					return self::getErrorMessage('cc', '0000', $voidResult['ResponseText']);
				}
			} else {
				return self::getErrorMessage('cc', '0000', $result['content']);
			}
		}
		// Now proceed the Recurring payment plan creation;
		$db = oseDB::instance();
		$result = array();
		$user_id = $orderInfo->user_id;
		$msc_id = $orderInfo->entry_id;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// Get User billing information;
		$desc = $this->generateDesc($order_id);
		//$node= oseRegistry :: call('msc')->getInfo($msc_id, 'obj');
		$billingInfo = $this->getBillingInfo($orderInfo->user_id);
		$taxRate = (isset($orderInfoParams->tax_rate)) ? $orderInfoParams->tax_rate : 0;
		// Reference ID;
		$refID = substr($order_number, 0, 19) . "A";
		$invoice = substr($order_number, 0, 19) . "A";
		$name = "MEM{$msc_id}UID{$user_id}_" . date("Ymdhis");
		// Credit Card Informaiton;
		$creditcard = $credit_info["creditcard_number"];
		$cardCode = $credit_info["creditcard_cvv"];
		$expiration = $credit_info["creditcard_expirationdate"];
		$expiration = strval($expiration);
		// Recurring payment setting;
		$msc = oseRegistry::call('msc');
		$ext = $orderInfoParams;//msc->getExtInfo($msc_id, 'payment', 'obj');
		$mscRegRecurrence = $this->AuthorizeAPITransInterval($orderInfoParams->t3, $orderInfoParams->p3);
		$totalOccurrences = 9999;
		// Check if Price is set correctly;
		if (empty($orderInfo->payment_price)) {
			//return self::getErrorMessage('cc', '0002');
		}
		// Trial payment setting;
		$trialOccurrences = (!empty($ext->has_trial)) ? "1" : "0";
		if ($ext->has_trial) {
			// If there is trial, we use Next Total, so only the first trial period has coupon applied to it
			$total = $orderInfoParams->next_total;
			$mscTrialRecurrence = $this->AuthorizeAPITransInterval($orderInfoParams->t1, $orderInfoParams->p1);
			if ($ext->total > 0) {
				if ($an_mode == 'an_aim_arb' || $an_mode == 'an_aimcap_arb') {
					$result = self::AuthorizeAIMPay($orderInfo, $credit_info, $params, $TransactionType = 'AUTH_CAPTURE', true);
					if ($result['isApproved'] == false) {
						return self::getErrorMessage('cc', '0000', $result['content']);
					}
					$trialOccurrences = 0;
					$trialAmount = 0.00;
					$startDate = date("Y-m-d", strtotime("+ {$mscTrialRecurrence['length']} {$mscTrialRecurrence['unit']}"));
				} else {
					$trialOccurrences = 1;
					$trialAmount = $ext->total;
					jimport('joomla.utilities.date');
					$curDate = date_create(oseHtml::getDateTime());
					$startDate = date_format($curDate, "Y-m-d");
				}
			} else {
				$mscTrialRecurrence = $this->AuthorizeAPITransInterval($orderInfoParams->t1, $orderInfoParams->p1);
				$startDate = date("Y-m-d", strtotime("+ {$mscTrialRecurrence['length']} {$mscTrialRecurrence['unit']}"));
				$trialAmount = 0.00;
				$trialOccurrences = 0;
			}
		} else {
			// If there is no trial, we use Total, so all periods have coupon applied to it
			$total = $orderInfoParams->total;
			if ($an_mode == 'an_aimcap_arb') {
				// Capture the User's credit card for the order;
				$result = self::AuthorizeAIMPay($orderInfo, $credit_info, $params, $TransactionType = 'AUTH_CAPTURE', true);
				if ($result['isApproved'] == false) {
					return self::getErrorMessage('cc', '0000', $result['content']);
				} else {
					jimport('joomla.utilities.date');
					$curDate = date_create(oseHtml::getDateTime());
					// If we use AIM Capture + ARB, then the start date or ARB should be the first day after the initial period.
					$startDate = date("Y-m-d", strtotime("+ {$mscRegRecurrence['length']} {$mscRegRecurrence['unit']}"));
					$trialOccurrences = 0;
					$trialAmount = 0.00;
				}
			} else {
				jimport('joomla.utilities.date');
				$curDate = date_create(oseHtml::getDateTime());
				$startDate = date_format($curDate, "Y-m-d");
				$trialOccurrences = 0;
				$trialAmount = 0.00;
			}
		}
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'AuthnetARB.class.php');
		$test_mode = $config->cc_testmode;
		$arbsubdomain = ($test_mode) ? 'apitest' : 'api';
		$arb = new AuthnetARB();
		$arb->url = $arbsubdomain . ".authorize.net";
		$arb->setParameter('startDate', $startDate);
		$arb->setParameter('interval_length', $mscRegRecurrence['length']);
		$arb->setParameter('interval_unit', $mscRegRecurrence['unit']);
		$arb->setParameter('totalOccurrences', 9999);
		$arb->setParameter('amount', $total);
		$arb->setParameter('trialOccurrences', $trialOccurrences);
		$arb->setParameter('trialAmount', $trialAmount);
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
		$return = array();
		if ($arb->isSuccessful() == true) {
			$params['payment_serial_number'] = $arb->getSubscriberID();
			$return = self::confirmOrder($order_id, $params, $msc_id, $user_id, 'authorize');
		} elseif ($arb->getResponseCode() == 'E00018') {
			$return = self::confirmOrder($order_id, $params, $msc_id, $user_id, 'authorize');
		} else {
			return self::getErrorMessage('cc', '0000', $arb->getResponse());
		}
		return $return;
	}
	function AuthorizeARBDeleteProfile($payment_serial_number, $refID, $user_id, $msc_id = 0) {
		require_once(JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "libraries" . DS . "class.connection.php");
		require_once(JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "libraries" . DS . "AuthnetARB.class.php");
		$arb = new AuthnetARB();
		$config = oseMscConfig::getConfig('payment', 'obj');
		$test_mode = $config->cc_testmode;
		$an_loginid = $config->an_loginid;
		$an_transkey = $config->an_transkey;
		$an_email_customer = $config->an_email_customer;
		$an_email_merchant = $config->an_email_merchant;
		$an_merchant_email = $config->an_merchant_email;
		$ProfileID = $payment_serial_number;//self:: GetProfileID($order_number);
		$arbsubdomain = ($test_mode) ? 'apitest' : 'api';
		$arb->url = $arbsubdomain . ".authorize.net";
		$arb->setParameter('login', $an_loginid);
		$arb->setParameter('transkey', $an_transkey);
		$arb->setParameter('refID', $refID);
		$arb->setParameter('subscrId', $ProfileID);
		$arb->deleteAccount();
		$result = array();
		$result['code'] = $arb->getResponseCode();
		$result['text'] = $arb->getResponse();
		$result['subscrId'] = $arb->getSubscriberID();
		$result['success'] = $arb->isSuccessful();
		return $result;
	}
	function AuthorizeAPITransInterval($t, $p) {
		$results = array();
		$t = strtolower($t);
		switch ($t) {
		case "year":
			$results['length'] = $p * 12;
			$results['unit'] = 'months';
			$results['unit2'] = 'month';
			break;
		case "month":
			$results['length'] = $p;
			$results['unit'] = 'months';
			$results['unit2'] = 'month';
			break;
		case "week":
			$results['length'] = $p * 7;
			$results['unit'] = 'days';
			$results['unit2'] = 'day';
			break;
		case "day":
			if ($p) {
				$results['length'] = $p;
				$results['unit'] = 'days';
				$results['unit2'] = 'day';
			}
			break;
		}
		return $results;
	}
	function AuthorizeARBCheckStatus($orderId) {
		$db = &JFactory::getDBO();
		$query = " SELECT `order_number` FROM `#__osemsc_order` " . " WHERE `order-id`= '{$orderId}'";
		$db->setQuery($query);
		$ProfileID = $db->loadResult();
		if (!empty($ProfileID)) {
			require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'libraries' . DS . 'AuthnetARB.class.php');
			$arb = new AuthnetARB();
			$arb->setParameter('subscrId', $ProfileID);
			$arb->getSubscriptionStatus();
			return $arb->status;
		}
	}
	function get_2coform($orderInfo) {
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $orderInfo->order_id));//$session->get('oseReturnUrl',false);
		$returnUrl = $returnUrl ? $returnUrl : JURI::base() . "index.php";
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$db = oseDB::instance();
		//$user = JFactory::getUser();
		$test_mode = $pConfig->twoco_testmode;
		$demo = ($test_mode == true) ? 'Y' : 'N';
		$VendorId = $pConfig->twocheckoutVendorId;
		$Secret = $pConfig->twocheckoutSecret;
		$query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = " . $orderInfo->order_id;
		$db->setQuery($query);
		$obj = $db->loadObject();
		$params = oseJson::decode($obj->params);
		$msc_option = $params->msc_option;
		$msc_id = $obj->entry_id;
		$query = "SELECT params FROM `#__osemsc_ext` WHERE `id` = " . $msc_id . " AND `type` = 'paymentAdv'";
		$db->setQuery($query);
		$ext = $db->loadResult();
		$ext = oseJson::decode($ext);
		$product_id = empty($ext->$msc_option->twoco_productid) ? 0 : $ext->$msc_option->twoco_productid;
		$query = " SELECT * FROM `#__osemsc_billinginfo`" . " WHERE `user_id`='{$orderInfo->user_id}'";
		$db->setQuery($query);
		$billingInfo = oseDB::loadItem('obj');
		$juser = JFactory::getUser($orderInfo->user_id);
		$coupon_user_id = empty($orderInfoParams->coupon_user_id) ? 0 : $orderInfoParams->coupon_user_id;
		$query = " SELECT c.code FROM `#__osemsc_coupon` AS c " . " INNER JOIN `#__osemsc_coupon_user` AS u" . " ON c.`id` = u.`coupon_id`" . " WHERE u.`id` = " . $coupon_user_id;
		$db->setQuery($query);
		$coupon = $db->loadResult();
		$coupon = empty($coupon) ? null : $coupon;
		$html = array();
		if (empty($product_id)) {
			if ($orderInfo->payment_mode == 'm') {
				$amount = round($orderInfo->payment_price, 2);
				$post_variables = Array('sid' => $VendorId, 'cart_order_id' => $orderInfo->order_id, 'total' => $amount, 'c_prod_1' => '1,1',
						'c_name_1' => substr(strip_tags(self::generateMSCTitle($orderInfo->order_id)), 0, 127),
						'c_description_1' => substr(strip_tags(self::generateDesc($orderInfo->order_id))), 'c_price_1' => $amount, 'coupon' => $coupon, 'demo' => $demo,
						'merchant_order_id' => $orderInfo->order_number, 'card_holder_name' => $billingInfo->firstname . " " . $billingInfo->lastname,
						'street_address' => $billingInfo->addr1, 'street_address2' => $billingInfo->addr2, 'city' => $billingInfo->city, 'state' => $billingInfo->state,
						'zip' => $billingInfo->postcode, 'country' => $billingInfo->country, 'phone' => $billingInfo->telephone, 'email' => $juser->email,
						'x_receipt_link_url' => $returnUrl, 'id_type' => 1, 'submit' => 'Purchase');
			} else {
				$html['form'] = "";
				$html['url'] = "";
				return $html;
			}
		} else {
			//Authnet vars to send
			$post_variables = array('sid' => $VendorId, 'product_id1' => $product_id, 'quantity1' => 1, 'merchant_order_id' => $orderInfo->order_number, 'demo' => $demo,
					'coupon' => $coupon, 
					// Customer Name and Billing Address
					'card_holder_name' => $billingInfo->firstname . " " . $billingInfo->lastname, 'street_address' => $billingInfo->addr1, 'street_address2' => $billingInfo->addr2,
					'city' => $billingInfo->city, 'state' => $billingInfo->state, 'zip' => $billingInfo->postcode, 'country' => $billingInfo->country,
					'phone' => $billingInfo->telephone, 'email' => $juser->email, 'x_receipt_link_url' => $returnUrl
			);
		}
		$url = "https://www.2checkout.com/checkout/spurchase";
		$html['form'] = '<form action="' . $url . '" method="post">';
		$html['form'] .= '<input type="image" id="2co_image" name="cartImage" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'
				. JText::_('Click to pay with 2Checkout') . '" />';
		$html['url'] = $url . "?";
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
			$html['url'] .= $name . "=" . urlencode($value) . "&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function twoCheckoutDeleteProfile($orderInfo) {
		$pConfig = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$sale_id = $orderInfo->payment_serial_number;
		$ch = curl_init('https://www.2checkout.com/api/sales/detail_sale');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, "{$pConfig->twocheckout_username}:{$pConfig->twocheckout_password}");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('sale_id' => $sale_id));
		$resp = curl_exec($ch);
		curl_close($ch);
		$resp = oseJson::decode($resp);
		$result = array();
		if ($resp->response_code == 'OK') {
			$invoices = $resp->sale->invoices;
			$key = count($invoices) - 1;
			$lineitems = $invoices[$key]->lineitems;
			$lineitem_id = null;
			foreach ($lineitems as $item) {
				if (!empty($item->billing->recurring_status)) {
					$lineitem_id = $item->billing->lineitem_id;
				}
			}
			if (empty($lineitem_id)) {
				$result['success'] = false;
				$result['text'] = 'Recurring invoice not found';
				return $result;
			}
			$ch = curl_init('https://www.2checkout.com/api/sales/stop_lineitem_recurring');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_USERPWD, "{$pConfig->twocheckout_username}:{$pConfig->twocheckout_password}");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('lineitem_id' => $lineitem_id));
			$resp = curl_exec($ch);
			curl_close($ch);
			$resp = oseJson::decode($resp);
			if ($resp->response_code == 'OK') {
				$result['success'] = true;
			} else {
				$result['success'] = false;
				$result['text'] = $resp->errors[0]->message;
			}
		} else {
			$result['success'] = false;
			$result['text'] = $resp->errors[0]->message;
		}
		return $result;
	}
	function getClickBankForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderclickbank.php');
		$clickbank = new osePaymentOrderClickBank();
		$html = $clickbank->ClickBankPostForm($orderInfo);
		return $html;
	}
	function CCAvenueOneOffPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderCCAvenue.php');
		$CCAvenue = new osePaymentOrderCCAvenue();
		$html = $CCAvenue->CCAvenueOneOffPostForm($orderInfo);
		return $html;
	}
	function processUSAePayForm($orderInfo, $creditInfo) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderUSAePay.php');
		$epay = new osePaymentOrderUSAePay();
		if ($orderInfo->payment_mode == 'm') {
			$result = $epay->USAePayOneOffPay($orderInfo, $creditInfo);
		}
		return $result;
	}
	function ICEPAYOffPostForm($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderICEPAY.php');
		$ICEPAY = new osePaymentOrderICEPAY();
		$html = $ICEPAY->ICEPAYOneOffPay($orderInfo);
		return $html;
	}
	function OOSPayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderOOSPay.php');
		$OOSPay = new osePaymentOrderOOSPay();
		$html = $OOSPay->OOSPayOneOffPay($orderInfo);
		return $html;
	}
	function EBSOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderEBS.php');
		$ebs = new osePaymentOrderEBS();
		$html = $ebs->EBSOneOffPay($orderInfo);
		return $html;
	}
	function LiqPayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderLiqPay.php');
		$LiqPay = new osePaymentOrderLiqPay();
		$html = $LiqPay->LiqPayOneOffPay($orderInfo);
		return $html;
	}
	function VirtualMerchantOneOffPay($orderInfo, $creditInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderVirtualMerchant.php');
		$VirtualMerchant = new osePaymentOrderVirtualMerchant();
		$result = $VirtualMerchant->VirtualMerchantOneOffPay($orderInfo, $creditInfo);
		return $result;
	}
	function RealexOneOffPay($orderInfo, $creditInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderRealex.php');
		$Realex = new osePaymentOrderRealex();
		$result = $Realex->RealexOneOffPay($orderInfo, $creditInfo);
		return $result;
	}
	function SisowgetIssuerList() {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderSisow.php');
		$Sisow = new osePaymentOrderSisow();
		$result = $Sisow->getSisowIssuerList();
		return $result;
	}
	function SisowPostForm($orderInfo) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderSisow.php');
		$Sisow = new osePaymentOrderSisow();
		$result = $Sisow->SisowPostForm($orderInfo);
		return $result;
	}
	function PagSeguroOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderPagSeguro.php');
		$PagSeguro = new osePaymentOrderPagSeguro();
		$html = $PagSeguro->PagSeguroOneOffPay($orderInfo);
		return $html;
	}
	function PayGateOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderPayGate.php');
		$PayGate = new osePaymentOrderPayGate();
		$html = $PayGate->PayGateOneOffPay($orderInfo);
		return $html;
	}
	function QuickpayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderQuickpay.php');
		$Quickpay = new osePaymentOrderQuickpay();
		$result = $Quickpay->QuickpayOneOffPay($orderInfo);
		return $result;
	}
	function sagepayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrdersagepay.php');
		$sagepay = new osePaymentOrdersagepay();
		$html = $sagepay->sagepayOneOffPay($orderInfo);
		return $html;
	}
	function AlipayOneOffPay($orderInfo, $params = array()) {
		$curPath = dirname(__FILE__);
		require_once($curPath . DS . 'osePaymentOrderAlipay.php');
		$Alipay = new osePaymentOrderAlipay();
		$result = $Alipay->AlipayOneOffPay($orderInfo);
		return $result;
	}
	function confirmOrder($order_id, $params = array()) {
		$db = oseDB::instance();
		$where = array();
		$where[] = '`order_id` = ' . $db->Quote($order_id);
		$orderInfo = $this->getOrder($where, 'obj');
		if (!isset($params['params'])) {
			$params['params'] = oseJson::decode($orderInfo->params);
			$params['params']->recurrence_times = 1 + oseObject::getValue($params['params'], 'recurrence_times', 0);
			$params['params'] = oseJson::encode($params['params']);
		}
		$this->updateOrder($order_id, "confirmed", $params);
		$user_id = $orderInfo->user_id;
		$payment_mode = $orderInfo->payment_mode;
		$payment_method = $orderInfo->payment_method;
		$user = new JUser($user_id);
		$email = $user->get('email');
		$query = " SELECT * FROM `#__osemsc_order_item`" . " WHERE `order_id` = '{$orderInfo->order_id}'";
		$db->setQuery($query);
		$items = oseDB::loadList('obj');
		foreach ($items as $item) {
			switch ($item->entry_type) {
			case ('license'):
				$license = oseRegistry::call('lic')->getInstance(0);
				$licenseInfo = $license->getKeyInfo($item->entry_id, 'obj');
				$licenseInfoParams = oseJson::decode($licenseInfo->params);
				$msc_id = $licenseInfoParams->msc_id;
				$updated = $this->joinMsc($order_id, $item->order_item_id, $msc_id, $user_id);
				break;
			default:
			case ('msc'):
				$updated = $this->joinMsc($order_id, $item->order_item_id, $item->entry_id, $user_id);
				break;
			}
			if (!$updated['success']) {
				return $updated;
			}
		}
		//Auto reucrring email control
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		$send = true;
		$orderparams = oseJson::decode($params['params']);
		$recurrence_times = oseObject::getValue($orderparams, 'recurrence_times', 1);
		if ($recurrence_times > 1 && oseObject::getValue($emailConfig, 'sendReceiptOnlyOneTime', false)) {
			$send = false;
		}
		if ($orderInfo->payment_mode == 'm' && (empty($orderInfo->payment_price) || $orderInfo->payment_price == '0.00')) {
			if (!oseObject::getValue($emailConfig, 'sendFreeReceipt', false)) {
				$send = false;
			}
		}
		if (oseObject::getValue($emailConfig, 'disabledSendEmailInAdmin', false)) {
			$app = JFactory::getApplication('CPU');
			if ($app->isAdmin()) {
				$send = false;
			}
		}
		if ($send && !empty($emailConfig->default_receipt)) {
			$memEmail = oseRegistry::call('member')->getInstance('Email');
			$receipt = $memEmail->getReceipt($orderInfo);
			$memEmail->sendEmail($receipt, $email);
			if (!empty($emailConfig->sendReceipt2Admin)) {
				$memEmail->sendToAdminGroup($receipt, $emailConfig->admin_group);
			}
		}
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$session = JFactory::getSession();
		$return_url = (isset($orderInfoParams->returnUrl)) ? urldecode($orderInfoParams->returnUrl) : "index.php";
		$return['success'] = true;
		$return['payment'] = $payment_method;
		$return['payment_method'] = $payment_method;
		$return['title'] = JText::_('SUCCESSFUL_ACTIVATION');
		$return['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
		$return['url'] = $return_url;
		$return['returnUrl'] = $return_url;
		$this->updateOrder($order_id, "confirmed");
		osePayment::getInstance('Cart')->init();
		return $return;
	}
	function addVmOrder($msc_id, $member_id, $params, $order_number) {
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		// Get the IP Address
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = 'unknown';
		}
		$post = JRequest::get('post');
		$payment_mode = $params['payment_mode'];
		$payment_method = $params['payment_method'];
		//Insert the vm order table(#__vm_orders)
		$order = array();
		//get membership price
		$payment = oseRegistry::call('payment');
		$paymentInfo = oseMscAddon::getExtInfo($msc_id, 'payment', 'obj');
		if ($payment_mode == 'm') {
			$order_subtotal = $paymentInfo->price;
		} else {
			$order_subtotal = (empty($paymentInfo->has_trial)) ? $paymentInfo->a3 : $paymentInfo->a1;
		}
		$order['order_subtotal'] = $params['payment_price'];
		$order_total = $params['payment_price'];
		$order['order_total'] = $order_total;
		$db = oseDB::instance();
		$query = "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = '" . (int) $member_id . "'  AND (`address_type` = 'BT' OR `address_type` IS NULL)";
		$db->setQuery($query);
		$result = $db->loadResult();
		$hash_secret = "VirtueMartIsCool";
		$user_info_id = empty($result) ? md5(uniqid($hash_secret)) : $result;
		$vendor_id = '1';
		$order['user_id'] = $member_id;
		$order['vendor_id'] = $vendor_id;
		$order['user_info_id'] = $user_info_id;
		$order['order_number'] = $order_number;
		$order['order_currency'] = (!empty($payment->currency)) ? $payment->currency : "USD";
		$order['order_status'] = 'C';
		$order['cdate'] = time();
		$order['ip_address'] = $ip;
		$keys = array_keys($order);
		$keys = '`' . implode('`,`', $keys) . '`';
		foreach ($order as $key => $value) {
			$order[$key] = $db->Quote($value);
		}
		$values = implode(',', $order);
		$query = "INSERT INTO `#__vm_orders` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if (!oseDB::query()) {
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}
		//Insert the #__vm_order_history table
		$order_id = $db->insertid();
		$history = array();
		$history['order_id'] = $order_id;
		$history['order_status_code'] = 'C';
		$history['date_added'] = date("Y-m-d G:i:s", time());
		$history['customer_notified'] = '1';
		$keys = array_keys($history);
		$keys = '`' . implode('`,`', $keys) . '`';
		foreach ($history as $key => $value) {
			$history[$key] = $db->Quote($value);
		}
		$values = implode(',', $history);
		$query = "INSERT INTO `#__vm_order_history` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if (!oseDB::query()) {
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}
		//Insert the Order payment info
		$payment = array();
		$payment['order_id'] = $order_id;
		$payment['payment_method_id'] = $payment_method;
		if ($payment_method == 'authorize') {
		}
		//Insert the User Bill
		$bill = array();
		$query = " SELECT * FROM `#__osemsc_billinginfo`" . " WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$billInfo = oseDB::loadItem();
		if (isset($billInfo)) {
			$bill['company'] = $billInfo['company'];
			$bill['address_1'] = $billInfo['addr1'];
			$bill['address_2'] = $billInfo['addr2'];
			$bill['city'] = $billInfo['city'];
			$bill['state'] = $billInfo['state'];
			$bill['country'] = $billInfo['country'];
			//get vm country code
			$query = " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$bill['country']}' ";
			$db->setQuery($query);
			$country_code = $db->loadResult();
			$bill['country'] = empty($country_code) ? $bill['country'] : $country_code;
			//get vm state code
			$query = " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$bill['state']}' ";
			$db->setQuery($query);
			$state_code = $db->loadResult();
			$bill['state'] = empty($state_code) ? $bill['state'] : $state_code;
			$bill['zip'] = $billInfo['postcode'];
			$bill['phone_1'] = $billInfo['telephone'];
		}
		$query = " SELECT * FROM `#__osemsc_userinfo_view`" . " WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$userInfo = oseDB::loadItem();
		$bill['order_id'] = $order_id;
		$bill['user_id'] = $member_id;
		$bill['address_type'] = 'BT';
		$bill['address_type_name'] = '-default-';
		$bill['last_name'] = $userInfo['lastname'];
		$bill['first_name'] = $userInfo['firstname'];
		$bill['user_email'] = $userInfo['email'];
		$keys = array_keys($bill);
		$keys = '`' . implode('`,`', $keys) . '`';
		foreach ($bill as $key => $value) {
			$bill[$key] = $db->Quote($value);
		}
		$values = implode(',', $bill);
		$query = "INSERT INTO `#__vm_order_user_info` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if (!oseDB::query()) {
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}
		//Insert the itme table(#__vm_order_item)
		$item = array();
		$item['order_id'] = $order_id;
		$item['user_info_id'] = $user_info_id;
		$item['vendor_id'] = $vendor_id;
		//get the product info
		$vm = oseMscAddon::getExtInfo($msc_id, 'vm', 'obj');
		$query = " SELECT * FROM `#__vm_product` WHERE `product_id` = '{$vm->product_id}' ";
		$db->setQuery($query);
		$product = $db->loadObject();
		$item['product_id'] = $vm->product_id;
		$item['order_item_sku'] = $product->product_sku;
		$item['order_item_name'] = $product->product_name;
		$item['product_quantity'] = '1';
		$item['product_item_price'] = $order_subtotal;
		$item['product_final_price'] = $order_total;
		$item['order_item_currency'] = (!empty($payment->currency)) ? $payment->currency : "USD";
		$item['order_status'] = 'C';
		$item['cdate'] = time();
		;
		$keys = array_keys($item);
		$keys = '`' . implode('`,`', $keys) . '`';
		foreach ($item as $key => $value) {
			$item[$key] = $db->Quote($value);
		}
		$values = implode(',', $item);
		$query = "INSERT INTO `#__vm_order_item` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if (!oseDB::query()) {
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}
		$result = array();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Done');
		return $result;
	}
	function autoOrderParams($payment_mode = 'a', $orderParams, $isNew = true, $status = 'confirmed') {
		$params = array();
		$orderParams = oseJson::decode($orderParams);
		if ($payment_mode == 'a') {
			if ($isNew || $status == 'confirmed') {
				if (!isset($orderParams->recurrence_times)) {
					if ($orderParams->has_trial) {
						$recurrence_times = 0;
					} else {
						$recurrence_times = 0;
					}
					$orderParams->recurrence_times = $recurrence_times;
				} else {
					$orderParams->recurrence_times += 1;
				}
			}
		} else {
		}
		return oseJson::encode($orderParams);
	}
	public static function randStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length = (strlen($chars) - 1);
		// Start our string
		$string = $chars{rand(0, $chars_length)};
		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string)) {
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};
			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1})
				$string .= $r;
		}
		// Return the string
		return $string;
	}
	function updateOrder($order_id, $status, $params = array()) {
		$db = oseDB::instance();
		$params['order_status'] = $status;
		$values = array();
		foreach ($params as $key => $value) {
			$values[$key] = '`' . $key . '`=' . $db->Quote($value);
		}
		$values = implode(',', $values);
		$query = " UPDATE `{$this->table}` " . " SET {$values}" . " WHERE order_id = {$order_id}";
		$db->setQuery($query);
		if (oseDB::query()) {
			return true;
		} else {
			return false;
		}
	}
	function updateOrderParams($orderInfo, $params) {
		$orderInfoParams = oseObject::getValue($orderInfo, 'params');
		$orderInfoParams = oseJson::decode($orderInfoParams);
		if (!is_Array($params)) {
			$params = (array) $params;
		}
		foreach ($params as $key => $value) {
			$orderInfoParams = oseObject::setValue($orderInfoParams, $key, $value);
		}
		$orderInfoParams = oseJson::encode($orderInfoParams);
		$orderInfo = oseObject::setValue($orderInfo, 'params', $orderInfoParams);
		return $orderInfo;
	}
	function updateMembership($msc_id, $user_id, $order_id, $payment_mode) {
		$db = oseDB::instance();
		$params['order_id'] = $order_id;
		$params['payment_mode'] = $payment_mode;
		$params = oseJSON::encode($params);
		$query = " UPDATE `#__osemsc_member` SET `params`='$params' WHERE `msc_id` = '{$msc_id}' AND `member_id` = '$user_id'";
		$db->setQuery($query);
		if (oseDB::query()) {
			return true;
		} else {
			return false;
		}
	}
	function getOrderItem($where = array(), $type = 'array') {
		$db = oseDB::instance();
		$where = oseDB::implodeWhere($where);
		$where = str_replace('order_id', 'order_item_id', $where);
		$query = " SELECT * FROM `#__osemsc_order_item` " . $where . ' ORDER BY create_date DESC' . ' LIMIT 1';
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	function getOrderItems($order_id, $type = 'array') {
		$db = oseDB::instance();
		$where = array();
		$where[] = '`order_id`=' . $db->Quote($order_id);
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_order_item` " . $where . ' ORDER BY create_date ASC';
		$db->setQuery($query);
		$items = oseDB::loadList($type);
		return $items;
	}
	function generateMSCTitle($order_id) {
		$where = array(' order_id = ' . (int) $order_id);
		$orderItems = $this->getOrderItem($where, 'obj');
		$node = oseMscTree::getNode($orderItems->entry_id, 'obj');
		return JText::_('ORDER_ID') . ' ' . $order_id . ": " . $node->title;
	}
	function generateDesc($order_id) {
		$where = array(' order_id = ' . (int) $order_id);
		$orderItems = $this->getOrderItem($where, 'obj');
		$node = oseMscTree::getNode($orderItems->entry_id, 'obj');
		return JText::_('PAYMENT_FOR_ORDER') . $node->title . ' ' . JText::_('ORDER_ID') . ' ' . $order_id;
	}
	function refundOrder($order_id, $params = array()) {
		$db = oseDB::instance();
		$this->updateOrder($order_id, "refunded", $params);
		$where = array();
		$where[] = '`order_id` = ' . $db->Quote($order_id);
		$orderInfo = $this->getOrder($where, 'obj');
		$user_id = $orderInfo->user_id;
		$query = " SELECT * FROM `#__osemsc_order_item`" . " WHERE `order_id` = '{$orderInfo->order_id}'";
		$db->setQuery($query);
		$items = oseDB::loadList('obj');
		foreach ($items as $item) {
			switch ($item->entry_type) {
			case ('license'):
				$license = oseRegistry::call('lic')->getInstance(0);
				$licenseInfo = $license->getKeyInfo($item->entry_id, 'obj');
				$licenseInfoParams = oseJson::decode($licenseInfo->params);
				$msc_id = $licenseInfoParams->msc_id;
				$updated = $this->cancelMsc($order_id, $item->order_item_id, $msc_id, $user_id);
				break;
			default:
			case ('msc'):
				$updated = $this->cancelMsc($order_id, $item->order_item_id, $item->entry_id, $user_id);
				break;
			}
			if (!$updated['success']) {
				return $updated;
			}
		}
		$return['success'] = true;
		$return['title'] = JText::_('Success');
		$return['content'] = JText::_('Refunded Successfully');
		$session = JFactory::getSession();
		$session->set('osecart', array());
		return $return;
	}
	private function cancelMsc($order_id, $order_item_id, $msc_id, $user_id) {
		$params = oseRegistry::call('member')->getAddonParams($msc_id, $user_id, $order_id, array('order_item_id' => $order_item_id));
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
		$updated = oseMscAddon::runAction('member.msc.cancelMsc', $params, true, false);
		return $updated;
	}
}
?>