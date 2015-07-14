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
function debug_msg($msg) 
{
	global $messages;
	if(PAYPAL_DEBUG == "1") 
	{
		if(!defined("_DEBUG_HEADER")) 
		{
			echo "<h2>PayPal Notify.php Debug OUTPUT</h2>";
			define("_DEBUG_HEADER", "1");
		}
		$messages[]= "<pre>$msg</pre>";
		echo end($messages);
	}
}

function translateUnit($t) 
{
	switch($t) 
	{
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
	
	//shiv start
include("../../../ebs/Rc43.php");
$RC = & new Crypt_RC4($oseMscConfig->ebs_secretKey);

 $GET_DR = str_replace('+','%20',$_GET['DR']);

$DR = preg_replace("/\s/","+",$GET_DR);
$DR= base64_decode($DR);

$RC->decrypt($DR);

$rows = explode('&',$DR);
//echo "<pre>"; print_R($rows); exit;
$DR=array();
foreach($rows as $row)
{	list($key,$val)= explode('=',$row);
	$DR[$key]=$val;	
}

//echo "<pre>GET: ";print_r($_GET);print_r($rows);echo "</pre>";

//shiv end

	
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
//	$cfg_test_mode= $oseMscConfig->cc_testmode;
	$root = dirname(dirname(dirname(JURI::root())));
	

	$DR_subscriptionid = $DR[PaymentID];
	$DR_tid = $DR[TransactionID];
	
	if(!$DR_subscriptionid && !$DR_tid)
	{
		$app->redirect($root.'/index.php?option=com_osemsc&view=member',JText::_('Your order is declined!'));
	}
	else
	{
		
		$merchantnumber = $oseMscConfig->ebs_merchantID;
		
//		$epay = $process->getAPI();
		
		$payment = oseRegistry :: call('payment');
		
		$db= oseDB :: instance();
		
		// $invoice = $db->quote( $db->getEscaped($invoice));
		//$invoice = $db->quote($invoice);
		$where= array();
//		$where[]= "`order_number` Like".$db->Quote($DR[MerchantRefNo].'%');
		$where[]= "`order_id` ='$DR[MerchantRefNo]'";
				
		$orderInfo = $payment->getOrder($where, 'obj');
		
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		$db->setQuery("select `block` from #__users where `id`=$member_id");
		$active = (int)(1-$db->loadResult());
		$orderInfoParams= oseJson :: decode($orderInfo->params);

			// get the subscription by id to check 
			if($DR[ResponseCode] === '0') //EBS: RESPONSECODE=0
			{
				//$result = $epay->capture($merchantnumber,$tid,$process->get('order_number'),$transaction['authamount'],$transaction['currency']);
				//$result = $subscription['getsubscriptionsResult'];
				//if($result['captureResult'])
				//{
					if($orderInfo->order_status == 'pending')
					{
						$payment->getInstance('Order')->confirmOrder($order_id,array('payment_serial_number'=>$DR_subscriptionid));//PAYMENT ID
						if($active)
						{
							@$app->redirect($root."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id,JText::_('Your Payment is Successful!'));
							echo "Your Payment is Successful! ";
						}
						else
						{
							@$app->redirect($root."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id,JText::_('Your Payment is Successful! Please check your mail for activation link.'));
							echo "Your Payment is Successful! Please check your mail for activation link.";
						}
						
					}
					else if($orderInfo->order_status == 'confirmed')
					{
						echo "This order has already been confirmed.<br>";
						echo "Order ID : ".$orderInfo->order_id."<br>";
						echo "Amount Paid : ".$orderInfo->payment_price."<br>";
						echo "Payment Serial : ".$orderInfo->payment_serial_number."<br>";
						echo "Payment Date & time : ".$orderInfo->create_date."<br>";
//						echo "<pre>GET: ";print_r($orderInfo);echo "</pre>";
					}
					else
					{
						echo "Unknown error<br>";
						echo "<pre>GET: ";print_r($_GET);print_r($rows);echo "</pre>";
					}
			}
			else
			{

				$mailsubject= "EBS Transaction on your Site";
				$mailbody = " Hello,An error occured while processing a EBS transaction.\n"
						   ."------------------------------------------------------------$DR[ResponseMessage]\n";
				$mailbody .= "Order ID: ".$order_id."\n";
				$mailbody .= "User ID: ".$member_id."\n";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
			//	$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$apiEmail->sendToAdminGroup($emailObj, 25);

				@$app->redirect($root.'/index.php',JText::_("Your Payment was declined at payment gateway.")."</li><li>With the Response: '$DR[ResponseMessage]'.");
				echo "Your Payment was declined at payment gateway, with the message: '$DR[ResponseMessage]'";
//				echo "<pre>";	print_r($DR); echo "</pre>";
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
