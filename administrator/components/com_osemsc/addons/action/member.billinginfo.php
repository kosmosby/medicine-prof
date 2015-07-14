<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberBillinginfo extends oseMscAddon
{
	public static function getItem($params = array())
	{
		$member_id = JRequest::getInt('member_id',0);

		$member = oseRegistry::call('member');

		$member->instance($member_id);
		$info = $member->getBillingInfo();

		if(empty($info))
		{
			$result['success'] = false;
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['success'] = true;
			$result['total'] = 1;
			$result['result'] = $info;
		}

		return $result;
	}

	public static function save()
    {
    	$post = JRequest::get('post');

    	if(!empty($post['firstname']))
    	{
    		$post['bill_firstname'] = $post['firstname'];
    	}

    	if(!empty($post['lastname']))
    	{
    		$post['bill_lastname'] = $post['lastname'];
    	}

    	$member_id = JRequest::getInt('member_id',0);

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
    	$query = " SELECT count(*) FROM `#__osemsc_billinginfo` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();
    	$my = JFactory::getUser();
    	if (empty ($num)) {
    		$keys = array_keys($billinfo);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = oseDB::getDBFields('#__osemsc_billinginfo');

    		foreach($billinfo as $key => $value)
    		{

				if(!isset($fields['#__osemsc_billinginfo'][$key]))
				{
					if($my->get('gid') == 24 || $my->get('gid') == 25)
    				{
						$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
						$db->setQuery($query);
						if (!oseDB::query())
						{
							$result['success'] = false;
							$result['title'] = JText::_('ERROR');
							$result['content'] = JText::_('FAIL_SAVING_OSEMSC_USER_INFO');
						}
					}
					else
	    			{
	    				unset($billinfo[$key]);
	    			}
    			}

    		}

    		foreach($billinfo as $key => $value)
    		{
    			$billinfo[$key] = (!empty($value))?$db->Quote($value):null;
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

		if (!oseDB::query()) {
			$result = array();

			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('FAIL_SAVING_OSEMSC_USER_INFO');

		} else {
			$result = array();

			$result['success'] = true;
			$result['title'] = JText::_('DONE');;
			$result['content'] = JText::_('SAVED_USER_BILLING_INFO');

		}
    	return $result;

    }


}
?>