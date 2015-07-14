<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_banners
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class modCoursesHelper
{
	static function &getList()
	{
        // Create a new query object.
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "(SELECT a.description, a.image, b.title, c.title as category, d.id as Itemid, d.link FROM #__flexpaper_content as a, #__osemsc_acl as b, #__categories as c, #__menu as d"
                ." WHERE a.membership_list_id = b.id"
                ." AND a.catid = c.id"
                ." AND d.id = a.content_id"
                ." AND a.show_module = 1"
                ." AND a.catid=34 ORDER BY RAND() LIMIT 4)"

                ." UNION "

                . "(SELECT a.description, a.image, b.title, c.title as category, d.id as Itemid, d.link FROM #__flexpaper_content as a, #__osemsc_acl as b, #__categories as c, #__menu as d"
                ." WHERE a.membership_list_id = b.id"
                ." AND a.catid = c.id"
                ." AND d.id = a.content_id"
                ." AND a.show_module = 1"
                ." AND a.catid=35 ORDER BY RAND() LIMIT 4)"

        ;

        $db->setQuery($query);
        $rows = $db->loadobjectList();

        $array = array();
        for($i=0;$i<count($rows);$i++) {
            $array[$rows[$i]->category][] = $rows[$i];
        }


        return $array;
    }
}
