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
class flexpaperModelCatalogSaleNames extends JModelLegacy
{

    public function getItems()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $atx_id = JRequest::getInt('atx_id');

        $query = "SELECT * FROM #__flexpaper_catalog_sale_name where atx_id = ".$atx_id;

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

        return $this->_items;
    }


    public function getCategory()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $atx_id = JRequest::getInt('atx_id');

        $query = "SELECT b.name FROM #__flexpaper_catalog_atx as a, #__flexpaper_catalog_category as b WHERE a.cat_id = b.id AND a.id = ".$atx_id;

        $db->setQuery($query);
        $this->_item = $db->loadResult();


        return $this->_item;
    }

    public function getCat_id()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $atx_id = JRequest::getInt('atx_id');

        $query = "SELECT b.id FROM #__flexpaper_catalog_atx as a, #__flexpaper_catalog_category as b WHERE a.cat_id = b.id AND a.id = ".$atx_id;

        $db->setQuery($query);
        $this->_item = $db->loadResult();


        return $this->_item;
    }

    public function getAtx()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $atx_id = JRequest::getInt('atx_id');

        $query = "SELECT a.name FROM #__flexpaper_catalog_atx as a WHERE a.id = ".$atx_id;

        $db->setQuery($query);
        $this->_item = $db->loadResult();

        $db->setQuery($query);
        $this->_item = $db->loadResult();

        return $this->_item;
    }
}
