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

	$post_msg= http_build_query($_POST);
	debug_msg("2. Received this POST: $post_msg");
	$post_msg= "";
	/**
	* Read post from PayPal system and create reply
	* starting with: 'cmd=_notify-validate'...
	* then repeating all values sent: that's our VALIDATION.
	**/
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$clickbank_account= $oseMscConfig->clickbank_account;
	$secret_key= $oseMscConfig->clickbank_secret_key;
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	$process= new oseMscIpnClickBank($secret_key);
	
	$isValid= $process->validate_ipn();
	if(!$isValid)
	{
		$mailsubject= "ClickBank IPN txn on your site";
		$mailbody = "Dear Administrator, <br/><br/>";
		$mailbody .= "A subscription payment transaction was failed on your website!<br/><br/>";
		$mailbody .= "IPN validation failed. <br/><br/>";

		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		
	}else{
		
		$db= oseDB :: instance();
		$where= array();
		$where[]= " `order_number` = ".$db->quote($process->get('invoice')).' OR `payment_serial_number`='.$db->quote($process->get('ctransreceipt'));
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		
		if(empty($orderInfo)) 
		{
			$mailsubject= "ClickBank IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The ClickBank IPN
					----------------------------------\n
					Invoice: ".$process->get('invoice')."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			exit;
		}
		
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		
		switch($process->get('ctransaction')) 
		{
			case('SALE')://The purchase of a standard product or the initial purchase of recurring billing product.
			case 'BILL'://A rebill for a recurring billing product.
				$payment= oseRegistry :: call('payment')->getInstance('Order');
				$payment->confirmOrder($order_id, array('payment_serial_number'=>$process->get('ctransreceipt')), 0, $member_id);
				break;
		
			case 'TEST': //Triggered by using the test link on the site page.
            case 'TEST_SALE':	
            	$mailsubject= "ClickBank IPN txn on your site";
				$mailbody = "Dear Administrator, <br/><br/>";
				$mailbody .= "A test subscription transaction for you has been made on your website!<br/><br/>";
				$mailbody .= "-----------------------------------------------------------<br/><br/>";
				$mailbody .= "Member ID: ".$member_id."<br /><br />";
				$mailbody .= "Order ID: $order_id<br/><br/>";
				$mailbody .= "Invoice: ".$process->get('invoice')."<br/><br/>";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$payment= oseRegistry :: call('payment')->getInstance('Order');
				$payment->confirmOrder($order_id, array('payment_serial_number'=>$process->get('ctransreceipt')), 0, $member_id);
            	break;
            	
            case 'RFND': //The refunding of a standard or recurring billing product. Recurring billing products that are refunded also result in a "CANCEL-REBILL" action.
            case 'CGBK': //A chargeback for a standard or recurring product.
            case 'INSF': //An eCheck chargeback for a standard or recurring product.
            	$paymentOrder= $payment->getInstance('Order');	
            	$paymentOrder->refundOrder($order_id);
            	break;
            	
            case 'CANCEL-REBILL': //The cancellation of a recurring billing product. Recurring billing products that are canceled do not result in any other action.
            case 'UNCANCEL-REBILL': //Reversing the cancellation of a recurring billing product.
            	$apiEmail->sendCancelOrderEmail(array('orderInfo'=>$orderInfo));
            	oseRegistry :: call('payment')->updateOrder($order_id, "cancelled");

				$query = " SELECT entry_id FROM `#__osemsc_order_item`"
						." WHERE `order_id` = '{$order_id}'"
						;
				$db->setQuery($query);
				$msc_id = $db->loadResult();

				$paymentOrder->updateMembership($msc_id, $member_id, $order_id, 'm');
            	break;
            	
            default:
            	break;	
		}			
	}
}
class oseMscIpnClickBank
{

	function __construct($secret_key) {
		// Notify string
		$this->secret_key= $secret_key;
		$this->ccustname= JRequest :: getVar('ccustname');
		$this->ccustemail= JRequest :: getVar('ccustemail');
		$this->ccustcc= JRequest :: getVar('ccustcc');
		$this->ccuststate= JRequest :: getVar('ccuststate');
		$this->ctransreceipt= JRequest :: getVar('ctransreceipt');
		$this->cproditem= JRequest :: getVar('cproditem');
		$this->ctransaction= JRequest :: getVar('ctransaction');
		$this->ctransaffiliate= JRequest :: getVar('ctransaffiliate');
		$this->ctranspublisher= JRequest :: getVar('ctranspublisher');
		$this->cprodtype= JRequest :: getVar('cprodtype');
		$this->ctranspaymentmethod= JRequest :: getVar('ctranspaymentmethod');
		$this->ctransamount= JRequest :: getVar('ctransamount');
		$this->caffitid= JRequest :: getVar('caffitid');
		$this->cvendthru= JRequest :: getVar('cvendthru');
		$this->cverify= JRequest :: getVar('cverify');
		//$this->invoice= JRequest :: getVar('invoice');
		parse_str($this->cvendthru,$vars);
		$this->invoice = $vars['invoice'];
	}

	function validate_ipn()
	{
		$secretKey=$this->secret_key;
		$pop = "";
    	$ipnFields = array();
	    foreach ($_POST as $key => $value) {
	        if ($key == "cverify") {
	            continue;
	        }
	        $ipnFields[] = $key;
	    }
	    sort($ipnFields);
	    foreach ($ipnFields as $field) {
	                // if Magic Quotes are enabled $_POST[$field] will need to be
	        // un-escaped before being appended to $pop
	        $pop = $pop . $_POST[$field] . "|";
	    }
	    $pop = $pop . $secretKey;
	    $calcedVerify = sha1(mb_convert_encoding($pop, "UTF-8"));
	    $calcedVerify = strtoupper(substr($calcedVerify,0,8));
		if($calcedVerify == $_POST["cverify"])
		{
			return true;
		}else{
			require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
			$apiEmail= oseRegistry :: call('member')->getInstance('email');
			$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
			$mailsubject= "ClickBank IPN txn on your site";
			$mailbody = "Dear Administrator, <br/><br/>";
			foreach ($_POST as $key => $value) 
			{
				$mailbody .= $key ." = ".$value."<br/><br/>";
			}
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			return false;
		}
    }
    
	function get($key, $default= null) 
	{
		if(empty($this->{$key})) {
			$this->{$key}= $default;
		}
		return $this-> {$key};
	}
}
?>