<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * flexpaper Controller
 */
class flexpaperControllerbundle extends JControllerForm
{

//    function edit() {
//
//       echo "<pre>";
//       print_r($this); die;
//
//
//    }

    function cancel() {

        $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=bundles&task=bundles', false));

    }

    function save() {

        $current_id = JRequest::getVar('id');
        $membership_list_id = JRequest::getVar('membership_list_id');
        $bundle_id = JRequest::getVar('bundle_id');

        if(count($bundle_id) && $membership_list_id) {
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query = "delete from  #__flexpaper_bundle where bundle_id = ".$bundle_id;
            $db->setQuery($query);
            $db->query();

            if ($this->getTask() == 'apply') {
                $query = $db->getQuery(true);
                $query = "delete from  #__flexpaper_bundle where bundle_id = ".$current_id;
                $db->setQuery($query);
                $db->query();
            }

            for($i=0;$i<count($membership_list_id);$i++) {
                $query = $db->getQuery(true);
                $query = "insert into #__flexpaper_bundle (`id`, `bundle_id`,`membership_list_id` ) values (NULL , ".$bundle_id.", ".$membership_list_id[$i].");";
                $db->setQuery($query);
                $db->query();
                echo $db->geterrormsg();
            }
        }

        if ($this->getTask() == 'apply') {
            // Redirect to the main page.
            $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=bundle&layout=edit&task=bundles&id='.$bundle_id, false));
        }
        else {
            $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&view=bundles&task=bundles', false));
        }

    }


}