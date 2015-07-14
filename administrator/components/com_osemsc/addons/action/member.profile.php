<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberProfile
{
	public static function getProfile($params = array())
	{
		$member_id = JRequest::getInt('member_id',0);
		
		$db = oseDB::instance();
    	$member = oseRegistry::call('member');
		$member->instance($member_id);
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
    	$member_id = JRequest::getInt('member_id',0);
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
    	
    	foreach($profile as $key =>$value)
    	{
    		$query = "SELECT count(*) FROM `#__osemsc_fields_values` WHERE `member_id` = '{$member_id}' AND `field_id` = '{$key}'";
    		$db->setQuery($query);
    		$exists = $db->loadResult();
    		if(empty($exists))
    		{
				$query = "INSERT INTO `#__osemsc_fields_values` (`member_id`, `field_id`, `value`) VALUES ('{$member_id}', '{$key}', '{$value}')";
    		}else
    		{
    			$query = "UPDATE `#__osemsc_fields_values` SET `value` = '{$value}' WHERE `member_id` = '{$member_id}' AND `field_id` = '{$key}'";
    		}	
			$db->setQuery($query);
    		if (!oseDB::query()) {
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('FAIL_SAVING_PROFILE_INFO');
				return $result;

			} 
		}	
    	$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('DONE');
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
    
	function upload()
	{
		$result = array();
    	$result['success'] = true;
    	$result['uploaded'] = false;
    	$result['file_path'] = null;

    	$uploadImg = JRequest::getVar('file', null, 'files', 'array' );
    	$pid = JRequest::getVar('pid',null);
    	$result['pid'] = $pid;

    	if(JFile::exists($uploadImg['tmp_name']) && !empty($pid))
    	{
			$db = oseDB::instance();
			$query= "SELECT params FROM `#__osemsc_fields` WHERE `id` = '{$pid}'";
			$db->setQuery($query);
			$params= $db->loadResult();
			if(empty($params))
			{
				return $result;
			}
    		$result['file_path'] = $params.'/'.$uploadImg['name'];
			$des = JPATH_SITE.DS.$params.DS.$uploadImg['name'];

    		if(JFile::upload($uploadImg['tmp_name'],$des))
	    	{
	    		$result['uploaded'] = true;
	    		$result['title'] = JText::_('DONE');
				$result['content'] = JText::_('SUCCESSFULLY');
	    	}
	    	else
	    	{
	    		$result['title'] = JText::_('ERROR');
		    	$result['content'] = JText::_('The directory:')." ". dirname($des). " ". JText::_('is not writable, please change the file permission of the folder to a writable status and re-upload the image again');
	    	}

			return $result;
    	}
    	else
    	{
    		$maxSize = ini_get('upload_max_filesize');
    		$result['title'] = JText::_('ERROR');
    		$result['content'] = JText::_('MAX_FILESIZE').$maxSize;
    		return $result;
    	}
		
	}
	
	function reset()
	{
		$result = array();
    	$result['success'] = true;
   		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('SUCCESSFULLY');
		$path = JRequest::getVar('file_path');
		if(JFile::exists($path))
		{
			if(!JFile::delete($path))
			{
    			$result['success'] = false;
    			$result['title'] = JText::_('ERROR');
		    	$result['content'] = JText::_('ERROR');
			}
		}
		return $result;
	}
    
}
?>