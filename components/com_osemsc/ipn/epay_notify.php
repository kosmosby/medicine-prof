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

if($_GET) 
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
	$md5 = $oseMscConfig->epay_md5;
	//$test_mode= $oseMscConfig->epay_testmode;
	$root = dirname(dirname(dirname(JURI::root())));
	
	$process = new oseMscIpnEPay();
	$subscriptionid = $process->get('subscriptionid');
	$tid = $process->get('tid');
	if(!$tid)
	{
		$app->redirect($root.'/index.php?option=com_osemsc&view=member',JText::_('Your order is declined!'));
	}
	else
	{
		if(!empty($md5))
		{
			if(!$process->authorizeEKey($md5))
			{
				$app->redirect($root.'/index.php?option=com_osemsc&view=member',JText::_('Your order is declined!'));
			}
		}
		
		$merchantnumber = $oseMscConfig->epay_merchantnumber;
		
		$epay = $process->getAPI();
		
		$db= oseDB :: instance();
		// $invoice = $db->quote( $db->getEscaped($invoice));
		//$invoice = $db->quote($invoice);
		$where= array();
		$where[]= "`order_number` Like".$db->Quote($process->get('order_number').'%');
		
		$payment = oseRegistry :: call('payment');
		$pOrder = $payment->getInstance('Order');
		$orderInfo = $pOrder->getOrder($where, 'obj');
		
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
			// only used by at the first time
			$subscription = $epay->getsubscriptions($merchantnumber, $subscriptionid);
			
			//$result = $epay->getEpayError($merchantnumber,2);
			
			// get the subscription by id to check 
			if($subscription['getsubscriptionsResult'])
			{
				$transactionid = $process->get('tid');
				$result = $epay->gettransaction($merchantnumber, $transactionid);
				//$result = $subscription['getsubscriptionsResult'];
				if($result['gettransactionResult'] == true)
				{
					if($result['transactionInformation']['status'] == 'PAYMENT_CAPTURED')
					{
						if($orderInfo->order_status == 'pending')
						{
							$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$subscriptionid));
						}
						else
						{
							// if invalid or fraud, inform admin
						}
					}
				}/*
				else
				{
					$errors = array();
					if(!empty($result['Pbsresponse']))
					{
						$err = $epay->getPbsError($merchantnumber,$result['Pbsresponse']);
						$errors[] = $err['pbsResponseString'];
					}
					
					if(!empty($result['epayresponse']))
					{
						$err = $epay->getEpayError($merchantnumber,$result['epayresponse']);
						$errors[] = $err['epayResponseString'];
					}
					
					
				}*/
			}
			else
			{
				$mailsubject= "Epay Transaction on your Site";
				$mailbody = " Hello,An error occured while processing a epay transaction.\n"
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
		else
		{
			$result = $epay->gettransaction($merchantnumber, $tid);
			//$result = $epay->getEpayError($merchantnumber,2);
			
			// get the subscription by id to check 
			if($result['gettransactionResult'] == true)
			{
				if($result['transactionInformation']['status'] == 'PAYMENT_CAPTURED')
				{
					if($orderInfo->order_status == 'pending')
					{
						$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$tid));
					}
					else
					{
						// if invalid or fraud, inform admin
					}
				}
			}
			/*if($transaction['status'] == 2)
			{
				//$result = $epay->capture($merchantnumber,$tid,$process->get('order_number'),$transaction['authamount'],$transaction['currency']);
				//$result = $subscription['getsubscriptionsResult'];
				//if($result['captureResult'])
				//{
					if($orderInfo->order_status == 'pending')
					{
						$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$tid));
					}
					else
					{
						// if invalid or fraud, inform admin
					}
				}
				else
				{
					$errors = array();
					
					if(!empty($result['epayresponse']))
					{
						$err = $epay->getEpayError($merchantnumber,$result['epayresponse']);
						$errors[] = $err['epayResponseString'];
					}
				}
			}*/
			/*else
			{
				$mailsubject= "Epay Transaction on your Site";
				$mailbody = " Hello,An error occured while processing a epay transaction.\n"
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
			}*/
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

