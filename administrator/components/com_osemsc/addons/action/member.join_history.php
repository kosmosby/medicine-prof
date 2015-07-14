<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberJoin_History
{

	public static function getJoinHistory()
	{
		$member_id = JRequest::getInt('member_id',0);
		$msc_id = JRequest :: getInt('msc_id', '0');
		$result = array();
		$db= oseDB :: instance();
		$query= " SELECT * FROM `#__osemsc_member_history` WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$member_id}'";
		$db->setQuery($query);
		$items = oseDB :: loadList();
		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}	
		$result = oseJson :: encode($result);
		oseExit($result);
	}

	public static function getMSCs()
	{
		$member_id = JRequest::getInt('member_id',0);
		$result = array();
		$db= oseDB :: instance();
		$query= " SELECT acl.id, acl.title FROM `#__osemsc_member_history` AS omh" 
			   ." INNER JOIN `#__osemsc_acl` AS acl" 
			   ." ON omh.`msc_id` = acl.`id`" 
			   ." WHERE omh.`member_id` = '{$member_id}'" 
			   ." GROUP BY acl.`id`";
		$db->setQuery($query);
		$items = oseDB :: loadList();
		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}	
		$result = oseJson :: encode($result);
		oseExit($result);
	}
}
?>