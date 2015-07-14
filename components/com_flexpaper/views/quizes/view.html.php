<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.5
 */
class flexpaperViewquizes extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
        $document =& JFactory::getDocument();
        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/custom.js');

        $course_id = JRequest::getVar('course_id');

        // Initialise variables
		$items		= $this->get('Items');
        //$course_info = $this->get('Coursedata');

//        echo "<pre>";
//        print_r($items); die;


		if($items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $itemid = JRequest::getVar('Itemid');

        $this->assignRef('itemid',$itemid);

		$this->assignRef('items',$items);
        //$this->assignRef('title',$course_info->title);
        //$this->assignRef('description',$course_info->description);
        $this->assignRef('course_id',$course_id);

		parent::display($tpl);
	}

}