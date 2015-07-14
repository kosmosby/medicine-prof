<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderCCAvenue extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function CCAvenueOneOffPostForm($orderInfo,$params=array()) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchantId = $pConfig->ccavenue_merchant_id;
		$workingKey = $pConfig->ccavenue_working_key;

		$html= array();
		$test_mode= $pConfig->ccavenue_testmode;

		if($pConfig->ccavenue_currency == 'USD')
		{
			$url = "https://world.ccavenue.com/servlet/ccw.CCAvenueController";	
		}else{
			$url= "https://www.ccavenue.com/shopzone/cc_details.jsp";
		}
		

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);

		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');
		$billinginfo= $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$query = "SELECT * FROM `#__osemsc_country` WHERE `country_3_code` = '{$billinginfo->country}'";
		$db->setQuery($query);
		$country = $db->loadObject();
		$query = "SELECT state_name FROM `#__osemsc_state` WHERE `state_2_code` = '{$billinginfo->state}' AND `country_id` = '{$country->country_id}'";
		$db->setQuery($query);
		$state = $db->loadResult();
		
		$billinginfo->country = empty($country->country_name)?$billinginfo->country:$country->country_name;
		$billinginfo->state = empty($state)?$billinginfo->state:$state;
		$billinginfo->country_2_code = empty($country->country_2_code)?$billinginfo->country:$country->country_2_code;
		$cust_name = $billinginfo->firstname.' '.$billinginfo->lastname;
		$cust_addr = $billinginfo->addr1.' '.$billinginfo->addr2;
		
		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;

		$user= & JFactory :: getUser($orderInfo->user_id);

		$orderInfoParams = oseJson::decode($orderInfo->params);
		$Redirect_Url = JURI :: base()."components/com_osemsc/ipn/ccavenue_notify.php";
		if($pConfig->ccavenue_currency == 'USD')
		{
			$Checksum = $this->getchecksumAll($merchantId,$order_id,$amount ,$workingKey,$currency,$Redirect_Url);
		}else{
			$Checksum = $this->getCheckSum($merchantId,$amount,$order_id ,$Redirect_Url,$workingKey);
		}
		
		// Create description
		$description = self::generateDesc($order_id);

		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		if($pConfig->ccavenue_currency == 'USD')
		{
			$post_variables = array(
				//// Merchant details
			    'Merchant_Id' => $merchantId,
				'Amount' => $amount,
				'Order_Id' => $order_id,
				'Redirect_Url' => $Redirect_Url,
				'Currency' => $currency,
				'TxnType' => 'A',
				'actionID' => 'txn',
			
			    //// Customer details
			    'billing_cust_name' => substr( $cust_name, 0, 50 ),
				'billing_cust_address' => substr( $cust_addr, 0, 500 ),
				'billing_cust_country' => $billinginfo->country_2_code,
				'billing_cust_state' => $billinginfo->state,
				'billing_cust_city' => $billinginfo->city,
				'billing_zip_code' => $billinginfo->postcode,
				'billing_cust_tel' => $billinginfo->telephone,
				'billing_cust_email' => $billinginfo->email,
			
				////Shipping details
				//'delivery_cust_name' => substr( $cust_name, 0, 50 ),
				//'delivery_cust_address' => substr( $cust_addr, 0, 500 ),
				//'delivery_cust_tel' => $billinginfo->telephone,
	
			    //// Item details
			    'billing_cust_notes' => $description,
				'Checksum' => $Checksum
			);
		}else{
			$post_variables = array(
				//// Merchant details
			    'Merchant_Id' => $merchantId,
				'Amount' => $amount,
				'Order_Id' => $order_id,
				'Redirect_Url' => $Redirect_Url,
	
			    //// Customer details
			    'billing_cust_name' => substr( $cust_name, 0, 50 ),
				'billing_cust_address' => substr( $cust_addr, 0, 500 ),
				'billing_cust_country' => $billinginfo->country,
				'billing_cust_state' => $billinginfo->state,
				'billing_cust_city' => $billinginfo->city,
				'billing_zip_code' => $billinginfo->postcode,
				'billing_cust_tel' => $billinginfo->telephone,
				'billing_cust_email' => $billinginfo->email,
			
				////Shipping details
				//'delivery_cust_name' => substr( $cust_name, 0, 50 ),
				//'delivery_cust_address' => substr( $cust_addr, 0, 500 ),
				//'delivery_cust_tel' => $billinginfo->telephone,
	
			    //// Item details
			    'billing_cust_notes' => $description,
				'Checksum' => $Checksum
			);
		}
		

		$html['form'] .= '<input type="image" id="ccavenue_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with CCAvenue').'" />';
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		return $html;
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
        $title = $msc_name.' : '.$title;
        return $title;
	}

	function getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
  	{
  		$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
	  	$adler = 1;
	 	$adler = $this->adler32($adler,$str);
	  	return $adler;
  	}
  	
	function getchecksumAll($MerchantId,$OrderId,$Amount ,$WorkingKey,$currencyType,$redirectURL)
	{
		$str ="$MerchantId|$OrderId|$Amount|$WorkingKey|$currencyType|$redirectURL";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		return $adler;
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
?>