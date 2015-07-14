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
class flexpaperModelquizes extends JModelLegacy
{

	public function getItems()
	{
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $available_quizes = $this->availablequizes();

        $query = "SELECT b.id, d.title, b.vmProductID as testid, c.id as results_id, c.passed, a.test_id as tid, d.id as course_id FROM #__flexpaper_quiz as a, #__lms_tests as b LEFT JOIN #__lms_results as c ON c.tid = b.id AND c.userid = ".$user->id." , #__osemsc_acl as d

          WHERE a.id IN (".implode(',',$available_quizes).") AND a.test_id = b.id AND d.id = a.membership_list_id

         GROUP BY b.id

         ";

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

//          echo $query;
//
//          echo "<pre>";
//          print_r($this->_items); die;

        for($i=0;$i<count($this->_items);$i++) {
            $this->checkQuizes($this->_items[$i]);
        }


        $this->_items = $this->JustnotEmplytQuizes($this->_items);


		return $this->_items;
	}

    public function JustnotEmplytQuizes($rows) {

        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $rows1 = array();

        for($i=0;$i<count($rows);$i++) {
            $query = "SELECT count(id) FROM #__lms_questions WHERE testid = ".$rows[$i]->tid;
            $db->setQuery($query);
            $count = $db->loadResult();

            echo $db->geterrormsg();

            if($count) {
                $rows1[] = $rows[$i];
            }
        }

        return $rows1;
    }

    public function availablequizes() {
        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        JLoader::import( 'courses', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $courses_model = JModel::getInstance( 'courses', 'flexpaperModel' );
        $rows = $courses_model->getItems($user->id,'mydocs');


        $query = "SELECT f.id FROM #__osemsc_order as d, #__osemsc_order_item as e, #__flexpaper_quiz as f WHERE d.user_id = ".$user->id." AND d.order_status = 'confirmed' AND d.order_id = e.order_id AND f.membership_list_id = e.entry_id";
        $db->setQuery($query);
        $rows1 = $db->loadResultArray();


        $query = "SELECT d.id FROM #__flexpaper_bundle as a, #__osemsc_order as b, #__osemsc_order_item as c, #__flexpaper_quiz as d WHERE b.user_id = ".$user->id." AND b.order_status = 'confirmed' AND b.order_id = c.order_id AND a.bundle_id = c.entry_id AND d.membership_list_id = a.membership_list_id";
        $db->setQuery($query);
        $rows2 = $db->loadResultArray();
        echo $db->geterrormsg();

        return array_unique(array_merge($rows1,$rows2));
    }


    public function isItaBundle() {
        $course_id = JRequest::getVar('course_id');
        $user =& JFactory::getUser();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query = "SELECT a.bundle_id from #__flexpaper_bundle as a, #__osemsc_order_item as b, #__osemsc_order as c WHERE a.membership_list_id = ".$course_id." AND a.bundle_id = b.entry_id AND b.order_id = c.order_id AND c.user_id = ".$user->id;

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

    public function checkQuizes($row) {

//        echo "<pre>";
//        print_r($row); die;
//
        $user =& JFactory::getUser();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        $is_there = false;
        if(isset($row->tid) && $row->tid) {

            $query = "SELECT tid FROM #__lms_results WHERE userid = ".$user->id." AND tid = ".$row->tid;
            $db->setQuery($query);
            $is_there = $db->loadResult();
       }

        if(!$is_there) {

           $query = "INSERT INTO #__lms_results SET "
                . "userid ='" . $user->id . "',"
                . "tid ='" . $row->id . "',"
                . "date_created = '" . date("Y-m-d H:i:s") . "',"
             //   . "testid ='" . $row->testid . "',"
                . "paid ='1',"
                . "approved ='1',"
                . "checked_out_time ='0'";

            $db->setQuery($query);
            $db->query();

        }




    }




}
