<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterCompany extends oseMscAddon
{
	
	public static function save( $params )
    {
    	$result = array();
    	
    	$db = oseDB::instance();
    	
    	if(!oseMscPublic::isTable('#__oselic_cs_company'))
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		
    	$post = JRequest::get('post');
    	
    	$msc_id = $params['msc_id'];
    	$member_id = $params['member_id'];
    	
    	JRequest::setVar('member_id',$member_id);
    	
    	$result = array();
    	
    	if(empty($member_id))
    	{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');
			
			return $result;
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
		//oseExit($company);
   
    	
    	$query = " SELECT company_id FROM `#__oselic_cs_company` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();
    	    	
    	if (empty ($num)) {
    		$keys = array_keys($company);
    		$keys = implode('`,`',$keys);
    		$fields = $db->getTableFields('#__oselic_cs_company');
    		
    		foreach($company as $key => $value)
    		{
				if(!isset($fields['#__oselic_cs_company'][$key]))
				{
					/*
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
					*/
					unset($company[$key]);
				}
    		}  
    		
    		foreach($company as $key => $value)
    		{
    			$company[$key] = $db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
    		$queryKey = empty($keys)?'':",`{$keys}`";	
    		$queryValue = empty($values)?'':",{$values}";	
    		
			$query = "INSERT INTO `#__oselic_cs_company` (`user_id` {$queryKey}) VALUES ('{$member_id}' {$queryValue});";
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
		
		if (!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Company Info.');
			
		} 
		else 
		{
			$licExtInfo =  oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj');
			$lic_id = $licExtInfo->lic_id;
			
			if(!empty($licExtInfo->enable_cs))
			{
				$lic = oseRegistry::call('lic')->getInstance($lic_id);
				
				$licInfo = $lic->getLicenseInfo($lic_id,'obj');
				
				if($licInfo->cs_mode == 'institution')
				{
					$query = " UPDATE `#__osemsc_billinginfo`"
							." SET company = ". $db->Quote($post['company_company'])
							." WHERE user_id = {$member_id}"
							;
					$db->setQuery($query);
				
					if(!oseDB::query())
					{
						$result['success'] = false;
						$result['title'] = 'Error';
						$result['content'] = JText::_('Fail Saving Company Info.');
						return $result;
					}
				}
			}
				
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Saved Company Info.');
			
		}
    	return $result; 
    	
    }

    
}
?>