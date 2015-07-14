<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinVm
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

		// get the vm shopper group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'vm'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);

		if(!empty($data->sg_id))
	    {
	    	$vm_sg_id = $data->sg_id;
	    }else
        {
           	$query = "SELECT shopper_group_id FROM `#__vm_shopper_group` WHERE `default` = '1'";
           	$db->setQuery($query);
        	$vm_sg_id = $db->loadResult();
        }

        $hash_secret = "VirtueMartIsCool";
        $user_info_id = md5(uniqid( $hash_secret));
		$query = "SELECT count(*) FROM #__vm_user_info WHERE user_id=".(int)$member_id;
        $db->setQuery($query);
        $result = $db ->loadResult();
        if (empty($result))
        {
    		$query ="INSERT INTO `#__vm_user_info` (`user_info_id`, `user_id`, `address_type`) VALUES ('{$user_info_id}', '{$member_id}', 'BT');";
            $db->setQuery($query);
             if (!$db->query())
             {
             	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM User Info Error.");
				return $result;
             }
        }

        $query = "SELECT shopper_group_id FROM #__vm_shopper_vendor_xref WHERE user_id=".(int)$member_id;
        $db->setQuery($query);
        $result = $db ->loadResult();
        if (!empty($result))
        {
        	$query = "UPDATE `#__vm_shopper_vendor_xref` SET `shopper_group_id` =".(int)$vm_sg_id." WHERE `user_id` =".(int)$member_id;
        }else
        {
             $query ="INSERT INTO `#__vm_shopper_vendor_xref` (`user_id` ,`vendor_id` ,`shopper_group_id` ,`customer_number`)VALUES ('{$member_id}', '1', '{$vm_sg_id}', '');";
        }
        $db->setQuery($query);
        if (!$db->query())
        {
           	$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join VM User Info Error.");
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

		// get the vm shopper group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'vm'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);
		
		$query = "SELECT shopper_group_id FROM #__vm_shopper_vendor_xref WHERE user_id=".(int)$member_id;
        $db->setQuery($query);
        $gid = $db ->loadResult();
       
        if($data->sg_id != $gid)
        {
        	$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
        }
        
        $query = "SELECT * FROM `#__osemsc_member` WHERE `member_id` = '{$member_id}' AND `status` = '1' ORDER BY `id` DESC";
        $db->setQuery($query);
        $Mems = $db->loadObjectList();
        if(!empty($Mems))
        {
        	$own_msc_id = $Mems[0]->msc_id;
        	
        	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$own_msc_id}' AND `type` = 'vm'";
	        $db->setQuery($query);
	        $ext = $db->loadObject();
			$ext = oseJson::decode($ext->params);
			$vm_sg_id = $ext->sg_id;
        }else{
        	$query = "SELECT shopper_group_id FROM `#__vm_shopper_group` WHERE `default` = '1'";
       	 	$db->setQuery($query);
        	$vm_sg_id = $db->loadResult();
        }
		$query = "UPDATE `#__vm_shopper_vendor_xref` SET `shopper_group_id` =".(int)$vm_sg_id." WHERE `user_id` =".(int)$member_id;
	    $db->setQuery($query);
	    if (!$db->query())
	    {
           	$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = $db->getErrorMsg();
			oseExit( $result);
	    }
	    else
	    {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			return $result;
	    }
			
	}


}
?>