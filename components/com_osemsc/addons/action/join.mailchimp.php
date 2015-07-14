<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinMailchimp
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

		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		// get the list id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'mailchimp'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->list_id) || empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		require_once(OSEMSC_B_LIB.DS.'MCAPI.class.php');
		$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
		$APIKey = $oseMscConfig->mailchimp_api_key;
		$api = new MCAPI($APIKey);
		
		$query = "SELECT email FROM `#__users` WHERE `id` = ".(int) $member_id;
		$db->setQuery($query);
		$email = $db->loadResult();

		$MC_MemInfo = $api->listMemberInfo($data->list_id,$email);
		
		if($MC_MemInfo['success'])
		{
			if($MC_MemInfo['data'][0]['status'] == 'subscribed')
			{
				$result['success'] = true;
				$result['title'] = JText::_('Done');
				$result['content'] = JText::_("Done");
				return $result;
			}
		}
		
		$join = $api->listSubscribe($data->list_id,$email);
		
		if(!$join)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join MailChimp Failed");
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

		$db = oseDB::instance();
		$msc_id =$params['msc_id'];
		$member_id = $params['member_id'];

		// get the list id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'mailchimp'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->list_id) || empty($data->enable) || empty($data->unsubscribe_enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		require_once(OSEMSC_B_LIB.DS.'MCAPI.class.php');
		$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
		$APIKey = $oseMscConfig->mailchimp_api_key;
		$api = new MCAPI($APIKey);
		
		$query = "SELECT email FROM `#__users` WHERE `id` = ".(int) $member_id;
		$db->setQuery($query);
		$email = $db->loadResult();
		
		$MC_MemInfo = $api->listMemberInfo($data->list_id,$email);
		
		if($MC_MemInfo['success'])
		{
			$api->listUnsubscribe($data->list_id,$email,$data->delete,$data->sendGoodbye,$data->sendNotify);
		}	
		return $result;
		
	}
	

}
?>