<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinEmail extends oseMscAddon
{
	public static function save($params)
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
		
			$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$msc = oseRegistry::call('msc');
		$member = oseRegistry::call('member');
		$payment = oseRegistry::call('payment');
		
		$ext = $msc->getExtInfo($msc_id,'msc','obj');
		$member->instance($member_id);
		
		$userInfo = $member->getBasicInfo('obj');
		
		$order = new osePaymentOrder();
		
		$orderValues = array();
		
		$payment_mode = 'a';
		$price = '10';
		
		$orderValues['payment_method'] = 'paypal';
		$orderValues['order_number'] = $payment->generateOrderNumber($user->id);
		$orderValues['payment_price'] = $price;//$payment->pricing($msc_id,$payment_mode);
		$orderValues['payment_mode'] = $payment_mode;
		//$orderValues['create_date'] = oseHTML::getDateTime();
		
		$orderValues['params'] = $payment->generateOrderParams($msc_id,$price,$payment_mode);
		$orderValues['params'] = http_build_query($orderValues['params']);
		
		$orderId = $order->generateOrder($msc_id,$member_id,$orderValues);
		
		if(!$orderId)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join History: No Membership ");
		}
		
		$post = JRequest::get('post');
		
		$member = oseRegistry::call('member');
		
		$email = $member->getInstance('email');
		
		$emailTempDetail = $email->getDoc($ext->wel_email,'obj');
		
		$variables = $email->getEmailVariablesWelcome($orderId);
		
		$emailParams = $email->buildEmailParams($emailTempDetail->type);
		
		$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
		
		$email->sendEmail($emailDetail,$userInfo->email);
		
		$emailConfig = oseMscConfig::getConfig('email','obj');
		if($emailConfig->sendWel2Admin)
		{
			
			$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
		}
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join History: No Membership ");
		}
		
		if(!oseMemHistory::record($msc_id,$member_id,'join'))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(" Fail Record Member's Footprint! ");
		}
		
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
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		
		$msc = oseRegistry::call('msc');
		$member = oseRegistry::call('member');
		$payment = oseRegistry::call('payment');
		
		$ext = $msc->getExtInfo($msc_id,'msc','obj');
		$member->instance($member_id);
		
		$userInfo = $member->getBasicInfo('obj');
				
		$member = oseRegistry::call('member');
		
		$email = $member->getInstance('email');
		
		$emailTempDetail = $email->getDoc($ext->cancel_email,'obj');
		
		$variables = $email->getEmailVariablesCancel($member_id,$msc_id);
		
		$emailParams = $email->buildEmailParams($emailTempDetail->type);
		
		$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
		
		$email->sendEmail($emailDetail,$userInfo->email);
		
		$emailConfig = oseMscConfig::getConfig('email','obj');
		if($emailConfig->sendWel2Admin)
		{
			
			$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
		}

		return $result;
		
	}
	
	function delete($params = array())
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