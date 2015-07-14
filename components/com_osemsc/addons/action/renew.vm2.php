<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewVm2
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
       
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
			
		return $result;
		
	}
	
	public static function activate($params)
	{
		return self:: renew($params);
	}
	
	
}
?>