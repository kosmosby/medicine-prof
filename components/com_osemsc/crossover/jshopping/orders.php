<?php
/**
* @version      2.9.4 25.06.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class JshoppingControllerOrders extends JController{
    
    function __construct( $config = array() ){
        parent::__construct( $config );

        addSubmenu("orders");
    }

    function display(){
        $jshopConfig = &JSFactory::getConfig();
        $mainframe =& JFactory::getApplication();        
        $context = "jshopping.list.admin.orders";
        $limit = $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int' );
        $id_vendor_cuser = getIdVendorForCUser();
        
        $status_id = $mainframe->getUserStateFromRequest( $context.'status_id', 'status_id', 0 );
        $year = $mainframe->getUserStateFromRequest( $context.'year', 'year', 0 );
        $month = $mainframe->getUserStateFromRequest( $context.'month', 'month', 0 );
        $day = $mainframe->getUserStateFromRequest( $context.'day', 'day', 0 );
        $notfinished = $mainframe->getUserStateFromRequest( $context.'notfinished', 'notfinished', 0 );
        $text_search = $mainframe->getUserStateFromRequest( $context.'text_search', 'text_search', '' );
        
        $filter = array("status_id"=>$status_id, "year"=>$year, "month"=>$month, "day"=>$day, "text_search"=>$text_search, 'notfinished'=>$notfinished);
        
        if ($id_vendor_cuser){            
            $filter["vendor_id"] = $id_vendor_cuser;
        }
        
        $orders = &$this->getModel("orders");                
        
        $total = $orders->getCountAllOrders($filter);        
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        
        $rows = $orders->getAllOrders($pageNav->limitstart, $pageNav->limit, $filter);
        $lists['status_orders'] = $orders->getAllOrderStatus();
        $_list_status0[] = JHTML::_('select.option', 0, _JSHOP_ALL_ORDERS, 'status_id', 'name');
        $_list_status = $lists['status_orders'];
        $_list_status = array_merge($_list_status0, $_list_status);
        $lists['changestatus'] = JHTML::_('select.genericlist', $_list_status,'status_id','','status_id','name', $status_id );
        $nf_option = array();
        $nf_option[] = JHTML::_('select.option', 0, _JSHOP_HIDE, 'id', 'name');
        $nf_option[] = JHTML::_('select.option', 1, _JSHOP_SHOW, 'id', 'name');
        $lists['notfinished'] = JHTML::_('select.genericlist', $nf_option, 'notfinished','','id','name', $notfinished );
        
        $firstYear = $orders->getMinYear(); 
        $y_option = array();
        $y_option[] = JHTML::_('select.option', 0, " - - - ", 'id', 'name');
        for($y=$firstYear;$y<=date("Y");$y++){
            $y_option[] = JHTML::_('select.option', $y, $y, 'id', 'name');
        }        
        $lists['year'] = JHTML::_('select.genericlist', $y_option, 'year', '', 'id', 'name', $year);
        
        $y_option = array();
        $y_option[] = JHTML::_('select.option', 0, " - - ", 'id', 'name');
        for($y=1;$y<=12;$y++){
            if ($y<10) $y_month = "0".$y; else $y_month = $y;
            $y_option[] = JHTML::_('select.option', $y_month, $y_month, 'id', 'name');
        }        
        $lists['month'] = JHTML::_('select.genericlist', $y_option, 'month', '', 'id', 'name', $month);
        
        $y_option = array();
        $y_option[] = JHTML::_('select.option', 0, " - - ", 'id', 'name');
        for($y=1;$y<=31;$y++){
            if ($y<10) $y_day = "0".$y; else $y_day = $y;
            $y_option[] = JHTML::_('select.option', $y_day, $y_day, 'id', 'name');
        }        
        $lists['day'] = JHTML::_('select.genericlist', $y_option, 'day', '', 'id', 'name', $day);
        
        $show_vendor = $jshopConfig->admin_show_vendors;
        if ($id_vendor_cuser) $show_vendor = 0;
        $display_info_only_my_order = 0;
        if ($jshopConfig->admin_show_vendors && $id_vendor_cuser){
            $display_info_only_my_order = 1; 
        }
        
        foreach($rows as $k=>$row){
            if ($row->vendor_id>0){
                $vendor_name = $row->v_fname . " " . $row->v_name;
            }elseif ($row->vendor_id==0){
                $vendor_name = $jshopConfig->contact_firstname." ".$jshopConfig->contact_lastname;
            }else{
                $vendor_name = "-";
            }
            $rows[$k]->vendor_name = $vendor_name;
            
            $display_info_order = 1;
            if ($display_info_only_my_order && $id_vendor_cuser!=$row->vendor_id) $display_info_order = 0;
            $rows[$k]->display_info_order = $display_info_order;
            
            $blocked = 0;
            if (orderBlocked($row) || !$display_info_order) $blocked = 1;
            $rows[$k]->blocked = $blocked;
        }
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayListOrderAdmin', array(&$rows) );
		
		$view=&$this->getView("orders_list", 'html');
        $view->assign('rows', $rows); 
        $view->assign('lists', $lists); 
        $view->assign('pageNav', $pageNav); 
        $view->assign('text_search', $text_search); 
        $view->assign('filter', $filter);        
        $view->assign('show_vendor', $show_vendor);
		$view->display(); 
    }
    
    function show(){
        $order_id = JRequest::getInt("order_id");
        $lang = &JSFactory::getLang();
        $db = &JFactory::getDBO();
        $jshopConfig = &JSFactory::getConfig();
        $orders = &$this->getModel("orders");
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);
        $orderstatus = &JTable::getInstance('orderStatus', 'jshop');
        $orderstatus->load($order->order_status);
        $name = $lang->get("name");    
        $order->status_name = $orderstatus->$name;
        
        $id_vendor_cuser = getIdVendorForCUser();
        
        $shipping_method =&JTable::getInstance('shippingMethod', 'jshop');
        $shipping_method->load($order->shipping_method_id);
        
        $name = $lang->get("name");
        $order->shipping_info = $shipping_method->$name;
        
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $pm_method->load($order->payment_method_id);
        $order->payment_name = $pm_method->$name;
        
        $order_items = $order->getAllItems();
        if ($jshopConfig->admin_show_vendors){
            $tmp_order_vendors = $order->getVendors();
            $order_vendors = array();
            foreach($tmp_order_vendors as $v){
                $order_vendors[$v->id] = $v;
            }
            $obj = new stdClass();
            $obj->f_name = $jshopConfig->contact_firstname;
            $obj->l_name = $jshopConfig->contact_lastname;
            $order_vendors[0] = $obj;
        }
        
        
        $order->weight = $order->getWeightItems();
        $order_history = $order->getHistory();
        $lists['status'] = JHTML::_('select.genericlist', $orders->getAllOrderStatus(),'order_status','class = "inputbox" size = "1" id = "order_status"','status_id','name', $order->order_status);        
        
        $country = &JTable::getInstance('country', 'jshop');
        $country->load($order->country);
        $field_country_name = $lang->get("name");
        $order->country = $country->$field_country_name;
        
        $d_country = &JTable::getInstance('country', 'jshop');
        $d_country->load($order->d_country);
        $field_country_name = $lang->get("name");
        $order->d_country = $d_country->$field_country_name;
        
        $jshopConfig->user_field_client_type[0]="";
        $order->client_type_name = $jshopConfig->user_field_client_type[$order->client_type];
        
        $order->order_tax_list = $order->getTaxExt();
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields["address"];
        $count_filed_delivery = 0;
        foreach($config_fields as $k=>$v){
            if (substr($k, 0, 2)=="d_" && $v['display']==1) $count_filed_delivery++;
        }
        
        $display_info_only_product = 0;
        if ($jshopConfig->admin_show_vendors && $id_vendor_cuser){
            if ($order->vendor_id!=$id_vendor_cuser) $display_info_only_product = 1; 
        }
        
        $display_block_change_order_status = $order->order_created;        
        if ($jshopConfig->admin_show_vendors && $id_vendor_cuser){
            if ($order->vendor_id!=$id_vendor_cuser) $display_block_change_order_status = 0;
            foreach($order_items as $k=>$v){
                if ($v->vendor_id!=$id_vendor_cuser){
                    unset($order_items[$k]);
                }
            }
        }
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeDisplayOrderAdmin', array(&$order, &$order_items, &$order_history) );        
        
        $print = JRequest::getInt("print");
        
        $view=&$this->getView("orders_show", 'html');                
        $view->assign('config', $jshopConfig); 
        $view->assign('order', $order); 
        $view->assign('order_history', $order_history); 
        $view->assign('order_items', $order_items); 
        $view->assign('lists', $lists); 
        $view->assign('print', $print);
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('display_info_only_product', $display_info_only_product);
        $view->assign('current_vendor_id', $id_vendor_cuser);
        $view->assign('display_block_change_order_status', $display_block_change_order_status);
        $view->assign('main_vendor_fullname', $jshopConfig->contact_firstname." ".$jshopConfig->contact_lastname);
        if ($jshopConfig->admin_show_vendors){ 
            $view->assign('order_vendors', $order_vendors);
        }
        $view->display();
    }
    
    //not finished
    function printOrder(){
        JRequest::setVar("print", 1);
        $this->show();
    }
    
    function update_one_status(){
        $this->_updateStatus(JRequest::getVar('order_id'),JRequest::getVar('order_status'),JRequest::getVar('status_id'),JRequest::getVar('notify',0),JRequest::getVar('comments',''),JRequest::getVar('include',''),1);
    }
    
    function update_status(){
        $this->_updateStatus(JRequest::getVar('order_id'),JRequest::getVar('order_status'),JRequest::getVar('status_id'),JRequest::getVar('notify',0),JRequest::getVar('comments',''),JRequest::getVar('include',''),0);        
    }    
    
    function _updateStatus($order_id, $order_status, $status_id, $notify, $comments, $include, $view_order) {
        $mainframe =& JFactory::getApplication();
        $jshopConfig = &JSFactory::getConfig();
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeChangeOrderStatusAdmin', array(&$order_id, &$order_status, &$status_id, &$notify, &$comments, &$include, &$view_order) );
        
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);        
        
        JSFactory::loadLanguageFile($order->getLang());        
        $prev_order_status = $order->order_status;
        $order->order_status = $order_status;
        $order->order_m_date = date("Y-m-d H:i:s");
        $order->store();
        //////////////////////////////////////////////////////////////////////////
       	// OSE  added
       	//////////////////////////////////////////////////////////////////////////
       	//2=>Confirmed, 1=>Pending, 4=>Refunded
        if ($order_status == "2" || $order_status == "1" || $order_status == "4")
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

        				if ($order_status == "2")
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
        
        if (in_array($order_status, $jshopConfig->payment_status_return_product_in_stock) && !in_array($prev_order_status, $jshopConfig->payment_status_return_product_in_stock)){
            $order->changeProductQTYinStock("+");            
        }
        
        if (in_array($prev_order_status, $jshopConfig->payment_status_return_product_in_stock) && !in_array($order_status, $jshopConfig->payment_status_return_product_in_stock)){
            $order->changeProductQTYinStock("-");            
        }
        
        $order_history = &JTable::getInstance('orderHistory', 'jshop');
        $order_history->order_id = $order_id;
        $order_history->order_status_id = $order_status;
        $order_history->status_date_added = date("Y-m-d H:i:s");
        $order_history->customer_notify = $notify;
        $order_history->comments = $comments;

        $order_history->store();
        
        $vendors_send_message = ($jshopConfig->vendor_order_message_type==1 || ($order->vendor_type==1 && $jshopConfig->vendor_order_message_type==2));
        $vendor_send_order = ($jshopConfig->vendor_order_message_type==2 && $order->vendor_type == 0 && $order->vendor_id);
        $admin_send_order = 1;
        if ($jshopConfig->admin_not_send_email_order_vendor_order && $vendor_send_order) $admin_send_order = 0;

        $lang = &JSFactory::getLang($order->getLang());
        $new_status = &JTable::getInstance('orderStatus', 'jshop'); 
        $new_status->load($order_status);
        $comments = ($include)?($comments):('');
        $name = $lang->get('name');
        
        $mailfrom = $mainframe->getCfg( 'mailfrom' );
        $fromname = $mainframe->getCfg( 'fromname' );
        
        $view=&$this->getView("orders_show", 'html');
        $view->setLayout("statusorder");        
        $view->assign('order', $order);
        $view->assign('comment', $comments);
        $view->assign('order_status', $new_status->$name);        
        $view->assign('vendorinfo', $vendorinfo);        
        $message = $view->loadTemplate();
            
        //message client
        if ($notify){            
            $subject = sprintf(_JSHOP_ORDER_STATUS_CHANGE_SUBJECT, $order->order_number);
            JUtility::sendMail($mailfrom, $fromname, $order->email, $subject, $message, 0);
        }
        
        //message vendors
        if ($vendors_send_message){
            $subject = sprintf(_JSHOP_ORDER_STATUS_CHANGE_SUBJECT, $order->order_number);
            $listVendors = $order->getVendors();            
            foreach($listVendors as $k=>$datavendor){
                JUtility::sendMail($mailfrom, $fromname, $datavendor->email, $subject, $message, 0);
            }
        }
        
        JSFactory::loadAdminLanguageFile();
        
        $dispatcher->trigger( 'onAfterChangeOrderStatusAdmin', array(&$order_id, &$order_status, &$status_id, &$notify, &$comments, &$include, &$view_order) );
        
        if ($view_order)
            $this->setRedirect("index.php?option=com_jshopping&controller=orders&task=show&order_id=".$order_id, _JSHOP_ORDER_STATUS_CHANGED);
        else
            $this->setRedirect("index.php?option=com_jshopping&controller=orders", _JSHOP_ORDER_STATUS_CHANGED);
    }

    function remove(){
        $cid = JRequest::getVar("cid");
        $db = &JFactory::getDBO();
        $tmp = array();
        
        JPluginHelper::importPlugin('jshoppingorder');
        $dispatcher =& JDispatcher::getInstance();
        
        $dispatcher->trigger( 'onBeforeRemoveOrder', array(&$cid) );
        
        if (count($cid)){
            foreach ($cid as $key=>$value){
                $query = "DELETE FROM `#__jshopping_orders` WHERE `order_id` = '" . $db->getEscaped($value) . "'";
                $db->setQuery($query);
                if ($db->query()){
                    $query = "DELETE FROM `#__jshopping_order_item` WHERE `order_id` = '" . $db->getEscaped($value) . "'";
                    $db->setQuery($query);
                    $db->query();
                    $query = "DELETE FROM `#__jshopping_order_history` WHERE `order_id` = '" . $db->getEscaped($value) . "'";
                    $db->setQuery($query);
                    $db->query();
                    $tmp[] = $value;
                }
            }
                        
            $dispatcher->trigger( 'onAfterRemoveOrder', array(&$cid) );
        }
        if (count($tmp)){
            $text = sprintf(_JSHOP_ORDER_DELETED_ID, implode(",",$tmp));
        }else{
            $text = "";
        }
        $this->setRedirect("index.php?option=com_jshopping&controller=orders", $text);
    }
    
    function edit(){
        $mainframe =& JFactory::getApplication();                         
        $order_id = JRequest::getVar("order_id");
        $lang = &JSFactory::getLang();
        $db = &JFactory::getDBO();
        $jshopConfig = &JSFactory::getConfig();
        $orders = &$this->getModel("orders");
        $order = &JTable::getInstance('order', 'jshop');
        $order->load($order_id);
        $name = $lang->get("name");
        
        $id_vendor_cuser = getIdVendorForCUser();
        if ($jshopConfig->admin_show_vendors && $id_vendor_cuser){
            if ($order->vendor_id!=$id_vendor_cuser) {
                $mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
                return 0;
            }
        }    

        $order_items = $order->getAllItems();
        
        $country = &JTable::getInstance('country', 'jshop');
        $countries = $country->getAllCountries();
        $select_countries = JHTML::_('select.genericlist', $countries, 'country', 'class = "inputbox"','country_id', 'name', $order->country );
        $select_d_countries = JHTML::_('select.genericlist', $countries, 'd_country', 'class = "inputbox"','country_id', 'name', $order->d_country);
        
        $client_types = array(); 
        foreach ($jshopConfig->user_field_client_type as $key => $value) {        
            $client_types[] = JHTML::_('select.option', $key, $value, 'id', 'name' );
        }
        $select_client_types = JHTML::_('select.genericlist', $client_types,'client_type','class = "inputbox" onchange="showHideFieldFirm(this.value)"','id','name', $order->client_type);

        $jshopConfig->user_field_client_type[0]="";
        $order->client_type_name = $jshopConfig->user_field_client_type[$order->client_type];
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields["address"];
        $count_filed_delivery = 0;
        foreach($config_fields as $k=>$v){
            if (substr($k, 0, 2)=="d_" && $v['display']==1) $count_filed_delivery++;
        }
        
        $pm_method = &JTable::getInstance('paymentMethod', 'jshop');
        $pm_method->load($order->payment_method_id);
        $order->payment_name = $pm_method->$name;      
        
        $order->order_tax_list = $order->getTaxExt();
        
        $view=&$this->getView("orders_edit", 'html');                
        $view->assign('config', $jshopConfig); 
        $view->assign('order', $order);  
        $view->assign('order_items', $order_items); 
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('order_id',$order_id);
        $view->assign('select_countries', $select_countries);
        $view->assign('select_d_countries', $select_d_countries);
        $view->assign('select_client_types', $select_client_types);

        $view->display();
    }
    
    function save(){
        $jshopConfig = &JSFactory::getConfig();
        $post = JRequest::get('post');
        $order = &JTable::getInstance('order', 'jshop');
        $order_id = $post['order_id'];
        $db = &JFactory::getDBO();
        JPluginHelper::importPlugin('jshoppingadmin');
        $dispatcher =& JDispatcher::getInstance();
        
        $n = JRequest::getVar('amount_tax_items');        
        $a = null;
        if (!$jshopConfig->hide_tax){
            $post['order_tax'] = 0;
            for($i = 1; $i<=$n; $i++) {
                $a[number_format(JRequest::getVar('tax_percent_'.$i,''),2)] = JRequest::getVar('tax_value_'.$i,'');     
                $post['order_tax'] += JRequest::getVar('tax_value_'.$i,'');   
            } 
            $post['order_tax_ext'] = serialize($a);
            $post['order_tax'] = number_format($post['order_tax'],2);  
        }
        
        $dispatcher->trigger( 'onBeforeSaveOrder', array(&$post) );
        
        $k = JRequest::getVar('amount_order_items');
        if ($k){
            for($i = 1; $i<=$k; $i++) {
                $product_name       = JRequest::getVar('product_name_'.$i,'');
                $product_ean        = JRequest::getVar('product_ean_'.$i,'');
                $product_item_price = JRequest::getVar('product_item_price_'.$i,0);
                $product_quantity   = JRequest::getVar('product_quantity_'.$i,0);
                $order_item_id   = JRequest::getVar('order_item_id_'.$i); 
                $query = 'UPDATE #__jshopping_order_item SET `product_name`="'.$product_name.'",`product_ean`="'.$product_ean.'",`product_item_price`="'.$product_item_price.'",`product_quantity`="'.$product_quantity.'" WHERE `order_id`='.$order_id.' AND `order_item_id`='.$order_item_id;          
                $db->setQuery($query);
                $db->query();    
            }
        }

        if (!$order->bind($post)) {
            JError::raiseWarning("",_JSHOP_ERROR_BIND);
            $this->setRedirect("index.php?option=com_jshopping&controller=orders");
        }

        if (!$order->store()) {
            JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE);
            $this->setRedirect("index.php?option=com_jshopping&controller=orders");
        }    
        
        if ($jshopConfig->order_send_pdf_client || $jshopConfig->order_send_pdf_admin){
            $order->load($post['order_id']);
            $order->products = $order->getAllItems();
            JSFactory::loadLanguageFile($order->getLang());
            $lang = &JSFactory::getLang($order->getLang());
            
            $order->order_date = strftime($jshopConfig->store_date_format, strtotime($order->order_date));
            $order->order_tax_list = $order->getTaxExt();
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
            
            include_once(JPATH_SITE . "/components/com_jshopping/lib/generete_pdf_order.php");
            $order->pdf_file = generatePdf($order);
            $order->insertPDF();
        }
        
        $dispatcher->trigger( 'onAfterSaveOrder', array(&$order) );
        
        $this->setRedirect("index.php?option=com_jshopping&controller=orders");       
    }

}
?>