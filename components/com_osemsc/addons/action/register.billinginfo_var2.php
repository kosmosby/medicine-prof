<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterBillinginfo_var2
{

	public static function save( $params )
    {
    	//$post = JRequest::get('post');

    	$member_id = $params['member_id'];

    	if(empty($member_id))
    	{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

		$post = JRequest::get('post');
		$bill = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'bill_'))
			{
				$billKey = preg_replace('/bill_/','',$key,1);
				$bill[$billKey] = $value;
			}
		}

		$bill['firstname'] = $post['juser_firstname'];
		$bill['lastname'] = $post['juser_lastname'];
		//oseExit($billinfo);
    	$db = oseDB::instance();

    	$query = " SELECT count(*) FROM `#__osemsc_billinginfo` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();
    	 //oseExit($db->_sql);
    	if (empty ($num)) {
    		$keys = array_keys($bill);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_billinginfo');

    		foreach($bill as $key => $value)
    		{
    			$my = JFactory::getUser();
    			if(oseMscPublic::isUserAdmin($my))
    			{
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

    		}
    		$values = array();

    		foreach($bill as $key => $value)
    		{
    			$values[] = $db->Quote($value);
    		}

    		$values = implode(',',$values);

			$query = "INSERT INTO `#__osemsc_billinginfo` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
		} else {

			foreach($bill as $key => $value)
    		{
    			$values[] = "`{$key}`=".$db->Quote($value);
    		}

    		$values = implode(',',$values);

			$query = " UPDATE `#__osemsc_billinginfo` SET {$values}"
					." WHERE `user_id` ={$member_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);

		if (!oseDB::query()) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Fail Saving Joomla User Info.');

		} else {


			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Saved User Billing Info.');

		}
    	return $result;

    }


}
?>
