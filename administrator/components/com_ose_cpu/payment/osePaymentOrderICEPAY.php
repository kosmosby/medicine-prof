<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderICEPAY extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function ICEPAYOneOffPay($orderInfo,$params=array()) 
	{
		require_once(OSEMSC_B_LIB.DS.'icepayAPI'.DS.'icepay.php');
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchant_id = $pConfig->icepay_merchant_id;
		$secret_code = $pConfig->icepay_secret_code;
		$icepay_country = $pConfig->icepay_country;
		$icepay_lang = $pConfig->icepay_lang;
		
		$db= oseDB :: instance();
		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');
		$billinginfo= $paymentOrder->getBillingInfo($orderInfo->user_id);
		if(empty($icepay_country))
		{
			$query = "SELECT country_2_code FROM `#__osemsc_country` WHERE `country_3_code` = '{$billinginfo->country }'";
			$db->setQuery($query);
			$icepay_country = $db->loadResult();
		}
		
		if(empty($icepay_lang))
		{
			$lang = &JFactory::getLanguage();
			$arr = explode("-",$lang->get('tag'));
			$icepay_lang = strtoupper($arr[0]);
		}
		//$orderInfo->payment_price = "19.99";
		$amount = intval(strval(100 * $orderInfo->payment_price));
		$Desc = $this->generateDesc($orderInfo->order_id);
		$currency = $orderInfo->payment_currency;
		
		$tran = new ICEPAY($merchant_id,$secret_code);
		
		$tran->SetOrderID($orderInfo->order_id);
		$tran->SetReference($orderInfo->order_number);
		//$tran->assignCountry('US');	
		//$tran->assignLanguage('EN');	
		//$tran->assignCurrency($currency);	
		//$tran->assignAmount($amount);	

		return $tran->Pay($icepay_country,$icepay_lang,$currency,$amount,$Desc);
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

	function sqlSafe($str){
		$str = addslashes($str);
		return $str;
	}
}
?>