<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelAcymailing extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('acymailing_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		
		return $result;
	}
	
		
	public static function getMailingList($params = array())
	{
		$db = oseDB::instance();
		
		$query = "SELECT * FROM `#__acymailing_list` WHERE `type`= 'list'";
        $db->setQuery($query);
        $items = oseDB::loadList();
		
		$result = array();
		
		if(count($items) < 1)
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
	
	public static function synchronize()
    {
		$db = oseDB::instance();
    	$listid = JRequest::getInt('acymailing_listid',null);
		$post = JRequest::get('post');
		$msc_id = $post['msc_id'];
    	if(empty($listid))
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLEASE_CHOOSE_A_MAILING_LIST_FIRST');
			return $result;
    	}
		self::save();
		
    	$query = " SELECT mem.* FROM `#__osemsc_member` AS mem WHERE mem.`msc_id` = '{$msc_id}' AND mem.`status` = '1'"
    			;
    	$db->setQuery($query);
    	$objs = $db->loadObjectList();
		
    	// First step :  sync to the acy
    	if(count($objs) > 0 && (!empty($objs)))
    	{
    		
    		$num = 0;
    		foreach($objs as $obj)
    		{
    			$query = "SELECT * FROM `#__acymailing_subscriber` WHERE `userid` = ".$obj->member_id;
    			$db->setQuery($query);
    			$res = $db->loadObject();
    			
    			if(empty($res))
    			{
    				$user = JFactory::getUser($obj->member_id);
    				$date = time();
    				$query = " INSERT INTO `#__acymailing_subscriber` (`email`, `userid`, `name`, `created`, `confirmed`, `enabled`, `accept`, `html`) "
    						." VALUES"
    						." ('{$user->email}', '{$obj->member_id}', {$db->Quote($user->name)}, '{$date}', 1, 1, 1, 1)";
    				$db->setQuery($query);
    				$db->query();
    				$obj->subid = $db->insertid();			
    			}else{
    				$obj->subid = $res->subid;
    			}
    			
	    		$query = " SELECT count(*) FROM `#__acymailing_listsub` "
	    				." WHERE `listid` = '{$listid}' AND `subid` = '{$obj->subid}'"
	    				;
	    		$db->setQuery($query);
	    		$num = $db->loadResult();

	    		if( $num < 1 )
	    		{
	    			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
	    				
	    				$result['success'] = false;
						$result['title'] = JText::_('ERROR');
						$result['content'] = JText::_('THIS_PLUGIN_CAN_NOT_WORK_WITHOUT_THE_ACYMAILING_COMPONENT');
	
						return $result;
					};
					// Add user to AcyMailing
					$userClass = acymailing::get('class.subscriber');

					//$member_info = self::getMemInfo($obj->member_id,$msc_id);
					$user_info = self::getUserInfo($obj->member_id);

					$subscriber = new stdClass;

					$subscriber->email = $user_info->email;
					$subscriber->name = $user_info->name;
					$subscriber->userid = $user_info->id;
					$subscriber->ip = "";
					
					$userClass->save($subscriber);

					$newSubscription = array();
					$newList = null;
					$newList['status'] = 1;
					$newSubscription[$listid] = $newList;
					$subid = $userClass->subid($obj->member_id);

					if(!empty($subid))
					{
						if (!$userClass->saveSubscription($subid,$newSubscription))
						{
							$result['success'] = false;
							$result['title'] = JText::_('ERROR');
							$result['content'] = JText::_('ERROR');
	
							return $result;
						}
					}
	    		}
    		}
			
    	}

    	$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		
    	return $result;
    }
    
    function getUserInfo($member_id)
    {
    	//@todo : change to load msc addon if no expired date if order mistake
		$db = oseDB::instance();
    	$query = "SELECT * FROM `#__users` "
    			." WHERE `id` = '{$member_id}'"
    			;
    	$db->setQuery($query);
		$obj = $db->loadObject();

		return $obj;
    }
    
    function getMemInfo($member_id,$msc_id)
    {
    	//@todo : change to load msc addon if no expired date if order mistake
		$db = oseDB::instance();
    	$query = "SELECT * FROM `#__osemsc_member` "
    			." WHERE `member_id` = '{$member_id}' AND `msc_id` = '{$msc_id}'"
    			;
    	$db->setQuery($query);
		$obj = $db->loadObject();

		return $obj;
    }
}
?>