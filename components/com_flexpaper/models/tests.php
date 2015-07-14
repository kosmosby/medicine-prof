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
class flexpaperModelTests extends JModelLegacy
{

	public function getItems()
	{

        $db = JFactory::getDBO();

        // all tests
        $query = $db->getQuery(true);
        $query = "select * from #__lms_tests";

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

        return $this->_items;
	}

}
