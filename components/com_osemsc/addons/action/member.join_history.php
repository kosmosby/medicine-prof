<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberJoin_History
{

	public static function getJoinHistory()
	{
		$db= oseDB :: instance();
		$my = JFactory::getUser();
		$member_id = $my->id;
		
		$msc_id = JRequest :: getInt('msc_id', '0');
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		
		$query = " SELECT COUNT(*) FROM `#__osemsc_member_history` "
				." WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$member_id}'"
				;
		$db->setQuery($query);
		$total = $db->loadResult();
		
		$query = " SELECT * FROM `#__osemsc_member_history` "
				." WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$member_id}'"
				." ORDER BY id DESC"
				;
		$db->setQuery($query,$start,$limit);
		$items = oseDB :: loadList();
		foreach($items as $key => $item)
		{
			$globalConfig = oseRegistry::call('msc')->getConfig('global','obj');
			if(!empty($globalConfig->DateFormat))
			{
				$items[$key]= oseObject::setValue($item,'date',date($globalConfig->DateFormat,strtotime($item['date'])));
			}
		}
		$result = array();
		
		$result['total'] = $total;
		$result['results'] = $items;	
		
		$result = oseJson :: encode($result);
		oseExit($result);
	}

	public static function getMSCs()
	{
		$my = JFactory::getUser();
		$member_id = $my->id;
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