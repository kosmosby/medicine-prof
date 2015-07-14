<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinJshopping
{
	public static function save($params)
	{
		$result = array();
		
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
		//$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		// get the jshopping shopper group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'jshopping'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(!empty($data->ug_id))
	    {
	    	$ug_id = $data->ug_id;
	    }else
        {
           	$query = "SELECT usergroup_id FROM `#__jshopping_usergroups` WHERE `usergroup_is_default` = '1'";
           	$db->setQuery($query);
        	$ug_id = $db->loadResult();
        }
	    
		$query = "SELECT count(*) FROM `#__jshopping_users` WHERE `user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $result = $db->loadResult();
        
        $query = "SELECT * FROM `#__osemsc_billinginfo` WHERE `user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $billInfo = $db->loadObject();
        $query = "SELECT country_id FROM `#__jshopping_countries` WHERE `country_code` = '{$billInfo->country}'";
        $db->setQuery($query);
        $country = $db->loadResult();
        $street = $billInfo->addr1.' '.$billInfo->addr2;
        $billInfo = self::QuoteData($billInfo);
        
        if (empty($result))
        {
        	$user = JFactory::getUser($member_id);
			$email = $user->email;
			$query = " INSERT INTO `#__jshopping_users`" 
					." (`user_id`,`usergroup_id`,`u_name`,`f_name`,`l_name`,`firma_name`,`email`,`street`,`zip`,`city`,`state`,`country`,`phone`)" 
					." VALUES" 
					." ('{$member_id}', '{$ug_id}', '{$user->username}', {$billInfo->firstname}, {$billInfo->lastname}, '{$user->name}', '{$email}', '{$street}', {$billInfo->postcode}, {$billInfo->city}, {$billInfo->state}, '{$country}', {$billInfo->telephone});";           
 
        }else
        {
             $query ="UPDATE `#__jshopping_users` SET `usergroup_id` = '{$ug_id}' WHERE `user_id` = ".(int)$member_id; 
        }
        $db->setQuery($query);
        if (!$db->query())
        {
           	$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join JoomShopping User Info Error.");
			return $result;
        } 
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
			
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

		$db = oseDB::instance();
		$msc_id =$params['msc_id'];
		$member_id = $params['member_id'];

		// get the jshopping shopper group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'jshopping'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		$query = "SELECT usergroup_id FROM `#__jshopping_users` WHERE `user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $gid = $db ->loadResult();
        
        if($data->ug_id != $gid)
        {
        	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
        }
        
        $query = "SELECT * FROM `#__osemsc_member` WHERE `member_id` = '{$member_id}' AND `status` = '1' ORDER BY `id` DESC";
        $db->setQuery($query);
        $Mems = $db->loadObjectList();
        $ug_id = null;
        if(!empty($Mems))
        {
        	$own_msc_id = $Mems[0]->msc_id;
        	
        	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$own_msc_id}' AND `type` = 'jshopping'";
	        $db->setQuery($query);
	        $ext = $db->loadObject();
			$ext = oseJson::decode($ext->params);
			$ug_id = $ext->ug_id;
        }
        if(empty($ug_id))
        {
        	$query = "SELECT usergroup_id FROM `#__jshopping_usergroups` WHERE `usergroup_is_default` = '1'";
       	 	$db->setQuery($query);
        	$ug_id = $db->loadResult();
        }
		$query = "UPDATE `#__jshopping_users` SET `usergroup_id` =".(int)$ug_id." WHERE `user_id` =".(int)$member_id;
	    $db->setQuery($query);
	    if (!$db->query())
	    {
		    $result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
	    }
			
		return $result;
		
	}
	
	public static function QuoteData($data)
	{
		$db = JFactory::getDBO();
		foreach($data as $key => $value)
		{
			$data->$key = $db->Quote($value);
		}
		return $data;
	}

}
?>