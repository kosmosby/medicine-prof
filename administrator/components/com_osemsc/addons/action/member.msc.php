<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberMsc extends oseMscAddon
{
	public static function getItems($params = array())
	{
		$member_id = JRequest::getInt('member_id',0);
		
		$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		$items = $member->getOwnMsc();
		
		$result = array();
		
		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		
		return $result;
	}
	
	public static function save()
    {
    	$post = JRequest::get('post');
    	
    	$member_id = JRequest::getInt('member_id',0);
    	
    	
    	return ''; 
    	
    }

    public static function joinMsc($params = array())
    {
    	$member_id = $params['member_id'];
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	
    	$db = oseDB::instance();
    	switch($params['join_from'])
    	{
    		case('lic'):
    			return self::joinFromLic($params);
    		break;
    		
    		case('payment'):
    		default:
    			return self::joinFromPayment($params);
    		break;
    	}
    }
    
    private static function joinFromLic($params)
    {
    	$db = oseDB::instance();
    	
		$msc = oseRegistry::call('msc');
		
		//$licInfo = $msc->getExtInfo($item->msc_id,'lic','obj');
		
		$member_id = $params['member_id'];
    	
    	$msc_id = $params['msc_id'];
    	
    	$result = array();
    	
    	if(empty($msc_id))
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('PLEASE_SELECT_A_MEMBER_FIRST');
			
			return $result;
    	}
    	
    	$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		
		$updated = $member->joinMsc($msc_id);
		
		if($updated)
		{
			$list = oseMscAddon::getAddonList('join',false,null,'obj');
			
			foreach($list as $addon)
			{
				$action_name = 'join.'.$addon->name.'.save';
				
				$result = oseMscAddon::runAction($action_name,$params);
				
				if(!$result['success'])
				{
					self::cancelMsc($params);
					return $result;
				}
			}
			
			
			
			$result = array();
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('JOINED_MEMBERSHIP');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_JOINING_MEMBERSHIP');
		}
		
    	return $result; 
    }
    
    private static function joinFromPayment($params)
    {
    	$db = oseDB::instance();
    	
		$msc = oseRegistry::call('msc');
		
		//$licInfo = $msc->getExtInfo($item->msc_id,'lic','obj');
		
		$member_id = $params['member_id'];
    	
    	$msc_id = $params['msc_id'];
    	
    	$order_id = $params['order_id'];
    	
    	$result = array();
    	
    	if(empty($msc_id))
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('PLEASE_SELECT_A_LEAF_MEMBERSHIP');
			
			return $result;
    	}
    	
    	$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		
		
		$updated = $member->joinMsc($msc_id);
		
		if($updated)
		{
			
			$list = oseMscAddon::getAddonList('join',true,1,'obj');
	
			foreach($list as $addon)
			{
				$action_name = 'join.'.$addon->name.'.save';
				echo $action_name.'<br>';
				$result = oseMscAddon::runAction('join.'.$addon->name.'.save',$params);
				//echo $action_name;
				if(!$result['success'])
				{
					self::cancelMsc($params);
					return $result;
				}
			}
			
			$userInfo = $member->getBasicInfo('obj');
			
			$ext = $msc->getExtInfo($msc_id,'msc','obj');
			if($ext->wel_email)
			{
				$email = $member->getInstance('email');
			
				$emailTempDetail = $email->getDoc($ext->wel_email,'obj');
				
				$variables = $email->getEmailVariablesWelcome($order_id,$msc_id);
				
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				
				$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
				
				$email->sendEmail($emailDetail,$userInfo->email);
				
				$emailConfig = oseMscConfig::getConfig('email','obj');
				if($emailConfig->sendWel2Admin)
				{
					$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
				}
			}
			
			$result = array();
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('JOINED_MEMBERSHIP');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_JOINING_MEMBERSHIP');
		}
		
    	return $result; 
    }
    
    public static function cancelMsc($params = array())
    {
    	$post = JRequest::get('post');
    	$result = array();
    	
    	if(empty($params))
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('ERROR');
			
			return $result;
    	}
    	
    	$member_id = $params['member_id'];
    	$msc_id = $params['msc_id'];
    
    	//$result = array();
    	
    	$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');
		//$member->getMemberInfo($msc_id,'obj');
		
		$updated = $member->cancelMsc($msc_id);
		
		// Email 1 => get Content First
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id,'msc','obj');
		
		if($ext->cancel_email)
		{
			$order_id = $params['order_id'];
			
			$email = $member->getInstance('email');
		
			$emailTempDetail = $email->getDoc($ext->cancel_email,'obj');
			
			$variables = $email->getEmailVariablesCancel($member_id,$msc_id);
			
			$emailParams = $email->buildEmailParams($emailTempDetail->type);
			
			$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
		}
		// Email 1 End
		
		if($updated)
		{
			$list = oseMscAddon::getAddonList('join',true,1,'obj');
			
			foreach($list as $addon)
			{
				$result = oseMscAddon::runAction('join.'.$addon->name.'.cancel',$params);
				
				if(!$result['success'])
				{
					return $result;
				}
			}
			
			// Email 2 => Send Out
			if($ext->cancel_email)
			{
				$email->sendEmail($emailDetail,$userInfo->email);
			
				$emailConfig = oseMscConfig::getConfig('email','obj');
				if($emailConfig->sendWel2Admin)
				{
					$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
				}
			}
			// Email 2 End
			
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('CANCELED_MEMBERSHIP');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_JOINING_MEMBERSHIP');
		}
		
    	return $result; 
    }
    
    function getMemRecurrence()
    {
    	$result = array();
    	
    	
    	$msc_member_id = JRequest::getInt('msc_member_id',0);
    	
    	$member = oseRegistry::call('member');
    	
    	$member->instance($msc_member_id,'msc_member_id');
    	
    	$mscInfo = $member->quickLoadMemberInfo('obj');
    	
    	
    	if(empty($mscInfo))
    	{
    		$result['total'] = 0;
			$result['result'] = array();
			return $result; 
    	}
    	
    	$start_date = date_create($mscInfo->start_date);
    	$expired_date = date_create($mscInfo->expired_date);
    	
    	$item = array();
    	$item['msc_member_id'] = $msc_member_id;
    	$item['start_date'] = date_format($start_date, 'Y-m-d');
    	$item['start_time'] = date_format($start_date, 'h:i A');
    	
    	$item['exp_date'] = date_format($expired_date, 'Y-m-d');
    	$item['exp_time'] = date_format($expired_date, 'h:i A');
    	
    	
		$result['total'] = 1;
		$result['result'] = $item;
		
		
    	return $result; 
    }
    
    function updateMemRecurrence()
    {
    	$result = array();
    	$db = oseDB::instance();
    	
    	$msc_member_id = JRequest::getInt('msc_member_id',0);
    	
    	$startDate = JRequest::getString('start_date',0);
    	$startTime = JRequest::getString('start_time',0);
    	$expDate = JRequest::getString('exp_date',0);
    	$expTime = JRequest::getString('exp_time',0);
    	
    	$start_date = date_create($startDate.$startTime);
    	$expired_date = date_create($expDate.$expTime);
    	
    	$start_date = date_format($start_date, 'Y-m-d H:i:s');
    	$expired_date = date_format($expired_date, 'Y-m-d H:i:s');
    	
    	$now = date_create(oseHtml::getDateTime());
    	$now = date_format($now, 'Y-m-d H:i:s');
    	$vals = array();
    	if($now > $expired_date)
    	{
    		$vals['status'] = 0;
    		$query = "SELECT * FROM `#__osemsc_member` WHERE `id` = ".$msc_member_id;
    		$db->setQuery($query);
    		$obj = oseDB :: loadItem('obj');
    		$msc_id = $obj->msc_id;
    		$member_id = $obj->member_id;
    		$member= oseRegistry :: call('member');
    		$member->instance($member_id);
    		$userInfo = $member->getUserInfo('obj');
    		$params = $member->getAddonParams($msc_id,$member_id,0,$params = array());
    		$list = oseMscAddon::getAddonList('join',true,1,'obj');			
			foreach($list as $addon)
			{
				$result = oseMscAddon::runAction('join.'.$addon->name.'.cancel',$params);
				if(!$result['success'])
				{
					return $result;
				}
			}
			
	    	$msc = oseRegistry::call('msc');
			$ext = $msc->getExtInfo($msc_id,'msc','obj');
	
			if(!empty($ext->exp_email))
			{
				$order_id = $params['order_id'];
	
				$email = $member->getInstance('email');
	
				$emailTempDetail = $email->getDoc($ext->exp_email,'obj');
	
				$variables = $email->getEmailVariablesExpire($member_id,$msc_id);
	
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
	
				$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
				
				$email->sendEmail($emailDetail,$userInfo->email);

				$emailConfig = oseMscConfig::getConfig('email','obj');
				if($emailConfig->sendExp2Admin)
				{
					$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
				}
			}
    	}
    	else
    	{
    		$vals['status'] = 1;
    	}
    	
    	
    	$db = oseDB::instance();
    	
    	
    	$vals['start_date'] = $start_date; 
    	$vals['expired_date'] = $expired_date; 
    	$vals['id'] = $msc_member_id; 
    	
    	
    	/*$query = " UPDATE `#__osemsc_member`"
    			." SET start_date = {$start_date}, expired_date = {$expired_date}"
    			." WHERE id = {$msc_member_id}"
    			;
    	
    	$db->setQuery($query);*/
    	
    	//if(oseDB::query())
    	if(oseDB::update('#__osemsc_member','id',$vals))
    	{
    		$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('UPDATED');
    	}
    	else
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('FAIL_UPDATING');
    	}
    	
    	return $result;
    	
    }
    
    public static function activateMsc($params = array())
    {
    	if(empty($params))
    	{
    		return array('success'=>false);
    	}
    	
    	$member_id = $params['member_id'];
    	
    	if(empty($member_id))
    	{
    		return false;
    	}
    	
    	$db = oseDB::instance();
    	
    	$msc = oseRegistry::call('msc');
		
		//$licInfo = $msc->getExtInfo($item->msc_id,'lic','obj');
		
		$member_id = $params['member_id'];
    	
    	$msc_id = $params['msc_id'];
    	
    	$order_id = $params['order_id'];
    	
    	$result = array();
    	
    	$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		
		$updated = $member->joinMsc($msc_id);
		
		if($updated)
		{
			$list = oseMscAddon::getAddonList('renew',true,1,'obj');
			
			foreach($list as $addon)
			{
				$action_name = 'renew.'.$addon->name.'.activate';
				//cho $action_name;
				$result = oseMscAddon::runAction($action_name,$params);
				
				if(!$result['success'])
				{
					self::cancelMsc($params);
					return $result;
				}
			}
			
			$userInfo = $member->getBasicInfo('obj');
			
			$ext = $msc->getExtInfo($msc_id,'msc','obj');
			if($ext->wel_email)
			{
				$email = $member->getInstance('email');
			
				$emailTempDetail = $email->getDoc($ext->wel_email,'obj');
				
				$variables = $email->getEmailVariablesWelcome($order_id,$msc_id);
				
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				
				$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
				
				$email->sendEmail($emailDetail,$userInfo->email);
				
				$emailConfig = oseMscConfig::getConfig('email','obj');
				if($emailConfig->sendWel2Admin)
				{
					$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
				}
			}
			
			$result = array();
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('JOINED_MEMBERSHIP');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_JOINING_MEMBERSHIP');
		}
		
    	return $result; 
    }
    
    public static function renewMsc($params = array())
    {
    	if(empty($params))
    	{
    		return array('success'=>false);
    	}
    	
    	$member_id = $params['member_id'];
    	
    	if(empty($member_id))
    	{
    		return array('success'=>false);
    	}
    	
    	$db = oseDB::instance();
    	
    	$msc = oseRegistry::call('msc');
		
		//$licInfo = $msc->getExtInfo($item->msc_id,'lic','obj');
		
		$member_id = $params['member_id'];
    	
    	$msc_id = $params['msc_id'];
    	
    	$order_id = $params['order_id'];
    	
    	$result = array();
    	
    	$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		
		$updated = $member->joinMsc($msc_id);
		
		if($updated)
		{
			$list = oseMscAddon::getAddonList('renew',true,1,'obj');
			
			foreach($list as $addon)
			{
				$action_name = 'renew.'.$addon->name.'.renew';
				//echo $action_name.'<br>';
				$result = oseMscAddon::runAction($action_name,$params);
				
				if(!$result['success'])
				{
					self::cancelMsc($params);
					return $result;
				}
			}
			
			$userInfo = $member->getBasicInfo('obj');
			
			$ext = $msc->getExtInfo($msc_id,'msc','obj');
			if($ext->wel_email)
			{
				$email = $member->getInstance('email');
			
				$emailTempDetail = $email->getDoc($ext->wel_email,'obj');
				
				$variables = $email->getEmailVariablesWelcome($order_id,$msc_id);
				
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				
				$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);
				
				$email->sendEmail($emailDetail,$userInfo->email);
				
				$emailConfig = oseMscConfig::getConfig('email','obj');
				if(!empty($emailConfig->sendWel2Admin))
				{
					$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
				}
			}
			
			$result = array();
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('JOINED_MEMBERSHIP');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_JOINING_MEMBERSHIP');
		}
		
    	return $result; 
    }
    
}
?>