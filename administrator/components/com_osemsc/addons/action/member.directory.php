<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberDirectory extends oseMscAddon
{
	public static function getDirectoryItem($params = array())
	{
		$user = JFactory::getUser();
    	$member_id = JRequest::getInt('member_id',0);
    	
    	
    	$db = oseDB::instance();
    	
    	$query = " SELECT directory.* FROM `#__osemsc_directory` AS directory "
				." INNER JOIN  `#__oselic_cs_keys_view` AS luv ON luv.company_id = directory.company_id"
    			." WHERE luv.user_id = {$member_id}";
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$info = oseDB::loadItem();
		//$member = oseRegistry::call('member');
		
		//$member->instance($member_id);
		//$info = $member->getBillingInfo();
		$info['directory_logo'] = urldecode($info['directory_logo']);
		
		if(empty($info))
		{
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
	
	public static function getLocationItem($params = array())
	{
		//$user = JFactory::getUser();
    	//$user_id = $user->id;
    	//$member_id = JRequest::getInt('member_id',0);
    	$location_id  = JRequest::getInt('location_id',0);
    	
    	$db = oseDB::instance();
    	
    	$query = " SELECT c.* FROM `#__oselic_cs_company_view` AS c"
    			." INNER JOIN `#__oselic_cs_keys_view` AS u ON u.company_id = c.company_id"
    			." WHERE c.location_id = {$location_id}"
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$item = oseDB::loadItem();
		
		if(empty($item))
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $item;
		}
		
		return $result;
	}
	
	public static function getMtCats($params = array())
	{
		$db = oseDB::instance();
		
		$user = JFactory::getUser();
    	$user_id = JRequest::getInt('member_id',0);
    	
    	$rootName = 'root';
    	
    	$node = JRequest::getString('node',$rootName);
    	$node_id = explode('/',$node);
    	$node_id = $node_id[count($node_id)-1];
    	//oseExit($node_id);
    	$parant_id = ($node_id == $rootName)? 0 : $node_id;
    	//oseExit($parant_id);
    	$query = " SELECT * FROM `#__mt_cats` "
    			." WHERE cat_parent = {$parant_id}"
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$items = oseDB::loadList();
		
		
		foreach($items as $key => $item)
		{
			$item['id'] = $node.'/'.$item['cat_id'];
			$item['leaf'] = false;
			$item['cls'] = 'folder';
			$item['text'] = $item['cat_name'];
			$items[$key] = $item;
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
		
		return $items;
	}
	
	function getSelectedLinkCats()
	{
		$db = oseDB::instance();
		
    	
    	$directory_id = JRequest::getInt('directory_id',0);
    	
    	$linkId = self::getMtLinkId($directory_id);
    	
    	$query = " SELECT cl.*,c.cat_name FROM `#__mt_cl` AS cl"
    			." INNER JOIN `#__mt_cats` AS c ON c.cat_id = cl.cat_id"
    			." WHERE cl.link_id = {$linkId}"
    			;
    	$db->setQuery($query);
    	//oseExit($db->_sql);
    	$objs =  oseDB::loadList();
    	
    	if( count($objs) < 1 )
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($objs);
			$result['results'] = $objs;
		}
		
		return $result;
	}
	
	public static function getLocationsByMt()
	{
    	
		$link_id = JRequest::getInt('link_id',0);
    	
    	$db = oseDB::instance();
    	
    	$query = " SELECT loc.location_id,loc.addr1 AS address,loc.city , loc.state , "
    			." loc.country,addr1 AS address2,  loc.postcode,"
    			." loc.contact_title, loc.contact_name, loc.contact_email"
    			." FROM `#__osemsc_location` AS loc"
    			." INNER JOIN `#__osemsc_mtrel` AS rel ON rel.directory_id = loc.directory_id"
    			." WHERE link_id = {$link_id}"
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$items = oseDB::loadList();
		
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
	
	public static function getLocations($params = array())
	{
		$user = JFactory::getUser();
    	$user_id = JRequest::getInt('member_id',0);
    	
    	$directory_id = JRequest::getInt('directory_id');
    	
    	$db = oseDB::instance();
    	
    	$query = " SELECT c.location_id,c.location_addr1 AS address,c.location_city AS city, c.location_state AS state, "
    			." c.location_country AS country, c.location_addr2 AS address2,  c.location_postcode AS postcode,"
    			." c.contact_title,c.contact_name,c.contact_email"
    			." FROM `#__oselic_cs_company_view` AS c"
    			//." INNER JOIN `#__oselic_cs_keys_view` AS u ON u.company_id = c.company_id"
    			." WHERE c.directory_id = {$directory_id}"
    			;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$items = oseDB::loadList();
		
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
	
	public static function updateDirectoryInfo()
    {
    	$db = oseDB::instance();
    	
    	$post = JRequest::get('post');
    	
    	$result = array();
    	$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Saved Directory Info.');
    	
    	$user = JFactory::getUser();
    	
    	$member_id = JRequest::getInt('member_id');
    	
    	$id = $post['directory_directory_id'];
					   
		$query = " SELECT link_id FROM `#__osemsc_mtrel`"
			  	." WHERE directory_id = {$id}"
			  	;
		$db->setQuery($query);
		$linkId = $db->loadResult();
		
		
		// Image ...
		$uploadImg = JRequest::getVar('directory_directory_logo', null, 'files', 'array' );
    	
    	$tmplPath = JPATH_COMPONENT_SITE.DS.'assets'.DS.'tmpl_image'.DS."{$uploadImg['name']}";
    	
    	if(JFile::exists($tmplPath))
    	{
    		
					
    		$fileType = JFile::getExt($tmplPath);
    		
    		$des = JPATH_COMPONENT_SITE.DS.'assets'.DS.'company_logo'.DS."company-{$id}.{$fileType}";
    		$logoPath = JPATH_COMPONENT_SITE.DS.'assets'.DS.'company_logo';
    		
    		$files = JFolder::files($logoPath,"company-{$id}.");
    		
    		foreach($files as $file)
    		{
    			JFile::delete($logoPath.DS.$file);
    		}
    		
    		if(JFile::move($tmplPath,$des))
    		{
    			
    			$post['directory_directory_logo'] = OSEMSC_F_URL."/assets/company_logo/company-{$id}.{$fileType}";
    			$post['directory_directory_logo'] = urlencode($post['directory_directory_logo']);
    			
    			if(!self::updateMtImage($linkId,$des))
    			{
    				$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_('Fail updating the image to MTree');
					return $result;
    			}
    			
    			
    		}
    		else
    		{
    			$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Fail updating the image');
				return $result;
    		}
    		//$post['image'] = $files;
    		
    	}
    	else
    	{
    		unset($post['directory_directory_logo']);
    	}
    	// Image End
    	
		
    	$query = " SELECT * FROM `#__oselic_cs_keys_view`" 
    			." WHERE user_id = {$member_id}"
    			;
    	$db->setQuery($query);
    	
    	$item = oseDB::loadItem('obj'); 
    	//oseExit($db->_sql);
    	$company_id = $item->company_id;

		$company = array();
		
		foreach($post as $key => $value)
		{
			if(strstr($key,'directory_'))
			{
				$billKey = preg_replace('/directory_/','',$key,1); 
				$company[$billKey] = $value;
			}
		}
		//oseExit($company);
    	
    	$query = " SELECT directory_id FROM `#__osemsc_directory`"
    			." WHERE company_id = {$item->company_id}"
    			;
    	$db->setQuery($query);
    	$directory_id = $db->loadResult();
    	
		foreach($company as $key => $value)
		{
			$company[$key] = "`{$key}`=".$db->Quote($value);
		}
		
		$values = implode(',',$company);
		
		$query = " UPDATE `#__osemsc_directory` SET {$values}" 
				." WHERE `directory_id` ={$directory_id}"
				;
			
		//echo $query;exit;
		$db->setQuery($query);
		
		
		if (!oseDB::query()) {	
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Directory Info.');
			
		} else {
			$company['directory_id'] = $directory_id;
			if(!self::updateMtLink($linkId,$post))
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Fail Saving Directory Info.');
			}
			else
			{
				$result['success'] = true;
				$result['title'] = 'Done';
				$result['content'] = JText::_('Saved Directory Info.');
			}
			
		}
		
    	return $result; 
    	
    }

    public static function updateLocationInfo($params = array())
    {
    	$db = oseDB::instance();
    	
    	$post = JRequest::get('post');
    	
    	$result = array();
    	$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Saved Directory Info.');
    	
    	$directory = array();
    	
    	foreach($post as $key => $value)
		{
			if(strstr($key,'directory_'))
			{
				$billKey = preg_replace('/directory_/','',$key,1); 
				$directory[$billKey] = $value;
			}
		}
		
		//$directory['directory_id'] = $directory['id'];
		$directory_id = $directory['id'];
		unset($directory['id']);

    	//$user = JFactory::getUser();
    	$location_id = JRequest::getInt('location_id',0);
    	// Add to Mt Link
    	/*
    	$query = " SELECT directory.* FROM `#__osemsc_directory` AS directory "
				." INNER JOIN  `#__oselic_cs_keys_view` AS luv ON luv.company_id = directory.company_id"
    			." WHERE luv.location_id = {$location_id}";
    			;
    	$db->setQuery($query);
    	$item = oseDB::loadItem('obj');
    	
    	$directory_id = $item->directory_id;
    	*/
    	
    	/*
    	$query = " SELECT directory_id FROM `#__osemsc_location` "
    			." WHERE location_id = {$location_id}"
    			;
    	$db->setQuery($query);
    	
    	$item = oseDB::loadItem('obj');		
    	*/
    	
    	
    	
    	
    	$query = " SELECT * FROM `#__oselic_cs_company_view` AS lcc "
				." INNER JOIN  `#__oselic_cs_keys_view` AS luv ON luv.company_id = lcc.company_id"
    			." WHERE lcc.directory_id = {$directory_id} "
    			." ORDER BY lcc.location_id"
    			;
    	$db->setQuery($query);
    	
    	$objs = oseDB::loadList('obj');
    	
    	
    	//$location_id = JRequest::getInt('location_id',0);
    	
    	if( count($objs) < 1 )
    	{
    		self::addToMtLink($directory_id,$directory);
    	}
    	else
    	{
    		if(!empty($location_id) && $location_id == $objs[0]->location_id)
    		{
    			self::addToMtLink($directory_id,$directory);
    		}
    	}
    	
    	// Add ends
    	
    	
    	    	
    	if (empty ($location_id)) 
    	{
    		
    	
    		$keys = array_keys($directory);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_location');
    		
    		foreach($directory as $key => $value)
    		{
				if(!isset($fields['#__osemsc_location'][$key]))
				{
					/*
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
					*/
					unset($directory[$key]);
				}
    		}  
    		
    		$values = array();
    		
    		foreach($directory as $key => $value)
    		{
    			$values[$key] = $db->Quote($value);
    		}
    		
    		$values = implode(',',$values);
    		
    		
    		
			$query = "INSERT INTO `#__osemsc_location` (`directory_id`,{$keys}) VALUES ('{$directory_id}',{$values});";
		} 
		else 
		{
			
			foreach($directory as $key => $value)
    		{
    			$company[$key] = "`{$key}`=".$db->Quote($value);
    		}
    		
    		$values = implode(',',$company);
    		
			$query = " UPDATE `#__osemsc_location` SET {$values}" 
					." WHERE `location_id` ={$location_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);
		
		$result = array();
		
		if (!oseDB::query()) {	
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Fail Saving Directory Info.');
			
		} else {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Saved Directory Info.');
		}
		
    	return $result; 
    	
    }
    
    private static function checkImage($img_type)
    {
		$allowExt = array();
		$allowExt[] = 'png';
		$allowExt[] = 'gif';
		$allowExt[] = 'jpeg';
		
		$ext = explode('/',$img_type);
		
		if(in_array($ext[1],$allowExt))
		{
			return true;	
		}
		else
		{
			return false;
		}
	
    }
    
    public static function preview()
    {
    	$result = array();
    	$result['success'] = true;
    	$result['uploaded'] = false;
    	$result['img_path'] = null;
    	
    	$uploadImg = JRequest::getVar('directory_directory_logo', null, 'files', 'array' );
    	
    	$des = JPATH_COMPONENT_SITE.DS.'assets'.DS.'tmpl_image'.DS.$uploadImg['name'];
    	
    	if(JFile::exists($uploadImg['tmp_name']))
    	{
    		if(!self::checkImage($uploadImg['type']))
    		{
    			$result['success'] = true;
		    	$result['uploaded'] = false;
		    	$result['img_path'] = null;
		    	$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Only .gif,.png,.jpeg is allowed');
		    	
				$result = oseJson::encode($result);
    			oseExit($result);
    		}
    		
    		$result['img_path'] = OSEMSC_F_URL."/assets/tmpl_image/{$uploadImg['name']}";//$uploadImg['tmp_name'].'.png';
    		
    		if(JFile::upload($uploadImg['tmp_name'],$des))
	    	{
	    		$result['uploaded'] = true;
	    	}
	    	
			$result = oseJson::encode($result);
    		oseExit($result);
    	}
			
		$result = oseJson::encode($result);
    	oseExit($result);
    }
    
    
    
    public static function updateMtCat()
	{
		$result = array();
		$db = oseDB::instance();
		$user = JFactory::getUser();
		$member_id = JRequest::getInt('member_id',0);
		$cl_id = JRequest::getInt('cl_id',0);
		$cat_id = JRequest::getInt('cat_id',0);
			
		$query = " SELECT directory.* FROM `#__osemsc_directory` AS directory "
				." INNER JOIN  `#__oselic_cs_keys_view` AS luv ON luv.company_id = directory.company_id"
    			." WHERE luv.user_id = {$member_id}";
    			;
    	$db->setQuery($query);
    	$item = oseDB::loadItem('obj');
    	
    	$directory_id = $item->directory_id;
	    
	    $linkId = self::getMtLinkId($directory_id);
	    
		$query = " SELECT count(*) FROM `#__mt_cl` " 
		    	." WHERE link_id = {$linkId}"
		    	;
		$db->setQuery($query);
		
		$main = ($db->loadResult() > 0)?0:1;
			
		if(empty($cl_id))
		{
			$query = "INSERT INTO `#__mt_cl` (link_id, cat_id, main) VALUES "
					." ('{$linkId}', '{$cat_id}', '{$main}')"
					;
			
		}
		else
		{
			$query = " UPDATE `#__mt_cl` "
					." SET cat_id = '{$cat_id}' "
					." WHERE cl_id = '{$cl_id}'"
					;
		}
		
		$db->setQuery($query);
		
		if (!oseDB::query()) {	
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Fail Saving Directory Info.');
			
		} else {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Saved Directory Info.');
		}
		
    	return $result; 
	}
    
    //mtree add listing
	private function updateMtLink($link_id,$post)
	{
		$db	= oseDB::instance();
		
		$arr = array();
		foreach($post as $key => $value)
		{
			if(strstr($key,'directory_'))
			{
				$newKey = preg_replace('/directory_/','',$key,1); 
				$arr[$newKey] = $value;
			}
		}
		
		$user = JFactory::getUser();
		
		$info = array();
		$info['link_name'] = $arr['directory_name'];
		$info['alias'] = JFilterOutput::stringURLSafe($arr['directory_name']);
		$info['link_desc'] = $arr['directory_description'];
		$info['website'] = $arr['directory_website'];
		
		$values = array();
		foreach($info as $key => $value)
		{
			$values[$key] = "`{$key}`=".$db->Quote($value);
		}
		
		$values = implode(',',$values);
		
		$query = " UPDATE `#__mt_links` "
				." SET {$values}"
				." WHERE link_id = {$link_id}"
				;
				
		$db->setQuery($query);
		if(!$db->query())
		{
			return false;
		}
		
		return true;
	}
	
	private function updateMtImage($link_id,$img_path)
	{
		$db = oseDB::instance();
		$imgName = basename($img_path);
		
		
					
		if(JFile::exists($img_path))
		{
			
			$mtImgPath = JPATH_SITE.DS.'components'.DS.'com_mtree'.DS.'attachments'.DS.$imgName;
			
			$fileSize = filesize($img_path);
			
			
			if(!JFile::copy($img_path,$mtImgPath))
			{
				
				return false;
			}
			
			
		
			$query = " UPDATE `#__mt_cfvalues`"
					." SET value = ".$db->Quote($imgName).', attachment = 1, counter=1'
					." WHERE cf_id = 23 AND link_id = {$link_id}"
					;
			$db->setQuery($query);
			
			if(!oseDB::query())
			{
				return false;
			}
			
			$ext = 'image/'.JFile::getExt($imgName);
			$query = " UPDATE `#__mt_cfvalues_att`"
					." SET raw_filename = ".$db->Quote($imgName).', '
					." filename = ".$db->Quote($imgName).', '
					." filesize = {$fileSize}, extension = ".$db->Quote($ext)
					." WHERE cf_id = 23 AND link_id = {$link_id}"
					;
			$db->setQuery($query);
			
			
			if(!oseDB::query())
			{
				return false;
			}
			
			return true;
		}
		else
		{
			return true;
		}
	}
	
	private function addToMtLink($directory_id,$post)
	{
		$db = oseDB::instance();
		$query = " SELECT link_id FROM `#__osemsc_mtrel`"
				." WHERE directory_id = {$directory_id}"
				;
		$db->setQuery($query);
		
		$link_id = $db->loadResult();
		
		$arr = array();
		
		$arr['address'] = $post['addr1']; 
		$arr['city'] = $post['city']; 
		$arr['state'] = $post['state'];  
		$arr['country'] = $post['country'];  
		$arr['postcode'] = $post['postcode'];  
		$arr['telephone'] = $post['telephone'];  
		$arr['email'] = $post['contact_email'];  
		
		foreach($arr as $key => $value)
		{
			$arr[$key] = "`{$key}`=".$db->Quote($value);
		}
		
		$values = implode(',',$arr);
		
		$query = " UPDATE `#__mt_links`"
				." SET {$values}"
				." WHERE link_id = {$link_id}"
				;
		$db->setQuery($query);
		return oseDB::query();
	}
	
	public static function removeLocation()
	{
		$location_id = JRequest::getInt('location_id',0);
		
		$db = oseDB::instance();
		
		$query = " DELETE FROM `#__osemsc_location`"
				." WHERE location_id = {$location_id}"
				;
		$db->setQuery($query);
		if (!oseDB::query()) {	
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving Directory Info.');
			
		} else {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Saved Directory Info.');
		}
		
    	return $result; 
		
	}
	
	private function getMtLinkId($directory_id)
	{
		$db = oseDB::instance();
		$query = " SELECT link_id FROM `#__osemsc_mtrel`"
				." WHERE directory_id = {$directory_id}"
				;
		$db->setQuery($query);
		
		$link_id = $db->loadResult();
		
		return $link_id;
	}
	
	public static function removeMtCat()
	{
		$result = array();
		$cl_id = JRequest::getInt('cl_id',0);
		$cat_id = JRequest::getInt('cat_id',0);;
		
		$db = oseDB::instance();
		
		$query = " DELETE FROM `#__mt_cl`"
				." WHERE `cl_id` = '{$cl_id}'"
				;
		$db->setQuery($query);
		if (!oseDB::query()) {	
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error.');
			
		} else {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Deleted.');
		}
		
    	return $result; 
		
	}

	
}
?>