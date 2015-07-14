<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberCompany extends oseMscAddon
{
	public static function getItem($params = array())
	{
    	$member_id = JRequest::getInt('member_id',null);
    	
    	$db = oseDB::instance();
    	
    	$query = " SELECT company.* FROM `#__oselic_cs_company` AS company "
				." INNER JOIN  `#__oselic_cs_keys_view` AS lic_view ON lic_view.company_id = company.company_id"
    			." WHERE lic_view.user_id = {$member_id}";
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$info = oseDB::loadList();
		//$member = oseRegistry::call('member');
		
		//$member->instance($member_id);
		//$info = $member->getBillingInfo();
		
		
		if(empty($info))
		{
			$item = array();
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $info;
		}
		
		return $result;
	}
	
	public static function save()
    {
    	$post = JRequest::get('post');
    	
    	$member_id = JRequest::getInt('member_id',0);
    	
    	$company_id = JRequest::getInt('company_id',0);
    	unset($post['company_id']);
    	
		$company = array();
		
		foreach($post as $key => $value)
		{
			if(strstr($key,'company_'))
			{
				$billKey = preg_replace('/company_/','',$key,1); 
				$company[$billKey] = $value;
			}
		}
		//oseExit($company);
    	$db = oseDB::instance();
    	    	
    	if (empty ($company_id)) {
    		$keys = array_keys($company);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__oselic_cs_company');
    		
    		foreach($company as $key => $value)
    		{
				if(!isset($fields['#__oselic_cs_company'][$key]))
				{
					if($my->get('gid') == 24 || $my->get('gid') == 25)
					{
						$query = "ALTER TABLE `#__oselic_cs_company` ADD `{$key}` TEXT NULL DEFAULT NULL";
						$db->setQuery($query);
						if (!oseDB::query())
						{
							$result['success'] = false;
							$result['title'] = JText::_('Error');
							$result['content'] = JText::_('Fail Saving Joomla User Info.');
						}
					}
					else
					{
						unset($company[$key]);
					}
				}
    		}  
    		
    		foreach($company as $key => $value)
    		{
    			$company[$key] = $db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
			$query = "INSERT INTO `#__oselic_cs_company` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
		} 
		else 
		{
			
			foreach($company as $key => $value)
    		{
    			$company[$key] = "`{$key}`=".$db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
			$query = " UPDATE `#__oselic_cs_company` SET {$values}" 
					." WHERE `company_id` ={$company_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);
		
		if (!oseDB::query()) {
			$result = array();
			
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company Info.');
			
		} else {
			$list = array();//oseMscAddon::getAddonList('usersync',true,null,'obj');
			$params = array();
			$params['member_id'] = $member_id;
			$params['allow_work'] = true;
			foreach($list as $addon)
			{
				$action_name = 'usersync.'.$addon->name.'.compnaySave';
				
				$result = oseMscAddon::runAction($action_name,$params);
				
				if(!$result['success'])
				{
					return $result;
				}
			}
			
			$result = array();
			
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Saved Company Info.');
			
		}
    	return $result; 
    	
    }

    public static function licSave($params = array())
    {
    	$post = $params;
    	
    	$my = JFactory::getUser();
    	
    	$member_id = $post['member_id'];
		
		$company = array();
		
		foreach($post as $key => $value)
		{
			if(strstr($key,'bill_'))
			{
				$billKey = str_replace('bill_','',$key); 
				$company[$billKey] = $value;
			}
		}
		//oseExit($company);
    	$db = oseDB::instance();
    	$member_id = JRequest::getInt('member_id',0);
    	$query = " SELECT count(*) FROM `#__osemsc_billinginfo` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();
    	    	
    	if (empty ($num)) {
    		$keys = array_keys($company);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_billinginfo');
    		
    		foreach($company as $key => $value)
    		{
				if(!isset($fields['#__osemsc_billinginfo'][$key]))
				{
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
				}
    		}  
    		
    		foreach($company as $key => $value)
    		{
    			$company[$key] = $db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
			$query = "INSERT INTO `#__osemsc_billinginfo` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
		} else {
			
			foreach($company as $key => $value)
    		{
    			$company[$key] = "`{$key}`=".$db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
			$query = " UPDATE `#__osemsc_billinginfo` SET {$values}" 
					." WHERE `user_id` ={$member_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);
		
    	return oseDB::query(); 
    	
    }
    
}
?>