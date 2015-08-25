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
}
