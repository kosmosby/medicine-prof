<?php
defined('_JEXEC') or die(";)");
					  
class oseMscAddonActionUsersyncJomsocial extends oseMscAddon
{	 
	
	public static function juserSave($params)
    {
    	if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		
		unset($params['allow_work']);
   		$db = oseDB::instance();
    	$post = JRequest::get('post');
    	
    	$member_id = $params['member_id'];
    	
    	//JRequest::setVar('member_id',$member_id);
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	
    			
	   	$query = "SELECT id,name FROM `#__community_fields` ";
	   	$db->setQuery($query);
	   	$objs=$db->loadObjectList('name');
	   	
    	$query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$post['firstname']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['First name']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving User Type.');
			
			return $result; 
		}
    	
    	$query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$post['lastname']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Last name']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving User Type.');
			
			return $result; 
		}
		
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$post['email']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Email']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving User Type.');
			
			return $result; 
		}
		return true;
    }
    
    
    
	public static function companySave($params)
    {
    	if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		
		unset($params['allow_work']);
		
   		$db = oseDB::instance();
    	$post = JRequest::get('post');
    	
    	$member_id = $params['member_id'];
    	
    	//JRequest::setVar('member_id',$member_id);
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	
    	$company = array();
		
		foreach($post as $key => $value)
		{
			if(strstr($key,'company_'))
			{
				$billKey = preg_replace('/company_/','',$key,1); 
				$company[$billKey] = $value;
			}
		}
		
		//get the country name
    	$query = " SELECT country_name FROM `#__osemsc_country` WHERE `country_2_code` = '{$company['country']}'";
    	$db->setQuery($query);
    	$country_name = $db->loadResult();
    	$company['country']=empty($country_name)?$company['country']:$country_name;
		//print_r($company);exit;
		
		$query = "SELECT id,name FROM `#__community_fields` ";
	   	$db->setQuery($query);
	   	$objs=$db->loadObjectList('name');
	   	
    	$query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['company']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Company']->id}' "
				;
		$db->setQuery($query);
		
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
    	
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['addr1']} {$company['addr2']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Address']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['city']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['City / Town']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['state']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['State']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['country']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Country']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
        $query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['postcode']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Zip']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
    	$query = " UPDATE `#__community_fields_values` "
				." SET `value` = '{$company['telephone']}' "
				." WHERE `user_id` = '{$member_id}' AND `field_id` = '{$objs['Land phone']->id}' "
				;
		$db->setQuery($query);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company info.');
			
			return $result; 
		}
		
		$result = array();
			
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Saved Company Info.');
		return $result;
		
    }    

}    