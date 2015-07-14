<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderQuickpay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function QuickpayOneOffPay($orderInfo,$params=array()) 
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchant = $pConfig->quickpay_merchant;
		$secret = $pConfig->quickpay_secret;
   		$test_mode = $pConfig->quickpay_testmode;
   		$cardtypelock = $pConfig->quickpay_cardtypelock;
   		$autocapture = $pConfig->quickpay_autocapture;
   		$autofee = $pConfig->quickpay_autofee;
   		$lang = empty($pConfig->quickpay_lang)?'en':$pConfig->quickpay_lang;
   		  
		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);

		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');
		$billinginfo= $paymentOrder->getBillingInfo($orderInfo->user_id);

		$amount = $orderInfo->payment_price * 100;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$desc = self::generateDesc($order_id);
		$user= & JFactory :: getUser($orderInfo->user_id);

		$order_id = sprintf("%04d", $order_id);
		$orderInfoParams = oseJson::decode($orderInfo->params);
		//$cancelUrl = JURI :: base()."index.php";
		$notifyUrl = JURI :: base()."components/com_osemsc/ipn/quickpay_notify.php";
		$returnUrl = urldecode(JROUTE::_(JURI :: base()."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id));
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		
		$date = oseHTML :: getDateTime();
		
		$url = "https://secure.quickpay.dk/form/";
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		$post_variables = array(
		    'protocol' => 5,
			'msgtype' => 'authorize',
			'merchant' => $merchant,
			'language' => $lang,
			'ordernumber' => $order_id,
			'amount' => $amount,
		   	'currency' => $currency,
			'continueurl' => $returnUrl,
			'continueurl' => $returnUrl,
			'cancelurl' => $returnUrl,
			'callbackurl' => $notifyUrl,
			'autocapture' => $autocapture,
			'autofee' => $autofee,
			'cardtypelock' => $cardtypelock,
			'description' => $desc,
			'testmode' => $test_mode
		);
		
		$md5String = '';     
       	foreach ($post_variables as $name => $value) {
            $md5String .= $value;
    	}
    	$md5String .= $secret;
    	$post_variables['md5check'] = md5($md5String);
		//print_r($post_variables);exit;
		$html['form'] .= '<input type="image" id="quickpay_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with Quickpay').'" />';
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

	
}
?>