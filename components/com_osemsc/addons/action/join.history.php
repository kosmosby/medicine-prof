<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinHistory
{
	public static function save($params)
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
		
		
		
		//oseExit($params);
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join History: No Membership ");
		}
		
		$history = oseRegistry::call('member')->getInstance('History');
		$updated = $history->record($msc_id,$member_id,'join');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		// Order problem for system add
		$hisParams = oseJSON::encode(array('order_id' => $params['order_id']));
		
		$query = " UPDATE `#__osemsc_member_history`"
				." SET params = ".$db->Quote($hisParams)
				." WHERE id = {$updated}"
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
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		
		if(!$params['master'])
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_("Done");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$history = oseRegistry::call('member')->getInstance('History');
		$updated = $history->record($msc_id,$member_id,'expired');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
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
		
		if(!$params['master'])
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_("Done");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$history = oseRegistry::call('member')->getInstance('History');
		$updated = $history->record($msc_id,$member_id,'remove');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
		
	}
	
	public function manualCancel($params)
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
		
		if(!$params['master'])
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_("Done");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$history = oseRegistry::call('member')->getInstance('History');
		$updated = $history->record($msc_id,$member_id,'cancelled');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
	}
	
	public function manualCancelOrder($params)
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
		
		if(!$params['master'])
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_("Done");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$history = oseRegistry::call('member')->getInstance('History');
		$updated = $history->record($msc_id,$member_id,'cancelOrder');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
	}
}
?>