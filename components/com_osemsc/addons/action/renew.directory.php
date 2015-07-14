<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewDirectory extends oseMscAddon
{
	public static function renew($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.Order');
			
			return $result;
		}
		unset($params['allow_work']);
		
		if($params['join_from'] != 'payment')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Success");
			return $result;
		}
		
		$db = oseDB::instance();
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$query = " SELECT mm.link_id "
				." FROM `#__osemsc_mtrel` AS mm"
				." INNER JOIN `#__osemsc_directory` AS directory ON directory.directory_id = mm.directory_id"
    			." INNER JOIN `#__oselic_cs_company` AS lcc ON lcc.company_id = directory.company_id"
    			." WHERE lcc.user_id = {$member_id}";
    			;
		$db->setQuery($query);
		
		$link_id = $db->loadResult();
		//oseExit($db->_sql);
		if(empty($link_id))
		{
			return $result;	
		}
		
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$memInfo = $member->getMemberInfo($msc_id,'obj');
		
		$query = " UPDATE `#__mt_links`"
				." SET `publish_down` = ".$db->Quote($memInfo->expired_date)
				." WHERE link_id = {$link_id}"
				;
			
		$db->setQuery($query);
		
		if(oseDB::query())
		{
			return $result;
		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("cancel.directory");
			return $result;
		}
	}
	
	public static function activate($params)
	{
		return self::renew($params);
	}
	

	
}
?>