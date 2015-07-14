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
class flexpaperModelQuestions extends JModelLegacy
{

	public function getItems($testid = '')
	{

        if(!$testid) {
            $testid = JRequest::getVar('testid');
        }

        if($testid) {
            $db = JFactory::getDBO();

            // all tests
            $query = $db->getQuery(true);
            $query = "SELECT * FROM #__lms_questions WHERE testid =".$testid;

            $db->setQuery($query);
            $this->_items = $db->loadobjectlist();

            return $this->_items;
        }
	}

    public function getCoursename()
    {

        $course_id = JRequest::getVar('course_id');


        if($course_id) {
            $db = JFactory::getDBO();

            // all tests
            $query = $db->getQuery(true);
            $query = "SELECT title FROM #__osemsc_acl WHERE id =".$course_id;

            $db->setQuery($query);
            $row = $db->loadResult();

            return $row;
        }
    }

    public function getResultTest() {

        $incorrect =  $this->getIncorrectAnswers();

        $passed = $this->CheckIfPassed($incorrect);

        $this->updateResultTables($passed);

        $this->WriteUserResultinDB();

        $this->updateCertificatesTable($passed);

        return $passed;
    }

    public function updateCertificatesTable($passed) {

        $testid = JRequest::getVar('testid');
        $user =& JFactory::getUser();

        require_once( JPATH_SITE.DS. DS . 'components' . DS . 'com_flexpaper' . DS . 'controller.php');
        $flexpaper_controller = JController::getInstance('flexpaper');

        $flexpaper_controller->CreateCertificate($testid,$user->id, $passed);

    }



    public function getIncorrectAnswers() {
        $questions = $this->getItems();
        $answers = JRequest::getVar('answer');
        $incorrect = 0;

        for($i=0;$i<count($questions);$i++) {

            $correct_answers = array();
            $correct_answers[] = strtolower(trim($questions[$i]->a1));
            $correct_answers[] = strtolower(trim($questions[$i]->a2));
            $correct_answers[] = strtolower(trim($questions[$i]->a3));
            $correct_answers[] = strtolower(trim($questions[$i]->a4));
            $correct_answers[] = strtolower(trim($questions[$i]->a5));

            $array_empty = array(null);
            $correct_answers = array_diff($correct_answers, $array_empty);

            $reg_ex = "[[:space:]]";
            $answers[$questions[$i]->id]= @ereg_replace($reg_ex,"",$answers[$questions[$i]->id]);

            //echo $answers[$questions[$i]->id]."<hr />";

            switch($questions[$i]->qtype) {
                case 'sa':
                    if(!$answers[$questions[$i]->id] || !in_array(strtolower(trim($answers[$questions[$i]->id])),$correct_answers)){
                        $incorrect++;
                    }
                    break;
                case 'mc5':
                    if(!$answers[$questions[$i]->id] || ($answers[$questions[$i]->id] != 'a'.$questions[$i]->answer)){
                        $incorrect++;
                    }
                    break;
            }
        }

        //echo $incorrect; die;

        return $incorrect;
    }


    public function getCorrectAnswers($testid) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT * FROM #__lms_questions WHERE testid =".$testid;

        $db->setQuery($query);
        $rows = $db->loadObjectList();


