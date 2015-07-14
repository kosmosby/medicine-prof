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
class osePaymentOrdergco extends osePaymentGateWay {
	protected $postVar = array();
	protected $ccInfo = array();
	protected $orderInfo = null;
	function __construct() {
		parent::__construct();
	}
	function GCOOneOffPostForm($orderInfo, $params = array()) {
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$gco_id = $pConfig->google_checkout_id;
		$gco_key = $pConfig->google_checkout_key;
		$html = array();
		$test_mode = $pConfig->gco_testmode;
		if (empty($gco_id) || empty($gco_key)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		if ($test_mode == true) {
			$url = "https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/" . $gco_id;
		} else {
			$url = "https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/" . $gco_id;
		}
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo = self::getBillingInfo($orderInfo->user_id);
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = JFactory::getUser($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$msc_name = $desc;
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $order_id));
		$returnUrl = $returnUrl ? $returnUrl : JURI::base() . "index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$vendor_image_url = "";
		$app = JFactory::getApplication();
		$currentSession = JSession::getInstance('none', array());
		$stores = $currentSession->getStores();
		$html['form'] = '<form action="' . $url . '" method="post" >';
		$post_variables = array(
								"item_name_1" => $desc . ' - ' . JText::_('ORDER_ID') . ' ' . $order_id, 
								"item_description_1" => JText::_('UNIQUE_ORDER_NUMBER') . ':' . $order_number, 
								"item_merchant_id_1" => $order_id,
								"item_quantity_1" => "1", 
								"item_price_1" => $amount, 
								"item_currency_1" => $currency, 
								"continue_url" => $returnUrl
							   );
		$html['form'] .= '<input type="image" id="gco_image" name="cartImage" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'. JText::_('Fast checkout through Google') . '" />';
		// Process payment variables;
		$html['url'] = $url . "?";
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
			$html['url'] .= $name . "=" . urlencode($value) . "&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function GCOrecurringPostForm($orderInfo, $params = array()) {
		$pConfig = oseMscConfig::getConfig('payment', 'obj');
		$gco_id = $pConfig->google_checkout_id;
		$gco_key = $pConfig->google_checkout_key;
		$gco_type = 'google';
		$html = array();
		$test_mode = $pConfig->gco_testmode;
		if (empty($gco_id) || empty($gco_key)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		if ($test_mode == true) {
			$url = "https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/" . $gco_id;
		} else {
			$url = "https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/" . $gco_id;
		}
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo = self::getBillingInfo($orderInfo->user_id);
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$order_id = $orderInfo->order_id;
		$order_number = $orderInfo->order_number;
		$user = JFactory::getUser($orderInfo->user_id);
		$desc = self::generateDesc($order_id);
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode($orderInfoParams->returnUrl);
		$returnUrl = $returnUrl ? JURI::base() . $returnUrl : JURI::base() . "index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$next_amount = (!empty($pConfig->google_accept_discount) && !empty($amount)) ? $amount : $orderInfoParams->next_total;
		$period = self::tranPeriod($orderInfoParams);
		if (empty($period)) {
			$html['form'] = "";
			$html['url'] = "";
			return $html;
		}
		$html['form'] = '<form action="' . $url . '" method="post" >';
		
		if ($orderInfoParams->has_trial) {
			$unitprice = 0; 
		}	
		else
		{
			$unitprice = $next_amount;
		}		
		
		$returnUrl = urldecode(JROUTE::_(JURI::base() . "index.php?option=com_osemsc&view=thankyou&order_id=" . $order_id));
		$returnUrl = $returnUrl ? $returnUrl : JURI::base() . "index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		
		$returnDomain = $this->checkProweb($order_id);
		if (!empty($returnDomain))
		{
			$returnUrl = $returnDomain.'/wp-admin/admin.php?page=ose_wp_firewall_subscription'; 
		}	
		
		$febdays = (date('Y') % 4 == 0) ? 29 : 28;
		$mdate = array(31, $febdays, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$nextPmtDate = date("y-m-d", strtotime('+' . $mdate[date('n') - 1] . ' days'));
		$post_variables = array(
					"shopping-cart.items.item-1.item-name" => $desc . ' - ' . JText::_('ORDER_ID') . ' ' . $order_id,
					"shopping-cart.items.item-1.item-description" => JText::_('UNIQUE_ORDER_NUMBER') . ':' . $order_number,
					"shopping-cart.items.item-1.unit-price.currency" => $currency, 
					"shopping-cart.items.item-1.unit-price" => $unitprice,
					"shopping-cart.items.item-1.quantity" => "1", 
					"shopping-cart.items.item-1.subscription.type" => $gco_type,
					"shopping-cart.items.item-1.subscription.period" => $period,
					"shopping-cart.items.item-1.subscription.payments.subscription-payment-1.maximum-charge" => $next_amount,
					"shopping-cart.items.item-1.subscription.payments.subscription-payment-1.maximum-charge.currency" => $currency,
					"shopping-cart.items.item-1.subscription.recurrent-item.item-name" => $desc . ' - ' . JText::_('ORDER_ID') . ' ' . $order_id,
					"shopping-cart.items.item-1.subscription.recurrent-item.item-description" => JText::_('UNIQUE_ORDER_NUMBER') . ':' . $order_number,
					"shopping-cart.items.item-1.subscription.recurrent-item.quantity" => '1', 
					"shopping-cart.items.item-1.subscription.recurrent-item.unit-price" => $next_amount,
					"shopping-cart.items.item-1.subscription.recurrent-item.unit-price.currency" => $currency,
					"shopping-cart.items.item-1.subscription.recurrent-item.digital-content.display-disposition" => 'OPTIMISTIC',
					"shopping-cart.items.item-1.subscription.recurrent-item.digital-content.url" => JURI::base(),
					"shopping-cart.items.item-1.subscription.recurrent-item.digital-content.description" => JText::_('UNIQUE_ORDER_NUMBER') . ':' . $order_number,
					"shopping-cart.items.item-1.digital-content.display-disposition" => 'PESSIMISTIC',
					"shopping-cart.items.item-1.digital-content.description" => $desc . ' - ' . JText::_('ORDER_ID') . ' ' . $order_id, 
					"continue_url" => $returnUrl
				);
		
		$html['form'] .= '<input type="image" id="gco_image" name="cartImage" src="' . "components/com_osemsc/assets/images/checkout.png" . '" alt="'. JText::_('Fast checkout through Google') . '" />';
		// Process payment variables;
		$html['url'] = $url . "?";
		foreach ($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
			$html['url'] .= $name . "=" . urlencode($value) . "&";
		}
		$html['form'] .= '</form>';
		return $html;
	}
	function generateDesc($order_id) {
		$title = null;
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = '{$order_id}'";
		$db->setQuery($query);
		$obj = $db->loadObject();
		$params = oseJson::decode($obj->params);
		$msc_id = $obj->entry_id;
		$query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = " . (int) $msc_id;
		$db->setQuery($query);
		$msc_name = $db->loadResult();
		$msc_option = $params->msc_option;
		$query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'payment' AND `id` = " . (int) $msc_id;
		$db->setQuery($query);
		$result = oseJson::decode($db->loadResult());
		foreach ($result as $key => $value) {
			if ($msc_option == $key) {
				if ($value->recurrence_mode == 'period') {
					if ($value->eternal) {
						$title = 'Life Time Membership';
					} else {
						if ($value->recurrence_unit == 'day') {
							$title = 'Daily Membership';
						} else {
							$title = ucfirst($value->recurrence_unit) . 'ly Membership';
						}
					}
				} else {
					$start_date = date("l,d F Y", strtotime($value->start_date));
					$expired_date = date("l,d F Y", strtotime($value->expired_date));
					$title = $start_date . ' - ' . $expired_date . ' Membership';
				}
			}
		}
		$title = $msc_name . ' : ' . $title;
		return $title;
	}
	function tranPeriod($orderInfoParams) {
		switch ($orderInfoParams->t3) {
		case ('day'):
			$period = ($orderInfoParams->p3 == '1') ? 'DAILY' : null;
			break;
		case ('week'):
			$period = ($orderInfoParams->p3 == '1') ? 'WEEKLY' : null;
			break;
		case ('month'):
			$period = ($orderInfoParams->p3 == '1') ? 'MONTHLY' : null;
			if (empty($period) && $orderInfoParams->p3 == '2') {
				$period = 'EVERY_TWO_MONTHS';
			} elseif (empty($period) && $orderInfoParams->p3 == '3') {
				$period = 'QUARTERLY';
			}
			break;
		case ('year'):
			$period = ($orderInfoParams->p3 == '1') ? 'YEARLY' : null;
			break;
		}
		return $period;
	}
	private function checkProweb($order_id)
	{
		$db= JFactory :: getDBO();
		$query= "SHOW TABLE STATUS LIKE '#__proweb_domains'";
		$config= new JConfig();
		$query = str_replace('#__', $config->dbprefix, $query);
		$db->setQuery($query);
		$result = $db->loadObjectlist();
		if (!empty($result))
		{
			$query = "SELECT `domain` FROM `#__proweb_domains` WHERE `orderid` = ". (int)$order_id;
			$db->setQuery($query);
			$result = $db->loadResult();
			return $result;
		}	
		else
		{
			return false;
		}	
		
	}
}
?>