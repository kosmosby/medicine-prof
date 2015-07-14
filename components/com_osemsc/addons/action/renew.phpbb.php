<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewPhpbb
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
		
		// get the group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'phpbb'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		$group_id = $data->group_id;
		
		if(empty($group_id))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
	    $query = "SELECT a.msc_id, a.member_id, c.userid from `#__osemsc_member` as a, `#__osemsc_acl` as b, `#__jfusion_users_plugin` as c WHERE c.`id` = a.`member_id` AND a.`status` = '1' AND a.`member_id` = '{$member_id}' AND a.`msc_id` = '{$msc_id}' AND a.`msc_id`=b.`id`";
        $db->setQuery($query);
        $obj = $db->loadObject();
	   
        require_once(OSEMSC_B_ADDON.DS.'action'.DS.'panel.phpbb.php');
        $phpbb = new oseMscAddonActionPanelPhpbb();
        //connect to phpbb
	    $check = $phpbb->connect_phpbb();
		if (!$check)
        {
        	$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Unable to connect to PHPBB');
			return $result;
        }
     
		if (!empty($obj))
        {
        	$query = "SELECT count(*) FROM `#__user_group` WHERE `user_id`= '{$obj->userid}' AND `group_id` = '{$group_id}'";
            $phpbb->setQuery($query);
            $result_query = $phpbb->loadResult();
            if (empty($result_query))
            {
            	
            	$query = "INSERT INTO `#__user_group` (`group_id`,`user_id`,`group_leader`, `user_pending`) VALUES ('{$group_id}', '{$obj->userid}', '0', '0');";
                $phpbb->setQuery($query);
                $phpbb->query(); 
                
                $query = "UPDATE `#__users` SET `user_permissions` = '', `user_perm_from` = 0 WHERE `user_id`= '{$obj->userid}'";
                $phpbb->setQuery($query);
                $phpbb->query();
            }
        }
       
        $result['success'] = true;
		$result['title'] = JText::_('Finished');
		$result['content'] = JText::_('Save Successfully!');       
        return $result;
		
	}
	
   
}
?>