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

    $phone = JRequest::getVar('phone');
    $ip = $_SERVER['REMOTE_ADDR'];
      if($phone[0]=="+"){
          $phone = substr($phone, 1);
      }
    $result = $ofService->registerPhone($phone, $ip);
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
      if( $ofService->verifyCode($phone, $code) ){
          $result = $ofService->createOrUpdateUser($phone, $code);
      }else{
          $result=array("status"=>"CODE_INCORRECT");
      }
      echo json_encode($result);
      exit;
	}

    public function get_contacts(){
        $jinput = JFactory::getApplication()->input;
        header('Content-Type: application/json');
        $phone = trim($jinput->getString("phone"));
        $contacts = $jinput->get('contacts', array(), 'ARRAY');

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
            $preparedPhones = array();
            foreach($contacts as $contactPhone){
                try {
                    $numberProto = $phoneUtil->parse($contactPhone, $regionCode);
                    $countryCode = $numberProto->getCountryCode();
                    $nationalNumber = $numberProto->getNationalNumber();
                    $preparedPhones[] = $countryCode . $nationalNumber;
                }catch (\libphonenumber\NumberParseException $e) {
                    //do nothing. just skip this phone.
                }
            }
        require_once dirname(__FILE__).'/classes/OpenFireService.php';
        $ofService = new OpenFireService();
        $result = $ofService->filterContacts($preparedPhones);

            echo(json_encode(array('status'=>'OK',
                                   'contacts'=>$result)));

        exit;
    }
}
