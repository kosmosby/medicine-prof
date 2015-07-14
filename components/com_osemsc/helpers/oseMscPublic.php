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
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R);
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
class oseMscPublic {
	function getSelectedCurrency() {
		$session = JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency', oseRegistry::call('msc')->getConfig('currency', 'obj')->primary_currency);
		return $osePaymentCurrency;
	}
	function setSelectedCurrency($ose_currency) {
		$session = JFactory::getSession();
		$osePaymentCurrency = $session->set('osePaymentCurrency', $ose_currency);
		//oseRegistry::call('payment')->getInstance('Cart')->setSelectedCurrency($ose_currency);
		return $osePaymentCurrency;
	}
	static function getCart() {
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		return $cart;
	}
	static function getCartItems() {
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$items = $cart->refreshCartItems($cart->get('items'), $cart->get('currency'));
		$cart->update();
		return $items;
	}
	function setCartItemsPayment($payment_mode) {
		$currency = self::getSelectedCurrency();
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$items = $cart->setCartItems($cart->cart['items'], 'payment_mode', 'm');
		$cart->update();
	}
	function getSubTotal() {
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$total = $cart->getSubtotal();
		return $total;
	}
	function setSubTotal($subtotal) {
		$currency = self::getSelectedCurrency();
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$cart->setSubTotal($subtotal);
		$cart->update();
	}
	function getUserInfo($user_id) {
		$user = oseRegistry::call('member')->getInstance('User');
		$user->instance($user_id);
		return $user->getUserInfo();
	}
	function removeCartItem($id) {
	}
	function setPaymentMode($payment_mode) {
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$params = $cart->get('params', array());
		$params['payment_mode'] = $payment_mode;
		$cart->set('params', $params);
		$cart->update();
	}
	function uniqueUserName($username, $user_id) {
		$result = array();
		$result['success'] = false;
		if (empty($username)) {
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('This field is required');
		} else {
			$db = oseDB::instance();
			$where = array();
			$username = $db->Quote(strtolower($username));
			$where[] = "LOWER(username) = {$username}";
			if (!empty($user_id)) {
				$where[] = "id != '{$user_id}'";
			}
			$where = oseDB::implodeWhere($where);
			$query = " SELECT COUNT(*) FROM `#__users`" . $where;
			$db->setQuery($query);
			$isValid = ($db->loadResult() > 0) ? false : true;
			if ($isValid) {
				$result['success'] = true;
			} else {
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_('This username has been registered by other user.');
			}
		}
		return $result;
	}
	function clearCart() {
		$cart = oseMscPublic::getCart();
		$cart->cart = array();
		$cart->update();
		oseExit('dfasdf');
	}
	public static function getPaymentMode($key = 'payment_payment_mode') {
		$default = oseRegistry::call('msc')->getConfig('register', 'obj')->onestep_payment_mode;
		return JRequest::getCmd($key, $default);
	}
	function getOrderItem() {
	}
	function isTable($table) {
		$db = oseDB::instance();
		$prefix = $db->getPrefix();
		$newTable = str_replace('#__', $prefix, $table);
		$list = $db->getTableList();
		if (in_array($newTable, $list)) {
			return true;
		} else {
			return false;
		}
	}
	public static function getEntryMscID($item) {
		switch (oseObject::getValue($item, 'entry_type')) {
		case ('license'):
			$msc_id = oseObject::getValue($item, 'id');
			break;
		case ('msc'):
		default:
			$msc_id = oseObject::getValue($item, 'id');
			break;
		}
		return $msc_id;
	}
	public static function processPayment($payment_method, $orderInfo, $post) {
		$switch = 1;
		if ($switch == 2) {
			// Testing Purpose; please do not delete;
			//$payment= oseRegistry :: call('payment');
			//$gateway = $payment->getGateWay($payment_method);
		}
		$order_id = $orderInfo->order_id;
		$payment = oseRegistry::call('payment');
		$config = oseRegistry::call('msc')->getConfig('', 'obj');
		//check payment method
		$db = oseDB::instance();
		$query = "SELECT entry_id FROM `#__osemsc_order_item` WHERE `order_id` = " . $order_id;
		$db->setQuery($query);
		$msc_id = $db->loadResult();
		$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'hidepayment'";
		$db->setQuery($query);
		$data = $db->loadObject();
		if (!empty($data->params))
		{	
			$data = oseJson::decode($data->params);
			if (!empty($data->enable) && !empty($data->value)) {
				$values = explode(",", $data->value);
				if (in_array($payment_method, $values)) {
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = $payment_method . ' ' . JText::_('CAN_NOT_USE_ON_THIS_MEMBERSIHP');
					$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
					return $result;
				}
			}
		}
		$creditInfo = array();
		if (isset($post['creditcard_number'])) {
			$creditInfo['creditcard_type'] = $post['creditcard_type'];
			$creditInfo['creditcard_name'] = $post['creditcard_name'];
			$creditInfo['creditcard_owner'] = $creditInfo['creditcard_name'];
			$creditInfo['creditcard_number'] = $post['creditcard_number'];
			$creditInfo['creditcard_year'] = $post['creditcard_year'];
			$creditInfo['creditcard_month'] = $post['creditcard_month'];
			$creditInfo['creditcard_expirationdate'] = $post['creditcard_year'] . '-' . $post['creditcard_month'];
			$creditInfo['creditcard_cvv'] = $post['creditcard_cvv'];
		}
		switch ($payment_method) {
		case ('authorize'):
			if (!$config->enable_authorize) {
				return false;
			}
			$updated = $payment->processCCForm($orderInfo, $creditInfo, 'authorize');
			if ($updated['success']) {
				$updated['success'] = true;
				$updated['title'] = JText::_('SUCCESSFUL_ACTIVATION');
				$updated['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
				$updated['payment_method'] = 'authorize';
			}
			$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			return $updated;
			break;
		case ('paypal_cc'):
			$expiration = $post['creditcard_year'] . '-' . $post['creditcard_month'];
			$creditInfo['creditcard_expirationdate'] = $expiration;
			$updated = $payment->processCCForm($orderInfo, $creditInfo, 'paypal_cc');
			$updated['payment_method'] = 'paypal_cc';
			if (JFile::exists(JPATH_SITE . DS . 'modules' . DS . 'mod_ose_gag' . DS . 'helper.php')) {
				require_once(JPATH_SITE . DS . 'modules' . DS . 'mod_ose_gag' . DS . 'helper.php');
				$mod = JModuleHelper::getModule('ose_gag');
				if (JOOMLA16) {
					$modParams = new JRegistry;
					$modParams->loadJSON($mod->params);
				} else {
					$modParams = new JParameter($mod->params);
				}
				$db = oseDB::instance();
				$where = array('`order_id`=' . $db->Quote($orderInfo->order_id));
				$code = osegagHelper::getOrderCode($modParams->get('account'), $where);
				$code = explode("\r\n", $code);
				array_shift($code);
				array_pop($code);
				if (empty($code)) {
					$code = false;
				} else {
					$code = implode("", $code);
				}
				$updated['code'] = $code;
			}
			if (class_exists('plgSystemoseMscGoogleAnalytics')) {
				$config = oseMscConfig::getConfig('thirdparty', 'obj');
				$account = $config->gag_account;
				$code = oseMscPublic::ajaxOrderTrack($account, $order_id);
				if (empty($code)) {
					$code = false;
				} else {
					$code = implode("", $code);
				}
				$updated['code'] = $code;
			}
			$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			return $updated;
			break;
		case ('paypal'):
			$html = $payment->getPaypalForm($orderInfo);
			$result = array();
			$result['success'] = true;
			$result['html'] = $html;
			$result['payment_method'] = 'paypal';
			$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			return $result;
			break;
		case ('eway'):
			$expiration = $post['creditcard_year'] . '-' . $post['creditcard_month'];
			$creditInfo['creditcard_expirationdate'] = $expiration;
			$updated = $payment->processCCForm($orderInfo, $creditInfo, 'eway');
			$config = oseMscConfig::getConfig('thirdparty', 'obj');
			$account = oseObject::getValue($config, 'gag_account');
			$standard_type = oseObject::getValue($config, 'gag_domain_mode');
			$domain = oseObject::getValue($config, 'gag_domain');
			$htmlTrack = oseMscPublic::htmlTrack($account, $standard_type, $domain, $order_id);
			$code = oseMscPublic::ajaxOrderTrack($account, $order_id);
			if (empty($code)) {
				$code = false;
			} else {
				$code = implode("", $code);
			}
			if (!empty($code) && !empty($htmlTrack)) {
				$updated['code'] = $code;
				$updated['htmlTrack'] = $htmlTrack;
			}
			if ($updated['success']) {
				$updated['success'] = true;
				$updated['title'] = JText::_('SUCCESSFUL_ACTIVATION');
				$updated['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
				$updated['payment_method'] = 'eway';
			}
			$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			return $updated;
			break;
		case ('epay'):
			if ($orderInfo->payment_mode == 'a') {
				$html = $payment->getInstance('Order')->ePayCreateProfile($orderInfo);
				if (!$html) {
					$result['success'] = false;
					$result['title'] = 'Epay Free Trial Banned';
					$result['payment_method'] = 'It does not support free trial subscription yet. If it still has problem, please contact the webmaster!';
					$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
					return $result;
				}
			} else {
				$html = $payment->getInstance('Order')->ePayOneOffPay($orderInfo);
			}
			$result = array();
			$result['success'] = true;
			$result['html'] = $html;
			$result['payment_method'] = 'epay';
			return $result;
			break;
		case ('pnw'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Payment Network Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
				return $result;
			} else {
				$html = $payment->getInstance('Order')->PNWOneOffPay($orderInfo);
			}
			$result = array();
			$result['success'] = true;
			$result['html'] = $html;
			$result['payment_method'] = $payment_method;
			return $result;
			break;
		case ('beanstream'):
			$updated = $payment->processCCForm($orderInfo, $creditInfo, 'beanstream');
			$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			$config = oseMscConfig::getConfig('thirdparty', 'obj');
			$account = oseObject::getValue($config, 'gag_account');
			$standard_type = oseObject::getValue($config, 'gag_domain_mode');
			$domain = oseObject::getValue($config, 'gag_domain');
			$htmlTrack = oseMscPublic::htmlTrack($account, $standard_type, $domain, $order_id);
			$code = oseMscPublic::ajaxOrderTrack($account, $order_id);
			if (empty($code)) {
				$code = false;
			} else {
				$code = implode("", $code);
			}
			if (!empty($code) && !empty($htmlTrack)) {
				$updated['code'] = $code;
				$updated['htmlTrack'] = $htmlTrack;
			}
			return $updated;
			break;
		case ('vpcash_cc'):
		case ('vpcash'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'VirtualPayCash Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->VpcashOneOffPostForm($orderInfo, $payment_method);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('bbva'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'BBVA Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->BBVAOneOffPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('payfast'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Payfast Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->PayFastOneOffPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('gco'):
			if ($orderInfo->payment_mode == 'a') {
				$html = $payment->getInstance('Order')->GcoRecurringPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			} else {
				$html = $payment->getInstance('Order')->GcoOneOffPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('ewaysh'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->eWaySharedHostingPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('2co'):
			$html = $payment->get2COForm($orderInfo);
			$result = array();
			$result['success'] = true;
			$result['html'] = $html;
			$result['payment_method'] = $payment_method;
			return $result;
			break;
		case ('poffline'):
		//send Pay 0fline notification email
			$member = oseRegistry::call('member');
			$email = $member->getInstance('email');
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			$emailTempDetail = $email->getDoc($emailConfig->pay_offline_email, 'obj');
			if (!empty($emailTempDetail)) {
				$juser = new JUser($orderInfo->user_id);
				$variables = $email->getEmailVariablesReceipt($order_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $juser->get('email'));
				if ($emailConfig->sendPayOffline2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result['payment_method'] = 'poffline';
			$user = JFactory::getUser();
			$params = JComponentHelper::getParams('com_users');
			$useractivation = $params->get('useractivation');
			if (($useractivation == 2 || $useractivation == 1) && $user->guest) {
				$result['title'] = JText::_('SUCCESSFUL_ACTIVATION');
				$result['content'] = JText::_('MEMBERSHIP_PAYOFFLINE_CONTINUE');
			}
			if (!empty($config->poffline_art_id)) {
				$link = JURI::root() . "index.php?option=com_content&view=article&id={$config->poffline_art_id}";
			} else {
				$link = JURI::root() . "index.php";
			}
			$result['success'] = true;
			$result['returnUrl'] = $link;
			$result['payment_method'] = $payment_method;
			return $result;
			break;
		case ('cp'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->CPoneOffPostForm($orderInfo, $creditInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('clickbank'):
			$html = $payment->getInstance('Order')->getClickBankForm($orderInfo);
			$result = array();
			$result['success'] = true;
			$result['html'] = $html;
			$result['payment_method'] = $payment_method;
			return $result;
			break;
		case ('ccavenue'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'CCAvenue Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->CCAvenueOneOffPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('usaepay'):
			if ($orderInfo->payment_mode == 'a') {
				$update = array();
				$updated['success'] = false;
				$updated['title'] = 'Error';
				$updated['content'] = 'It does not support subscription.';
				$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$updated = $payment->getInstance('Order')->processUSAePayForm($orderInfo, $creditInfo);
				$config = oseMscConfig::getConfig('thirdparty', 'obj');
				$account = oseObject::getValue($config, 'gag_account');
				$standard_type = oseObject::getValue($config, 'gag_domain_mode');
				$domain = oseObject::getValue($config, 'gag_domain');
				$htmlTrack = oseMscPublic::htmlTrack($account, $standard_type, $domain, $order_id);
				$code = oseMscPublic::ajaxOrderTrack($account, $order_id);
				if (empty($code)) {
					$code = false;
				} else {
					$code = implode("", $code);
				}
				if (!empty($code) && !empty($htmlTrack)) {
					$updated['code'] = $code;
					$updated['htmlTrack'] = $htmlTrack;
				}
			}
			return $updated;
			break;
		case ('icepay'):
			if ($orderInfo->payment_mode == 'a') {
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->ICEPAYOffPostForm($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('oospay'):
			if ($orderInfo->payment_mode == 'a') {
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->OOSPayOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('ebs'):
			if ($orderInfo->payment_mode == 'a') {
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->EBSOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('liqpay'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->LiqPayOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('virtualmerchant'):
			if ($orderInfo->payment_mode == 'a') {
				$update = array();
				$updated['success'] = false;
				$updated['title'] = 'Error';
				$updated['content'] = 'It does not support subscription.';
				$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$updated = $payment->getInstance('Order')->VirtualMerchantOneOffPay($orderInfo, $creditInfo);
			}
			return $updated;
			break;
		case ('realex_remote'):
		case ('realex_redirect'):
			if ($orderInfo->payment_mode == 'a') {
				$update = array();
				$updated['success'] = false;
				$updated['title'] = 'Error';
				$updated['content'] = 'It does not support subscription.';
				$updated['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$updated = $payment->getInstance('Order')->RealexOneOffPay($orderInfo, $creditInfo);
				$config = oseMscConfig::getConfig('thirdparty', 'obj');
				$account = oseObject::getValue($config, 'gag_account');
				$standard_type = oseObject::getValue($config, 'gag_domain_mode');
				$domain = oseObject::getValue($config, 'gag_domain');
				$htmlTrack = oseMscPublic::htmlTrack($account, $standard_type, $domain, $order_id);
				$code = oseMscPublic::ajaxOrderTrack($account, $order_id);
				if (empty($code)) {
					$code = false;
				} else {
					$code = implode("", $code);
				}
				if (!empty($code) && !empty($htmlTrack)) {
					$updated['code'] = $code;
					$updated['htmlTrack'] = $htmlTrack;
				}
			}
			return $updated;
			break;
		case ('sisow'):
			$result = array();
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'It does not support subscription.';
				$result['payment_method'] = $payment_method;
			} else {
				$result = $payment->getInstance('Order')->SisowPostForm($orderInfo);
			}
			return $result;
			break;
		case ('pagseguro'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'It does not support subscription.';
				$result['payment_method'] = $payment_method;
			} else {
				$result = $payment->getInstance('Order')->PagSeguroOneOffPay($orderInfo);
			}
			return $result;
			break;
		case ('paygate'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->PayGateOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('quickpay'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->QuickpayOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('sagepay'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->sagepayOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		case ('alipay'):
			if ($orderInfo->payment_mode == 'a') {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['payment_method'] = 'It does not support subscription.';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			} else {
				$html = $payment->getInstance('Order')->AlipayOneOffPay($orderInfo);
				$result = array();
				$result['success'] = true;
				$result['html'] = $html;
				$result['payment_mode'] = $orderInfo->payment_mode;
				$result['payment_method'] = $payment_method;
			}
			return $result;
			break;
		default:
			$result = array();
			$orderParams = oseJSON::decode($orderInfo->params);
			if ($orderParams->total == "0.00" && $orderParams->next_total == "0.00") {
				$result['success'] = true;
				$result['title'] = JText::_('SUCCESSFUL_ACTIVATION');
				$result['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
				$result['payment_method'] = 'none';
			} else {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('No Payment Method');
				$result['payment_method'] = 'none';
				$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
			}
			return $result;
			break;
		}
		return $result;
	}
	public static function getConfig($type = null, $xtype = 'array') {
		return oseRegistry::call('msc')->getConfig($type, $xtype);
	}
	public static function juserRegister($juser) {
		$result = array();
		$oseMscconfig = oseRegistry::call('msc')->getConfig('', 'obj');
		$config = JFactory::getConfig();
		$params = JComponentHelper::getParams('com_users');
		$newUserType = self::getNewUserType($params->get('new_usertype'));
		$juser['gid'] = $newUserType;
		$data = (array) self::getJuserData($juser);
		// Initialise the table with JUser.
		$user = new JUser;
		foreach ($juser as $k => $v) {
			$data[$k] = $v;
		}
		// Prepare the data for the user object.
		$useractivation = $params->get('useractivation');
		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2)) {
			jimport('joomla.user.helper');
			$data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}
		// Bind the data.
		if (!$user->bind($data)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError());
		}
		// Load the users plugin group.
		JPluginHelper::importPlugin('user');
		if (!$user->save()) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['reload'] = ($oseMscconfig->error_registration == 'refresh') ? true : false;
			;
			$result['content'] = JText::_($user->getError());
		} else {
			// Mark the user_id in order to user in payment form
			if (($useractivation == 1) || ($useractivation == 2)) {
				$session = JFactory::getSession();
				$oseUser = array();
				$oseUser['user_id'] = $user->id;
				$oseUser['block'] = true;
				$oseUser['activation'] = true;
				$session->set('ose_user', $oseUser);
			}
			$result['success'] = true;
			$result['user'] = $user;
			$result['title'] = 'Done';
			$result['content'] = 'Juser saved successfully';
			// Compile the notification mail values.
			$data = $user->getProperties();
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$data['siteurl'] = JUri::base();
			if (JOOMLA16 == true) {
				// Handle account activation/confirmation emails.
				if ($useractivation == 2) {
					// Set the link to confirm the user email.
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
					$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);
					$emailSubject = JText::sprintf('COM_USERS_OSEMSC_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);
					$emailBody = JText::sprintf('COM_USERS_OSEMSC_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY', $data['name'], $data['sitename'],
							$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'], $data['siteurl'], $data['username'],
							$data['password_clear']);
				} else if ($useractivation == 1) {
					// Set the link to activate the user account.
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
					$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);
					$emailSubject = JText::sprintf('COM_USERS_OSEMSC_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);
					$emailBody = JText::sprintf('COM_USERS_OSEMSC_EMAIL_REGISTERED_WITH_ACTIVATION_BODY', $data['name'], $data['sitename'],
							$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'], $data['siteurl'], $data['username'],
							$data['password_clear']);
				} else {
					$emailSubject = "";
					$emailBody = "";
				}
				// Send the registration email.
				if (!empty($emailSubject) && !empty($emailBody)) {
					if (JOOMLA30 == true) {
						$mailer = new JMail();
						$return = $mailer->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
					} else {
						$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
					}
				} else {
					$return = true;
				}
				// Check for an error.
				if ($return !== true) {
					$this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));
					// Send a system message to administrators receiving system mails
					$db = JFactory::getDBO();
					$q = "SELECT id
						FROM #__users
						WHERE block = 0
						AND sendEmail = 1";
					$db->setQuery($q);
					$sendEmail = $db->loadResultArray();
					if (count($sendEmail) > 0) {
						$jdate = new JDate();
						// Build the query to add the messages
						$q = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `date_time`, `subject`, `message`)
							VALUES ";
						$messages = array();
						foreach ($sendEmail as $userid) {
							$messages[] = "(" . $userid . ", " . $userid . ", '" . $jdate->toMySQL() . "', '" . JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT') . "', '"
									. JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username']) . "')";
						}
						$q .= implode(',', $messages);
						$db->setQuery($q);
						$db->query();
					}
					//return false;
				}
				if ($useractivation == 1) {
					$result['user_active'] = "useractivate";
				} else if ($useractivation == 2) {
					$result['user_active'] = "adminactivate";
				} else {
					$result['user_active'] = null;
				}
			} else {
				$mainframe = JFactory::getApplication('SITE');
				if ($useractivation == 1) {
					$password = $data['password_clear'];
					$db = JFactory::getDBO();
					$name = $user->get('name');
					$email = $user->get('email');
					$username = $user->get('username');
					$usersConfig = &JComponentHelper::getParams('com_users');
					$sitename = $mainframe->getCfg('sitename');
					$useractivation = $usersConfig->get('useractivation');
					$mailfrom = $mainframe->getCfg('mailfrom');
					$fromname = $mainframe->getCfg('fromname');
					$siteURL = JURI::base();
					$subject = sprintf(JText::_('ACCOUNT_DETAILS_FOR'), $name, $sitename);
					$subject = html_entity_decode($subject, ENT_QUOTES);
					$message = sprintf(JText::_('SEND_MSG_ACTIVATE'), $name, $sitename, $siteURL . "index.php?option=com_user&task=activate&activation=" . $user->get('activation'),
							$siteURL, $username, $password);
					$message = html_entity_decode($message, ENT_QUOTES);
					//get all super administrator
					$query = 'SELECT name, email, sendEmail' . ' FROM #__users' . ' WHERE LOWER( usertype ) = "super administrator"';
					$db->setQuery($query);
					$rows = $db->loadObjectList();
					// Send email to user
					if (!$mailfrom || !$fromname) {
						$fromname = $rows[0]->name;
						$mailfrom = $rows[0]->email;
					}
					JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);
					// Send notification to all administrators
					$subject2 = sprintf(JText::_('ACCOUNT_DETAILS_FOR'), $name, $sitename);
					$subject2 = html_entity_decode($subject2, ENT_QUOTES);
					// get superadministrators id
					foreach ($rows as $row) {
						if ($row->sendEmail) {
							$message2 = sprintf(JText::_('SEND_MSG_ADMIN'), $row->name, $sitename, $name, $email, $username);
							$message2 = html_entity_decode($message2, ENT_QUOTES);
							JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
						}
					}
				} else {
					$name = $user->get('name');
					$email = $user->get('email');
					$username = $user->get('username');
					$usersConfig = &JComponentHelper::getParams('com_users');
					$sitename = $mainframe->getCfg('sitename');
					$useractivation = $usersConfig->get('useractivation');
					$mailfrom = $mainframe->getCfg('mailfrom');
					$fromname = $mainframe->getCfg('fromname');
					$siteURL = JURI::base();
					$message = sprintf(JText::_('SEND_MSG'), $name, $sitename, $siteURL);
				}
			}
		}
		return $result;
	}
	public static function getNewUserType($newusertype) {
		if (JOOMLA16 == true) {
			return $newusertype;
		} else {
			$authorize = JFactory::getACL();
			return $authorize->get_group_id('', $newusertype, 'ARO');
		}
	}
	public static function getJuserData($temp) {
		$data = null;
		if ($data === null) {
			$data = new stdClass();
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_users');
			// Override the base user data with any data in the session.
			// Get the groups the user should be added to after registration.
			$data->groups = isset($data->groups) ? array_unique($data->groups) : array();
			// Get the default new user group, Registered if not specified.
			$system = $params->get('new_usertype', 2);
			$data->groups[] = $system;
			// Unset the passwords.
			unset($data->password1);
			unset($data->password2);
			// Get the dispatcher and load the users plugins.
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('user');
			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $data));
			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$data = false;
			}
		}
		return $data;
	}
	public static function getLangList($div) {
		$List = self::getLangArray();
		$options = array();
		foreach ($List as $key => $arr) {
			if ($arr['allow']) {
				$options[] = JHTML::_('select.option', $arr['code'], $arr['title']);
			}
		}
		$lang = self::getLang();
		$selected = JFactory::getConfig()->get('language');
		$event = array();
		$event[] = ' onChange="javascript:oseMsc.translate(\'' . $div
				. '\',this.value,Ext.get(\'ose_language\').getAttribute(\'oldvalue\'));Ext.get(\'ose_language\').setAttribute(\'oldvalue\',this.value)"';
		$event[] = ' onload="javascript:alert(\'dd\');oseMsc.translate(\'' . $div . '\',this.value,Ext.get(\'ose_language\').getAttribute(\'oldvalue\'));"';
		$event = implode(' ', $event);
		$combo = JHTML::_('select.genericlist', $options, 'ose_language', ' oldvalue="en-GB" ' . $event . ' class="ose_combo"  size="1" style="width:200px"', 'value', 'text', $lang);
		return $combo;
	}
	public static function getLangArray() {
		$lang = array();
		$lang['en-GB'] = array('code' => 'en-GB', 'title' => JText::_('English'), 'allow' => 1);
		$lang['en'] = array('code' => 'en-GB', 'title' => JText::_('English'), 'allow' => 0);
		$lang['cn'] = array('code' => 'cn', 'title' => JText::_('Chinese'), 'allow' => 1);
		$lang['fr'] = array('code' => 'fr', 'title' => JText::_('France'), 'allow' => 1);
		return $lang;
	}
	public static function setLang($code) {
		return oseLanguage::setCode($code);
	}
	public static function getLang() {
		return oseLanguage::getCode();
	}
	public static function instanceLang($code) {
		return new oseLanguage($code);
	}
	public static function getIP() {
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else {
				if (!empty($_SERVER["REMOTE_ADDR"])) {
					$cip = $_SERVER["REMOTE_ADDR"];
				} else {
					$cip = '';
				}
			}
		}
		preg_match("/[\d\.]{7,15}/", $cip, $cips);
		$cip = isset($cips[0]) ? $cips[0] : 'unknown';
		unset($cips);
		return $cip;
	}
	static public function getTax($country, $state) {
		return oseRegistry::call('payment')->getInstance('Tax')->getTax($country, $state);
	}
	static public function getList() {
		$where = array();
		$type = JRequest::getVar('type', null);
		if ($type != 'renew') {
			$where[] = "`published` = 1";
		}
		$where[] = "`leaf` =1";
		$where = oseDB::implodeWhere($where);
		$db = oseDB::instance();
		$query = " SELECT id,title,alias,description,ordering,image,params" . " FROM `#__osemsc_acl`" . $where . " ORDER BY lft ASC";
		$db->setQuery($query);
		$objs = oseDB::loadList();
		return $objs;
	}
	static public function getCurrency() {
		$List = oseRegistry::call('msc')->getCurrencyList();
		$options = array();
		foreach ($List as $key => $value) {
			$options[] = array('value' => $value['currency']);
		}
		return $options;
	}
	static public function generatePriceOption($node, $paymentInfos, $osePaymentCurrency) {
		$option = array();
		$i = 0;
		$oseMscConfig = oseRegistry::call('msc')->getConfig(null, 'obj');
		$show_discounted_price = empty($oseMscConfig->show_discounted_price) ? false : true;
		foreach ($paymentInfos as $paymentInfo) {
			$node = oseRegistry::call('payment')->getInstance('View')->getMscInfo(oseObject::getValue($node, 'id'), $osePaymentCurrency, oseObject::getValue($paymentInfo, 'id'));
			$msc_option = oseObject::getValue($paymentInfo, 'id');
			if ($show_discounted_price) {
				$db = JFactory::getDBO();
				$user = JFactory::getUser();
				$member = oseRegistry::call('member');
				$member->instance($user->id);
				$mscs = $member->getAllOwnedMsc(true, 1, 'obj');
				if (!empty($mscs)) {
					foreach ($mscs as $obj) {
						$Mem_mscs[] = $obj->msc_id;
					}
				} else {
					$Mem_mscs = array();
				}
				$msc_id = oseObject::getValue($node, 'id');
				if (in_array($msc_id, $Mem_mscs)) {
					$standard_renewal_raw_price = oseObject::getValue($node, 'standard_renewal_raw_price');
					if (!empty($standard_renewal_raw_price)) {
						$node['standard_price'] = $osePaymentCurrency . " " . $standard_renewal_raw_price;
						$node['trial_price'] = '';
					}
				}
			}
			$option[$i] = array('id' => oseObject::getValue($paymentInfo, 'id'), 'msc_id' => oseObject::getValue($node, 'id'),
					'title' => ucfirst(oseObject::getValue($node, 'standard_recurrence') . " " . JText::_('PAID_MEMBERSHIP')),
					'isFree' => oseObject::getValue($paymentInfo, 'isFree', 0), 'has_trial' => oseObject::getValue($paymentInfo, 'has_trial'),
					'trial_price' => oseObject::getValue($node, 'trial_price'), 'standard_price' => oseObject::getValue($node, 'standard_price'),
					'trial_recurrence' => oseObject::getValue($node, 'trial_recurrence'), 'standard_recurrence' => oseObject::getValue($node, 'standard_recurrence'));
			/* Special */
			$asianFormat = false;
			if ($asianFormat == true) {
				setlocale(LC_MONETARY, 'ko_KR');
				if ($option[$i]['has_trial'] == true) {
					$tmpPrice = explode(" ", $option[$i]['trial_price']);
					$option[$i]['trial_price'] = money_format('%!i', $tmpPrice[1]) . " " . $tmpPrice[0];
				}
				$tmpPrice = explode(" ", $option[$i]['standard_price']);
				$option[$i]['standard_price'] = money_format('%!i', $tmpPrice[1]) . " " . $tmpPrice[0];
			}
			if (!oseObject::getValue($paymentInfo, 'optionname', false)) {
				if (($option[$i]['standard_recurrence'] == ' ' && $option[$i]['trial_recurrence'] == ' ' && oseObject::getValue($paymentInfo, 'recurrence_mode') != 'fixed')
						|| oseObject::getValue($paymentInfo, 'eternal', false)) {
					if (oseObject::getValue($paymentInfo, 'isFree') == true) {
						$option[$i]['title'] = JText::_('LIFETIME_FREE_MEMBERSHIP');
						$option[$i]['standard_recurrence'] = JText::_('LIFETIME');
					} else {
						$option[$i]['title'] = JText::_('LIFETIME_MEMBERSHIP');
						$option[$i]['standard_recurrence'] = JText::_('LIFETIME');
					}
				} else {
					if (oseObject::getValue($paymentInfo, 'isFree') == true) {
						$option[$i]['title'] = ucfirst(oseObject::getValue($node, 'standard_recurrence') . " " . JText::_('FREE_MEMBERSHIP'));
					} else {
						//$option[$i]['title'] = ucfirst(oseObject::getValue($node,'standard_recurrence').JText::_(' Free Membership'));
					}
					if (oseObject::getValue($node, 'standard_raw_price') == '0.00' && oseObject::getValue($node, 'trial_raw_price') == '0.00') {
						//$option[$i]['title'] = ucfirst(oseObject::getValue($node,'standard_recurrence').JText::_(' Free Membership'));
					}
				}
			} else {
				$option[$i]['title'] = oseObject::getValue($paymentInfo, 'optionname');
				if (($option[$i]['standard_recurrence'] == ' ' && $option[$i]['trial_recurrence'] == ' ' && oseObject::getValue($paymentInfo, 'recurrence_mode') != 'fixed')
						|| oseObject::getValue($paymentInfo, 'eternal', false)) {
					$option[$i]['standard_recurrence'] = JText::_('LIFETIME');
				}
			}
			/* Special Ends*/
			$i++;
		}
		return $option;
	}
	public static function isUserAdmin($user) {
		if (JOOMLA16) {
			if (isset($user->groups['Super Users']) || isset($user->groups['Administrator'])) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($user->get('gid') == '24' || $user->get('gid') == '25') {
				return true;
			} else {
				return false;
			}
		}
	}
	public function filter($objs, $addons, $exact = true) {
		foreach ($objs as $key => $obj) {
			if ($exact) {
				if (in_array($obj->name, $addons)) {
					unset($objs[$key]);
				}
			} else {
				foreach ($addons as $addon) {
					if (strpos($obj->name, $addon) === false) {
						continue;
					} else {
						unset($objs[$key]);
					}
				}
			}
		}
		$objs = array_values($objs);
		return $objs;
	}
	static function getRegisterForm() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			return 'default';
		} else {
			switch ($register_form) {
			case ('onestep'):
				return 'onestep';
				break;
			}
		}
	}
	function savePaymentMode() {
		$cart = oseMscPublic::getCart();
		$payment_mode = oseMscPublic::getPaymentMode();
		$config = oseMscPublic::getConfig('global', 'obj');
		if ($config->payment_mode != 'b') {
			$payment_mode = $config->payment_mode;
		}
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		// force to m
		if (empty($register_form) || $register_form == 'default') {
			$payment_mode = 'm';
		} else {
			$items = $cart->get('items');
			$msc = oseRegistry::call('msc');
			foreach ($items as $item) {
				if (oseObject::getValue($item, 'recurrence_mode', 'period') == 'fixed') {
					$payment_mode = 'm';
				}
				if (oseObject::getValue($item, 'entry_type', 'msc') == 'license') {
					$payment_mode = 'm';
				} else {
					$extItem = $msc->getExtInfoItem(self::getEntryMscID($item), 'paymentAdv', 'obj');
					$advItems = oseJson::decode(oseObject::getvalue($extItem, 'params', '{}'), true);
					$advItems = empty($advItems) ? array() : $advItems;
					$advOption = isset($advItems[oseObject::getvalue($item, 'msc_option')]) ? $advItems[oseObject::getvalue($item, 'msc_option')] : array();
					if (oseObject::getValue($advOption, 'payment_mode', 'b') != 'b') {
						$payment_mode = $advOption['payment_mode'];
					}
				}
				$payment_method = oseMscPublic::getPaymentMethod();
				if ($payment_method == 'paypal') {
					if (oseObject::getValue($item, 'p3', 0) >= 2 && strtolower(oseObject::getValue($item, 't3', 'day')) == 'year') {
						$payment_mode = 'm';
					}
					if (oseObject::getValue($item, 'p3', 0) >= 24 && strtolower(oseObject::getValue($item, 't3', 'day')) == 'month') {
						$payment_mode = 'm';
					}
				}
			}
		}
		if ($cart->get('total') <= 0 && $cart->get('next_total') <= 0) {
			$payment_mode = 'm';
		}
		$cart->updateParams('payment_mode', $payment_mode);
		$cart->update();
		return $payment_mode;
	}
	public static function getPaymentMethod($key = 'payment_payment_method') {
		return JRequest::getString($key, 'none');
	}
	function generateOrder($member_id, $payment_method, $orderPaymentInfo) {
		return oseRegistry::call('msc')->runAddonAction('register.payment.save', array('member_id' => $member_id, 'payment_method' => $payment_method), true, false);
		$result = array();
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		$paymentOrder = oseRegistry::call('payment')->getInstance('Order');
		$params = array();
		$items = $orderPaymentInfo['items'];
		unset($orderPaymentInfo['items']);
		$order_number = $paymentOrder->generateOrderNumber($member_id);
		$orderPaymentInfo['order_number'] = $order_number;
		$orderPaymentInfo['entry_type'] = 'msc_list';
		$orderPaymentInfo['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
		$orderPaymentInfo['payment_serial_number'] = substr($orderPaymentInfo['order_number'], 0, 20);
		$orderPaymentInfo['payment_method'] = 'system';
		$orderPaymentInfo['payment_from'] = 'system_admin';
		$orderPaymentInfo['payment_mode'] = 'm';
		oseObject::setParams($orderPaymentInfo, array('time_stamp' => uniqid("{$member_id}_", true)));
		// Extra Order Params Updating Function
		$list = oseMscAddon::getAddonList('register_order', true, 1, 'obj');
		foreach ($list as $addon) {
			$action_name = 'register_order.' . $addon->name . '.add';
			//echo $action_name;
			$params = oseMscAddon::runAction($action_name, $orderPaymentInfo['params'], true, false);
		}
		// generate Order
		$updated = $paymentOrder->generateOrder('', $member_id, $orderPaymentInfo);
		if (!$updated) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		// generate orer item
		// in the backend, only manual payment
		$order_id = $result['order_id'] = $updated;
		$payment_mode = 'm';
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
				$itemParams['payment_mode'] = 'm';
			}
			$price = oseObject::getValue($item, 'a3');
			if ($payment_mode == 'a') {
				if (oseObject::getValue($item, 'has_trial')) {
					$price = oseObject::getValue($item, 'a1');
				}
			}
			$itemParams['entry_type'] = oseObject::getValue($item, 'entry_type');
			$itemParams['payment_price'] = 0;//oseObject::getValue($item,'first_raw_price');
			$itemParams['payment_currency'] = $orderPaymentInfo['payment_currency'];
			$itemParams['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
			$price_params = $paymentOrder->generateOrderParams($msc_id, $price, $payment_mode, $msc_option);
			$price_params['start_date'] = oseObject::getValue($item, 'start_date', null);
			$price_params['expired_date'] = oseObject::getValue($item, 'expired_date', null);
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
		return $result;
	}
	static function getUser() {
		$params = JComponentHelper::getParams('com_users');
		$useractivation = $params->get('useractivation');
		$session = JFactory::getSession();
		$oseUser = $session->get('ose_user', array());
		$user = JFactory::getUser();
		if ($user->guest) {
			if ($useractivation == 1 || $useractivation == 2) {
				if (oseObject::getValue($oseUser, 'block', false) && oseObject::getValue($oseUser, 'activation', false)) {
					$user = JFactory::getUser(oseObject::getValue($oseUser, 'user_id', null));
				}
			} else {
				$memConfig = oseMscConfig::getConfig('register', 'obj');
				if (!$memConfig->auto_login) {
					$user = JFactory::getUser(oseObject::getValue($oseUser, 'user_id', null));
				}
			}
		}
		return $user;
	}
	function pointedRedirection($sefroutemethod, $menu) {
		if ((isset($menu->type)) && $menu->type == 'url') {
			$return = $menu->link;
		} elseif ((isset($menu->type)) && $menu->type == 'alias') {
			$menuParams = oseJson::decode($menu->params);
			$aMenuId = $menuParams->aliasoptions;
			//$aMenu   = JSite::getMenu(true)->getItem($aMenuId);
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__menu` WHERE `id` = " . (int) $aMenuId;
			$db->setQuery($query);
			$aMenu = $db->loadObject();
			return self::pointedRedirection($sefroutemethod, $aMenu);
		} else {
			switch ($sefroutemethod) {
			default:
			case 0:
				$redURL = $menu->link . "&Itemid=" . $menu->id;
				break;
			case 1:
				$return = $redURL = ($menu->link == 'index.php?option=com_content&view=featured') ? JRoute::_(JURI::root() . 'index.php')
						: JRoute::_($menu->link . "&Itemid=" . $menu->id);
				break;
			case 2:
				$jConfig = JFactory::getConfig();
				if (JOOMLA16 || JOOMLA17) {
					if ($jConfig->get('sef_rewrite')) {
						$redURL = JRoute::_($menu->path);
					} else {
						$redURL = "index.php/" . JRoute::_($menu->path);
					}
				} else {
					static $menuPath;
					$parent_id = oseObject::getValue($menu, 'parent');
					if (empty($menuPath)) {
						$menuPath = array();
						array_unshift($menuPath, $menu->alias);
					}
					if ($parent_id != 0) {
						$aMenu = JSite::getMenu(true)->getItem($parent_id);
						array_unshift($menuPath, $aMenu->alias);
						$redURL = self::pointedRedirection($sefroutemethod, $aMenu);
						return $redURL;
					} else {
						$menuPath = implode('/', $menuPath);
						if ($jConfig->get('sef_rewrite')) {
							$redURL = JRoute::_($menuPath);
						} else {
							$redURL = "index.php/" . JRoute::_($menuPath);
						}
					}
				}
				break;
			}
		}
		if (strpos($redURL, 'http') === false && $sefroutemethod != 1) {
			$return = JURI::root() . $redURL;
		}
		return $return;
	}
	public static function htmlTrack($account, $standard_type, $domain, $order_id = null) {
		$code = array();
		$code[] = "var _gaq = _gaq || [];";
		$code[] = "_gaq.push(['_setAccount', '{$account}']);";
		switch ($standard_type) {
		case (2):
			$code[] = "_gaq.push(['_setDomainName', '{$domain}']);";
			break;
		case (3):
			$code[] = "_gaq.push(['_setDomainName', 'none']);";
			$code[] = " _gaq.push(['_setAllowLinker', true]);";
			break;
		case (1):
		default:
			break;
		}
		$code[] = "_gaq.push(['_trackPageview']);";
		// order
		if (!empty($order_id)) {
			$db = JFactory::getDBO();
			$where = array('`order_id`=' . $db->Quote($order_id));
			//$code = array();
			if (!JFile::exists(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php')) {
				return false;
			} else {
				require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php');
			}
			$db = oseDB::instance();
			$pOrder = oseRegistry::call('payment')->getInstance('Order');
			$orderInfo = $pOrder->getOrder($where, 'obj');
			$orderInfoParams = oseJson::decode($orderInfo->params);
			// transaction
			$code[] = "_gaq.push(['_addTrans',";
			$code[] = "'{$orderInfo->order_id}',";
			$code[] = "'{$orderInfo->order_number}',";
			$code[] = "'{$orderInfo->payment_price}',";
			$code[] = "'{$orderInfoParams->gross_tax}',";
			$code[] = '"0",';//"'0',";
			$code[] = "'',";
			$code[] = "'', ";
			$code[] = "'' ";
			$code[] = "]);";
			// product
			$msc = oseRegistry::call('msc');
			$orderItems = $pOrder->getOrderItems($orderInfo->order_id, 'obj');
			foreach ($orderItems as $orderItem) {
				$curOrderItemParams = oseJson::decode($orderItem->params);
				$msc_id = $orderItem->entry_id;
				$node = $msc->getInfo($msc_id, 'obj');
				$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
				foreach ($paymentInfos as $key => $paymentInfo) {
					if ($key != $curOrderItemParams->msc_option) {
						unset($paymentInfos[$key]);
					}
				}
				$cart = oseMscPublic::getCart();
				$osePaymentCurrency = $cart->get('currency');
				$items = $cart->get('items');
				$options = oseMscPublic::generatePriceOption($node, $paymentInfos, $osePaymentCurrency);
				$option = $options[0];
				$code[] = "_gaq.push(['_addItem',";
				$code[] = "'{$orderItem->order_id}',";
				$code[] = "'{$orderItem->entry_type}_{$orderItem->entry_id}',";
				$code[] = "'{$node->title}-{$option['title']}',";
				$code[] = "'{$orderItem->entry_type}',";
				$code[] = "'{$orderItem->payment_price}',";
				$code[] = "'1'";
				$code[] = "]);";
			}
			$code[] = "_gaq.push(['_trackTrans']);";
		}
		$code[] = "(function() {";
		$code[] = "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
		$code[] = "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';";
		$code[] = "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
		$code[] = " })();";
		$code = implode("\r\n", $code);
		return $code;
	}
	function ajaxOrderTrack($account, $order_id) {
		$db = JFactory::getDBO();
		$where = array('`order_id`=' . $db->Quote($order_id));
		$code = array();
		if (!JFile::exists(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php')) {
			return false;
		} else {
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php');
		}
		$db = oseDB::instance();
		$pOrder = oseRegistry::call('payment')->getInstance('Order');
		$orderInfo = $pOrder->getOrder($where, 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// transaction
		$code[] = 'var pageTracker = _gat._getTracker("' . $account . '");';
		$code[] = "pageTracker._addTrans(";
		$code[] = "'{$orderInfo->order_id}',";
		$code[] = "'{$orderInfo->order_number}',";
		$code[] = "'{$orderInfo->payment_price}',";
		$code[] = "'{$orderInfoParams->gross_tax}',";
		$code[] = "'0',";
		$code[] = "'',";
		$code[] = "'', ";
		$code[] = "'' ";
		$code[] = ");";
		// product
		$msc = oseRegistry::call('msc');
		$orderItems = $pOrder->getOrderItems($orderInfo->order_id, 'obj');
		foreach ($orderItems as $orderItem) {
			$curOrderItemParams = oseJson::decode($orderItem->params);
			$msc_id = $orderItem->entry_id;
			$node = $msc->getInfo($msc_id, 'obj');
			$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
			foreach ($paymentInfos as $key => $paymentInfo) {
				if ($key != $curOrderItemParams->msc_option) {
					unset($paymentInfos[$key]);
				}
			}
			$cart = oseMscPublic::getCart();
			$osePaymentCurrency = $cart->get('currency');
			$items = $cart->get('items');
			$options = oseMscPublic::generatePriceOption($node, $paymentInfos, $osePaymentCurrency);
			$option = $options[0];
			$code[] = "pageTracker._addItem(";
			$code[] = "'{$orderItem->order_id}',";
			$code[] = "'{$orderItem->entry_type}_{$orderItem->entry_id}',";
			$code[] = "'{$node->title}-{$option['title']}',";
			$code[] = "'{$orderItem->entry_type}',";
			$code[] = "'{$orderItem->payment_price}',";
			$code[] = "'1'";
			$code[] = ");";
		}
		$code[] = "pageTracker._trackTrans();";
		return $code;
	}
	function text() {
		$language = JFactory::getLanguage();
		// load commerce
		$language->load('com_osemsc', JPATH_SITE);
		$lStrings = $language->getProperties();
		// filter
		foreach ($lStrings['strings'] as $k => $v) {
			oseText::$strings[$k] = $v;
		}
		return oseText::$strings;
	}
}
?>