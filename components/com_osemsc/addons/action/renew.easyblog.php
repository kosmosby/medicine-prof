<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewEasyblog
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
		
		// get the easyblog acl of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'easyblog'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		$query = " SELECT * FROM `#__easyblog_acl_group` AS g"
				." INNER JOIN `#__easyblog_acl` AS a"
				." ON g.`acl_id` = a.`id`"
				." WHERE g.`content_id` = '{$member_id}' AND g.`type` = 'assigned'";
		$db->setQuery($query);
        $acls = $db->loadObjectList();		
		    
        if(empty($acls))
        {
        	foreach($data as $key =>$value)
        	{
        		if($key != 'enable')
        		{
        			$query = "SELECT id FROM `#__easyblog_acl` WHERE `action` = '{$key}'";
        			$db->setQuery($query);
        			$acl_id = $db->loadResult();
        			if(!empty($acl_id))
        			{
        				$query = " INSERT INTO `#__easyblog_acl_group` (`content_id`, `acl_id`, `status`, `type`)"
        						." VALUES"
        						." ('{$member_id}', '{$acl_id}', '{$value}', 'assigned')";
        			 	$db->setQuery($query);
					    if (!$db->query())
					    {
						    $result['success'] = false;
							$result['title'] = 'Error';
							$result['content'] = JText::_('Error');
							return $result;
						}		
        			}
        		}
        	}	
        }else{
        	foreach($acls as $acl)
        	{
        		$action = $acl->action;
        		if(!empty($data->$action) && empty($acl->status))
        		{
        			$query = "UPDATE `#__easyblog_acl_group` SET `status` = '{$data->$action}' WHERE `content_id` ='{$member_id}' AND `acl_id` = '{$acl->acl_id}' AND `type` = 'assigned'";
        			$db->setQuery($query);
					if (!$db->query())
					{
						$result['success'] = false;
						$result['title'] = 'Error';
						$result['content'] = JText::_('Error');
						return $result;
					}
        		}
        	}
        }
        
		return $result;
	}
	
	public static function activate($params)
	{
		return self::renew($params);
	}
}
?>