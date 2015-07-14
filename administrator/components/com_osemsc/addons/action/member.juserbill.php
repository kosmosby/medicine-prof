<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberJuserbill extends oseMscAddon
{
	public static function getItem($params = array())
	{
		$member_id = JRequest::getInt('member_id',0);
		
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_userinfo_view` AS u"
				." INNER JOIN `#__osemsc_billinginfo` AS b"
				." ON u.`user_id` = b.`user_id`"
				." WHERE u.`user_id` = {$member_id}"
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