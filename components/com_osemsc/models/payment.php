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
class osemscModelPayment extends oseMscModel {
	function __construct() {
		parent::__construct();
	}
	private function joinFree($oseMscPayment) {
		$msc = oseRegistry::call('msc');
		$msc_id = $oseMscPayment['msc_id'];
		$payment_mode = $oseMscPayment['payment_mode'];
		$ext = $msc->getExtInfo($msc_id, 'payment', 'obj');
		if ($payment_mode == 'a' || ($ext->payment_mode != 'b' && $ext->payment_mode != 'a')) {
			return false;
		}
		if ($ext->isFree) {
			$user = JFactory::getUser();
			$params['msc_id'] = $msc_id;
			$params['order_id'] = 'null';
			$params['join_from'] = 'payment';
			$params['member_id'] = $user->id;
			$params['allow_work'] = true;
			$params['master'] = true;
			$updated = $msc->runAction('member.msc.save', $params);
			if ($updated['success']) {
				return true;
			} else {
				return false;
			}
		}
	}
	function toPaymentVm($oseMscPayment) {
		$payment = oseRegistry::call('payment');
		if ($this->joinFree($oseMscPayment)) {
			$result['success'] = false;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Done');
			$result['returnUrl'] = JRoute::_('index.php?option=com_osemsc&view=member');
			return $result;
		}
		$msc_id = $oseMscPayment['msc_id'];
		$link = $payment->addCartlink($msc_id);
		$result = array();
		if ($link) {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JRoute::_($link);
			$result['payment_system'] = 'vm';
			return $result;
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Link Error...');
			return $result;
		}
	}
	function getMemberships() {
		$msc = oseRegistry::call('msc');
		$items = $msc->getInstance('tree')->getSubTreeDepth(0, 0, 'obj');
		$filter = array();
		foreach ($items as $key => $item) {
			if ($item->published) {
				$filter[$key] = $item;
			}
		}
		return $filter;
	}
	function getMembership($msc_id) {
		$db = oseDB::instance();
		$node = oseRegistry::call('payment')->getMscInfo($msc_id);
		return $node;
	}
	function generatePayment($payment_method) {
		$isGenerated = $this->generateOrder();
		if (!$isGenerated) {
			return false;
		}
		$order_id = $isGenerated;
		$payment = oseRegistry::call('payment');
		$html = '';
		$orderInfo = $payment->getOrder(array("order_id = {$order_id}"), 'obj');
		switch (strtolower($payment_method)) {
		case ('paypal'):
			$html = $payment->getPaypalForm($orderInfo);
			break;
		case ('creditcard'):
			$post = JRequest::get('post');
			$creditInfo = array();
			$creditInfo['creditcard_type'] = $post['creditcard_type'];
			$creditInfo['creditcard_name'] = $post['creditcard_name'];
			$creditInfo['creditcard_number'] = $post['creditcard_number'];
			$creditInfo['creditcard_cvv'] = $post['creditcard_cvv'];
			$creditInfo['creditcard_month'] = $post['creditcard_type'];
			$creditInfo['creditcard_year'] = $post['creditcard_year'];
			$html = $payment->getCCForm($orderInfo, $creditInfo);
			break;
		case ('gco'):
			$html = $payment->getGCOForm($orderInfo);
			break;
		case ('2co'):
			$html = $payment->get2COForm($orderInfo);
			break;
		case ('poffline'):
		default:
			break;
		}
		return $html;
	}
	function generateConfirm($payment_method) {
		$msc = oseRegistry::call('msc');
		$country = JRequest::getCmd('bill_country', null);
		$state = JRequest::getCmd('bill_state', null);
		$taxParams = oseMscPublic::getTax($country, $state);
		$cart = oseMscPublic::getCart();
		$cart->updateTaxParams('country', $country);
		$cart->updateTaxParams('state', $state);
		$cart->updateTaxParams('rate', $taxParams['rate']);
		$cart->updateTaxParams('file_control', $taxParams['file_control']);
		$cart->updateTaxParams('has_file_control', $taxParams['has_file_control']);
		$cart->refreshSubTotal();
		$items = $cart->get('items');
		$item = $items[0];
		$msc_id = oseMscPublic::getEntryMscID($item);
		$msc_option = oseObject::getValue($item, 'msc_option');
		$payment_mode = $cart->getParams('payment_mode');
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
			if ($payment_mode == 'a') {
				$price = oseObject::getValue($item, 'second_price') . ' for every ' . oseObject::getValue($item, 'standard_recurrence');
				if (oseObject::getValue($item, 'has_trial')) {
					$price .= ' (' . oseObject::getValue($item, 'first_price') . ' in the first ' . oseObject::getValue($item, 'trial_recurrence') . ')';
				}
				$paymentPre = 'Automatic Billing ';
			} else {
				if (oseObject::getValue($item, 'eternal')) {
					$price = oseObject::getValue($item, 'second_price') . ' for lifetime';
				} else {
					$price = oseObject::getValue($item, 'second_price') . ' for every ' . oseObject::getValue($item, 'standard_recurrence');
				}
				$paymentPre = 'Manual Billing ';
			}
			$array['price'] = $draw->drawPrice('Billing Plan: ' . $price);
			$array['payment_preference'] = '<div id="ose-confirm-preference">Payment Preference: ' . $paymentPre . '</div>';
			if ($payment_method == 'authorize' || $payment_method == 'paypal_cc' || $payment_method == 'eway') {
				$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: Credit Card</div>';
				;
			} else {
				$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: ' . JText::_(ucfirst($payment_method)) . '</div>';
			}
			$array['subtotal'] = '<div id="osetotalcosts"><div class="items">Subtotal: ' . $osePaymentCurrency . ' ' . $subtotal . '</div>';
			$array['discount'] = '<div class="items">Discount: ' . $osePaymentCurrency . ' ' . $discount . '</div>';
			$array['tax'] = '<div class="items">Tax: ' . $osePaymentCurrency . ' ' . $cart->getTaxParams('amount', '0.00') . '</div>';
			$array['total'] = '<div class="items" id ="osegradntotal">Grand Total: ' . $osePaymentCurrency . ' ' . $total . '</div></div>';
		}
		if (is_array($array)) {
			$array = implode("\r\n", $array);
		}
		$divSelectedRow = $draw->drawDiv('ose-selected-row');
		$array = '<div class="ose-selected-heading">' . JText::_('Selected Membership') . '</div>' . "\r\n" . $array;
		$html = sprintf($divSelectedRow, "\r\n" . $array . "\r\n");
		return $html;
	}
	function generateConfirmCart($payment_method) {
		$array = array();
		$cart = oseMscPublic::getCart();
		$items = $cart->get('items');
		$subtotal = oseMscPublic::getSubtotal();
		$total = $cart->get('total');
		$discount = $cart->get('discount');
		$msc = oseRegistry::call('msc');
		$draw = new oseMscListDraw();
		$payment = oseRegistry::call('payment');
		$osePaymentCurrency = oseMscPublic::getSelectedCurrency();
		$paymentView = $payment->getInstance('View');
		$keys = array_keys($items);
		$payment_mode = $cart->getParams('payment_mode');
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
		$array['subtotal'] = '<div id="osetotalcosts"><div class="items">Subtotal: ' . $osePaymentCurrency . ' ' . $subtotal . '</div>';
		$array['discount'] = '<div class="items">Discount: ' . $osePaymentCurrency . ' ' . $discount . '</div>';
		$array['total'] = '<div class="items" id ="osegradntotal">Grand Total: ' . $osePaymentCurrency . ' ' . $total . '</div></div>';
		$array['payment_preference'] = '<div id="ose-confirm-preference">Payment Preference: ' . $paymentPre . '</div>';
		if ($payment_method == 'authorize' || $payment_method == 'paypal_cc' || $payment_method == 'eway') {
			$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: Credit Card</div>';
			;
		} else {
			$array['payment_method'] = '<div id="ose-confirm-method">Payment Method: ' . ucfirst($payment_method) . '</div>';
		}
		if (is_array($array)) {
			$array = implode("\r\n", $array);
		}
		$divSelectedRow = $draw->drawDiv('ose-selected-row');
		$html = sprintf($divSelectedRow, "\r\n" . $array . "\r\n");
		return $html;
	}
	function processPayment($payment_method, $order_id, $post, $msc_option) {
		$payment = oseRegistry::call('payment');
		$orderInfo = $payment->getOrder(array("order_id = {$order_id}"), 'obj');
		$msc_id = $orderInfo->entry_id;
		$member_id = $orderInfo->user_id;
		if ($orderInfo->payment_price > 0) {
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post, $msc_option);
		} elseif ($orderInfo->payment_price == 0 && $orderInfo->payment_mode == 'a') {
			return oseMscPublic::processPayment($payment_method, $orderInfo, $post, $msc_option);
		} else {
			$result = $payment->getInstance('Order')->confirmOrder($order_id, array(), $msc_id, $member_id, $payment_method);
			$result['payment_method'] = 'none';
			return $result;
		}
	}
	function getBillingInfo() {
		$initMscPayment = array('msc_id' => 0, 'msc_option' => null);
		$cart = oseMscPublic::getCart();
		;
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		$member->instance($user->id);
		$item = $member->getBillingInfo();
		$cartItems = $cart->get('items');
		$cartItem = $cartItems[0];
		$item['msc_option'] = $cartItem['msc_option'];
		return $item;
	}
	function getPaymentMsc($msc_id, $msc_option) {
		$session = &JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency', oseRegistry::call('msc')->getConfig('currency', 'obj')->primary_currency);
		$item = oseRegistry::call('msc')->getPaymentMscInfo($msc_id, $osePaymentCurrency, $msc_option);
		return $item;
	}
	function toPaymentPage() {
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		$member->instance($user->id);
		return $member->getMemberPanelView('Payment');
	}
}
