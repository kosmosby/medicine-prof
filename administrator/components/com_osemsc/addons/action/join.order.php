<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinOrder extends oseMscAddon
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
		
		$where = array();
		$where[] = "order_id = {$order_id}";
		
		$payment = oseRegistry::call('payment');
		$curOrder = $payment->getOrder($where,'obj');
	
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$memParams = $member->getMemberInfo($msc_id,'obj')->memParams;
		$memParams = oseJSON::decode($memParams);
		$memParams->order_id = $order_id;
		$memParams->payment_mode = $curOrder->payment_mode;
		
		
		// Order problem for system add
		$memParams = oseJSON::encode($memParams);
		
		$query = " UPDATE `#__osemsc_member`"
				." SET params = ".$db->Quote($memParams)
				." WHERE msc_id = {$msc_id} AND member_id = {$member_id}"
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.2');
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
		
		
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
		
		return $result;
	}
	
	
	function delete($params = array())
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