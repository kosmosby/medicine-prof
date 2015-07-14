<?php
defined('_JEXEC') or die("Direct Access Not Allowed");

class oseMscPublic
{
	public static function getIP()
	{
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		else
		{
			if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
	    	{
	    		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	    	}
	    	else
	    	{
	    		if (!empty($_SERVER["REMOTE_ADDR"]))
		    	{
		    		$cip = $_SERVER["REMOTE_ADDR"];
		    	}
		    	else
		    	{
		    		$cip = '';
		    	}
	    	}
		}


		preg_match("/[\d\.]{7,15}/", $cip, $cips);

		$cip = isset($cips[0]) ? $cips[0] : 'unknown';

		unset($cips);

		return $cip;
	}

	function uniqueUserName($username, $user_id)
	{
		$result = array();
		$result['success'] = false;
		$result['script'] = "('username').focus()";
		if(empty($username))
		{
			$result['result'] = JText::_('This field is required');
		}
		else
		{
			$db = oseDB::instance();

			$where = array();

			$username = $db->Quote(strtolower($username));

			$where[] = "LOWER(username) = {$username}";

			if(!empty($user_id))
			{
				$where[] = "id != '{$user_id}'";
			}

			$where = oseDB::implodeWhere($where);


			$query = " SELECT COUNT(*) FROM `#__users`"
					. $where
					;
			$db->setQuery($query);
			//oseExit($db->_sql);
			$isValid = ($db->loadResult() > 0) ? false: true;

			if($isValid)
			{
				$result['success'] = true;
			}
			else
			{
				$result['result'] = JText::_('This username has been registered by other user.');
			}
		}

		return $result;
	}

	function getPaymentMode($key = 'payment_mode')
	{
		$default = 'a';

		return JRequest::getCmd($key,$default);
	}

