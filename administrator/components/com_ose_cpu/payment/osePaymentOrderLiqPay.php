<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderLiqPay extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function LiqPayOneOffPay($orderInfo,$params=array()) 
	{
		$config = oseMscConfig :: getConfig('', 'obj');
		$merchant_id = $config->liqpay_merchant_id;	
		$signature = $config->liqpay_signature;
		$method = $config->liqpay_payment;
		$phone = $config->liqpay_phone;
		
		$orderId= $orderInfo->order_id;
		$amount = $orderInfo->payment_price;
		$currency = $orderInfo->payment_currency;
		$notify_url = JURI :: base()."components/com_osemsc/ipn/liqpay_notify.php"; 
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$returnUrl = urldecode($orderInfoParams->returnUrl);
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$url="https://www.liqpay.com/?do=clickNbuy";
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		$desc = self::generateDesc($orderId,$orderInfo->user_id,$billinginfo);
		$user = JFactory::getUser($orderInfo->user_id);
		
		$xml="<request>      
			<version>1.2</version>
			<result_url>$notify_url</result_url>
			<server_url>$notify_url</server_url>
			<merchant_id>$merchant_id</merchant_id>
			<order_id>$orderId</order_id>
			<amount>$amount</amount>
			<currency>$currency</currency>
			<description>$desc</description>
			<default_phone>$phone</default_phone>
			<pay_way>$method</pay_way> 
			</request>
		";
		
		$xml_encoded = base64_encode($xml); 
		$lqsignature = base64_encode(sha1($signature.$xml.$signature,1));
	
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		$post_variables = array(
		
			'operation_xml' =>$xml_encoded,
			'signature' => $lqsignature
			
		);
		//print_r($post_variables);exit;
		$html['form'] .= '<input type="image" id="liqpay_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with LiqPay').'" />';
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		return $html;
	}

	
	function generateDesc($order_id,$user_id=null,$billinginfo=null)
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
		if(!empty($user_id))
		{
			$user = JFactory::getUser($user_id);
			$title.= ' - Username : '.$user->username;
		}
        if(!empty($billinginfo))
        {
        	$title.= ' - Name : '.$billinginfo->firstname.' '.$billinginfo->lastname;
        }
        return $title;
	}

}
?>