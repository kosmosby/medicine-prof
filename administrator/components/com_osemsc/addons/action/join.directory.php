<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinDirectory extends oseMscAddon
{
	public static function save($params)
	{
	
		$jdate = JFactory::getDate();
        $now = $jdate->toMySQL();
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
		
		$post = JRequest::get('post');
		
		$member_id = $params['member_id'];
		
		$db = oseDB::instance();
		$user = JFactory::getUser();
		
		$query = " SELECT * FROM `#__oselic_cs_keys_view`" 
    			." WHERE user_id = {$member_id}"
    			;
    	$db->setQuery($query);
    	
    	$item = oseDB::loadItem('obj'); 
    	$company_id = $item->company_id;

		//oseExit($company);
    	
        $query = " SELECT eternal, expired_date FROM `#__osemsc_member`"
    		." WHERE member_id = {$member_id}"
    		;
    	$db->setQuery($query);
    	$obj = $db->loadObject();
    	
    	$publish_down = empty($obj->eternal)?$obj->expired_date:'0000-00-00 00:00:00';
    	
    	$query = " SELECT directory_id FROM `#__osemsc_directory`"
    			." WHERE company_id = {$company_id}"
    			;
    	$db->setQuery($query);
    	$directory_id = $db->loadResult();
    	
    	if (empty ($directory_id)) 
    	{
    		$query = " INSERT INTO `#__mt_links` (user_id,link_published,link_approved,link_created,publish_down) VALUES ('{$member_id}','1','1','{$now}', '$publish_down')";
    		$db->setQuery($query);
    		
    		if(!oseDB::query())
    		{
    			$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Error");
				return $result;
    		}
    		
    		$newMtLinkID = $db->insertid();
    		
    		//Insert the image
                $query = " SELECT cf_id FROM `#__mt_customfields`"
                      ." WHERE caption = 'Image' AND field_type = 'image'";
            	$db->setQuery($query);
            	$cf_id = $db->loadResult();
            
            
           	 $query = " INSERT INTO `#__mt_cfvalues` (cf_id, link_id) VALUES ('{$cf_id}', '{$newMtLinkID}')";
           	 $db->setQuery($query);
            
           	 if(!oseDB::query())
           	 {
             	  	 $result['success'] = false;
              	  	 $result['title'] = 'Error';
               		 $result['content'] = JText::_("Error");
               		 return $result;
           	 }
            
           	 $query = " INSERT INTO `#__mt_cfvalues_att` (cf_id, link_id) VALUES ('{$cf_id}', '{$newMtLinkID}')";
          	  $db->setQuery($query);
            
          	  if(!oseDB::query())
           	 {
             		$result['success'] = false;
                	$result['title'] = 'Error';
                	$result['content'] = JText::_("Error");
                	return $result;
           	 }
            
    		
    		$query = " INSERT INTO `#__osemsc_directory` (company_id) VALUES ({$company_id})";
    		$db->setQuery($query);
    		
    		if(!oseDB::query())
    		{
    			$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Error");
				return $result;
    		}
    		
    		$newDirID = $db->insertid();
    		
    		$query = " INSERT INTO `#__osemsc_mtrel` (link_id,directory_id) "
    				." VALUES ('{$newMtLinkID}','{$newDirID}')"
    				;
    		$db->setQuery($query);
    		
    		if(!oseDB::query())
    		{
    			$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Error");
				return $result;
    		}
    		
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Directory.');
		} 
		else 
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Directory.');
			
		}
		//echo $query;exit;
		
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
		
		$result = array();
		$result['success'] = true;
		
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
	
	function delete($params = array())
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
}
?>