        $correct_answers = array();
        for($i=0;$i<count($rows);$i++) {

            switch($rows[$i]->qtype) {
                case 'sa':
                    $correct_answers[$rows[$i]->id] = $rows[$i]->a1;
                break;
                case 'mc5':
                    $cor_answ = $rows[$i]->answer;
                    $correct_answers[$rows[$i]->id] = $rows[$i]->$cor_answ;
                break;
            }
        }
        return $correct_answers;
    }



    public function getUserScore() {

        $incorrect = $this->getIncorrectAnswers();

        $testid = JRequest::getVar('testid');
        $count_questions_in_the_test = $this->count_questions_in_the_test($testid);
        $correct_answers = $count_questions_in_the_test-$incorrect;

        $user_score = $correct_answers*100/$count_questions_in_the_test;

        return $user_score;
    }


    public function CheckIfPassed($incorrect) {

        $testid = JRequest::getVar('testid');

        $count_questions_in_the_test = $this->count_questions_in_the_test($testid);

        $passed_mark_for_the_test = $this->getPassedMarkForTheTest();

        $correct_answers = $count_questions_in_the_test-$incorrect;

        $user_score = $correct_answers*100/$count_questions_in_the_test;

        if($user_score >= $passed_mark_for_the_test) {
            $passed = 1;
        }
        else {
            $passed = 0;
        }

        return $passed;
    }


    public function getPassedMarkForTheTest() {

        $testid = JRequest::getVar('testid');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT passmark FROM #__lms_tests WHERE id =".$testid;

        $db->setQuery($query);
        $passmark = $db->loadResult();

        return $passmark;
    }

    public function count_questions_in_the_test($test_id) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT count(id) FROM #__lms_questions WHERE testid =".$test_id;

        $db->setQuery($query);
        $count_questions = $db->loadResult();

        return $count_questions;
    }


    public function updateResultTables($passed) {

        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $testid = JRequest::getVar('testid');

        $user_score = $this->getUserScore();

        $query = $db->getQuery(true);

        $query = "UPDATE #__lms_results SET passed = ".$passed.", score=".$user_score." WHERE tid = ".$testid." AND userid = ".$user->id;
        $db->setQuery( $query );
        $db->query();

    }

    function WriteUserResultinDB($testid = '', $answers = '', $user_id = '') {

        $for_admin = false;

        if(!$user_id) {
            $user =& JFactory::getUser();
            $user_id = $user->id;
        }
        else {
            $for_admin = true;
        }

        $questions = $this->getItems($testid);

//        if(!$for_admin) {
         $answers = JRequest::getVar('answer');
//        }

        if(!$testid) {
            $testid = JRequest::getVar('testid');
        }

        $db = JFactory::getDBO();
        $query = "DELETE FROM #__flexpaper_quiz_results WHERE user_id =".$user_id." AND tid=".$testid;
        $db->setQuery($query);
        $db->query();

        for($i=0;$i<count($questions);$i++) {

            switch($questions[$i]->qtype) {
                case 'sa':
                    $correct_answer_ = array();
                    $correct_answer_[] = $questions[$i]->a1;
                    $correct_answer_[] = $questions[$i]->a2;
                    $correct_answer_[] = $questions[$i]->a3;
                    $correct_answer_[] = $questions[$i]->a4;
                    $correct_answer_[] = $questions[$i]->a5;

                    $array_empty = array(null);
                    $correct_answer_ = array_diff($correct_answer_, $array_empty);

                    $user_answer =  $answers[$questions[$i]->id];

                    if(in_array($user_answer,$correct_answer_))
                        $correct_answer = $user_answer;
                    else {
                        $correct_answer = $questions[$i]->a1;
                     }

//                    if($for_admin) {
//                        $correct_answer = $questions[$i]->a1;
//                        if(isset($questions[$i]->a2) && $questions[$i]->a2) {
//                            $correct_answer .= ', '.$questions[$i]->a2;
//                        }
//                        if(isset($questions[$i]->a3) && $questions[$i]->a3) {
//                            $correct_answer .= ', '.$questions[$i]->a3;
//                        }
//                        if(isset($questions[$i]->a4) && $questions[$i]->a4) {
//                            $correct_answer .= ', '.$questions[$i]->a4;
//                        }
//                        if(isset($questions[$i]->a5) && $questions[$i]->a5) {
//                            $correct_answer .= ', '.$questions[$i]->a5;
//                        }
//                    }

                    break;
                case 'mc5':
                    $value = 'a'.$questions[$i]->answer;
                    $correct_answer = isset($questions[$i]->$value)?$questions[$i]->$value:'';

                    $answer_value = isset($answers[$questions[$i]->id])?$answers[$questions[$i]->id]:'';
                    $user_answer =  isset($questions[$i]->$answer_value)?$questions[$i]->$answer_value:'';

                    break;
            }

            $query = "INSERT INTO #__flexpaper_quiz_results"
                . "\n (`user_id`, `tid`,`question`,`answer`,`correct_answer`,`time`, `question_id`)"
                . "\n VALUES (".$user_id.", ".$testid.", ".$db->quote($questions[$i]->question).", ".$db->quote($user_answer).", ".$db->quote($correct_answer).", NOW(), ".$questions[$i]->id." )";

//echo $query; die;

            $db->setQuery( $query );
            $db->query();
        }
    }

    public function UserHadAttempt() {

        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $testid = JRequest::getVar('testid');

        $query = "SELECT * FROM #__flexpaper_quiz_results WHERE tid =".$testid." AND user_id = ".$user->id."";

        $db->setQuery($query);
        $answers = $db->LoadObjectList();

//        echo "<pre>";
//        print_r($answers); die;

        $arr = array();
        for($i=0;$i<count($answers);$i++) {
            $arr[$answers[$i]->question_id] = $answers[$i]->answer;
        }

        return $arr;

    }

    public function getUserScoreMark($test_id ='') {

        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        if(!$test_id) {
            $test_id = JRequest::getVar('testid');
        }

        $query = "SELECT score FROM #__lms_results WHERE tid =".$test_id." AND userid = ".$user->id."";

        $db->setQuery($query);
        $score = $db->LoadResult();

        return $score;

    }

    public function getUserPassedMark() {

        $user =& JFactory::getUser();
        $db = JFactory::getDBO();

        $testid = JRequest::getVar('testid');

        $query = "SELECT passed FROM #__lms_results WHERE tid =".$testid." AND userid = ".$user->id."";

        $db->setQuery($query);
        $passed = $db->LoadResult();

        return $passed;

    }

}
