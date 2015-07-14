<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionUsersyncPhpbb extends oseMscAddon
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
    	    	
    	//JRequest::setVar('member_id',$member_id);
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	$member_id = $params['member_id'];
    	$query=" SELECT username FROM `#__users` WHERE `id` = '{$member_id}'";
        //$db->setQuery($query);
    	$username=$phpbb->loadResult();
    	
    	$phpbb_class = dirname(__FILE__).DS.'phpbb.php';
        require_once ($phpbb_class);
        $phpbb = new phpbbdb();
        $check = $phpbb->connect_phpbb();
        if ($check == false)
        {
			$result = array();
			
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Connecting PHPBB.');
			return $result;
        }
        /************************************************************************************/
       	
        
        $phpbb_query = "SELECT user_id FROM `#__users` WHERE `username` = '{$username}' ";
	    $phpbb->setQuery($phpbb_query);
	    //$db->setQuery($phpbb->query);
	    $user_id = $phpbb->loadResult();
	    
	    $query = " UPDATE `#__profile_fields_data` "
				." SET `pf_first_name` = '{$post['firstname']}', `pf_last_name` = '{$post['lastname']}', `pf_email` = '{$post['email']}' "
				." WHERE `user_id` = '{$user_id}' "
				;
		$phpbb->setQuery($query);
		$phpbb->query();
  	
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
    	
    	
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	
    	
    	$query=" SELECT username FROM `#__users` WHERE `id` = '{$member_id}'";
    	$db->setQuery($query);
    	$username=$db->loadResult();
    	
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
    	
    	$phpbb_class = dirname(__FILE__).DS.'phpbb.php';
        require_once ($phpbb_class);
        $phpbb = new phpbbdb();
        $check = $phpbb->connect_phpbb();
        if ($check == false)
        {
			$result = array();
			
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Connecting PHPBB.');
			return $result;
        }
        /************************************************************************************/
       	
        
	    $phpbb_query = "SELECT user_id FROM `#__users` WHERE `username` = '{$username}' ";
	    $phpbb->setQuery($phpbb_query);
	    //$db->setQuery($phpbb->query);
	    $user_id = $phpbb->loadResult();
	    
    	$query = " UPDATE `#__profile_fields_data` "
				." SET `pf_company` = '{$company['company']}', `pf_address1` = '{$company['addr1']}', `pf_address2` = '{$company['addr2']}', `pf_city` = '{$company['city']}', `pf_state` = '{$company['state']}', `pf_country` = '{$company['country']}', `pf_zip` = '{$company['postcode']}', `pf_phone` = '{$company['telephone']}' "
				." WHERE `user_id` = '{$user_id}'"
				;
		$phpbb->setQuery($query);
		$phpbb->query();
  	
		$result = array();
			
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Saved Company Info.');
		return $result;
    	
    }    

}    