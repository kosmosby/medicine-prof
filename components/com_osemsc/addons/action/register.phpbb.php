<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterPhpbb extends oseMscAddon
{
	
	public static function save( $params )
    {
    	
    	$db = JFactory::getDBO();
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
    	
		//oseExit($company);
    	
    	$phpbb_class = dirname(__FILE__).DS.'phpbb.php';
        require_once ($phpbb_class);
        $phpbb = new phpbbdb();
        $check = $phpbb->connect_phpbb();
        if ($check == false)
        {
			$result = array();
			
			$result['success'] = false;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Fail Connecting PHPBB.');
			return $result;
        }
        /************************************************************************************/
       	
		$phpbb_query = "SELECT field_name FROM `#__profile_fields` ";
	    $phpbb->setQuery($phpbb_query);
	    //$db->setQuery($phpbb->query);
        $field_names=$phpbb->loadObjectList('field_name');
	     
        if(!array_key_exists('first_name',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('first_name', '2', 'first_name', '20', '0', '50', '', '.*', '1', '1', '1')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('last_name',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('last_name', '2', 'last_name', '20', '0', '50', '', '.*', '1', '1', '2')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('email',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('email', '2', 'email', '20', '0', '50', '', '.*', '1', '1', '3')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('company',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('company', '2', 'company', '20', '0', '50', '', '.*', '1', '1', '4')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('address1',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('address1', '2', 'address1', '20', '0', '50', '', '.*', '1', '1', '5')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('address2',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('address2', '2', 'address2', '20', '0', '50', '', '.*', '1', '1', '6')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('city',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('city', '2', 'city', '20', '0', '50', '', '.*', '1', '1', '7')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('state',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('state', '2', 'state', '20', '0', '50', '', '.*', '1', '1', '8')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('country',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('country', '2', 'country', '20', '0', '50', '', '.*', '1', '1', '9')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('zip',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('zip', '2', 'zip', '20', '0', '50', '', '.*', '1', '1', '10')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}
	   		
	   	if(!array_key_exists('phone',$field_names))
	   	{
	   		$phpbb_query = "INSERT INTO `#__profile_fields` (field_name, field_type, field_ident, field_length, field_minlen, field_maxlen, field_novalue, field_validation, field_show_profile, field_active, field_order) VALUES "
						  ." ('phone', '2', 'phone', '20', '0', '50', '', '.*', '1', '1', '11')";
			$phpbb->setQuery($phpbb_query);
			$phpbb->query();
	   	}

    	$fields = $phpbb->getTableFields('#__profile_fields_data');
    	
	    $keys = array_keys($fields['#__profile_fields_data']);
	   	$phpbb_query = "SELECT field_id, field_name FROM `#__profile_fields` ";
	    $phpbb->setQuery($phpbb_query);
	    //$db->setQuery($phpbb->query);
        $field_names=$phpbb->loadObjectList('field_name');
        
	    foreach ($field_names as $field)
	    {
	   		if(!in_array($field,$keys))
	   		{
	   				
	   			$query = "ALTER TABLE `#__profile_fields_data` ADD `pf_$field->field_name` varchar (255) NULL";
				$phpbb->setQuery($query);
				$phpbb->query();
					
				$lang_name=ucfirst(str_replace('_',' ',$field->field_name));
				$query = "INSERT INTO `#__profile_lang` (field_id, lang_id, lang_name, lang_explain) VALUES ('{$field->field_id}', '1', '{$lang_name}', '{$lang_name}')";
				$phpbb->setQuery($query);
				$phpbb->query();
	   		}
	   	}
	   		
	    $username=$db->Quote($post['juser_username']);
	    $phpbb_query = "SELECT user_id FROM `#__users` WHERE `username` = $username ";
	    $phpbb->setQuery($phpbb_query);
	    //$db->setQuery($phpbb->query);
	    $user_id = $phpbb->loadResult(); 
	               
	    if(empty($user_id))
	    {
	    	return false;       
        }
        $firstname = $db->Quote($post['juser_firstname']);
    	$lastname = $db->Quote($post['juser_lastname']);
    	$email=$db->Quote($post['juser_email']);
        $query = "INSERT INTO `#__profile_fields_data` (user_id, pf_last_name, pf_first_name, pf_email, pf_company, pf_address1, pf_address2, pf_city, pf_state, pf_country, pf_zip, pf_phone) VALUES "
        		."('{$user_id}', $lastname, $firstname, $email, '{$company['company']}', '{$company['addr1']}', '{$company['addr2']}', '{$company['city']}', '{$company['state']}', '{$company['country']}', '{$company['postcode']}', '{$company['telephone']}') ";
        		
		$phpbb->setQuery($query);
		$phpbb->query();
		
        $result = array();
			
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Saved PHPBB Info.');
		return $result;
		
    }		
		

}
?>