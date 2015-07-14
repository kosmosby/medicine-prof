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
			echo "<h2>BBVA Notify.php Debug OUTPUT</h2>";
			define("_DEBUG_HEADER", "1");
		}
		$messages[]= "<pre>$msg</pre>";
		echo end($messages);
	}
}

if(!empty($_GET['peticion'])) 
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
	require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_ose_cpu".DS."payment".DS."osePaymentOrderbbva.php");
	/*** END OSE part ***/
	
	
	$peticion=$_GET['peticion'];
 	
	$resXml = simplexml_load_string($peticion);
 
	foreach ($resXml->respago as $res)
	{
	   $estado="{$res->estado}";
	   $idtransaccion="{$res->idtransaccion}";
	   $codigoerror="{$res->coderror}";
	   $codigoerrordescripcion="{$res->deserror}";
	   $codigoautorizacion="{$res->codautorizacion}";
	   $localizador="{$res->localizador}";
	   $idterminal="{$res->idterminal}";
	   $idcomercio="{$res->idcomercio}";
	   $moneda="{$res->moneda}";
	   $importe="{$res->importe}";
	   $firma="{$res->firma}";
	} 
	$idtransaccion2 = intval($idtransaccion);
	
	
	$oseMscConfig= oseRegistry :: call('msc')->getConfig('', 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$bbva = new osePaymentOrderbbva();
	$db= oseDB :: instance();
	$where= array();
	$where[]= "`order_id` =".$idtransaccion2;
		
	$payment = oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	
	if(empty($orderInfo)) 
	{
		$mailsubject= "BBVA IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order number feedbacked by The BBVA IPN
				----------------------------------\n
				Invoice: ".$process->get('item_id')."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		
	}
	$obfuscated="88;87;1A;02;78;03;74;05;70;03;1F;XX;XX;XX;XX;XX;XX;XX;XX;XX";
	$clave=$oseMscConfig->bbva_clave;
	$comercio=$oseMscConfig->bbva_comercio;
	$terminal=$oseMscConfig->bbva_terminal;
	$moneda=$oseMscConfig->bbva_currency;
	
	$des_key=$clave.substr($comercio,0,9)."***";

	$desobfuscated=$bbva->desobfuscate($obfuscated,$des_key);
	
	$importe2=$orderInfo->payment_price / 1;
	$importe_formatado=$importe2 * 100;
	$datos_firma = $terminal.$comercio.$idtransaccion.$importe_formatado.$moneda.$estado.$codigoerror.$codigoautorizacion.$desobfuscated;
	$firma2 = strtoupper(sha1($datos_firma));
		
	if($firma != $firma2)
	{
		$app->redirect(JRoute::_('index.php?option=com_osemsc&view=member',JText::_('Your Payment is failed!')));
	}
	else
	{
		if (intval($estado)==2)
		{
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
				$mailsubject= "BBVA Transaction on your Site";
				$mailbody = " Hello,An error occured while processing a BBVA transaction.\n"
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
					$payment->getInstance('Order')->confirmOrder($order_id);
				}
	
			}
		}else{
			
			//$arr = array('allow_work'=>true,'msc_id'=>$msc_id,'member_id'=>$user_id,'master'=>true);
			//oseMscAddon::runAction('join.history.manualCancelOrder', $arr);
			$order_id= $orderInfo->order_id;
			$member_id= $orderInfo->user_id;
			$apiEmail->sendCancelOrderEmail(array('orderInfo'=>$orderInfo));
			oseRegistry :: call('payment')->updateOrder($order_id, "cancelled");
			$query = " SELECT entry_id FROM `#__osemsc_order_item`"
					." WHERE `order_id` = '{$order_id}'"
					;
			$db->setQuery($query);
			$msc_id = $db->loadResult();
			$paymentOrder->updateMembership($msc_id, $member_id, $order_id, 'm');
			$arr = array('allow_work'=>true,'msc_id'=>$msc_id,'member_id'=>$member_id,'master'=>true);
			//oseMscAddon::runAction('join.history.manualCancel', $arr);
			$mailsubject= "BBVA Transaction on your site";
			$mailbody = "Dear Administrator, <br/><br/>";
			$mailbody .= "A cancellation transaction for you has been made on your website!<br/><br/>";
			$mailbody .= "-----------------------------------------------------------<br/><br/>";
			$mailbody .= "Member ID: ".$member_id."<br /><br />";
			$mailbody .= "Order ID: $order_id<br/><br/>";

			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		}	
	}

}
/*
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
*/
?>