<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionUsersyncVm extends oseMscAddon
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
    			
    	$query = " UPDATE `#__vm_user_info` "
				." SET `first_name` = '{$post['firstname']}', `last_name` = '{$post['lastname']}', `user_email` = '{$post['email']}' "
				." WHERE `user_id` = '{$member_id}' AND `address_type` = 'BT' "
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
    	
		//get vm country code
		$query = " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$company['country']}'";
    	$db->setQuery($query);
    	$country_code = $db->loadResult();
    	$company['country']=empty($country_code)?$company['country']:$country_code;
    	
    	//get vm state code
    	$query = " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$company['state']}'";
    	$db->setQuery($query);
    	$state_code = $db->loadResult();
    	$company['state']=empty($state_code)?$company['state']:$state_code;
		
    	$query = " UPDATE `#__vm_user_info` "
				." SET `company` = '{$company['company']}', `phone_1` = '{$company['telephone']}', `address_1` = '{$company['addr1']}', `address_2` = '{$company['addr2']}', `city` = '{$company['city']}', `state` = '{$company['state']}', `country` = '{$company['country']}', `zip` = '{$company['postcode']}' "
				." WHERE `user_id` = '{$member_id}' AND `address_type` = 'BT' "
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