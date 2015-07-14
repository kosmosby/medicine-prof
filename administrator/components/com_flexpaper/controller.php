<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
/**
 * General Controller of HelloWorld component
 */
class flexpaperController extends JController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false) 
	{

		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'flexpapers'));
 
		// call parent behavior
		parent::display($cachable);


        $submenu = JRequest::getVar('view');



        // Set the submenu
        FlexpaperHelper::addSubmenu($submenu);
	}


}