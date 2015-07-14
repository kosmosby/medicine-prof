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
defined('_JEXEC') or die("Direct Access Not Allowed");
class osemscModelRegister extends oseMscModel {
	function __construct() {
		parent::__construct();
	}
	function saveOS($post) {
		$config = oseRegistry::call('msc')->getConfig('', 'obj');
		$result = array();
		$result['success'] = true;
		$result['reload'] = ($config->error_registration == 'refresh') ? true : false;
		$result['script'] = false;
		$result['title'] = JText::_('SUCCESSFUL_ACTIVATION');
		$result['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
		$juser = oseMscAddon::getPost('juser_', $post);
		$password1 = JRequest::getString('juser_password1', '', 'post', JREQUEST_ALLOWRAW);
		$password2 = JRequest::getString('juser_password2', '', 'post', JREQUEST_ALLOWRAW);
		if (oseObject::getValue($juser, 'firstname', false) && oseObject::getValue($juser, 'lastname', false)) {
			$juser['name'] = oseObject::getValue($juser, 'firstname', '') . ' ' . oseObject::getValue($juser, 'lastname', '');
		} else {
			$juser['name'] = oseObject::getValue($juser, 'username', false);
		}
		$juser['password'] = $juser['password1'] = $password1;
		$juser['password2'] = $password2;
		$juser['email1'] = $juser['email'];
		$juser['email2'] = $juser['email'];
		// check unique username
		$list = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
		foreach ($list as $addon) {
			$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
			$updated = oseMscAddon::runAction($action_name, array('inner' => true));
			if (!$updated['success']) {
				return $updated;
			}
		}
		if (!$updated['success']) {
			return $updated;
		}
		// check empty password
		if (empty($juser['password'])) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('MUST_SUPPLY_PASSWORD');
			return $result;
		}
		$register = oseMscPublic::juserRegister($juser);
		/*
		 * After register successfully, some errors occur will triger reload action
		 * so that it will show login if auto login is enabled
		 */
		if (!oseObject::getValue($register, 'success')) {
			return $register;
		} else {
			$memConfig = oseMscConfig::getConfig('register', 'obj');
			$comUserparams = JComponentHelper::getParams('com_users');
			$useractivation = $comUserparams->get('useractivation');
			$list = array();
			$list1 = oseMscAddon::getAddonList('registerOS_header', false, 1, 'obj');
			$list2 = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
			$list3 = oseMscAddon::getAddonList('registerOS_footer', false, 1, 'obj');
			$list = array_merge($list1, $list2, $list3);
			$user = oseObject::getValue($register, 'user');
			$cart = oseMscPublic::getCart();
			$items = $cart->get('items');
			$item = $items[0];
			$params = array();
			$params['msc_id'] = oseMscPublic::getEntryMscId($item);
			$params['msc_option'] = oseObject::getValue($item, 'msc_option');
			$params['payment_mode'] = $post['payment_payment_mode'];
			$params['member_id'] = $user->id;
			$params['payment_method'] = $post['payment_method'];//oseMscPublic::getPaymentMethod();//$post['payment_payment_method'];
			foreach ($list as $addon) {
				$action_name = oseMscAddon::getActionName($addon, 'save', 'register');
				$updated = oseMscAddon::runAction($action_name, $params);
				if (!$updated['success']) {
					return $updated;
				}
			}
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			if (!empty($emailConfig->default_reg_email)) {
				$member = oseRegistry::call('member');
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($emailConfig->default_reg_email, 'obj');
				if (!empty($emailTempDetail)) {
					$variables = $email->getEmailVariablesRegistration($user->id);
					$variables['user'] = oseObject::setValue($variables['user'], 'password', $juser['password1']);
					$emailParams = $email->buildEmailParams($emailTempDetail->type);
					$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
					$email->sendEmail($emailDetail, $user->email);
					if ($emailConfig->sendReg2Admin) {
						$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
					}
				}
			}
			if ($memConfig->auto_login) {
				$isLogin = $this->login($juser['username'], $juser['password']);
				if (!$isLogin['success']) {
					$session = JFactory::getSession();
					$return = $session->get('oseReturnUrl', base64_encode('index.php?option=com_osemsc&view=login'));
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = $isLogin['content'];
					$result['returnUrl'] = $return;
					return $result;
				}
			}
			if ($memConfig->auto_login == false && $memConfig->auto_clearsession == true) {
				$session = JFactory::getSession();
				$session->clear('osecart');
				$session->clear('ose_user');
			}
			return $result;
		}
	}
	function login($username, $password) {
		$app = JFactory::getApplication();
		// Populate the data array:
		$data = array();
		$data['return'] = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		// Set the return URL if empty.
		if (empty($data['return'])) {
			$data['return'] = 'index.php?option=com_users&view=profile';
		}
		// Get the log in options.
		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = $data['return'];
		// Get the log in credentials.
		$credentials = array();
		$credentials['username'] = $username;
		$credentials['password'] = $password;
		// Perform the log in.
		$error = $app->login($credentials, $options);
		if (JOOMLA16 || JOOMLA17) {
			$plugin = JPluginHelper::getPlugin('user', 'oseuser');
			if (!empty($plugin)) {
				$pluginParams = oseJSON::decode($plugin->params);
				$loginRedirect = $pluginParams->loginRedirect;
				$redmenuid = $pluginParams->redmenuid;
				$sefroutemethod = $pluginParams->sefroutemethod;
			} else {
				$loginRedirect = false;
			}
		} else {
			$plugin = JPluginHelper::getPlugin('user', 'oseuser');
			if (!empty($plugin)) {
				$pluginParams = new JParameter($plugin->params);
				$loginRedirect = $pluginParams->get('loginRedirect');
				$redmenuid = $pluginParams->get('redmenuid');
				$sefroutemethod = $pluginParams->get('sefroutemethod');
			} else {
				$loginRedirect = false;
			}
		}
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = " SELECT a.menuid" . " FROM `#__menu` as m" . " LEFT JOIN `#__osemsc_acl` as a ON a.menuid = m.id" . " LEFT JOIN `#__osemsc_member` as b ON b.msc_id = a.id"
				. " WHERE b.member_id={$user->id} AND b.status = 1 ORDER BY a.menuid DESC LIMIT 1";
		$db->setQuery($query);
		$menu = $db->loadObject();
		if (!empty($menu)) {
			$redmenuid = $menu->menuid;
		}
		if ($loginRedirect && !empty($redmenuid)) {
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__menu` WHERE `id` = " . (int) $redmenuid;
			$db->setQuery($query);
			$menu = $db->loadObject();
			switch ($sefroutemethod) {
			default:
			case 0:
				$return = ($menu->link == 'index.php?Itemid=') ? 'index.php' : $menu->link . "&Itemid=" . $menu->id;
				break;
			case 1:
				$return = ($menu->link == 'index.php?Itemid=') ? JRoute::_(JURI::root() . 'index.php') : JRoute::_($menu->link . "&Itemid=" . $menu->id);
				break;
			case 2:
				$return = JRoute::_($menu->alias);
				break;
			}
		} else {
			$session = JFactory::getSession();
			$return = $session->get('oseReturnUrl', base64_encode(JURI::root() . 'index.php?option=com_osemsc&view=login'));
			$return = base64_decode($return);
		}
		$result = array();
		// Check if the log in succeeded.
		$user_id = intval(JUserHelper::getUserId($username));
		$user = JFactory::getUser($user_id);
		if ($user->get('block')) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('LOGIN_DENIED_YOUR_ACCOUNT_HAS_EITHER_BEEN_BLOCKED_OR_YOU_HAVE_NOT_ACTIVATED_IT_YET');
			$result['returnUrl'] = $return;
			return $result;
		}
		if ($error) {
			if (!JError::isError($error)) {
				$result['success'] = true;
				$result['returnUrl'] = $return;
				return $result;
			} else {
				$errors = JError::getErrors();
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_('PLEASE_MAKE_SURE_YOUR_ACCOUNT_AND_PASSWORD_IS_CORRECT');//implode("<br />",JError::getErrors());//'Make sure your account and password is correct';//Error ::getError();
				$result['returnUrl'] = $return;
				return $result;
			}
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('PLEASE_MAKE_SURE_YOUR_ACCOUNT_AND_PASSWORD_IS_CORRECT');//implode("<br />",JError::getErrors());//'Make sure your account and password is correct';//Error ::getError();
			$result['returnUrl'] = $return;
			return $result;
		}
	}
	////////////////////// Tidy ///////////////////////////
	function processPayment($order_id, $post) {
		$payment = oseRegistry::call('payment');
		$orderInfo = $payment->getOrder(array("order_id = {$order_id}"), 'obj');
		$config = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$msc_id = $orderInfo->entry_id;
		$member_id = $orderInfo->user_id;
		$payment_method = $orderInfo->payment_method;
		$orderInfo->payment_price = floatval($orderInfo->payment_price);
		if ($orderInfo->payment_price != '0.00' && $orderInfo->payment_price > 0) {
			if ($orderInfo->payment_mode == 'm') {
				$msc = oseCall('msc');
				$mscExt = $msc->getExtInfo($post['msc_id'], 'msc', 'obj');
				$msc_id = $post['msc_id'];
				if (oseObject::getValue($mscExt, 'renew_disable', false)) {
					$member = oseRegistry::call('member');
					$member->instance($member_id);
					$isRenewMember = $member->isMember($msc_id);
					if (!empty($isRenewMember)) {
						$return['success'] = false;
						$return['payment'] = $payment_method;
						$return['payment_method'] = $payment_method;
						$return['title'] = JText::_('RESTRICTED');
						$return['content'] = JText::_('MEMBERSHIP_RENEW_NOT_ALLOW');
						return $return;
					}
				}
			}
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post);
		} elseif ($orderInfo->payment_price <= 0 && $orderInfo->payment_mode == 'a') {
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post);
		} else {
			if ($payment_method != 'poffline') {
				$payment_method = 'none';
			}
			$db = JFactory::getDBO();
			$query = "SELECT entry_id FROM `#__osemsc_order_item` WHERE `order_id` = " . $order_id;
			$db->setQuery($query);
			$msc_id = $db->loadResult();
			$member = oseRegistry::call('member');
			$member->instance($member_id);
			$isFreeMember = $member->isMember($msc_id);
			$memConfig = oseMscConfig::getConfig('register', 'obj');
			if (!empty($isFreeMember) && $memConfig->allow_freerenewal == false) {
				$result['success'] = false;
				$result['title'] = JText::_('RESTRICTED');
				$result['content'] = JText::_('FREE_MEMBERSHIP_RENEW_NOT_ALLOW');
			} else {
				$result = $payment->getInstance('Order')->confirmOrder($order_id, array());
				$result['payment_method'] = $payment_method;
			}
			return $result;
		}
	}
	function processPaymentCart($order_id, $post) {
		$payment = oseRegistry::call('payment');
		$orderInfo = $payment->getOrder(array("order_id = {$order_id}"), 'obj');
		$config = oseRegistry::call('msc')->getConfig('payment', 'obj');
		$msc_id = $orderInfo->entry_id;
		$member_id = $orderInfo->user_id;
		$payment_method = $orderInfo->payment_method;
		$orderInfo->payment_price = floatval($orderInfo->payment_price);
		if ($orderInfo->payment_price != '0.00' && $orderInfo->payment_price > 0) {
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post);
		} elseif ($orderInfo->payment_price == '0.00' && $orderInfo->payment_mode == 'a') {
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post);
		} else {
			if ($payment_method != 'poffline') {
				$payment_method = 'none';
			}
			$result = $payment->getInstance('Order')->confirmOrder($order_id, array(), $msc_id, $member_id, $payment_method);
			$result['payment_method'] = $payment_method;
			return $result;
		}
	}
	function getMscList($msc_id = 0) {
		$db = oseDB::instance();
		$where = array();
		$where[] = "published = 1";
		if (!empty($msc_id)) {
			$where[] = "id = {$msc_id}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_acl`" . $where . " ORDER BY lft ASC";
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		$mscExtend = oseRegistry::call('msc')->getConfig('global', 'obj')->msc_extend;
		$items = array();
		$session = JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency', oseRegistry::call('msc')->getConfig('currency', 'obj')->primary_currency);
		foreach ($objs as $obj) {
			$paymentInfos = oseRegistry::call('msc')->getExtInfo($obj->id, 'payment', 'array');
			foreach ($paymentInfos as $key => $paymentInfo) {
				if (!empty($mscExtend)) {
					$ext = oseRegistry::call($mscExtend);
					$item = oseRegistry::call('payment')->getInstance('View')->getMscInfo($obj->id, $osePaymentCurrency, oseObject::getValue($paymentInfo, 'id'));
					$itemExtend = $ext->getMscExtendInfo($obj->id);
					$fItem = (object) array_merge((array) $item, (array) $itemExtend);
				} else {
					$item = oseRegistry::call('payment')->getInstance('View')->getMscInfo($obj->id, $osePaymentCurrency, oseObject::getValue($paymentInfo, 'id'));
					$fItem = $item;
				}
				$items[] = $fItem;
			}
		}
		return $items;
	}
	function getList() {
		return oseMscPublic::getList();
	}
	function getAllOptions() {
		$list = oseMscPublic::getList();
		$options = array();
		$msc = oseRegistry::call('msc');
		foreach ($list as $key => $entry) {
			$msc_id = oseObject::getValue($entry, 'id');
			$node = $msc->getInfo($msc_id, 'obj');
			$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
			$cart = oseMscPublic::getCart();
			$osePaymentCurrency = $cart->get('currency');
			$option = oseMscPublic::generatePriceOption($node, $paymentInfos, $osePaymentCurrency);
			$options = array_merge($options, $option);
		}
		return $options;
	}
	function sendReceipt($order_id) {
		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$orderInfo = oseRegistry::call('payment')->getOrder($where, 'obj');
		$member = oseRegistry::call('member');
		$email = $member->getInstance('email');
		$member->instance($orderInfo->user_id);
		$my = JFactory::getUser($orderInfo->user_id);
		$emailDetail = $member->getReceipt($orderInfo);
		$email->sendEmail($emailDetail, $my->email);
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		if ($emailConfig->sendReceipt2Admin) {
			$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
		}
	}
	function checkCartItems() {
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		return $cart->countCart();
	}
	function saveCart1($post) {
		$result = array();
		$result['success'] = true;
		$result['reload'] = false;
		$result['script'] = false;
		$result['title'] = JText::_('SUCCESSFUL_ACTIVATION');
		$result['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
		$juser = oseMscAddon::getPost('juser_', $post);
		$password1 = JRequest::getString('juser_password1', '', 'post', JREQUEST_ALLOWRAW);
		$password2 = JRequest::getString('juser_password2', '', 'post', JREQUEST_ALLOWRAW);
		$juser['name'] = $juser['firstname'] . ' ' . $juser['lastname'];
		$juser['password'] = $juser['password1'] = $password1;
		$juser['password2'] = $password2;
		$juser['email1'] = $juser['email'];
		$juser['email2'] = $juser['email'];
		// check unique username
		$list = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
		foreach ($list as $addon) {
			$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
			$updated = oseMscAddon::runAction($action_name, array('inner' => true));
			if (!$updated['success']) {
				return $updated;
			}
		}
		if (!$updated['success']) {
			return $updated;
		}
		// check empty password
		if (empty($juser['password'])) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('MUST_SUPPLY_PASSWORD');
			return $result;
		}
		$register = oseMscPublic::juserRegister($juser);
		if (!oseObject::getValue($register, 'success', false)) {
			return $register;
		} else {
			$params = array();
			$params['member_id'] = $user->id;
			$params['payment_method'] = $post['payment_payment_method'];
			$list1 = oseMscAddon::getAddonList('register_billing', false, 1, 'obj');
			$list2 = oseMscAddon::getAddonList('register_payment', false, 1, 'obj');
			$list = array_merge($list1, $list2);
			foreach ($list as $addon) {
				$action_name = 'register.' . $addon->name . '.save';
				$updated = oseRegistry::call('msc')->runAddonAction($action_name, $params);
				if (!$updated['success']) {
					return $updated;
				}
			}
			$action_name = 'register.payment.save';
			$updated = oseRegistry::call('msc')->runAddonAction($action_name, $params);
			if (!$updated['success']) {
				return $updated;
			}
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			if (!empty($emailConfig->default_reg_email)) {
				$member = oseRegistry::call('member');
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($emailConfig->default_reg_email, 'obj');
				$variables = $email->getEmailVariablesRegistration($user->id);
				// Test Code Start
				$memUser = oseRegistry::call('member')->getInstance('User');
				$memUser->instance($user->id);
				$variables['user'] = $memUser->getUserInfo();
				// Test Code End
				$variables['user'] = oseObject::setValue($variables['user'], 'password', $juser['password']);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $user->email);
				if ($emailConfig->sendReg2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$memConfig = oseMscConfig::getConfig('register', 'obj');
			$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
			if ($memConfig->auto_login) //|| (empty($register_form) || $register_form == 'default'))
			{
				$isLogin = $this->login($juser['username'], $juser['passwd']);
				if (!$isLogin['success']) {
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = $isLogin['error'];
					return $result;
				}
			}
			return $result;
		}
	}
	function saveCart($post) {
		$result = array();
		$result['success'] = true;
		$result['reload'] = false;
		$result['script'] = false;
		$result['title'] = JText::_('SUCCESSFUL_ACTIVATION');
		$result['content'] = JText::_('MEMBERSHIP_ACTIVATED_CONTINUE');
		$juser = oseMscAddon::getPost('juser_', $post);
		$password1 = JRequest::getString('juser_password1', '', 'post', JREQUEST_ALLOWRAW);
		$password2 = JRequest::getString('juser_password2', '', 'post', JREQUEST_ALLOWRAW);
		if (oseObject::getValue($juser, 'firstname', false) && oseObject::getValue($juser, 'lastname', false)) {
			$juser['name'] = oseObject::getValue($juser, 'firstname', '') . ' ' . oseObject::getValue($juser, 'lastname', '');
		} else {
			$juser['name'] = oseObject::getValue($juser, 'username', false);
		}
		$juser['password'] = $juser['password1'] = $password1;
		$juser['password2'] = $password2;
		$juser['email1'] = $juser['email'];
		$juser['email2'] = $juser['email'];
		// check unique username
		$list = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
		foreach ($list as $addon) {
			$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
			$updated = oseMscAddon::runAction($action_name, array('inner' => true));
			if (!$updated['success']) {
				return $updated;
			}
		}
		if (!$updated['success']) {
			return $updated;
		}
		// check empty password
		if (empty($juser['password'])) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('MUST_SUPPLY_PASSWORD');
			return $result;
		}
		$register = oseMscPublic::juserRegister($juser);
		if (!oseObject::getValue($register, 'success', false)) {
			return $register;
		} else {
			$memConfig = oseMscConfig::getConfig('register', 'obj');
			if ($memConfig->auto_login) {
				$result['reload'] = true;
			}
			$list1 = oseMscAddon::getAddonList('registerOS_header', false, 1, 'obj');
			$list2 = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
			$list3 = oseMscAddon::getAddonList('registerOS_footer', false, 1, 'obj');
			$list = array_merge($list1, $list2, $list3);
			$user = oseObject::getValue($register, 'user');
			$cart = oseMscPublic::getCart();
			$items = $cart->get('items');
			$item = $items[0];
			$params = array();
			$params['msc_id'] = oseMscPublic::getEntryMscId($item);
			$params['msc_option'] = oseObject::getValue($item, 'msc_option');
			$params['payment_mode'] = $post['payment_payment_mode'];
			$params['member_id'] = $user->id;
			$params['payment_method'] = $post['payment_method'];
			foreach ($list as $addon) {
				$action_name = oseMscAddon::getActionName($addon, 'save', 'register');
				$updated = oseMscAddon::runAction($action_name, $params);
				if (!$updated['success']) {
					return $updated;
				}
			}
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			if (!empty($emailConfig->default_reg_email)) {
				$member = oseRegistry::call('member');
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($emailConfig->default_reg_email, 'obj');
				if (!empty($emailTempDetail)) {
					$variables = $email->getEmailVariablesRegistration($user->id);
					$variables['user'] = oseObject::setValue($variables['user'], 'password', $juser['password1']);
					$emailParams = $email->buildEmailParams($emailTempDetail->type);
					$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
					$email->sendEmail($emailDetail, $user->email);
					if ($emailConfig->sendReg2Admin) {
						$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
					}
				}
			}
			if ($memConfig->auto_login) {
				$isLogin = $this->login($juser['username'], $juser['password']);
				if (!$isLogin['success']) {
					$session = JFactory::getSession();
					$return = $session->get('oseReturnUrl', base64_encode('index.php?option=com_osemsc&view=login'));
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = $isLogin['content'];
					$result['returnUrl'] = $return;
					return $result;
				}
			}
			return $result;
		}
	}
	function generateConfirm($payment_method) {
		$msc = oseRegistry::call('msc');
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', 'all');
		$taxParams = oseMscPublic::getTax($country, $state);
		$cart = oseMscPublic::getCart();
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		$cart->updateTaxParams('vat_number', $taxParams['vat_number']);
		$cart->refreshSubTotal();

		$msc_id = JRequest::getInt('msc_id',0);
		$msc_option = JRequest::getCmd('msc_option',null);
		$item = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option);
		$cart->addItem($item['entry_id'],$item['entry_type'],$item);
		$cart->update();
		$cart->refreshCartItems(); 

		$items = $cart->get('items');
		$item = $items[0];

		$msc_id = oseMscPublic::getEntryMscID($item);
		$msc_option = oseObject::getValue($item, 'msc_option');
		$payment_mode = oseMscPublic::getPaymentMode('payment_payment_mode');
		$payment_mode = oseMscPublic::savePaymentMode();
		$node = $msc->getInfo($msc_id, 'obj');
		$draw = new oseMscListDraw();
		$payment = oseRegistry::call('payment');
		$paymentView = $payment->getInstance('View');
		$items = $cart->get('items');
		$subtotal = oseMscPublic::getSubtotal();
		$total = $cart->get('total');
		$discount = $cart->get('discount');
		$osePaymentCurrency = $cart->get('currency');
		$item = $items[0];
		$array = array();
		$array['title'] = $draw->drawFirstTitle(oseObject::getValue($item, 'title'), oseObject::getValue($item, 'leaf'));
		if (oseObject::getValue($item, 'leaf')) {
			if (oseObject::getValue($item, 'recurrence_mode', 'period') == 'fixed') {
				$price = oseObject::getValue($item, 'second_price') . ' ' . oseObject::getValue($item, 'standard_recurrence');
				$paymentPre = JText::_('MANUAL_BILLING');
			} else {
				if ($payment_mode == 'a') {
					$price = oseObject::getValue($item, 'second_price') . ' ' . JText::_('FOR_EVERY') . ' ' . oseObject::getValue($item, 'standard_recurrence');
					if (oseObject::getValue($item, 'has_trial')) {
						$price .= ' (' . oseObject::getValue($item, 'first_price') . ' ' . JText::_('IN_THE_FIRST') . ' ' . oseObject::getValue($item, 'trial_recurrence') . ')';
					}
					$paymentPre = JText::_('AUTOMATIC_BILLING');
				} else {
					if (oseObject::getValue($item, 'eternal')) {
						$price = oseObject::getValue($item, 'second_price') . ' ' . JText::_('FOR_LIFETIME');
					} else {
						$price = oseObject::getValue($item, 'second_price') . ' ' . JText::_('FOR_EVERY') . ' ' . oseObject::getValue($item, 'standard_recurrence');
					}
					$paymentPre = JText::_('MANUAL_BILLING');
				}
			}
			$array['price'] = $draw->drawPrice(JText::_('BILLING_PLAN') . ': ' . $price);
			$array['payment_preference'] = '<div id="ose-confirm-preference">' . JText::_('PAYMENT_PREFERENCE') . ': ' . $paymentPre . '</div>';
			$gwInfo = oseRegistry::call('payment')->getInstance('GateWay')->getGWInfo($payment_method);
			if ($payment_method == 'oospay') {
				$payment_method = 'Garanti Bankası Güvenli Ödeme Noktası';
			}
			if (!empty($gwInfo) && $gwInfo->is_cc) {
				$array['payment_method'] = '<div id="ose-confirm-method">' . JText::_('PAYMENT_METHOD') . ': ' . JText::_('CREDIT_CARD') . '</div>';
				;
			} else {
				$array['payment_method'] = '<div id="ose-confirm-method">' . JText::_('PAYMENT_METHOD') . ': ' . JText::_(ucfirst($payment_method)) . '</div>';
			}
			$array['subtotal'] = '<div id="osetotalcosts"><div class="items">' . JText::_('SUBTOTAL') . ': ' . $osePaymentCurrency . ' ' . $subtotal . '</div>';
			$array['discount'] = '<div class="items">' . JText::_('DISCOUNT') . ': ' . $osePaymentCurrency . ' ' . $discount . '</div>';
			$array['tax'] = '<div class="items">' . JText::_('TAX') . ' (@' . $cart->getTaxParams('rate', '0') . '%): ' . $osePaymentCurrency . ' '
					. $cart->getTaxParams('amount', '0.00') . '</div>';
			if ($cart->getTaxParams('amount', 0) > 0) {
				if ($cart->getTaxParams('vat_number', false)) {
					$array['tax_vat_number'] = '<div class="items">' . JText::_('VAT_NUMBER') . ': ' . $cart->getTaxParams('vat_number') . '</div>';
				}
			}
			$array['total'] = '<div class="items" id ="osegradntotal">' . JText::_('GRANT_TOTAL') . ': ' . $osePaymentCurrency . ' ' . $total . '</div></div>';
		}
		if (is_array($array)) {
			$array = implode("\r\n", $array);
		}
		$divSelectedRow = $draw->drawDiv('ose-selected-row');
		$array = '<div class="ose-selected-heading">' . JText::_('SELECTED_MEMBERSHIP') . '</div>' . "\r\n" . $array;
		$html = sprintf($divSelectedRow, "\r\n" . $array . "\r\n");
		return $html;
	}
	function generateConfirmCart($payment_method) {
		$array = array();
		$cart = oseMscPublic::getCart();
		// begin to count tax
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', 'all');
		$taxParams = oseMscPublic::getTax($country, $state);
		$cart = oseMscPublic::getCart();
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		$cart->updateTaxParams('vat_number', $taxParams['vat_number']);
		$cart->refreshSubTotal();
		// end
		$items = $cart->get('items');
		$subtotal = oseMscPublic::getSubtotal();
		$total = $cart->get('total');
		$discount = $cart->get('discount');
		$msc = oseRegistry::call('msc');
		// begin to draw the confirm box
		$draw = new oseMscListDraw();
		$payment = oseRegistry::call('payment');
		$osePaymentCurrency = oseMscPublic::getSelectedCurrency();
		$paymentView = $payment->getInstance('View');
		$keys = array_keys($items);
		$payment_mode = $cart->getParams('payment_mode');
		if (strtolower($payment_mode) == "paypal_cc") {
			$payment_mode = JText::_("Credit Card");
		}
		$paymentPre = ($payment_mode == 'm') ? 'Manual Re-Billing ' : 'Automatic Re-Billing ';
		$tHtml = '<table width="100%"><th width="76%" class="first">Item</th><th width="10%">Length</th><th width="10%" class="last">Price</th>';
		foreach ($items as $item) {
			$msc_id = oseObject::getValue($item, 'entry_id');
			$msc_option = oseObject::getValue($item, 'msc_option');
			if ($payment_mode == 'm') {
				$rows = array();
				$rows['title'] = $draw->drawFirstTitle(oseObject::getValue($item, 'title'), oseObject::getValue($item, 'leaf'));
				$rows['recurrence'] = oseObject::getValue($item, 'standard_recurrence');
				$rows['price'] = oseObject::getValue($item, 'standard_price');
				$tHtml .= '<tr><td>' . implode('</td><td>', $rows) . '</td></tr>';
			}
		}
		$tHtml .= '</table>';
		$array['items'] = '<div id="osecart-items">' . $tHtml . '</div>';
		$array['payment_preference'] = '<div id="ose-confirm-preference">Payment Preference: ' . $paymentPre . '</div>';
		$gwInfo = oseRegistry::call('payment')->getInstance('GateWay')->getGWInfo($payment_method);
		if (!empty($gwInfo) && $gwInfo->is_cc) {
			$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: Credit Card</div>';
			;
		} else {
			$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: ' . ucfirst($payment_method) . '</div>';
		}
		$array['subtotal'] = '<div id="osetotalcosts"><div class="items">Subtotal: ' . $osePaymentCurrency . ' ' . $subtotal . '</div>';
		$array['discount'] = '<div class="items">Discount: ' . $osePaymentCurrency . ' ' . $discount . '</div>';
		$array['tax'] = '<div class="items">Tax: ' . $osePaymentCurrency . ' ' . $cart->getTaxParams('amount', '0.00') . '</div>';
		$array['total'] = '<div class="items" id ="osegradntotal">Grand Total: ' . $osePaymentCurrency . ' ' . $total . '</div></div>';
		if (is_array($array)) {
			$array = implode("\r\n", $array);
		}
		$divSelectedRow = $draw->drawDiv('ose-selected-row');
		$html = sprintf($divSelectedRow, "\r\n" . $array . "\r\n");
		return $html;
	}
	function addToCart($msc_id, $msc_option) {
		$cart = oseRegistry::Call('payment')->getInstance('Cart');
		$payment_mode = oseMscPublic::getPaymentMode();
		$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
		$cart->addItem($item['entry_id'], $item['entry_type'], $item);
		$cart->updateParams('payment_mode', $payment_mode);
		$cart->update();
	}
	function getBillingInfo() {
		$user = oseMscPublic::getUser();
		$item = array();
		$cart = oseMscPublic::getCart();
		$cartItems = $cart->get('items');
		if (!$user->guest) {
			$member = oseRegistry::call('member');
			$member->instance($user->id);
			$item = $member->getBillingInfo();
		} else {
			$item['id'] = 1;
			$item['city'] = '';
		}
		if (oseMscPublic::getRegisterForm() == 'onestep') {
			$cartItem = empty($cartItems[0]) ? array() : $cartItems[0];
			$item['msc_id'] = oseObject::getValue($cartItem, 'entry_id', 0);
			$item['ose_currency'] = $cart->get('currency');
			if (!empty($item['msc_id'])) {
				$item['msc_option'] = $cartItem['msc_option'];
			} else {
				$options = $this->getAllOptions();
				if (!empty($options)) {
					$item['msc_id'] = $options[0]['msc_id'];
					$item['msc_option'] = $options[0]['id'];
				}
			}
		} else {
			$item['ose_currency'] = $cart->get('currency');
			if ($cart->get('total') <= 0) {
				$item['total'] = 'free';
			} else {
				$item['total'] = 'nonfree';
			}
		}
		return $item;
	}
}
?>