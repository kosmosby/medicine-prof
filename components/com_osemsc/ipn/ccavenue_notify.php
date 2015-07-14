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
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');

	$WorkingKey = $oseMscConfig->ccavenue_working_key;
	$Merchant_Id = JRequest::getVar('Merchant_Id');
	$Amount = JRequest::getVar('Amount');
	$Currency = JRequest::getVar('Currency');
	$Order_Id = JRequest::getVar('Order_Id');
	$Merchant_Param = JRequest::getVar('Merchant_Param');
	$Checksum = JRequest::getVar('Checksum');
	$AuthDesc = JRequest::getVar('AuthDesc',null);
	
	$process = new oseMscIpnCCAvenue();
	if($oseMscConfig->ccavenue_currency == 'USD')
	{
		$AuthDesc = JRequest::getVar('Auth_Status',$AuthDesc);
		$verify = $process->verifyCheckSumAll($Merchant_Id, $Order_Id, $Amount, $WorkingKey, $Currency ,$AuthDesc,$Checksum) ;
	}else{
		$verify = $process->verifyChecksum($Merchant_Id, $Order_Id , $Amount,$AuthDesc,$Checksum,$WorkingKey);
	}
	
	if(!$verify)
	{
		$mailsubject= "CCAvenue IPN Fatal Error on your site";
		$mailbody= "Hello,
			a Failed CCAvenue Transaction requires your attention.IPN verify failed
			-----------------------------------------------------------
			Order ID: ".$Order_Id;
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$app->redirect(JURI :: root(),'IPN verify failed');
		return;
	}
	if($verify)
	{
		//$order_number = $Order_Id;
		$db= oseDB :: instance();
		$where= array();
		$where[]= " `order_id` = '{$Order_Id}'";
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		
		if(empty($orderInfo))
		{
			$mailsubject= "CCAvenuel IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The PayPal IPN
					----------------------------------\n
					Invoice: ".$Order_Id."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
			$app->redirect(JURI :: root()."index.php?option=com_osemsc&view=register",'Transaction Failed, Order Not Found!');
		}
		switch($AuthDesc) 
		{
			case('Y'):
				//transaction is successful. 
				$paymentOrder = oseRegistry :: call('payment')->getInstance('Order');
				$paymentOrder->confirmOrder($orderInfo->order_id, array());

				$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
				$returnUrl = urldecode(JROUTE::_($server."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id));
				$returnUrl = $returnUrl?$returnUrl:JURI :: root()."index.php?option=com_osemsc&view=member";
				$app->redirect($returnUrl,'Transaction Success!');
				break;
				
			case('B'):
				
				$mailsubject= "PayPal IPN Transaction on your site";
				$mailbody= "Hello,
							Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail \n\n
							This is only if payment for this transaction has been made by an American Express Card \n\n
							since American Express authorisation status is available only after 5-6 hours by mail from ccavenue and at the 'View Pending Orders' \n\n"
							;
				$emailObj= new stdClass();
				$emailObj->subject= $mailsubject;
				$emailObj->body= $mailbody;
				$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
				$app->redirect(JURI :: root());
				break;	

			case('N'):
				//the transaction has been declined
				$app->redirect(JURI :: root(),'The transaction has been declined');
				break;
		}
	}

    
class oseMscIpnCCAvenue {

	function __construct()
	{

	}

	function verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
    {
  		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
 		$adler = 1;
  		$adler = $this->adler32($adler,$str);
 
  		if($adler == $CheckSum)
  		{
  			return true ;
  		}else{
  			return false;
  		}
 	}
  
	function verifyCheckSumAll($MerchantId, $OrderId,$Amount, $WorkingKey,$currencyType,$Auth_Status,$checksum) 
	{
		$str = "$MerchantId|$OrderId|$Amount|$WorkingKey|$currencyType|$Auth_Status";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		if($adler == $checksum)
		{
  			return true ;
  		}else{
  			return false;
  		}
	}

  	function adler32($adler , $str)
  	{
	  	$BASE =  65521 ;
		$s1 = $adler & 0xffff ;
	  	$s2 = ($adler >> 16) & 0xffff;
	  	for($i = 0 ; $i < strlen($str) ; $i++)
	  	{
	  		$s1 = ($s1 + Ord($str[$i])) % $BASE ;
	 		$s2 = ($s2 + $s1) % $BASE ;
	  		//echo "s1 : $s1 <BR> s2 : $s2 <BR>";
		}
	  	return $this->leftshift($s2 , 16) + $s1;
  	}
  
  	function leftshift($str , $num)
  	{
		$str = decbin($str);
		for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
	  	$str = "0".$str ;
	
		for($i = 0 ; $i < $num ; $i++) 
	  	{
	  		$str = $str."0";
	  		$str = substr($str , 1 ) ;
	  		//echo "str : $str <BR>";
  		}
  		return $this->cdec($str) ;
  	}
  
	function cdec($num)
  	{
		for ($n = 0 ; $n < strlen($num) ; $n++)
	  	{
	  		$temp = $num[$n] ;
	  		$dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
	  	}
		return $dec;
  	}
  
}	