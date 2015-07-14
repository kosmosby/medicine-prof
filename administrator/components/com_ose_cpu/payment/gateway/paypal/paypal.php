<?php
defined('_JEXEC') or die(";)");

class osePaymentGateWayPaypal extends osePaymentGateWay
{
	protected $orderInfo = null;
	
	function __construct($orderInfo , $item)
	{
		parent::__construct($item);
		$this->orderInfo = $orderInfo;
	}
	
	protected function preparePostVars($orderInfo)
	{
		$prepare = array();
		
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$prepare['paypal_email'] = $pConfig->paypal_email;
		$prepare['test_mode'] = $pConfig->paypal_testmode;
		
		if($prepare['test_mode']) {
			$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url= "https://www.paypal.com/cgi-bin/webscr";
		}
		
		$prepare['url'] = $url;
		
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= $member->getBillingInfo('obj');

		$amount= $orderInfo->payment_price;
		$currency= $orderInfo->payment_currency;
		$order_id= $orderInfo->order_id;
		$order_number= $orderInfo->order_number;
		//$billinginfo = OSEPAYMENTS::get_billinginfo($user_id);
		$user= & JFactory :: getUser($orderInfo->user_id);
		
		$desc = parent::generateDesc($order_id);
		$msc_name = $desc;
		
		return $prepare;
	}
	
	function generateForm()
	{
		$orderInfo = $this->orderInfo;
		if(oseObject::getValue($orderInfo,'payment_mode') == 'm') 
		{
			$html = $this->generateFormManual();
		}
		else
		{
			$html = $this->generateFormAuto();
		}
		
		return $html;
	}
	
	protected function generateFormManual()
	{
		$form = oseHTML::getInstance('form');
		
		if($this->get('test')) {
			$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url= "https://www.paypal.com/cgi-bin/webscr";
		}

		$db= oseDB :: instance();
		$billinginfo= $this->billingInfo;
		
		$orderInfo = $this->orderInfo;
		$amount= oseObject::getValue($orderInfo,'payment_price');
		$currency=  oseObject::getValue($orderInfo,'payment_currency');
		$order_id= oseObject::getValue($orderInfo,'order_id');
		$entry_type= oseObject::getValue($orderInfo,'entry_type');
		$order_number= oseObject::getValue($orderInfo,'order_number');
		$user_id = oseObject::getValue($orderInfo,'user_id');
		
		$user=  oseUser :: init($user_id);

		$desc = $this->generateDesc($order_id);
		
		$vendor_image_url= "";
		
		$html = array();
		
		$form->createForm($url,'ose_pgw_checkout_form','ose_pgw_checkout_form');
		$form->append($form->image('submit','ose_pgw_checkout_form_submit','components/com_osemsc/assets/images/checkout.png',JText :: _('Click to pay with PayPal - it is fast, free and secure!')));
		
		$post_variables= array();
		$post_variables['phpMyAdmin'] = "octl53wDFSC-rSEy-S6gRa-jWtb";
		$post_variables['cmd'] = "_ext-enter";
		$post_variables['redirect_cm'] = "_xclick";
		$post_variables['upload'] = "1";
		$post_variables['business'] = $this->matchConfig('paypal_email');
		$post_variables['item_name'] = JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for '.$entry_type.' Type:');
		$post_variables['order_id'] = $order_id;
		$post_variables['invoice'] = $order_number;
		$post_variables['amount'] = round($amount, 2);
		$post_variables['shipping'] = '0.00';
		$post_variables['currency_code'] = $currency;
		$post_variables['address_override'] = 0;
		$post_variables['first_name'] = oseObject::getValue($billinginfo,'firstname');
		$post_variables['last_name'] = oseObject::getValue($billinginfo,'lastname');;
		$post_variables['address1'] = oseObject::getValue($billinginfo,'addr1');;
		$post_variables['address2'] = oseObject::getValue($billinginfo,'addr2');;
		$post_variables['zip'] = oseObject::getValue($billinginfo,'postcode');;
		$post_variables['city'] = oseObject::getValue($billinginfo,'city');;
		$post_variables['state'] = oseObject::getValue($billinginfo,'state');;
		$post_variables['email'] = $user->email;
		$post_variables['night_phone_b'] = oseObject::getValue($billinginfo,'telephone');;
		$post_variables['cpp_header_image'] = $vendor_image_url;
		$post_variables['return'] = JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$post_variables['notify_url'] = JURI :: base()."components/com_osemsc/ipn/paypal_notify.php";
		$post_variables['cancel_return'] = JURI :: base()."index.php";
		$post_variables['undefined_quantity'] = "0";
		$post_variables['test_ipn'] = "0";
		$post_variables['pal'] = "NRUBJXESJTY24";
		$post_variables['no_shipping'] = "1";
		$post_variables['no_note'] = "1";
		
		foreach($post_variables as $name => $value) {
			$form->append($form->hidden($name,$value));
		}
		
		$html = $form->output();
		
		return $html;
	}
	
