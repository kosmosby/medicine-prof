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
// This API is used by users who would like to import members into OSE Membership
// Version beta 1; 
defined('_JEXEC') or die("Direct Access Not Allowed");
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
define ('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
require_once(JPATH_BASE.'/includes/defines.php');
require_once(JPATH_SITE.DS.'configuration.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
require_once(JPATH_BASE.DS.'includes'.DS.'helper.php');
require_once(JPATH_BASE.DS.'includes'.DS.'toolbar.php');
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ose_cpu' . DS . 'define.php');
require_once(OSECPU_B_PATH . DS . 'oseregistry' . DS . 'oseregistry.php');
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'define.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'oseMscPublic.php');
	
class osemscAPI {
	private $msc = null; 
	public function __construct()
	{
		oseRegistry::register('registry', 'oseregistry');
		oseRegistry::call('registry');
		oseRegistry::register('msc', 'membership');
		$this->msc = oseRegistry::call('msc');
		oseRegistry::register('user', 'user');
		oseRegistry::quickRequire('user');
		oseRegistry::register('member', 'member');
		oseRegistry::call('member');
		oseRegistry::register('payment', 'payment');
		oseRegistry::quickRequire('payment');
		JFactory::getApplication('administrator');
	}
	public function createPlan($title)
	{
		$msc_id = $this->msc->create();
		if(!empty($msc_id))
		{
			$var['id'] = $msc_id;
			$var['title'] = $title;
			$updated = $this->msc->update($var);
			return $msc_id;
		}
		else
		{
			return false;
		}
	}
	public function createOrder($payment_mode, $payment_method, $msc_id, $msc_option, $user_id)
	{
		$nItem = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option);
		$cart = $this->getCart($payment_mode, $nItem);
       	$result = oseMscAddon::runAction('register.payment.save',array('member_id'=>$user_id,'payment_method'=>$payment_method), true, false);
       	return $result['order_id'];  
	}
	private function getCart($payment_mode, $nItem)
	{
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		$cart->updateParams('payment_mode',$payment_mode);
		$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);
		$cart->update();
		return $cart;
	}
	public function updateSerial($order_id, $profile_id)
	{
		$db= JFactory::getDBO();
		$query = " UPDATE `#__osemsc_order` SET `payment_serial_number` = ". $db->Quote($profile_id, true)
				." WHERE `order_id` = ". (int)$order_id;
		$db->setQuery($query);
		return $db->query();
	}
	public function updatePaymentMethod($order_id, $method)
	{
		$db= JFactory::getDBO();
		$query = " UPDATE `#__osemsc_order` SET `payment_method` = ". $db->Quote($method, true)
				." WHERE `order_id` = ". (int)$order_id;
		$db->setQuery($query);
		return $db->query();
	}
	public function confirmOrder($order_id)
	{
		$db = JFactory::getDBO();
		$where = array();
		$where[] = 'order_id = '.$order_id;
		$payment = oseRegistry::call('payment');
		$order = $payment->getOrder($where,'obj');
		if($order->order_status != 'confirmed' )
		{
			$paymentOrder = $payment->getInstance('Order');
			$updated = $paymentOrder->confirmOrder($order_id);
			$payment->updateOrder($order_id, 'confirmed');
			return $updated['success'];
		}
		else
		{
			return true;
		}		
	}
}
