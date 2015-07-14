<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");

class oseMscModelConfig extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //
    function checkViewExists()
	{
		$db = oseDB::instance();
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$query[0]= "SHOW CREATE VIEW `#__osemsc_userinfo_view`";
		}
		else
		{
			$query[0]= "SHOW TABLE STATUS LIKE '#__osemsc_userinfo_view'";
		}
		$query[0] = OsemscHelper::setQuery($query[0]);
		$createquery[0] = "CREATE SQL SECURITY INVOKER VIEW `#__osemsc_userinfo_view` AS select `u`.`id` AS `user_id`,`u`.`name` AS `jname`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`block` AS `block`,`ui`.`firstname` AS `firstname`,`ui`.`lastname` AS `lastname`,`ui`.`primary_contact` AS `primary_contact` FROM (`#__users` `u` join `#__osemsc_userinfo` `ui` on((`u`.`id` = `ui`.`user_id`)));";		

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$query[1]= "SHOW CREATE VIEW `#__osemsc_member_view`";
		}
		else
		{
			$query[1]= "SHOW TABLE STATUS LIKE '#__osemsc_member_view'";
		}
		$query[1] = OsemscHelper::setQuery($query[1]);
		$createquery[1] = "CREATE SQL SECURITY INVOKER VIEW `#__osemsc_member_view` AS select `mem`.`id` AS `id`,`acl`.`title` AS `msc_name`,`mem`.`msc_id` AS `msc_id`,`mem`.`member_id` AS `member_id`,`mem`.`status` AS `status`,`mem`.`notified` AS `notified`,`mem`.`eternal` AS `eternal`,`mem`.`start_date` AS `start_date`,`mem`.`expired_date` AS `expired_date`,`mem`.`params` AS `memParams`,`u`.`username` AS `username`,`u`.`name` AS `name`,`u`.`email` AS `email`,`u`.`registerDate` AS `registerDate`,`u`.`params` AS `userParams` from ((`#__osemsc_member` `mem` join `#__users` `u` on((`u`.`id` = `mem`.`member_id`))) join `#__osemsc_acl` `acl` on((`acl`.`id` = `mem`.`msc_id`)));";
		
		$config = new JConfig();

		for ($i=0; $i<2; $i++)
		{
			$db->setQuery($query[$i]);
			$result= $db->loadObjectlist();
	        if (empty($result))
	        {
				//$db->setQuery();
				echo "<div class='setting-msg'>#The following View cannot be created in Joomla, please execute the following SQL through phpmyadmin in your hosting control panel:<br />";
				$sql = str_replace("#__", $config->dbprefix, $createquery[$i]);
				echo $sql."</div>";
	        }
		}
	}
	function save($post)
	{
		$db = oseDB::instance();

		$type = $post['config_type'];
		unset($post['config_type']);

		if($type == 'global')
		{
			if(empty($post['is_member_mode_customized']))
			{
				$post['is_member_mode_customized'] = 0;
			}

			if(empty($post['is_msc_mode_customized']))
			{
				$post['is_msc_mode_customized'] = 0;
			}

			if(empty($post['msc_extend']))
			{
				$post['msc_extend'] = 0;
			}

			if(empty($post['member_mode']))
			{
				$post['member_mode'] = 0;
			}
		}



		if($type == 'payment')
		{
			if(!empty($post['cc_methods']))
			{
				//$post['cc_methods'] = implode(',',$post['cc_methods']);
			}
		}

		$type = $db->Quote($type);

		foreach($post as $key => $value)
		{
			$qKey = $db->Quote($key);

			if(is_array($value))
			{
				$value = implode(',',$value);
			}

			$qValue = $db->Quote($value);
			$query = " SELECT id FROM `#__osemsc_configuration` "
					." WHERE `key` = {$qKey} AND type = {$type}"
					;
			$db->setQuery($query);
			$exists = $db->loadResult();

			if(!empty($exists))
			{
				$query = " UPDATE `#__osemsc_configuration` "
						." SET `value` = {$qValue}"
						." WHERE `key` = {$qKey} AND type = {$type} "
						;
				$db->setQuery($query);
				if(!oseDB::query())
				{
					return false;
				}
			}
			else
			{
				$query = " INSERT INTO `#__osemsc_configuration` "
						." (`key`,`value`,`type`) "
						." VALUES "
						." ({$qKey},{$qValue},{$type}) "
						;
				$db->setQuery($query);
				if(!oseDB::query())
				{
					return false;
				}
			}
		}

		return true;
	}

	function getConfig($config_type,$type = 'array')
	{
		$msc = oseRegistry::call('msc');

		$config = $msc->getConfig($config_type,$type);

		//if($config_type == '3rd') oseExit(oseDB::instance()->getQuery());
		return $config;
	}

	function setDefault()
	{

	}

	function getGroupList()
	{
    	$acl		=& JFactory::getACL();

    	$myuser		=& JFactory::getUser();

    	$member_id = JRequest::getVar('member_id','');

    	$db = oseDB::instance();

    	$query = " SELECT CONCAT( REPEAT('&nbsp;&nbsp;', COUNT(parent.name) - 1),'-', node.name) AS name,node.id AS id "
    			." FROM `#__core_acl_aro_groups` AS node, "
    			." (SELECT * FROM `#__core_acl_aro_groups` WHERE lft >= 13 ) AS parent "
    			." WHERE node.lft BETWEEN parent.lft AND parent.rgt  "
    			." GROUP BY node.id"
    			." ORDER BY node.lft"
    			;

    	$db->setQuery($query);

    	$gtree = oseDB::loadList('obj');

		$option = array();
		foreach ($gtree as $tree)
		{
			$option[] = JHTML::_('select.option', $tree->id, JText::_($tree->name));;
		}

    	if(empty($member_id))
    	{
    		$user 		=& JUser::getInstance();
    	}
    	else
    	{
    		$user 		=& JUser::getInstance( $member_id );
    	}

		$selected = oseRegistry::call('msc')->getConfig('access','obj');
		$selected->backend_access = empty($selected->backend_access)?null:$selected->backend_access;
		$selected = explode(',',$selected->backend_access);
		//$gtree = $acl->get_group_children_tree( null, 'USERS', false );

		$gid 	= JHTML::_('select.genericlist',   $option, 'backend_access[]', 'size="5" multiple = true', 'value', 'text', $selected );

    	return $gid;
	}

	function getEmailAdminGroupList()
	{
		$selected = oseRegistry::call('msc')->getConfig('email','obj');
		$selected->admin_group = empty($selected->admin_group)?null:$selected->admin_group;
		$selected = explode(',',$selected->admin_group);

		if (JOOMLA16==true)
		{
			$gid = JHtml::_('access.usergroups', 'admin_group', $selected, true);
		}
		else
		{
			$acl		=& JFactory::getACL();
	    	$db = oseDB::instance();
	    	$query = " SELECT CONCAT( REPEAT('&nbsp;&nbsp;', COUNT(parent.name) - 1),'-', node.name) AS name,node.id AS id "
	    			." FROM `#__core_acl_aro_groups` AS node, "
	    			." (SELECT * FROM `#__core_acl_aro_groups` WHERE lft >= 13 ) AS parent "
	    			." WHERE node.lft BETWEEN parent.lft AND parent.rgt  "
	    			." GROUP BY node.id"
	    			." ORDER BY node.lft"
	    			;
	    	$db->setQuery($query);
	    	$gtree = oseDB::loadList('obj');
			$option = array();
			foreach ($gtree as $tree)
			{
				$option[] = JHTML::_('select.option', $tree->id, JText::_($tree->name));;
			}
			$gid 	= JHTML::_('select.genericlist',   $option, 'admin_group[]', 'size="5" multiple = true', 'value', 'text', $selected );

		}
    	return $gid;
	}

	function saveMCurrency($currency,$rate)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_configuration`"
				." WHERE `key` = 'primary_currency' AND `type` = 'currency'"
				;

		$db->setQuery($query);

		$item = oseDB::loadItem('obj');


		$currencyInfos = oseJson::decode($item->default,true);

		$currencyInfos[$currency] = array('currency'=>$currency,'rate'=>$rate);

		$currencyInfos = oseJson::encode($currencyInfos);

		$query = " UPDATE `#__osemsc_configuration`"
				." SET `default` = ".$db->Quote($currencyInfos)
				." WHERE `id` = '{$item->id}'"
				;

		$db->setQuery($query);

		return oseDB::query();

	}

	function removeCurrency($currency)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_configuration`"
				." WHERE `key` = 'primary_currency' AND `type` = 'currency'"
				;

		$db->setQuery($query);

		$item = oseDB::loadItem('obj');


		$currencyInfos = oseJson::decode($item->default,true);

		unset($currencyInfos[$currency]);

		$currencyInfos = oseJson::encode($currencyInfos);

		$query = " UPDATE `#__osemsc_configuration`"
				." SET `default` = ".$db->Quote($currencyInfos)
				." WHERE `id` = '{$item->id}'"
				;

		$db->setQuery($query);

		return oseDB::query();

	}

	function removeAllCurrency()
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_configuration`"
				." WHERE `key` = 'primary_currency' AND `type` = 'currency'"
				;

		$db->setQuery($query);

		$item = oseDB::loadItem('obj');


		//$currencyInfos = oseJson::decode($item->default,true);

		//unset($currencyInfos[$currency]);

		$currencyInfos = oseJson::encode(null);

		$query = " UPDATE `#__osemsc_configuration`"
				." SET `default` = ".$db->Quote($currencyInfos)
				." WHERE `id` = '{$item->id}'"
				;

		$db->setQuery($query);

		return oseDB::query();

	}
}


