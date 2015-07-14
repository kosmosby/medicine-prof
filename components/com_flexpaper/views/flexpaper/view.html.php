<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 */
class flexpaperViewflexpaper extends JView
{
	// Overwriting JView display method
	function display($tpl = null) 
	{

        $id = JRequest::getInt('id', 0);
        $course_id = JRequest::getVar('course_id');

        $document =& JFactory::getDocument();

        $document->addStyleSheet(JURI::base().'components/com_flexpaper/css/flexpaper.css');

        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.min.js');

        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.extensions.min.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/flexpaper.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/flexpaper_handlers.js');

        $document->addScript(JURI::base().'components/com_flexpaper/js/custom.js');

        $item = $this->get('doc');
        $activeCourse = $this->get('ActiveCourse');

        // for menu
        $items		= $this->get('Items', 'flexpapers');

        $course_info = $this->get('Coursedata', 'flexpapers');

        $absolute_pate = JPATH_SITE;

        $this->assignRef('path',JURI::base());
        $this->assignRef('absolute_path',$absolute_pate);

        $this->assignRef('item',$item);

        //for menu
        $this->assignRef('items',$items);
        $this->assignRef('title',$course_info->title);
        $this->assignRef('course_id',$course_id);

        $this->assignRef('current_id',$id);

        $this->assignRef('activeCourse',$activeCourse);

        $itemid = JRequest::getVar('Itemid');
        $this->assignRef('itemid',$itemid);

        // Display the view
		parent::display($tpl);
	}
}