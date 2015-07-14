<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinOsecredit
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
		
		// get the plan id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'osecredit'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->osecredit_id) || empty($data->enable))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
	    $query = "SELECT credit_amount FROM `#__ose_credit_plan` WHERE `id` = '{$data->osecredit_id}'"; 
	    $db->setQuery($query);
        $amount = $db->loadResult();
        
		$query = "SELECT * FROM `#__ose_credit_member` WHERE `member_id` = '{$member_id}'";
		$db->setQuery($query);
        $obj = $db->loadObject();
        if(empty($obj))
        {
        	$credit_params = oseJson::encode(array());
        	$query = " INSERT INTO `#__ose_credit_member` (`member_id`, `credit_amount`, `params`)"
        			." VALUES"
        			." ('{$member_id}', '{$amount}', '{$credit_params}')";
        }else{
        	$amount = $amount+$obj->credit_amount;
        	$query = "UPDATE `#__ose_credit_member` SET `credit_amount` = '{$amount}' WHERE `id` = '{$obj->id}'";
        }
		$db->setQuery($query);
		if(!$db->query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
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

		return $result;
	}
	
}
?>