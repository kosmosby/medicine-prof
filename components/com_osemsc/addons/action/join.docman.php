<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinDocman
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
		
		// get the docman groups_id  of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'docman'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->groups_id) || $data->groups_id =='-1')
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
	    $docman_group_id = $data->groups_id;
		$query = "SELECT * FROM `#__docman_groups` WHERE `groups_id` IN ( ".$docman_group_id.")";
	    $db->setQuery($query);
	    $objs = $db ->loadObjectList();
	    if(!empty($objs))
	    {
		    foreach($objs as $obj)
			{
			    $newmembers[]=$member_id;	
			    if (!empty($obj->groups_members))
			    {
			    	$oldmembers = explode(",", $obj->groups_members);
			   		if (count($oldmembers)>0)
			    	{
			   	 		$newmembers = array_merge($oldmembers, $newmembers); 	
			   	 	} 
			    }
		   	    $newmembers = array_unique($newmembers);
			    $newmembers = implode(",", $newmembers);
			    $query = "UPDATE `#__docman_groups` SET `groups_members` = '".$newmembers."' WHERE `groups_id` = ".(int)$obj->groups_id;
			    $db->setQuery($query);
				if (!$db->query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_("Error");
					return $result;
				}
				unset($newmembers);
				unset($oldmembers);
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

		// get the docman groups_id  of msc
    	/*$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'docman'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);*/
		$msc = oseRegistry::call('msc');
		$data = $msc->getExtInfo($msc_id,'docman','obj');
		
		if(empty($data->groups_id) || $data->groups_id =='-1')
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }

	    $docman_group_id = $data->groups_id;
		$query = "SELECT * FROM `#__docman_groups` WHERE `groups_id` IN ( ".$docman_group_id.")";
	    $db->setQuery($query);
	    $objs = $db ->loadObjectList();
	    if(!empty($objs))
	    {
		    foreach($objs as $obj)
			{
				$actMemGroups = self::getActMemGroups($member_id,$msc_id);
				if(!empty($actMemGroups) && in_array($obj->groups_id,$actMemGroups))
				{
					continue;
				}
				
				$newmembers[]=$member_id;	
		    	if (!empty($obj->groups_members))
		   		{
		    		$oldmembers = explode(",", $obj->groups_members);
				    if (count($oldmembers)>0)
				    {
				   	 	$newmembers = array_diff($oldmembers, $newmembers); 	
				    } 
		   		}
	   	    	$newmembers = array_unique($newmembers);
		    	$newmembers = implode(",", $newmembers);
		    	$query = "UPDATE `#__docman_groups` SET `groups_members` = '".$newmembers."' WHERE `groups_id` = ".(int)$obj->groups_id;
		   		$db->setQuery($query);
				if (!$db->query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_("Error");
					return $result;
				}
				unset($newmembers);
				unset($oldmembers);
			}
	    }

		return $result;
		
	}
		
	function getActMemGroups($member_id,$msc_id)
	{
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_member_view` WHERE `member_id` ='{$member_id}' AND `status` = '1' AND `msc_id` != ".$msc_id;
		$db->setQuery($query);
	    $objs = $db ->loadObjectList();
	    $msc_ids = array();
	    foreach($objs as $obj)
	    {
	    	$msc_ids[] = $obj->msc_id;
	    }
	   
	    $msc = oseRegistry::call('msc');
		$groups = array();
	    foreach($msc_ids as $mscid)
	    {
	    	$data = $msc->getExtInfo($mscid,'docman','obj');
	    	if(empty($data->groups_id) || $data->groups_id =='-1')
	    	{
	    		continue;
	    	}
	    	$docman_group_id = $data->groups_id;
	    	$gids = explode(",", $docman_group_id);
	    	$groups = array_merge($gids,$groups);
	    }
	    $groups = array_unique($groups);
	    return $groups;
	}
}
?>