class oseMscIpnEPay {
	function __construct() {
		// Notify string
		$this->subscriptionid = JRequest :: getVar('subscriptionid',false); //trim(stripslashes($this->_POST['business']));
		//$this->subscriptionid = JRequest :: getVar('transactionid',false);
		$this->tid = JRequest :: getVar('tid'); //trim(stripslashes($this->_POST['item_name']));
		$this->order_number = JRequest :: getVar('orderid'); //trim(stripslashes(@ $this->_POST['item_number']));
		$this->amount = JRequest :: getVar('amount'); //trim(stripslashes($this->_POST['payment_status']));
		// The order total amount including taxes, shipping and discounts
		$this->transfee = JRequest :: getVar('transfee'); //trim(stripslashes($this->_POST['mc_gross']));
		$this->cur = JRequest :: getVar('cur');
		$this->date = JRequest :: getVar('date');
		$this->time = JRequest :: getVar('time');
		$this->seskey = JRequest :: getVar('seskey');
		$this->eKey = JRequest :: getVar('eKey');
	}
	
	function authorizeEKey($md5)
	{
		$md5Key = md5( $this->amount . $this->order_number . $this->tid . $md5);
		if( $this->eKey == $md5Key )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getAPI() {
		require_once(OSEMSC_B_PATH.DS.'libraries'.DS.'epaysoap.php');
		$epaySoap = new EpaySoap();
		
		return $epaySoap;
	}
	
	function checkManual($orderInfo) {
		// get all ingredient
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		if($this->currency_code != $orderInfo->payment_currency) {
			return false;
		}
		if($this->mc_gross != $orderInfoParams->total) {
			return false;
		}
		return true;
	}
	function checkSubscription($orderInfo) {
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		if(oseObject :: getValue($orderInfo, 'order_status') == 'fraud') {
			return false;
			;
		}
		if($this->currency_code != $orderInfo->payment_currency) {
			return false;
		}
		$amount= $orderInfo->payment_price;
		$backA1= (float) $this->get('mc_amount1', 0);
		$backPT1= strtolower($this->get('period1', '0 D'));
		$backA3= (float) $this->get('mc_amount3', 0);
		$backPT3= strtolower($this->get('period3', '0 D'));
		// a, check whether all the params in recurring is the same with the record
		$a1= null;
		$p1= null;
		$t1= null;
		if($orderInfoParams->has_trial) {
			$a1= $orderInfoParams->total;
			$p1= $orderInfoParams->p1;
			$t1= translateUnit($orderInfoParams->t1);
			$pt1= strtolower($p1.' '.$t1);
		}
		$a3= $orderInfoParams->next_total;
		$p3= $orderInfoParams->p3;
		$t3= translateUnit($orderInfoParams->t3);
		$pt3= strtolower($p3.' '.$t3);
		///
		$mailsubject= "PayPal IPN txn on your site";
		$mailbody= "Hello,\n\n";
		$mailbody .= "a PayPal transaction for you has been made on your website!\n";
		$mailbody .= "-----------------------------------------------------------\n";
		$mailbody .= "Transaction ID: $txn_id\n";
		$mailbody .= "Payer Email: $payer_email\n";
		$mailbody .= "Order ID: $order_id\n";
		$mailbody .= "\n";
		$mailbody .= "{$a1}&{$p1}&{$t1}&{$a3}&{$p3}&{$t3}&{$pt1}&{$pt3}";
		$mailbody .= "{$backA1}&{$p1}&{$t1}&{$backPT3}&{$p3}&{$t3}&{$backPT1}&{$backPT3}";
		//$mailbody .= "Order Status Code: " . $txn_type;
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		///
		if($orderInfoParams->has_trial) {
			if($a1 == $backA1 && $pt1 == $backPT1) {
				//
			} else {
				return false;
			}
		}
		if($a3 == $backA3 && $pt3 == $backPT3) {
			//
		} else {
			return false;
		}
		return true;
	}
	function checkAuto($orderInfo) {
		// get all ingredient
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		if(oseObject :: getValue($orderInfo, 'order_status') == 'fraud') {
			return false;
			;
		}
		if($this->currency_code != $orderInfo->payment_currency) {
			return false;
		}
		if(oseObject :: getValue($orderInfoParams, 'has_trial')) {
			if(oseObject :: getValue($orderInfoParams, 'recurrence_times') > 0) {
				if($this->mc_gross != $orderInfoParams->next_total) {
					return false;
				}
			} else {
				if($this->mc_gross != $orderInfoParams->total) {
					return false;
				}
			}
		} else {
			if(oseObject :: getValue($orderInfoParams, 'recurrence_times') > 1) {
				if($this->mc_gross != $orderInfoParams->next_total) {
					return false;
				}
			} else {
				if($this->mc_gross != $orderInfoParams->total) {
					return false;
				}
			}
		}
		return true;
	}
	function checkInvoice() {
		if(empty($this->invoice)) {
			return false;
		} else {
			return true;
		}
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
	function get($key, $default= null) {
		if(empty($this->{$key})) 
		{
			$this-> {$key} = $default;
		}
		return $this-> {$key};
	}
}
?>