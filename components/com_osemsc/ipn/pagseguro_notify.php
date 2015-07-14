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
	require_once(OSEMSC_B_LIB.DS.'PagSeguroLibrary'.DS.'PagSeguroLibrary.php');
	/*** END OSE part ***/

	/**
	* Read post from PayPal system and create reply
	* starting with: 'cmd=_notify-validate'...
	* then repeating all values sent: that's our VALIDATION.
	**/
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$account = $oseMscConfig->pagseguro_account;
	$token = $oseMscConfig->pagseguro_token;
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	
	$code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) !== ""  ? trim($_POST['notificationCode']) : null);
    $type = (isset($_POST['notificationType']) && trim($_POST['notificationType']) !== ""  ? trim($_POST['notificationType']) : null);
	
    if ( $code && $type ) 
    {
   		$notificationType = new NotificationType($type);
    	$strType = $notificationType->getTypeFromValue();
			
		switch($strType) 
		{
				
			case 'TRANSACTION':
				$credentials = new AccountCredentials($account, $token);
		    	try 
		    	{
		    		$transaction = NotificationService::checkTransaction($credentials, $code);
		    	} catch (PagSeguroServiceException $e) 
		    	{
		    		$error = $e->getMessage();
		    	}
				break;
			
			default:
				$error = $notificationType->getValue();
				break;
		}
    }else{
    	$error = "Invalid notification parameters.";
    }
    
    if($transaction)
    {
    	$status = $transaction->getStatus (); 
    	$reference = $transaction->getReference();
    	$db= oseDB :: instance();
		$where= array();
		$where[]= "`order_number` = ".$db->quote($reference);
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		
    	if(empty($orderInfo))
		{
			$mailsubject= "IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The IPN
					----------------------------------\n
					Order Number: ".$reference."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			exit;
		}
		
    	switch($status)//(1-7)
    	{
    		//confirm (a transação foi paga pelo comprador e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento.)
    		case('3'):
    			$paymentOrder = oseRegistry :: call('payment')->getInstance('Order');
			
				$paymentOrder->confirmOrder($order_id, array());
    			break;
    			
    		//refund (o valor da transação foi devolvido para o compra)	
    		case('6'):
    			$paymentOrder= $payment->getInstance('Order');
    			$paymentOrder->refundOrder($order_id);
    			$mailsubject= "IPN txn on your site (Refunded)";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "a transaction for you has been refunded on your website!<br />";
				$mailbody .= "the memberships refered to have been cancelled<br />";
				$mailbody .= "Order ID:".$order_id;
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
    			break;
    			
    		default:
    			break;	
    	}
    }else{
    	$mailsubject= "IPN Fatal Error on your Site";
		$mailbody .= "Error:".$error;
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
    }
	
}

?>