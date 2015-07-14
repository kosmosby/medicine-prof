<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * flexpaper Controller
 */
class flexpaperControllerquiz extends JControllerForm
{

//    function edit() {
//
//       echo "<pre>";
//       print_r($this); die;
//
//
//    }

    function cancel() {

        $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=quizes&task=quizes', false));

    }

    function save() {

//        echo "<pre>";
//        print_r($_REQUEST); die;

        $current_id = JRequest::getVar('id');
        $membership_list_id = JRequest::getVar('membership_list_id');
        $test_id = JRequest::getVar('test_id');

//        echo "<pre>";
//        print_r($membership_list_id);
//        print_r($test_id); die;

        if($test_id && $membership_list_id) {

            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query = "delete from  #__flexpaper_quiz where membership_list_id = ".$membership_list_id;
            $db->setQuery($query);
            $db->query();

            if ($this->getTask() == 'apply') {
                $query = $db->getQuery(true);
                $query = "delete from  #__flexpaper_quiz where id = ".$current_id;
                $db->setQuery($query);
                $db->query();
            }

            $query = $db->getQuery(true);
            $query = "insert into #__flexpaper_quiz (`id`, `test_id`,`membership_list_id` ) values (NULL , ".$test_id.", ".$membership_list_id.");";
            $db->setQuery($query);
            $db->query();
            echo $db->geterrormsg();

            //echo $query; die;
        }

        if ($this->getTask() == 'apply') {
            // Redirect to the main page.
            $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=quiz&layout=edit&task=quizes&id='.$test_id, false));
        }
        else {
            $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=quizes&task=quizes', false));
        }

    }


}