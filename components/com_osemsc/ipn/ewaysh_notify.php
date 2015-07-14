<?php

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
	
	$pConfig= oseMscConfig :: getConfig('payment', 'obj');
	if($pConfig->ewaysh_testmode)
	{
		$CustomerID = '87654321';
		$UserName = 'TestAccount';
	}else{
		$CustomerID = $pConfig->ewaysh_customer_id;
		$UserName = $pConfig->ewaysh_username;
	}
	
	$querystring="CustomerID=".$CustomerID."&UserName=".$UserName."&AccessPaymentCode=".$_REQUEST['AccessPaymentCode'];
	$querystring = str_replace(" ", "%20", $querystring);
	$posturl="https://nz.ewaygateway.com/Result/?".$querystring;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $posturl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	if (@CURL_PROXY_REQUIRED == 'True')
	{
		$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
		curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
		curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	}

	$response = curl_exec($ch);

	//print_r($response);exit;
	$eway = new oseMscIpneWay();
	$authecode = $eway->fetch_data($response, '<authCode>', '</authCode>');
	$responsecode = $eway->fetch_data($response, '<responsecode>', '</responsecode>');
	$trxnnumber = $eway->fetch_data($response, '<trxnnumber>', '</trxnnumber>');
	$trxnstatus = $eway->fetch_data($response, '<trxnstatus>', '</trxnstatus>');
	$trxnresponsemessage = $eway->fetch_data($response, '<trxnresponsemessage>', '</trxnresponsemessage>');
	$MerchantReference = $eway->fetch_data($response, '<MerchantReference>', '</MerchantReference>');
	$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
	//echo $authecode;exit;
	// Response Success Message
	if($responsecode=="00" || $responsecode=="08" || $responsecode=="10" || $responsecode=="11" || $responsecode=="16")
    {
    	//Approve
		//$session =& JFactory::getSession();
		//$order_id = $session->get( 'oseOrder_id', null);
		$order_id = $MerchantReference;
		if(!empty($order_id))
		{
			$db= oseDB :: instance();
			//$user = JFactory::getUser();
			$where= array();
			$where[]= "`order_id` = ".$order_id;
			$payment= oseRegistry :: call('payment');
			$orderInfo = $payment->getOrder($where, 'obj');
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			$paymentOrder = oseRegistry :: call('payment')->getInstance('Order');
			$paymentOrder->confirmOrder($order_id, array());
			
			$returnUrl = JROUTE::_($server."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id);
			$returnUrl = $returnUrl?$returnUrl:$server."index.php?option=com_osemsc&view=member";
			$app->redirect($returnUrl,'Transaction Success!');
		}else{
			$app->redirect($server."index.php?option=com_osemsc&view=register",'Transaction Failed, Order Not Found!');
		}	
    }else{
    	$app->redirect($server.'index.php?option=com_osemsc&view=register','Transaction Failed '.$trxnresponsemessage);

    }
    
class oseMscIpneWay {

	function __construct()
	{

	}

	function fetch_data($string, $start_tag, $end_tag)
   {

		$position = stripos($string, $start_tag);

		$str = substr($string, $position);

		$str_second = substr($str, strlen($start_tag));

		$second_positon = stripos($str_second, $end_tag);

		$str_third = substr($str_second, 0, $second_positon);

		$fetch_data = trim($str_third);

		return $fetch_data;
	}
}	