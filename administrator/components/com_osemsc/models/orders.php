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


class oseMscModelOrders extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function

	public function getOrders($type,$start,$limit)
	{
		$db = oseDB::instance();
		$where = array();
		
		$search_order= JRequest :: getString('search_order', null);
		$search_order= JString :: strtolower($search_order);
		$search_user= JRequest :: getString('search_user', null);
		$search_user= JString :: strtolower($search_user);
		
		$filterStatus = JRequest::getString('filter_status',null);
		
		if(!empty($search_order))
		{
			$searchQuery = $db->Quote('%'.$search_order.'%');
			$where[] = "o.payment_serial_number LIKE {$searchQuery} OR o.order_id =".(int)$search_order." OR o.order_number LIKE {$searchQuery}";
		}
		
		if(!empty($search_user))
		{
			$searchQuery = $db->Quote('%'.$search_user.'%');
			$where[] = "u.username LIKE {$searchQuery} OR u.name LIKE {$searchQuery} OR u.email LIKE {$searchQuery} OR u.id=".(int)$search_user;
		}
		
		/*if (isset( $search ) && $search!= '')
		{
			$searchEscaped = $db->Quote('%'.$search.'%');
			$where[] = " u.username LIKE {$searchEscaped} OR u.name LIKE {$searchEscaped}";
		}*/

		if(!empty($filterStatus))
		{
			$where[] = "o.order_status = ".$db->Quote($filterStatus);
		}

		$where[] = "o.entry_type IN ('msc','msc_list')";
		$where[] = "o.payment_from != 'system_admin'";
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				//." ORDER BY o.create_date DESC"
				//." LIMIT {$start},{$limit} "
				;
		$db->setQuery($query);
		//oseExit($db->getQuery());
		$total = $db->loadResult();
		
		$query = " SELECT CONCAT('Order:',o.order_id) AS title, u.username,u.name, o.* "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				." ORDER BY o.create_date DESC"
				//." LIMIT {$start},{$limit} "
				;
		$db->setQuery($query,$start,$limit);
		//oseExit($db->getQuery());
		$items = oseDB::loadList();
		$return = array();
		$i=0;
		foreach ($items as $item)
		{
			$item['mscTitle'] = self::getMSCTitle($item['order_id']);
			$item['name'] = $item['user_id'].' - '. $item['name'];
			$item['title'] = $item['title'].' - '. $item['mscTitle'];
			$return[$i] = $item;
			$i++;
		}
		$result = array();
		$result['total'] = $total;//$this->getTotal();
		$result['results'] = $return;
		return $result;
	}
	private function getMSCTitle($orderID)
	{
		$db = oseDB::instance();
		$query = " SELECT acl.title FROM `#__osemsc_acl` AS acl, `#__osemsc_order_item` AS oitem " .
				 " WHERE oitem.order_id = ". (int)$orderID.
				 " AND acl.id = oitem.entry_id";
		$db->setQuery($query);
		$titles = $db->loadObjectlist();
		$return = array();
		foreach ($titles as $title)
		{
			$return[]= $title->title;
		}
		$return = implode("<br />", $return);
		return $return;
	}
	private function getTotal()
	{
		$db = oseDB::instance();

		$where = array();

		$search = JRequest::getString('search',null);

		$filterStatus = JRequest::getString('filter_status',null);

		if (isset( $search ) && $search!= '')
		{
			$searchEscaped = $db->Quote('%'.$search.'%');
			$where[] = " u.username LIKE {$searchEscaped} OR u.name LIKE {$searchEscaped}";
		}

		if(!empty($filterStatus))
		{
			$where[] = "o.order_status = ".$db->Quote($filterStatus);
		}

		$where[] = "o.entry_type IN ('msc','msc_list')";
		$where[] = "o.payment_from != 'system_admin'";
		$where = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$result = $db->loadResult();
		return $result;
	}

	public function confirmOrder($order_id)
	{
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('ADDED_SUCCESSFULLY');

		$db = oseDB::instance();

		$where = array();

		$where[] = 'order_id = '.$order_id;

		$payment = oseRegistry::call('payment');
		$order = $payment->getOrder($where,'obj');

		if($order->order_status == 'confirmed' )
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('DONE');
			return $result;
		}
		else
		{
			$paymentOrder = $payment->getInstance('Order');

			$updated = $paymentOrder->confirmOrder($order_id);

			$payment->updateOrder($order_id, 'confirmed');

			if(!$updated['success'])
			{
				$result = $updated;
			}

		    return $result;
		}
	}

	public function pendingOrder($order_id)
	{
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('PENDING_SUCCESSFULLY');

		$db = oseDB::instance();

		$where = array();

		$where[] = 'order_id = '.$order_id;

		$payment = oseRegistry::call('payment');
		$order = $payment->getOrder($where,'obj');

		if( $order->order_status == 'confirmed' )
		{
			$paymentOrder = $payment->getInstance('Order');

			$updated = $paymentOrder->refundOrder($order_id);

			$payment->updateOrder($order_id, 'pending');

			if(!$updated['success'])
			{
				$result = $updated;
			}
		}

		//oseExit('dd');
		return $result;
	}

	public function Truncate()
	{
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('TRUNCATE_SUCCESSFULLY');

		$db = oseDB::instance();
		$query = "TRUNCATE TABLE `#__osemsc_order` ";
		$db->setQuery($query);
		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		$query = "TRUNCATE TABLE `#__osemsc_order_item` ";
		$db->setQuery($query);
		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		return $result;
	}
	
	function getAllOptions()
	{
		$list = oseMscPublic::getList();

		$options = array();

		$msc = oseRegistry::call('msc');
		foreach($list as $key => $entry)
		{
			$msc_id = oseObject::getValue($entry,'id',0);

			$node = $msc->getInfo($msc_id,'obj');
			$paymentInfos = $msc->getExtInfo($msc_id,'payment');

			$cart = oseMscPublic::getCart();

	    	$osePaymentCurrency = $cart->get('currency');

	    	$items = $cart->get('items');

			$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			$options = array_merge($options,$option);
		}

		$combo = array();
    	$combo['total'] = count($options);
    	$combo['results'] = $options;
    	return $combo;
	}
	
	function getUsers()
	{
		$db = oseDB::instance();
		$member = oseRegistry::call('member');
		$post = JRequest::get('post');
		$where= array();

		$search = $post['search'];

		if($search)
		{
			$searchQuery = $db->Quote('%'.$search.'%');
			$where[] = "u.username LIKE {$searchQuery} OR u.name LIKE {$searchQuery} OR u.email LIKE {$searchQuery}";
		}

		$where = count($where>0)?oseDB::implodeWhere($where):null;
		$start = $post['start'];
        $limit = $post['limit'];

		$result['results'] = oseMemGroup::getUsers($where,$start,$limit);
		//oseExit($db->_sql);
		$result['total'] = oseMemGroup::getUsersTotal($where);
		return $result;
	}
	
	function createOrder()
	{
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('DONE');
		
		$post = JRequest::get('post');
		$msc_id = $post['msc_id'];
		$msc_option = $post['msc_option'];
		$user_id = $post['user_id'];
		$cart = oseMscPublic::getCart();
   		// get current item
  		$cCart = $cart->cart;
  		$cart->init();
   		$cart->__construct();
   		$cart->updateParams('payment_mode','m');
       	$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($msc_id,$cart->get('currency'),0);
       	$nItem = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option);
		$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);
		$cart->update();
   		oseMscAddon::runAction('register.payment.save',array('member_id'=>$user_id,'payment_method'=>'none'), true, false);
   		return $result;
	}
}


?>