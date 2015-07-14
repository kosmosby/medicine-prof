<?php
defined('_JEXEC') or die(";)");
class osePaymentOrderOS extends osePaymentOrder
{
	protected $tableOrder = '#__ose_payment_order';
	protected $tableOrderItem = '#__ose_payment_order_item';
	
	function __construct()
	{
		
	}

	function generateOrder($msc_id, $user_id, $params= array())
	{
		$db = oseDB :: instance();
		//$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		//$params['payment_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$params['create_date']=(empty($params['create_date'])) ? oseHTML :: getDateTime() : $params['create_date'];
		$keys= array_keys($params);
		$keys= '`'.implode('`,`', $keys).'`';
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= $db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " INSERT INTO `{$this->table}` "." (`user_id`,`entry_id`,{$keys}) "." VALUES "." ( '{$user_id}', '{$msc_id}', {$values})";
		$db->setQuery($query);

		if(oseDB :: query()) {
			$order_id= $db->insertid();
			$orderParams= $this->autoOrderParams($params['payment_mode'], $params['params']);

			$this->updateOrder($order_id, 'pending', array('params' => $orderParams, 'payment_mode' => $params['payment_mode']));
			return $order_id;
		} else {
			return false;
		}
	}

	function generateOrderItem($order_id,$entry_id, $params= array()) {
		$db = oseDB :: instance();
		//$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'obj');
		//$params['payment_currency']=(!empty($payment->currency)) ? $payment->currency : "USD";
		$params['create_date']=(empty($params['create_date'])) ? oseHTML :: getDateTime() : $params['create_date'];
		$keys= array_keys($params);
		$keys= '`'.implode('`,`', $keys).'`';
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= $db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " INSERT INTO `{$this->table}_item` "." (`order_id`,`entry_id`,{$keys}) "." VALUES "." ('{$order_id}' , '{$entry_id}', {$values})";
		$db->setQuery($query);

		if(oseDB :: query()) {
			$order_id= $db->insertid();
			//$orderParams= $this->autoOrderParams($params['payment_mode'], $params['params']);
			//$this->updateOrder($order_id, 'pending', array('params' => $orderParams, 'payment_mode' => $params['payment_mode']));
			return $order_id;
		} else {
			return false;
		}
	}


	function generateOrderNumber($user_id) {
		$order_number= $user_id."_".self :: randStr(28, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890");
		return $order_number;
	}

	function generateOrderParams($msc_id, $price, $payment_mode, $msc_option) {
		$params= array();
		$params['msc_id']= $msc_id;
		if($payment_mode == 'a') {
			$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'array');
			$payment=$payment[$msc_option];
			if(oseObject::getValue($payment,'has_trial')) {
				$params['has_trial']= 1;
				$params['a1']= $price;
				$params['p1']= oseObject::getValue($payment,'p1');
				$params['t1']= oseObject::getValue($payment,'t1');
				$params['a3']= oseObject::getValue($payment,'a3');
				$params['p3']= oseObject::getValue($payment,'p3');
				$params['t3']= oseObject::getValue($payment,'t3');
			} else {
				$params['has_trial']= 0;
				$params['a3']= $price;
				$params['p3']= oseObject::getValue($payment,'p3');
				$params['t3']= oseObject::getValue($payment,'t3');
			}
		}

		if($payment_mode == 'm') {
			$payment= oseMscAddon :: getExtInfo($msc_id, 'payment', 'array');
			$payment=$payment[$msc_option];

			$params['recurrence_mode'] = 'period';
			$params['a3']= $price;
			$params['p3']= oseObject::getValue($payment,'p3');
			$params['t3']= oseObject::getValue($payment,'t3');
			$params['eternal']= oseObject::getValue($payment,'eternal');
		}

		$params['msc_option'] = $msc_option;
		return $params;
	}

