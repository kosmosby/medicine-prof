<?php
/**
  * @version       1.0 +
  * @package       Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Reporter - com_osereporter
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 24-May-2011
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
	public static $extension= 'com_osemsc';
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
		$vName= JRequest :: getCmd('view', 'memberships');
		$db = &JFactory::getDBO();
		if (JOOMLA16==true)
		{
			$query = "SELECT * FROM `#__menu` WHERE `alias` =  'OSE Reporter™' AND `client_id` = 1";
			$db->setQuery($query);
			$results = $db->loadResult();
			if (empty($results))
			{
				$query= "UPDATE `#__menu` SET `alias` =  'OSE Reporter™', `path` =  'OSE Reporter™', `published`=1, `img` = '\"components/com_osereporter/favicon.ico\"'  WHERE `component_id` = ( SELECT extension_id FROM `#__extensions` WHERE element ='com_osereporter' ) AND `client_id` = 1";
				$db->setQuery($query);
				$db->query();
			}
		}
		JSubMenuHelper :: addEntry(JText :: _('Daily Statistics'), 'index.php?option=com_osereporter&view=daily', $vName == '');
		JSubMenuHelper :: addEntry(JText :: _('Monthly Statistics'), 'index.php?option=com_osereporter&view=monthly', $vName == '');
		JSubMenuHelper :: addEntry(JText :: _('Member List Export'), 'index.php?option=com_osereporter&view=memlist', $vName == '');
		JSubMenuHelper :: addEntry(JText :: _('Additional Info Export'), 'index.php?option=com_osereporter&view=customfield', $vName == '');
	}
}