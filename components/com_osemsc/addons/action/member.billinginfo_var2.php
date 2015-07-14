<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberBillinginfo_var2
{
	public static function getItem($params = array())
	{
		$user = JFactory::getUser();
    	$member_id = $user->id;

		$member = oseRegistry::call('member');

		$member->instance($member_id);
		$info = $member->getBillingInfo();

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
		
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_('Saved User Billing Info.');
			
    	$post = JRequest::get('post');

    	$user = JFactory::getUser();
    	$member_id = $user->id;

    	if(!empty($post['juser_firstname']))
    	{
    		$post['bill_firstname'] = $post['juser_firstname'];
    	}

    	if(!empty($post['juser_lastname']))
    	{
    		$post['bill_lastname'] = $post['juser_lastname'];
    	}

		$billinfo = array();
		foreach($post as $key => $value)
		{
			if(strstr($key,'bill_'))
			{
				$billKey = str_replace('bill_','',$key);
				$billinfo[$billKey] = $value;
			}
		}

		if(empty($billinfo))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Nothing need to save!');
		}
		//oseExit($billinfo);
    	$db = oseDB::instance();
    	//$member_id = JRequest::getInt('member_id',0);
    	$query = " SELECT count(*) FROM `#__osemsc_billinginfo` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();

    	if (empty ($num)) {
    		$keys = array_keys($billinfo);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_billinginfo');

    		foreach($billinfo as $key => $value)
    		{
    			$my = JFactory::getUser();

    			if(!isset($fields['#__osemsc_billinginfo'][$key]))
				{
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!oseDB::query())
					{
						$result['success'] = false;
						$result['title'] = JText::_('Error');
						$result['content'] = JText::_('Fail Saving Joomla User Info.');
					}
				}

    		}

    		foreach($billinfo as $key => $value)
    		{
    			$billinfo[$key] = $db->Quote($value);
    		}

    		$values = implode(',',$billinfo);

			$query = "INSERT INTO `#__osemsc_billinginfo` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
			
			$db->setQuery($query);
			
			if (!oseDB::query()) {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_('Fail Saving Joomla User Info.');
	
			}
		} else {
			if(count($billinfo) > 0)
			{
				foreach($billinfo as $key => $value)
	    		{
	    			$billinfo[$key] = "`{$key}`=".$db->Quote($value);
	    		}
	
	    		$values = implode(',',$billinfo);
	
				$query = " UPDATE `#__osemsc_billinginfo` SET {$values}"
						." WHERE `user_id` ={$member_id}"
						;
				$db->setQuery($query);
				
				if (!oseDB::query()) {
					$result['success'] = false;
					$result['title'] = JText::_('Error');
					$result['content'] = JText::_('Fail Saving Joomla User Info.');
		
				}
			}
				
		}
		//echo $query;exit;
		
    	return $result;

    }

    public static function licSave($params = array())
    {
    	$post = $params;

    	$my = JFactory::getUser();

    	$member_id = $post['member_id'];

		$billinfo = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'bill_'))
			{
				$billKey = str_replace('bill_','',$key);
				$billinfo[$billKey] = $value;
			}
		}
		//oseExit($billinfo);
    	$db = oseDB::instance();
    	$member_id = JRequest::getInt('member_id',0);
    	$query = " SELECT count(*) FROM `#__osemsc_billinginfo` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();

    	if (empty ($num)) {
    		$keys = array_keys($billinfo);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_billinginfo');

    		foreach($billinfo as $key => $value)
    		{
				if(!isset($fields['#__osemsc_billinginfo'][$key]))
				{
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
				}
    		}

    		foreach($billinfo as $key => $value)
    		{
    			$billinfo[$key] = $db->Quote($value);
    		}

    		$values = implode(',',$billinfo);

			$query = "INSERT INTO `#__osemsc_billinginfo` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
		} else {

			foreach($billinfo as $key => $value)
    		{
    			$billinfo[$key] = "`{$key}`=".$db->Quote($value);
    		}

    		$values = implode(',',$billinfo);

			$query = " UPDATE `#__osemsc_billinginfo` SET {$values}"
					." WHERE `user_id` ={$member_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);

    	return oseDB::query();

    }

}
?>
