<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderPagSeguro extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function PagSeguroOneOffPay($orderInfo,$params=array()) 
	{
		require_once(OSEMSC_B_LIB.DS.'PagSeguroLibrary'.DS.'PagSeguroLibrary.php');
		
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$account = $pConfig->pagseguro_account;
		$token = $pConfig->pagseguro_token;
		
		$paymentRequest = new PaymentRequest();
		
		$payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		$address = $billinginfo->addr1.' '.$billinginfo->addr2;
		
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$redirectUrl = urldecode(JROUTE::_(JURI :: base()."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id));
		$redirectUrl = $redirectUrl?$redirectUrl:JURI :: root()."index.php?option=com_osemsc&view=member";
		$des = $this->generateDesc($orderInfo->order_id);
		// Sets the currency
		$paymentRequest->setCurrency($orderInfo->payment_currency);

		// Add an item for this payment request
		$paymentRequest->addItem($orderInfo->order_id, $des, 1, $orderInfo->payment_price);
		
		// Sets a reference code for this payment request, it is useful to identify this payment in future notifications.
		$paymentRequest->setReference($orderInfo->order_number);
		
		// Sets shipping information for this payment request
		$CODIGO_SEDEX = ShippingType::getCodeByType('SEDEX');
		$paymentRequest->setShippingType($CODIGO_SEDEX);
		$paymentRequest->setShippingAddress('',  $address,  $billinginfo->telephone, null, null, $billinginfo->city, $billinginfo->state, $billinginfo->country);
		
		// Sets your customer information.
		$paymentRequest->setSender($billinginfo->firstname.' '.$billinginfo->lastname, $billinginfo->email, null, null);
		
		$redirectUrl = str_replace('https://','',$redirectUrl);
		$redirectUrl = str_replace('http://','',$redirectUrl);
		$paymentRequest->setRedirectUrl($redirectUrl);
		
		$result = array();
		$result['payment_method']= 'pagseguro';
		try {
			
			$credentials = new AccountCredentials($account, $token);
		
			$url = $paymentRequest->register($credentials);
			$result['success'] = true;
			$result['url'] = $url;
			
		} catch (PagSeguroServiceException $e) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = $e->getMessage();
			//$result['url'] = '';
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

}
?>