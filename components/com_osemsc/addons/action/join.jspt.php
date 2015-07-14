<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinJspt
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
		
		/*
		if( $params['join_from'] != 'payment' )
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		*/
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		// get the jspt id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'jspt'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->jspt_id) || empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		$query = " SELECT userid FROM `#__xipt_users` WHERE `userid` = '{$member_id}' ";
		$db->setQuery($query);
		$userid = $db->loadResult();
		        	
		if($userid > 0)
		{
			$query = "SELECT id from `#__community_fields` WHERE `type` = 'profiletypes'";
			$db->setQuery($query);
			$jsptid = $db->loadResult(); 
		        		
			$query = "UPDATE `#__community_fields_values`  SET `value` = '{$data->jspt_id}'  WHERE `user_id` = '{$member_id}' AND `field_id` = '{$jsptid}'";
			$db->setQuery($query);
			$db->query(); 
		        		 
		   	$query = "UPDATE `#__xipt_users`  SET `profiletype` = '{$data->jspt_id}'  WHERE `userid` = '{$member_id}'";
			
		    $db->setQuery($query);
		    if (!$db->query())
		    {
			    $result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Error');
				return $result;
			}
		}else{
			
			$query = " INSERT INTO `#__xipt_users` (`profiletype`, `userid`) "
					." VALUES ('{$data->jspt_id}','{$member_id}') "
					;
			$db->setQuery($query);
			if (!$db->query())
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Error');
				return $result;
			}
		}
		    
		//Jomsocial Sync
		if(!empty($data->jomsync))
		{
			$query = "SELECT * FROM `#__osemsc_billinginfo` WHERE `user_id` = ".$member_id;
			$db->setQuery($query);
			$obj = $db->loadObject();
			if(!empty($obj))
			{
				$query = "SELECT * FROM `#__osemsc_country` WHERE `country_3_code` = '{$obj->country}'";
				$db->setQuery($query);
				$country = $db->loadObject();
				$obj->country = $country->country_name;
				$query = "SELECT state_name FROM `#__osemsc_state` WHERE `state_2_code` = '{$obj->state}' AND `country_id` = '{$country->country_id}'";
				$db->setQuery($query);
				$obj->state = $db->loadResult();
				$array = array();
				$array['firstname'] = $obj->firstname; 
				$array['lastname'] = $obj->lastname; 
				$array['company'] = $obj->company; 
				$array['addr1'] = $obj->addr1; 
				$array['addr2'] = $obj->addr2; 
				$array['city'] = $obj->city; 
				$array['state'] = $obj->state;
				$array['country'] = $obj->country;
				$array['postcode'] = $obj->postcode;
				$array['telephone'] =  $obj->telephone;
				
				foreach($array as $key => $val)
				{
					$val = $db->Quote($val);
					if(empty($data->$key))
					{
						continue;
					}
					$fid = $data->$key;
					$query = "SELECT count(*) FROM `#__community_fields_values` WHERE `field_id` = '{$fid}' AND `user_id` = ".$member_id;
					$db->setQuery($query);
					$exist = $db->loadResult();
					if(empty($exist))
					{
						$query = "INSERT INTO `#__community_fields_values` (`user_id`, `field_id`, `value`) VALUES ('{$member_id}', '{$fid}', {$val})";
					}else{
						$query = "UPDATE `#__community_fields_values` SET `value` = {$val} WHERE `field_id` = '{$fid}' AND `user_id` = ".$member_id;
					}
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

		$query = "SELECT id from `#__community_fields` WHERE `type` = 'profiletypes'";
		$db->setQuery($query);
		$jsptid = $db->loadResult(); 

		$query = "SELECT params FROM `#__xipt_settings` WHERE `name` = 'settings'";
		$db->setQuery($query);
		$result = $db->loadResult();
		$objs= explode("\n",$result);
		foreach($objs as $obj)
		{
		
			if(strstr($obj,'defaultProfiletypeID'))
			{
				
				$array = explode('=',$obj);
				$dpid = trim($array[1],'"');
			}
		}
		$query = "UPDATE `#__community_fields_values`  SET `value` = '{$dpid}'  WHERE `user_id` = '{$member_id}' AND `field_id` = '{$jsptid}'";
		$db->setQuery($query);
		$db->query(); 
		        	    			
	    $query = "UPDATE `#__xipt_users` SET `profiletype` = '{$dpid}' WHERE `userid` = '{$member_id}'";
	    $db->setQuery($query);
	    if (!$db->query())
	    {
		    $result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
	    }
			
		return $result;
		
	}
	

}
?>