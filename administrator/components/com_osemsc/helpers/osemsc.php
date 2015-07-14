<?php
/**
 * @version		$Id: content.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class OsemscHelper
{
	public static $extension = 'com_osemsc';

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu()
	{
		$vName = JRequest::getCmd('view','memberships');
		$db = JFactory::getDBO();
		if (JOOMLA16==true)
		{
			$query = "SELECT * FROM `#__menu` WHERE `alias` =  'OSE Membership™' AND `client_id` = 1";
			$db->setQuery($query);
			$results = $db->loadResult();
			if (empty($results))
			{
				$query= "UPDATE `#__menu` SET `alias` =  'OSE Membership™', `path` =  'OSE Membership™', `published`=1, `img` = '\"components/com_osemsc/favicon.ico\"'  WHERE `component_id` = ( SELECT extension_id FROM `#__extensions` WHERE element ='com_osemsc' ) AND `client_id` = 1";
				$db->setQuery($query);
				$db->query();
			}
		}
		JSubMenuHelper::addEntry(
			JText::_('MEMBERSHIP_MANAGEMENT'),
			'index.php?option=com_osemsc&view=memberships',
			$vName == 'memberships'
		);
		JSubMenuHelper::addEntry(
			JText::_('MEMBER_MANAGEMENT'),
			'index.php?option=com_osemsc&view=members',
			$vName == 'members');
		JSubMenuHelper::addEntry(
			JText::_('ORDER_MANAGEMENT'),
			'index.php?option=com_osemsc&view=orders',
			$vName == 'orders'
		);
		JSubMenuHelper::addEntry(
			JText::_('COUPON_MANAGEMENT'),
			'index.php?option=com_osemsc&view=coupons',
			$vName == 'coupons'
		);
		JSubMenuHelper::addEntry(
			JText::_('TEMPLATES'),
			'index.php?option=com_osemsc&view=emails',
			$vName == 'emails'
		);
		JSubMenuHelper::addEntry(
			JText::_('CONFIGURATION'),
			'index.php?option=com_osemsc&view=config',
			$vName == 'config'
		);
		JSubMenuHelper::addEntry(
			JText::_('CUSTOM_PROFILE'),
			'index.php?option=com_osemsc&view=profile',
			$vName == 'profile'
		);
		JSubMenuHelper::addEntry(
			JText::_('ADDONS'),
			'index.php?option=com_osemsc&view=addons',
			$vName == 'addons'
		);
		JSubMenuHelper::addEntry(
		JText::_('ABOUT_OSE'),
					'index.php?option=com_osemsc&view=aboutose',
		$vName == 'aboutose'
		);
	}
	public static function getDBFields($table)
	{
		$db = JFactory::getDBO();
		if (JOOMLA30)
		{
			$fields= $db->getTableColumns($table);
			$fields[$table]=$fields;
		}
		else
		{
			$fields= $db->getTableFields($table);
		}
		return $fields;
	
	}
	public static function setQuery($sql)
	{
		$config= new JConfig();
		$return = str_replace('#__', $config->dbprefix, $sql);
		return  $return; 
	}
}
