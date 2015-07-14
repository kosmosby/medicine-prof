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
class flexpaperViewQuestions extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{

        $user =& JFactory::getUser();

        // Initialise variables
		$items		= $this->get('Items');

        $coursename	= $this->get('Coursename');
        $course_id = JRequest::getVar('course_id');


        if($items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $itemid = JRequest::getVar('Itemid');
        $testid = JRequest::getVar('testid');

        $this->assignRef('itemid',$itemid);
        $this->assignRef('testid',$testid);

        $this->assignRef('user_id',$user->id);
        $this->assignRef('coursename',$coursename);
        $this->assignRef('course_id',$course_id);


        $this->assignRef('items',$items);

		parent::display($tpl);
	}


    function show_resulst_test($tpl = null) {

        $user =& JFactory::getUser();

        $coursename	= $this->get('Coursename');

        $questions = $this->get('Items');

        $passed = $this->get('ResultTest');

        $passed_mark = $this->get('PassedMarkForTheTest');

        $user_score = $this->get('UserScore');

        $answers = JRequest::getVar('answer');

        $itemid = JRequest::getVar('Itemid');
        $testid = JRequest::getVar('testid');

        $this->assignRef('itemid',$itemid);
        $this->assignRef('testid',$testid);

        $this->assignRef('user_id',$user->id);
        $this->assignRef('coursename',$coursename);


        $this->assignRef('questions',$questions);
        $this->assignRef('answers',$answers);

        $this->assignRef('passed',$passed);
        $this->assignRef('passed_mark',$passed_mark);
        $this->assignRef('user_score',$user_score);

        parent::display($tpl);
    }

}

