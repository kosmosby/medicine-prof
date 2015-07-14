<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinVm2
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

		// get the vm shopper group id of msc
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'vm2'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);

		if(!empty($data->sg_id))
	    {
	    	$vm_sg_id = $data->sg_id;
	    }else
        {
           	$query = "SELECT virtuemart_shoppergroup_id FROM `#__virtuemart_shoppergroups` WHERE `default` = '1'";
           	$db->setQuery($query);
        	$vm_sg_id = $db->loadResult();
        }

		$customer_number = md5(uniqid($member_id));
        $query = "SELECT count(*) FROM `#__virtuemart_vmusers` WHERE `virtuemart_user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $vmuser = $db ->loadResult();
        if(empty($vmuser) && $option != 'com_virtuemart')
        {
        	$query ="INSERT INTO `#__virtuemart_vmusers` (`virtuemart_user_id`, `customer_number`, `agreed`) VALUES ('{$member_id}', '{$customer_number}', 1)";
        	$db->setQuery($query);
            if (!$db->query())
            {
            	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM User Info Error.");
				return $result;
            }
        }
        
		$query = "SELECT count(*) FROM `#__virtuemart_userinfos` WHERE `virtuemart_user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $result = $db ->loadResult();
        $option = JRequest::getVar('option');
        if (empty($result) && $option != 'com_virtuemart')
        {
			$query ="INSERT INTO `#__virtuemart_userinfos` (`virtuemart_user_id`, `address_type`) VALUES ('{$member_id}', 'BT');";
            $db->setQuery($query);
             if (!$db->query())
             {
             	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM User Info Error.");
				return $result;
             }
        }

        $query = "SELECT virtuemart_shoppergroup_id FROM `#__virtuemart_vmuser_shoppergroups` WHERE `virtuemart_user_id` = ".(int)$member_id;
        $db->setQuery($query);
        $result = $db ->loadResult();
        if (!empty($result))
        {
        	$query = "UPDATE `#__virtuemart_vmuser_shoppergroups` SET `virtuemart_shoppergroup_id` =".(int)$vm_sg_id." WHERE `virtuemart_user_id` =".(int)$member_id;
        }else
        {
             $query ="INSERT INTO `#__virtuemart_vmuser_shoppergroups` (`id` ,`virtuemart_user_id` ,`virtuemart_shoppergroup_id`)VALUES (NULL, '{$member_id}', '{$vm_sg_id}');";
        }
        $db->setQuery($query);
        if (!$db->query())
        {
           	$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join VM User Info Error.");
			return $result;
        }
		//Update VM billing Info
        if($data->update_billing)
        {
        	$payment= oseRegistry :: call('payment');
			$paymentOrder = $payment->getInstance('Order');
			$billinginfo = $paymentOrder->getBillingInfo($member_id);
			$user = JFactory::getUser($member_id);
			if(!empty($billinginfo))
			{
				$query = "SELECT virtuemart_country_id FROM `#__virtuemart_countries` WHERE `country_3_code` = '{$billinginfo->country}'";
				$db->setQuery($query);
        		$country_id = $db ->loadResult();
        		
        		$query = "SELECT virtuemart_state_id FROM `#__virtuemart_states` WHERE `virtuemart_country_id` = '{$country_id}' AND `state_2_code` = '{$billinginfo->state}'";
				$db->setQuery($query);
        		$state_id = $db ->loadResult();
        		
				$bill = array();
				
				$bill['company'] = empty($billinginfo->company )?null:$billinginfo->company;
				$bill['first_name'] = empty($billinginfo->firstname )?null:$billinginfo->firstname;
				$bill['last_name'] = empty($billinginfo->lastname )?null:$billinginfo->lastname;
				$bill['name'] = $bill['first_name'].' '.$bill['last_name'];
				$bill['phone_1'] = empty($billinginfo->telephone )?null:$billinginfo->telephone;
				$bill['address_1'] = empty($billinginfo->addr1 )?null:$billinginfo->addr1;
				$bill['address_2'] = empty($billinginfo->addr2 )?null:$billinginfo->addr2;
				$bill['city'] = empty($billinginfo->city )?null:$billinginfo->city;
				$bill['virtuemart_state_id'] = empty($state_id)?null:$state_id;
				$bill['zip'] = empty($billinginfo->postcode )?null:$billinginfo->postcode;
				//$bill['user_email'] = empty($user->email )?null:$user->email;
				$bill['virtuemart_country_id'] = empty($country_id)?null:$country_id;
				$billinfo = array();
				foreach($bill as $key => $value)
				{
					if(!empty($value))
					{
						$billinfo[$key] = "`{$key}`=".$db->Quote($value);
					}
				}
				
				$values = implode(',',$billinfo);
				$query = " UPDATE `#__virtuemart_userinfos` SET {$values}"
						." WHERE `virtuemart_user_id` ={$member_id} AND `address_type` = 'BT'"
						;
				$db->setQuery($query);		
				if (!$db->query())
		        {
		           	$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_("Join VM User Info Error.");
					return $result;
		        }		
					
			}
        }
        /*
        //generate VM order
        if($data->update_order && !empty($data->product_id))
        {
        	$order_id = $params['order_id'];
        	$order_item_id = $params['order_item_id'];
        	
			$where= array();
			$where[]= "`order_id` = ".$db->quote($order_id);
			$payment= oseRegistry :: call('payment');
			$orderInfo = $payment->getOrder($where, 'obj');
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			
			$str = session_id();
			$str .= (string)time();
			$order_number = $member_id .'_'. md5($str);
			$vm_order_number = substr($order_number, 0, 32);
			$query = "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = {$member_id} AND `address_type` = 'BT'";
			$db->setQuery($query);	
			$user_info_id = $db->loadResult();
			$timestamp = time();
			$query = " INSERT INTO `#__vm_orders` (`user_id`, `vendor_id`, `order_number`, `user_info_id`, `order_total`, `order_subtotal`, `order_tax`, `order_currency`, `order_status`, `cdate`, `mdate`)"
					." VALUES"
					." ('{$member_id}', 1, '{$vm_order_number}', '{$user_info_id}', '{$orderInfoParams->total}', '{$orderInfoParams->subtotal}', '{$orderInfoParams->gross_tax}', '{$orderInfo->payment_currency}', 'C', '{$timestamp}', '{$timestamp}')";
	        $db->setQuery($query);	
			if (!$db->query())
		    {
		       	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM Order Info Error.");
				return $result;
		    }
		    
		    //order item 
		    $vm_order_id = $db->insertid();
		    $product_id = $data->product_id;
		    $query = " SELECT p.*,pp.product_price FROM `#__vm_product` AS p "
		    		." INNER JOIN `#__vm_product_price` AS pp"
		    		." ON p.`product_id` = pp.`product_id`"
		    		." WHERE p.`product_id` = ".$product_id;
		    $db->setQuery($query);
		    $proInfo = $db->loadObject();
			
		    $query = " INSERT INTO `#__vm_order_item` (`order_id`, `user_info_id`, `vendor_id`, `product_id`, `order_item_sku`, `order_item_name`, `product_quantity`, `product_item_price`, `product_final_price`, `order_item_currency`, `order_status`, `cdate`, `mdate`)"
		    		." VALUES"
		    		." ('{$vm_order_id}', '{$user_info_id}', 1, '{$product_id}', '{$proInfo->product_sku}', '{$proInfo->product_name}', 1, '{$proInfo->product_price}', '{$orderInfoParams->total}', '{$orderInfo->payment_currency}', 'C', '{$timestamp}', '{$timestamp}')";
         	$db->setQuery($query);	
			if (!$db->query())
		    {
		       	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM Order Info Error.");
				return $result;
		    }

		    //order user info
		    $array = array();
		    foreach($bill as $key => $value)
    		{
    			$array[$key] = $db->Quote($value);
    		}
			$keys = array_keys($bill);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$values = implode(',',$array);

			$query = "INSERT INTO `#__vm_order_user_info` (`order_id`,`user_id`,`address_type`,{$keys}) VALUES ('{$vm_order_id}','{$member_id}','BT',{$values});";
        	$db->setQuery($query);
			if (!$db->query())
		    {
		       	$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Join VM Order Info Error.");
				return $result;
		    }
        }
        */
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
    	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'vm2'";
        $db->setQuery($query);
        $data = $db->loadObject();
		$data = oseJson::decode($data->params);

		$query = "SELECT virtuemart_shoppergroup_id FROM `#__virtuemart_vmuser_shoppergroups` WHERE `virtuemart_user_id` = ".(int)$member_id;
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

        	$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$own_msc_id}' AND `type` = 'vm2'";
	        $db->setQuery($query);
	        $ext = $db->loadObject();
			$ext = oseJson::decode($ext->params);
			$vm_sg_id = $ext->sg_id;
        }else{
        	$query = "SELECT virtuemart_shoppergroup_id FROM `#__virtuemart_shoppergroups` WHERE `default` = '1'";
       	 	$db->setQuery($query);
        	$vm_sg_id = $db->loadResult();
        }
		$query = "UPDATE `#__virtuemart_vmuser_shoppergroups` SET `virtuemart_shoppergroup_id` =".(int)$vm_sg_id." WHERE `virtuemart_user_id` =".(int)$member_id;
	    $db->setQuery($query);
	    if (!$db->query())
	    {
		    $result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
	    }

		return $result;

	}


}
?>