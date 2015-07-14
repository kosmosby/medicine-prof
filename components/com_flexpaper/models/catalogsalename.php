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
class flexpaperModelCatalogSaleName extends JModelLegacy
{

    public function getItem()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $query = "SELECT a.*, b.name as man_name, c.name as atx_name FROM #__flexpaper_catalog_sale_name as a, #__flexpaper_catalog_manufacturer as b, #__flexpaper_catalog_atx as c where a.id = ".$id." AND b.id = a.man_id AND c.id = a.atx_id";

        $db->setQuery($query);
        $this->_item = $db->loadobject();



        return $this->_item;
    }


    public function getInternational_name()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $query = "SELECT b.name FROM #__flexpaper_catalog_sale_name as a, #__flexpaper_catalog_international_name as b where a.id = ".$id." AND a.atx_id = b.id";

        $db->setQuery($query);
        $this->_item = $db->loadResult();


        return $this->_item;
    }

    public function getAnalogs()
    {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $query = "SELECT a.name FROM #__flexpaper_catalog_analog as a, #__flexpaper_catalog_international_name as b, #__flexpaper_catalog_sale_name as c  where b.id = a.international_name_id and c.international_name_id = b.id AND c.id = ".$id;

        $db->setQuery($query);
        $this->_items = $db->loadObjectList();


        return $this->_items;
    }



    public function getCategory()
    {
        $db = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $query = "SELECT c.name FROM #__flexpaper_catalog_atx as b,#__flexpaper_catalog_sale_name as a, #__flexpaper_catalog_category as c WHERE a.id = ".$id." AND a.atx_id = b.id AND c.id = a.atx_id";

        $db->setQuery($query);
        $this->_item = $db->loadResult();


        return $this->_item;
    }

    public function getCat_id()
    {
        $db = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $query = "SELECT c.id FROM #__flexpaper_catalog_atx as b,#__flexpaper_catalog_sale_name as a, #__flexpaper_catalog_category as c WHERE a.id = ".$id." AND a.atx_id = b.id AND c.id = a.atx_id";

        $db->setQuery($query);
        $this->_item = $db->loadResult();


        return $this->_item;
    }

//    public function getAtx()
//    {
//        $db = JFactory::getDBO();
//
//        $id = JRequest::getInt('id');
//
//        $query = "SELECT b.name FROM #__flexpaper_catalog_atx as b,#__flexpaper_catalog_sale_name as a  WHERE a.id = ".$id." AND a.atx_id = b.id";
//
//        $db->setQuery($query);
//        $this->_item = $db->loadResult();
//
//        return $this->_item;
//    }
//
//    public function getAtx_id()
//    {
//        $db = JFactory::getDBO();
//
//        $id = JRequest::getInt('id');
//
//        $query = "SELECT b.id FROM #__flexpaper_catalog_atx as b,#__flexpaper_catalog_sale_name as a  WHERE a.id = ".$id." AND a.atx_id = b.id";
//
//        $db->setQuery($query);
//        $this->_item = $db->loadResult();
//
//        return $this->_item;
//    }
}