	/*
	function addVmOrder($msc_id, $member_id, $params, $order_number) {
		if(empty($member_id)) {
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
			return $result;
		}
		// Get the IP Address
		if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip= $_SERVER['REMOTE_ADDR'];
		} else {
			$ip= 'unknown';
		}
		$post= JRequest :: get('post');
		$payment_mode= $params['payment_mode'];
		$payment_method= $params['payment_method'];
		//Insert the vm order table(#__vm_orders)
		$order= array();
		//get membership price
		$payment= oseRegistry :: call('payment');
		$paymentInfo= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		if($payment_mode == 'm') {
			$order_subtotal= $paymentInfo->price;
		} else {
			$order_subtotal=(empty($paymentInfo->has_trial)) ? $paymentInfo->a3 : $paymentInfo->a1;
		}
		$order['order_subtotal']= $params['payment_price'];
		$order_total= $params['payment_price'];
		$order['order_total']= $order_total;
		$db= oseDB :: instance();
		//$order['order_tax'] = '0.00';
		$query= "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = '".(int) $member_id."'  AND (`address_type` = 'BT' OR `address_type` IS NULL)";
		$db->setQuery($query);
		$result= $db->loadResult();
		$hash_secret= "VirtueMartIsCool";
		$user_info_id= empty($result) ? md5(uniqid($hash_secret)) : $result;
		$vendor_id= '1';
		$order['user_id']= $member_id;
		$order['vendor_id']= $vendor_id;
		$order['user_info_id']= $user_info_id;
		$order['order_number']= $order_number;
		$order['order_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$order['order_status']= 'C';
		$order['cdate']= time();
		$order['ip_address']= $ip;
		$keys= array_keys($order);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($order as $key => $value) {
			$order[$key]= $db->Quote($value);
		}
		$values= implode(',', $order);
		$query= "INSERT INTO `#__vm_orders` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the #__vm_order_history table
		$order_id= $db->insertid();
		$history= array();
		$history['order_id']= $order_id;
		$history['order_status_code']= 'C';
		$history['date_added']= date("Y-m-d G:i:s", time());
		$history['customer_notified']= '1';
		$keys= array_keys($history);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($history as $key => $value) {
			$history[$key]= $db->Quote($value);
		}
		$values= implode(',', $history);
		$query= "INSERT INTO `#__vm_order_history` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the Order payment info
		$payment= array();
		$payment['order_id']= $order_id;
		$payment['payment_method_id']= $payment_method;
		if($payment_method == 'authorize') {}
		//Insert the User Bill
		$bill= array();
		$query= " SELECT * FROM `#__osemsc_billinginfo`"." WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$billInfo= oseDB :: loadItem();
		if(isset($billInfo)) {
			$bill['company']= $billInfo['company'];
			$bill['address_1']= $billInfo['addr1'];
			$bill['address_2']= $billInfo['addr2'];
			$bill['city']= $billInfo['city'];
			$bill['state']= $billInfo['state'];
			$bill['country']= $billInfo['country'];
			//get vm country code
			$query= " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$bill['country']}' ";
			$db->setQuery($query);
			$country_code= $db->loadResult();
			$bill['country']= empty($country_code) ? $bill['country'] : $country_code;
			//get vm state code
			$query= " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$bill['state']}' ";
			$db->setQuery($query);
			$state_code= $db->loadResult();
			$bill['state']= empty($state_code) ? $bill['state'] : $state_code;
			$bill['zip']= $billInfo['postcode'];
			$bill['phone_1']= $billInfo['telephone'];
		}
		$query= " SELECT * FROM `#__osemsc_userinfo_view`"." WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$userInfo= oseDB :: loadItem();
		$bill['order_id']= $order_id;
		$bill['user_id']= $member_id;
		$bill['address_type']= 'BT';
		$bill['address_type_name']= '-default-';
		$bill['last_name']= $userInfo['lastname'];
		$bill['first_name']= $userInfo['firstname'];
		$bill['user_email']= $userInfo['email'];
		$keys= array_keys($bill);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($bill as $key => $value) {
			$bill[$key]= $db->Quote($value);
		}
		$values= implode(',', $bill);
		$query= "INSERT INTO `#__vm_order_user_info` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		//Insert the itme table(#__vm_order_item)
		$item= array();
		$item['order_id']= $order_id;
		$item['user_info_id']= $user_info_id;
		$item['vendor_id']= $vendor_id;
		//get the product info
		$vm= oseMscAddon :: getExtInfo($msc_id, 'vm', 'obj');
		$query= " SELECT * FROM `#__vm_product` WHERE `product_id` = '{$vm->product_id}' ";
		$db->setQuery($query);
		$product= $db->loadObject();
		$item['product_id']= $vm->product_id;
		$item['order_item_sku']= $product->product_sku;
		$item['order_item_name']= $product->product_name;
		$item['product_quantity']= '1';
		$item['product_item_price']= $order_subtotal;
		$item['product_final_price']= $order_total;
		$item['order_item_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$item['order_status']= 'C';
		$item['cdate']= time();
		;
		$keys= array_keys($item);
		$keys= '`'.implode('`,`', $keys).'`';
		foreach($item as $key => $value) {
			$item[$key]= $db->Quote($value);
		}
		$values= implode(',', $item);
		$query= "INSERT INTO `#__vm_order_item` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);
		if(!oseDB :: query()) {
			$result= array();
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error');
		}
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= JText :: _('Done');
		return $result;
	}
	*/

