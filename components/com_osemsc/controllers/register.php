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
class osemscControllerRegister extends osemscController {
	function __construct() {
		parent::__construct();
		$this->registerTask('subscribe', 'addToCart');
		$this->registerTask('toPaymentPage', 'addToCart');
		$this->registerTask('addCartItem', 'addToCart');
	}
	function save() {
		if (isset($_POST['msc_id']) && isset($_POST['msc_option'])) {
			$msc_id = JRequest::setVar('msc_id', JRequest::getInt('msc_id'));
			$msc_option = JRequest::setVar('msc_option', $_POST['msc_option']);
			self::addtoCart(false);
		}
		ini_set('max_execution_time', '180');
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		if (count($items) < 1) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('No Item! Please go to membership list and select one.');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		// count the tax params;
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', 'all');
		$taxParams = oseMscPublic::getTax($country, $state);
		oseMscPublic::savePaymentMode();
		$cart = oseMscPublic::getCart();
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		$cart->updateTaxParams('vat_number', $taxParams['vat_number']);
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		$user = oseMscPublic::getUser();
		if ($user->guest) {
			if (empty($register_form) || $register_form == 'default') {
				$this->saveSC();
			} else {
				switch ($register_form) {
				case ('onestep'):
					$this->saveOS();
					break;
				}
			}
		} else {
			if (empty($register_form) || $register_form == 'default') {
				$result = $this->toPaymentCart();
			} else {
				switch ($register_form) {
				case ('onestep'):
					$result = $this->toPaymentOS();
					break;
				}
			}
			$result['activation'] = false;
			$result = oseJson::encode($result);
			oseExit($result);
		}
	}
	function saveMobile() {
		ini_set('max_execution_time', '180');
		$config = osemscPublic::getConfig('register', 'obj');
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', null);
		$creditcard_expirationdate = JRequest::getVar('creditcard_expirationdate', 0);
		if (!empty($creditcard_expirationdate)) {
			$array = explode('-', $creditcard_expirationdate);
			JRequest::setVar('creditcard_year', $array[0]);
			JRequest::setVar('creditcard_month', $array[1]);
		}
		$cart = oseMscPublic::getCart();
		$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
		$cart->addItem($item['entry_id'], $item['entry_type'], $item);
		$cart->update();
		$items = $cart->get('items');
		if (count($items) < 1) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('No Item! Please go to membership list and select one.');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		// count the tax params;
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', 'all');
		$taxParams = oseMscPublic::getTax($country, $state);
		oseMscPublic::savePaymentMode();
		$cart = oseMscPublic::getCart();
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		$cart->updateTaxParams('vat_number', $taxParams['vat_number']);
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		$user = oseMscPublic::getUser();
		if ($user->guest) {
			if (empty($register_form) || $register_form == 'default') {
				$this->saveSC();
			} else {
				switch ($register_form) {
				case ('onestep'):
					$this->saveOS();
					break;
				}
			}
		} else {
			if (empty($register_form) || $register_form == 'default') {
				$result = $this->toPaymentCart();
			} else {
				switch ($register_form) {
				case ('onestep'):
					$result = $this->toPaymentOS();
					break;
				}
			}
			$result['activation'] = false;
			$result = oseJson::encode($result);
			oseExit($result);
		}
	}
	private function saveOS() {
		$result = array();
		$activation = JComponentHelper::getParams('com_users')->get('allowUserRegistration');
		if ($activation == 0) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('The site is not allowed to register now!');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		ob_clean();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$item = $items[0];
		if (oseObject::getValue($item, 'entry_type') != 'license' || oseObject::getValue($item, 'entry_type') != 'msc') {
			if (oseObject::getValue($item, 'entry_id') < 1) {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_('Please Select One Membership!');
				$result = oseJson::encode($result);
				oseExit($result);
			}
		}
		$msc_id = oseMscPublic::getEntryMscID($item);
		$allow_to_join = $this->checkJoiningControl($msc_id);
		if ($allow_to_join == false) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Permission denied, this membership is for members only!');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		$post = JRequest::get('post');
		$post['payment_method'] = oseMscPublic::getPaymentMethod();
		$model = $this->getModel('Register', 'osemscModel');
		$updated = $model->saveOS($post);
		$result = array();
		if ($updated['success']) {
			$result['reload'] = $updated['reload'];
			// Process the Payment
			$order_id = JRequest::getInt('order_id', 0);
			$msc_id = oseMscPublic::getEntryMscID($item);
			$msc_option = oseObject::getValue($item, 'msc_option');
			if ($order_id) {
				$updated = $model->processPayment($order_id, $post, $msc_option);
				if (!$updated['success']) {
					$updated['reload'] = $result['reload'];
					$result = oseJson::encode($updated);
					oseExit($result);
				} else {
					//send receipt
					$updated['reload'] = $result['reload'];
					$result = $updated;
					$params = JComponentHelper::getParams('com_users');
					$useractivation = $params->get('useractivation');
					if ($useractivation == 1) {
						$activationMsg = JText::_('ACCOUNT_ACTIVATED_SELF');
						$result['content'] = $activationMsg . "<br>" . $result['content'];
					}
					if ($useractivation == 2) {
						$activationMsg = JText::_('ACCOUNT_ACTIVATED_ADMIN');
						$result['content'] = $activationMsg . "<br>" . $result['content'];
					}
				}
			}
		} else {
			$result = $updated;
		}
		$comUserparams = JComponentHelper::getParams('com_users');
		$useractivation = $comUserparams->get('useractivation');
		if ($useractivation != 1 && $useractivation != 2) {
			$result['activation'] = false;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	private function saveSC() {
		$result = array();
		if (JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('The site is not allowed to register now!');
			$result = oseJson::encode($result);
			return $result;
		}
		ob_clean();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$post = JRequest::get('post');
		$post['payment_method'] = oseMscPublic::getPaymentMethod();
		$model = $this->getModel('Register', 'osemscModel');
		$updated = $model->saveCart($post);
		$result = array();
		if ($updated['success']) {
			// Process the Payment
			$order_id = JRequest::getInt('order_id', 0);
			if ($order_id) {
				$updated = $model->processPaymentCart($order_id, $post);
				if (!$updated['success']) {
					$result = oseJson::encode($updated);
					oseExit($result);
				} else {
					//send receipt
					$result = $updated;
				}
			}
		} else {
			$result = $updated;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function login() {
		$session = JFactory::getSession();
		$session->clear('oseReturnUrl');
		$model = $this->getModel('register');
		$username = JRequest::getVar('username', '', 'method', 'username');
		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$updated = $model->login($username, $password);
		$result = array();
		if ($updated['success']) {
			$result = $updated;
		} else {
			$result = $updated;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function logout() {
		$result = array();
		$app = JFactory::getApplication('SITE');
		//preform the logout action
		$error = $app->logout();
		if (!JError::isError($error)) {
			$session = JFactory::getSession();
			$return = $session->get('oseReturnUrl', base64_encode('index.php'));
			$session->clear('oseReturnUrl');
			// if not enable the plg??
			$plugin = JPluginHelper::getPlugin('user', 'oseuser');
			if (JOOMLA16) {
				if (empty($plugin)) {
					$logoutRedirect = false;
					$logoutredmenuid = 0;
				} else {
					$pluginParams = oseJson::decode($plugin->params);
					$logoutRedirect = $pluginParams->logoutRedirect;
					$logoutredmenuid = $pluginParams->logoutredmenuid;
					$sefroutemethod = $pluginParams->sefroutemethod;
				}
			} else {
				$pluginParams = new JParameter($plugin->params);
				$logoutRedirect = $pluginParams->get('logoutRedirect');
				$logoutredmenuid = $pluginParams->get('logoutredmenuid');
				$sefroutemethod = $pluginParams->get('sefroutemethod');
			}
			if ($logoutRedirect && !empty($logoutredmenuid)) {
				$db = JFactory::getDBO();
				$query = "SELECT * FROM `#__menu` WHERE `id` = " . (int) $logoutredmenuid;
				$db->setQuery($query);
				$menu = $db->loadObject();
				$return = $uri = oseMscPublic::pointedRedirection($sefroutemethod, $menu);
				$result['success'] = true;
				$result['returnUrl'] = $return;
			} else {
				$result['success'] = true;
				$result['returnUrl'] = base64_decode($return);
			}
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Failed');
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function quicklogout() {
		$app = JFactory::getApplication();
		// Perform the log in.
		$error = $app->logout();
		// Check if the log out succeeded.
		if (!($error instanceof Exception)) {
			// Get the return url from the request and validate that it is internal.
			$return = JRequest::getVar('return', '', 'method', 'base64');
			$return = base64_decode($return);
			if (!JURI::isInternal($return)) {
				$return = '';
			}
			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		} else {
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
	}
	function getAddons() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			$pos = JRequest::getCmd('pos', 'billing');
			$type = 'register_' . $pos;
			$items = oseMscAddon::getAddonList($type, false, null, 'obj');
			$items = oseJson::encode($items);
			oseExit($items);
		} else {
			switch ($register_form) {
			case ('onestep'):
				$pos = JRequest::getCmd('pos', 'body');
				$type = 'registerOS_' . $pos;
				$items = oseMscAddon::getAddonList($type, false, null, 'obj');
				$items = oseJson::encode($items);
				oseExit($items);
				break;
			}
		}
	}
	function formValidate() {
		$result = array();
		$result['success'] = false;
		$user = JFactory::getUser();
		$fieldName = JRequest::getCmd('field_name', null);
		$type = JRequest::getCmd('addon', null);
		if (empty($type)) {
			$result['title'] = 'Error';
			$result['content'] = 'No addon name';
		}
		$addon = oseMscAddon::getAddonbyName($type, 'registerOS_body', 'obj');
		$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
		$updated = oseMscAddon::runAction($action_name, array('inner' => true, 'field_name' => $fieldName));
		$result = $updated;
		if ($updated['success']) {
		} else {
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function generateConfirmDialog() {
		$model = $this->getModel('register');
		$post = JRequest::get('post');
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		if (empty($items)) {
			$item = array('entry_id' => $post['msc_id'], 'entry_type' => 'msc', 'msc_option' => $post['msc_option']);
			$cart->addItem($item['entry_id'], $item['entry_type'], $item);
			$cart->update();
		}
		oseMscPublic::savePaymentMode();
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		$payment_method = oseMscPublic::getPaymentMethod();
		if (empty($register_form) || $register_form == 'default') {
			$html = $model->generateConfirmCart($payment_method);
		} else {
			$payment_mode = oseMscPublic::getPaymentMode('payment_mode');
			$html = $model->generateConfirm($payment_method);
		}
		oseExit($html);
	}
	private function runStep() {
		$result = array();
		$model = $this->getModel('register');
		$session = JFactory::getSession();
		$lastStep = $session->get('ose_reg_step', 'cart');
		if (!$model->checkCartItems()) {
			$session->set('ose_reg_step', 'cart');
		}
		$step = $session->get('ose_reg_step', 'cart');
		switch ($step) {
		case ('cart'):
			$result['success'] = true;
			$user = JFactory::getUser();
			if ($user->guest) {
				$session->set('ose_reg_step', 'signin');
			} else {
				$session->set('ose_reg_step', 'billing');
			}
			if ($lastStep != $step) {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['success'] = JText::_('No item in your cart, please click OK to return');
			} else {
			}
			return $result;
			break;
		case ('signin'):
			$signin = JRequest::getCmd('signin', 'register');
			if ($signin == 'login') {
				$username = JRequest::getVar('username', '', 'method', 'username');
				$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
				$updated = $model->login($username, $password);
				if ($updated['success']) {
					$session->set('ose_reg_step', 'billing');
				}
				$result = $updated;
			} else {
				$session->set('ose_reg_step', 'billing_reg');
				$result['success'] = true;
			}
			break;
		case ('billing'):
			$post = JRequest::get('post');
			$user = JFactory::getUser();
			$payment_method = $post['payment_payment_method'];
			$updated = oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
			if (!$updated['success']) {
				return $updated;
			}
			$params['member_id'] = $user->id;
			$params['payment_method'] = $payment_method;
			$action_name = 'register.payment.save';
			$updated = oseRegistry::call('msc')->runAddonAction($action_name, $params);
			if (!$updated['success']) {
				$result = $updated;
			} else {
				$order_id = JRequest::getInt('order_id', 0);
				if ($order_id) {
					$updated = $model->processPaymentCart($payment_method, $order_id, $post);
					if (!$updated['success']) {
						$result = $updated;
					} else {
						$result = $updated;
					}
				}
			}
			$result = oseJson::encode($result);
			oseExit($result);
			break;
		case ('billing_reg'):
			$model = $this->getModel('register');
			$post = JRequest::get('post');
			$username = $post['juser_username'];
			$updated = oseMscPublic::uniqueUserName($username, 0);
			if (!$updated['success']) {
				$updated = oseJson::encode($updated);
				oseExit($updated);
			}
			$updated = $model->saveCart($post);
			if ($updated['success']) {
				// Process the Payment
				$order_id = JRequest::getInt('order_id', 0);
				if ($order_id) {
					$payment_method = $post['payment_payment_method'];
					$updated = $model->processPaymentCart($payment_method, $order_id, $post);
					if (!$updated['success']) {
						$result = oseJson::encode($updated);
						oseExit($result);
					} else {
						$result = $updated;
					}
				}
			} else {
				$result = $updated;
			}
			$result = oseJson::encode($result);
			oseExit($result);
			break;
		default:
			oseExit();
			break;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function loadStepPage() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if ($register_form == 'default' || empty($register_form)) {
			$session = JFactory::getSession();
			$model = $this->getModel('register');
			if (!$model->checkCartItems()) {
				$session->set('ose_reg_step', 'cart');
			}
			$step = $session->get('ose_reg_step', 'cart');
			switch ($step) {
			case ('cart'):
			case ('signin'):
			case ('billing'):
				$tpl = $step;
				break;
			case ('billing_reg'):
				$user = JFactory::getUser();
				if ($user->guest) {
					$tpl = $step;
				} else {
					$step = $session->set('ose_reg_step', 'billing');
					$tpl = 'billing';
				}
				break;
			}
			require_once(OSEMSC_F_VIEW . DS . 'register' . DS . 'tmpl' . DS . "default_{$tpl}.php");
			oseExit();
		} else {
			oseExit('You do not have access to do it!');
		}
	}
	function changeCurrency() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		$currency = JRequest::getCmd('ose_currency', oseRegistry::call('msc')->getConfig('currency', 'obj')->primary_currency);
		oseMscPublic::setSelectedCurrency($currency);
		oseExit();
	}
	function changePaymentMode() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if ($register_form == 'default' || empty($register_form)) {
			$currency = JRequest::getCmd('ose_payment_mode', 'm');
			oseMscPublic::setPaymentMode($currency);
		}
		oseExit();
	}
	function removeCartItem() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			$id = JRequest::getCmd('entry_id', null);
			$type = JRequest::getCmd('entry_type', null);
			$cart = oseRegistry::Call('payment')->getInstance('Cart');
			$cart->removeItem($id, $type);
			$model = $this->getModel('register');
			if (!$model->checkCartItems()) {
				$cart->cart = array();
			}
			$cart->update();
		}
		oseExit('success');
	}
	function backStep() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			$step = JRequest::getCmd('step', 'cart');
			$session = JFactory::getSession();
			$session->set('ose_reg_step', $step);
		}
		oseExit();
	}
	function addToCart($exit = true) {
		$cart = oseRegistry::Call('payment')->getInstance('Cart');
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', null);
		$payment_mode = oseMscPublic::getPaymentMode();
		$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
		$cart->addItem($item['entry_id'], $item['entry_type'], $item);
		$cart->updateParams('payment_mode', $payment_mode);
		$cart->update();
		$session = JFactory::getSession();
		$session->set('ose_reg_step', 'cart');
		$result = array();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Added to Cart!');
		$Itemid = JRequest::getInt('Itemid');
		$Itemid = (!empty($Itemid)) ? "&Itemid=" . $Itemid : "";
		$result['link'] = str_replace("&amp;", "&", JRoute::_('index.php?option=com_osemsc&view=register' . $Itemid));
		$result = oseJson::encode($result);
		if ($exit == true) {
			oseExit($result);
		}
	}
	private function toPaymentCart() {
		$model = $this->getModel('register');
		$post = JRequest::get('POST');
		$post['payment_method'] = $payment_method = oseMscPublic::getPaymentMethod();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$payment_mode = $cart->getParams('payment_mode');
		if (empty($payment_mode)) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Paying...');
			return $result;
		}
		$list = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
		$list = oseMscPublic::filter($list, array('juser'), false);
		foreach ($list as $addon) {
			$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
			$updated = oseMscAddon::runAction($action_name, array('inner' => true));
			if (!$updated['success']) {
				return $updated;
			}
		}
		oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
		$user = JFactory::getUser();
		$params['member_id'] = $user->id;
		$params['payment_method'] = $payment_method;
		oseRegistry::call('msc')->runAddonAction('register.payment.save', $params);
		$order_id = JRequest::getInt('order_id', 0);
		if ($order_id) {
			$updated = $model->processPaymentCart($order_id, $post);
			$result = $updated;
			if (is_array($result)) {
				$result['order_id'] = $order_id;
			}
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Creating Order...');
		}
		return $result;
	}
	private function toPaymentOS() {
		$model = $this->getModel('register');
		$post = JRequest::get('POST');
		$post['payment_method'] = $payment_method = oseMscPublic::getPaymentMethod();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$item = $items[0];
		$payment_mode = $cart->getParams('payment_mode');
		$msc_id = oseMscPublic::getEntryMscID($item);
		$allow_to_join = $this->checkJoiningControl($msc_id);
		if ($allow_to_join == false) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Permission denied, this membership is for members only!');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		$msc_option = JRequest::getCmd('msc_option', null);
		if (empty($payment_mode)) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Paying...');
			return $result;
		}
		if (empty($msc_id)) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Paying...');
			return $result;
		}
		$list = oseMscAddon::getAddonList('registerOS_body', false, 1, 'obj');
		$list = oseMscPublic::filter($list, array('juser'), false);
		foreach ($list as $addon) {
			$action_name = oseMscAddon::getActionName($addon, 'formValidate', 'register');
			$updated = oseMscAddon::runAction($action_name, array('inner' => true));
			if (!$updated['success']) {
				return $updated;
			}
		}
		$user = oseMscPublic::getUser();
		$params['member_id'] = $user->id;
		$params['payment_method'] = $payment_method;
		oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
		oseRegistry::call('msc')->runAddonAction('register.profile.save', $params);
		$order = oseRegistry::call('msc')->runAddonAction('register.payment.save', $params);
		$order_id = $order['order_id'];
		if ($order_id) {
			$updated = $model->processPayment($order_id, $post, $msc_option);
			$result = $updated;
			if (is_array($result)) {
				$result['order_id'] = $order_id;
			}
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Creating Order...');
		}
		return $result;
	}
	function getBillingInfo() {
		$model = $this->getModel('register');
		$item = $model->getBillingInfo();
		$result = array();
		if (!empty($item)) {
			$result['success'] = true;
			$result['total'] = 1;
			$result['results'] = $item;
		} else {
			$result['success'] = true;
			$result['total'] = 0;
			$result['results'] = array();
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function checkFree() {
		$cart = oseMscPublic::getCart();
		$cartItems = $cart->get('items');
		$item = array();
		if ($cart->get('total') <= 0) {
			$item['free'] = 'free';
		} else {
			$item['free'] = 'nonfree';
		}
		$result = oseJson::encode($item);
		oseExit($result);
	}
	function checkJoiningControl($msc_id) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$query = " SELECT `params` FROM `#__osemsc_ext` " . " WHERE `id` = " . (int) $msc_id . " AND `type` = 'msc'";
		$db->setQuery($query);
		$result = $db->loadResult();
		$result = oseJSON::decode($result);
		if (!empty($result->control_joining) && $result->control_joining == true) {
			if (!empty($result->joined_msc)) {
				if (!isset($result->control_active)) {
					$result->control_active = 1;
				}
				$query = " SELECT `id` FROM `#__osemsc_member` " . " WHERE `msc_id` = " . (int) $result->joined_msc . " AND `member_id` = " . (int) $user->id . " AND `status` = "
						. (int) $result->control_active;
				$db->setQuery($query);
				$result = $db->loadResult();
				return (!empty($result)) ? true : false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
}
?>