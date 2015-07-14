<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * HelloWorld Model
 */
class flexpaperModelVideo extends JModelItem
{
    public function getItems()
    {

        $course_id = JRequest::getVar('course_id');

        $db = JFactory::getDBO();

        // all tests
        $query = $db->getQuery(true);
        $query = "SELECT a.*,b.title FROM #__flexpaper_video as a, #__osemsc_acl as b

        WHERE a.membership_list_id =".$course_id." AND a.published > 0 AND b.id = a.membership_list_id";

        $db->setQuery($query);
        $this->_items = $db->loadobjectlist();

        return $this->_items;
    }
}