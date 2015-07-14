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
	require_once(OSEMSC_B_LIB.DS.'icepayAPI'.DS.'icepay.php');
	/*** END OSE part ***/
	
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$merchant_id = $oseMscConfig->icepay_merchant_id;
	$secret_code = $oseMscConfig->icepay_secret_code;
	$checkIP = $oseMscConfig->icepay_checkIP;
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	$process= new oseMscIpnICEPAY();

	if($checkIP)
	{
		$valid_ip = $process->uc_icepay_ipCheck($_SERVER['REMOTE_ADDR']);
		if(!$valid_ip)
		{
			$mailsubject= "ICEPAY IPN Fatal Error on your Site";
			$mailbody= "Hello,
			A fatal error occured while processing a ICEPAY transaction(IP not in range).
			----------------------------------
			IP: ".$_SERVER['REMOTE_ADDR'];
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			exit;
		}
	}
	
	$tran = new ICEPAY($merchant_id,$secret_code);
	if($tran->OnPostback())
	{
		$data = $tran->GetPostback();
		$status = $data->status;
		$reference = $data->reference;
		$db = JFactory::getDBO();
		$where= array();
		$where[]= "`order_number`=".$db->quote($reference);
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		
		if(empty($orderInfo)) {
			$mailsubject= "ICEPAY IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The IPN
					----------------------------------\n
					Invoice: ".$reference."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			exit;
		}
		
		switch($status)
		{
			case('OK'):
				$payment= oseRegistry :: call('payment')->getInstance('Order');
				$payment->confirmOrder($order_id, array(), 0, $member_id);
				break;

			case('ERR'):
				oseRegistry :: call('payment')->updateOrder($order_id, "invalid");
				$mailsubject= "ICEPAY IPN Transaction on your site";
				$mailbody= "Hello,
						a Failed ICEPAY Transaction requires your attention.
						-----------------------------------------------------------
						Order ID: ".$order_id."
						User ID: ".$member_id."
						Payment Status returned by ICEPAY: $status \n\n"
						."\r\n IPN".http_build_query($_POST)
						;
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				break;
					
			case('OPEN'):
				break;	

			case('CBACK'):
			case('REFUND'):
				$paymentOrder= $payment->getInstance('Order');
				$list= $paymentOrder->refundOrder($order_id);
				
				$mailsubject= "ICEPAY IPN txn on your site (Refunded)";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "a ICEPAY transaction for you has been refunded on your website!<br />";
				$mailbody .= "the memberships refered to have been cancelled<br />";
				$mailbody .= "-----------------------------------------------------------<br />";
				$mailbody .= "User ID: ". $member_id."<br />";
				$mailbody .= "Order ID: $order_id<br />";
				$mailbody .= "Payment Status returned by ICEPAY: Payment Refunded<br />";
				$mailbody .= "Transaction ID: ".$data->transactionID;
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				break;	
		}
	}
}

class oseMscIpnICEPAY {
	function __construct() {
	
	}
	function uc_icepay_ipCheck($ip){
		if (!$this->uc_icepay_ip_in_range($ip, "194.30.175.0-194.30.175.255") && !$this->uc_icepay_ip_in_range($ip, "194.126.241.128-194.126.241.191")) return false;
		return true;
	}
	
	function uc_icepay_decbin32 ($dec) {
	  return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
	}
	
	function uc_icepay_ip_in_range($ip, $range) {
	  if (strpos($range, '/') !== false) {
		// $range is in IP/NETMASK format
		list($range, $netmask) = explode('/', $range, 2);
		if (strpos($netmask, '.') !== false) {
		  // $netmask is a 255.255.0.0 format
		  $netmask = str_replace('*', '0', $netmask);
		  $netmask_dec = ip2long($netmask);
		  return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
		} else {
		  // $netmask is a CIDR size block
		  // fix the range argument
		  $x = explode('.', $range);
		  while(count($x)<4) $x[] = '0';
		  list($a,$b,$c,$d) = $x;
		  $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
		  $range_dec = ip2long($range);
		  $ip_dec = ip2long($ip);
	
		  # Strategy 1 - Using substr to chop up the range and pad it with 1s to the right
		  $broadcast_dec = bindec(substr($this->uc_icepay_decbin32($range_dec), 0, $netmask) 
								. str_pad('', 32-$netmask, '1'));
	
		  # Strategy 2 - Use math to OR the range with the wildcard to create the Broadcast address
		  $wildcard_dec = pow(2, (32-$netmask)) - 1;
		  $broadcast_dec = $range_dec | $wildcard_dec;
	
		  return (($ip_dec & $broadcast_dec) == $ip_dec);
		}
	  } else {
		// range might be 255.255.*.* or 1.2.3.0-1.2.3.255
		if (strpos($range, '*') !==false) { // a.b.*.* format
		  // Just convert to A-B format by setting * to 0 for A and 255 for B
		  $lower = str_replace('*', '0', $range);
		  $upper = str_replace('*', '255', $range);
		  $range = "$lower-$upper";
		}
	
		if (strpos($range, '-')!==false) { // A-B format
		  list($lower, $upper) = explode('-', $range, 2);
		  $lower_dec = ip2long($lower);
		  $upper_dec = ip2long($upper);
		  $ip_dec = ip2long($ip);
		  return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
		}
		return false;
	  }
	
	  $ip_dec = ip2long($ip);
	  return (($ip_dec & $netmask_dec) == $ip_dec);
	}
}
?>