<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * flexpaper Controller
 */
class flexpaperControllerCertificate extends JControllerForm
{

    function sendcert() {

        $cert_image = $this->generateImageCertificate();

        $this->getPdf($cert_image->outputfilepathname);

        $this->sendMail();
    }

    function getPdf($image) {

        require (JPATH_SITE . "/components/com_flexpaper/fpdf/fpdf.php");

        $filePath = JPATH_SITE . "/components/com_flexpaper/output/certificate.pdf";

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

        $filePath = JPATH_SITE . "/components/com_flexpaper/output/certificate.pdf";

        $mailer =& JFactory::getMailer();
        $config =& JFactory::getConfig();

        //sender
        $sender = array(
            $config->getValue( 'config.mailfrom' ),
            $config->getValue( 'config.fromname' ) );

        $mailer->setSender($sender);


        //recipient
        $recipient = array( $this->getEmail($user_id) );
        $mailer->addRecipient($recipient);

        $body   = "Your body string\nin double quotes if you want to parse the \nnewlines etc";
        $mailer->setSubject('Your subject string');
        $mailer->setBody($body);
        // Optional file attached
        $mailer->addAttachment($filePath);

        $send =& $mailer->Send();

//        echo "<pre>";
//        print_r($send); die;


        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->message;
        } else {
            echo 'Mail with certificate was sent on email address '.$this->getEmail($user_id);
        }

        exit();
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

    function getCourseName() {

        $testid = JRequest::getVar('cert_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        //get course name

        $query = "SELECT a.title from #__osemsc_acl as a, #__flexpaper_quiz as b where b.test_id = ".$testid." AND b.membership_list_id = a.id";
        $db->setQuery( $query );
        $coursename = $db->loadResult();

        return $coursename;
    }


    function getDateoftest() {

        $user_id = JRequest::getVar('user_id');
        $testid = JRequest::getVar('cert_id');

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

    function prepareDataForCertificate() {

        $user_id = JRequest::getVar('user_id');

        $dateoftest = $this->getDateoftest();
        $username = $this->getUsername($user_id);
        $coursename = $this->getCourseName();
        $sertificate_no = $this->getSertificateNo();

        $locX = 385;
        $locY = 392;
        $locS =20;
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

        $rows[2]->text = $coursename;
        $rows[2]->x = 220;
        $rows[2]->y = 440;
        $rows[2]->size = $locS;
        $rows[2]->align = $locA;

        $rows[3]->text = $sertificate_no;
        $rows[3]->x = 570;
        $rows[3]->y = 528;
        $rows[3]->size = 12;
        $rows[3]->align = $locA;

        return $rows;
    }

    function getSertificateNo() {

        $user_id = JRequest::getVar('user_id');
        $testid = JRequest::getVar('cert_id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "Select cert_id from #__flexpaper_certificate where user_id = ".$user_id." AND tid=".$testid;
        $db->setQuery( $query );
        $row = $db->loadResult();

        return $row;
    }


    function generateImageCertificate() {

        $type = JRequest::getVar('type');
        $user_id = JRequest::getVar('user_id');

        if($type=='ks_certificate') {
            $name = 'ks.jpg';
        }
        else if($type=='bs_certificate') {
            $name = 'bs.jpg';
        }

        $file 	= JPATH_SITE . "/components/com_flexpaper/certificates/" . $name;

        require (JPATH_SITE . "/components/com_flexpaper/img.class.php");

        $myimage = new rscImage($file) ;

        $rows = $this-> prepareDataForCertificate();

        for( $i = 0; $i < count($rows); $i++ ){
            $myimage->addText( $rows[$i]->text, $rows[$i]->x, $rows[$i]->y, $rows[$i]->size, $rows[$i]->align);
        }

        $prefix = JPATH_SITE . '/components/com_flexpaper/output/lms_' . $user_id . "_" ;

        $outfile = new rscUniqFName( $prefix, '.png' );

        $output = $outfile->getName();
//$myimage->drawGraph();

        $myimage->writeGraph($output);

        return $myimage;
    }

}