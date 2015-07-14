<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterProfile
{

	public static function save( $params )
    {
    	//$post = JRequest::get('post');

    	$member_id = $params['member_id'];

    	if(empty($member_id))
    	{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

		$post = JRequest::get('post');

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
	
	function getList()
    {
    	$user = JFactory::getUser();
    	$member_id = $user->id;
    	
    	$db = oseDB::instance();
    	$msc_id = JRequest::getInt('msc_id');
    	if(empty($msc_id))
    	{
    		$cart= oseMscPublic :: getCart();
    		$items= $cart->get('items');
			$item= $items[0];
			$msc_id = oseMscPublic :: getEntryMscId($item);	
    	}
    	$where = array();
    	if(!empty($msc_id))
    	{
    		$query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'profilecontrol' AND `id` = ".$msc_id;
    		$db->setQuery($query);
    		$paymentParams = $db->loadResult();
    		$paymentParams = oseJSON::decode($paymentParams);
    		if(!empty($paymentParams->enable))
    		{
    			$pids = $paymentParams->value;
    			$where[] = " `id` IN (".$pids.")";
    		}
    	}
    	$where[] = " `published` = '1'";
    	$where= oseDB :: implodeWhere($where);
    	$query = "SELECT * FROM `#__osemsc_fields` ".$where." ORDER BY `ordering`";
    	$db->setQuery($query);
    	$items = $db->loadObjectList();
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
					$options[] = $option;
				}
				$item->params = $options;
				unset($options);
			}
			
			if(!empty($member_id))
			{
				$query = "SELECT value FROM `#__osemsc_fields_values` WHERE `field_id` = '{$item->id}' AND `member_id` = '{$member_id}'";
				$db->setQuery($query);
				$item->value = $db->loadResult();
			}else{
				$item->value = null;
			}
		}
		$result = array();
		
		$result['total'] = count($items);
		$result['results'] = $items;
		
		$result = oseJSON::encode($result);

		oseExit($result);
    }
    
	function getOptions()
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
	    		$result['title'] = JText::_('Done');
				$result['content'] = JText::_('Successfully');
	    	}
	    	else
	    	{
	    		$result['title'] = JText::_('Error');
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
   		$result['title'] = JText::_('Done');
		$result['content'] = JText::_('Successfully');
		$path = JRequest::getVar('file_path');
		if(JFile::exists($path))
		{
			if(!JFile::delete($path))
			{
    			$result['success'] = false;
    			$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Error');
			}
		}
		return $result;
	}
	
	function getOseProfile()
	{
		$user = JFactory::getUser();
    	$member_id = $user->id;
    	
    	$db = oseDB::instance();
    	$msc_id = JRequest::getInt('msc_id');
    	if(empty($msc_id))
    	{
    		$cart= oseMscPublic :: getCart();
    		$items= $cart->get('items');
			$item= $items[0];
			$msc_id = oseMscPublic :: getEntryMscId($item);	
    	}
    	$where = array();
    	if(!empty($msc_id))
    	{
    		$query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'profilecontrol' AND `id` = ".$msc_id;
    		$db->setQuery($query);
    		$paymentParams = $db->loadResult();
    		$paymentParams = oseJSON::decode($paymentParams);
    		if(!empty($paymentParams->enable))
    		{
    			$pids = $paymentParams->value;
    			$where[] = " `id` IN (".$pids.")";
    		}
    	}
    	$where[] = " `published` = '1'";
    	$where= oseDB :: implodeWhere($where);
    	$query = "SELECT * FROM `#__osemsc_fields` ".$where." ORDER BY `ordering`";
    	$db->setQuery($query);
    	$items = $db->loadObjectList();
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
					$options[] = $option;
				}
				$item->params = $options;
				unset($options);
			}
			if(!empty($item->params))
			{
				$params=explode(',',$item->params);
				foreach($params as $param)
				{
					$option = array();
					$option['name'] = $param;	
					$options[] = $option;
				}
				$item->data = $options;
				unset($options);
			}
			if(!empty($member_id))
			{
				$query = "SELECT value FROM `#__osemsc_fields_values` WHERE `field_id` = '{$item->id}' AND `member_id` = '{$member_id}'";
				$db->setQuery($query);
				$item->value = $db->loadResult();
			}else{
				$item->value = null;
			}
		}
		$result = array();
		
		$result['total'] = count($items);
		$result['results'] = $items;
		
		return $result;
	}
}
?>