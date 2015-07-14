<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die(";)");

class osePaymentOrderRealex extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function RealexOneOffPay($orderInfo,$creditInfo,$params=array()) 
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$realex_mode = $pConfig->realex_mode;
		switch($realex_mode)
		{
			case('redirect'):
				return self::RealexRedirectPay($orderInfo);
				break;
			case('remote'):
				return self::RealexRemotePay($orderInfo,$creditInfo);
				break;	
		}
	}

	function RealexRedirectPay($orderInfo)
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchantid = $pConfig->realex_merchant_id;
		$secret = $pConfig->realex_secret;
		$account = $pConfig->realex_account;
		
		$timestamp = strftime("%Y%m%d%H%M%S");
		mt_srand((double)microtime()*1000000);
		$orderid = $orderInfo->order_id;
		$currency = $orderInfo->payment_currency;
		$amount = $orderInfo->payment_price * 100;
		$amount = number_format($amount, 0, '.', '');
		$user = JFactory::getUser($orderInfo->user_id);
		$PAYER_REF = $user->id;
		$PMT_REF = $user->id."_card";
		$PMT_REF = "mycard1";
		
		$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency";
		$md5hash = md5($tmp);
		$tmp = "$md5hash.$secret";
		$md5hash = md5($tmp);
		/*
		$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$PAYER_REF.$PMT_REF";
		$md5hash = sha1($tmp);
		$tmp = "$md5hash.$secret";
		$sha1hash = sha1($tmp);
		*/
		$autosettle = $pConfig->realex_type;
		
		$desc = self::generateDesc($orderInfo->order_id);
		
		$url = "https://epage.payandshop.com/epage.cgi";
		$html['form']= '<form id="realex_redirect_form" action="'.$url.'" method="post">';

		// Construct variables for post
		$post_variables = array(
		
			'MERCHANT_ID' =>$merchantid,
			'ORDER_ID' => $orderid,
			'ACCOUNT' => $account,
			'CURRENCY' => $currency,
			'AMOUNT' => $amount,
			'TIMESTAMP' => $timestamp,
			'MD5HASH' => $md5hash,
			//'SHA1HASH"' => $sha1hash,
			'AUTO_SETTLE_FLAG' => $autosettle,
			'COMMENT2' => $desc,
			'OFFER_SAVE_CARD' => 1,
			'PAYER_REF' => $PAYER_REF,
			'PMT_REF' => $PMT_REF,
			'PAYER_EXIST' => 0
		);
		//print_r($post_variables);exit;
		$html['form'] .= '<input type="image" id="realex_image" name="cartImage" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with Realex').'" />';
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		$result= array();
		$result['success']= true;
		$result['html']= $html;
		$result['payment_method']= 'realex_redirect';
		return $result;
	}
	
	function RealexRemotePay($orderInfo,$creditInfo)
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$merchantid = $pConfig->realex_merchant_id;
		$secret = $pConfig->realex_secret;
		$account = $pConfig->realex_account;
		
		$cardnumber = $creditInfo['creditcard_number'];
		$expdate = sprintf('%02d%02d', $creditInfo['creditcard_month'], substr($creditInfo['creditcard_year'], 2));
		$cardname = $creditInfo['creditcard_name'];
		$cardtype = $creditInfo['creditcard_type'];
		$issueno = '';
		$cvc = $creditInfo['creditcard_cvv'];	
		$timestamp = strftime("%Y%m%d%H%M%S");
		if($cardtype == 'amex')
		{
			$account = $pConfig->realex_amex_account;
		}

		$orderid = $orderInfo->order_number;
		$currency = $orderInfo->payment_currency;
		$amount = $orderInfo->payment_price * 100;
		$autosettle = $pConfig->realex_type;
		$customerID = $orderInfo->user_id;
		
		$db = oseDB::instance();
		$query = "SELECT entry_id FROM `#__osemsc_order_item` WHERE `order_id` = ". $orderInfo->order_id;
		$db->setQuery($query);
        $msc_id = $db->loadResult();
        $payment= oseRegistry :: call('payment');
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$productID = $msc_id;
		$billingCountry = $billinginfo->country;
		$shippingPostcode = $billinginfo->postcode;
		$shippingCountry = $billinginfo->country;
		$billingPostcode = substr($billinginfo->postcode, 0, 20);
		$billingPostcodeNumbers = ereg_replace("[^0-9]", "", $billingPostcode);
		$billingStreetName = substr($billinginfo->addr1.' '.$billinginfo->addr2, 0, 60);
		preg_match('{(\d+)}', $billingStreetName, $m);
		if(isset($m[1])){
			$billingStreetNumber = $m[1];
		}else{
			$billingStreetNumber = '';
		}
		$billingCode = $billingPostcodeNumbers . '|' . $billingStreetNumber;
		
		$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $request = "<request timestamp='$timestamp' type='auth' >
                        <merchantid>$merchantid</merchantid>
						<account>$account</account>
                        <orderid>$orderid</orderid>
                        <amount currency='$currency'>$amount</amount>
                        <card> 
                            <number>$cardnumber</number>
                            <expdate>$expdate</expdate>
                            <chname>$cardname</chname> 
                            <type>$cardtype</type> 
                            <issueno>$issueno</issueno>
                        </card>
                        <cvn>
                            <number>$cvc</number>
                            <presind>1</presind>
                        </cvn>
                        <autosettle flag='$autosettle'/>
						<tssinfo>
						    <custnum>$customerID</custnum>
				    		<prodid>$productID</prodid>
					    	<address type='billing'> 
								<code>$billingCode</code>  
								<country>$billingCountry</country>  
						    </address> 
						    <address type='shipping'> 
								<code>$shippingPostcode</code>  
								<country>$shippingCountry</country>  
						    </address>
						</tssinfo>
                        <sha1hash>$sha1hash</sha1hash>
					</request>";
					
		//echo($request);
		$url = "https://epage.payandshop.com/epage-remote.cgi";
		
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // this line makes it work under https
		$response = curl_exec ($ch);     
		curl_close ($ch); 
		
		$xml = simplexml_load_string($response);
		$result = array();
		$result['payment_method']= 'realex_remote';
		if($xml->result == '00')
		{
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			$redirectUrl = urldecode($orderInfoParams->returnUrl);
			$redirectUrl = $redirectUrl?$redirectUrl:JURI :: base()."index.php?option=com_osemsc&view=member";
			$paymentOrder->confirmOrder($orderInfo->order_id,array());
			$result['success'] = true;
			$result['title'] = JText :: _('SUCCESSFUL_ACTIVATION');
			$result['content'] = JText :: _('MEMBERSHIP_ACTIVATED_CONTINUE');
			$result['returnUrl'] = $redirectUrl;
			
		}else
		{
			$result['success'] = false;
			$result['title'] = 'Declined';
			$result['content'] = (string)$xml->message;
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

	
}
?>