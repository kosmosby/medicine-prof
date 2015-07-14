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
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/
	
	foreach ($_POST as $key => $val) {
		JRequest::setVar($key,$val);
	}
	
	$process = new oseMscIpnBeanStream();
	$oseMscConfig = oseRegistry::call('msc')->getConfig('','obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$db= oseDB :: instance();
	// $invoice = $db->quote( $db->getEscaped($invoice));
	//$invoice = $db->quote($invoice);
	$where= array();
	$where[] = "`order_id`=".$db->quote($process->get('order_id'));
	
	$payment= oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	
	if(empty($orderInfo)) {
		$mailsubject= "BeanStream IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order number feedbacked by The PayPal IPN
				----------------------------------\n
				Invoice: ".$process->get('order_id')."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

	}
	
	$order_id = $orderInfo->order_id;
	$member_id = $orderInfo->user_id;
	$orderInfoParams = oseJson :: decode($orderInfo->params);
	$query = " SELECT * FROM `#__osemsc_order_item`"
			." WHERE order_id = '{$order_id}'"
			;
	$db->setQuery($query);
	$orderItems= oseDB :: loadList('obj');
	
	if(!$process->check($oseMscConfig,$orderInfoParams->timestamp))
	{
		$mailsubject= "BeanStream IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order number feedbacked by The BeanStream IPN
				----------------------------------\n
				Invoice: ".$process->get('order_id')."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$res= 'Other Error!';
	}
	else
	{
		$payment= oseRegistry :: call('payment')->getInstance('Order');
		$payment->confirmOrder($order_id, array());
		//$params = oseRegistry::call('member')->getAddonParams($msc_id,$member_id,$order_id);
		//oseRegistry::call('msc')->runAddonAction('member.msc.joinMsc',$params);
		$payment_status= "Payment Completed";
		$mailsubject= "BeanStream IPN txn on your site";
		$mailbody= "Hello,\n\n";
		$mailbody .= "a BeanStream transaction for you has been made on your website!\n";
		$mailbody .= "-----------------------------------------------------------\n";
		$mailbody .= "Transaction ID: $process->trnId\n";
		$mailbody .= "Payer Email: $process->email\n";
		$mailbody .= "Order ID: $order_id\n";
		$mailbody .= "Payment Status returned by BeanStream: $payment_status\n";
		$mailbody .= "Order Status Code: ".$payment_status;
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
	}
	
}

class oseMscIpnBeanStream 
{
	function __construct() {
		// Notify string
		$this->ipn_code = JRequest :: getString('ipn',null);
		$this->payment_serial_number = JRequest :: getInt('billingId');
		$this->order_id = JRequest :: getInt('ref1');
		$this->timestamp = JRequest :: getString('ref2');
		$this->email = JRequest :: getString('emailAddress');
		$this->trnId = JRequest :: getInt('trnId');
		$this->trnApproved = JRequest :: getInt('trnApproved');
	}
	
	function check($config,$timestamp)
	{	
		if(empty($config->beanstream_ipn))
		{
			
		}
		else
		{
			if($this->ipn_code != $config->beanstream_ipn)
			{
				//return false;
			}	
		}
		
		
		if($this->timestamp != $timestamp)
		{
			return false;
		}
		
		return true;
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