	function getErrorMessage($paymentMethod, $code, $message = null)
	{
		$return = array();
		$return['payment']= $paymentMethod;
		$return['success']= false;
		$return['title']= JText :: _('Error');
		switch($code)
		{
			case '0000':
			$return['content']= $message;
			break;
			case '0001':
			$return['content']= JText :: _("This transaction utilizes Authorize.net as the payment processor, which does not support non-USD currency. Please choose USD as your payment currency.");
			break;
			case '0002':
			$return['content']= JText :: _("Please check your membership setting. Membership Price cannot be empty.");
			break;
			case '0003':
			$return['content']= JText :: _("Authorize.net is not enabled, please enable it through OSE backend.");
			break;

		}
		return $return;
	}
	
	function getItemid() {
		$db= oseDB :: instance();
		$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=confirm%'";
		$db->setQuery($query);
		$Itemid= $db->loadResult();
		if(empty($Itemid)) {
			$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=register%'";
			$db->setQuery($query);
			$Itemid= $db->loadResult();
		}
		return $Itemid;
	}
	
	function getProfileID($order_number)
	{
		$db= oseDB :: instance();
		$query = "SELECT `payment_serial_number` FROM `#__osemsc_order` WHERE `order_number`= '{$order_number}'";
		$db->setQuery($query);
		$ProfileID = $db->loadResult();
		return $ProfileID;
	}
	
	function getOrder($where= array(), $type= 'array') {
		$db= oseDB :: instance();
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT * FROM `{$this->table}` ".$where.' ORDER BY create_date DESC'.' LIMIT 1';
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}

	


	function autoOrderParams($payment_mode= 'a', $orderParams, $isNew= true, $status= 'confirmed')
	{
		$params= array();
		$orderParams= oseJson :: decode($orderParams);
		if($payment_mode == 'a') {
			if($isNew || $status == 'confirmed') {
				if(!isset($orderParams->recurrence_times))
				{
					if($orderParams->has_trial) {
						$recurrence_times= 0;
					} else {
						$recurrence_times= 1;
					}
					$orderParams->recurrence_times= $recurrence_times;
				} else {
					$orderParams->recurrence_times += 1;
				}
			}
		} else {}
		return oseJson :: encode($orderParams);
	}
	function randStr($length= 32, $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length=(strlen($chars) - 1);
		// Start our string
		$string= $chars {
			rand(0, $chars_length)
			};
		// Generate random string
		for($i= 1; $i < $length; $i= strlen($string)) {
			// Grab a random character from our list
			$r= $chars {
				rand(0, $chars_length)
				};
			// Make sure the same two characters don't appear next to each other
			if($r != $string {
				$i -1 })
			$string .= $r;
		}
		// Return the string
		return $string;
	}

