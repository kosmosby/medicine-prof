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
class flexpaperModelcourses extends JModelLegacy
{

	public function getItems( $user_id = '', $task ='', $only_published = true, $only_confirmed_status = true)
	{

        $db = JFactory::getDBO();

        $courses_bought = $this->courses_bought($user_id, $only_confirmed_status);


        if(!$task) {
            $task =  JRequest::getVar('task');
        }

        $bundles = $this->bundles();

        $bundles_arr = $this->bundles_arr($bundles);

        $array_minus_bundles = $this->array_minus_bundles($courses_bought,$bundles_arr);

        // all courses
            $query = $db->getQuery(true);
            $query = "select a.*, b.params, c.content_id, e.title as category, e.id as category_id, e.lft as ordering from #__osemsc_acl as a

            LEFT JOIN #__osemsc_ext as b  ON  a.id = b.id and b.type = 'payment' 
            LEFT JOIN #__flexpaper_content as c ON c.membership_list_id = a.id
            /*LEFT JOIN #__flexpaper_content as d ON a.id = d.membership_list_id*/
            LEFT JOIN #__categories as e ON e.id = c.catid

            WHERE 1=1";

            if($only_published) {
             $query .= " AND a.published = 1 ";
            } 

            if(count($array_minus_bundles)) {
                $query .= " AND a.id NOT IN (".implode(',',array_unique($array_minus_bundles)).")";
            }

            if($task== 'mydocs') {
                $query .= " AND a.id IN (".implode(',',array_unique($courses_bought)).")";
            }

        $query .= " GROUP BY a.id";

        $query .= " ORDER BY e.lft ASC";

            $db->setQuery($query);
            $this->_items = $db->loadobjectlist();

//        echo "<pre>";
//        print_r($this->_items); die;

//         echo $query; die;
        // echo $db->geterrormsg(); die;


        // was course bought?
            for($i=0;$i<count($this->_items);$i++) {
                $this->_items[$i]->params = json_decode($this->_items[$i]->params);

                if(in_array($this->_items[$i]->id,$courses_bought)) {
                    $this->_items[$i]->bought =1;
                    $this->_items[$i]->membersarea_itemid = $this->get_membersarea_itemid();
                }
                else {
                    $this->_items[$i]->bought =0;
                    $this->_items[$i]->content_link = $this->getContentLink($this->_items[$i]->content_id);
                }
            }

        // bought course the first
           $new_array= array();

            for($i=0;$i<count($this->_items);$i++) {
                if($this->_items[$i]->bought ==1) {

                     $new_array[] = $this->_items[$i];
                }
            }

            $flag = 0;
            for($i=0;$i<count($this->_items);$i++) {

                if($this->_items[$i]->bought == 0) {

                    if($flag==0) {
                        $this->_items[$i]->the_first_havent_bought = 1;
                        $flag = 1;
                    }

                    $new_array[] = $this->_items[$i];
                }
            }

            $this->_items = $new_array;

//         echo "<pre>";
//         print_r($this->_items); die;

        return $this->_items;
	}

    public function getContentLink($id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query = "SELECT link FROM #__menu WHERE id = ".$id;

        $db->setQuery($query);
        $row = $db->loadResult();

        return $row.'&Itemid='.$id;

    }

    public function get_membersarea_itemid() {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query = "SELECT id FROM #__menu WHERE (link = 'index.php?option=com_flexpaper&view=membersarea&task=membersarea' OR link = 'index.php?option=com_flexpaper&view=membersarea') AND published > 0";

        $db->setQuery($query);
        $row = $db->loadResult();

        return $row;
    }

    public function courses_bought( $user_id = '', $only_confirmed_status = true) {

        if($user_id) {
            $user->id = $user_id;
        }
        else {
            $user =& JFactory::getUser();
        }


        $db = JFactory::getDBO();

        //if user has bought course
        $result_courses_bought_array = array();
        $courses_bought = array();
        if($user->id) {
            $query = $db->getQuery(true);
            $query = "SELECT b.entry_id FROM #__osemsc_order as a, #__osemsc_order_item as b WHERE a.user_id = ".$user->id." AND a.order_id = b.order_id";

            if($only_confirmed_status) {
               $query .= " AND a.order_status = 'confirmed'";
            }
            $query .=" GROUP BY entry_id";

            $db->setQuery($query);
            $courses_bought = $db->loadResultArray();

            //bundles
            $bundles = $this->bundles();

            if(count($bundles)) {
                $bundles_arr = $this->bundles_arr($bundles);

                $result_courses_bought_array = $this->result_courses_bought_array($bundles_arr,$courses_bought);

                if(count($result_courses_bought_array)) {
                    //$courses_bought = array_unique($result_courses_bought_array);
                }
            }
            else {
                $result_courses_bought_array = $courses_bought;
            }
        }


        return $result_courses_bought_array;
    }

    public function bundles() {
        $db = JFactory::getDBO();
        //bundles
        $query = $db->getQuery(true);
        $query = "SELECT bundle_id,membership_list_id FROM #__flexpaper_bundle";

        $db->setQuery($query);
        $bundles = $db->loadObjectList();

        return $bundles;
    }

    public function bundles_arr($bundles) {
        $bundles_arr = array();
        foreach($bundles as $k=>$v) {
            $bundles_arr[$v->bundle_id][] = $v->membership_list_id;;
        }

        return $bundles_arr;
    }


    public function result_courses_bought_array($bundles_arr,$courses_bought) {

        $result_courses_bought_array = array();
        $new_array = $courses_bought;

        for($i=0;$i<count($courses_bought);$i++) {
            if(isset($bundles_arr[$courses_bought[$i]]) && count($bundles_arr[$courses_bought[$i]])) {
                $result_courses_bought_array = array_merge($courses_bought,$bundles_arr[$courses_bought[$i]]);

                $new_array = array_merge($result_courses_bought_array,$new_array);
            }
        }

        return $new_array;
    }

    public function array_minus_bundles($courses_bought, $bundles_arr) {

        $array_minus_bundles = array();

        for($i=0;$i<count($courses_bought);$i++) {
            if(isset($bundles_arr[$courses_bought[$i]]) && count($bundles_arr[$courses_bought[$i]])) {
                $array_minus_bundles[] = $courses_bought[$i];
            }
        }

        return $array_minus_bundles;
    }


}
