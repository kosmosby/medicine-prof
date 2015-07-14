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
$debugMode= 0;
if($debugMode)
{
	// one off
	$str = 'mc_gross=11.00&invoice=52_qNRI52V2VyIg0Lr6dP4SawoYEv0A&settle_amount=5.00&protection_eligibility=Ineligible&payer_id=WRNLL35SBF8B2&tax=0.00&payment_date=00:45:09 May 15, 2011 PDT&payment_status=Completed&charset=windows-1252&first_name=Test&mc_fee=0.73&exchange_rate=0.486854&notify_version=3.1&settle_currency=GBP&custom=&payer_status=verified&business=info_1291826714_biz@opensource-excellence.co.uk&quantity=1&verify_sign=AFcWxV21C7fd0v3bYYYRCpSSRl31AaUPoqtLk7JgXMZ9HK2z8rSEfSWo&payer_email=info_1273774729_per@opensource-excellence.co.uk&txn_id=5XR41955AC390090X&payment_type=instant&last_name=User&receiver_email=info_1291826714_biz@opensource-excellence.co.uk&payment_fee=0.73&receiver_id=KALYQVVKVGKVW&txn_type=web_accept&item_name=Order ID: 59 - Payment for Credit Type:&mc_currency=USD&item_number=&residence_country=US&test_ipn=1&handling_amount=0.00&transaction_subject=Order ID: 59 - Payment for Credit Type:&payment_gross=11.00&shipping=0.00&ipn_track_id=BzSKf.XOKFvTzL-De3T9Wg';
	// recurring
	$str = 'payment_cycle=every 6 Months&txn_type=recurring_payment_profile_created&last_name=User&initial_payment_status=Completed&next_payment_date=02:00:00 Jan 09, 2012 PST&residence_country=US&initial_payment_amount=49.98&currency_code=USD&time_created=06:59:38 Jul 09, 2011 PDT&verify_sign=An5ns1Kso7MWUdW4ErQKJJJ4qi4-A-F0HQQA1eVRp4LKK3AxE5ohoGNC&period_type= Regular&payer_status=verified&test_ipn=1&tax=0.00&payer_email=info_1273774729_per@opensource-excellence.co.uk&first_name=Test&receiver_email=info_1291826740_biz@opensource-excellence.co.uk&payer_id=WRNLL35SBF8B2&product_type=1&initial_payment_txn_id=6PE667852H583583N&shipping=0.00&amount_per_cycle=99.95&profile_status=Active&charset=windows-1252&notify_version=3.1&amount=99.95&outstanding_balance=0.00&recurring_payment_id=I-N4TX9E7126SU&product_name=Order ID: 7476&ipn_track_id=GnclITRnG-RCwFIUKKpQlA';
	// refund
	// cancel
	parse_str($str, $_POST);
}
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
	if(!class_exists('oseMscPublic'))
	{
		require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."helpers".DS."oseMscPublic.php");
	}

	/*** END OSE part ***/
	/*
	$post_msg = "";
	foreach ($_POST as $ipnkey => $ipnval) {
		$post_msg .= "$ipnkey=$ipnval&amp;";
	}
	*/
	$post_msg= http_build_query($_POST);
	debug_msg("2. Received this POST: $post_msg");
	$post_msg= "";
	/**
	* Read post from PayPal system and create reply
	* starting with: 'cmd=_notify-validate'...
	* then repeating all values sent: that's our VALIDATION.
	**/
	$workstring= 'cmd=_notify-validate'; // Notify validate
	$i= 1;
	foreach($_POST as $ipnkey => $ipnval) {
		if(get_magic_quotes_gpc()) // Fix issue with magic quotes
			{
			$ipnval= stripslashes($ipnval);
		}
		if(!eregi("^[_0-9a-z-]{1,30}$", $ipnkey) || !strcasecmp($ipnkey, 'cmd')) {
			// ^ Antidote to potential variable injection and poisoning
			unset($ipnkey);
			unset($ipnval);
		}
		// Eliminate the above
		// Remove empty keys (not values)
		if(@ $ipnkey != '') {
			//unset ($_POST); // Destroy the original ipn post array, sniff...
			$workstring .= '&'.@ $ipnkey.'='.urlencode(@ $ipnval);
		}
		$post_msg .= "key ".$i++.": $ipnkey, value: $ipnval<br />";
	}
	JRequest :: set($_POST, 'post');
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$paypal_mode = oseObject::getValue($oseMscConfig,'paypal_mode','paypal_express');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$paypal_email= $oseMscConfig->paypal_email;
	$test_mode= $oseMscConfig->paypal_testmode;
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	$process= new oseMscIpnPaypal($paypal_email,$paypal_mode);

	$remote_hostname= gethostbyaddr($_SERVER['REMOTE_ADDR']);
	/*if(oseObject::getValue($oseMscConfig,'paypal_ipvalidate',false))
	{
		// Get the list of IP addresses for www.paypal.com and notify.paypal.com
		$paypal_iplist= gethostbynamel('www.paypal.com');
		$paypal_iplist2= gethostbynamel('notify.paypal.com');
		$paypal_iplist= array_merge($paypal_iplist, $paypal_iplist2);
		$paypal_sandbox_hostname= 'ipn.sandbox.paypal.com';
		$remote_hostname= gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$valid_ip= false;
		if($paypal_sandbox_hostname == $remote_hostname) {
			if($test_mode == true) {
				$valid_ip= true;
				$hostname= 'www.sandbox.paypal.com';
			} else {
				$valid_ip= false;
				$hostname= 'www.paypal.com';
			}
		} else {
			$ips= "";
			// Loop through all allowed IPs and test if the remote IP connected here
			// is a valid IP address
			foreach($paypal_iplist as $ip) {
				$ips .= "$ip,\n";
				$parts= explode(".", $ip);
				$first_three= $parts[0].".".$parts[1].".".$parts[2];
				if(preg_match("/^$first_three/", $_SERVER['REMOTE_ADDR'])) {
					$valid_ip= true;
				}
			}
			$hostname= 'www.paypal.com';
		}
		if(!$valid_ip) {
			debug_msg("Error code 506. Possible fraud. Error with REMOTE IP ADDRESS = ".$_SERVER['REMOTE_ADDR'].".
				The remote address of the script posting to this notify script does not match a valid PayPal ip address\n");
			$mailsubject= "PayPal IPN Transaction on your site: Possible fraud";
			$mailbody= "Error code 506. Possible fraud. Error with REMOTE IP ADDRESS = ".$_SERVER['REMOTE_ADDR'].".
				The remote address of the script posting to this notify script does not match a valid PayPal ip address\n
				These are the valid IP Addresses: $ips

				The Order ID received was: $invoice";
			//mail($mailfrom, $mailsubject, $mailbody);
			//exit();
		}
	}
	else
	{
		if($test_mode == true) {
			$hostname= 'www.sandbox.paypal.com';
		} else {
			$hostname= 'www.paypal.com';
		}
	}*/
	if($test_mode == true) {
		$hostname= 'www.sandbox.paypal.com';
	} else {
		$hostname= 'www.paypal.com';
	}
	/**--------------------------------------------
	* Create message to post back to PayPal...
	* Open a socket to the PayPal server...
	*--------------------------------------------*/
	$uri= "/cgi-bin/webscr";
	$header= "POST $uri HTTP/1.0\r\n";
	$header .= "User-Agent: PHP/".phpversion()."\r\n";
	$header .= "Referer: ".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].@ $_SERVER['QUERY_STRING']."\r\n";
	$header .= "Server: ".$_SERVER['SERVER_SOFTWARE']."\r\n";
	$header .= "Host: ".$hostname.":443\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: ".strlen($workstring)."\r\n";
	$header .= "Accept: */*\r\n\r\n";
	$fp= fsockopen("ssl://".$hostname, 443, $errno, $errstr, 30);
	debug_msg("3. Connecting to: $hostname"."$uri
		Using these http Headers:

		$header

		and this String:

		$workstring");
	//----------------------------------------------------------------------
	// Check HTTP connection made to PayPal OK, If not, print an error msg
	//----------------------------------------------------------------------
	if(!$fp) {
		$error_description= "$errstr ($errno)
			Status: FAILED";
		debug_msg("4. Connection failed: $error_description");
		$res= "FAILED";
		$mailsubject= "PayPal Pro IPN Fatal Error on your Site";
		$mailbody= "Hello,
			A fatal error occured while processing a paypal transaction.
			----------------------------------
			Hostname: $hostname
			URI: $uri
			$error_description";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
	}
	//--------------------------------------------------------
	// If connected OK, write the posted values back, then...
	//--------------------------------------------------------
	else {
		fwrite($fp, $header.$workstring);
		$res= '';
		while(!feof($fp)) {
			$res .= fgets($fp, 1024);
		}
		fclose($fp);
		$error_description= "Response from $hostname: ".$res."\n";
		// Get the Order Details from the database
		$db= oseDB :: instance();
		// $invoice = $db->quote( $db->getEscaped($invoice));
		//$invoice = $db->quote($invoice);
		$where= array();
		if($process->get('paypal_mode') == 'paypal_express')
		{
			$invoice = $process->get('invoice');
			if (empty($invoice))
			{
				return;
			}
			$where[]= "`order_number`=".$db->quote($invoice).' OR `payment_serial_number`='.$db->quote($invoice);
		}
		else
		{
			$invoice = $process->get('invoice');
			$recurring_payment_id = $process->get('recurring_payment_id');

			if (!empty($recurring_payment_id) && !empty($invoice))
			{
			   $where[]= "`order_number`=".$db->quote($invoice).' OR `payment_serial_number`='.$db->quote($recurring_payment_id);
			}
			elseif (empty($invoice) && !empty($recurring_payment_id))
			{
			   $where[]= '`payment_serial_number`='.$db->quote($recurring_payment_id);
			}
			elseif (empty($recurring_payment_id) && !empty($invoice))
			{
				$where[]= "`order_number`=".$db->quote($invoice);
			}
			else
			{
				exit;;
			}
		}

		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getInstance('Order')->getOrder($where, 'obj');
		if(empty($orderInfo))
		{
			$mailsubject= "PayPal Pro IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The PayPal IPN
					----------------------------------\n
					Hostname: $hostname\n
					URI: $uri\n
					Invoice: ".$process->get('invoice')."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			$res= 'Other Error!';
		}
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$query= " SELECT * FROM `#__osemsc_order_item`".		" WHERE order_id = '{$order_id}'";
		$db->setQuery($query);
		$orderItems= oseDB :: loadList('obj');
		// $msc_id = $orderInfo->entry_id;
		// remove post headers if present.
		//OSE pending
		$res= preg_replace("'Content-type: text/plain'si", "", $res);
		//-------------------------------------------
		// ...read the results of the verification...
		// If VERIFIED = continue to process the TX...
		//-------------------------------------------
		if(eregi("VERIFIED", $res)) {
			// check the invoice
			$isValid= false;
			if(!$process->checkInvoice()) {
				$payment_status= "Failed";
				$mailsubject= "PayPal Pro IPN Transaction on your site: Order ID not found";
				$mailbody= "The right order_id wasn't found during a PayPal transaction on your website.
							The Order ID received was: ".$process->get('invoice');
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			}
			$paymentOrder= $payment->getInstance('Order');
			// check the price detail : m & a
			switch($process->getStatus()) {
				case('recurring_payment_profile_created'):
					// check whethere there is a init txn
					$result = $process->authorizeProfileCreation($orderInfo);
					if(!$result)
					{
						$mailsubject= "Invalid Paypal Pro IPN Transaction on your site - Invalid Transaction";
						$mailbody= "Hello,<br /><br />";
						$mailbody .= "An Invalid Paypal Transaction requires your attention.<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "REMOTE IP ADDRESS:".$_SERVER['REMOTE_ADDR']."<br />";
						$mailbody .= "REMOTE HOST NAME: {$remote_hostname}<br />";
						$mailbody .= "Order ID: {$order_id}<br />";
						$mailbody .= "User ID: {$member_id}<br />";

						$mailbody .= "Paypal Unique Transaction( Subscription ) ID: ".$process->get('txn_id')."<br />";
						$mailbody .= "Description: fail,recurring_payment_profile_created<br />";

						$emailObj= new stdClass();

						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

					}
					else
					{
						$ini_txn_id = $process->get('init_txn_id');
						if(!empty($ini_txn_id))
						{
							$transactions = oseJson::decode(oseObject::getValue($orderInfo,'transactions','[]'));
							$transactions[] = $process->get('init_txn_id');
							$transactions = oseJson::encode($transactions);
							oseDB::update('#__osemsc_order','order_id',array(
								'order_id'=>$order_id
								,'order_status'=>'confirmed','transactions'=>$transactions
							));
							//$paymentOrder->updateOrder($order_id, "confirmed",array('transactions'=>$transactions));
							//$oseMscConfig = oseMscConfig::getConfig('payment','obj');
							if(oseObject::getValue($oseMscConfig,'paypal_pro_access','instant') == 'instant')
							{
								// confirm
								//self::confirmOrder($order_id, $params);
							}
							else
							{
								// confirm until paid
								$paymentOrder->confirmOrder($order_id);
							}
							
							$process->set('txn_id',$process->get('recurring_payment_id'));
							$eVars['payment_status'] = 'Profile created and the first transaction is charge';
						}
						else
						{
							$paymentOrder->updateOrder($order_id, "confirmed");
							$process->set('txn_id',$process->get('recurring_payment_id'));
							$eVars['payment_status'] = 'Profile created';
						}
/*
				$query = " UPDATE `#__users` SET `block` = 0 where `id` = ". $member_id;
				$db->setQuery($query);
				$db->query();

*/
						$user = JFactory::getUser($member_id);
						$mailsubject= "Paypal Pro IPN txn on your site - Transaction Succeed";
						$mailbody= "Dear Administrator,<br /><br />";
						$mailbody .= "a Paypal transaction for you has been made on your website!<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "Paypal Unique Transaction ID: ".$process->get('txn_id')."<br />";
						$mailbody .= "Payer Email: ".$process->get('payer_email')."<br />";
						$mailbody .= "Order ID: {$order_id}<br />";
						$mailbody .= "Payment Status returned by Paypal: completed<br />";
						$mailbody .= "Order Status Code: ".$process->get('txn_type');

						$emailObj= new stdClass();

						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					}
				break;

				case('recurring_payment'):
					$result = true;
					$mailsubject= "Paypal Pro IPN txn on your site - Transaction Succeed";
					$mailbody= "Dear Administrator,<br /><br />";
					$mailbody .= "a Paypal transaction for you has been made on your website!<br />";
					$mailbody .= "-----------------------------------------------------------<br />";
					$mailbody .= "Paypal Unique Transaction ID: ".$process->get('txn_id')."<br />";
					$mailbody .= "Payer Email: ".$process->get('payer_email')."<br />";
					$mailbody .= "Order ID: {$order_id}<br />";
					$mailbody .= "Payment Status returned by Paypal: Completed<br />";
					$mailbody .= "Order Status Code: ".$process->get('txn_type');
					$emailObj= new stdClass();
					$emailObj->subject= $mailsubject;
					$emailObj->body= $mailbody;
					$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					$transactions = oseJson::decode(oseObject::getValue($orderInfo,'transactions','[]'));
					$transactions[] = $process->get('init_txn_id');
					$transactions = oseJson::encode($transactions);
					$paymentOrder->updateOrder($order_id, "confirmed",array('transactions'=>$transactions));
					$paymentOrder->confirmOrder($order_id);
				break;

				case('recurring_payment_skipped'):
					//$list= $paymentOrder->refundOrder($order_id);
					$paymentOrder->updateOrder($order_id,'skipped');
					$mailsubject= "PayPal Pro IPN txn on your site - Order ID: {$order_id} Skipped";
					$mailbody = "Dear Administrator, <br/><br/>";
					$mailbody .= "A subscription payment transaction was skipped on your website!<br/><br/>";
					$mailbody .= "Please check this transaction on Paypal and contact the subscriber.<br/><br/>";
					$mailbody .= "-----------------------------------------------------------<br/><br/>";
					$mailbody .= "Paypal Subscriber ID: ". $process->get('recurring_payment_id')."<br />";
					$mailbody .= "Member ID: ".$member_id."<br /><br />";
					$mailbody .= "Order ID: {$order_id}<br/><br/>";
					$mailbody .= "Payment Status returned by PayPal: ".$process->getStatus()."<br/><br/>";

					$emailObj= new stdClass();
					$emailObj->subject= $mailsubject;
					$emailObj->body= $mailbody;
					$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				break;
				case('recurring_payment_failed'):
					$list= $paymentOrder->refundOrder($order_id);
					$paymentOrder->updateOrder($order_id,'failed');
					$mailsubject= "PayPal Pro IPN txn on your site";
					$mailbody = "Dear Administrator, <br/><br/>";
					$mailbody .= "A subscription payment transaction was failed on your website!<br/><br/>";
					$mailbody .= "Please check this transaction on Paypal and contact the subscriber, then take actions to update the user's membership manually.<br/><br/>";
					$mailbody .= "-----------------------------------------------------------<br/><br/>";
					$mailbody .= "Paypal Subscriber ID: ". $process->get('recurring_payment_id')."<br />";
					$mailbody .= "Member ID: ".$member_id."<br /><br />";
					$mailbody .= "Order ID: {$order_id}<br/><br/>";
					$mailbody .= "Payment Status returned by PayPal: ".$process->getStatus()."<br/><br/>";

					$emailObj= new stdClass();
					$emailObj->subject= $mailsubject;
					$emailObj->body= $mailbody;
					$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

				break;
				
				case('subscr_signup') :
					$payment->getInstance('Order')->updateOrder($orderInfo->order_id,'confirmed',array('payment_serial_number'=>$process->get('subscr_id')));
				break;
				case('completed') :

					if($process->get('paypal_mode') == 'paypal_express')
					{
						if ($process->getStatus()=='subscr_signup' && ($process->amount1!=0.00))
						{
							break;
						}
						$payment= oseRegistry :: call('payment')->getInstance('Order');
						$payment->confirmOrder($order_id, array(), 0, $member_id);
						$payment_status= "Payment Completed";
						$mailsubject= "PayPal IPN txn on your site";
						$mailbody= "Dear Administrator,<br /><br />";
						$mailbody .= "a PayPal transaction for you has been made on your website!<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "Paypal Unique Transaction ID: ". $process->get('txn_id')."<br />";
						$mailbody .= "Payer Email:  ". $process->get('payer_email')."<br />";
						$mailbody .= "Order ID: $order_id<br />";
						$mailbody .= "Payment Status returned by PayPal: $payment_status<br />";
						$mailbody .= "Order Status Code: ".$payment_status;
						$emailObj= new stdClass();
						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					}
					else
					{
						$payment_status= "Payment Completed";
						$mailsubject= "PayPal IPN txn on your site";
						$mailbody= "Dear Administrator,<br /><br />";
						$mailbody .= "a PayPal transaction for you has been made on your website!<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "Paypal Unique Transaction ID: ". $process->get('txn_id')."<br />";
						$mailbody .= "Payer Email:  ". $process->get('payer_email')."<br />";
						$mailbody .= "Order ID: $order_id<br />";
						$mailbody .= "Payment Status returned by PayPal: $payment_status<br />";
						$mailbody .= "Order Status Code: ".$payment_status;
						$emailObj= new stdClass();
						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					}

					break;
				case('refunded') :
					$list= $paymentOrder->refundOrder($order_id);
					if($updated['success']) {
						$mailsubject= "PayPal IPN txn on your site (Refunded)";
						$mailbody= "Dear Administrator,<br /><br />";
						$mailbody .= "a PayPal transaction for you has been refunded on your website!<br />";
						$mailbody .= "the memberships refered to have been cancelled<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "Paypal Unique Transaction ID: ". $process->get('txn_id')."<br />";
						$mailbody .= "Payer Email: ". $process->get('payer_email')."<br />";
						$mailbody .= "Order ID: $order_id<br />";
						$mailbody .= "Payment Status returned by PayPal: Payment Refunded<br />";
						$mailbody .= "Order Status Code: ".$txn_type;
						$emailObj= new stdClass();
						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					} else {
						$mailsubject= "PayPal IPN txn on your site (Refunded)";
						$mailbody= "Dear Administrator,<br /><br />";
						$mailbody .= "a PayPal transaction for you has been refunded on your website!<br />";
						$mailbody .= "the memberships refered to have not been cancelled yet!<br />";
						$mailbody .= "-----------------------------------------------------------<br />";
						$mailbody .= "Paypal Unique Transaction ID: ". $process->get('txn_id')."<br />";
						$mailbody .= "Payer Email: ". $process->get('payer_email')."<br />";
						$mailbody .= "Order ID: $order_id<br />";
						$mailbody .= "Payment Status returned by PayPal: Payment Refunded<br />";
						$mailbody .= "Order Status Code: ".$txn_type;
						$emailObj= new stdClass();
						$emailObj->subject= $mailsubject;
						$emailObj->body= $mailbody;
						$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					}
					break;
				
				case('recurring_payment_profile_cancel'):
					
				case('subscr_cancel') :
				case('subscr_eot') :
				case('recurring_payment_suspended'):
					if($orderInfo->order_status != 'profile_cancelled')
					{
						$paymentOrder->updateOrder($order_id, "profile_cancelled");
						$apiEmail->sendCancelOrderEmail(array('orderInfo'=>$orderInfo));
					}
					
					$mailsubject= "PayPal IPN txn on your site";
					$mailbody = "Dear Administrator, <br/><br/>";
					$mailbody .= "A subscription cancellation transaction for you has been made on your website!<br/><br/>";
					$mailbody .= "-----------------------------------------------------------<br/><br/>";
					$mailbody .= "Paypal Subscriber ID: ". $process->get('subscr_id')."<br />";
					$mailbody .= "Payer Email: ". $process->get('payer_email')."<br />";
					$mailbody .= "Member ID: ".$member_id."<br /><br />";
					$mailbody .= "Order ID: $order_id<br/><br/>";
					$mailbody .= "Payment Status returned by PayPal: Subscription Cancelled<br/><br/>";

					$emailObj= new stdClass();

					$emailObj->subject= $mailsubject;
					$emailObj->body= $mailbody;
					$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					break;
				case('pending') :
					break;

				case('failed') :
				default :
					//----------------------------------------------------------------------
					// If the payment_status is not Completed... do nothing but mail
					//----------------------------------------------------------------------
					// UPDATE THE ORDER STATUS to 'INVALID'
					oseRegistry :: call('payment')->updateOrder($order_id, "invalid");
					//$process->blockUser($member_id);
					$member = oseRegistry :: call('member');
					$member->init($member_id);
					foreach($orderItems as $item)
					{
						$member->cancelMsc($item->entry_id);
					}
					$payment_status= "Invalid";
					$mailsubject= "PayPal IPN Transaction on your site";
					$mailbody= "Hello,
									a Failed PayPal Transaction requires your attention.
									-----------------------------------------------------------
									Order ID: ".$order_id."
									User ID: ".$member_id."
									Payment Status returned by PayPal: $payment_status

									$error_description"
									."\r\n IPN".http_build_query($_POST)
									;
					$emailObj= new stdClass();
					$emailObj->subject= $mailsubject;
					$emailObj->body= $mailbody;
					$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
					break;
			}
			// finish checking
			//----------------------------------------------------------------------
			// If the payment_status is Completed... Get the password for the product
			// from the DB and email it to the customer.
			//----------------------------------------------------------------------
		}
		//----------------------------------------------------------------
		// ..If UNVerified - It's 'Suspicious' and needs investigating!
		// Send an email to yourself so you investigate it.
		//----------------------------------------------------------------
		elseif(eregi("INVALID", $res)) {
			oseRegistry :: call('payment')->updateOrder($order_id, "invalid");
			//$process->blockUser($member_id);
			$member = oseRegistry :: call('member');
			//$member->init($member_id);
			foreach($orderItems as $item)
			{
				//$member->cancelMsc($item->entry_id);
			}
			$mailsubject= "Invalid PayPal IPN Transaction on your site";
			$mailbody= "Hello,<br /><br />";
			$mailbody .= "An Invalid PayPal Transaction requires your attention.<br />";
			$mailbody .= "-----------------------------------------------------------<br />";
			$mailbody .= "REMOTE IP ADDRESS: ".$_SERVER['REMOTE_ADDR']."<br />";
			$mailbody .= "REMOTE HOST NAME: $remote_hostname<br />";
			$mailbody .= "Order ID: ".$order_id."<br />";
			$mailbody .= "User ID: ".$member_id."<br />";
			$mailbody .= "Paypal Unique Transaction ID: ". $process->get('txn_id')."<br />";
			$mailbody .= $error_description;
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		} else {
			$mailsubject= "PayPal IPN Transaction on your Site";
			$mailbody= "Hello,
					An error occured while processing a paypal pro transaction.
					----------------------------------<br />";
			$mailbody .= "Order ID: ".$order_id."<br />";
			$mailbody .= "User ID: ".$member_id."<br />";
			$mailbody .= "Desc: ".http_build_query($_POST)."<br />";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		}
		//fclose($fp);
	}
}
class oseMscIpnPaypal {
	function __construct($paypal_email,$type = 'paypal_express') {
		// Notify string
		$this->paypal_mode = $type;
		if($type == 'paypal_express')
		{
			$this->paypal_email= $paypal_email;
			$this->paypal_receiver_email= $paypal_email;
			$this->business= JRequest :: getVar('business'); //trim(stripslashes($this->_POST['business']));
			$this->item_name= JRequest :: getVar('item_name'); //trim(stripslashes($this->_POST['item_name']));
			$this->item_number= JRequest :: getVar('item_number'); //trim(stripslashes(@ $this->_POST['item_number']));
			$this->payment_status= JRequest :: getVar('payment_status'); //trim(stripslashes($this->_POST['payment_status']));
			// The order total amount including taxes, shipping and discounts
			$this->mc_gross= JRequest :: getVar('mc_gross'); //trim(stripslashes($this->_POST['mc_gross']));
			// Can be USD, GBP, EUR, CAD, JPY
			$this->currency_code= JRequest :: getVar('mc_currency'); //trim(stripslashes($this->_POST['mc_currency']));
			$this->txn_id= JRequest :: getVar('txn_id'); //trim(stripslashes($this->_POST['txn_id']));
			$this->receiver_email= JRequest :: getVar('receiver_email'); //trim(stripslashes($this->_POST['receiver_email']));
			$this->payer_email= JRequest :: getVar('payer_email'); //trim(stripslashes($this->_POST['payer_email']));
			$this->payment_date= JRequest :: getVar('payment_date'); //trim(stripslashes($this->_POST['payment_date']));
			// The Order Number (not order_id !)
			$this->invoice= JRequest :: getVar('invoice',JRequest :: getCmd('parent_txn_id')); //trim(stripslashes($this->_POST['invoice']));
			$this->amount= JRequest :: getVar('amount'); //trim(stripslashes(@ $this->_POST['amount']));
			$this->quantity= JRequest :: getVar('quantity'); //trim(stripslashes($this->_POST['quantity']));
			$this->pending_reason= JRequest :: getVar('pending_reason'); //trim(stripslashes(@ $this->_POST['pending_reason']));
			$this->payment_method= JRequest :: getVar('payment_method'); //trim(stripslashes(@ $this->_POST['payment_method'])); // deprecated
			$this->payment_type= JRequest :: getVar('payment_type'); //trim(stripslashes(@ $this->_POST['payment_type']));
			// Billto
			$this->first_name= JRequest :: getVar('first_name'); //trim(stripslashes($this->_POST['first_name']));
			$this->last_name= JRequest :: getVar('last_name'); //trim(stripslashes($this->_POST['last_name']));
			$this->address_street= JRequest :: getVar('address_street'); //trim(stripslashes(@ $this->_POST['address_street']));
			$this->address_city= JRequest :: getVar('address_city'); //trim(stripslashes(@ $this->_POST['address_city']));
			$this->address_state= JRequest :: getVar('address_state'); //trim(stripslashes(@ $this->_POST['address_state']));
			$this->address_zipcode= JRequest :: getVar('address_zip'); //trim(stripslashes(@ $this->_POST['address_zip']));
			$this->address_country= JRequest :: getVar('address_country'); //trim(stripslashes(@ $this->_POST['address_country']));
			$this->residence_country= JRequest :: getVar('residence_country'); //trim(stripslashes(@ $this->_POST['residence_country']));
			$this->address_status= JRequest :: getVar('address_status'); //trim(stripslashes(@ $this->_POST['address_status']));
			$this->payer_status= JRequest :: getVar('payer_status'); //trim(stripslashes($this->_POST['payer_status']));
			$this->notify_version= JRequest :: getVar('notify_version'); //trim(stripslashes($this->_POST['notify_version']));
			$this->verify_sign= JRequest :: getVar('verify_sign'); //trim(stripslashes($this->_POST['verify_sign']));
			$this->custom= JRequest :: getVar('custom'); //trim(stripslashes(@ $this->_POST['custom']));
			$this->txn_type= JRequest :: getVar('txn_type'); //trim(stripslashes($this->_POST['txn_type']));
			$this->subscr_id= JRequest :: getVar('subscr_id');
		}
		else
		{
			$this->first_name= JRequest :: getCmd('first_name'); //trim(stripslashes($this->_POST['first_name']));
			$this->last_name= JRequest :: getCmd('last_name'); //trim(stripslashes($this->_POST['last_name']));
			$this->address_street= JRequest :: getString('address_street'); //trim(stripslashes(@ $this->_POST['address_street']));
			$this->address_city= JRequest :: getString('address_city'); //trim(stripslashes(@ $this->_POST['address_city']));
			$this->address_state= JRequest :: getString('address_state'); //trim(stripslashes(@ $this->_POST['address_state']));
			$this->address_zipcode= JRequest :: getCmd('address_zip'); //trim(stripslashes(@ $this->_POST['address_zip']));
			$this->address_country= JRequest :: getString('address_country'); //trim(stripslashes(@ $this->_POST['address_country']));
			$this->residence_country= JRequest :: getString('residence_country'); //trim(stripslashes(@ $this->_POST['residence_country']));
			$this->address_status= JRequest :: getCmd('address_status'); //trim(stripslashes(@ $this->_POST['address_status']));
			 //trim(stripslashes($this->_POST['payer_status']));
			$this->custom= JRequest :: getCmd('custom');

			// common
			$this->business = JRequest :: getString('business');
			$this->receiver_email = JRequest :: getString('receiver_email');
			$this->payer_email = JRequest :: getString('payer_email');
			$this->test_ipn = JRequest :: getString('test_ipn');
			$this->payer_status= JRequest :: getCmd('payer_status');

			$this->payment_date= JRequest :: getString('payment_date');
			$this->notify_version= JRequest :: getCmd('notify_version'); //trim(stripslashes($this->_POST['notify_version']));
			$this->verify_sign= JRequest :: getCmd('verify_sign');

			// common variables between profile created & recurring payment
			$this->init_txn_id = JRequest :: getCmd('initial_payment_txn_id');
			$this->init_txn_status = JRequest :: getCmd('initial_payment_status');
			$this->init_payment_amount = JRequest :: getCmd('initial_payment_amount');
			$this->profile_status = JRequest :: getCmd('profile_status');

			$this->currency_code = JRequest :: getCmd('currency_code'); // Can be USD, GBP, EUR, CAD, JPY
			$this->recurring_payment_id = JRequest :: getCmd('recurring_payment_id');
			$this->amount= JRequest :: getFloat('amount');
			$this->amount_per_cycle= JRequest :: getFloat('amount_per_cycle');
			$this->payment_cycle = JRequest :: getFloat('payment_cycle');
			$this->txn_type= JRequest :: getCmd('txn_type');

			// recurring payment
			$this->mc_gross = JRequest :: getFloat('mc_gross');
			$this->mc_fee = JRequest :: getFloat('mc_fee');
			$this->payment_fee = JRequest :: getFloat('payment_fee');
			$this->payment_gross = JRequest :: getFloat('payment_gross');
			$this->txn_id = JRequest :: getCmd('txn_id');
			$this->payment_status = JRequest :: getCmd('payment_status');

			$this->mc_currency = JRequest :: getCmd('mc_currency'); // Can be USD, GBP, EUR, CAD, JPY

			// transaction refunded
			$this->parent_txn_id = JRequest :: getCmd('parent_txn_id');
			$this->reason_code = JRequest :: getString('reason_code');
			$this->invoice= JRequest :: getVar('invoice',JRequest :: getCmd('parent_txn_id'));
			$this->rp_invoice_id = JRequest :: getVar('rp_invoice_id',null);
			if (!empty($this->rp_invoice_id) && empty($this->invoice))
			{
				$this->invoice = $this->rp_invoice_id;
			}
		}


	}
	function getStatus()
	{
		if(eregi("recurring_payment_profile_created", $this->txn_type))
		{
			return 'recurring_payment_profile_created';
		}

		if(eregi("recurring_payment_profile_cancel", $this->txn_type))
		{
			$this->set('txn_id',$this->get('recurring_payment_id'));
			return 'recurring_payment_profile_cancel';
		}
		if(eregi("recurring_payment_skipped", $this->txn_type))
		{
			$this->set('txn_id',$this->get('recurring_payment_id'));
			return 'recurring_payment_skipped';
		}
		if(eregi("recurring_payment_failed", $this->txn_type))
		{
			$this->set('txn_id',$this->get('recurring_payment_id'));
			return 'recurring_payment_failed';
		}
		if (eregi("recurring_payment_suspended", $this->txn_type))
		{
			$this->set('txn_id',$this->get('recurring_payment_id'));
			return 'recurring_payment_suspended';
		}
		if(eregi("recurring_payment", $this->txn_type))
		{
			return 'recurring_payment';
		}
		if(eregi("Refunded", $this->payment_status))
		{
			$this->set('txn_type',$this->get('reason_code'));
			return 'refunded';
		}
		if(eregi("Completed", $this->payment_status))
		{
			return 'completed';
		}

		if(eregi("subscr_signup", $this->txn_type)) {
			return 'subscr_signup';
		}

		if(eregi("subscr_cancel", $this->txn_type)) {
			return 'subscr_cancel';
		}
		if(eregi("subscr_eot", $this->txn_type)) {
			return 'subscr_eot';
		}
		if(eregi("Pending", $this->payment_status)) {
			return 'pending';
		}
		if(eregi("Denied", $this->payment_status)) {
			return 'denied';
		}
		if(eregi("Expired", $this->payment_status)) {
			return 'expired';
		}
		if(eregi("Refunded", $this->payment_status)) {
			return 'refunded';
		}
		if(eregi("Reversed", $this->payment_status)) {
			return 'reversed';
		}
		if(eregi("Failed", $this->payment_status)) {
			return 'failed';
		}
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
		}
		if($this->currency_code != $orderInfo->payment_currency) {
			return false;
		}
		$amount= $orderInfo->payment_price;
		$backA1= (float) $this->get('mc_amount1', 0);
		$backPT1= strtolower($this->get('period1', ''));
		$backA3= (float) $this->get('mc_amount3', 0);
		$backPT3= strtolower($this->get('period3', ''));
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
		$mailbody= "Dear Administrator,<br /><br />";
		$mailbody .= "a PayPal transaction for you has been made on your website!<br /><br />";
		$mailbody .= "-----------------------------------------------------------<br /><br />";
		$mailbody .= "Paypal Unique Transaction ID: $this->txn_id<br /><br />";
		$mailbody .= "Payer Email: $this->payer_email<br /><br />";
		$mailbody .= "Order ID: $order_id<br /><br />";
		$mailbody .= "<br /><br />";
		$mailbody .= "Your setting: {$a1}&{$p1}&{$t1}&{$a3}&{$p3}&{$t3}&{$pt1}&{$pt3}<br /><br />";
		$mailbody .= "Paypal Info: {$backA1}&{$p1}&{$t1}&{$backA3}&{$p3}&{$t3}&{$backPT1}&{$backPT3}<br /><br />";
		//$mailbody .= "Order Status Code: " . $txn_type;
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
		$apiEmail= oseRegistry :: call('member')->getInstance('email');
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
		}
		if($this->currency_code != $orderInfo->payment_currency) {
			return false;
		}

		if(oseObject :: getValue($orderInfoParams, 'recurrence_times') > 0)
		{
			if($this->mc_gross != $orderInfoParams->next_total)
			{
				return false;
			}
		}
		else
		{
			// first time
			if($this->mc_gross != $orderInfoParams->total)
			{
				return false;
			}
		}
		/*
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
		}*/
		return true;
	}
	function checkInvoice() {
		if($this->paypal_mode == 'paypal_express')
		{
			if(empty($this->invoice)) {
				return false;
			} else {
				return true;
			}
		}
		else
		{
			return true;
		}

	}

	function authorizeAuto($orderInfo)
	{
		// get all ingredient
		$orderInfoParams = oseJson::decode($orderInfo->params);
		if($orderInfo->order_status == 'fraud')
		{
			return false;
		}

		if($this->payment_status != 'Completed')
		{
			return false;
		}

		/*
		if($this->get('mc_currency') != $orderInfo->get('payment_currency'))
		{
			return false;
		}

		if( $this->mc_gross != $orderInfoParams->next_total)
		{
			return false;
		}
		*/
		return true;
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
		if(empty($this->{$key})) {
			$this->{$key}= $default;
		}
		return $this-> {$key};
	}
	
	function toString()
	{
		$vars1 = get_class_vars( get_class($this) );
		$vars = array();
		foreach($vars as $key => $value)
		{
			$vars[$key] = $key.'='.$this->get($key);
		}
		
		return implode('&',$vars);
	}

	function set($key, $value= null)
	{
		$this->{$key}= $value;
	}

	function authorizeProfileCreation($orderInfo)
	{
		$ini_txn_id = $this->get('init_txn_id');
		if(!empty($ini_txn_id))
		{
			if($this->get('currency_code') != $orderInfo->payment_currency)
			{
				return false;
			}

			if($this->get('init_txn_status') != 'Completed')
			{
				return false;
			}

			if($this->get('profile_status') != 'Active')
			{
				return false;
			}

			return true;
			/*
			// check init payment
			if($this->get('init_payment_amount') > 0 && $this->get('init_txn_status') == 'Completed')
			{
				if($orderInfo->params->total != $this->get('init_payment_amount'))
				{
					return false;
				}
			}

			// check standard payment
			if($orderInfo->params->next_total != $this->get('amount_per_cycle'))
			{
				return false;
			}

			return true;
			*/
		}
		else
		{
			return true;
		}
	}
}
?>