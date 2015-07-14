<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinJgroup
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
		
		// get the groupid of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'jgroup'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->jgroup_id))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	   	    
	    $user = JUser::getInstance($member_id);
	    $group['groups'] = array_merge($user->groups,(array)$data->jgroup_id);
		$user->bind($group);
		//$user->groups = array_flip($data->jgroup_id);
		if (!$user->save()) {

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
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
		$member_id = $params['member_id'];

		$user = new JUser($member_id);
		$iAmSuperAdmin	= $user->authorise('core.admin');
		if($iAmSuperAdmin)
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
		}
		
		$db = oseDB::instance();
		
        $query = "SELECT * FROM `#__extensions` WHERE `name` = 'com_users' AND `type` = 'component'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
	   
	    $group['groups'] = (array)$data->new_usertype;
	    $member= oseRegistry :: call('member');
	    $member->instance($member_id);
	    $Mscs = $member->getAllOwnedMsc(false,1,'obj');
	    foreach($Mscs as $Msc)
	    {
	    	if($Msc->msc_id == $params['msc_id'])
	    	{
	    		continue;
	    	}
	    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$Msc->msc_id}' AND `type` = 'jgroup'";
        	$db->setQuery($query);
       		$ext = $db->loadObject();
       		if(!empty($ext))
	    	{
	    		$ext = oseJson::decode($ext->params);
	    		$group['groups'] = array_merge($group['groups'],(array)$ext->jgroup_id);
	    	}
	    }
	    $group['groups'] = array_unique( $group['groups']);
	    //$user = JUser::getInstance($member_id);
		$user->bind($group);
		//$user->groups = array_flip($data->jgroup_id);
		if (!$user->save()) {

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
		
		return $result;
		
	}
	

}
?>