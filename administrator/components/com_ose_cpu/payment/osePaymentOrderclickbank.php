<?php
defined('_JEXEC') or die(";)");

class osePaymentOrderClickBank extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function ClickBankPostForm($orderInfo) 
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$db= oseDB :: instance();
		
		$query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = ".$orderInfo->order_id;
		$db->setQuery($query);
		$obj = $db->loadObject();
		$params = oseJson::decode($obj->params);
		$msc_option = $params->msc_option;
		$msc_id = $obj->entry_id;
		$query = "SELECT params FROM `#__osemsc_ext` WHERE `id` = ".$msc_id." AND `type` = 'paymentAdv'";
		$db->setQuery($query);
		$ext = $db->loadResult();
		$ext = oseJson::decode($ext);
		$item_number = empty($ext->$msc_option->clickbank_productid)?0:$ext->$msc_option->clickbank_productid;
		
		$test_mode= $pConfig->clickbank_testmode;
		$account = $pConfig->clickbank_account;
		$secret_key = $pConfig->clickbank_secret_key;
		
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billingInfo= $member->getBillingInfo('obj');
		
		// Construct variables for post
		$post_variables = array(
			'invoice' => $orderInfo->order_number,
			'name' => $billingInfo -> firstname . " " . $billingInfo->lastname,
			'email' => $billingInfo->user_email,
			'country' => $billingInfo->country,
			'zipcode' => $billingInfo->postcode
			//'detail' => self::generateDesc($order_id)
			
		);
		
		$url = 'http://'.$item_number.'.'.$account.'.pay.clickbank.net';
		$html= array();
		$html['form']= '<form action="'.$url.'" method="post">';
		$html['form'] .= '<input type="image" id="clickbank_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with ClickBank').'" />';
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