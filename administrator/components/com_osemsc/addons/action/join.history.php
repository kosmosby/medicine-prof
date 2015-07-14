<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinHistory extends oseMscAddon
{
	public static function save($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Msc');
			
			return $result;
		}
		unset($params['allow_work']);
		
		if( $params['join_from'] != 'payment' )
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$result = array();
		$result['success'] = true;
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" History Error ");
			return $result;
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
			$result['content'] = JText::_('Error Join.Msc');
			
			return $result;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$result = array();
		$result['success'] = true;
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" History Error ");
			return $result;
		}
		
		$history = oseRegistry::call('member')->getInstance('History');
		if(!$history->record($msc_id,$member_id,'cancel'))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
	}
	
	public static function delete($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Msc');
			
			return $result;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$result = array();
		$result['success'] = true;
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" History Error ");
			return $result;
		}
		
		$history = oseRegistry::call('member')->getInstance('History');
		if(!$history->record($msc_id,$member_id,'delete'))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
	}
	
	public static function kick($params)
	{
		if(empty($params['allow_work']))
		{
			return false;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		if(empty($msc_id))
		{
			return false;
		}
		
		if(!oseMemHistory::record($msc_id,$member_id,'cancel'))
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return true;
	}
	
	public static function blacklist($params)
	{
		if(empty($params['allow_work']))
		{
			return false;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		if(empty($msc_id))
		{
			return false;
		}
		
		if(!oseMemHistory::record($msc_id,$member_id,'cancel'))
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return true;
	}
}
?>