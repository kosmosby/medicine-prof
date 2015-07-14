<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewK2group
{
	public static function renew($params)
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
			$result['content'] = JText::_("Renew Msc: No Msc ID");
			return $result;
		}
		
		// get the k2 group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'k2group'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->group_id) || empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		$query = "SELECT count(*) FROM `#__k2_users` WHERE `userID` = ".(int)$member_id;
		$db->setQuery($query);
		$exists = $db->loadResult();
		if(empty($exists))
		{
			$query = "SELECT name FROM `#__users` WHERE `id` = ".(int)$member_id;
			$db->setQuery($query);
			$name = $db->loadResult();
			
			$query = "INSERT INTO `#__k2_users` (`userID`, `userName`, `group` ) VALUES ('{$member_id}', '{$name}', '{$data->group_id}')";
		}else{
			$query = "UPDATE `#__k2_users` SET `group` = '{$data->group_id}' WHERE `userID` = ".(int)$member_id;
		}

		$db->setQuery($query);
		if (!$db->query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
	
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
			
		return $result;
		
	}
	
	public static function activate($params)
	{
		return self::renew($params);
	}
	
	
}
?>