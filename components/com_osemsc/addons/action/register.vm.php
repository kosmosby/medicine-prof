<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterVm extends oseMscAddon
{	 
	
	public static function save( $params )
    {
    	
    	$db = oseDB::instance();
    	$post = JRequest::get('post');
    	
    	$member_id = $params['member_id'];
    	
    	//JRequest::setVar('member_id',$member_id);
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
		
    	
    	$query = " SELECT count(*) FROM `#__vm_auth_user_vendor` WHERE `user_id` = '{$member_id}' ";
    	$db->setQuery($query);
    	$exists = $db->loadResult();
    	if(!empty($exists))
    	{
    		return false;
    	}

   		$query = " INSERT INTO `#__vm_auth_user_vendor` "
					." (user_id,vendor_id) "
					." VALUES "
					." ('{$member_id}','1')"
					;
			$db->setQuery($query);
			
			if(!oseDB::query())
			{
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _('Fail Saving VM Info.');
				
				return $result;
			}
		//Add the user to the default group
		$query = " SELECT shopper_group_id FROM `#__vm_shopper_group` WHERE `default` = '1' ";
    	$db->setQuery($query);
    	$default_group = $db->loadResult();
    		
		$query = " INSERT INTO `#__vm_shopper_vendor_xref` "
				." (user_id,vendor_id,shopper_group_id) "
				." VALUES "
				." ('{$member_id}','1','{$default_group}')"
				;
		$db->setQuery($query);
    	if(!oseDB::query())
		{
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Fail Saving VM Info.');
				
			return $result;
		}
			
			
    	$company = array();
		
		foreach($post as $key => $value)
		{
			if(strstr($key,'company_'))
			{
				$billKey = preg_replace('/company_/','',$key,1); 
				$company[$billKey] = $value;
			}
		}

		//get vm country code
		$query = " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$company['country']}' ";
    	$db->setQuery($query);
    	$country_code = $db->loadResult();
    	$company['country']=empty($country_code)?$company['country']:$country_code;
    	
    	//get vm state code
    	$query = " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$company['state']}' ";
    	$db->setQuery($query);
    	$state_code = $db->loadResult();
    	$company['state']=empty($state_code)?$company['state']:$state_code;
    	
		$hash_secret = "VirtueMartIsCool";
		$user_info_id = md5(uniqid( $hash_secret));
		$firstname = $db->Quote($post['juser_firstname']);
    	$lastname = $db->Quote($post['juser_lastname']);
		$email=$db->Quote($post['juser_email']);
		$mdate = mktime(date("Y-m-d H:i:s"));
		
		$query = " INSERT INTO `#__vm_user_info` "
				." (user_info_id, user_id, address_type, address_type_name, company, last_name, first_name, phone_1, address_1, address_2, city, state, country, zip, user_email, mdate) "
				." VALUES"
				." ('{$user_info_id}', '{$member_id}','BT', '-default-', '{$company['company']}', $lastname, $firstname, '{$company['telephone']}', '{$company['addr1']}', '{$company['addr2']}', '{$company['city']}', '{$company['state']}', '{$company['country']}', '{$company['postcode']}', $email, '{$mdate}' )"
				;
		$db->setQuery($query);
    	if(!oseDB::query())
		{
			$result = array();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Fail Saving VM Info.');
				
			return $result;	
		}else
		{
			$result = array();
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText :: _('Saved VM Info.');
				
			
		}
    					
     
    	return $result;
    	
    }

public static function AddVmOrder($params,$order_number)
{

	$member_id = $params['member_id'];
	
	if(empty($member_id))
    {
    		
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = JText :: _('Error');
			
		return $result;
   	}
   	
	// Get the IP Address
	if (!empty($_SERVER['REMOTE_ADDR'])) 
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	else {
		$ip = 'unknown';
	}
   	
	$post = JRequest::get('post');
	
	$msc_id = $post['msc_id'];
	$payment_mode = $post['payment_payment_mode'];
	$payment_method = $post['payment_payment_method'];
		
	//Insert the vm order table(#__vm_orders)
	$order =array();
	//get membership price	
	$payment = oseRegistry::call('payment');
	$paymentInfo = oseMscAddon::getExtInfo($msc_id,'payment','obj');
	
	if($payment_mode == 'm')
	{
		$order_subtotal = $paymentInfo->price;
	}
	else
	{
		$order_subtotal = (empty($paymentInfo->has_trial))?$paymentInfo->a3:$paymentInfo->a1;
	}
	
	$order['order_subtotal'] = $order_subtotal;
	
	$order_total = $payment->pricing($msc_id,$payment_mode);
	$order['order_total'] = $order_total;
	
	$db=JFactory::getDBO();
	
	//$order['order_tax'] = '0.00';
	
	$query= "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = '".(int) $member_id."'  AND (`address_type` = 'BT' OR `address_type` IS NULL)";
	$db->setQuery($query);
	$result= $db->loadResult();
	
	$hash_secret = "VirtueMartIsCool";
	$user_info_id = empty($result)?md5(uniqid( $hash_secret)):$result;
	$vendor_id = '1';
		
	$order['user_id'] = $member_id;
	$order['vendor_id'] = $vendor_id;
	$order['user_info_id'] = $user_info_id;
	$order['order_number'] = $order_number;
	$order['order_currency'] = (!empty($payment->currency))?$payment->currency:"USD";
    $order['order_status'] = 'P';
    $order['cdate'] = time();
    $order['ip_address'] = $ip;
    $keys = array_keys($order);
    $keys = '`'.implode('`,`',$keys).'`';
    
    foreach($order as $key => $value)
    {
    	$order[$key] = $db->Quote($value);
    }
    
    $values = implode(',',$order);
    	
	$query = "INSERT INTO `#__vm_orders` ({$keys}) VALUES ({$values});";
	$db->setQuery($query);
	
	if (!oseDB::query()) 
	{
		$result = array();
		
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = JText::_('Error');
	}
	
	//Insert the #__vm_order_history table
	$order_id = $db->insertid();
	$history = array();
	$history['order_id'] = $order_id;
	$history['order_status_code'] = 'P';
	$history['date_added'] = date("Y-m-d G:i:s", time());
	$history['customer_notified'] = '1';
	
	$keys = array_keys($history);
    $keys = '`'.implode('`,`',$keys).'`';
    
    foreach($history as $key => $value)
    {
    	$history[$key] = $db->Quote($value);
    }
    
    $values = implode(',',$history);
    	
	$query = "INSERT INTO `#__vm_order_history` ({$keys}) VALUES ({$values});";
	$db->setQuery($query);
	
	if (!oseDB::query()) 
	{
		$result = array();
		
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = JText::_('Error');
	}
	
	
	//Insert the Order payment info
	$payment = array();
	$payment['order_id'] = $order_id;
	$payment['payment_method_id'] = $payment_method;
	
	if($payment_method == 'authorize')
	{
		
	}
	
	
	//Insert the User Bill
	$bill = array();
	if(isset($post['company_company']))
	{
		$bill['company'] = $post['company_company'];
		$bill['address_1'] = $post['company_addr1'];
		$bill['address_2'] = $post['company_addr2'];
		$bill['city'] = $post['company_city'];
		$bill['state'] = $post['company_state'];
		$bill['country'] = $post['company_country'];
		
		//get vm country code
		$query = " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$bill['country']}' ";
    	$db->setQuery($query);
    	$country_code = $db->loadResult();
    	$bill['country'] = empty($country_code)?$bill['country']:$country_code;
    	
    	//get vm state code
    	$query = " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$bill['state']}' ";
    	$db->setQuery($query);
    	$state_code = $db->loadResult();
    	$bill['state'] = empty($state_code)?$bill['state']:$state_code;
    	
		$bill['zip'] = $post['company_postcode'];
		$bill['phone_1'] = $post['company_telephone'];
	}else
	{
		$bill['address_1'] = $post['bill_addr1'];
		$bill['city'] = $post['bill_city'];
		$bill['state'] = $post['bill_state'];
		$bill['country'] = $post['bill_postcode'];
	}
	
	$bill['order_id'] = $order_id;
	$bill['user_id'] = $member_id;
	$bill['address_type'] = 'BT';
	$bill['address_type_name'] = '-default-';
	$bill['last_name'] = $post['juser_lastname'];
	$bill['first_name'] = $post['juser_firstname'];
	$bill['user_email'] = $post['juser_email'];
	
	$keys = array_keys($bill);
    $keys = '`'.implode('`,`',$keys).'`';
    
    foreach($bill as $key => $value)
    {
    	$bill[$key] = $db->Quote($value);
    }
    
    $values = implode(',',$bill);
    	
	$query = "INSERT INTO `#__vm_order_user_info` ({$keys}) VALUES ({$values});";
	$db->setQuery($query);
	
	if (!oseDB::query()) 
	{
		$result = array();
		
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = JText::_('Error');
	}
	
	
	//Insert the itme table(#__vm_order_item)
	$item=array();
	$item['order_id']=$order_id;
	$item['user_info_id'] = $user_info_id;
	$item['vendor_id'] = $vendor_id;
	
	//get the product info
	$vm = oseMscAddon::getExtInfo($msc_id,'vm','obj');
	$query = " SELECT * FROM `#__vm_product` WHERE `product_id` = '{$vm->product_id}' ";
    $db->setQuery($query);
    $product = $db->loadObject();
    
	$item['product_id'] = $vm->product_id;
	$item['order_item_sku'] = $product->product_sku;
	$item['order_item_name'] = $product->product_name;
	$item['product_quantity'] = '1';
	$item['product_item_price'] = $order_subtotal;
	$item['product_final_price'] = $order_total;
	$item['order_item_currency'] = (!empty($payment->currency))?$payment->currency:"USD";
	$item['order_status'] = 'P';
	$item['cdate'] = time();;
	
	$keys = array_keys($item);
    $keys = '`'.implode('`,`',$keys).'`';
    
    foreach($item as $key => $value)
    {
    	$item[$key] = $db->Quote($value);
    }
    
    $values = implode(',',$item);
    	
	$query = "INSERT INTO `#__vm_order_item` ({$keys}) VALUES ({$values});";
	$db->setQuery($query);
	
	if (!oseDB::query()) 
	{
		$result = array();
		
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = JText::_('Error');
	}
	
		$result = array();
		
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Done');
		
		return $result;
	
}	
    
}
?>