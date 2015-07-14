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
class osemscControllerPayment extends osemscController {
	function __construct() {
		parent::__construct();
	}
	function generateConfirmDialog() {
		$model = $this->getModel('payment');
		$payment_mode = JRequest::getString('payment_mode', 'm');
		$payment_method = JRequest::getString('payment_method', null);
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', null);
		$html = $model->generateConfirm($payment_method);
		$result = array();
		if ($html) {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = $html;
		} else {
			$html = JText::_('Error!');
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Registering...');
		}
		$result = oseJson::encode($html);
		oseExit($html);
	}
	function toPaymentVm() {
		$result = array();
		$config = oseRegistry::call('msc')->getConfig('payment', 'obj');
		if ($config->payment_system != 'vm') {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(' Config Error...');
		} else {
			$session = JFactory::getSession();
			$oseMscPayment = $session->get('oseMscPayment', array());
			if (empty($oseMscPayment)) {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Session Error...');
			} else {
				$model = $this->getModel('payment');
				$updated = $model->toPaymentVm($oseMscPayment);
				$result = $updated;
			}
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getValidPaymentMode() {
		$model = $this->getModel('payment');
		$msc_id = JRequest::getInt('msc_id', 0);
		$payment_mode = JRequest::getWord('payment_moded', 0);
		if (!empty($msc_id)) {
			$session = JFactory::getSession();
			$oseMscPayment = array();
			$oseMscPayment['msc_id'] = $msc_id;
			$oseMscPayment['payment_mode'] = $payment_mode;
			$session->set('oseMscPayment', $oseMscPayment);
		}
		$memberships = $model->getValidPaymentMode();
		$result = array();
		$total = count($memberships);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $memberships;
		} else {
			$result['total'] = 0;
			$result['results'] = '';
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getMemberships() {
		$model = $this->getModel('payment');
		$memberships = $model->getMemberships();
		$result = array();
		$total = count($memberships);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $memberships;
		} else {
			$result['total'] = $total;
			$result['results'] = '';
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getMembership() {
		$model = $this->getModel('payment');
		$msc_id = JRequest::getInt('msc_id', 0);
		$membership = $model->getMembership($msc_id);
		$result = array();
		if (empty($membership)) {
			$result['total'] = 0;
			$result['membership'] = '';
		} else {
			$result['total'] = 1;
			$result['membership'] = $membership;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function generatePayment() {
		$model = $this->getModel('payment');
		$payment_method = JRequest::getString('payment_method', null);
		$html = $model->generatePayment($payment_method);
		$result = array();
		if ($html) {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = $html;
			$result['payment_method'] = $payment_method;
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Registering...');
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function subscribe() {
		$msc_id = JRequest::getInt('msc_id', 0);
		if (!empty($msc_id)) {
			$session = JFactory::getSession();
			$session->set('pay_msc_id', $msc_id);
		}
		JRequest::setVar('view', 'payment');
		parent::display();
	}
	function toPayment() {
		ini_set('max_execution_time', '180');
		$result = array();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		if (count($items) < 1) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('No Item! Please go to membership list and select one.');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			$result = $this->toPaymentCart();
		} else {
			switch ($register_form) {
			case ('onestep'):
				$result = $this->toPaymentOS();
				break;
			}
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	private function toPaymentCart() {
		$model = $this->getModel('payment');
		$post = JRequest::get('POST');
		$payment_mode = JRequest::getString('payment_payment_mode', 'm');
		$msc_id = JRequest::getInt('msc_id', 0);
		$payment_method = JRequest::getString('payment_payment_method', 'authorize');
		$msc_option = JRequest::getCmd('msc_option', null);
		if ($msc_id < 1) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Paying...');
			return $result;
		}
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
		} else {
			$cart = oseMscPublic::getCart();
			$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
			$cart->addItem($item['entry_id'], $item['entry_type'], $item);
			$cart->updateParams('payment_mode', $payment_mode);
			$cart->update();
		}
		oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
		$order_id = $model->generateOrder($msc_id, $msc_option, $payment_mode, $payment_method);
		if ($order_id) {
			$updated = $model->processPayment($payment_method, $order_id, $post, $msc_option);
			$result = $updated;
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Creating Order...');
		}
		return $result;
	}
	private function toPaymentOS() {
		$model = $this->getModel('payment');
		$post = JRequest::get('POST');
		$payment_mode = oseMscPublic::getPaymentMode('payment_payment_mode');//JRequest::getString('payment_payment_mode','m');
		$payment_method = JRequest::getString('payment_payment_method', 'authorize');
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$item = $items[0];
		$msc_id = oseMscPublic::getEntryMscID($item);
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
		} else {
		}
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', null);
		$taxParams = oseMscPublic::getTax($country, $state);
		oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
		$cart = oseMscPublic::getCart();
		$user = JFactory::getUser();
		$params['member_id'] = $user->id;
		$params['payment_method'] = $payment_method;
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		oseRegistry::call('msc')->runAddonAction('register.payment.save', $params);
		$order_id = JRequest::getInt('order_id', 0);
		if ($order_id) {
			$updated = $model->processPayment($payment_method, $order_id, $post, $msc_option);
			$result = $updated;
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Fail Creating Order...');
		}
		return $result;
	}
	function getBillingInfo() {
		$model = $this->getModel('payment');
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
	function getPaymentMsc() {
		$session = JFactory::getSession();
		$items = oseMscPublic::getCartItems();
		$oseMscPayment = $items[0];
		$model = $this->getModel('payment');
		$msc_id = oseMscPublic::getEntryMscID($oseMscPayment);
		$item = $model->getPaymentMsc($msc_id, oseObject::getValue($oseMscPayment, 'msc_option'));
		$result = array();
		if (!empty($item)) {
			$result['total'] = 1;
			$result['results'] = $item;
		} else {
			$result['total'] = 0;
			$result['results'] = array();
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function toPaymentPage() {
		$model = $this->getModel('payment');
		$updated = $model->toPaymentPage();
		$result = array();
		if (!$updated) {
			$result['success'] = false;
			$result['title'] = JText::_('Notice');
			$result['content'] = JText::_('You don\'t need to renew the membership');
		} else {
			$payment_mode = oseMscPublic::getPaymentMode('payment_mode');
			$msc_id = JRequest::getInt('msc_id', 0);
			$msc_option = JRequest::getCmd('msc_option', null);
			$session = JFactory::getSession();
			$session->set('ose_reg_step', 'cart');
			if (empty($payment_mode)) {
				$result['success'] = false;
				$result['title'] = JText::_('Error!');
				$result['content'] = JText::_('Have Not Selected Payment Method');
				$result = oseJson::encode($result);
				oseExit($result);
			}
			if (!empty($msc_id)) {
				$cart = oseMscPublic::getCart();
				$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
				$cart->addItem($item['entry_id'], $item['entry_type'], $item);
				$cart->updateParams('payment_mode', $payment_mode);
				$cart->update();
			}
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done');
			$result['link'] = JRoute::_('index.php?option=com_osemsc&view=payment');
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getAddons() {
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
		} else {
			switch ($register_form) {
			case ('onestep'):
				$type = 'paymentOS';
				$items = oseMscAddon::getAddonList($type, false, null, 'obj');
				$items = oseJson::encode($items);
				oseExit($items);
				break;
			}
		}
	}
}
