<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die(";)");
class oseMscAddonActionRegisterPayment {
	public static function save($params) {
		$member_id = $params['member_id'];
		JRequest::setVar('member_id', $member_id);
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			return self::saveCart($params);
		} else {
			return self::saveOS($params);
		}
	}
	public static function AddVmOrder($msc_id, $params, $order_number, $paymentInfo) {
		$member_id = JRequest::getVar('member_id', '0');
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
		$payment_mode = $post['payment_payment_mode'];
		$payment_method = $post['payment_payment_method'];
		//Insert the vm order table(#__vm_orders)
		$order = array();
		//get membership price
		$payment = oseRegistry::call('payment');
		if ($payment_mode == 'm') {
			$order_subtotal = $paymentInfo->price;
		} else {
			$order_subtotal = (empty($paymentInfo->has_trial)) ? $paymentInfo->a3 : $paymentInfo->a1;
		}
		$order['order_subtotal'] = $order_subtotal;
		$payment = oseRegistry::call('payment');
		$session = &JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency', oseRegistry::call('msc')->getConfig('currency', 'obj')->primary_currency);
		$price = $paymentInfo->a3;
		if ($payment_mode == 'a') {
			if ($paymentInfo->has_trial) {
				$price = $paymentInfo->a1;
			}
		}
		$order_total = $payment->pricing($price, $msc_id, $osePaymentCurrency);
		$order['order_total'] = $order_total;
		$db = JFactory::getDBO();
		//$order['order_tax'] = '0.00';
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
		$order['order_status'] = 'P';
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
		$history['order_status_code'] = 'P';
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
		if (isset($post['company_company'])) {
			$bill['company'] = $post['company_company'];
			$bill['address_1'] = $post['company_addr1'];
			$bill['address_2'] = $post['company_addr2'];
			$bill['city'] = $post['company_city'];
			$bill['state'] = $post['company_state'];
			$bill['country'] = $post['company_country'];
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
			$bill['zip'] = $post['company_postcode'];
			$bill['phone_1'] = $post['company_telephone'];
		} else {
			$bill['address_1'] = $post['bill_addr1'];
			$bill['city'] = $post['bill_city'];
			$bill['state'] = $post['bill_state'];
			$bill['country'] = $post['bill_postcode'];
		}
		$bill['order_id'] = $order_id;
		$bill['user_id'] = $member_id;
		$bill['address_type'] = 'BT';
		$bill['address_type_name'] = '-default-';
		$bill['last_name'] = $post['juser_lastname'];
		$bill['first_name'] = $post['juser_firstname'];
		$bill['user_email'] = $post['juser_email'];
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
		$vm = oseRegistry::call('msc')->getExtInfo($msc_id, 'vm', 'obj');
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
		$item['order_status'] = 'P';
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
	function getMethod() {
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$methods = array();
		if (!empty($pConfig->enable_cc)) {
			$cc_methods = explode(',', $pConfig->cc_methods);
			foreach ($cc_methods as $cc_method) {
				switch ($cc_method) {
				case ('authorize'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('paypal_cc'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('eway'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('epay'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('vpcash_cc'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('usaepay'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('oospay'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				case ('ebs'):
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				default:
					$methods[] = array('id' => 4, 'value' => $cc_method, 'text' => JText::_('Credit_Card'));
					break;
				}
			}
		}
		if (!empty($pConfig->enable_paypal)) {
			$methods[] = array('id' => 1, 'value' => 'paypal', 'text' => JText::_('Paypal'));
		}
		if (!empty($pConfig->enable_gco)) {
			$methods[] = array('id' => 2, 'value' => 'gco', 'text' => JText::_('Google_Checkout'));
		}
		if (!empty($pConfig->enable_twoco)) {
			$methods[] = array('id' => 3, 'value' => '2co', 'text' => JText::_('2Checkout'));
		}
		if (!empty($pConfig->enable_poffline)) {
			$methods[] = array('id' => 5, 'value' => 'poffline', 'text' => JText::_('Pay_Offline'));
		}
		if (!empty($pConfig->enable_vpcash)) {
			$methods[] = array('id' => 6, 'value' => 'vpcash', 'text' => JText::_('VirtualPayCash'));
		}
		if (!empty($pConfig->enable_bbva)) {
			$methods[] = array('id' => 7, 'value' => 'bbva', 'text' => JText::_('BBVA'));
		}
		if (!empty($pConfig->enable_payfast)) {
			$methods[] = array('id' => 8, 'value' => 'payfast', 'text' => JText::_('PayFast'));
		}
		if (!empty($pConfig->enable_clickbank)) {
			$methods[] = array('id' => 9, 'value' => 'clickbank', 'text' => JText::_('ClickBank'));
		}
		if (!empty($pConfig->enable_ccavenue)) {
			$methods[] = array('id' => 10, 'value' => 'ccavenue', 'text' => JText::_('CCAvenue'));
		}
		if (!empty($pConfig->enable_icepay)) {
			$methods[] = array('id' => 11, 'value' => 'icepay', 'text' => JText::_('ICEPAY'));
		}
		if (!empty($pConfig->enable_liqpay)) {
			$methods[] = array('id' => 12, 'value' => 'liqpay', 'text' => JText::_('LiqPay'));
		}
		if (!empty($pConfig->enable_realex)) {
			$methods[] = array('id' => 13, 'value' => 'realex_' . $pConfig->realex_mode, 'text' => JText::_('Realex Payments'));
		}
		if (!empty($pConfig->enable_sisow)) {
			$methods[] = array('id' => 14, 'value' => 'sisow', 'text' => JText::_('Sisow'));
		}
		if (!empty($pConfig->enable_pagseguro)) {
			$methods[] = array('id' => 15, 'value' => 'pagseguro', 'text' => JText::_('PagSeguro'));
		}
		if (!empty($pConfig->enable_paygate)) {
			$methods[] = array('id' => 16, 'value' => 'paygate', 'text' => JText::_('PayGate'));
		}
		if (!empty($pConfig->enable_quickpay)) {
			$methods[] = array('id' => 17, 'value' => 'quickpay', 'text' => JText::_('Quickpay'));
		}
		if (!empty($pConfig->enable_sagepay)) {
			$methods[] = array('id' => 18, 'value' => 'sagepay', 'text' => JText::_('sagepay'));
		}
		if (!empty($pConfig->enable_alipay)) {
			$methods[] = array('id' => 19, 'value' => 'alipay', 'text' => JText::_('Alipay'));
		}
		$msc_id = JRequest::getInt('msc_id');
		if (!empty($msc_id)) {
			$db = oseDB::instance();
			$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'hidepayment'";
			$db->setQuery($query);
			$data = $db->loadObject();
			$data = oseJson::decode($data->params);
			if (!empty($data->enable) && !empty($data->value)) {
				$values = explode(",", $data->value);
				foreach ($methods as $key => $val) {
					if (in_array($val['value'], $values)) {
						unset($methods[$key]);
					}
				}
			}
		}
		$methods = array_values($methods);
		$result = array();
		$result['total'] = count($methods);
		$result['results'] = $methods;
		return $result;
	}
	function getPaymentMode() {
		$payment_mode = oseRegistry::call('msc')->getConfig('payment_mode', 'global');
		if ($payment_mode == 'm') {
			$option[] = array('id' => 1, 'value' => 'm', 'text' => JText::_('Manual Billing'));
		} elseif ($payment_mode == 'a') {
			//$option[] = array('id'=>1,'value'=>'a','text'=>JText::_('Automatic Billing'));
		} else {
			$option[] = array('id' => 1, 'value' => 'm', 'text' => JText::_('Manual Billing'));
			//$option[] = array('id'=>2,'value'=>'a','text'=>JText::_('Automatic Billing'));
		}
		$options = array();
		foreach ($option as $key => $value) {
			$options[] = JHTML::_('select.option', $value['value'], $value['text']);
		}
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$items = $cart->get('items');
		$keys = array_keys($items);
		$selected_payment_mode = oseObject::getValue($items[$keys[0]], 'payment_mode');
		$combo = JHTML::_('select.genericlist', $options, 'ose_payment_mode', 'onChange="javascript:oseMsc.reg.reload()" class="ose_currency"  size="1" style="width:200px"',
				'value', 'text', $selected_payment_mode);
		return $combo;
	}
	private static function saveCart($params) {
		$result = array();
		$post = JRequest::get('post');
		// check the parameters required exist
		$member_id = $params['member_id'];
		$payment_method = $params['payment_method'];
		JRequest::setVar('member_id', $member_id);
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		$params = array();
		$paymentCart = oseMscPublic::getCart();
		$subtotal = $paymentCart->getSubtotal();
		$items = $paymentCart->get('items');
		$osePaymentCurrency = $paymentCart->get('currency');
		$payment = oseRegistry::call('payment');
		$keys = array_keys($items);
		// force the payment_mode to manual, only m
		$payment_mode = $paymentCart->getParams('payment_mode');
		if (empty($payment_mode)) {
			$payment_mode = oseMscPublic::savePaymentMode();
		}
		// generate order params
		$order_number = $payment->generateOrderNumber($member_id);
		$params['entry_type'] = 'msc_list';
		$params['payment_price'] = $paymentCart->get('total');
		$params['payment_currency'] = $osePaymentCurrency;
		$params['order_number'] = $order_number;
		$params['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
		$params['payment_serial_number'] = substr($order_number, 0, 20);
		$params['payment_method'] = $payment_method;
		$params['payment_mode'] = $payment_mode;
		$params['payment_from'] = 'system_reg';
		// generate order params values
		$params['params'] = array();
		$params['params']['total'] = $paymentCart->get('total');
		$params['params']['next_total'] = $paymentCart->get('next_total');
		$params['params']['discount'] = $paymentCart->get('discount');
		$params['params']['subtotal'] = $subtotal;
		$params['params']['coupon_user_id'] = $paymentCart->getParams('coupon_user_id');
		$params['params']['gross_tax'] = $paymentCart->getTaxParams('amount');
		$params['params']['next_gross_tax'] = $paymentCart->getTaxParams('next_amount');
		$params['params']['vat_number'] = $paymentCart->getTaxParams('vat_number');
		$params['params']['timestamp'] = uniqid("{$member_id}_", true);
		$params['params']['returnUrl'] = $paymentCart->getParams('returnUrl');
		// no auto recurring currently
		if ($payment_mode == 'a') {
			//$params['params']['has_trial'] = oseObject::getValue($oneItem,'has_trial',0);
		} else {
			$params['params']['has_trial'] = 0;
		}
		$params['params'] = oseJSON::encode($params['params']);
		$paymentOrder = $payment->getInstance('Order');
		$updated = $paymentOrder->generateOrder('', $member_id, $params);
		if ($updated) {
			$order_id = $updated;
			JRequest::setVar('order_id', $order_id);
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		/* generate order item
		 *
		 */
		foreach ($items as $item) {
			$itemParams = array();
			$entry_type = oseObject::getValue($item, 'entry_type');
			switch ($entry_type) {
			case ('license'):
				$license_id = oseObject::getValue($item, 'entry_id');
				$license = oseRegistry::call('lic')->getInstance(0);
				$licenseInfo = $license->getKeyInfo($license_id, 'obj');
				$licenseInfoParams = oseJson::decode($licenseInfo->params);
				$msc_id = $licenseInfoParams->msc_id;
				break;
			case ('msc'):
				$msc_id = oseObject::getValue($item, 'entry_id');
				break;
			}
			$msc_option = oseObject::getValue($item, 'msc_option');
			$price = oseObject::getValue($item, 'a3');
			if ($payment_mode == 'a') {
				if (oseObject::getValue($item, 'has_trial')) {
					$price = oseObject::getValue($item, 'a1');
				}
			}
			$itemParams['entry_type'] = oseObject::getValue($item, 'entry_type');
			$itemParams['payment_price'] = oseObject::getValue($item, 'first_raw_price');
			$itemParams['payment_currency'] = $osePaymentCurrency;
			$itemParams['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
			$price_params = $payment->generateOrderParams($msc_id, $price, $payment_mode, $msc_option);
			$price_params['start_date'] = oseObject::getValue($item, 'start_date', null);
			$price_params['expired_date'] = oseObject::getValue($item, 'expired_date', null);
			$price_params['recurrence_mode'] = oseObject::getValue($item, 'recurrence_mode', 'period');
			$itemParams['params'] = oseJSON::encode($price_params);
			$paymentInfos = oseMscAddon::getExtInfo($msc_id, 'payment', 'obj');
			$paymentInfo = oseObject::getValue($paymentInfos, $msc_option);
			if ($paymentInfo->payment_mode == $payment_mode || $paymentInfo->payment_mode == 'b') {
				$itemParams['payment_mode'] = $payment_mode;
			} else {
				$itemParams['payment_mode'] = $paymentInfo->payment_mode;
			}
			$updated = $paymentOrder->generateOrderItem($order_id, oseObject::getValue($item, 'entry_id'), $itemParams);
		}
		// end
		if ($updated) {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Order Generate Error');
		}
		return $result;
	}
	private static function saveOS($params) {
		$result = array();
		$member_id = $params['member_id'];
		$payment_method = $params['payment_method'];
		JRequest::setVar('member_id', $member_id);
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error1');
			return $result;
		}
		$params = array();
		$paymentCart = $cart = oseRegistry::call('payment')->getInstance('Cart');
		$paymentCart->refreshCartItems($paymentCart->get('items'), $paymentCart->get('currency'));
		$osePaymentCurrency = $paymentCart->get('currency');
		$payment_mode = $paymentCart->getParams('payment_mode');
		$subtotal = $paymentCart->getSubtotal();
		if (empty($payment_mode)) {
			$payment_mode = oseMscPublic::savePaymentMode();
		}
		$items = $paymentCart->get('items');
		$oneItem = $items[0];
		$payment = oseRegistry::call('payment');
		$keys = array_keys($items);
		$order_number = $payment->generateOrderNumber($member_id);
		$params['entry_type'] = 'msc_list';
		$params['payment_price'] = $paymentCart->get('total');
		$params['payment_currency'] = $osePaymentCurrency;
		$params['order_number'] = $order_number;
		$params['create_date'] = oseHTML::getDateTime();
		$params['payment_serial_number'] = substr($order_number, 0, 20);
		$params['payment_method'] = $payment_method;
		$params['payment_mode'] = $payment_mode;
		$params['payment_from'] = 'system_reg';
		$params['params'] = array();
		$params['params']['start_date'] = oseObject::getValue($oneItem, 'start_date', null);
		$params['params']['expired_date'] = oseObject::getValue($oneItem, 'expired_date', null);
		$params['params']['total'] = $paymentCart->get('total');
		$params['params']['next_total'] = $paymentCart->get('next_total');
		$params['params']['discount'] = $paymentCart->get('discount');
		$params['params']['subtotal'] = $subtotal;
		$params['params']['coupon_user_id'] = $paymentCart->getParams('coupon_user_id');
		$params['params']['gross_tax'] = $paymentCart->getTaxParams('amount');
		$params['params']['next_gross_tax'] = $paymentCart->getTaxParams('next_amount');
		$params['params']['vat_number'] = $paymentCart->getTaxParams('vat_number');
		$params['params']['timestamp'] = uniqid("{$member_id}_", true);
		$params['params']['returnUrl'] = $paymentCart->getParams('returnUrl');
		if ($payment_mode == 'a') {
			$params['params']['has_trial'] = oseObject::getValue($oneItem, 'has_trial', 0);
		} else {
			$params['params']['has_trial'] = 0;
		}
		$sisow_issuerid = JRequest::getVar('sisow_issuerid');
		if ($sisow_issuerid) {
			$params['params']['sisow_issuerid'] = $sisow_issuerid;
		}
		$sisow_payment = JRequest::getVar('sisow_payment');
		if ($sisow_payment) {
			$params['params']['sisow_payment'] = $sisow_payment;
		}
		$params['params']['a1'] = $paymentCart->get('total');
		$params['params']['p1'] = oseObject::getValue($oneItem, 'p1', 0);
		$params['params']['t1'] = oseObject::getValue($oneItem, 't1');
		$params['params']['a3'] = $paymentCart->get('next_total');
		$params['params']['p3'] = oseObject::getValue($oneItem, 'p3', 0);
		$params['params']['t3'] = oseObject::getValue($oneItem, 't3');
		$params['params'] = oseJSON::encode($params['params']);
		$list = oseMscAddon::getAddonList('register_order', false, 1, 'obj');
		foreach ($list as $addon) {
			$action_name = 'register_order.' . $addon->name . '.add';
			$params = oseMscAddon::runAction($action_name, $params);
		}
		$paymentOrder = $payment->getInstance('Order');
		$updated = $paymentOrder->generateOrder('', $member_id, $params);
		if ($updated) {
			$order_id = $updated;
			$result['order_id'] = $order_id;
			JRequest::setVar('order_id', $order_id);
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error2');
			return $result;
		}
		foreach ($items as $item) {
			$itemParams = array();
			$entry_type = oseObject::getValue($item, 'entry_type');
			switch ($entry_type) {
			case ('license'):
				$license_id = oseObject::getValue($item, 'entry_id');
				$license = oseRegistry::call('lic')->getInstance(0);
				$licenseInfo = $license->getKeyInfo($license_id, 'obj');
				$licenseInfoParams = oseJson::decode($licenseInfo->params);
				$msc_id = $licenseInfoParams->msc_id;
				break;
			case ('msc'):
				$msc_id = oseObject::getValue($item, 'entry_id');
				break;
			}
			$msc_option = oseObject::getValue($item, 'msc_option');
			if (oseObject::getValue($item, 'eternal')) {
				$itemParams['payment_mode'] = 'm';
			} else {
				$itemParams['payment_mode'] = $payment_mode;
			}
			$price = oseObject::getValue($item, 'a3');
			if ($payment_mode == 'a') {
				if (oseObject::getValue($item, 'has_trial')) {
					$price = oseObject::getValue($item, 'a1');
				}
			}
			$itemParams['entry_type'] = oseObject::getValue($item, 'entry_type');
			$itemParams['payment_price'] = oseObject::getValue($item, 'first_raw_price');
			$itemParams['payment_currency'] = $osePaymentCurrency;
			$itemParams['create_date'] = oseHTML::getDateTime();
			$price_params = $payment->generateOrderParams($msc_id, $price, $payment_mode, $msc_option);
			$price_params['start_date'] = oseObject::getValue($item, 'start_date', null);
			$price_params['expired_date'] = oseObject::getValue($item, 'expired_date', null);
			$price_params['recurrence_mode'] = oseObject::getValue($item, 'recurrence_mode', 'period');
			$itemParams['params'] = oseJSON::encode($price_params);
			$paymentInfos = oseMscAddon::getExtInfo($msc_id, 'payment', 'obj');
			$paymentInfo = oseObject::getValue($paymentInfos, $msc_option);
			$updated = $paymentOrder->generateOrderItem($order_id, oseObject::getValue($item, 'entry_id'), $itemParams);
		}
		if ($updated) {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Order Generate Error');
		}
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		if (!empty($emailConfig->order_notification)) {
			$db = oseDB::instance();
			$where = array();
			$where[] = '`order_id` = ' . $db->Quote($order_id);
			$orderInfo = $paymentOrder->getOrder($where, 'obj');
			$memEmail = oseRegistry::call('member')->getInstance('Email');
			$receipt = $memEmail->getOrderNotification($orderInfo);//print_r($receipt);exit;
			$memEmail->sendToAdminGroup($receipt, $emailConfig->admin_group);
		}
		return $result;
	}
	function getSubTotal() {
		$cart = oseMscPublic::getCart();
		$subtotal = $cart->get('currency') . ' ' . $cart->get('subtotal');
		$result = array();
		$result['success'] = true;
		$result['content'] = array('subtotal' => $subtotal);
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
?>