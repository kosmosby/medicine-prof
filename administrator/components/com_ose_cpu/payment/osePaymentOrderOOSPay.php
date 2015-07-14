<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderOOSPay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function OOSPayOneOffPay($orderInfo,$params=array()) 
	{

        $pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$TerminalID = $pConfig->oospay_terminal_id;
		$TerminalID_ = "0".$pConfig->oospay_terminal_id;
		$MerchantID = $pConfig->oospay_merchant_id;
		$StoreKey = $pConfig->oospay_store_key;
		$ProvisionPassword = $pConfig->oospay_provision_password;
		$oospay_lang = $pConfig->oospay_lang;
		
		if(empty($oospay_lang))
		{
			$lang = &JFactory::getLanguage();
			$arr = explode("-",$lang->get('tag'));
			$oospay_lang = strtoupper($arr[0]);
		}
		
		$timestamp = time();
		$Mode = empty($pConfig->oospay_testmode)?'PROD':'TEST';
		$ApiVersion = "v0.01";
//		$TerminalProvUserID = "PROVAUT";
		$TerminalProvUserID = "PROVOOS";
		$Type = "sales";
		$Amount = intval(strval(100 * $orderInfo->payment_price));
		$CurrencyCode = $this->transCurrency($orderInfo->payment_currency);
		if(empty($CurrencyCode))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = 'Currency Error';
			return $result;
		}
		
		
		$InstallmentCount = "";

		$db = JFactory::getDBO();
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
        $cart = oseMscPublic::getCart();



        if(isset($cart->cart['params']['garanti_taksit']) && $cart->cart['params']['garanti_taksit'] && isset($cart->cart['params']['garanti_vade']) && $cart->cart['params']['garanti_vade']) {
            $InstallmentCount = $cart->cart['params']['garanti_taksit'];
            $vade = $cart->cart['params']['garanti_vade'];


            //$Amount = $cart->cart['total']*100;
            $Amount = $Amount * $vade/100 + $Amount;

            $Amount = round($Amount);

            //echo $Amount; die;
        }
        //end garanti settings

		$TerminalUserID = $orderInfo->user_id;
		$OrderID = $orderInfo->order_id;
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$companyname = $billinginfo->company;
		$customeremailaddress =  $billinginfo->email;
		$Customeripaddress = oseMscPublic::getIP();
		
		$SuccessURL = JURI :: base()."components/com_osemsc/ipn/oospay_notify.php";
		$ErrorURL = JURI :: base()."components/com_osemsc/ipn/oospay_notify.php";
		
		$SecurityData = strtoupper(sha1($ProvisionPassword.$TerminalID_));
		$HashData = strtoupper(sha1($TerminalID.$OrderID.$Amount.$SuccessURL.$ErrorURL.$Type.$InstallmentCount.$StoreKey.$SecurityData));
		
		
		if($pConfig->oospay_testmode)
		{
			$url = "https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine";
		//	$url = "https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine";
		//	$url = "http://www.garantipos.com.tr/Admin/post.asp";
		}else{
			$url = "https://sanalposprov.garanti.com.tr/servlet/gt3dengine";
		}
		
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		$post_variables = array(
		
			'secure3dsecuritylevel' =>'3D_OOS_FULL', //Added by Kemal
//			'secure3dsecuritylevel' =>'3D_OOS_PAY', //Added by Hakan
//			'secure3dsecuritylevel' =>'OOS_PAY', //Added by Hakan
			'refreshtime' =>'5',
			'mode' => $Mode,
			'apiversion' => $ApiVersion,
			'terminalprovuserid' => $TerminalProvUserID,
			'terminaluserid' => $TerminalUserID,
			'terminalid' => $TerminalID,
			'terminalmerchantid' => $MerchantID,
			'orderid' => $OrderID,
			'customeremailaddress' => $customeremailaddress,
			'customeripaddress' => $Customeripaddress,
			'txntype' => $Type,
			'txnamount' => $Amount,
			'txncurrencycode' => $CurrencyCode,
//			'companyname' => $companyname,
			'companyname' => 'Ava Danismanlik Hizmetleri (www.kaliteegitimleri.com)',
			'txninstallmentcount' => $InstallmentCount,
			'successurl' => $SuccessURL,
			'errorurl' => $ErrorURL,
			'secure3dhash' => $HashData,
			'lang' => $oospay_lang,
			'txntimestamp' => $timestamp

			//billing info detail
///			'orderaddresscount' => 1,
///			'orderaddresscity1' => $billinginfo->city,
///			'orderaddresscompany1' => $companyname,
///			'orderaddresscountry1' => $billinginfo->country,
///			'orderaddressdistrict1' => $billinginfo->state,
			//'orderaddressfaxnumber1' => '',
			//'orderaddressgsmnumber1' => $billinginfo->telephone,
///			'orderaddresslastname1' => $billinginfo->lastname,
///			'orderaddressname1' => $billinginfo->firstname,
///			'orderaddressphonenumber1' => $billinginfo->telephone,
///			'orderaddresspostalcode1' => $billinginfo->postcode
			
		);
		if(!empty($InstallmentCount))
		{
			$post_variables['txninstallmentcount'] = $InstallmentCount;
		}
		//print_r($post_variables);exit;
		$html['form'] .= '<input type="image" id="oospay_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with OOSPay').'" />';
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

	function transCurrency($code)
	{
		$currency = array(
			'JPY' => '392',
			'TL' => '949',
			'USD' => '840',
			'EUR' => '978',
			'GBP' => '826'
		);
		
		foreach($currency as $key => $value)
		{
			if($code == $key)
			{
				return $value;
			}
		}
		
		return false;
	}

}
?>