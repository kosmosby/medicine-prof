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
			echo "<h2>PayPal Notify.php Debug OUTPUT</h2>";
			define("_DEBUG_HEADER", "1");
		}
		$messages[]= "<pre>$msg</pre>";
		echo end($messages);
	}
}
function translateUnit($t) {
	switch($t) {
		case('day') :
			$t= 'D';
			break;
		case('week') :
			$t= 'W';
			break;
		case('month') :
			$t= 'M';
			break;
		case('year') :
			$t= 'Y';
			break;
	}
	return $t;
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
	
	
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	
	//$test_mode= $oseMscConfig->epay_testmode;
	
	$data = array( 
	 'transaction' => $_POST['transaction'], 
	 'user_id' => $_POST['user_id'], 
	 'project_id' => $_POST['project_id'], 
	 'sender_holder' => $_POST['sender_holder'], 
	 'sender_account_number' => $_POST['sender_account_number'], 
	 'sender_bank_code' => $_POST['sender_bank_code'], 
	 'sender_bank_name' => $_POST['sender_bank_name'], 
	 'sender_bank_bic' => $_POST['sender_bank_bic'], 
	 'sender_iban' => $_POST['sender_iban'], 
	 'sender_country_id' => $_POST['sender_country_id'], 
	 'recipient_holder' => $_POST['recipient_holder'], 
	 'recipient_account_number' => $_POST['recipient_account_number'], 
	 'recipient_bank_code' => $_POST['recipient_bank_code'], 
	 'recipient_bank_name' => $_POST['recipient_bank_name'], 
	 'recipient_bank_bic' => $_POST['recipient_bank_bic'], 
	 'recipient_iban' => $_POST['recipient_iban'], 
	 'recipient_country_id' => $_POST['recipient_country_id'], 
	 'international_transaction' => $_POST['international_transaction'], 
	 'amount' => $_POST['amount'], 
	 'currency_id' => $_POST['currency_id'], 
	 'reason_1' => $_POST['reason_1'], 
	 'reason_2' => $_POST['reason_2'], 
	 'security_criteria' => $_POST['security_criteria'], 
	 'user_variable_0' => $_POST['user_variable_0'],
	 'user_variable_1' => $_POST['user_variable_1'], 
	 'user_variable_2' => $_POST['user_variable_2'], 
	 'user_variable_3' => $_POST['user_variable_3'], 
	 'user_variable_4' => $_POST['user_variable_4'], 
	 'user_variable_5' => $_POST['user_variable_5'], 
	 'created' => $_POST['created'], 
	 'project_password' => $oseMscConfig->pnw_project_password
	); 
	
	$data_implode = implode('|', $data); 
	$hash = sha1($data_implode);

	$dp24_result = "FALSE";
	if($hash == $_POST['hash']) {
		$dp24_result = "TRUE";
	}
	
	if(!$dp24_result) 
	{
		$app->redirect(JRoute::_('index.php?option=com_osemsc&view=member',JText::_('Your Payment is failed!')));
	}
	else
	{
		
		$db= oseDB :: instance();
		$process = new oseMscIpnPNW($data);
		// $invoice = $db->quote( $db->getEscaped($invoice));
		//$invoice = $db->quote($invoice);
		$where= array();
		$where[]= "`order_number` =".$db->Quote($process->get('user_variable_0'));
		
		$payment = oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		
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
			$mailsubject= "Payment Network Transaction on your Site";
			$mailbody = " Hello,An error occured while processing a payment network transaction.\n"
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
			
			if(!empty($process->transaction))
			{
				//$result = $epay->capture($merchantnumber,$tid,$process->get('order_number'),$transaction['authamount'],$transaction['currency']);
				//$result = $subscription['getsubscriptionsResult'];
				//if($result['captureResult'])
				//{
					if($orderInfo->order_status == 'pending')
					{
						$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$process->transaction));
					}
					else
					{
						// if invalid or fraud, inform admin
					}
				/*}
				else
				{
					$errors = array();
					
					if(!empty($result['epayresponse']))
					{
						$err = $epay->getEpayError($merchantnumber,$result['epayresponse']);
						$errors[] = $err['epayResponseString'];
					}
				}*/
			}
			else
			{
				$mailsubject= "Payment Network Transaction on your Site";
				$mailbody = " Hello,An error occured while processing a payment network transaction.\n"
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
		}
			
	}
	/*
	$mailsubject= "PayPal IPN Transaction on your Site";
			$mailbody= "Hello,
					An error occured while processing a paypal transaction.
					----------------------------------\n";
			$mailbody .= "Order ID: ".$order_id."\n";
			$mailbody .= "User ID: ".$member_id."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
	*/	
}

class oseMscIpnPNW {
	
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