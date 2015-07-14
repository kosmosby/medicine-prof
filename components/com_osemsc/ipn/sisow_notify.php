<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R);
*/

//if($_POST) {
	header("HTTP/1.0 200 OK");
	//global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mailfrom, $fromname;
	/*** access Joomla's configuration file ***/
	// Set flag that this is a parent file
	define('_JEXEC', 1);
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define('DS', DIRECTORY_SEPARATOR);
	require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
	require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe= & JFactory :: getApplication('site');
	jimport('joomla.plugin.helper');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/

	/**
	* Read post from PayPal system and create reply
	* starting with: 'cmd=_notify-validate'...
	* then repeating all values sent: that's our VALIDATION.
	**/
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$db= oseDB :: instance();
	
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	
	$MerchantId = $oseMscConfig->sisow_merchant_id;
	$testmode = $oseMscConfig->sisow_testmode;
	$MerchantKey = $oseMscConfig->sisow_merchant_key;
	$Shop_id = $oseMscConfig->sisow_shop_id;
	
	$sTransactionId = JRequest :: getVar('trxid',null);
	$sTransactionCode = JRequest :: getVar('ec',null);
	$sTransactionStatus = JRequest :: getVar('status',null);
	$sSignature = JRequest :: getVar('sha1',null);
	
	$where= array();
	$where[]= "`order_id`=".$db->quote($sTransactionCode);
	$payment= oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
	
	if(empty($orderInfo)) 
	{
		$mailsubject= "IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order number feedbacked by The IPN
				----------------------------------\n
				Invoice: ".$sTransactionCode."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$message = 'Transaction Failed.';
    	$redirectUrl = $server."index.php?option=com_osemsc&view=register";
    	$mainframe->redirect($redirectUrl,$message);
	}
		
	$order_id = $orderInfo->order_id;
	$orderInfoParams= oseJson :: decode($orderInfo->params);
	$redirectUrl = urldecode($orderInfoParams->returnUrl);
	$redirectUrl = $redirectUrl?$redirectUrl:$server."index.php?option=com_osemsc&view=member";	
			
	if(($sTransactionCode !== false) && ($sTransactionStatus !== false) && ($sSignature !== false))
	{
		$sHash = sha1($sTransactionId . $sTransactionCode . $sTransactionStatus . $MerchantId . $MerchantKey);
		if(strcasecmp($sSignature, $sHash) !== 0)
		{
			//$result['error'] = 'Status Request Error: Invalid signature.';
			$redirectUrl = $server."index.php?option=com_osemsc&view=register";
			$mainframe->redirect($redirectUrl,'Status Request Error: Invalid signature.');
		}
		
		if((strtoupper($sTransactionStatus) == 'SUCCESS'))
		{
			$payment= oseRegistry :: call('payment')->getInstance('Order');
			$payment->confirmOrder($order_id, array());
			$redirectUrl = JROUTE::_($server."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id);
			$mainframe->redirect($redirectUrl);
		}else{
			$redirectUrl = $server."index.php?option=com_osemsc&view=register";
			$mainframe->redirect($redirectUrl,$sTransactionStatus);
		}
	}else{
		$redirectUrl = $server."index.php?option=com_osemsc&view=register";
		$mainframe->redirect($redirectUrl,'Unknown Error');
	}
//}

?>