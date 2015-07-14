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
$messages= array();
function debug_msg($msg) {
	global $messages;
	if(PAYPAL_DEBUG == "1") {
		if(!defined("_DEBUG_HEADER")) {
			echo "<h2>VPcash Notify.php Debug OUTPUT</h2>";
			define("_DEBUG_HEADER", "1");
		}
		$messages[]= "<pre>$msg</pre>";
		echo end($messages);
	}
}

if($_POST) 
{
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
	$app = & JFactory :: getApplication('site');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/
	
	
	$oseMscConfig= oseRegistry :: call('msc')->getConfig('', 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	
	$conf_merchantEmail = $oseMscConfig->vpcash_account;
	$merchantSecureWord = $oseMscConfig->vpcash_secureword;
	
	
	//$test_mode= $oseMscConfig->epay_testmode;
	
	$data = array( 
	 'securEmail' => $_POST['securEmail'], 
	 'merchant' => $_POST['merchantAccount'], 
	 'amount' => $_POST['amount'], 
	 'x_firstname' => $_POST['x_firstname'], 
	 'currency' => $_POST['currency'], 
	 'buyerName' => $_POST['buyerName'], 
	 'userEmail' => $_POST['userEmail'], 
	 'userAccount' => $_POST['userAccount'], 
	 'encrypt' => $_POST['encrypt'], 
	 'date' => $_POST['date'], 
	 'item_id' => $_POST['item_id']
	); 
	
	$merchant = $_POST['merchantAccount'];
	$amount = $_POST['amount']; 
	$currency = $_POST['currency'];
	$secur_encrypted = sha1($merchant.$amount.$currency.$merchantSecureWord);

	$dp24_result = "FALSE";
	if($secur_encrypted == $_POST['encrypt']) 
	{
		$dp24_result = "TRUE";
	}
	
	if(!$dp24_result) 
	{
		$app->redirect(JRoute::_('index.php?option=com_osemsc&view=member',JText::_('Your Payment is failed!')));
	}
	else
	{
		
		$db= oseDB :: instance();
		$process = new oseMscIpnVPC($data);

		$where= array();
		$where[]= "`order_number` =".$db->Quote($process->get('item_id'));
		
		$payment = oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		
		if(empty($orderInfo)) 
		{
			$mailsubject= "VirtualPayVash IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The VirtualPayVash IPN
					----------------------------------\n
					Invoice: ".$process->get('item_id')."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			$res= 'Other Error!';
		}
		
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		$orderInfoParams= oseJson :: decode($orderInfo->params);

		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE order_id = '{$order_id}'"
				;
		$db->setQuery($query);
		$orderItems= oseDB :: loadList('obj');
		
		if($orderInfo->payment_mode == 'a')
		{
			$mailsubject= "VirtualPayVash Transaction on your Site";
			$mailbody = " Hello,An error occured while processing a VirtualPayVash transaction.\n"
					   ."------------------------------------------------------------\n";
			$mailbody .= "Order ID: ".$order_id."\n";
			$mailbody .= "User ID: ".$member_id."\n";
			foreach($errors as $key => $error)
			{
				$mailbody .= ($key+1).". {$error}\n";
			}
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		}
		else
		{
			
			if($orderInfo->order_status == 'pending')
			{
				$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$process->get('item_id')));
			}

		}
			
	}

}

class oseMscIpnVPC {
	
	function __construct($post) {
		// Notify string
		foreach($post as $key=>$value)
		{
			$this->{$key} = JRequest :: getVar($key,null);
		}
	}
	
	
	function get($key, $default= null) {
		if(empty($this-> {
			$key })) {
			$this-> {
				$key }
			= $default;
		}
		return $this-> {
			$key };
	}
}
?>