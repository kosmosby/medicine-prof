<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberProfile
{
	public static function getProfile($params = array())
	{
		$user = JFactory::getUser();
    	$member_id = $user->id;
		
    	$db = oseDB::instance();
    	
		$member = oseRegistry::call('member');
		$member->instance($user->id);
		$mscs = $member->getAllOwnedMsc(true,1,'obj');
		if(!empty($mscs))
		{
			foreach($mscs as $msc)
			{
				$Mem_mscs[] = $msc->msc_id;
			}
		}else{
			$Mem_mscs = array();
		}
		$pids = array();
		if(!empty($Mem_mscs))
		{
			foreach($Mem_mscs as $msc_id)
			{
				$query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'profilecontrol' AND `id` = ".$msc_id;
	    		$db->setQuery($query);
	    		$paymentParams = $db->loadResult();
	    		$paymentParams = oseJSON::decode($paymentParams);
	    		if(!empty($paymentParams->enable))
    			{
    				$values = explode(",",$paymentParams->value);
    				$pids = array_merge($pids,$values);
    			}
			}
		}
		$where = array();
		if(!empty($pids))
		{
			$pids = array_unique($pids);
			$pids = implode(",",$pids);
			$where[] = " `id` IN (".$pids.")";
		}
		$where[] = " `published` = '1'";
    	$where= oseDB :: implodeWhere($where);
	    $query = "SELECT * FROM `#__osemsc_fields` ".$where." ORDER BY `ordering`";
		$db->setQuery($query);
		$items = oseDB::loadList('obj');
		foreach($items as $item)
		{
			if($item->type == 'radio')
			{
				$params=explode(',',$item->params);
				foreach($params as $param)
				{
					$option = array();
					$option['boxLabel'] = $param;	
					$option['inputValue'] = $param;
					$option['autoWidth'] = true;
					$options[] = $option;
				}
				$item->params = $options;
				unset($options);
			}
			$query = "SELECT value FROM `#__osemsc_fields_values` WHERE `field_id` = '{$item->id}' AND `member_id` = '{$member_id}'";
			$db->setQuery($query);
			$item->value = $db->loadResult();
		}
		if(empty($items))
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		
		return $result;
	}
	
	public static function save()
    {
    	$result = array();
    	
    	$post = JRequest::get('post');
    	$user = JFactory::getUser();
    	$member_id = $user->id;
    	$profile = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'profile_'))
			{
				$pKey = preg_replace('/profile_/','',$key,1);
				$profile[$pKey] = $value;
			}
		}

		//oseExit($billinfo);
    	$db = oseDB::instance();
    	
    	$query = "SELECT * FROM `#__osemsc_fields` WHERE `type` = 'fileuploadfield'";
    	$db->setQuery($query);
    	$objs = $db->loadObjectList();
    	$fileuploadfields = array();
    	if(!empty($objs))
    	{
    		foreach($objs as $obj)
    		{
    			$fileuploadfields[] = $obj->id;
    		}
    	}	
    	
    	foreach($profile as $key =>$value)
    	{
    		if(in_array($key,$fileuploadfields) && !strstr($value,$member_id.'_'))
    		{
    			$arr = explode('/',$value);
    			$count = count($arr);
    			$name = $arr[$count-1];
    			$des = str_replace($name,$member_id.'_'.$name,$value);
    			if(JFile::exists(JPATH_SITE.DS.$value))
    			{
    				JFile::move(JPATH_SITE.DS.$value,JPATH_SITE.DS.$des);
    				$value = $des;
    			}
    		}
    		$query = "SELECT count(*) FROM `#__osemsc_fields_values` WHERE `member_id` = '{$member_id}' AND `field_id` = '{$key}'";
    		$db->setQuery($query);
    		$exists = $db->loadResult();
    		$value = $db->Quote($value);
    		if(empty($exists))
    		{
				$query = "INSERT INTO `#__osemsc_fields_values` (`member_id`, `field_id`, `value`) VALUES ('{$member_id}', '{$key}', {$value})";
    		}else
    		{
    			$query = "UPDATE `#__osemsc_fields_values` SET `value` = {$value} WHERE `member_id` = '{$member_id}' AND `field_id` = '{$key}'";
    		}	
			$db->setQuery($query);
    		if (!oseDB::query()) {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_('Fail Saving Profile Info.');
				return $result;

			} 
		}	
    	$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_('Saved User Profile Info.');
    	return $result;
    	
    }

	public static function getOptions()
	{
		$db = JFactory::getDBO();
		$id = JRequest::getInt('id',0);
		//$type = JRequest::getVar('type',null);
		$query = "SELECT params FROM `#__osemsc_fields` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		$params = $db->loadResult();
		$params = explode(',',$params);
		foreach($params as $param)
		{
			$option['option'] = $param;
			$options[] = $option;
		}

		$result = array();

		$result['total'] = count($options);
		$result['results'] = $options;

		$result = oseJson::encode($result);

		oseExit($result);
	}
    
}
?>