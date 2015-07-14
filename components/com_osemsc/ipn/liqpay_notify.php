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
define("PAYPAL_DEBUG", 0);

if($_POST) {
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
	
	$resp = base64_decode($_POST['operation_xml']);
	$insig = $_POST['signature'];
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$merchant_id = $oseMscConfig->liqpay_merchant_id;	
	$signature = $oseMscConfig->liqpay_signature;
	
	$liqpay = new oseMscIpnLiqPay();
	$invoice = $liqpay->parseTag($resp, 'order_id');
	$status = $liqpay->parseTag($resp, 'status');
	
	$gensig = base64_encode(sha1($signature.$resp.$signature,1));
	
	$db= oseDB :: instance();
	$where= array();
	$where[]= "`order_id` = ".$db->quote($invoice);
	$payment= oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	
	if(empty($orderInfo)) 
	{
		$mailsubject= "LiqPay IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order id feedbacked by The IPN
				----------------------------------\n
				Invoice: ".$invoice."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		return;
	}
	$order_id= $orderInfo->order_id;
	$member_id= $orderInfo->user_id;
	$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());		
	$orderInfoParams= oseJson :: decode($orderInfo->params);
	$returnUrl = JROUTE::_($server."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id);
	$returnUrl = $returnUrl?$returnUrl:$server."index.php?option=com_osemsc&view=member";
	if ($insig == $gensig)
	{
		switch($status) 
		{
			case('success'):
				$payment= oseRegistry :: call('payment')->getInstance('Order');
				$payment->confirmOrder($order_id, array());	
				break;
			case('failure'):
				oseRegistry :: call('payment')->updateOrder($order_id, "invalid");

				$mailsubject= "LiqPay IPN Transaction on your site";
				$mailbody= "Hello,
							a Failed LiqPay Transaction requires your attention.
							-----------------------------------------------------------
							Order ID: ".$order_id."
							User ID: ".$member_id."
							Payment Status returned by PayPal: $status";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				break;
			case('wait_secure'):
				
				break;	
		}
	}else{
		oseRegistry :: call('payment')->updateOrder($order_id, "invalid");
		$liqpay->blockUser($member_id);
		$mailsubject= "Invalid LiqPay IPN Transaction on your site";
		$mailbody= "Hello,<br /><br />";
		$mailbody .= "An Invalid LiqPay Transaction requires your attention.<br />";
		$mailbody .= "-----------------------------------------------------------<br />";
		$mailbody .= "REMOTE IP ADDRESS: ".$_SERVER['REMOTE_ADDR']."<br />";
		$mailbody .= "Order ID: ".$order_id."<br />";
		$mailbody .= "User ID: ".$member_id."<br />";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
	}
	$mainframe->redirect($returnUrl);
}else{
	define('_JEXEC', 1);
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define('DS', DIRECTORY_SEPARATOR);
	require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
	require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
	$mainframe= & JFactory :: getApplication('site');
	jimport('joomla.plugin.helper');
	$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
	$returnUrl = $server."index.php?option=com_osemsc&view=member";
	$mainframe->redirect($returnUrl);
}	
class oseMscIpnLiqPay {
	function __construct() {
		
	}
	
	function parseTag($rs, $tag) {            
	   $rs = str_replace("\n", "", str_replace("\r", "", $rs));
	   $tags = '<'.$tag.'>';
	   $tage = '</'.$tag;
	   $start = strpos($rs, $tags)+strlen($tags);
	   $end = strpos($rs, $tage);
	   return substr($rs, $start, ($end-$start)); 
	 }
	 
	function blockUser($member_id) {
		// Block the user immediately;
		$db= & JFactory :: getDBO();
		$query= "UPDATE `#__users` SET `block` =  '1' WHERE `id` = ".(int) $member_id;
		$db->setQuery($query);
		$db->query();
		// Logout the user as well;
//		$query= "DELETE FROM `#__session` WHERE `userid` = ".(int) $member_id." AND `client_id` = 0";
//		$db->setQuery($query);
//		$db->query();
	}
}
?>