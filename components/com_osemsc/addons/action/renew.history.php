<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewHistory
{
	public static function renew($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.History');
			
			return $result;
		}
		unset($params['allow_work']);
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
		$updated = $history->record($msc_id,$member_id,'renew');
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
			$result['content'] = JText::_('Error Renew.History');
		}
		
		
		return $result;
	}
	
	public static function activate($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.History');
			
			return $result;
		}
		unset($params['allow_work']);
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
		if(!$history->record($msc_id,$member_id,'activate'))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
		return $result;
	}
	

	
}
?>