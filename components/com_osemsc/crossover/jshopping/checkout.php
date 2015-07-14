<?php
/**
* @version      2.9.5 19.07.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
include_once(JPATH_COMPONENT_SITE."/payments/payment.php");

class JshoppingControllerCheckout extends JController{
    
    function step2(){                                        
        $this->_check(2);
        
        $session =& JFactory::getSession();
        $user = &JFactory::getUser();
        $jshopConfig = &JSFactory::getConfig();
        $country = &JTable::getInstance('country', 'jshop');
        
        $checkLogin = JRequest::getInt('check_login');
        if ($checkLogin){            
            $session->set("show_pay_without_reg", 1);
            checkUserLogin();
        }

        appendPathWay(_JSHOP_CHECKOUT_ADDRESS);
        $seo = &JTable::getInstance("seo", "jshop");
        $seodata = $seo->loadData("checkout-address");
        if ($seodata->title==""){
            $seodata->title = _JSHOP_CHECKOUT_ADDRESS;
        }
        setMetaData($seodata->title, $seodata->keyword, $seodata->description);
        
        $this->_showCheckoutNavigation(2);
        if ($jshopConfig->show_cart_all_step_checkout){
            $this->_showSmallCart(2);
        }
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        $cart->getSum();
        
        if ($user->id){
            $adv_user = &JSFactory::getUserShop();
        }else{
            $adv_user = &JSFactory::getUserShopGuest();    
        }
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields['address'];
        $count_filed_delivery = 0;
        foreach($config_fields as $k=>$v){
            if (substr($k, 0, 2)=="d_" && $v['display']==1) $count_filed_delivery++;
        }
                
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("adress");
                                
        $view->assign('select', $jshopConfig->arr['title']);
        
        if (!$adv_user->country) $adv_user->country = $jshopConfig->default_country;
        if (!$adv_user->d_country) $adv_user->d_country = $jshopConfig->default_country;

        $option_country[] = JHTML::_('select.option',  '0', _JSHOP_REG_SELECT, 'country_id', 'name' );
        $option_countryes = array_merge($option_country, jshopCountry::getAllCountries());
        $select_countries = JHTML::_('select.genericlist', $option_countryes, 'country', 'class = "inputbox" size = "1"','country_id', 'name', $adv_user->country );
        $select_d_countries = JHTML::_('select.genericlist', $option_countryes, 'd_country', 'class = "inputbox" size = "1"','country_id', 'name', $adv_user->d_country);

        foreach ($jshopConfig->arr['title'] as $key => $value) {                
            $option_title[] = JHTML::_('select.option', $key, $value, 'title_id', 'title_name');
        }            
        $select_titles = JHTML::_('select.genericlist', $option_title, 'title', 'class = "inputbox"','title_id', 'title_name', $adv_user->title);            
        $select_d_titles = JHTML::_('select.genericlist', $option_title, 'd_title', 'class = "inputbox"','title_id', 'title_name', $adv_user->d_title);
        
        $client_types = array();
        foreach ($jshopConfig->user_field_client_type as $key => $value) {        
            $client_types[] = JHTML::_('select.option', $key, $value, 'id', 'name' );
        }
        $select_client_types = JHTML::_('select.genericlist', $client_types,'client_type','class = "inputbox" onchange="showHideFieldFirm(this.value)"','id','name', $adv_user->client_type);
        
        filterHTMLSafe( $adv_user, ENT_QUOTES);
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutStep2', array(&$adv_user) );

        $view->assign('select_countries', $select_countries);
        $view->assign('select_d_countries', $select_d_countries);
        $view->assign('select_titles', $select_titles);
        $view->assign('select_d_titles', $select_d_titles);
        $view->assign('select_client_types', $select_client_types);
        $view->assign('live_path', JURI::base());
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('user', $adv_user);
        $view->assign('delivery_adress', $adv_user->delivery_adress);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=checkout&task=step2save', 0, 0, $jshopConfig->use_ssl));
        $view->display();    
    }
    
    function step2save(){
        
        $this->_check(2);
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $session =& JFactory::getSession();
        $jshopConfig = &JSFactory::getConfig();          
        $post = JRequest::get('post');
        if (!count($post)){
            JError::raiseWarning("",_JSHOP_ERROR_DATA);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step2',0,1, $jshopConfig->use_ssl));
            return 0;
        }
        
        unset($post['user_id']);
        unset($post['usergroup_id']);
        
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JTable::getInstance('userShop', 'jshop');        
            $adv_user->load($user->id);
        }else{
            $adv_user = &JSFactory::getUserShopGuest();
        }
        
        $adv_user->bind($post);
        if(!$adv_user->check("address")){
            JError::raiseWarning("",$adv_user->getError());
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step2',0,1, $jshopConfig->use_ssl));
            return 0;
        }
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeSaveCheckoutStep2', array(&$adv_user) );
        
        if(!$adv_user->store()){
            JError::raiseWarning(500,_JSHOP_REGWARN_ERROR_DATABASE);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step2',0,1, $jshopConfig->use_ssl));
            return 0;
        }
        
        if ($user->id){
            $user = clone(JFactory::getUser());
            $user->email = $adv_user->email;
            $user->save();
        }
        
        setNextUpdatePrices();
        
        $dispatcher->trigger( 'onAfterSaveCheckoutStep2', array(&$adv_user) );
        
        if ($jshopConfig->without_shipping && $jshopConfig->without_payment) {
            $this->_setMaxStep(5);
            $cart->setShippingId(0);
            $cart->setShippingPrId(0);
            $cart->setShippingPrice(0);
            $cart->setPaymentId(0);
            $cart->setPaymentParams("");
            $cart->setPaymentPrice(0);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1, $jshopConfig->use_ssl));
            return 0; 
        }
        
        if ($jshopConfig->without_payment){
            $this->_setMaxStep(4);
            $cart->setPaymentId(0);
            $cart->setPaymentParams("");
            $cart->setPaymentPrice(0);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        $this->_setMaxStep(3);
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3',0,1,$jshopConfig->use_ssl));
    }
    
    function step3(){
    
    	$this->_check(3);
    	
        $jshopConfig = &JSFactory::getConfig();
        $session =& JFactory::getSession();
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JSFactory::getUserShop();
        }else{
            $adv_user = &JSFactory::getUserShopGuest();    
        }    
        
        appendPathWay(_JSHOP_CHECKOUT_PAYMENT);
        $seo = &JTable::getInstance("seo", "jshop");
        $seodata = $seo->loadData("checkout-payment");
        if ($seodata->title==""){
            $seodata->title = _JSHOP_CHECKOUT_PAYMENT;
        }
        setMetaData($seodata->title, $seodata->keyword, $seodata->description);
        
        $this->_showCheckoutNavigation(3);
        if ($jshopConfig->show_cart_all_step_checkout){
            $this->_showSmallCart(3);
        }                        
        
        if ($jshopConfig->without_payment){
            $this->_setMaxStep(4);
            $cart->setPaymentId(0);
            $cart->setPaymentParams("");
            $cart->setPaymentPrice(0);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        $paymentmethod = &JTable::getInstance('paymentmethod', 'jshop');
        $all_payment_methods = jshopPaymentMethod::getAllPaymentMethods();
        $i = 0;
        $paym = array();
        foreach ($all_payment_methods as $pm) {
            if (file_exists($jshopConfig->path . 'payments/'.$pm->payment_class."/".$pm->payment_class.'.php')){
                require_once ($jshopConfig->path . 'payments/'.$pm->payment_class."/".$pm->payment_class.'.php');
                $paym[$i]->existentcheckform = 1;
            }else{
                $paym[$i]->existentcheckform = 0;
            }
            
            $paym[$i]->name = $pm->name;
            $paym[$i]->payment_id = $pm->payment_id;
            $paym[$i]->payment_class = $pm->payment_class;
            $paym[$i]->payment_description = $pm->description;
            $paym[$i]->price_type = $pm->price_type;
            if ($pm->price_type==2){
                $paym[$i]->calculeprice = $pm->price;
            }else{
                $paym[$i]->calculeprice = getPriceCalcParamsTax($pm->price * $jshopConfig->currency_value, $pm->tax_id);
            }
            
            $s_payment_method_id = $cart->getPaymentId();
            if ($s_payment_method_id == $pm->payment_id){
                $params = $cart->getPaymentParams();
            }else{
                $params = array();
            }

            $parseString = new parseString($pm->payment_params);
            $pmconfig = $parseString->parseStringToParams();            

            if ($paym[$i]->existentcheckform){
                ob_start();
                call_user_func(array($pm->payment_class, 'showPaymentForm'), $params, $pmconfig);
                $paym[$i]->form = ob_get_contents();
                ob_get_clean();
            }else{
                $paym[$i]->form = "";
            }
            
            $i++;           
        }
        
        $s_payment_method_id = $cart->getPaymentId();
        $active_payment = intval($s_payment_method_id);

        if (!$active_payment){
            $list_payment_id = array();
            foreach($paym as $v){            
                $list_payment_id[] = $v->payment_id;
            }
            if (in_array($adv_user->payment_id, $list_payment_id)) $active_payment = $adv_user->payment_id;
        }
        
        if (!$active_payment){
            if (isset($paym[0])){
                $active_payment = $paym[0]->payment_id;
            }
        }
        
        if ($jshopConfig->hide_payment_step){
            $first_payment = $paym[0]->payment_class;
            if (!$first_payment){
                JError::raiseWarning("", _JSHOP_ERROR_PAYMENT);
                return 0;    
            }
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3save&payment_method='.$first_payment,0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutStep3', array(&$adv_user, &$paym) );
        
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("payments");        
        $view->assign('payment_methods', $paym);
        $view->assign('active_payment', $active_payment);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3save', 0, 0, $jshopConfig->use_ssl));
        $view->display();    
    }
    
    function step3save(){
        $this->_check(3);
        
        $session =& JFactory::getSession();
        $jshopConfig = &JSFactory::getConfig();        
        $post = JRequest::get('post');
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JTable::getInstance('userShop', 'jshop');        
            $adv_user->load($user->id);
        }else{
            $adv_user = &JSFactory::getUserShopGuest();
        }
        
        $payment_method = JRequest::getVar('payment_method'); //class payment method
        $params = JRequest::getVar('params');
        $params_pm = $params[$payment_method];
        
        $paym_method = &JTable::getInstance('paymentmethod', 'jshop');
        $paym_method->class = $payment_method;
        $payment_method_id = $paym_method->getId();
        $paym_method->load($payment_method_id);
        $pmconfigs = $paym_method->getConfigs();
        
        if (!file_exists($jshopConfig->path . 'payments/'.$payment_method."/".$payment_method.'.php')) {
            $existentcheckform = 0;
        }else{
            $existentcheckform = 1;
            include_once($jshopConfig->path . 'payments/'.$payment_method."/".$payment_method.'.php');
            
            if (!class_exists($payment_method)) {                
                $cart->setPaymentParams('');
                JError::raiseWarning(500, _JSHOP_ERROR_PAYMENT);
                $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3',0,1,$jshopConfig->use_ssl));
                return 0;
            }
            
            $payment_system = new $payment_method();
            
            if (!$payment_system->checkPaymentInfo($params_pm, $pmconfigs)){
                $cart->setPaymentParams('');
                JError::raiseWarning("", $payment_system->getErrorMessage());
                $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3',0,1,$jshopConfig->use_ssl));
                return 0;
            }            
        }                        
        
        $paym_method->setCart($cart);
        $cart->setPaymentId($payment_method_id);
        $cart->setPaymentPrice($paym_method->getPrice());
        $cart->setPaymentTax($paym_method->calculateTax());
        $cart->setPaymentTaxPercent($paym_method->getTax());        
        
        if (isset($params[$payment_method])) {
            $cart->setPaymentParams($params_pm);
        } else {
            $cart->setPaymentParams('');
        }        
        
        $adv_user->saveTypePayment($payment_method_id);
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterSaveCheckoutStep3', array(&$adv_user, &$paym_method) );
        
        //////////////////////////////////////////////////////////////////////////
		// OSE added
		//////////////////////////////////////////////////////////////////////////
		//////////////Joomla Database Class//////////////
		$jdb = &JFactory::getDBO();
		foreach($cart->products as $product)
		{
			$jquery = "SELECT * FROM `#__osemsc_ext` WHERE `type` = 'jshopping'";
			$jdb->setQuery($jquery);
			$jdb->query();
			$rows = $jdb->loadObjectList();
			foreach ($rows as $row)
			{
				$msc_data = oseJson::decode($row->params);
				if ($product['product_id]']  == $msc_data->product_id)
				{
					$this->_setMaxStep(5);
            		$cart->setShippingId(0);
            		$cart->setShippingPrice(0);
            		$this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
           			return 0; 
				}
			}
		}
		// OSE Ends;
        if ($jshopConfig->without_shipping) {
            $this->_setMaxStep(5);
            $cart->setShippingId(0);
            $cart->setShippingPrice(0);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
            return 0; 
        }
        
        $this->_setMaxStep(4);        
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
    }
    
    function step4(){
        
        $this->_check(4);
        
        $session =& JFactory::getSession();
        $jshopConfig = &JSFactory::getConfig(); 

        appendPathWay(_JSHOP_CHECKOUT_SHIPPING);
        $seo = &JTable::getInstance("seo", "jshop");
        $seodata = $seo->loadData("checkout-shipping");
        if ($seodata->title==""){
            $seodata->title = _JSHOP_CHECKOUT_SHIPPING;
        }
        setMetaData($seodata->title, $seodata->keyword, $seodata->description);
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JSFactory::getUserShop();
        }else{
            $adv_user = &JSFactory::getUserShopGuest();    
        }

        $this->_showCheckoutNavigation(4);
        if ($jshopConfig->show_cart_all_step_checkout){
            $this->_showSmallCart(4);
        }                
        
        if ($jshopConfig->without_shipping) {
        	$this->_setMaxStep(5);
            $cart->setShippingId(0);
            $cart->setShippingPrice(0);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
            return 0; 
        }
        
        $shippingmethod = &JTable::getInstance('shippingMethod', 'jshop');
        $shippingmethodprice = &JTable::getInstance('shippingMethodPrice', 'jshop');
        
        if ($adv_user->delivery_adress){
            $id_country = $adv_user->d_country;
        }else{
            $id_country = $adv_user->country;
        }
        if (!$id_country) $id_country = $jshopConfig->default_country;
        
        if (!$id_country) {
            JError::raiseWarning("", _JSHOP_REGWARN_COUNTRY);
        }
        
        $shippings = $shippingmethod->getAllShippingMethodsCountry($id_country);
        foreach ($shippings as $key => $value) {
            $shippingmethodprice->load($value->sh_pr_method_id);
            if ($jshopConfig->show_list_price_shipping_weight){
                $shippings[$key]->shipping_price = $shippingmethodprice->getPricesWeight($value->sh_pr_method_id, $id_country);
            }
            $shippings[$key]->calculeprice = $shippingmethodprice->calculateSum($cart);
        }

        $sh_pr_method_id = $cart->getShippingPrId();
        $active_shipping = intval($sh_pr_method_id);
        if (!$active_shipping){
            foreach($shippings as $v){
                if ($v->shipping_id == $adv_user->shipping_id){
                    $active_shipping = $v->sh_pr_method_id;
                    break;
                }
            }
        }
        if (!$active_shipping){
            if (isset($shippings[0])){
                $active_shipping = $shippings[0]->sh_pr_method_id;
            }
        }
        
        if ($jshopConfig->hide_shipping_step){
            $first_shipping = $shippings[0]->sh_pr_method_id;
            if (!$first_shipping){
                JError::raiseWarning("", _JSHOP_ERROR_SHIPPING);
                return 0;    
            }
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4save&sh_pr_method_id='.$first_shipping,0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutStep4', array(&$adv_user, &$shippings, &$active_shipping) );
        
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("shippings");
        $view->assign('shipping_methods', $shippings);
        $view->assign('active_shipping', $active_shipping);
        $view->assign('config', $jshopConfig);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4save',0,0,$jshopConfig->use_ssl));
        
        $view->display();
    }
    
    function step4save(){
    
    	$this->_check(4);
    	
        $session =& JFactory::getSession();
        $jshopConfig = &JSFactory::getConfig();
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JTable::getInstance('userShop', 'jshop');        
            $adv_user->load($user->id);
        }else{
            $adv_user = &JSFactory::getUserShopGuest();
        }
        
        if ($adv_user->delivery_adress){
            $id_country = $adv_user->d_country;
        }else{
            $id_country = $adv_user->country;
        }
        if (!$id_country) $id_country = $jshopConfig->default_country;
        
        $sh_pr_method_id = JRequest::getInt('sh_pr_method_id');
                
        $shipping_method_price = &JTable::getInstance('shippingMethodPrice', 'jshop');        
        $shipping_method_price->load($sh_pr_method_id);
        
        $sh_method = &JTable::getInstance('shippingMethod', 'jshop');
        $sh_method->load($shipping_method_price->shipping_method_id);
        
        if (!$shipping_method_price->sh_pr_method_id){            
            JError::raiseWarning("", _JSHOP_ERROR_SHIPPING);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        if (!$shipping_method_price->isCorrectMethodForCountry($id_country)){            
            JError::raiseWarning("",_JSHOP_ERROR_SHIPPING);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        if (!$sh_method->shipping_id){            
            JError::raiseWarning("", _JSHOP_ERROR_SHIPPING);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step4',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        $price_shipping = $shipping_method_price->calculateSum($cart);
        
        $cart->setShippingId($sh_method->shipping_id);
        $cart->setShippingPrId($sh_pr_method_id);
        $cart->setShippingPrice($price_shipping);
        $cart->setShippingPriceTax($shipping_method_price->calculateTax($price_shipping));
        $cart->setShippingPriceTaxPercent($shipping_method_price->getTax());
        
        //update payment price
        $payment_method_id = $cart->getPaymentId();
        if ($payment_method_id){
            $paym_method = &JTable::getInstance('paymentmethod', 'jshop');
            $paym_method->load($payment_method_id);
            $cart->setDisplayItem(1, 1);
            $paym_method->setCart($cart);
            $cart->setPaymentPrice($paym_method->getPrice());
            $cart->setPaymentTax($paym_method->calculateTax());
            $cart->setPaymentTaxPercent($paym_method->getTax());
        }

        $adv_user->saveTypeShipping($sh_method->shipping_id);
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterSaveCheckoutStep4', array(&$adv_user, &$sh_method) );
        
        $this->_setMaxStep(5);
                
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
    }
    
    function step5(){
        
        $this->_check(5);
            
        appendPathWay(_JSHOP_CHECKOUT_PREVIEW);
        $seo = &JTable::getInstance("seo", "jshop");
        $seodata = $seo->loadData("checkout-preview");
        if ($seodata->title==""){
            $seodata->title = _JSHOP_CHECKOUT_PREVIEW;
        }
        setMetaData($seodata->title, $seodata->keyword, $seodata->description);
        
        $this->_showCheckoutNavigation(5);
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();              
        
        $session =& JFactory::getSession();        
        $jshopConfig = &JSFactory::getConfig(); 
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JSFactory::getUserShop();
        }else{
            $adv_user = &JSFactory::getUserShopGuest();    
        }
        
        $this->_showSmallCart(5);
                
        $sh_method = &JTable::getInstance('shippingMethod', 'jshop');
        $shipping_method_id = $cart->getShippingId();
        $sh_method->load($shipping_method_id);
        
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $payment_method_id = $cart->getPaymentId();
		$pm_method->load($payment_method_id);        
				
        $lang = &JSFactory::getLang();
        
		if ($adv_user->delivery_adress){
            $country = &JTable::getInstance('country', 'jshop');
            $country->load($adv_user->d_country);
            
			$delivery_info['f_name'] = $adv_user->d_f_name;
            $delivery_info['l_name'] = $adv_user->d_l_name;
			$delivery_info['firma_name'] = $adv_user->d_firma_name;
			$delivery_info['street'] = $adv_user->d_street;
			$delivery_info['zip'] = $adv_user->d_zip;
			$delivery_info['state'] = $adv_user->d_state;
            $delivery_info['city'] = $adv_user->d_city;
            $field_country_name = $lang->get("name");
			$delivery_info['country'] = $country->$field_country_name;
		} else {
            $country = &JTable::getInstance('country', 'jshop');
            $country->load($adv_user->country);
            
			$delivery_info['f_name'] = $adv_user->f_name;
			$delivery_info['l_name'] = $adv_user->l_name;
            $delivery_info['firma_name'] = $adv_user->firma_name;
			$delivery_info['street'] = $adv_user->street;
			$delivery_info['zip'] = $adv_user->zip;
			$delivery_info['state'] = $adv_user->state;
            $delivery_info['city'] = $adv_user->city;
            $field_country_name = $lang->get("name");
			$delivery_info['country'] = $country->$field_country_name;
		}
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutStep5', array(&$sh_method, &$pm_method, &$delivery_info) );
		
		$view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("previewfinish");        
                
        $lang = &JSFactory::getLang();
        $name = $lang->get("name");
        $sh_method->name = $sh_method->$name;
		$view->assign('sh_method', $sh_method );    
		$view->assign('payment_name', $pm_method->$name);
		$view->assign('delivery_info', $delivery_info);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5save',0,0, $jshopConfig->use_ssl));       
		$view->assign('config', $jshopConfig);
        
    	$view->display();
    }
    
    function step5save(){    
        $mainframe =& JFactory::getApplication();    
        $this->_check(5);
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        
        $lang = &JSFactory::getLang();
        $user = &JFactory::getUser();
        if ($user->id){
            $adv_user = &JSFactory::getUserShop();
        }else{
            $adv_user = &JSFactory::getUserShopGuest();    
        }
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        $cart->setDisplayItem(1, 1);
        $cart->setDisplayFreeAttributes();
        
        $session =& JFactory::getSession();
        $jshopConfig = &JSFactory::getConfig();
        $orderNumber = $jshopConfig->next_order_number;
        $jshopConfig->updateNextOrderNumber();
        $db = &JFactory::getDBO();        
        
        $payment_method_id = $cart->getPaymentId();
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $pm_method->load($payment_method_id);        

        $payment_method = $pm_method->payment_class; 
        
        if ($jshopConfig->without_payment){
            $pm_method->payment_type = 1;
            $paymentSystemVerySimple = 1; 
        }elseif (!file_exists($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method. '.php')) {
            $paymentSystemVerySimple = 1;
        }else{ 
            $paymentSystemVerySimple = 0;
            
            include_once ($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method . '.php');        

            if (!class_exists($payment_method)) {
                $cart->setPaymentParams("");
                JError::raiseWarning("",_JSHOP_ERROR_PAYMENT);
                $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3',0,1,$jshopConfig->use_ssl));
                return 0;
            }
        }
                
        $order = &JTable::getInstance('order', 'jshop');
        $arr_property = array('user_id','f_name','l_name','firma_name','client_type','firma_code','tax_number','email','street','zip','city','state','country','phone','mobil_phone','fax','title','ext_field_1','ext_field_2','ext_field_3','d_f_name','d_l_name','d_firma_name','d_email','d_street','d_zip','d_city','d_state','d_country','d_phone','d_mobil_phone','d_title','d_fax','d_ext_field_1','d_ext_field_2','d_ext_field_3');

        foreach ($adv_user as $key => $value) {
            if(in_array($key, $arr_property)) {
                $order->$key = $value;
            }
        }
        
        $order->order_date = $order->order_m_date = date("Y-m-d H:i:s", mktime());
        $order->order_tax = $cart->getTax(1, 1, 1);        
        $order->setTaxExt($cart->getTaxExt(1, 1, 1));
        $order->order_subtotal = $cart->getPriceProducts();        
        $order->order_shipping = $cart->getShippingPrice();
        $order->order_payment = $cart->getPaymentPrice();
        $order->order_discount = $cart->getDiscountShow();
        $order->order_total = $cart->getSum(1, 1, 1);
        $order->currency_exchange = $jshopConfig->currency_value;
        $order->vendor_type = $cart->getVendorType();
        $order->vendor_id = $cart->getVendorId();        
        $order->order_status = $jshopConfig->default_status_order;                
        $order->shipping_method_id = $cart->getShippingId();
        $order->payment_method_id = $cart->getPaymentId();

        $pm_params = $cart->getPaymentParams();
                    
        if (is_array($pm_params) && !$paymentSystemVerySimple) {
            $payment_system = new $payment_method();
            $payment_system->setParams($pm_params);
            $payment_params_names = $payment_system->getDisplayNameParams();            
            $order->payment_params = getTextNameArrayValue($payment_params_names, $pm_params);
            $order->setPaymentParamsData($pm_params);
        }
        
        $name = $lang->get("name");        
        $order->ip_address = $_SERVER['REMOTE_ADDR'];
        $order->order_add_info = JRequest::getVar('order_add_info','');
        $order->currency_code = $jshopConfig->currency_code;
        $order->currency_code_iso = $jshopConfig->currency_code_iso;
        $order->order_number = $order->formatOrderNumber($orderNumber);
        $order->order_hash = md5(time().$order->order_total.$order->user_id);
        $order->file_hash = md5(time().$order->order_total.$order->user_id."hashfile");
        $order->display_price = $jshopConfig->display_price_front_current;
        $order->lang = $jshopConfig->cur_lang;
        
        if ($order->client_type){
            $order->client_type_name = $jshopConfig->user_field_client_type[$order->client_type];
        }else{
            $order->client_type_name = "";
        }
        
        if ($pm_method->payment_type == 1){
            $order->order_created = 1; 
        }else {
            $order->order_created = 0;
        }
        
        if (!$adv_user->delivery_adress) $order->copyDeliveryData();
        
        $dispatcher->trigger( 'onBeforeCreateOrder', array(&$order) );

        if (!$order->store()){
            JError::raiseWarning("", $order->getError());
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
            return 0;            
        }
        
        $dispatcher->trigger( 'onAfterCreateOrder', array(&$order) );
                        
        if ($cart->getCouponId()){
            $coupon = &JTable::getInstance('coupon', 'jshop');
            $coupon->load($cart->getCouponId());
            if ($coupon->finished_after_used){
                $free_discount = $cart->getFreeDiscount();
                if ($free_discount > 0){
                    $coupon->coupon_value = $free_discount / $jshopConfig->currency_value;
                }else{
                    $coupon->used = $adv_user->user_id;
                }
                $coupon->store();
            }
        }
        
        $order->saveOrderItem($cart->products);
        
        $session->set("jshop_end_order_id", $order->order_id);
        
        $order_history = &JTable::getInstance('orderHistory', 'jshop');    
        $order_history->order_id = $order->order_id;
        $order_history->order_status_id = $order->order_status;
        $order_history->status_date_added = $order->order_date;
        $order_history->customer_notify = 1;
        $order_history->store();
        
        if ($pm_method->payment_type == 1){
            $order->changeProductQTYinStock("-");
        }
        
        if ($pm_method->payment_type == 1){
            $this->_sendOrderEmail($order->order_id);
        }
        
        $dispatcher->trigger( 'onEndCheckoutStep5', array(&$order) );
                
        $session->set("jshop_send_end_form", 0);
        
        if ($jshopConfig->without_payment) { 
            $this->_setMaxStep(10);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=finish',0,1,$jshopConfig->use_ssl));
            return 0;    
        }
        
        $this->_setMaxStep(6);
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step6',0,1,$jshopConfig->use_ssl));                
    }
    
    function step6(){
        $this->_check(6);
        $jshopConfig = &JSFactory::getConfig();
        $session =& JFactory::getSession();
        header("Cache-Control: no-cache, must-revalidate");        
        $order_id = $session->get('jshop_end_order_id');

        if (!$order_id){
            JError::raiseWarning("", _JSHOP_SESSION_FINISH);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
        }
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
        
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);        

        // user click back in payment system 
        $jshop_send_end_form = $session->get('jshop_send_end_form');        
        if ($jshop_send_end_form == 1){
            $this->_cancelPayOrder($order_id);
            return 0;
        }

        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $payment_method_id = $order->payment_method_id;
        $pm_method->load($payment_method_id);
        $payment_method = $pm_method->payment_class; 
        
        if (!file_exists($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method. '.php')) {
            $paymentSystemVerySimple = 1;
        }else{
            $paymentSystemVerySimple = 0;
            include_once ($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method. '.php');

            if (!class_exists($payment_method)) {
                $cart->setPaymentParams("");
                JError::raiseWarning("",_JSHOP_ERROR_PAYMENT);
                $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3',0,1,$jshopConfig->use_ssl));
                return 0;
            }
        }
        
        if ($pm_method->payment_type == 1 || $paymentSystemVerySimple) { 
            $this->_setMaxStep(10);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=finish',0,1,$jshopConfig->use_ssl));
            return 0;
        }

        $session->set('jshop_send_end_form', 1);
                
        $pmconfigs = $pm_method->getConfigs();
        $payment_system = new $payment_method();
        $payment_system->showEndForm($pmconfigs, $order);
    }
    
    function step7(){
        
        $mainframe =& JFactory::getApplication();
        $jshopConfig = &JSFactory::getConfig();
        $session =& JFactory::getSession();
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        
        if ($jshopConfig->savelog && $jshopConfig->savelogpaymentdata){            
            $str = "url: ".$_SERVER['REQUEST_URI']."\n";
            foreach($_POST as $k=>$v) $str .= $k."=".$v."\n";
            saveToLog("paymentdata.log", $str);
        }
        
        $act = JRequest::getVar("act");        
        $payment_method = JRequest::getVar("js_paymentclass");        
        
         if (!file_exists($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method. '.php')) {
            if (JRequest::getInt('no_lang')) JSFactory::loadLanguageFile();
            saveToLog("payment.log", "#001 - Error payment method file. PM ".$payment_method);
            JError::raiseWarning(500, _JSHOP_ERROR_PAYMENT);            
            return 0;
        } 
        require_once ($jshopConfig->path . 'payments/' . $payment_method."/".$payment_method. '.php');        

        if (!class_exists($payment_method)) {
            if (JRequest::getInt('no_lang')) JSFactory::loadLanguageFile();
            saveToLog("payment.log", "#002 - Error payment. CLASS ".$payment_method);
            JError::raiseWarning(500, _JSHOP_ERROR_PAYMENT);            
            return 0;
        }
        
        $pmconfigs = $pm_method->getConfigsForClassName($payment_method);        
        $payment_system = new $payment_method();
        $urlParamsPS = $payment_system->getUrlParams($pmconfigs);
        
        $order_id = $urlParamsPS['order_id'];
        $hash = $urlParamsPS['hash'];
        $checkHash = $urlParamsPS['checkHash'];
        $checkReturnParams = $urlParamsPS['checkReturnParams'];
        
        $session->set('jshop_send_end_form', 0);
        
        if ($act == "cancel"){
            $this->_cancelPayOrder($order_id);
            return 0;
        }
        
        if ($act == "return" && !$checkReturnParams){
            $this->_setMaxStep(10);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=finish', 0, 1, $jshopConfig->use_ssl));
            return 1;    
        }               
        
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);
        
        if (JRequest::getInt('no_lang')){
            JSFactory::loadLanguageFile($order->getLang());
            $lang = &JSFactory::getLang($order->getLang());
        }

        if ($checkHash && $order->order_hash != $hash){
            saveToLog("payment.log", "#003 - Error order hash. Order id ".$order_id);
            JError::raiseWarning("", _JSHOP_ERROR_ORDER_HASH);
            return 0;    
        }
        
        if (!$order->payment_method_id){
            saveToLog("payment.log", "#004 - Error payment method id. Order id ".$order_id);
            JError::raiseWarning("", _JSHOP_ERROR_PAYMENT);
            return 0;    
        }        
                
        $pm_method->load($order->payment_method_id);
        
        if ($payment_method != $pm_method->payment_class){
            saveToLog("payment.log", "#005 - Error payment method set url. Order id ".$order_id);
            JError::raiseWarning("", _JSHOP_ERROR_PAYMENT);
            return 0;
        }
                
        $pmconfigs = $pm_method->getConfigs();
        $res = $payment_system->checkTransaction($pmconfigs, $order, $act);
        $rescode = $res[0];
        $restext = $res[1];        
        
        if ($rescode == 0 || $rescode == 3){        
            saveToLog("payment.log", $restext);
        }
        
        if ($rescode==0){
            $status = 0;
        }elseif($rescode==1){
            $status = $pmconfigs['transaction_end_status'];
        }elseif($rescode==2){
            $status = $pmconfigs['transaction_pending_status'];
        }elseif($rescode==3){
            $status = $pmconfigs['transaction_failed_status'];
        }
        
        if ($status && !$order->order_created){
            $order->order_created = 1;
            $order->order_status = $status;
            $order->store();
            $this->_sendOrderEmail($order->order_id);
            $order->changeProductQTYinStock("-");
            $this->_changeStatusOrder($order_id, $status, 0);
        }
            
        if ($status && $order->order_status != $status){            
           $this->_changeStatusOrder($order_id, $status, 1);
        }        

        if ($act == "notify"){
            $payment_system->nofityFinish($pmconfigs, $order, $rescode);            
            die();            
        }
              
        if ($rescode == 0 || $rescode == 3){			
            JError::raiseWarning(500, $restext); 
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
            return 0;
        }else{
            $this->_setMaxStep(10);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=finish',0,1,$jshopConfig->use_ssl));
            return 1;
        }
        
    }
    
    function finish(){
        $this->_check(10);
        
        $jshopConfig = &JSFactory::getConfig();
        $db = &JFactory::getDBO();
        
        $document =& JFactory::getDocument();
        $document->setTitle(_JSHOP_CHECKOUT_FINISH);            
        appendPathWay(_JSHOP_CHECKOUT_FINISH);
        
        $statictext = &JTable::getInstance("statictext","jshop");
        $rowstatictext = $statictext->loadData("order_finish_descr");        
        $text = $rowstatictext->text;
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutFinish', array(&$text) );
        
        if (trim(strip_tags($text)) == ""){        
            $view_name = "checkout";
            $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
            $view = &$this->getView($view_name, 'html', '', $view_config);
            $view->setLayout("finish");        
            $view->display();
        }else{
            print $text;
        }
        
        //clear all info order
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();                
        $cart->getSum();
        $cart->clear();
        $this->_deleteSession();    
    }

    
    function _sendOrderEmail($order_id){
        $mainframe =& JFactory::getApplication();        
        
        $lang = &JSFactory::getLang();
        $jshopConfig = &JSFactory::getConfig();
        $db = &JFactory::getDBO();
        $order = &JTable::getInstance('order', 'jshop');
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields["address"];
        $count_filed_delivery = 0;
        foreach($config_fields as $k=>$v){
            if (substr($k, 0, 2)=="d_" && $v['display']==1) $count_filed_delivery++;
        }
        
        $order->load($order_id);
        
        $status = &JTable::getInstance('orderStatus', 'jshop');
        $status->load($order->order_status);
        $name = $lang->get("name");
        $order->status = $status->$name;
        $order->order_date = strftime($jshopConfig->store_date_format, strtotime($order->order_date));
        $order->products = $order->getAllItems();
        $order->weight = $order->getWeightItems();        
        $order->order_tax_list = $order->getTaxExt();
        $show_percent_tax = 0;        
        if (count($order->order_tax_list)>1 || $jshopConfig->show_tax_in_product) $show_percent_tax = 1;        
        if ($jshopConfig->hide_tax) $show_percent_tax = 0;
        $hide_subtotal = 0;
        if (($jshopConfig->hide_tax || count($order->order_tax_list)==0) && $order->order_discount==0 && $jshopConfig->without_shipping && $order->order_payment==0) $hide_subtotal = 1;
        
        $country = &JTable::getInstance('country', 'jshop');
        $country->load($order->country);
        $field_country_name = $lang->get("name");
        $order->country = $country->$field_country_name;        
        
        $d_country = &JTable::getInstance('country', 'jshop');
        $d_country->load($order->d_country);
        $field_country_name = $lang->get("name");
        $order->d_country = $d_country->$field_country_name;

        $shippingMethod = &JTable::getInstance('shippingMethod', 'jshop');
        $shippingMethod->load($order->shipping_method_id);
        
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $pm_method->load($order->payment_method_id);
        
        $name = $lang->get("name");
        $description = $lang->get("description");
        $order->shipping_information = $shippingMethod->$name;
        $order->payment_name = $pm_method->$name;
        $order->payment_information = $order->payment_params;
        if ($pm_method->show_descr_in_email) $order->payment_description = $pm_method->$description;  else $order->payment_description = "";

        $statictext = &JTable::getInstance("statictext","jshop");
        $rowstatictext = $statictext->loadData("order_email_descr");        
        $order_email_descr = $rowstatictext->text;
        $order_email_descr = str_replace("{name}",$order->f_name, $order_email_descr);
        $order_email_descr = str_replace("{family}",$order->l_name, $order_email_descr);
        $order_email_descr = str_replace("{email}",$order->email, $order_email_descr);
                
        $text_total = _JSHOP_ENDTOTAL;
        if (($jshopConfig->show_tax_in_product || $jshopConfig->show_tax_product_in_cart) && (count($order->order_tax_list)>0)){
            $text_total = _JSHOP_ENDTOTAL_INKL_TAX;
        }
        
        $uri =& JURI::getInstance();        
        $liveurlhost = $uri->toString( array("scheme",'host', 'port'));
        
        $vendors_send_message = ($jshopConfig->vendor_order_message_type==1 || ($order->vendor_type==1 && $jshopConfig->vendor_order_message_type==2));
        $vendor_send_order = ($jshopConfig->vendor_order_message_type==2 && $order->vendor_type == 0 && $order->vendor_id);
        $admin_send_order = 1;
        if ($jshopConfig->admin_not_send_email_order_vendor_order && $vendor_send_order) $admin_send_order = 0;
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeSendEmailsOrder', array(&$order) );
        
        //client message
        $client = 1;
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("orderemail");
        $view->assign('client', $client);
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('order_email_descr', $order_email_descr);
        $view->assign('config', $jshopConfig);
        $view->assign('order', $order);
        $view->assign('show_percent_tax', $show_percent_tax);
        $view->assign('hide_subtotal', $hide_subtotal);
        $view->assign('noimage',"noimage.gif");
        $view->assign('text_total',$text_total);
        $view->assign('liveurlhost',$liveurlhost);
        $message_client = $view->loadTemplate();
        
        //admin message
        $client = 0;
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("orderemail");
        $view->assign('client', $client);
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('config', $jshopConfig);
        $view->assign('order',$order);
        $view->assign('show_percent_tax', $show_percent_tax);
        $view->assign('hide_subtotal', $hide_subtotal);
        $view->assign('noimage',"noimage.gif");
        $view->assign('text_total',$text_total);
        $view->assign('liveurlhost',$liveurlhost);
        $message_admin = $view->loadTemplate();
        
        //vendors messages        
        if ($vendors_send_message){            
            $listVendors = $order->getVendors();            
            foreach($listVendors as $k=>$datavendor){
                $vendor_order_items = $order->getVendorItems($datavendor->id);
                $client = 0;
                $view_name = "checkout";
                $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
                $view = &$this->getView($view_name, 'html', '', $view_config);
                $view->setLayout("orderemailpart");
                $view->assign('client', $client);
                $view->assign('config_fields', $config_fields);
                $view->assign('count_filed_delivery', $count_filed_delivery);
                $view->assign('config', $jshopConfig);
                $view->assign('order',$order);
                $view->assign('products', $vendor_order_items);
                $view->assign('show_percent_tax', $show_percent_tax);
                $view->assign('hide_subtotal', $hide_subtotal);
                $view->assign('noimage',"noimage.gif");
                $view->assign('text_total',$text_total);
                $view->assign('liveurlhost',$liveurlhost);
                $message_vendor = $view->loadTemplate();
                $listVendors[$k]->message = $message_vendor;
            }
        }
        
        if ($jshopConfig->order_send_pdf_client || $jshopConfig->order_send_pdf_admin){
            include_once(JPATH_SITE . "/components/com_jshopping/lib/generete_pdf_order.php");
            $order->pdf_file = generatePdf($order, $jshopConfig);
            $order->insertPDF();
        }
        
        $mailfrom = $mainframe->getCfg( 'mailfrom' );
        $fromname = $mainframe->getCfg( 'fromname' );
        
        //send mail client
        $mailer =& JFactory::getMailer();
        $mailer->setSender(array($mailfrom, $fromname));
        $mailer->addRecipient($order->email);
        $mailer->setSubject( sprintf(_JSHOP_NEW_ORDER, $order->order_number, $order->f_name." ".$order->l_name));
        $mailer->setBody($message_client);
        if ($jshopConfig->order_send_pdf_client){
            $mailer->addAttachment($jshopConfig->pdf_orders_path."/".$order->pdf_file);
        }
        $mailer->isHTML(true);
        $send =& $mailer->Send();
        
        //send mail admin        
        if ($admin_send_order){
            $mailer =& JFactory::getMailer();
            $mailer->setSender(array($mailfrom, $fromname));
            $mailer->addRecipient($jshopConfig->contact_email);
            $mailer->setSubject( sprintf(_JSHOP_NEW_ORDER, $order->order_number, $order->f_name." ".$order->l_name));
            $mailer->setBody($message_admin);
            if ($jshopConfig->order_send_pdf_admin){
                $mailer->addAttachment($jshopConfig->pdf_orders_path."/".$order->pdf_file);
            }
            $mailer->isHTML(true);
            $send =& $mailer->Send();
        }
        
        //send mail vendors
        if ($vendors_send_message){
            foreach($listVendors as $k=>$vendor){
                $mailer =& JFactory::getMailer();
                $mailer->setSender(array($mailfrom, $fromname));
                $mailer->addRecipient($vendor->email);
                $mailer->setSubject( sprintf(_JSHOP_NEW_ORDER_V, $order->order_number, ""));
                $mailer->setBody($vendor->message);                
                $mailer->isHTML(true);
                $send =& $mailer->Send();
            }            
        }
        
        //vendor send order
        if ($vendor_send_order){
            $vendor = &JTable::getInstance('vendor', 'jshop');
            $vendor->load($order->vendor_id);
            $mailer =& JFactory::getMailer();
            $mailer->setSender(array($mailfrom, $fromname));
            $mailer->addRecipient($vendor->email);
            $mailer->setSubject( sprintf(_JSHOP_NEW_ORDER, $order->order_number, $order->f_name." ".$order->l_name));
            $mailer->setBody($message_admin);
            if ($jshopConfig->order_send_pdf_admin){
                $mailer->addAttachment($jshopConfig->pdf_orders_path."/".$order->pdf_file);
            }
            $mailer->isHTML(true);
            $send =& $mailer->Send();            
        }
                
        $dispatcher->trigger( 'onAfterSendEmailsOrder', array(&$order) );
        
    }

    function _showSmallCart($step = 0){
        $jshopConfig = &JSFactory::getConfig();
        $session =& JFactory::getSession();
        
        $cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();        
        $cart->addLinkToProducts(0);
        $cart->setDisplayFreeAttributes();
        
        if ($step == 5) {
            $cart->setDisplayItem(1, 1);
        }elseif ($step == 4) {
            $cart->setDisplayItem(0, 1);
        }else{
            $cart->setDisplayItem(0, 0);
        }
        $cart->updateDiscountData();
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplaySmallCart', array(&$cart) );
                
        $view_name = "cart";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("checkout");                                
        
        $view->assign('config', $jshopConfig);
        $view->assign('products', $cart->products);
        $view->assign('summ', $cart->getPriceProducts());
        $view->assign('image_product_path', $jshopConfig->image_product_live_path);
        $view->assign('image_path', $jshopConfig->live_path);
        $view->assign('no_image', 'noimage.gif');
        $view->assign('discount', $cart->getDiscountShow());
        $view->assign('free_discount', $cart->getFreeDiscount());
        
        if ($step == 5) {
            if (!$jshopConfig->without_shipping){
                $view->assign('summ_delivery', $cart->getShippingPrice());
                $view->assign('summ_payment', $cart->getPaymentPrice());
                $fullsumm = $cart->getSum(1,1,1);
                $tax_list = $cart->getTaxExt(1,1,1);
            }else{
                $view->assign('summ_payment', $cart->getPaymentPrice());
                $fullsumm = $cart->getSum(0,1,1);
                $tax_list = $cart->getTaxExt(0,1,1);
            }
            
            $lang = &JSFactory::getLang();
            $name = $lang->get("name");
            $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
            $payment_method_id = $cart->getPaymentId();
            $pm_method->load($payment_method_id);
            $view->assign('payment_name', $pm_method->$name);            
        }elseif($step == 4){            
            $view->assign('summ_payment', $cart->getPaymentPrice());
            $fullsumm = $cart->getSum(0,1,1);
            $tax_list = $cart->getTaxExt(0,1,1);
            
            $lang = &JSFactory::getLang();
            $name = $lang->get("name");
            $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
            $payment_method_id = $cart->getPaymentId();
            $pm_method->load($payment_method_id);
            $view->assign('payment_name', $pm_method->$name);            
        }else{
            $fullsumm = $cart->getSum(0, 1, 0);
            $tax_list = $cart->getTaxExt(0, 1, 0);
        }
        
        $show_percent_tax = 0;        
        if (count($tax_list)>1 || $jshopConfig->show_tax_in_product) $show_percent_tax = 1;
        if ($jshopConfig->hide_tax) $show_percent_tax = 0;
        $hide_subtotal = 0;
        if ($step == 5) {
            if (($jshopConfig->hide_tax || count($tax_list)==0) && !$cart->rabatt_summ && $jshopConfig->without_shipping && $cart->getPaymentPrice()==0) $hide_subtotal = 1;
        }elseif ($step == 4) {
            if (($jshopConfig->hide_tax || count($tax_list)==0) && !$cart->rabatt_summ && $cart->getPaymentPrice()==0) $hide_subtotal = 1;
        }else{
            if (($jshopConfig->hide_tax || count($tax_list)==0) && !$cart->rabatt_summ) $hide_subtotal = 1;
        }
        
        $text_total = _JSHOP_PRICE_TOTAL;
        if ($step == 5){
            $text_total = _JSHOP_ENDTOTAL;
            if (($jshopConfig->show_tax_in_product || $jshopConfig->show_tax_product_in_cart) && (count($tax_list)>0)){
                $text_total = _JSHOP_ENDTOTAL_INKL_TAX;
            }
        }                
        
        $view->assign('tax_list', $tax_list);
        $view->assign('fullsumm', $fullsumm);        
        $view->assign('show_percent_tax', $show_percent_tax);
        $view->assign('hide_subtotal', $hide_subtotal);
        $view->assign('text_total', $text_total);
        $view->assign('weight', $cart->getWeightProducts());
                
        $view->display();
    }
    
    function _showCheckoutNavigation($step) {
    
        $jshopConfig = &JSFactory::getConfig();
        $array_navigation_steps = array('2' => _JSHOP_STEP_ORDER_2, '3' => _JSHOP_STEP_ORDER_3, '4' => _JSHOP_STEP_ORDER_4, '5' => _JSHOP_STEP_ORDER_5);        
        $output = array();        
        if ($jshopConfig->without_shipping || $jshopConfig->hide_shipping_step) unset($array_navigation_steps[4]);
        if ($jshopConfig->without_payment || $jshopConfig->hide_payment_step) unset($array_navigation_steps[3]);
        //////////////////////////////////////////////////////////////////////////
		// OSE added
		//////////////////////////////////////////////////////////////////////////
		//////////////Joomla Database Class//////////////
		$cart = &JModel::getInstance('cart', 'jshop');
        $cart->load();
		$jdb = &JFactory::getDBO();
		foreach($cart->products as $product)
		{
			$jquery = "SELECT * FROM `#__osemsc_ext` WHERE `type` = 'jshopping'";
			$jdb->setQuery($jquery);
			$jdb->query();
			$rows = $jdb->loadObjectList();
			foreach ($rows as $row)
			{
				$msc_data = oseJson::decode($row->params);
				if ($product['product_id']  == $msc_data->product_id)
				{
					unset($array_navigation_steps[4]);
				}
			}
		}
		// OSE Ends;
       
        foreach ($array_navigation_steps as $key => $value) {
            if($key < $step){
                $output[] = '<a href = "'.SEFLink('index.php?option=com_jshopping&controller=checkout&task=step'.$key,0,0,$jshopConfig->use_ssl).'">' . $value . '</a>';
            } else{
                if($key == $step)
                    $output[] = '<span id = "active_step">' . $value . '</span>';
                else
                    $output[] = $value;
            }
        }
        
        JPluginHelper::importPlugin('jshoppingcheckout');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayCheckoutNovigator', array(&$output) );
                
        $view_name = "checkout";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("menu");
        
        $view->assign('steps', $output);
        $view->display();
    }
    
    function _check($step){
        $mainframe =& JFactory::getApplication();        
        $jshopConfig = &JSFactory::getConfig();
        $session =& JFactory::getSession();        
        
        if ($step<10){
            
            if (!$jshopConfig->shop_user_guest){
                checkUserLogin();
            }
            
            $cart = &JModel::getInstance('cart', 'jshop');
            $cart->load();
                                
            if ($cart->getCountProduct() == 0) {
                JError::raiseWarning("", _JSHOP_NO_SELECT_PRODUCT);
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',0,1));
                exit();
            }
            
            if ($jshopConfig->min_price_order && ($cart->getPriceProducts() < ($jshopConfig->min_price_order * $jshopConfig->currency_value) )){
                JError::raiseNotice("", sprintf(_JSHOP_ERROR_MIN_SUM_ORDER, formatprice($jshopConfig->min_price_order * $jshopConfig->currency_value)));
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',0,1));
                exit();
            }
            
            if ($jshopConfig->max_price_order && ($cart->getPriceProducts() > ($jshopConfig->max_price_order * $jshopConfig->currency_value) )){
                JError::raiseNotice("", sprintf(_JSHOP_ERROR_MAX_SUM_ORDER, formatprice($jshopConfig->max_price_order * $jshopConfig->currency_value)));
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',0,1));
                exit();
            }
            
            
        }
        
        if ($step>2){
            $jhop_max_step = $session->get("jhop_max_step");
        	if (!$jhop_max_step){
                $session->set('jhop_max_step', 2);
                $jhop_max_step = 2;
            }
        	if ($step > $jhop_max_step){
	        	JError::raiseWarning("", _JHOP_ERROR_STEP);
	            $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step'.$jhop_max_step,0,1, $jshopConfig->use_ssl));
        	}
        }
    }
    
    function _cancelPayOrder($order_id = ""){
        $session =& JFactory::getSession();
        if (!$order_id) $order_id = $session->get('jshop_end_order_id');
        if (!$order_id){
            JError::raiseWarning("", _JSHOP_SESSION_FINISH); 
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1,$jshopConfig->use_ssl));
            return 0;
        }
        
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $pm_method->load($order->payment_method_id);
        $pmconfigs = $pm_method->getConfigs();
        $status = $pmconfigs['transaction_failed_status'];
        if ($order->order_created) $sendmessage = 1; else $sendmessage = 0;
        $this->_changeStatusOrder($order_id, $status, $sendmessage);
        
        JError::raiseWarning("", _JSHOP_PAYMENT_CANCELED); 
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step5',0,1, $jshopConfig->use_ssl));
        return 0;
    }

    function _changeStatusOrder($order_id, $status, $sendmessage = 1){
        $mainframe =& JFactory::getApplication();
        
        $lang = &JSFactory::getLang();
        $jshopConfig = &JSFactory::getConfig();
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeChangeOrderStatus', array(&$order_id, &$status, &$sendmessage) );
            
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);
        $order->order_status = $status;
        $order->store();
        //////////////////////////////////////////////////////////////////////////
       	// OSE  added
       	//////////////////////////////////////////////////////////////////////////
       	//2=>Confirmed, 1=>Pending, 4=>Refunded
        if ($status == "2" || $status == "1" || $status == "4")
        {
        	require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
        	require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
        	$db = &JFactory::getDBO();
        	$jquery = "SELECT user_id FROM `#__jshopping_orders` WHERE `order_id` = '" . $db->getEscaped($order_id) . "'";
        	$db->setQuery($jquery);
        	$user_id = $db->loadResult();
        	$jquery = "SELECT a.product_id, b.category_id FROM `#__jshopping_order_item` as a, `#__jshopping_products_to_categories` as b WHERE a.`order_id` = '" . $db->getEscaped($order_id) . "' AND a.`product_id` = b.`product_id`";
        	$db->setQuery($jquery);
        	$results = $db->loadObjectList();
        	$cart = oseMscPublic::getCart();
  
        	foreach ($results as $result)
        	{
        		//////////////////////////////////////////////////
        		$product_id = $result->product_id;
        		$jquery = "SELECT * FROM `#__osemsc_ext` WHERE `type` = 'jshopping'";
        		$db->setQuery($jquery);
        		$db->query();
        		$rows = $db->loadObjectList();
        		foreach ($rows as $row)
        		{
        			$msc_data = oseJson::decode($row->params);
        			$left_var = "";
        			$right_var = "";
        			if ($msc_data->category_id > 0)
        			{
        				$left_var = $result->category_id;
        				$right_var = $msc_data->category_id;
        			}
        			else
        			{
        				$left_var = $result->product_id;
        				$right_var = $msc_data->product_id;
        			}
        			if ($left_var == $right_var)
        			{
        				$msc_id = $row->id;
        				$query = "SELECT currency_code FROM `#__jshopping_orders` WHERE `order_id` = ".(int)$order_id;
        				$db->setQuery($query);
        				$currency = $db->loadResult();

        				if ($status == "2")
        				{
        					$member= oseRegistry :: call('member');
        					$member->instance($user_id, 'member_id');

        					// get current item
        					$cart->updateParams('payment_mode','m');
        					$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($msc_id,$currency,0);
        					$nItem = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>oseObject::getValue($paymentInfo,'msc_option'));
        					$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);

        					$cart->update();

        					// join msc
        					oseRegistry::call('msc')->runAddonAction('register.payment.save',array('member_id'=>$user_id,'payment_method'=>'none'), true, false);
        					$msc_order_id = JRequest::getInt('order_id',0);

        					oseRegistry::call('payment')->getInstance('Order')->confirmOrder($msc_order_id, array());
        				}
        				else
        				{
        					$msc_id= $row->id;
        					$member= oseRegistry :: call('member');
        					$member->instance($user_id, 'member_id');
        					$params = $member->getAddonParams($msc_id,$user_id,0,$params = array());
        					$updated= $msc->runAddonAction('member.msc.cancelMsc', $params);
        				}
                     }
                  }
             }
                
        }
        //////////////////////////////////////////////////////////////////////////
        $vendorinfo = $order->getVendorInfo();
        
        $order_status = &JTable::getInstance('orderStatus', 'jshop');
        $order_status->load($status);
        
        if ($order->order_created && in_array($status, $jshopConfig->payment_status_return_product_in_stock)){
            $order->changeProductQTYinStock("+");
        }
        
        $order_history = &JTable::getInstance('orderHistory', 'jshop');
        $order_history->order_id = $order->order_id;
        $order_history->order_status_id = $status;
        $order_history->status_date_added = date("Y-m-d H:i:s");
        $order_history->customer_notify = 1;
        $order_history->comments  = $restext;
        $order_history->store();        
        if (!$sendmessage) return 1;
        
        $name = $lang->get("name");
        
        $view_name = "order";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$jshopConfig->template."/".$view_name);
        $view = &$this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("statusorder");
        $view->assign('order', $order);
        $view->assign('order_status', $order_status->$name);
        $view->assign('vendorinfo', $vendorinfo);
        $message = $view->loadTemplate();
        
        $vendors_send_message = ($jshopConfig->vendor_order_message_type==1 || ($order->vendor_type==1 && $jshopConfig->vendor_order_message_type==2));
        $vendor_send_order = ($jshopConfig->vendor_order_message_type==2 && $order->vendor_type == 0 && $order->vendor_id);
        $admin_send_order = 1;
        if ($jshopConfig->admin_not_send_email_order_vendor_order && $vendor_send_order) $admin_send_order = 0;
         
        $mailfrom = $mainframe->getCfg( 'mailfrom' );
        $fromname = $mainframe->getCfg( 'fromname' );
        
        //message client
        $subject = sprintf(_JSHOP_ORDER_STATUS_CHANGE_SUBJECT, $order->order_number);
        JUtility::sendMail($mailfrom, $fromname, $order->email, $subject, $message);
        
        //message admin
        if ($admin_send_order){
            JUtility::sendMail($mailfrom, $fromname, $jshopConfig->contact_email, _JSHOP_ORDER_STATUS_CHANGE_TITLE, $message);
        }
        
        //message vendors
        if ($vendors_send_message){            
            $listVendors = $order->getVendors();            
            foreach($listVendors as $k=>$datavendor){
                JUtility::sendMail($mailfrom, $fromname, $datavendor->email, _JSHOP_ORDER_STATUS_CHANGE_TITLE, $message);
            }
        }
        
        //message vendor
        if ($vendor_send_order){
            $vendor = &JTable::getInstance('vendor', 'jshop');
            $vendor->load($order->vendor_id);
            JUtility::sendMail($mailfrom, $fromname, $vendor->email, _JSHOP_ORDER_STATUS_CHANGE_TITLE, $message);
        }
        
        $dispatcher->trigger( 'onAfterChangeOrderStatus', array(&$order_id, &$status, &$sendmessage) );
        
        return 1;
    }
    
    function _setMaxStep($step){
        $session =& JFactory::getSession();
        $jhop_max_step = $session->get('jhop_max_step');
    	if (!isset($jhop_max_step)) $session->set('jhop_max_step', 2);
        $jhop_max_step = $session->get('jhop_max_step');
    	if ($jhop_max_step < $step) $session->set('jhop_max_step', $step);
    }
    
    function _deleteSession(){
        $session =& JFactory::getSession();        
        $session->set('check_params', null);
        $session->set('cart', null);
        $session->set('jhop_max_step', null);        
        $session->set('jshop_price_shipping_tax_percent', null);
        $session->set('jshop_price_shipping', null);
        $session->set('jshop_price_shipping_tax', null);
        $session->set('pm_params', null);        
        $session->set('payment_method_id', null);
        $session->set('jshop_payment_price', null);
        $session->set('shipping_method_id', null);
        $session->set('sh_pr_method_id', null);
        $session->set('jshop_price_shipping_tax_percent', null);                
        $session->set('jshop_end_order_id', null);
        $session->set('jshop_send_end_form', null);
        $session->set('show_pay_without_reg', 0);
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterDeleteDataOrder', array() );
    }   
    
}

?>