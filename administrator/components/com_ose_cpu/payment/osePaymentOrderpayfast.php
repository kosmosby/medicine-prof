<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderPayFast extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function PayFastOneOffPostForm($orderInfo,$params=array()) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchantId = $pConfig->payfast_merchant_id;
		$merchantKey = $pConfig->payfast_merchant_key;

		$html= array();
		$test_mode= $pConfig->payfast_testmode;
		if(!$test_mode)
		{
			if(empty($merchantId) || empty($merchantKey)) {
				$html['form']= "";
				$html['url']= "";
				return $html;
			}
		}

		if($test_mode == true)
		{
			$merchantId = '10000100';
	    	$merchantKey = '46f0cd694581a';
			$url= "https://sandbox.payfast.co.za/eng/process";
		} else {
			$url= "https://www.payfast.co.za/eng/process";
		}

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);

		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');
		$billinginfo= $paymentOrder->getBillingInfo($orderInfo->user_id);

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;

		$user= & JFactory :: getUser($orderInfo->user_id);

		$orderInfoParams = oseJson::decode($orderInfo->params);
		$cancelUrl = JURI :: base()."index.php";
		$notifyUrl = JURI :: base()."components/com_osemsc/ipn/payfast_notify.php";
		$returnUrl = urldecode(JROUTE::_(JURI :: base()."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id));
		$returnUrl = $returnUrl?$returnUrl:JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";

		// Create description
		$description = '';

		$vendor_image_url= "";
		$app= & JFactory :: getApplication();
		$currentSession= JSession :: getInstance('none', array());
		$stores= $currentSession->getStores();
		$html['form']= '<form action="'.$url.'" method="post">';

		// Construct variables for post
		$post_variables = array(
			//// Merchant details
		    'merchant_id' => $merchantId,
			'merchant_key' => $merchantKey,
			'return_url' => $returnUrl,
			'cancel_url' => $cancelUrl,
			'notify_url' => $notifyUrl,

		    //// Customer details
		    'name_first' => substr( $billinginfo->firstname, 0, 100 ),
			'name_last' => substr( $billinginfo->lastname, 0, 100 ),
			'email_address' => substr( $billinginfo->email, 0, 255 ),

		    //// Item details
		    'item_name' => JText::_('Order ID: ') . $order_id,
			'item_description' => $description,
			'amount' => number_format( $amount, 2, '.', '' ),
			'm_payment_id' => $order_id,
			'currency_code' => $currency,
			'custom_str1' => $order_number,

		    // Other details
		    'user_agent' => 'Open Source Membership Control V5',
		);

		$html['form'] .= '<input type="image" id="payfast_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayFast').'" />';
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		return $html;
	}


}
?>