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
class flexpaperViewRuntest extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{

        $user =& JFactory::getUser();

        $document =& JFactory::getDocument();
        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.min.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/custom.js');

        // Initialise variables
		$items		= $this->get('Items');


        //check if your had attempt
        JLoader::import( 'questions', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $questions_model = JModel::getInstance( 'questions', 'flexpaperModel' );

        $answers = $questions_model->UserHadAttempt();

        $is_passed = false;

        if(count($answers)) {
            $is_passed = true;
            $coursename	= $questions_model->getCoursename();
            $questions = $questions_model->getItems();
            $passed = $questions_model->getUserPassedMark();
            $user_score = $questions_model->getUserScoreMark();
            $passed_mark = $questions_model->getPassedMarkForTheTest();


            $this->assignRef('user_id',$user->id);
            $this->assignRef('coursename',$coursename);


            $this->assignRef('questions',$questions);
            $this->assignRef('answers',$answers);

            $this->assignRef('passed',$passed);
            $this->assignRef('passed_mark',$passed_mark);
            $this->assignRef('user_score',$user_score);
        }

        if($items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $itemid = JRequest::getVar('Itemid');
        $testid = JRequest::getVar('testid');
        $course_id = JRequest::getVar('course_id');

        $this->assignRef('itemid',$itemid);
        $this->assignRef('testid',$testid);
        $this->assignRef('course_id',$course_id);

        $this->assignRef('course_id',$course_id);

        $this->assignRef('answers',$answers);

        $this->assignRef('is_passed',$is_passed);



 //       $this->assignRef('items',$items);

		parent::display($tpl);
	}

}