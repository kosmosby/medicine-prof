<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderUSAePay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function USAePayOneOffPay($orderInfo,$creditInfo,$params=array()) 
	{
		require_once(OSEMSC_B_LIB.DS.'usaepay.php');
		$tran = new umTransaction;
		
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$source_key = $pConfig->usaepay_source_key;
		$testmode = $pConfig->usaepay_testmode;
		$sendReceipt = empty($pConfig->usaepay_sendReceipt)?'no':'yes';
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$redirectUrl = urldecode($orderInfoParams->returnUrl);
		$redirectUrl = $redirectUrl?$redirectUrl:JURI :: base()."index.php?option=com_osemsc&view=member";
			
		$tran->key = $source_key;
		$tran->testmode = $testmode;
		$tran->usesandbox =  $pConfig->usaepay_usesandbox;
		$tran->custreceipt = $sendReceipt;
		
		//Credit Card Info
		$tran->cardholder = $creditInfo['creditcard_name'];
		$tran->card = $creditInfo['creditcard_number'];
		$tran->cvv2 = $creditInfo['creditcard_cvv'];
		$tran->exp = $creditInfo['creditcard_month'].substr($creditInfo['creditcard_year'],2,2);
		$tran->street = $billinginfo->addr1.' '.$billinginfo->addr2;
		$tran->zip = $billinginfo->postcode;
		
		//Item Info
		$tran->currency = $this->transCurrency($orderInfo->payment_currency);// international currency, full list of codes: http://wiki.usaepay.com/developer/currencycode
		
		if(empty($tran->currency))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = 'Currency Error';
			return $result;
		}
		
		$tran->amount = $orderInfo->payment_price;
		$tran->invoice = $orderInfo->order_number;
		$tran->description = self::generateDesc($orderInfo->order_id);
		$tran->command = 'sale';
		
		
		//billing Info
		$tran->billfname = $billinginfo->firstname;
		$tran->billlname = $billinginfo->lastname;
		$tran->billcompany = $billinginfo->company;
		$tran->billstreet = $billinginfo->addr1;
		$tran->billstreet2 = $billinginfo->addr2;
		$tran->billcity = $billinginfo->city;
		$tran->billstate = $billinginfo->state;
		$tran->billzip = $billinginfo->postcode;
		$tran->billcountry = $billinginfo->country;
		$tran->billphone = $billinginfo->telephone;
		$tran->email = $billinginfo->email;
		
		$result = array();
		if($tran->Process())
		{
			
			$paymentOrder->confirmOrder($orderInfo->order_id,array());
			$result['success'] = true;
			$result['title'] = JText :: _('SUCCESSFUL_ACTIVATION');
			$result['content'] = JText :: _('MEMBERSHIP_ACTIVATED_CONTINUE');
			$result['returnUrl'] = $redirectUrl;
		}else{
			$result['success'] = false;
			$result['title'] = $tran->result;
			$result['content'] = $tran->error;
			$result['returnUrl'] = JURI :: base()."index.php?option=com_osemsc&view=register";
		}
		
		return $result;
	}

	function USAePayCreateProfile($orderInfo,$creditInfo,$params=array())
	{
		
		require_once(OSEMSC_B_LIB.DS.'usaepay.php');
		$tran = new umTransaction;
		
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$source_key = $pConfig->usaepay_source_key;
		$testmode = $pConfig->usaepay_testmode;
		$sendReceipt = empty($pConfig->usaepay_sendReceipt)?'no':'yes';
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$redirectUrl = urldecode($orderInfoParams->returnUrl);
		$redirectUrl = $redirectUrl?$redirectUrl:JURI :: base()."index.php?option=com_osemsc&view=member";
			
		$tran->key = $source_key;
		$tran->testmode = $testmode;
		$tran->usesandbox =  $pConfig->usaepay_usesandbox;
		$tran->custreceipt = $sendReceipt;
		
		//Credit Card Info
		$tran->cardholder = $creditInfo['creditcard_name'];
		$tran->card = $creditInfo['creditcard_number'];
		$tran->cvv2 = $creditInfo['creditcard_cvv'];
		$tran->exp = $creditInfo['creditcard_month'].substr($creditInfo['creditcard_year'],2,2);
		$tran->street = $billinginfo->addr1.' '.$billinginfo->addr2;
		$tran->zip = $billinginfo->postcode;
		
		//Item Info
		$tran->currency = $this->transCurrency($orderInfo->payment_currency);// international currency, full list of codes: http://wiki.usaepay.com/developer/currencycode
		
		if(empty($tran->currency))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = 'Currency Error';
			return $result;
		}
		
		$tran->amount = $orderInfo->payment_price;
		$tran->billamount = $orderInfoParams->next_total;
		$tran->invoice = $orderInfo->order_number;
		$tran->description = self::generateDesc($orderInfo->order_id);
		$tran->command = 'sale';
		$tran->addcustomer = 'yes';
		$tran->schedule = $this->transUnit($orderInfoParams->t3);
		//billing Info
		$tran->billfname = $billinginfo->firstname;
		$tran->billlname = $billinginfo->lastname;
		$tran->billcompany = $billinginfo->company;
		$tran->billstreet = $billinginfo->addr1;
		$tran->billstreet2 = $billinginfo->addr2;
		$tran->billcity = $billinginfo->city;
		$tran->billstate = $billinginfo->state;
		$tran->billzip = $billinginfo->postcode;
		$tran->billcountry = $billinginfo->country;
		$tran->billphone = $billinginfo->telephone;
		$tran->email = $billinginfo->email;
		
		$result = array();
		if($tran->Process())
		{
			
			$paymentOrder->confirmOrder($orderInfo->order_id,array());
			$result['success'] = true;
			$result['title'] = JText :: _('SUCCESSFUL_ACTIVATION');
			$result['content'] = JText :: _('MEMBERSHIP_ACTIVATED_CONTINUE');
			$result['returnUrl'] = $redirectUrl;
		}else{
			$result['success'] = false;
			$result['title'] = $tran->result;
			$result['content'] = $tran->error;
			$result['returnUrl'] = JURI :: base()."index.php?option=com_osemsc&view=register";
		}
		
		return $result;
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
			'AFA' => '971',
			'AWG' => '533',
			'AUD' => '036',
			'ARS' => '032',
			'AZN' => '944',
			
			'BSD' => '044',
			'BDT' => '050',
			'BBD' => '052',
			'BYR' => '974',
			'BOB' => '068',
			
			'BRL' => '986',
			'GBP' => '826',
			'BGN' => '975',
			'KHR' => '116',
			'CAD' => '124',
			
			'KYD' => '136',
			'CLP' => '152',
			'CNY' => '156',
			'COP' => '170',
			'CRC' => '188',
			
			'HRK' => '191',
			'CPY' => '196',
			'CZK' => '203',
			'DKK' => '208',
			'DOP' => '214',
			
			'XCD' => '951',
			'EGP' => '818',
			'ERN' => '232',
			'EEK' => '233',
			'EUR' => '978',
			
			'GEL' => '981',
			'GHC' => '288',
			'GIP' => '292',
			'GTQ' => '320',
			'HNL' => '340',
			
			'HKD' => '344',
			'HUF' => '348',
			'ISK' => '352',
			'INR' => '356',
			'IDR' => '360',
			
			'ILS' => '376',
			'JMD' => '388',
			'JPY' => '392',
			'KZT' => '368',
			'KES' => '404',
			
			'KWD' => '414',
			'LVL' => '428',
			'LBP' => '422',
			'LTL' => '440',
			'MOP' => '446',
			
			'MKD' => '807',
			'MGA' => '969',
			'MYR' => '458',
			'MTL' => '470',
			'BAM' => '977',
			
			'MUR' => '480',
			'MXN' => '484',
			'MZM' => '508',
			'NPR' => '524',
			'ANG' => '532',
			
			'TWD' => '901',
			'NZD' => '554',
			'NIO' => '558',
			'NGN' => '566',
			'KPW' => '408',
			
			'NOK' => '578',
			'OMR' => '512',
			'PKR' => '586',
			'PYG' => '600',
			'PEN' => '604',
			
			'PHP' => '608',
			'QAR' => '634',
			'RON' => '946',
			'RUB' => '643',
			'SAR' => '682',
			
			'CSD' => '891',
			'SCR' => '690',
			'SGD' => '702',
			'SKK' => '703',
			'SIT' => '705',
			
			'ZAR' => '710',
			'KRW' => '410',
			'LKR' => '144',
			'SRD' => '968',
			'SEK' => '752',
			
			'CHF' => '756',
			'TZS' => '834',
			'THB' => '764',
			'TTD' => '780',
			'TRY' => '949',
			
			'AED' => '784',
			'USD' => '840',
			'UGX' => '800',
			'UAH' => '980',
			'UYU' => '858',
			
			'UZS' => '860',
			'VEB' => '862',
			'VND' => '704',
			'AMK' => '894',
			'ZWD' => '716'
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
	
	function transUnit($unit)
	{
		switch($unit)
		{
			case('day'):
				return 'daily';
			break;

			case('week'):
				return 'weekly';
			break;
			
			case('month'):
				return 'monthly';
			break;
		}
	}
}
?>