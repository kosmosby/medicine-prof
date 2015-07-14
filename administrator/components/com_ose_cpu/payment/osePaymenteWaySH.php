<?php
defined('_JEXEC') or die(";)");

class osePaymentOrdereWaySH extends osePaymentGateWay
{
	protected $postVar= array();
	protected $ccInfo = array();
	protected $orderInfo = null;

	function __construct()
	{
		parent::__construct();

	}

	function eWaySharedHostingPostForm($orderInfo,$billingInfo) {
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$user =& JFactory::getUser();
		
		if($pConfig->ewaysh_testmode)
		{
			$CustomerID = '87654321';
			$UserName = 'TestAccount';
		}else{
			$CustomerID = $pConfig->ewaysh_customer_id;
			$UserName = $pConfig->ewaysh_username;
		}
		
		$order_id = $orderInfo->order_id;
		$desc = self::generateDesc($order_id);
		
		//$session =& JFactory::getSession();
		//$session->set( 'oseOrder_id', $order_id);
		$ewayCustomerID = $CustomerID;
		$ewayUserName = $UserName;
		$ewayAmount = number_format($orderInfo->payment_price, 2, '.', ' ');
		//$ewayAmount = "100".".00";
		$ewayCurrency = $orderInfo->payment_currency;
		$ewayPageTitle = $pConfig->ewaysh_pagetitle;
		$ewayPageDesc = $pConfig->ewaysh_pagetitle;
		$ewayReturnUrl = JURI :: base()."components/com_osemsc/ipn/ewaysh_notify.php";
		$ewayCancelURL = JURI :: base()."index.php";
		$ewayLanguage = empty($pConfig->ewaysh_language)?'EN':$pConfig->ewaysh_language;
		$ewayModified = $pConfig->ewaysh_mcd;
		$ewayFirstName = $billingInfo->firstname;
		$ewayLastName = $billingInfo->lastname;
		$ewayZipcode = $billingInfo->postcode;
		//$ewayCountry = $billingInfo->country;
		$ewayEmail = $billingInfo->email;
		$ewayLogourl = $pConfig->ewaysh_logourl;
		$ewayBanner= $pConfig->ewaysh_banner;
		$ewayCompanyName= $pConfig->ewaysh_companyname;
		$ewayfooter= $pConfig->ewaysh_pagefooter;
		$ewayCustomerAddress = $billingInfo->addr1."&nbsp;".$billingInfo->addr1;
		$ewayCustomerPostCode = $billingInfo->postcode;
		$ewayCustomerCountry = $billingInfo->country;
		$ewayCustomerCity = $billingInfo->city;
		$ewayCustomerState = $billingInfo->state;
		$ewayCustomerPhone = $billingInfo->telephone;
		#$ewayCustomerAddress= $row['address_1'];
		$ewayurl = null;
		$ewayurl.="?CustomerID=".$ewayCustomerID;
		$ewayurl.="&UserName=".$ewayUserName;
		$ewayurl.="&Amount=".$ewayAmount;
		$ewayurl.="&Currency=".$ewayCurrency;
		$ewayurl.="&PageTitle=".$ewayPageTitle;
	    $ewayurl.="&PageDescription=".$ewayPageDesc;
		$ewayurl.="&PageFooter=".$ewayfooter;
		$ewayurl.="&Language=".$ewayLanguage;
		$ewayurl.="&CompanyName=".$ewayCompanyName;
		$ewayurl.="&CustomerFirstName=".$ewayFirstName;
	    $ewayurl.="&CustomerLastName=".$ewayLastName;
		$ewayurl.="&CustomerAddress=".$ewayCustomerAddress;
		$ewayurl.="&CustomerCity=".$ewayCustomerCity;
		$ewayurl.="&CustomerState=".$ewayCustomerState;
		$ewayurl.="&CustomerPostCode=".$ewayZipcode;
		$ewayurl.="&CustomerCountry=".$ewayCustomerCountry;
		$ewayurl.="&CustomerEmail=".$ewayEmail;
		$ewayurl.="&CustomerPhone=".$ewayCustomerPhone;
		$ewayurl.="&InvoiceDescription=".$desc;
		$ewayurl.="&CancelURL=".$ewayCancelURL;
		$ewayurl.="&ReturnUrl=".$ewayReturnUrl;
		$ewayurl.="&CompanyLogo=".$ewayLogourl;
		$ewayurl.="&PageBanner=".$ewayBanner;
		#$ewayurl.="&MerchantReference=".$new_order_id;
		$ewayurl.="&MerchantReference=".$order_id;

		#$ewayurl.="&MerchantOption1=";
		#$ewayurl.="&MerchantOption2=";
		#$ewayurl.="&MerchantOption3=";
		$ewayurl.="&ModifiableCustomerDetails=".$ewayModified;
		/*$ewayurl.="?CustomerID=87654321&UserName=TestAccount&Amount=10.20&Currency=NZD&PageTitle=Webpage Title&PageDescription=Customised Page Description - Add a unique custom message for the customer here.&PageFooter=Customised Page Footer - Add a unique footer useful for contact information.&Language=EN&CompanyName=Merchant Company Name&CustomerFirstName=John&CustomerLastName=Doe&CustomerAddress=123 ABC Street&CustomerCity=Auckland&CustomerState=State&CustomerPostCode=1010&CustomerCountry=New Zealand&CustomerEmail=sample@eway.co.nz&CustomerPhone=0800 896 210&InvoiceDescription=Individual Invoice Description&CancelURL=http://www.yoursite.com/MerchantResponse.aspx&ReturnUrl=http://www.yoursite.com/MerchantResponse.aspx&MerchantReference=513456&MerchantInvoice=Inv 21540&MerchantOption1=Option1&MerchantOption2=Option2&MerchantOption3=Option3&ModifiableCustomerDetails=false";
		$spacereplace = str_replace(" ", "%20", $ewayurl);	*/
		$spacereplace = str_replace(" ", "%20", $ewayurl);

	    $posturl="https://nz.ewaygateway.com/Request/$spacereplace";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if (CURL_PROXY_REQUIRED == 'True')
		{
			$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		}
		
		$response = curl_exec($ch);
		#print_r($response);
		$responsemode = $this->fetch_data($response, '<result>', '</result>');
	    $responseurl = $this->fetch_data($response, '<uri>', '</uri>');
	    
		if($responsemode=="True")
		{
		 	return $responseurl;
		}
		else
		{
			 return JURI :: base()."index.php";
		}
	}

	function fetch_data($string, $start_tag, $end_tag)
	{

		$position = stripos($string, $start_tag);

		$str = substr($string, $position);

		$str_second = substr($str, strlen($start_tag));

		$second_positon = stripos($str_second, $end_tag);

		$str_third = substr($str_second, 0, $second_positon);

		$fetch_data = trim($str_third);

		return $fetch_data;
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