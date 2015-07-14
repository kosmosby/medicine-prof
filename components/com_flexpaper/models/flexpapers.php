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
class flexpaperModelflexpapers extends JModelLegacy
{

	public function getItems()
	{
        $id = JRequest::getVar('id');

        $course_id = JRequest::getVar('course_id');

        $user =& JFactory::getUser();

        $db = JFactory::getDBO();

        $isitabundle = $this->isItaBundle();


            $query = $db->getQuery(true);


            if(!$isitabundle) {
                $query = "select a.*, e.title from #__flexpaper as a, #__osemsc_order_item as b, #__osemsc_order as c, #__categories as e"

                     . " WHERE c.user_id = ".$user->id." AND c.order_status = 'confirmed' AND c.order_id = b.order_id "
                     . " AND b.entry_id = ".$course_id." "

                     . " AND a.membership_list_id = b.entry_id"

                     . " AND a.catid = e.id"

                     . " GROUP BY a.id ORDER BY e.lft, a.name";

            }
            else {
                $query = "select a.*, e.title from #__flexpaper as a, #__osemsc_order_item as b, #__osemsc_order as c, #__categories as e, #__flexpaper_bundle as f"

                    . " WHERE c.user_id = ".$user->id." AND c.order_status = 'confirmed' AND c.order_id = b.order_id "

//                    . " AND b.entry_id = ".$course_id." "

                    . " AND f.bundle_id = b.entry_id AND f.membership_list_id = ".$course_id." AND f.membership_list_id = a.membership_list_id"

                    . " AND a.catid = e.id"

                    . " GROUP BY a.id ORDER BY e.lft, a.name";

            }

            $db->setQuery($query);
            $this->_items = $db->loadobjectlist();


            $cat = array();

            for($i=0;$i<count($this->_items);$i++) {
                $cat[$this->_items[$i]->catid] = $this->_items[$i]->title;
            }

            $arr = array();

            foreach($cat as $k=>$v) {
                for($i=0;$i<count($this->_items);$i++) {
                    if($this->_items[$i]->catid == $k) {
                        $arr[$v][]= $this->_items[$i];
                    }
                }
            }


        $this->_items = $arr;

		return $this->_items;
	}

    public function isItaBundle() {
        $course_id = JRequest::getVar('course_id');
        $user =& JFactory::getUser();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query = "SELECT a.bundle_id from #__flexpaper_bundle as a, #__osemsc_order_item as b, #__osemsc_order as c WHERE a.membership_list_id = ".$course_id." AND a.bundle_id = b.entry_id AND b.order_id = c.order_id AND c.order_status = 'confirmed' AND c.user_id = ".$user->id;

        $db->setQuery($query);
        $row = $db->loadResult();

        return $row;
   }


    public function getCoursedata() {

        $course_id = JRequest::getVar('course_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT title, description from #__osemsc_acl where id = ".$course_id;
        $db->setQuery($query);
        $this->_coursedata = $db->loadObject();

        return $this->_coursedata;
    }



}
