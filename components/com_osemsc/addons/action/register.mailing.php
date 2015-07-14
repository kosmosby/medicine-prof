<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterMailing
{
	public static function save( $params )
    {
    	$member_id = $params['member_id'];
//print_r($_POST); exit;
    	if(empty($member_id))
    	{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

		$post = JRequest::get('post');
		$mailing = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'mailing_'))
			{
				$mailingKey = preg_replace('/mailing_/','',$key,1);
				$mailing[$mailingKey] = $value;
			}
		}
    	$db = oseDB::instance();

		$query = "CREATE TABLE IF NOT EXISTS `#__osemsc_mailing` (
			  `user_id` int(11) NOT NULL default '0',
			  `company` varchar(200) default NULL,
			  `addr1` text COMMENT 'address 1',
			  `addr2` text COMMENT 'address 2',
			  `city` varchar(100) default NULL,
			  `state` varchar(100) default NULL COMMENT 'State ID',
			  `country` varchar(100) default NULL COMMENT 'Country ID',
			  `postcode` varchar(20) default NULL,
			  `telephone` varchar(20) default NULL,
			  `sector` varchar(20) default NULL,
			  PRIMARY KEY  (`user_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;	";

		$db->setQuery($query);
		$db->query();

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
}
?>