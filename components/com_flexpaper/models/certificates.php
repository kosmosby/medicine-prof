<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of article categories.
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.6
 */
class flexpaperModelCertificates extends JModelLegacy
{

	public function getItems()
	{
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $query = "SELECT b.id, b.name, a.passed, a.userid, a.tid FROM #__lms_results as a, #__lms_tests as b

          WHERE a.userid = ".$user->id." AND  a.tid = b.id GROUP BY a.tid";

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

		return $this->_items;
	}

}