	public static function AddVmOrder($msc_id,$member_id,$params,$paymentInfo)
	{
	  	$order_number = $params['order_number'];
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


		$payment_mode = $params['payment_mode'];
		$payment_method = $params['payment_method'];

		//Insert the vm order table(#__vm_orders)
		$order =array();
		//get membership price
		$payment = oseRegistry::call('payment');
		//$paymentInfo = oseMscAddon::getExtInfo($msc_id,'payment','obj');

		if($payment_mode == 'm')
		{
			$order_subtotal = $paymentInfo->price;
		}
		else
		{
			$order_subtotal = (empty($paymentInfo->has_trial))?$paymentInfo->a3:$paymentInfo->a1;
		}

		$order['order_subtotal'] = $params['payment_price'];//$order_subtotal;

		$payment = oseRegistry::call('payment');


		$order_total = $order['order_subtotal'];//$payment->pricing($price,$msc_id,$osePaymentCurrency);

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
		$order['order_currency'] = $params['payment_currency'];
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
		//oseExit($msc_id);
		$vm = oseRegistry::call('msc')->getExtInfo($msc_id,'vm','obj');
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

	public static function getMscList()
	{
		$where[] = "`published` = 1 AND `leaf` =1";
		$where = oseDB::implodeWhere($where);

		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_acl`"
				. $where
				." ORDER BY lft"
				;
		$db->setQuery($query);
		$objs = oseDB::loadList();

		$combo = array();
    	$combo['total'] = count($objs);
    	$combo['results'] = $objs;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	public static function getMscOptions()
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$msc = oseRegistry::call('msc');
		$node = $msc->getInfo($msc_id,'obj');
		$paymentInfos = $msc->getExtInfo($msc_id,'payment');

		$option = array();

		foreach($paymentInfos as  $paymentInfo)
		{
			//$node = oseRegistry::call('payment')->getInstance('View')->getPriceStandard($node,$paymentInfo,$osePaymentCurrency);
			$optionPrice = $paymentInfo['p3'].' '.$paymentInfo['t3'];

			if(oseObject::getValue($paymentInfo,'has_trial'))
			{
				//$node = oseRegistry::call('payment')->getInstance('View')->getPriceTrial($node,$paymentInfo,$osePaymentCurrency);
				$optionPrice .= ' ( trial: '.$paymentInfo['recurrence_num'].' '.$paymentInfo['recurrence_unit'].')';
			}

			$option[] = array('id'=>oseObject::getValue($paymentInfo,'id'),'text'=>$optionPrice);
		}

		$combo = array();
    	$combo['total'] = count($option);
    	$combo['results'] = $option;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	public static function juserRegister($juser)
	{
		$result = array();

		$config = JFactory::getConfig();
		$params = JComponentHelper::getParams('com_users');

		$newUserType = self::getNewUserType($params->get('new_usertype'));
		$juser['gid']=$newUserType;
		$data = (array)self::getJuserData($juser);
		// Initialise the table with JUser.
		$user = new JUser;

		foreach ($juser as $k => $v) {
			$data[$k] = $v;
		}

		// Prepare the data for the user object.
		//$data['email']		= $data['email1'];
		//$data['password']	= $data['password1'];
		$useractivation = $params->get('useractivation');

		// Check if the user needs to activate their account.
		/*if (($useractivation == 1) || ($useractivation == 2)) {
			jimport('joomla.user.helper');
			$data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}*/

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('user');

		if(!$user->save()) {
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _($user->getError());
			$result = oseJson::encode($result);
			oseExit($result);
		} else {
			$result['success']= true;
			$result['user'] = $user;
			$result['title']= 'Done';
			$result['content']= 'Juser saved successfully';

			// Compile the notification mail values.
			$data = $user->getProperties();
			$data['fromname']	= $config->get('fromname');
			$data['mailfrom']	= $config->get('mailfrom');
			$data['sitename']	= $config->get('sitename');
			$data['siteurl']	= JUri::base();

			/*if (JOOMLA16==true)	{
				// Handle account activation/confirmation emails.
				if ($useractivation == 2)
				{
					// Set the link to confirm the user email.
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
					$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

					$emailSubject	= JText::sprintf(
						'COM_USERS_EMAIL_ACCOUNT_DETAILS',
						$data['name'],
						$data['sitename']
					);

					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				}
				else if ($useractivation == 1)
				{
					// Set the link to activate the user account.
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
					$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

					$emailSubject	= JText::sprintf(
						'COM_USERS_EMAIL_ACCOUNT_DETAILS',
						$data['name'],
						$data['sitename']
					);

					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				} else {

					$emailSubject	= "";

					$emailBody = "";
				}

				// Send the registration email.
				if (!empty($emailSubject) && !empty($emailBody))
				{
					$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
				}
				else
				{
					$return = true;
				}
				// Check for an error.
				if ($return !== true) {
					$this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

					// Send a system message to administrators receiving system mails
					$db = JFactory::getDBO();
					$q = "SELECT id
						FROM #__users
						WHERE block = 0
						AND sendEmail = 1";
					$db->setQuery($q);
					$sendEmail = $db->loadResultArray();
					if (count($sendEmail) > 0) {
						$jdate = new JDate();
						// Build the query to add the messages
						$q = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `date_time`, `subject`, `message`)
							VALUES ";
						$messages = array();
						foreach ($sendEmail as $userid) {
							$messages[] = "(".$userid.", ".$userid.", '".$jdate->toMySQL()."', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
						}
						$q .= implode(',', $messages);
						$db->setQuery($q);
						$db->query();
					}
					//return false;
				}

				if ($useractivation == 1)
				{
					$result['user_active'] =  "useractivate";
				}
				else if ($useractivation == 2)
				{
					$result['user_active'] = "adminactivate";
				}
				else
				{
					$result['user_active'] = null;
				}
			}
			else
			{
				if ($useractivation == 1)
				{
					// Set the link to activate the user account.
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
					$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

					$emailSubject	= JText::sprintf(
						'COM_USERS_EMAIL_ACCOUNT_DETAILS',
						$data['name'],
						$data['sitename']
					);

					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'].'index.php?option=com_user&task=activate&activation='.$data['activation'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
					// Send the registration email.
					if (!empty($emailSubject) && !empty($emailBody))
					{
						$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
					}
					else
					{
						$return = true;
					}
				}
			}*/

		}

		return $result;
	}

	public static function getNewUserType($newusertype)
	{
		if (JOOMLA16==true)
		{
			return $newusertype;
		}
		else
		{
			$authorize= JFactory :: getACL();
			return  $authorize->get_group_id('', $newusertype, 'ARO');
		}
	}

	static function isUserAdmin($user)
	{
		if(JOOMLA16)
		{
			$db =JFactory::getDBO();
			$db->setQuery("SELECT id FROM #__usergroups");
			$groups = $db->loadObjectList();

			$admin_groups = array();
			foreach ($groups as $group)
			{
				if (JAccess::checkGroup($group->id, 'core.login.admin'))
				{
					$admin_groups[] = $group->id;
				}
				elseif (JAccess::checkGroup($group->id, 'core.admin'))
				{
					$admin_groups[] = $group->id;
				}
			}
			$admin_groups = array_unique($admin_groups);
			$user_groups = JAccess::getGroupsByUser($user->id);

			if (count(array_intersect($user_groups, $admin_groups))>0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if($user->get('gid') == '24' ||  $user->get('gid') == '25')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	public static function getJuserData($temp)
	{
		$data = null;
		if ($data === null) {

			$data	= new stdClass();
			$app	= JFactory::getApplication();
			$params	= JComponentHelper::getParams('com_users');

			// Override the base user data with any data in the session.

			// Get the groups the user should be added to after registration.
			$data->groups = isset($data->groups) ? array_unique($data->groups) : array();

			// Get the default new user group, Registered if not specified.
			$system	= $params->get('new_usertype', 2);

			$data->groups[] = $system;

			// Unset the passwords.
			unset($data->password1);
			unset($data->password2);

			// Get the dispatcher and load the users plugins.
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('user');

			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $data));

			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$data = false;
			}
		}

		return $data;
	}

	function processPayment( $orderInfo)
	{
		return oseRegistry::call('payment')->getInstance('Order')->confirmOrder($orderInfo->order_id);
	}

	function getPrimaryCurrency()
	{
		return oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency;
	}

	static function getCart()
	{
		$cart = oseRegistry::call('payment')->getInstance('Cart');
		return $cart;
	}

	function generateOrder($member_id,$payment_method,$orderPaymentInfo)
	{
		$result = array();

    	if(empty($member_id))
    	{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

		$paymentOrder = oseRegistry::call('payment')->getInstance('Order');

    	$params = array();

		$items = $orderPaymentInfo['items'];
		unset($orderPaymentInfo['items']);

		$order_number = $paymentOrder->generateOrderNumber( $member_id );

		$orderPaymentInfo['order_number'] = $order_number;
		$orderPaymentInfo['entry_type'] = 'msc_list';
        $orderPaymentInfo['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
		$orderPaymentInfo['payment_serial_number'] = substr($orderPaymentInfo['order_number'],0,20);
		$orderPaymentInfo['payment_method'] = 'system';
		$orderPaymentInfo['payment_from'] = 'system_admin';
		$orderPaymentInfo['payment_mode'] = 'm';
		oseObject::setParams($orderPaymentInfo,array('time_stamp'=>uniqid("{$member_id}_",true)));
		// Extra Order Params Updating Function
		$list = oseMscAddon :: getAddonList('register_order', false, 1, 'obj');
    	foreach($list as $addon) {
			$action_name= 'register_order.'.$addon->name.'.add';
			//echo $action_name;
			$params = oseMscAddon :: runAction($action_name, $orderPaymentInfo['params'],true,false);
		}

		// generate Order

		$updated = $paymentOrder->generateOrder('', $member_id, $orderPaymentInfo);

		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
		}


		// generate orer item
		// in the backend, only manual payment
		$order_id = $result['order_id'] = $updated;
		$payment_mode = 'm';
		foreach($items as $item)
		{
			$itemParams = array();

			$entry_type = oseObject::getValue($item,'entry_type');

			switch($entry_type)
			{
				case('license'):
					$license_id = oseObject::getValue($item,'entry_id');

					$license = oseRegistry::call('lic')->getInstance(0);
					$licenseInfo = $license->getKeyInfo($license_id,'obj');
					//oseExit($item);
					$licenseInfoParams = oseJson::decode($licenseInfo->params);

					$msc_id = $licenseInfoParams->msc_id;
				break;

				case('msc'):
					$msc_id = oseObject::getValue($item,'entry_id');
				break;
			}
			$msc_option = oseObject::getValue($item,'msc_option');

			if ( oseObject::getValue($item,'eternal'))
	        {
	        	$itemParams['payment_mode'] = 'm';
	        }
	        else
	        {
	        	$itemParams['payment_mode'] = 'm';
	        }

			$price = oseObject::getValue($item,'a3');
			if($payment_mode == 'a')
			{
				if(oseObject::getValue($item,'has_trial'))
				{
					$price = oseObject::getValue($item,'a1');
				}
			}

			//$price = $payment->pricing($price,$msc_id,$osePaymentCurrency);

			$itemParams['entry_type'] = oseObject::getValue($item,'entry_type');
			$itemParams['payment_price'] = $orderPaymentInfo['payment_price'];//oseObject::getValue($item,'first_raw_price');
			$itemParams['payment_currency'] = $orderPaymentInfo['payment_currency'];

	        $itemParams['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");

	        $price_params = $paymentOrder->generateOrderParams($msc_id,$price,$payment_mode,$msc_option);
	        $price_params['recurrence_mode'] = oseObject::getValue($item,'recurrence_mode','period');
	        $price_params['start_date'] = oseObject::getValue($item,'start_date',null);
			$price_params['expired_date'] = oseObject::getValue($item,'expired_date',null);
	        $itemParams['params'] = oseJSON::encode($price_params);

	        $paymentInfos = oseMscAddon::getExtInfo($msc_id,'payment','obj');
	        $paymentInfo = oseObject::getValue($paymentInfos,$msc_option);

			$updated = $paymentOrder->generateOrderItem($order_id,oseObject::getValue($item,'entry_id'), $itemParams);
		}

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText :: _('Done');
			$result['content'] = JText :: _('Done');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Order Generate Error');
		}

		return $result;
	}

	static public function getList()
	{
		$where = array();

		$where[] = "`published` = 1";
		$where[] = "`leaf` =1";
		$where = oseDB::implodeWhere($where);

		$db = oseDB::instance();
		$query = " SELECT id,title,alias,description,ordering,image,params"
				." FROM `#__osemsc_acl`"
				. $where
				." ORDER BY lft ASC"
				;
		$db->setQuery($query);
		$objs = oseDB::loadList();

		return $objs;
	}

	static public function generatePriceOption($node,$paymentInfos,$osePaymentCurrency)
	{
		$option = array();
		$i = 0;
		foreach($paymentInfos as  $paymentInfo)
		{
			//$node = oseRegistry::call('payment')->getInstance('View')->getPriceTrial($node,$paymentInfo,$osePaymentCurrency);
			//$node = oseRegistry::call('payment')->getInstance('View')->getPriceStandard($node,$paymentInfo,$osePaymentCurrency);
			$node = oseRegistry::call('payment')->getInstance('View')->getMscInfo(oseObject::getValue($node,'id'),$osePaymentCurrency,oseObject::getValue($paymentInfo,'id'));

			$option[$i] = array(
						'id'=>oseObject::getValue($paymentInfo,'id')
						,'msc_id' => oseObject::getValue($node,'id')
						,'title'=> oseObject::getValue($node,'standard_recurrence')." ".'Paid Membership'//JText::_('PAID_MEMBERSHIP'))
						,'has_trial' => oseObject::getValue($paymentInfo,'has_trial')
						,'trial_price' => oseObject::getValue($node,'trial_price')
						,'standard_price' => oseObject::getValue($node,'standard_price')
						,'trial_recurrence' => oseObject::getValue($node,'trial_recurrence')
						,'standard_recurrence' => oseObject::getValue($node,'standard_recurrence')
						);
			if(!oseObject::getValue($paymentInfo,'optionname',false))
            {
				if (($option[$i]['standard_recurrence']==' ' && $option[$i]['trial_recurrence']==' ' && oseObject::getValue($paymentInfo,'recurrence_mode') != 'fixed') || oseObject::getValue($paymentInfo,'eternal',false))
				{
					if (oseObject::getValue($paymentInfo,'isFree')==true)
					{
						$option[$i]['title'] = 'Lifetime Membership';//JText::_('LIFETIME_FREE_MEMBERSHIP');
					}
					else
					{
						$option[$i]['title'] = 'Lifetime Membership';//JText::_('LIFETIME_MEMBERSHIP');
					}
				}
				else
				{
					if (oseObject::getValue($paymentInfo,'isFree')==true)
					{
						$option[$i]['title'] = oseObject::getValue($node,'standard_recurrence')." ".'Free Membership';//JText::_('FREE_MEMBERSHIP'));
					}
					else
					{
						//$option[$i]['title'] = ucfirst(oseObject::getValue($node,'standard_recurrence').JText::_(' Free Membership'));
					}
	
					if (oseObject::getValue($node,'standard_raw_price')=='0.00' && oseObject::getValue($node,'trial_raw_price')=='0.00')
					{
						//$option[$i]['title'] = ucfirst(oseObject::getValue($node,'standard_recurrence').JText::_(' Free Membership'));
					}
	
				}
            }else
            {
                $option[$i]['title'] = oseObject::getValue($paymentInfo,'optionname');
            	if (($option[$i]['standard_recurrence']==' ' && $option[$i]['trial_recurrence']==' ' && oseObject::getValue($paymentInfo,'recurrence_mode') != 'fixed') || oseObject::getValue($paymentInfo,'eternal',false))
                {
                	 $option[$i]['standard_recurrence'] = JText::_('LIFETIME');
                }
            }	

			//$option[$i]['title'] = ucwords(strtolower($option[$i]['title']));
			$i++;
		}

		return $option;
	}
}
?>