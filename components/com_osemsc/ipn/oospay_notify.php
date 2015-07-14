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
	$app = & JFactory :: getApplication('site');
	jimport('joomla.plugin.helper');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/

	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	
	$TerminalID = $oseMscConfig->oospay_terminal_id;
	$TerminalID_ = "0".$oseMscConfig->oospay_terminal_id;
	$MerchantID = $oseMscConfig->oospay_merchant_id;
	$StoreKey = $oseMscConfig->oospay_store_key;
	$ProvisionPassword = $oseMscConfig->oospay_provision_password;
	$oospay_lang = $oseMscConfig->oospay_lang;
	$SecurityData = strtoupper(sha1($ProvisionPassword.$TerminalID_));
	$Type = "sales";
	$InstallmentCount = "";
		
	$process = new oseMscIpnOOSPay($_POST);
	$OrderID = $process->get('orderid');
	
	// Get the Order Details from the database
	$db= oseDB :: instance();
		
	$where= array();
	$where[]= "`order_id` = ".$db->quote($OrderID);
	$payment= oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	$orderInfoParams= oseJson :: decode($orderInfo->params);
	$order_id= $orderInfo->order_id;
	$member_id= $orderInfo->user_id;
	$Amount = intval(strval(100 * $orderInfo->payment_price));
	
	$query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = ".$orderInfo->order_id;
	$db->setQuery($query);
	$orderItem = $db->loadObject();
	$msc_id = $orderItem->entry_id;
	$orderItemParams = oseJson :: decode($orderItem->params);
	$msc_option = $orderItemParams->msc_option;
	$query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'paymentAdv' AND `id` = ".$msc_id;
	$db->setQuery($query);
	$ext = $db->loadResult();
	$params = oseJson :: decode($ext);



	if(isset($params->$msc_option->installment) && !empty($params->$msc_option->installment))
	{
		$InstallmentCount = $params->$msc_option->installment;
	}

    //settings for garanti
    require_once(OSEMSC_F_HELPER . DS . 'oseMscPublic.php');
    $cart = oseMscPublic::getCart();

    if(isset($cart->cart['params']['garanti_taksit']) && $cart->cart['params']['garanti_taksit'] && isset($cart->cart['params']['garanti_vade']) && $cart->cart['params']['garanti_vade']) {
        $InstallmentCount = $cart->cart['params']['garanti_taksit'];
        $vade = $cart->cart['params']['garanti_vade'];

        //$Amount = $cart->cart['total']*100;
        $Amount = $Amount * $vade/100 + $Amount;

        $Amount = round($Amount);

        //echo $Amount; die;

    }

    $server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
	$returnUrl = urldecode($orderInfoParams->returnUrl);
	$returnUrl = !empty($returnUrl)?$returnUrl:$server."index.php?option=com_osemsc&view=member";
	$HashData = strtoupper(sha1($TerminalID.$OrderID.$Amount.$process->get('successurl').$process->get('errorurl').$Type.$InstallmentCount.$StoreKey.$SecurityData));
	
	//debug
	/*
	foreach($_POST as $key => $value)
	{
	    echo "<br>".$key." : ".$value;
	}
	exit;
	*/
	if($process->get('response') == 'Declined')
	{
		$mailsubject= "OOSPay IPN Transaction Error on your site";
		$mailbody= "Dear Administrator,<br /><br />";
		$mailbody .= "IPN Error Message : ".$process->get('hostmsg');
		$mailbody .= "Order ID: $order_id<br />";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$message = $process->get('errmsg').". ".$process->get('hostmsg');
		$app->redirect($server."index.php?option=com_osemsc&view=register",$message);
		return;
	}
	if($HashData == $process->get('secure3dhash'))
	{
		if($process->get('response') == 'Approved')
		{
			$payment= oseRegistry :: call('payment')->getInstance('Order');
			$payment->confirmOrder($order_id, array(), 0, $member_id);	
			$message = JText::_("İşleminiz başarıyla tamamlandı! Günlük uygulamalar bölümüne hoş geldiniz.");
			$app->redirect($returnUrl,$message);
			return;
		}
		
		$status = $process->get("mdstatus");
		if(empty($status))
		{
			$mailsubject= "OOSPay IPN Transaction on your site";
			$mailbody= "Unknown transaction status";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			return;
		}

		switch($status)
		{
			//Tam Doğrulama Full Validation
			case(1) :
				$payment= oseRegistry :: call('payment')->getInstance('Order');
				$payment->confirmOrder($order_id, array(), 0, $member_id);	
				$message = 'Transaction Success!';
			break;
			
			//Kart Sahibi veya bankası sisteme kayıtlı değil Cardholder or the bank is not registered in the system
			case(2) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "Cardholder or the bank is not registered in the system.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Kartın bankası sisteme kayıtlı değil Bank card is not registered in the system
			case(3) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "Bank card is not registered in the system.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Doğrulama denemesi, kart sahibi sisteme daha sonra kayıt olmayı seçmiş Verification experiment, the card holder to register on the system chosen, then
			case(4) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "Verification experiment.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Doğrulama yapılamıyor validation can not be made
			case(5) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "validation can not be made.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Sistem Hatası system Error
			case(7) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "System Error.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Bilinmeyen Kart No Unknown Card No.
			case(8) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "Unknown Card Number.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			//Doğrulama Başarısız, 3D Secure imzası geçersiz validation Failed,Secure the signature is invalid
			case(0) :
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "validation Failed,Secure the signature is invalid.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
			
			default:
				$mailsubject= "OOSPay IPN Transaction Error on your site";
				$mailbody= "Dear Administrator,<br /><br />";
				$mailbody .= "validation Failed,Secure the signature is invalid.<br />";
				$mailbody .= "IPN Error Message : ".$process->get('mderrormessage');
				$mailbody .= "Order ID: $order_id<br />";
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$message = $process->get('mderrormessage');
			break;
		}
		$link = ($status == 1)?$returnUrl:$server."index.php?option=com_osemsc&view=register";
		$app->redirect($link,$message);
	}else{
		$mailsubject= "OOSPay IPN Transaction Error on your site";
		$mailbody= "Validation Failed,Secure the signature is invalid";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$app->redirect($server."index.php?option=com_osemsc&view=register",JText::_('ERROR'));
		return;
	}
}

class oseMscIpnOOSPay {
	
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