<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_openfire
 * @since       1.5
 */
require_once __DIR__.'/vendor/autoload.php';

class OpenfireController extends JControllerLegacy
{
	/**
	 * Show the form so that the user can send the link to someone.
	 *
	 * @return  void
	 *
	 * @since 1.5
	 */
  public function register_phone()
  {
      header('Content-Type: application/json');
      require_once dirname(__FILE__).'/classes/OpenFireService.php';
      $ofService = new OpenFireService();

      $phone = trim(JRequest::getVar('phone'),'');
      if(empty($phone)){
          echo json_encode(array('status'=>'BAD_PHONE'));
          exit;
      }
      if($phone[0]!="+"){
          $phone = '+'.$phone;
      }

      $name = trim(JRequest::getVar('name'),'');
      if(empty($name)){
          echo json_encode(array('status'=>'BAD_NAME'));
          exit;
      }
      //Verify that phone is correct.
      $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
      try {
          $numberProto = $phoneUtil->parse($phone);
          $countryCode = $numberProto->getCountryCode();
          $regionCode = $phoneUtil->getRegionCodeForCountryCode($countryCode);
      } catch (\libphonenumber\NumberParseException $e) {
          echo json_encode(array('status'=>'BAD_PHONE'));
          exit;
      }
      if(!$phoneUtil->isValidNumber($numberProto)){
          echo json_encode(array('status'=>'BAD_PHONE'));
          exit;
      }
      $ip = $_SERVER['REMOTE_ADDR'];
      if($phone[0]=="+"){
          $phone = substr($phone, 1);
      }
      $result = $ofService->registerPhone($phone, $ip, $name);
      echo json_encode(array('status'=>$result));
      exit;
  }

	public function verify_code()
	{
      header('Content-Type: application/json');
      require_once dirname(__FILE__).'/classes/OpenFireService.php';
      $ofService = new OpenFireService();

      $phone = JRequest::getVar('phone');
      $code = JRequest::getVar('code');
        $code = strtoupper($code);
        if($phone[0]=="+"){
            $phone = substr($phone, 1);
        }

      $result = $ofService->verifyCode($phone, $code);
      echo json_encode($result);
      exit;
	}

    public function get_contacts(){
        $jinput = JFactory::getApplication()->input;
        header('Content-Type: application/json');

        $user = trim($jinput->getString("user"));
        $arr = explode('@', $user);
        $phone = $arr[0];
        
        $contactPhones = $jinput->get('contact_phones', array(), 'ARRAY');
        $contactNames = $jinput->get('contact_names', array(), 'ARRAY');

        if($phone[0]!='+'){
            $phone = '+'.$phone;
        }
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($phone);
            $countryCode = $numberProto->getCountryCode();
            $regionCode = $phoneUtil->getRegionCodeForCountryCode($countryCode);
        } catch (\libphonenumber\NumberParseException $e) {
            echo json_encode(array('status'=>'BAD_PHONE'));
            exit;
        }

        $contacts = array();
        for($i=0;$i<count($contactPhones);$i++){
            $contacts[] = array("phone"=>$contactPhones[$i], "name"=>$contactNames[$i]);
        }
        $preparedPhones = array();

        foreach ($contacts as &$contact) {
            try {
                $contactPhone = $contact["phone"];
                $numberProto = $phoneUtil->parse($contactPhone, $regionCode);
                $countryCode = $numberProto->getCountryCode();
                $nationalNumber = $numberProto->getNationalNumber();
                $preparedPhones[] = $countryCode . $nationalNumber;
                $contact['phoneCanonical'] = $countryCode . $nationalNumber;

            } catch (\libphonenumber\NumberParseException $e) {
                //do nothing. just skip this phone.
            }
        }

        require_once dirname(__FILE__).'/classes/OpenFireService.php';
        $ofService = new OpenFireService();
        $result = $ofService->filterContacts($preparedPhones, $user);

        foreach ($contacts as $key => $contact) {
            if(in_array($contact["phoneCanonical"], $result) ){
                $contacts[$key]['jabberUsername'] = $contact["phoneCanonical"]."@medicine-prof.com";
                $contacts[$key]['contactAdded'] = false;
                $contacts[$key]['contactExists'] = true;
            }else{
                $contacts[$key]['jabberUsername'] = null;
                $contacts[$key]['contactAdded'] = false;
                $contacts[$key]['contactExists'] = false;
            }
        }
            echo(json_encode(array('status'=>'OK',
                                   'contacts'=>$contacts)));

        exit;
    }
}
