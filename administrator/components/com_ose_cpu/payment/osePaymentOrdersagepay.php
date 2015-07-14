<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderSagepay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function sagepayOneOffPay($orderInfo,$params=array()) 
	{
		$config = oseMscConfig :: getConfig('', 'obj');
		$vendorname = $config->sagepay_vendorname;	
		$password = $config->sagepay_password;
		$sagepay_mode = $config->sagepay_mode;
		$vendoremail = $config->sagepay_vendoremail;
		$txtype = $config->sagepay_txtype;
		
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$amount = number_format($orderInfo->payment_price,2);
		$currency = $orderInfo->payment_currency;
		$notify_url = JURI :: base()."components/com_osemsc/ipn/sagepay_notify.php"; 
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode($orderInfoParams->returnUrl);
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		$db= oseDB :: instance();
		$query = "SELECT country_2_code FROM `#__osemsc_country` WHERE `country_3_code` = '{$billinginfo->country}'";
		$db->setQuery($query);
		$billinginfo->country = $db->loadResult();
		$desc = self::generateDesc($order_id);
		$user = JFactory::getUser($orderInfo->user_id);
		
		
		if($sagepay_mode == 'simulator')
		{
			$url = "https://test.sagepay.com/simulator/vspformgateway.asp";
		}elseif($sagepay_mode == 'test')
		{
			$url = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
		}elseif($sagepay_mode == 'live')
		{
			$url = "https://live.sagepay.com/gateway/service/vspform-register.vsp"; 
		}
		
		$post_variables = array(
			//'VPSProtocol' => '2.23',
			//'TxType' => 'PAYMENT',
			//'Vendor' => $vendorname,
			'VendorTxCode' =>$order_number,
			'Amount' => $amount,
			'Currency' => $currency,
			'Description' => $desc,
			'SuccessURL' => $notify_url,
			'FailureURL' => $notify_url,
			'CustomerName' => $billinginfo->firstname." ".$billinginfo->lastname,
			//'SendEMail' => 1, //0 = Do not send either customer or vendor e-mails, 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided. 
			'CustomerEMail' => $user->email,
			'VendorEMail' => $vendoremail,
			'BillingFirstnames' => $billinginfo->firstname,
			'BillingSurname' => $billinginfo->lastname,
			'BillingAddress1' => $billinginfo->addr1,
			'BillingAddress2' => $billinginfo->addr2,
			'BillingCity' => $billinginfo->city,
			'BillingPostCode' => $billinginfo->postcode,
			'BillingCountry' => $billinginfo->country,
			'BillingState' => $billinginfo->state,
			'DeliveryFirstnames' => $billinginfo->firstname,
			'DeliverySurname' => $billinginfo->lastname,
			'DeliveryAddress1' => $billinginfo->addr1,
			'DeliveryAddress2' => $billinginfo->addr2,
			'DeliveryCity' => $billinginfo->city,
			'DeliveryPostCode' => $billinginfo->postcode,
			'DeliveryCountry' => $billinginfo->country,
			'DeliveryState' => $billinginfo->state,
			'DeliveryPhone' => $billinginfo->telephone,
			'Basket' => '1:'.$desc.':1:'.$orderInfoParams->subtotal.':'.$orderInfoParams->gross_tax.':'.$amount.':'.$amount
			//'ApplyAVSCV2'=>1
			//'Apply3DSecure'=>1
		);
		if($billinginfo->country != 'US')
		{
			unset($post_variables['BillingState']);
			unset($post_variables['DeliveryState']);
		}
		$strIn = null;
		foreach($post_variables as $name => $value) 
		{
			$strIn.=$name."=".$value."&";
		}
		$strIn = trim($strIn,"&");
		$Crypt = self::encryptAndEncode($strIn,'AES',$password); 
		//print_r($post_variables);exit;
		$html['form']= '<form action="'.$url.'" method="post">';
		$html['form'] .= '<input type="image" id="sagepay_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayGate').'" />';
		$html['form'] .= '<input type="hidden" name="VPSProtocol" value="2.23" />';
		$html['form'] .= '<input type="hidden" name="TxType" value="'.$txtype.'" />';
		$html['form'] .= '<input type="hidden" name="Vendor" value="'.$vendorname.'" />';
		$html['form'] .= '<input type="hidden" name="Crypt" value="'.$Crypt.'" />';
		$html['form'] .= '</form>';
		return $html;
	}

	function encryptAndEncode($strIn,$strEncryptionType,$strEncryptionPassword) 
	{
	
		if ($strEncryptionType=="XOR") 
		{
			//** XOR encryption with Base64 encoding **
			return base64Encode(simpleXor($strIn,$strEncryptionPassword));
		} 
		else 
		{
			//** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **
	
			//** use initialization vector (IV) set from $strEncryptionPassword
	    	$strIV = $strEncryptionPassword;
	    	
	    	//** add PKCS5 padding to the text to be encypted
	    	$strIn = self::addPKCS5Padding($strIn);
	
	    	//** perform encryption with PHP's MCRYPT module
			$strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV);
			
			//** perform hex encoding and return
			return "@" . bin2hex($strCrypt);
		}
	}
	
	function addPKCS5Padding($input)
	{
	   $blocksize = 16;
	   $padding = "";
	
	   // Pad input to an even block size boundary
	   $padlength = $blocksize - (strlen($input) % $blocksize);
	   for($i = 1; $i <= $padlength; $i++) {
	      $padding .= chr($padlength);
	   }
	   
	   return $input . $padding;
	}

	
	function generateDesc($order_id)
	{
		$title = null;
        $db = oseDB::instance();
        $query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = '{$order_id}'";
        $db->setQuery($query);
        $obj = $db->loadObject();
        $params = oseJson::decode($obj->params);
        $msc_id = $obj->entry_id;
       
        $query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = ".(int)$msc_id;
        $db->setQuery($query);
        $msc_name = $db->loadResult();
       
        $msc_option = $params->msc_option;
        $query = "SELECT params FROM `#__osemsc_ext` WHERE `type` = 'payment' AND `id` = ".(int)$msc_id;
        $db->setQuery($query);
        $result = oseJson::decode($db->loadResult());
        foreach($result as $key => $value)
        {
            if($msc_option == $key)
            {
                if($value->recurrence_mode == 'period')
                {
                    if($value->eternal)
                    {
                        $title = 'Life Time Membership';
                    }else{
                       
                        $title = $value->recurrence_num.' '.ucfirst($value->recurrence_unit).' Membership';
                    }
                }else{
                    $start_date = date("l,d F Y",strtotime($value->start_date));
                    $expired_date = date("l,d F Y",strtotime($value->expired_date));
                    $title  = $start_date.' - '. $expired_date.' Membership';
                }
               
            }
        }
        $title = $msc_name.' - '.$title;
        return $title;
	}
}
?>