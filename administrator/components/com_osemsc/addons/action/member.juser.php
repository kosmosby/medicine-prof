<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberJuser extends oseMscAddon
{
	public static function getItem($params = array())
	{
		$member_id = JRequest::getInt('member_id',0);
		
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_userinfo_view` "
				." WHERE user_id = {$member_id}"
				;
		$db->setQuery($query);
		$info = oseDB::loadItem();
		
		if(empty($info))
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $info;
		}
		
		return $result;
	}
	
	public static function save()
    {
    	$result = array();
    	
    	$post = JRequest::get('post');
    	
    	$post['primary_contact'] = oseObject::getValue($post,'primary_contact',1);
    	
    	$member_id = JRequest::getInt('user_id',0);
		JRequest::setVar('member_id',$member_id);
		$array = array();
		$array['username'] = $post['username'];
		$array['name'] = $post['firstname'].' '.$post['lastname'];
		$array['password'] = $array['password1'] = $post['password'];
		$array['password2'] = $post['password2'];
		$array['email'] = $post['email'];
		
    	if(empty($member_id))
    	{
    		$isNew = true;
    	}
    	else
    	{
    		$isNew = false;
    	}
    	
    	$user_id = $member_id;
		$username = $array['username'];
		
		$updated = oseMscPublic::uniqueUserName($username,$user_id);
		
		if(!$updated['success'])
		{
			return $updated;
		}
		
		//$array['id'] = $user_id;
    	$uid = self::jvsave($member_id,$array);
		
		$member = oseRegistry::call('member');
    		
		$member->instance($member_id);
		$updated = $member->updateUserInfo($post);
		oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_SAVING_OSEMSC_USER_INFO');
			$result['member_id'] = '';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('SAVED_JOOMLA_USER_INFO');
		}
		
		return $result;
    	
    }

    private function jvsave($member_id,$post)
    {
    	if(empty($member_id))
    	{
    		$juser= $post;
	    	$register = oseMscPublic::juserRegister($juser);
	    	
	    	if(!$register['success'])
	    	{
	    		return false;
	    	}
	    	//oseExit($register);
	    	return $register['user']->id;
    	}
    	else
    	{
    		$user = JFactory::getUser($member_id);
    		if (!$user->bind($post)) {
				//$this->setError($user->getError());
				return false;
			}
	
			// Store the data.
			if (!$user->save()) {
				//$this->setError($user->getError());
				return false;
			}
			
			return $member_id;
    	}
	    	
    }
    
    function createUser()
    {
    	$post = JRequest::get('post');
    	
    	$member_id = JRequest::getInt('member_id',0);
		
		$password1 = JRequest :: getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$password2 = JRequest :: getString('password2', '', 'post', JREQUEST_ALLOWRAW);
		
		$array = array();
		$array['username'] = $post['username'];
		$array['name'] = $post['firstname'].' '.$post['lastname'];
		$array['password'] = $password1;
		$array['password2'] = $password2;
		$array['email'] = $array['email1'] = $post['email'];
		$array['email2'] = $post['email'];
		
    	$uid = self::jvsave($member_id,$array);
    	
    	if(empty($uid))
    	{
    		oseExit('dfdf');
    	}
    	$list = array();//oseMscAddon::getAddonList('usersync',true,null,'obj');
		$params = array();
		$params['member_id'] = $uid;
		$params['allow_work'] = true;
		
		foreach($list as $addon)
		{
			$action_name = 'usersync.'.$addon->name.'.juserSave';
			
			$result = oseMscAddon::runAction($action_name,$params);
			
			if(!$result['success'])
			{
				return $result;
			}
		}
    	
    	JRequest::setVar('member_id',$uid);
    	
    	$member = oseRegistry::call('member');
    		
		$member->instance($uid);
		$updated = $member->updateUserInfo($post);
		
		if(!$updated)
    	{
    		$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_SAVING_OSEMSC_USER_INFO');
			$result['member_id'] = '';
			
			return $result;
    	}
    	
		$updated = oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
    	
    	if(!$updated['success'])
    	{
    		return $updated;
    	}
    	else
    	{
    		$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('CREATE_SUCCESSFULLY');
			return $result;
    	}
    }
    
    function createMember()
    {
    	$created = self::createUser();
    	if($created['success'])
    	{
    		$member_id = JRequest::getInt('member_id',0);
    		$msc_id = JRequest::getInt('msc_id',0);
    		$msc_option = JRequest::getCmd('msc_option',null);
    		
    		if(empty($msc_id))
    		{
    			$result['success'] = false;
				$result['title'] = JText::_('PLEASE_SELECT_MEMBERSHIP_PLAN_FIRST');
				$result['content'] = JText::_('PLEASE_SELECT_ONE_PLAN_IN_THE_LEFT_SIDE_PANEL');
				
				return $result;
    		}
    		
    		if(empty($msc_option))
    		{
    			$result['success'] = false;
				$result['title'] = JText::_('PLEASE_SELECT_MEMBERSHIP_PLAN_FIRST');
				$result['content'] = JText::_('IF_THERE_IS_NO_OPTION_IN_THE_DROPDOWN_LIST_PLEASE_CREATE_ONE_IN_THE_PANEL_PAYMENT');
				
				return $result;
    		}
    		
    		$cart = oseMscPublic::getCart();
			$cart->addItem(null,null,array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option));
			$cart->refreshCartItems();
			$paymentInfo = $cart->output();
			
			if(!empty($msc_option))
			{
				$payment = oseRegistry::call('msc')->getExtInfo($msc_id,'payment','array');
				$payment = $payment[$msc_option];
				$price = oseObject::getValue($payment,'a3');
				$paymentInfo['payment_price'] = $price;
				$paymentInfo = oseObject::setParams($paymentInfo,array(
					'a3'=>$price,'total'=>$price,'next_total'=>0,'subtotal'=>$price
				));
			}else{
				$paymentInfo['payment_price'] = '0.00';
				$paymentInfo = oseObject::setParams($paymentInfo,array(
					'a3'=>0,'total'=>0,'next_total'=>0
				));
			}
			$result = oseMscPublic::generateOrder($member_id,'system_admin',$paymentInfo);

			if(!$result['success'])
			{
				return $result;
			}
	
			$orderInfo = oseRegistry::call('payment')->getInstance('Order')->getOrder(array("`order_id`='{$result['order_id']}'"),'obj');
	
			$updated = oseMscPublic::processPayment($orderInfo);
			
			if($updated['success'])
			{
				$result = $updated;
				$result['success'] = true;
				$result['title'] = JText::_('DONE');;
				$result['content'] = JText::_('ADDED_SUCCESSFULLY');
			}
			else
			{
				$result = $updated;
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');;
				$result['content'] = JText::_("MEMBER_ID")." :{$member_id} ".JText::_("FAILED_JOINING");
			}
			
			return  $result;
    	}
    	else
    	{
    		return $created;
    	}
    }
    
    function formValidate()
    {
    	$user_id = JRequest::getInt('member_id',0);
		
		$username = JRequest::getString('username',null);
		
		$result = array();
		
		$updated = oseMscPublic::uniqueUserName($username, $user_id);
		
		if($updated['success'])
		{
			$result['result'] = $updated['success'];
		}
		else
		{
			$result['result'] = JText::_('THIS_USERNAME_HAS_BEEN_REGISTERED_BY_OTHER_USER');
		}
		
		$result = oseJson::encode($result);
		
		oseExit($result);
    }
    
    
}
?>