	protected function generateFormAuto()
	{
		$form = oseHTML::getInstance('form');
		
		if($this->get('test')) {
			$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url= "https://www.paypal.com/cgi-bin/webscr";
		}

		$db= oseDB :: instance();
		$billinginfo= $this->billingInfo;
		
		$orderInfo = $this->orderInfo;
		$amount= oseObject::getValue($orderInfo,'payment_price');
		$currency=  oseObject::getValue($orderInfo,'payment_currency');
		$order_id= oseObject::getValue($orderInfo,'order_id');
		$entry_type= oseObject::getValue($orderInfo,'entry_type');
		$order_number= oseObject::getValue($orderInfo,'order_number');
		$user_id = oseObject::getValue($orderInfo,'user_id');
		
		$user=  oseUser :: init($user_id);

		$desc = $this->generateDesc($order_id);
		
		$vendor_image_url= "";
		
		$html = array();
		
		$form->createForm($url,'ose_pgw_checkout_form','ose_pgw_checkout_form');
		$form->append($form->image('submit','ose_pgw_checkout_form_submit','components/com_osemsc/assets/images/checkout.png',JText :: _('Click to pay with PayPal - it is fast, free and secure!')));
		
		$post_variables= array();
		$post_variables['phpMyAdmin'] = "octl53wDFSC-rSEy-S6gRa-jWtb";
		$post_variables['cmd'] = "_xclick-subscriptions";
		$post_variables['redirect_cm'] = "_xclick";
		$post_variables['upload'] = "1";
		$post_variables['business'] = $this->matchConfig('paypal_email');
		$post_variables['item_name'] = JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for '.$entry_type.' Type:');
		$post_variables['item_number'] = $order_id;
		$post_variables['invoice'] = $order_number;
		$post_variables['amount'] = round($amount, 2);
		$post_variables['shipping'] = '0.00';
		$post_variables['currency_code'] = $currency;
		$post_variables['address_override'] = 0;
		$post_variables['first_name'] = oseObject::getValue($billinginfo,'firstname');
		$post_variables['last_name'] = oseObject::getValue($billinginfo,'lastname');;
		$post_variables['address1'] = oseObject::getValue($billinginfo,'addr1');;
		$post_variables['address2'] = oseObject::getValue($billinginfo,'addr2');;
		$post_variables['zip'] = oseObject::getValue($billinginfo,'postcode');;
		$post_variables['city'] = oseObject::getValue($billinginfo,'city');;
		$post_variables['state'] = oseObject::getValue($billinginfo,'state');;
		$post_variables['email'] = $user->email;
		$post_variables['night_phone_b'] = oseObject::getValue($billinginfo,'telephone');
		$post_variables['cpp_header_image'] = $vendor_image_url;
		
		$post_variables['notify_url'] = JURI :: base()."components/com_osemsc/ipn/paypal_notify.php";
		$post_variables['cancel_return'] = JURI :: base()."index.php";
		//$post_variables['undefined_quantity'] = "0";
		//$post_variables['test_ipn'] = "0";
		//$post_variables['pal'] = "NRUBJXESJTY24";
		$post_variables['no_shipping'] = "1";
		$post_variables['no_note'] = "1";
		
		
		
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		if(!$orderInfoParams->has_trial) 
		{
			$a3= $orderInfoParams->total;
			$p3= $orderInfoParams->p3;
			$t3= $orderInfoParams->t3;
			$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			$post_variables['return'] = JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$a3}&ordernumber={$order_number}";
			$post_variables['a3'] = round($a3, 2);
			$post_variables['p3'] = $p3;
			$post_variables['t3'] = $t3;
			$post_variables['src'] = 1;
			$post_variables['sra'] = 1;
			$post_variables['currency_code'] = $currency;
			$post_variables['page_style'] = 'primary';
			
		} 
		else 
		{
			$a1= $orderInfoParams->total;
			$p1= $orderInfoParams->p1;
			$t1= $orderInfoParams->t1;

			$a3= $orderInfoParams->next_total;
			$p3= $orderInfoParams->p3;
			$t3= $orderInfoParams->t3;
			
			$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
			$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
			
			$post_variables['return'] = JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$a3}&ordernumber={$order_number}";
			$post_variables['a1'] = round($a1, 2);
			$post_variables['p1'] = $p1;
			$post_variables['t1'] = $t1;
			$post_variables['a3'] = round($a3, 2);
			$post_variables['p3'] = $p3;
			$post_variables['t3'] = $t3;
			$post_variables['src'] = 1;
			$post_variables['sra'] = 1;
			$post_variables['currency_code'] = $currency;
			$post_variables['cpp_header_image'] = $vendor_image_url;
			$post_variables['page_style'] = 'primary';	
		}
		
		foreach($post_variables as $name => $value) {
			$form->append($form->hidden($name,$value));
		}
		
		$html = $form->output();
		
		return $html;
	}
	
	function PaypalExpPostForm($orderInfo,$params) {
		/*$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$paypal_email= $pConfig->paypal_email;
		$html= array();
		$test_mode= $pConfig->paypal_testmode;
		if(empty($paypal_email)) {
			$html['form']= "";
			$html['url']= "";
			return $html;
		}*/
		$form = oseHTML::getInstance('form');
		
		if($this->get('test')) {
			$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$url= "https://www.paypal.com/cgi-bin/webscr";
		}

		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($orderInfo->user_id);
		$billinginfo= $member->getBillingInfo('obj');

		$amount= oseObject::getValue($orderInfo,'payment_price');
		$currency=  oseObject::getValue($orderInfo,'payment_currency');
		$order_id= oseObject::getValue($orderInfo,'order_id');
		$order_number= oseObject::getValue($orderInfo,'order_number');
		//$billinginfo = OSEPAYMENTS::get_billinginfo($user_id);
		
		$user=  oseUser :: init($orderInfo->user_id);

		//oseExit($billinginfo);
		//$node= oseMscTree :: getNode($orderInfo->entry_id, 'obj');

		/*
		$payment= oseMscAddon :: getExtInfo($orderInfo->entry_id, 'payment', 'obj');

		if(isset($params['msc_option']))
		{
			$msc_option = $params['msc_option'];
			unset($params['msc_option']);
		}

		$payment = oseObject::getValue($payment,$msc_option);
		

		$desc = $this->generateDesc($order_id);
		
		$vendor_image_url= "";
		//$app= & JFactory :: getApplication();
		//$currentSession= JSession :: getInstance('none', array());
		//$stores= $currentSession->getStores();
		//$html['form']= '<form action="'.$url.'" method="post" target="_self">';
		//$html['form'] .= '<input type="hidden" name="phpMyAdmin" value="octl53wDFSC-rSEy-S6gRa-jWtb" />';
		$html = array();
		
		$form->createForm($url,'ose_pgw_form','ose_pgw_form');
		$form->append($form->image('submit','components/com_osemsc/assets/images/checkout.png',JText :: _('Click to pay with PayPal - it is fast, free and secure!')));
		
		$post_variables= array();
		$post_variables['phpMyAdmin'] = "octl53wDFSC-rSEy-S6gRa-jWtb";
		$post_variables['cmd'] = "_ext-enter";
		$post_variables['redirect_cm'] = "_xclick";
		$post_variables['upload'] = "1";
		$post_variables['business'] = $paypal_email;
		$post_variables['item_name'] = JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name;
		$post_variables['order_id'] = $order_id;
		$post_variables['invoice'] = $order_number;
		$post_variables['amount'] = round($amount, 2);
		$post_variables['shipping'] = '0.00';
		$post_variables['currency_code'] = $currency;
		$post_variables['address_override'] = 0;
		$post_variables['first_name'] = $billinginfo->firstname;
		$post_variables['last_name'] = $billinginfo->lastname;
		$post_variables['address1'] = $billinginfo->addr1;
		$post_variables['address2'] = $billinginfo->addr2;
		$post_variables['zip'] = $billinginfo->postcode;
		$post_variables['city'] = $billinginfo->city;
		$post_variables['state'] = $billinginfo->state;
		$post_variables['email'] = $user->email;
		$post_variables['night_phone_b'] = $billinginfo->telephone;
		$post_variables['cpp_header_image'] = $vendor_image_url;
		$post_variables['return'] = JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}";
		$post_variables['notify_url'] = JURI :: base()."components/com_osemsc/ipn/paypal_notify.php";
		$post_variables['cancel_return'] = JURI :: base()."index.php";
		$post_variables['undefined_quantity'] = "0";
		$post_variables['test_ipn'] = "0";
		$post_variables['pal'] = "NRUBJXESJTY24";
		$post_variables['no_shipping'] = "1";
		$post_variables['no_note'] = "1";
		
		foreach($post_variables as $name => $value) {
			$form->append($form->hidden($name,$value));
		}
		
		$html['form'] = $form->output();*/
		/*
		if($orderInfo->payment_mode == 'm') {
			$post_variables= array("cmd" => "_ext-enter", "redirect_cmd" => "_xclick", "upload" => "1", "business" => $paypal_email, "receiver_email" => $paypal_email, "item_name" => JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name, "order_id" => $order_id, "invoice" => $order_number, "amount" => round($amount, 2), "shipping" => '0.00', "currency_code" => $currency, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "cpp_header_image" => $vendor_image_url, "return" => JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$amount}&ordernumber={$order_number}", "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "undefined_quantity" => "0", "test_ipn" => 0, "pal" => "NRUBJXESJTY24", "no_shipping" => "1", "no_note" => "1");
			//$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
			
		} else {
			$orderInfoParams= oseJson :: decode($orderInfo->params);
			if(!$orderInfoParams->has_trial) {
				$a3= $orderInfoParams->total;
				$p3= $orderInfoParams->p3;
				$t3= $orderInfoParams->t3;
				$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);
				$post_variables= array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "item_name" => JText :: _('Order ID:')." ".$order_id." - ".JText :: _('Payment for Membership Type:')." ".$msc_name, "order_id" => $order_id, "item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$a3}&ordernumber={$order_number}", "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "a3" => round($a3, 2), "p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency, "cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
			} else {
				$a1= $orderInfoParams->total;
				$p1= $orderInfoParams->p1;
				$t1= $orderInfoParams->t1;

				$a3= $orderInfoParams->next_total;
				$p3= $orderInfoParams->p3;
				$t3= $orderInfoParams->t3;

				$t1= str_replace(substr($t1, 0, 1), strtoupper(substr($t1, 0, 1)), $t1);
				$t3= str_replace(substr($t3, 0, 1), strtoupper(substr($t3, 0, 1)), $t3);

				$post_variables= array("business" => $paypal_email, "cmd" => "_xclick-subscriptions", "item_name" => JText :: _('Order ID: ').$order_id." - ".JText :: _('Payment for Membership Type: ').$msc_name, "order_id" => $order_id, "item_number" => $order_id, "invoice" => $order_number, "address_override" => "0", "first_name" => $billinginfo->firstname, "last_name" => $billinginfo->lastname, "address1" => $billinginfo->addr1, "address2" => $billinginfo->addr2, "zip" => $billinginfo->postcode, "city" => $billinginfo->city, "state" => $billinginfo->state, "email" => $user->email, "night_phone_b" => $billinginfo->telephone, "return" => JURI :: base()."index.php?option=com_osemsc&view=member&result=success&amount={$a1}&ordernumber={$order_number}", "notify_url" => JURI :: base()."components/com_osemsc/ipn/paypal_notify.php", "cancel_return" => JURI :: base()."index.php", "a1" => $a1, "p1" => $p1, "t1" => $t1, "a3" => $a3, "p3" => $p3, "t3" => $t3, "src" => "1", "sra" => "1", "no_note" => "1", "invoice" => $order_number, "currency_code" => $currency, "cpp_header_image" => $vendor_image_url, "page_style" => "primary");
				$html['form'] .= '<input type="image" id="paypal_image" name="submit" src="'."components/com_osemsc/assets/images/checkout.png".'" alt="'.JText :: _('Click to pay with PayPal - it is fast, free and secure!').'" />';
			}
		}
		// Process payment variables;
		$html['url']= $url."?";
		foreach($post_variables as $name => $value) {
			$html['form'] .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			$html['url'] .= $name."=".urlencode($value)."&";
		}
		$html['form'] .= '</form>';
		*/
		return $html;
	}
}
?>