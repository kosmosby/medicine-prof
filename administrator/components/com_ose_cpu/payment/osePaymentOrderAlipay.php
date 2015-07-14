<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderAlipay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function AlipayOneOffPay($orderInfo,$params=array()) 
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$alipay_partner = $pConfig->alipay_partner;
		$alipay_key = $pConfig->alipay_key;
   		$seller_email = $pConfig->alipay_seller_email;
   	   		  
		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);

		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');
		$billinginfo= $paymentOrder->getBillingInfo($orderInfo->user_id);

		$amount = $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		$desc = self::generateDesc($order_id);
		$user= & JFactory :: getUser($orderInfo->user_id);

		$orderInfoParams = oseJson::decode($orderInfo->params);
		//$cancelUrl = JURI :: base()."index.php";
		$notifyUrl = JURI :: base()."components/com_osemsc/ipn/alipay_notify.php";
		$returnUrl = urldecode($orderInfoParams->returnUrl);
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		
		$date = oseHTML :: getDateTime();
		
		$url = "https://mapi.alipay.com/gateway.do?";
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		$variables = array(
		    'service' => 'create_direct_pay_by_user',
			'payment_type' => 1,
			'partner' => $alipay_partner,
			'seller_email' => $seller_email,
			'_input_charset' => 'utf-8',
			'notify_url' => $notifyUrl,
			'return_url' => $returnUrl,
			'out_trade_no' => $order_number,
		   	'subject' => $desc,
			'body' => $desc,
			'total_fee' => $amount,
			'sign_type' => 'MD5'
		);
		
		$post_variables = $this->buildRequestPara($variables,$alipay_key);
		//print_r($post_variables);exit;
		$html['form'] .= '<input type="image" id="alipay_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with Alipay').'" />';
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

	function buildRequestPara($para_temp,$key) {

		$para_filter = $this->paraFilter($para_temp);

		$para_sort = $this->argSort($para_filter);

		$mysign = $this->buildMysign($para_sort, $key, 'MD5');

		$para_sort['sign'] = $mysign;
	
		return $para_sort;
	}
	
	function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	
	function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	
	function buildMysign($sort_para,$key,$sign_type = "MD5") {

		$prestr = $this->createLinkstring($sort_para);

		$prestr = $prestr.$key;

		$mysgin = md5($prestr);
		return $mysgin;
	}
	
	function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}

		$arg = substr($arg,0,count($arg)-2);
		
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
}
?>