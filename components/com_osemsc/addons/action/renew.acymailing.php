<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewAcymailing
{
	public static function renew($params)
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
		return $result;
		
	}
	
	public static function activate($params)
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
		
		//oseExit($params);
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
				
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Renew Msc: No Msc ID");
			return $result;
		}
		
		// get the list id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'acymailing'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->listid))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
	    if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
	    	
	    	$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_("This plugin can not work without the AcyMailing Component");
			return $result;
		}
		
		$userClass = acymailing::get('class.subscriber');
		$user_info = self::getUserInfo($member_id);
        $subid = $userClass->subid($member_id);

		$newSubscription = array();
		$newList = null;
		$newList['status'] = 1;
		$newSubscription[$data->listid] = $newList;

		if (empty($subid))
		{
			$subscriber = new stdClass;
			$subscriber->email = $user_info->email;
			$subscriber->name = $user_info->name;
			$subscriber->userid = $user_info->id;
			$subscriber->ip = "";
			$userClass->save($subscriber);
		}

		$userClass->saveSubscription($subid,$newSubscription);
		
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
		
		return $result;
		
	}
	
   public static function getUserInfo($member_id)
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
	
	
}
?>