	function updateOrder($order_id, $status, $params= array())
	{
		$db= oseDB :: instance();
		$params['order_status']= $status;

		/*
		if(isset($params['params'])) {
			$params['params']= $this->autoOrderParams($params['payment_mode'], $params['params'], false, $status);
		} else {
			$orderInfo= $this->getOrder(array('order_id' => $order_id));
			$params['params']= $this->autoOrderParams($orderInfo['payment_mode'], $orderInfo['params'], false, $status);
		}
		*/
		$values= array();
		foreach($params as $key => $value) {
			$values[$key]= '`'.$key.'`='.$db->Quote($value);
		}
		$values= implode(',', $values);
		$query= " UPDATE `{$this->table}` "." SET {$values}"." WHERE order_id = {$order_id}";
		$db->setQuery($query);
		//oseExit($db->getQuery());
		if(oseDB :: query())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function updateOrderParams($orderInfo,$params)
	{
		$orderInfoParams = oseObject::getValue($orderInfo,'params');
		$orderInfoParams = oseJson::decode($orderInfoParams);


		if(!is_Array($params))
		{
			$params = (array)$params;
		}

		foreach($params as $key => $value)
		{
			$orderInfoParams = oseObject::setValue($orderInfoParams,$key,$value);
		}

		$orderInfoParams = oseJson::encode($orderInfoParams);

		$orderInfo = oseObject::setValue($orderInfo,'params',$orderInfoParams);

		return $orderInfo;

	}

	function updateMembership($msc_id, $user_id, $order_id, $payment_mode)
	{
		$db= oseDB :: instance();
		$params['order_id'] = $order_id;
		$params['payment_mode'] = $payment_mode;
		$params = oseJSON::encode($params);
		$query= " UPDATE `#__osemsc_member` SET `params`='$params' WHERE `msc_id` = '{$msc_id}' AND `member_id` = '$user_id'";
		$db->setQuery($query);
		if(oseDB :: query()) {
			return true;
		} else {
			return false;
		}
	}

	function getOrderItem($where = array(),$type = 'array')
	{
		$db= oseDB :: instance();
		$where= oseDB :: implodeWhere($where);

		$where = str_replace('order_id','order_item_id',$where);
		$query= " SELECT * FROM `#__osemsc_order_item` "
				. $where
				.' ORDER BY create_date DESC'.' LIMIT 1'
				;
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}

	function getOrderItems($order_id,$type = 'array')
	{
		$db= oseDB :: instance();
		$where = array();
		$where[] = '`order_id`='.$db->Quote($order_id);
		$where= oseDB :: implodeWhere($where);

		$query= " SELECT * FROM `#__osemsc_order_item` "
				. $where
				.' ORDER BY create_date ASC'
				;
		$db->setQuery($query);
		$items= oseDB :: loadList($type);
		return $items;
	}

	function generateDesc($order_id)
	{
		return JText::_('Payment for Order :'.$order_id);
		/*
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$order_id}'"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj');

		$desc = array();
		foreach($items as $item)
		{
			switch($item->entry_type)
			{
				case('msc'):
					$msc_id = $item->entry_id;
				break;

				case('license');
					$itemParams = oseJson::decode($item->params);
					$msc_id = $itemParams->msc_id;
				break;
			}
			$node= oseRegistry :: call('msc')->getInfo($msc_id, 'obj');
			$desc[] = $node->title;
		}
		$desc = implode(',',$desc);

		return $desc;
		*/
	}

	function refundOrder($order_id,$params = array())
	{
		$db= oseDB::instance();

		$this->updateOrder($order_id, "refunded", $params);


		$where = array();
		$where[] = '`order_id` = '.$db->Quote($order_id);
		$orderInfo = $this->getOrder($where,'obj');

		$user_id =  $orderInfo->user_id;

		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$orderInfo->order_id}'"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj');

		foreach($items as $item)
		{
			switch($item->entry_type)
			{
				case('license'):
					$license = oseRegistry::call('lic')->getInstance(0);
					$licenseInfo = $license->getKeyInfo($item->entry_id,'obj');

					$licenseInfoParams = oseJson::decode($licenseInfo->params);

					$msc_id = $licenseInfoParams->msc_id;
					$updated= $this->cancelMsc($order_id,$item->order_item_id, $msc_id, $user_id);
				break;

				default:
				case('msc'):
					$updated = $this->cancelMsc($order_id,$item->order_item_id, $item->entry_id, $user_id);
				break;
			}

			if(!$updated['success'])
			{
				return $updated;
			}
		}

		$return['success']= true;
		$return['title']= JText :: _('Success');
		$return['content']= JText :: _('Refunded Successfully');

		$session = JFactory::getSession();
		$session->set('osecart',array());
		return $return;
	}

	private function cancelMsc($order_id,$order_item_id, $msc_id, $user_id)
	{
		$params= oseRegistry :: call('member')->getAddonParams($msc_id, $user_id, $order_id,array('order_item_id'=>$order_item_id));

		$member= oseRegistry :: call('member');
		$member->instance($user_id);

		$msc= oseRegistry :: call('msc');

		$ext = $msc->getExtInfo($msc_id,'msc','obj');

		$updated = $msc->runAddonAction('member.msc.cancelMsc', $params,true,false);
		/*
		if($ext->cancel_email)
		{

			$email = $member->getInstance('email');

			$emailTempDetail = $email->getDoc($ext->cancel_email,'obj');

			$variables = $email->getEmailVariablesCancel($user_id,$msc_id);

			$emailParams = $email->buildEmailParams($emailTempDetail->type);

			$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
		}

		if($ext->cancel_email)
		{
			$email->sendEmail($emailDetail,$userInfo->email);

			$emailConfig = oseMscConfig::getConfig('email','obj');
			if($emailConfig->sendCancel2Admin)
			{
				$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
			}
		}
		*/
		return $updated;
	}
}
?>