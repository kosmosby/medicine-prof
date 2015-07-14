<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinCoupon
{
	public static function save($params)
	{
		$result = array();
		$result['success'] = true;
		//oseExit($params);
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.1');
			
			return $result;
		}
		unset($params['allow_work']);
		
		if( $params['join_from'] != 'payment')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done Join.Order');
			
			return $result;
		}
		
		$db = oseDB::instance();
		//$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];
		
		$payment= oseRegistry :: call('payment');
		$orderInfo= $payment->getInstance('Order')->getOrder(array("order_id = {$order_id}"), 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		
		$array = array();
		$array['id'] = $orderInfoParams->coupon_user_id;
		
		if(!empty($array['id']))
		{
			$array['paid'] = 1;
			$array['user_id'] = $member_id;
			$updated = oseDB::update('#__osemsc_coupon_user','id',$array);
		}
		
		return $result;
	}
	
	public static function cancel($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error Join.Order');
			
			return $result;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
		
		return $result;
	}
	
	
	public function remove($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		return $result;
		
	}
	
}
?>