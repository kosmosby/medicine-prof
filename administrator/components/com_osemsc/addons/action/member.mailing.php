<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberMailing
{
	public static function getItem($params = array())
	{
		$user = JFactory::getUser();
    	$member_id = JRequest::getInt('member_id',0);

		$db= oseDB::instance();
		$query = "SELECT * FROM #__osemsc_mailing WHERE user_id = ". (int)$member_id;
		$db->setQuery($query);

		$info = $db->loadObject();

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

    	$member_id = JRequest::getInt('member_id');

		$mailing = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'mailing_'))
			{
				$mailingKey = preg_replace('/mailing_/','',$key,1);
				$mailing[$mailingKey] = $value;
			}
		}

		if(empty($mailing))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Nothing need to save!');
		}
		//oseExit($billinfo);
    	$db = oseDB::instance();
    	//$member_id = JRequest::getInt('member_id',0);
    	$query = " SELECT count(*) FROM `#__osemsc_mailing` WHERE user_id='{$member_id}'";
    	$db->setQuery($query);
    	$num = $db->loadResult();
    	 //oseExit($db->_sql);
    	if (empty ($num)) {
    		$keys = array_keys($mailing);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_mailing');

    		foreach($mailing as $key => $value)
    		{
    			$my = JFactory::getUser();
    			if(oseMscPublic::isUserAdmin($my))
    			{
    				if(!isset($fields['#__osemsc_mailing'][$key]))
					{
						$query = "ALTER TABLE `#__osemsc_mailing` ADD `{$key}` TEXT NULL DEFAULT NULL";
						$db->setQuery($query);
						if (!oseDB::query())
						{
							$result['success'] = false;
							$result['title'] = JText::_('Error');
							$result['content'] = JText::_('Fail Saving Mailing Info.');
						}
	    			}
    			}
    		}
    		$values = array();

    		foreach($mailing as $key => $value)
    		{
    			$values[] = $db->Quote($value);
    		}

    		$values = implode(',',$values);

			$query = "INSERT INTO `#__osemsc_mailing` (`user_id`,{$keys}) VALUES ('{$member_id}',{$values});";
		} else {

			foreach($mailing as $key => $value)
    		{
    			$values[] = "`{$key}`=".$db->Quote($value);
    		}

    		$values = implode(',',$values);

			$query = " UPDATE `#__osemsc_mailing` SET {$values}"
					." WHERE `user_id` ={$member_id}"
					;
		}
		//echo $query;exit;
		$db->setQuery($query);

		if (!oseDB::query()) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Failed Saving Mailing Info.');

		} else {


			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Saved User\'s Mailing Info.');

		}
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