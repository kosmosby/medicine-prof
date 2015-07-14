<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
/**
 * flexpaper Component Controller
 */
class flexpaperController extends JControllerLegacy
{
    function viewCertificate() {
        $cert_name = JRequest::getVar('certificate');

        $info = $this->getInfoByCertName($cert_name);

        $cert_image = $this->generateImageCertificate(strtolower(substr($info->cert_id, 0,2))."_certificate", $info->user_id, $info->tid, true);

        echo str_replace(JPATH_SITE, JURI::base(),$cert_image->outputfilepathname);
        exit();
    }

    function getInfoByCertName($cert_name) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT * FROM #__flexpaper_certificate WHERE cert_id = ".$db->quote($cert_name);
        $db->setQuery( $query );
        $row = $db->loadObject();

        //echo $db->geterrormsg();
        return $row;
    }

    function createcertificateAJAX() {

        $course_id = JRequest::getVar('course_id');
        $user_id = JRequest::getVar('user_id');
        $passed = JRequest::getVar('passed');
        $testid = JRequest::getVar('testid');

        if($this->getTid($course_id)) {

            $this->insertResultRecord($user_id, $this->getTid($course_id), $testid);
            $cert = $this->CreateCertificate($this->getTid($course_id),$user_id, $passed, 1);

            $this->sendresults(42, $cert);
            exit();
        }
        else {
          echo "Quiz for that certificate wasn't created. Create quiz first";
              exit();
        }
    }

    function getTid($course_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select test_id from #__flexpaper_quiz WHERE membership_list_id = ".$course_id;
        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;
    }

    function insertResultRecord($userId, $testid, $productID) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


	if($userId && $testid) {

		$query = "DELETE FROM #__lms_results WHERE userid =".$userId." AND tid=".$testid;
		$db->setQuery($query);
		$db->query();

		$query = "INSERT INTO #__lms_results SET "
		    . "userid ='" . $userId. "',"
		    . "tid ='" . $testid . "',"
		    . "date_created = '" . date("Y-m-d H:i:s") . "',"
	//            . "testid ='" . $productID . "',"
		    . "paid ='1',"
		    . "approved ='1',"
		    . "checked_out_time ='0',"
		    . "passed = '1',"
            . "score = '100'";

		$db->setQuery($query);
		$db->query();

        //echo $db->geterrormsg(); die;

	}
    }

    function CreateCertificate($testid,$user_id, $passed, $forAdmin=0) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT id from #__flexpaper_certificate WHERE user_id = ".$user_id." AND tid = ".$testid;
        $db->setQuery( $query );
        $isset = $db->loadResult();

        $rand = $this->RandomString(6, $passed);

        if(!$isset) {
            $query = "INSERT INTO #__flexpaper_certificate (`cert_id`,`user_id`,`tid`,`date_created`) VALUES ('".$rand."',".$user_id.",".$testid.",NOW())";
        } else {
            $query = "UPDATE #__flexpaper_certificate  SET `cert_id` = '".$rand."',`user_id` = ".$user_id.",`tid` = ".$testid." WHERE id = ".$isset;
        }

        $db->setQuery( $query );
        $db->query();

        $this->addAnswersForCreatedByAdminCertificate($testid, $user_id);

        if($forAdmin) {
            if(!$db->geterrormsg()) {
                echo JText::_('COM_FLEXPAPER_CERTIFICATE_WAS_CREATED');
            }
            else {
                echo $db->geterrormsg();
            }
        }

        return $rand;
    }

    function addAnswersForCreatedByAdminCertificate($testid, $user_id) {

        require_once( JPATH_SITE.''.DS.'components'.DS. 'com_flexpaper' . DS .'models'. DS. 'questions.php');
        $questions_model = JModel::getInstance('questions', 'FlexpaperModel');

//      echo "<pre>";
//      print_r($questions_model->getCorrectAnswers($testid)); die;

        $questions_model->WriteUserResultinDB($testid, $questions_model->getCorrectAnswers($testid), $user_id);
    }


    function RandomString($length, $passed) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        //$original_string = array_merge(range(0,9), range('A', 'Z'));

        $original_string = array_merge(range(0,9), array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','R','S','T','U','V','Y','Z'));

        $original_string = implode("", $original_string);
        $string = substr(str_shuffle($original_string), 0, $length);

        $string = $passed?'BS-'.$string:'KS-'.$string;

        $proceed = true;
        while($proceed)
        {
            $query = "SELECT COUNT(*) as cert_id FROM #__flexpaper_certificate WHERE cert_id= ".$db->quote($string)."";
            $db->setQuery( $query );
            $row = $db->loadResult();

            if($row == 0) $proceed = false;
            else $this->RandomString($length, $passed);
        }

        return $string;
    }


    function deletecertificate() {
        $testid = JRequest::getVar('cert_id');
        $user_id = JRequest::getVar('user_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        //get course name

        $query = "DELETE from #__lms_results where userid = ".$user_id." AND tid  = ".$testid."";
        $db->setQuery( $query );
        $db->Query();

        $query = "DELETE from #__flexpaper_quiz_results where user_id = ".$user_id." AND tid  = ".$testid."";
        $db->setQuery( $query );
        $db->Query();

        $query = "DELETE from #__flexpaper_certificate where user_id = ".$user_id." AND tid  = ".$testid."";
        $db->setQuery( $query );
        $db->Query();

        if(!$db->geterrormsg()) {
            echo JText::_('COM_FLEXPAPER_CERTIFICATE_AND_RESULTS_WERE_DELETED');
        }
        else {
            echo $db->geterrormsg();
        }
        exit();
    }

    function list_docs() {
        $vName	= 'flexpapers';
        JRequest::setVar('view', $vName);

        return parent::display();
    }

    function list_video() {
    $vName	= 'video';
    JRequest::setVar('view', $vName);

    return parent::display();
    }

    function list_settings() {
        $vName	= 'settings';
        JRequest::setVar('view', $vName);

        return parent::display();
    }

    function list_conference() {
        $vName	= 'conference';
        JRequest::setVar('view', $vName);

        return parent::display();
    }


    function click_doc() {
        $id = JRequest::getInt('id', 0);

        if ($id) {

            $model = $this->getModel ( 'flexpaper' );
            $view =& $this->getView( 'flexpaper', 'html' );


            $view->setModel( $model, true );  // true is for the default model;

            $flexpapers = $this->getModel ( 'flexpapers' );
            $flexpapers->_name = 'flexpapers';
            $view->setModel( $flexpapers );

            //$vName	= 'flexpaper';
            //JRequest::setVar('view', $vName);

            return parent::display();
        }
    }


    function certificates() {
        $vName = 'certificates';
        JRequest::setVar('view', $vName);

        return parent::display();
    }

    function runtest() {
        $vName = 'runtest';
        JRequest::setVar('view', $vName);


        return parent::display();
    }

    function gettest() {

        $testid = JRequest::getInt('testid', 0);

        if ($testid) {

            $model = $this->getModel ( 'questions' );
            $view =& $this->getView( 'Questions', 'html' );

            $view->setModel( $model, true );  // true is for the default model;

            $view->display();
            jexit();
        }
    }

    function finishtest() {

        $model = $this->getModel ( 'questions' );
        $view =& $this->getView( 'Questions', 'html' );

        $view->setModel( $model, true );
        $view->show_resulst_test('result');

        $this->sendresults(42);
        $this->sendresults();

        jexit();
    }


    function courses_list() {
        $vName = 'courses';
        JRequest::setVar('view', $vName);

        return parent::display();
    }

    function membersarea() {

    $user =& JFactory::getUser();

        if (!$user->guest) {
//            echo 'You are logged in as:<br />';
//            echo 'User name: ' . $user->username . '<br />';
//            echo 'Real name: ' . $user->name . '<br />';
//            echo 'User ID  : ' . $user->id . '<br />';

            $vName = 'membersarea';
            JRequest::setVar('view', $vName);

            return parent::display();
        }
        else {
            echo "Please authorize to view this this resourse";
        }

    }


    function quizes() {
        $user =& JFactory::getUser();

        if (!$user->guest) {
            $vName = 'quizes';
            JRequest::setVar('view', $vName);

            return parent::display();
        }
        else {
            echo "Please authorize to view this this resourse";
        }

    }



    //send email

    function sendresults( $recepient_admin = '', $cert= '') {

        $results = $this->getResults();

 //       echo "<pre>";
  //      print_r($results); die;


        if(count($results)) {
            $this->getPdfResultsQuiz($results, $recepient_admin);
	 $this->sendMailResults($recepient_admin, $cert);	
        }
	jexit();
       
    }

    function resultsHTML($results) {

        $string = '# Question Answer Correct Answer /n';

        for($i=0;$i<count($results);$i++) {

            $string .= ($i+1).$results[$i]->question.' '.$results[$i]->answer.' '.$results[$i]->correct_answer.' /n';
        }
        return $string;
    }

    function getResults() {

        $testid = JRequest::getVar('cert_id');

        if(!$testid && JRequest::getVar('testid')) {
            $testid = JRequest::getVar('testid');
        }

        $user_id = JRequest::getVar('user_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        //get course name

        $query = "SELECT a.*, b.qtype, b.a1, b.a2, b.a3, b.a4, b.a5, b.answer as mc5_answer from #__flexpaper_quiz_results as a, #__lms_questions as b where a.question_id = b.id AND a.user_id = ".$user_id." AND a.tid  = ".$testid." AND a.time IN (SELECT MAX(time) FROM #__flexpaper_quiz_results WHERE user_id = ".$user_id." AND tid  = ".$testid.") ";
        $db->setQuery( $query );
        $results = $db->loadObjectList();

        //echo $db->geterrormsg(); die;


        if(!count($results)) {
            echo JText::_('COM_FLEXPAPER_RESULT_DOESNT_EXIST');
            //exit();
        }

        return $results;
    }

    function sendcert() {

	 $results = $this->getResults();

	 if(count($results)) {
        	$cert_image = $this->generateImageCertificate();
        	$this->getPdf($cert_image->outputfilepathname);
        	$this->sendMail();
	 }
	jexit();


    }

    function getPdfResultsQuiz($rows, $recepient_admin= '') {

        //define('FPDF_FONTPATH',JPATH_SITE.'/components/com_flexpaper/fonts/');

        require_once (JPATH_SITE . "/components/com_flexpaper/tfpdf/tfpdf.php");

        $results_file = JPATH_SITE . "/components/com_flexpaper/output/sonuclar.pdf";

        $pdf = new tFPDF('L','mm',array(500,500));

        $pdf->AddPage();

        //$pdf->SetFont('Arial','B',10);

        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
        $pdf->SetFont('DejaVu','',10);

        $pdf->Cell(5,10,'#');
        $pdf->Cell(220,10,'Soru');
        $pdf->Cell(140,10,'Cevap');
        $pdf->Cell(100,10,'Doğru Cevap');

        $pdf->ln();

        $question_number = 1;
        for($i=0;$i<count($rows);$i++) {


           if($recepient_admin) {

//                echo "<pre>";
//                print_r($rows); die;

                switch($rows[$i]->qtype) {
                    case 'sa':
                        $rows[$i]->correct_answer = $rows[$i]->a1;
                        $rows[$i]->correct_answer .= ($rows[$i]->a2)?', '.$rows[$i]->a2:'';
                        $rows[$i]->correct_answer .= ($rows[$i]->a3)?', '.$rows[$i]->a3:'';
                        $rows[$i]->correct_answer .= ($rows[$i]->a4)?', '.$rows[$i]->a4:'';
                        $rows[$i]->correct_answer .= ($rows[$i]->a5)?', '.$rows[$i]->a5:'';
                    break;
                    case 'mc5':
                        $number = 'a'.$rows[$i]->mc5_answer;
                        $rows[$i]->correct_answer = $rows[$i]->$number;
                    break;
                }
            }


            $pdf->Cell(5,10,"$question_number");
            $pdf->Cell(220,10,substr($rows[$i]->question,0,136));
            $pdf->Cell(140,10,$rows[$i]->answer);
            $pdf->Cell(100,10,$rows[$i]->correct_answer);
            $pdf->ln();

            $question_number++;
        }


        $pdf_file = $pdf->Output($results_file,'F');

        return $pdf_file;
    }



    function getPdf($image) {

        require (JPATH_SITE . "/components/com_flexpaper/fpdf/fpdf.php");

        $filePath = JPATH_SITE . "/components/com_flexpaper/output/sertifika.pdf";

        $pdf = new FPDF('L','mm');
        $pdf->SetLeftMargin(0);
        $pdf->SetRightMargin(0);
        $pdf->SetTopMargin(0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $pdf->Image($image);

        $pdf_file = $pdf->Output($filePath,'F');

        return $pdf_file;
    }

    function sendMail() {

        $user_id = JRequest::getVar('user_id');
        $recipient_admin = JRequest::getVar('recipient_admin');

        $filePath = JPATH_SITE . "/components/com_flexpaper/output/sertifika.pdf";

        $mailer =& JFactory::getMailer();
        $config =& JFactory::getConfig();

        //sender
        $sender = array(
            $config->getValue( 'config.mailfrom' ),
            $config->getValue( 'config.fromname' ) );

        $mailer->setSender($sender);

//        //recipient
//        $recipient = array( $this->getEmail($user_id) );

        if($recipient_admin) {
            $recipient[] = 'ahmet@kaliteegitimleri.com';
            $recipient[] = 'info@kaliteegitimleri.com';
        }
        else {
            //recipient
            $recipient = array( $this->getEmail($user_id) );
        }

//        $recipient[] = 'ahmet@kaliteegitimleri.com';
//        $recipient[] = 'info@kaliteegitimleri.com';

        $mailer->addRecipient($recipient);

        $body   = JText::_('COM_FLEXPAPER_MAIL_BODY_CERTIFICATE');
        $mailer->setSubject(JText::_('COM_FLEXPAPER_YOUR_SUBJECT_STRING_CERTIFICATE'));
        $mailer->setBody($body);
        // Optional file attached
        $mailer->addAttachment($filePath);

        $send =& $mailer->Send();

//        echo "<pre>";
//        print_r($send); die;


        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->message;
        } else {
            echo JText::_('COM_FLEXPAPER_MAIL_WITH_RESULTS_CERTIFICATE').$this->getEmail($user_id);
        }

        exit();
    }


    function sendMailResults( $recipient_admin = '' , $cert = '') {

        $user_id = JRequest::getVar('user_id');
        $testid = JRequest::getVar('testid');


        if($recipient_admin) {
            $body_for_admin = $this->user_information_for_email(JRequest::getVar('user_id'), $cert);
            $user_id = $recipient_admin;
       }
        else {
            $body_for_admin = '';
        }

        $filePath = JPATH_SITE . "/components/com_flexpaper/output/sonuclar.pdf";

        $mailer =& JFactory::getMailer();
        $config =& JFactory::getConfig();

        //sender
        $sender = array(
            $config->getValue( 'config.mailfrom' ),
            $config->getValue( 'config.fromname' ) );

        $mailer->setSender($sender);

        if($recipient_admin) {
            $recipient[] = 'ahmet@kaliteegitimleri.com';
            $recipient[] = 'info@kaliteegitimleri.com';
        }
        else {
        //recipient
         $recipient = array( $this->getEmail($user_id) );
        }

        $mailer->addRecipient($recipient);

        $body = '';
        if($recipient_admin && !$testid) {
            //$body   = JText::_('COM_FLEXPAPER_MAIL_BODY_RESULTS');
            //$body .="\n\n";
            $body .= JText::_('COM_FLEXPAPER_MAIL_BODY_ADDITION_MESSAGE_FOR_ADMIN');
        }
        else {
            $body   = JText::_('COM_FLEXPAPER_MAIL_BODY_RESULTS');
        }

        $body .= $body_for_admin;

//        if($recipient_admin) {
//            $body .="\n\n";
//            $body .= JText::_('COM_FLEXPAPER_MAIL_BODY_ADDITION_MESSAGE_FOR_ADMIN');
//        }



        if($recipient_admin && !$testid) {
            $mailer->setSubject(JText::_('COM_FLEXPAPER_YOUR_SUBJECT_STRING_RESULTS_ADMIN'));
        }
        else {
            $mailer->setSubject(JText::_('COM_FLEXPAPER_YOUR_SUBJECT_STRING_RESULTS'));
        }

        $mailer->setBody($body);
        // Optional file attached

        //if(!$recipient_admin) {
            $mailer->addAttachment($filePath);
        //}

        $send =& $mailer->Send();

        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->message;
        } else {
            echo JText::_('COM_FLEXPAPER_MAIL_WITH_RESULTS_RESULTS').$this->getEmail($user_id);
        }

        //exit();
    }

    function user_information_for_email($user_id = '', $cert = '') {


        $testid = JRequest::getVar('testid');
        $user_id = JRequest::getVar('user_id');
        $course_id = JRequest::getVar('course_id');

        require_once( JPATH_SITE.''.DS.'components'.DS. 'com_flexpaper' . DS .'models'. DS. 'questions.php');
        $questions_model = JModel::getInstance('questions', 'FlexpaperModel');

        $score = $questions_model->getUserScoreMark($testid);

        $billing_info = $this->getBillingInfo($user_id);

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $string =  "\n\n\n".JText::_('COM_FLEXPAPER_USERNAME').': '.$billing_info->firstname. ' '.$billing_info->lastname. " \n";
        $string .=  JText::_('COM_FLEXPAPER_COMPANY').': '.$billing_info->company. " \n";
        $string .=  JText::_('COM_FLEXPAPER_ADDR1').': '.$billing_info->addr1. " \n";
        $string .=  JText::_('COM_FLEXPAPER_ADDR2').': '.$billing_info->addr2. " \n";
        $string .=  JText::_('COM_FLEXPAPER_CITY').': '.$billing_info->city. " \n";
        $string .=  JText::_('COM_FLEXPAPER_COUNTRY').': '.$billing_info->country. " \n";
        $string .=  JText::_('COM_FLEXPAPER_POSTCODE').': '.$billing_info->postcode. " \n";
        $string .=  JText::_('COM_FLEXPAPER_TELEPHONE').': '.$billing_info->telephone. " \n";
        if($cert) {
            $string .= "\n".JText::_('COM_FLEXPAPER_CERTIFICATE_NUMBER').': '.$cert. "\n";
        }
        else {
            $string .= "\n".JText::_('COM_FLEXPAPER_CERTIFICATE_NUMBER').': '.$this->getSertificateNo($user_id, $testid). "\n";
        }

        if($testid) {
            $string .= JTEXT::_('COM_FLEXPAPER_COURSE_NAME').': '.$this->getCourseName($testid). "\n";
        }
        elseif($course_id) {
            $string .= JTEXT::_('COM_FLEXPAPER_COURSE_NAME').': '.$this->getCourseNameById($course_id). "\n";
        }

        if($testid) {
            $string .= JTEXT::_('COM_FLEXPAPER_USER_SCORE').': '.$score. "% \n";
        }

        $string .= JTEXT::_('COM_FLEXPAPER_EMAIL_ADDRESS').': '.$this->getEmail($user_id). " \n";
        $string .= JText::_('COM_FLEXPAPER_CREATION_DATE').': '.date('d/m/Y');

        $bundleInfo = $this->getBundleInfo($testid);

        if($bundleInfo->last == 2) {
            $string .="\n \n".JText::_('COM_FLEXPAPER_COURSE_STATUS').': '.$bundleInfo->courseStatus." ".JText::_('COM_FLEXPAPER_COMPLETED')." \n";
            if(isset($bundleInfo->theLastCourseName) && $bundleInfo->theLastCourseName) {
                $string .=JText::_('COM_FLEXPAPER_LAST_COURSE_NAME').': '.$bundleInfo->theLastCourseName. " \n";
            }

            if($this->showCertificatesOfPrevious($bundleInfo->certificateOfPrevious, $this->getSertificateNo($user_id, $testid))) {
                $string .=JText::_('COM_FLEXPAPER_CERTIFICATES_OF_PREVIOUS').': '.$this->showCertificatesOfPrevious($bundleInfo->certificateOfPrevious, $this->getSertificateNo($user_id, $testid)). " \n";
            }

        }

        elseif($bundleInfo->last) {
             $string .="\n \n".JText::_('COM_FLEXPAPER_PURCHASED_COURSE').': '.$bundleInfo->bundle_name. " \n";
             $string .=JText::_('COM_FLEXPAPER_DATE_OF_PURCHASE').': '.$bundleInfo->date_of_purchase. " \n";

             if($bundleInfo->methodOfPayment=='m') {
                 $string .=JText::_('COM_FLEXPAPER_METHOD_OF_PAYMENT').': Pay-offline'. " \n";
             }
             elseif($bundleInfo->methodOfPayment=='a') {
                 $string .=JText::_('COM_FLEXPAPER_METHOD_OF_PAYMENT').': Credit Card'. " \n";
             }

             $string .=JText::_('COM_FLEXPAPER_COURSE_STATUS').': '.JText::_('COM_FLEXPAPER_ALL_COURSES_HAVE_COMPLETED'). " \n";

             if($this->showCertificatesOfPrevious($bundleInfo->certificateOfPrevious, $this->getSertificateNo($user_id, $testid))) {
                $string .=JText::_('COM_FLEXPAPER_CERTIFICATES_OF_PREVIOUS').': '.$this->showCertificatesOfPrevious($bundleInfo->certificateOfPrevious, $this->getSertificateNo($user_id, $testid)). " \n";
             }
         }


        return $string;
    }

    function showCertificatesOfPrevious($rows, $already_cert_id) {

        $string = '';
        for($i=0;$i<count($rows);$i++) {

            if($rows[$i]->cert_id != $already_cert_id) {
                $string .= "\n".$rows[$i]->cert_id." ".date("d/m/Y", strtotime($rows[$i]->time))." ".$rows[$i]->title."\n";
            }
        }

        return $string;

    }


    function getBundleInfo($testid) {

        $bundleinfo = new stdClass();

        $user =& JFactory::getUser();
        $user_id = $user->id;

        $course_id =  JRequest::getVar('course_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        JLoader::import( 'courses', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $courses_model = JModel::getInstance( 'courses', 'flexpaperModel' );
        $courses_bought = $courses_model->courses_bought($user_id);

        if(in_array($course_id,$courses_bought)) {

            $bundle_ids = $this->getBundleIds($course_id);

            $bundle_ids = array_unique(array_intersect($courses_bought,$bundle_ids));

            if(count($bundle_ids)) {
                //need to think about it, just take a first id from the list of bundles
                foreach($bundle_ids as $k)  {
                    $bundle_id = $k; break; //take the first element
                }
                $list_courses_in_a_bundle = $this->listcourseinabundle($bundle_id);

                if(count($list_courses_in_a_bundle)) {

                   $PassedCourses = $this->PassedQuizes($list_courses_in_a_bundle);

                   $bundleinfo->certificateOfPrevious= $this->certificateOfPrevious($list_courses_in_a_bundle);

                   if(count($list_courses_in_a_bundle) != count($PassedCourses)) {
                       $bundleinfo->last = 2;
                       $bundleinfo->courseStatus = count($PassedCourses)."/".count($list_courses_in_a_bundle);

                       if(count($list_courses_in_a_bundle) - count($PassedCourses) == 1) {
                           $the_last_course = array_diff($list_courses_in_a_bundle,$PassedCourses);

                           foreach($the_last_course as $k=>$v) {
                               $bundleinfo->theLastCourseName = $this->getCourseNameById($v);
                           }
                       }
                   }
                   else {
                       $bundleinfo->last = 1;
                       $bundleinfo->bundle_name = $this->getCourseNameById($bundle_id);
                       $bundleinfo->date_of_purchase = date('d/m/Y');
                       $bundleinfo->methodOfPayment = $this->getMethodOfPayment($bundle_id);
                       $bundleinfo->courseStatus = 1;
                   }
                    $bundleinfo->certificateOfPrevious= $this->certificateOfPrevious($list_courses_in_a_bundle);
                }
//                echo "<pre>";
//                print_r($bundleinfo); die;
            }
       }
        return $bundleinfo;
    }

    public function certificateOfPrevious($list_courses_in_a_bundle) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $user =& JFactory::getUser();
        $user_id = $user->id;

        $query = "SELECT a.cert_id, b.time,c.title FROM #__flexpaper_certificate as a, #__flexpaper_quiz_results as b, #__osemsc_acl as c, #__flexpaper_quiz as d

        WHERE b.user_id = ".$user_id." AND a.user_id = ".$user_id." AND a.tid IN (".implode(',',$list_courses_in_a_bundle).") AND b.tid = a.tid AND a.tid = d.test_id AND d.membership_list_id = c.id GROUP BY b.tid";

        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        return $rows;
    }


    public function getMethodOfPayment($bundle_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $user =& JFactory::getUser();
        $user_id = $user->id;

        $query = "SELECT a.payment_mode FROM #__osemsc_order as a, #__osemsc_order_item as b where a.user_id = ".$user_id." AND b.entry_id = ".$bundle_id." AND b.order_item_id = a.order_id";

        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;



    }

    public function getCourseNameById($course_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT title FROM #__osemsc_acl where id = ".$course_id;

        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;
    }

    public function PassedQuizes($list_courses_in_a_bundle) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $user =& JFactory::getUser();
        $user_id = $user->id;

        $query = "SELECT tid FROM #__flexpaper_quiz_results where tid IN (".implode(',',$list_courses_in_a_bundle).") AND user_id = ".$user->id." GROUP BY tid";

        $db->setQuery( $query );
        $rows = $db->loadResultArray();

        return $rows;
    }



    public function listcourseinabundle($bundle_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT membership_list_id FROM #__flexpaper_bundle where bundle_id = ".$bundle_id;

        $db->setQuery( $query );
        $rows = $db->loadResultArray();

        return $rows;
    }

    public function getBundleIds($course_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT bundle_id FROM #__flexpaper_bundle where membership_list_id = ".$course_id;

        $db->setQuery( $query );
        $rows = $db->loadResultArray();

        return $rows;
    }




    function getBillingInfo($user_id) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select * from #__osemsc_billinginfo where user_id = ".$user_id;
        $db->setQuery( $query );
        $row = $db->loadObject();

        return $row;
    }


    function getUsername($user_id) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select name from #__users where id = ".$user_id;
        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;
    }

    function getEmail($user_id) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select email from #__users where id = ".$user_id;
        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;
    }

    function getCourseName($testid = '') {

        if(!$testid) {
            $testid = JRequest::getVar('cert_id');
        }    

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        //get course name

        $query = "SELECT a.title from #__osemsc_acl as a, #__flexpaper_quiz as b where b.test_id = ".$testid." AND b.membership_list_id = a.id";
        $db->setQuery( $query );
        $coursename = $db->loadResult();

        return $coursename;
    }


    function getDateoftest($user_id = '', $testid = '') {

        if(!$user_id) {
            $user_id = JRequest::getVar('user_id');
        }
        if(!$testid) {    
            $testid = JRequest::getVar('cert_id');
        }

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Get date of test
        $query = "SELECT UNIX_TIMESTAMP(date_created) as unixtime"
            . "\n FROM #__lms_results"
            . "\n WHERE userid = " . $user_id
            . "\n AND tid = " . $testid;

        $db->setQuery( $query );
        $dateoftest = $db->loadResult();

        return $dateoftest;
    }

    function prepareDataForCertificate($user_id = '', $testid = '') {

        if(!$user_id) {
            $user_id = JRequest::getVar('user_id');
        }

        $dateoftest = $this->getDateoftest($user_id, $testid);
        $username = $this->getUsername($user_id);
        $coursename = $this->getCourseName($testid);
        $sertificate_no = $this->getSertificateNo($user_id, $testid);

        $locX = 385;
        $locY = 392;
        $locS =15;
        $locA = 'left';

        $dateformat = 'd.m.Y';
        $dateX = 570;
        $dateY = 557;
        $dateS = 12;
        $dateA = 'left';

        $formatted_date = date($dateformat,$dateoftest);

        $rows = array();
        $rows[0]->text = $username;
        $rows[0]->x = $locX;
        $rows[0]->y = $locY;
        $rows[0]->size = $locS;
        $rows[0]->align = $locA;

        $rows[1]->text = $formatted_date;
        $rows[1]->x = $dateX;
        $rows[1]->y = $dateY;
        $rows[1]->size = $dateS;
        $rows[1]->align = $dateA;

        $rows[2]->text = '" '.$coursename.' "';
        $rows[2]->x = 530;
        $rows[2]->y = 440;
        $rows[2]->size = $locS;
        $rows[2]->align = 'center';

        $rows[3]->text = $sertificate_no;
        $rows[3]->x = 570;
        $rows[3]->y = 528;
        $rows[3]->size = 12;
        $rows[3]->align = $locA;

        return $rows;
    }

    function calculateXcoordinateForTitle($text) {
       $strlen = strlen($text);
       $length = (1121/70)*$strlen;
       $x = (1121 - $length)/2;
       return $x;
    }


    function getSertificateNo($user_id = '', $testid = '') {

        if(!$user_id) {
            $user_id = JRequest::getVar('user_id');
        }
        if(!$testid) {    
            $testid = JRequest::getVar('cert_id');
        }    

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select cert_id from #__flexpaper_certificate where user_id = ".$user_id." AND tid=".$testid;
        $db->setQuery( $query );
        $row = $db->loadResult();

        if(!$row) {
            echo JText::_('COM_FLEXPAPER_CERTIFICATE_DOESNT_EXIST');
            //exit();
        }

        return $row;
    }


    function generateImageCertificate($type = '', $user_id = '', $testid = '', $live_link = false) {

        if(!$type) {
            $type = JRequest::getVar('type');
        }
        if(!$user_id) {    
            $user_id = JRequest::getVar('user_id');
        }    

        if($type=='ks_certificate') {
            $name = 'ks.jpg';
        }
        else if($type=='bs_certificate') {
            $name = 'bs.jpg';
        }

        $file 	= JPATH_SITE . "/components/com_flexpaper/certificates/" . $name;

        require (JPATH_SITE . "/components/com_flexpaper/img.class.php");

        $myimage = new rscImage($file) ;

        $rows = $this-> prepareDataForCertificate($user_id, $testid);

        for( $i = 0; $i < count($rows); $i++ ){
            $myimage->addText( $rows[$i]->text, $rows[$i]->x, $rows[$i]->y, $rows[$i]->size, $rows[$i]->align);
        }

        //if($live_link) {
        //    $prefix = JURI::base() . 'components/com_flexpaper/output/lms_' . $user_id . "_" ;
        //}
        //else {
            $prefix = JPATH_SITE . '/components/com_flexpaper/output/' . $rows[3]->text;
        //}    

        $outfile = new rscUniqFName( $prefix, '.png' );

        $output = $outfile->getName();
//$myimage->drawGraph();

        $myimage->writeGraph($output);

        return $myimage;
    }


}

