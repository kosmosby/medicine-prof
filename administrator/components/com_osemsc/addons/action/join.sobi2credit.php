<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinSobi2credit
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
		
		// get the sobi2 credit of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'sobi2credit'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		if(empty($data->credit))
	    {
	    	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
	    
		$query = "SELECT * FROM `#__osemsc_credit` WHERE `member_id` = '{$member_id}'";
		$db->setQuery($query);
        $obj = $db->loadObject();
        if(empty($obj))
        {
        	$query = " INSERT INTO `#__osemsc_credit`" 
        			." (`member_id`, `credit`, `recharge_times`, `total_consume_amout`)"
        			." VALUES"
        			." ('{$member_id}', '{$data->credit}', '0', '0')";
        }else
        {
        	$credit = $obj->credit+$data->credit;
        	$recharge_times = $obj->recharge_times+1;
        	$query = "UPDATE `#__osemsc_credit` SET `credit` = '{$credit}', `recharge_times` = '{$recharge_times}' WHERE `member_id` = '{$member_id}'";
        }

		$db->setQuery($query);
		if (!$db->query())
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

		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");	
		return $result;
		
	}
	

}
?>