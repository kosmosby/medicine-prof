<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinCb
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
		
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'cb'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
	    $payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($member_id);
		$user = JFactory::getUser($member_id);
		$billinginfo->firstname = (empty($billinginfo->firstname) && empty($billinginfo->lastname))?$user->name:$billinginfo->firstname;
		$billinginfo->firstname = $db->Quote($billinginfo->firstname);
		$billinginfo->lastname = $db->Quote($billinginfo->lastname);
		
		$query = "SELECT * FROM `#__comprofiler` WHERE `user_id` = ".$member_id;
		$db->setQuery($query);
        $obj = $db->loadObject();
        if(empty($obj))
        {
        	$query = "INSERT INTO `#__comprofiler` (`id`, `user_id`, `firstname`, `lastname`) VALUES ('{$member_id}', '{$member_id}', {$billinginfo->firstname}, {$billinginfo->lastname})";
        }else{
        	$query = "UPDATE `#__comprofiler` SET `firstname` = {$billinginfo->firstname}, `lastname` =  {$billinginfo->lastname} WHERE `user_id` = ".$member_id;
        }
		
		$db->setQuery($query);
		if(!$db->query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = 'CB Error';
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
			
		return $result;
		
	}
	

}
?>