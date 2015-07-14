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
class flexpaperModelCatalogAtx extends JModelLegacy
{

    public function getItems()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $cat_id = JRequest::getInt('cat_id');

        $query = "SELECT a.* FROM #__flexpaper_catalog_atx as a, #__flexpaper_catalog_category as b WHERE a.cat_id = b.id AND a.cat_id =".$cat_id;

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

        return $this->_items;
    }
    public function getCategory()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $cat_id = JRequest::getInt('cat_id');

        $query = "SELECT name FROM #__flexpaper_catalog_category WHERE id = ".$cat_id;

        $db->setQuery($query);
        $this->_item = $db->loadResult();

        return $this->_item;
    }

}
