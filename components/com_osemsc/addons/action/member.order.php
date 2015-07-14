<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberOrder
{
	public function getOrders()
	{
		$db = oseDB::instance();
		$my = JFactory::getUser();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$where = array();

		$where[] = "o.user_id = '{$my->id}'";
		$where[] = "o.entry_type IN ('msc','msc_list')";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT u.username,u.name, o.* "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				." LIMIT {$start},{$limit} "
				;

		$db->setQuery($query);
		//oseExit($db->_sql);
		//oseExit($db->getQuery());
		$items = oseDB::loadList();

		$gw = oseRegistry::call('payment')->getInstance('GateWay');
		foreach($items as $key => $item)
		{
			$gwInfo = $gw->getGWInfo(oseObject::getValue($item,'payment_method'));
			if(oseObject::getValue($gwInfo,'is_cc',0) == 1)
			{
				$items[$key] = oseObject::setValue($item,'payment_method','Credit Card');
			}
			if ($item['order_status']=='confirmed' || $item['order_status']=='pending')
			{
				$items[$key]=oseObject::setValue($item,'order_status',JText::_(strtoupper($items[$key]['order_status'])));
			}
			if (isset($item['payment_method']))
			{
				$items[$key]=oseObject::setValue($item,'payment_method',JText::_(strtoupper($items[$key]['payment_method'])));
			}

			$globalConfig = oseRegistry::call('msc')->getConfig('global','obj');
			if(!empty($globalConfig->DateFormat))
			{
				$items[$key]= oseObject::setValue($item,'create_date',date($globalConfig->DateFormat,strtotime($item['create_date'])));
			}
		}

		$result = array();
		$result['total'] = $this->getTotal();
		$result['results'] = $items;
		return $result;
	}
	
	public function getOrdersMobile()
	{
		$db = oseDB::instance();
		$my = JFactory::getUser();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',999);

		$where = array();

		$where[] = "o.user_id = '{$my->id}'";
		$where[] = "o.entry_type IN ('msc','msc_list')";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT u.username,u.name, o.* "
		." FROM `#__osemsc_order` AS o "
		." INNER JOIN `#__users` AS u ON u.id = o.user_id"
		. $where
		." LIMIT {$start},999 "
		;

		$db->setQuery($query);
		//oseExit($db->_sql);
		//oseExit($db->getQuery());
		$items = oseDB::loadList();

		$gw = oseRegistry::call('payment')->getInstance('GateWay');
		foreach($items as $key => $item)
		{
			$gwInfo = $gw->getGWInfo(oseObject::getValue($item,'payment_method'));
			if(oseObject::getValue($gwInfo,'is_cc',0) == 1)
			{
				$items[$key] = oseObject::setValue($item,'payment_method','Credit Card');
			}
			if ($item['order_status']=='confirmed' || $item['order_status']=='pending')
			{
				$items[$key]=oseObject::setValue($item,'order_status',JText::_(strtoupper($items[$key]['order_status'])));
			}
			if (isset($item['payment_method']))
			{
				$items[$key]=oseObject::setValue($item,'payment_method',JText::_(strtoupper($items[$key]['payment_method'])));
			}

			$globalConfig = oseRegistry::call('msc')->getConfig('global','obj');
			if(!empty($globalConfig->DateFormat))
			{
				$items[$key]= oseObject::setValue($item,'create_date',date($globalConfig->DateFormat,strtotime($item['create_date'])));
			}
		}

		$result = array();
		$result['total'] = $this->getTotal();
		$result['results'] = $items;
		return $result;
	}

	private function getTotal()
	{
		$db = oseDB::instance();
		$my = JFactory::getUser();

		$where = array();

		$where[] = "o.user_id = '{$my->id}'";
		$where[] = "o.entry_type IN ('msc','msc_list')";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				;
		$db->setQuery($query);

		$result = $db->loadResult();
		return $result;
	}

	function orderView()
	{
		$db = oseDB::instance();

		$order_id = JRequest::getInt('order_id',0);

		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$orderInfo = oseRegistry::call('payment')->getOrder($where,'obj');

		$receipt = oseRegistry::call('member')->getReceipt($orderInfo);

		return $receipt;
	}

    function orderViewPDF()
    {
   		$l = null;
    	require_once(OSEMSC_F_PATH.DS.'libraries'.DS.'tcpdf'.DS.'tcpdf.php');
    	require_once(OSEMSC_F_PATH.DS.'libraries'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');

    	$order_id = JRequest::getInt('order_id',0);

    	$my = JFactory::getUser();

		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$where[] = " `user_id` = {$my->id}";

		$orderInfo = oseRegistry::call('payment')->getOrder($where,'obj');

		if(empty($orderInfo))
		{
			$result = array();
			$result['title'] = 'Error';
			$result['content'] = 'Error';

			oseExit('Error');
		}

		$receipt = oseRegistry::call('member')->getReceipt($orderInfo);


		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('OSEMSC');
		$pdf->SetTitle('Invoice #'.$order_id);
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('invoice');


		// set default header data
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->SetMargins(30, 18, 30);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->AddPage();

		ob_get_clean();
		//oseExit($receipt->body);
		$css = file_get_contents(JPATH_SITE.DS."components/com_osemsc/assets/css/msc5_invoice.css");
		$receipt->body="<style>".$css."</style>".$receipt->body;

		$pdf->WriteHTML($receipt->body, true, false, true , false , "");
		ob_end_clean();
		$pdf->Output("Invoice-#{$order_id}.pdf", "I");

		oseExit();


    	/*
    	//$receipt = self::orderView();
    	$order_id = JRequest::getInt('order_id');

    	$app = JFactory::getApplication('SITE');
    	//oseExit('dfdf');
    	$app->redirect( JRoute::_('index.php?option=com_osemsc&view=member&format=pdf&memberTask=generateOrderView&order_id='.$order_id));
    	*/
    }

    public function getConfirmedOrders()
	{
		$db = oseDB::instance();
		$my = JFactory::getUser();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$member = oseRegistry::call('member');
		$member->instance($my->id);
		$memberships = $member->getMemberOwnedMscInfo(false,1,'obj');

		/*$order_ids = array();
		foreach($memberships as $membership)
		{
			$memParams = oseJson::decode($membership->params);
			if($memParams->payment_mode == 'a')
			{
				$order_ids[$memParams->order_id] = $db->Quote($memParams->order_id);
			}

		}

		$order_ids = '('.implode(',',$order_ids).')';*/

		$where = array();
		//$where[] = "o.order_id IN {$order_ids}";
		$where[] = "o.payment_mode = 'a'";
		$where[] = "o.order_status = 'confirmed'";
		$where[] = "o.user_id = '{$my->id}'";
		//$where[] = "`order_status` = 'confirmed'";
		//$where[] = "`payment_mode` = 'a'";
		$where = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				. $where
				;

		$db->setQuery($query);
		$total = $db->loadResult();

		$title = JText::_('INVOICE_ID').': '.JText::_('ORDER').' ';
		$query = " SELECT o.* , CONCAT('{$title}', o.order_id) AS invoice"
				." FROM `#__osemsc_order` AS o "
				. $where
				." ORDER BY o.order_id DESC"
				." LIMIT {$start},{$limit} "
				;

		$db->setQuery($query);

		$items = oseDB::loadList();

		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;

		return $result;
	}

	function getOrderMembershipTable()
	{
		$order_id = JRequest::getInt('order_id',0);
		$db = oseDB::instance();
		$my = JFactory::getUser();

		$member = oseRegistry::call('member');

		$memEmail = $member->getInstance('Email');

		$tableHtml = $memEmail->generateOrderTable($order_id,$my->id);
		$tableHtml = preg_replace('/100%/','90%',$tableHtml,1);
		oseExit($tableHtml);
	}

	function getOrder()
	{
		$order_id = JRequest::getInt('order_id',0);

		$where = array();
		$where[] = "`order_id` = {$order_id}";
		//$where[] = "`order_status` = 'confirmed'";
		//$where[] = "`payment_mode` = 'a'";

		$payment = oseRegistry::call('payment');
		$order = $payment->getOrder($where,'obj');

		$result = array();
		$result['total'] = empty($order)?0:1;
		$result['result'] = $order;
		$result['success'] = true;

		return $result;
	}

	function cancelOrder()
	{
		$result = array();
		$result['success']= false;
		$result['title']= 'Error';
		$result['content']= 'Error';

		$db = oseDB::instance();

		$msc= oseRegistry :: call('msc');
		$member = oseRegistry::call('member');
		$email = oseRegistry::call('member')->getInstance('Email');
		$payment= oseRegistry :: call('payment');
		$paymentOrder= $payment->getInstance('Order');

		$my = JFactory::getUser();
		$user_id = $my->id;
		$member->instance($user_id);

		$order_id = JRequest::getInt('order_id',0);

		$member->instance($user_id);
		$memberships = $member->getMemberOwnedMscInfo(false,1,'obj');

		$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `user_id` = '{$user_id}' AND `order_status` = 'confirmed' AND `payment_mode`='a'"
				;

		$db->setQuery($query);
		$list = oseDB::loadList('obj');
		$order_ids = array();
		foreach($list as $oItem)
		{
			//$memParams = oseJson::decode($membership->params);
			$order_ids[$oItem->order_id] = $oItem->order_id;
		}

		if( !in_array( $order_id,$order_ids ) )
		{
			$result['success']= false;
			$result['title']= 'Error';
			$result['content']= JText :: _('Error No this authority: ').$order_id;
		}

		$where= array();
		$where[]= "`order_id` = ".$db->Quote($order_id);
		$order = $payment->getOrder($where, 'obj');

		//$result['success']= true;
		$result['payment_mode']= $order->payment_mode;
		$result['payment_method'] = $order->payment_method;

		//$msc_id = $order->entry_id;

		switch($order->payment_method)
		{
			case('paypal_cc') :
				if($order->payment_mode == 'a') {

					$config= oseMscConfig :: getConfig('payment', 'obj');

					$updated = $paymentOrder->PaypalAPIDeleteProfile($order->payment_serial_number, substr($order->order_number, 0, 20), $user_id);

					if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));
						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';
						$result['content']= JText :: _('ERROR_CANCELLING_SUB_PLAN').' '.urldecode($order->payment_serial_number). "<br />".JText::_(' Error response from server: '). urldecode($updated['text']);
					}
				}
			break;

			case('paypal') :
				if($order->payment_mode == 'a') {

					$config= oseMscConfig :: getConfig('payment', 'obj');

					if($config->paypal_mode == 'paypal_express')
					{
						$test_mode= $config->paypal_testmode;
						$paypal_email = $config->paypal_email;
						if($test_mode == true)
						{
							$url= "https://www.sandbox.paypal.com/cgi-bin/webscr";
						} else {
							$url= "https://www.paypal.com/cgi-bin/webscr";
						}
						$url = $url.'?cmd=_subscr-find&alias='.$paypal_email;

						$result['success']= true;
						$result['payment_method'] = 'paypal';
						$result['paypal']= 'ipn';
						$result['url']= $url;
						return $result;
					}
					else
					{
						//$result['payment_method'] = 'paypal_pro';
						$updated= $paymentOrder->PaypalAPIDeleteProfile($order->payment_serial_number, substr($order->order_number, 0, 20), $user_id);
						if ($updated['success']==true)
						{
							$paymentOrder->updateOrder($order->order_id,'cancelled');
							$email->sendCancelOrderEmail(array('orderInfo'=>$order));
							$result['success']= true;
							$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
							$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
						}
						else
						{
							$result['success']= false;
							$result['title']= 'Error';
							$result['content']= JText :: _('ERROR_CANCELLING_SUB_PLAN').' '.urldecode($order->payment_serial_number). "<br />".JText::_(' Error response from server: '). urldecode($updated['text']);
						}

					}
				}
				break;

			case('authorize') :
				if($order->payment_mode == 'a') {
					$updated= $paymentOrder->AuthorizeARBDeleteProfile($order->payment_serial_number, substr($order->order_number, 0, 20), $user_id);
					if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));

						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';

						if (strpos($order->payment_serial_number, "_")>0)
						{
							$tmpUID = explode("_", $order->payment_serial_number);
							if ($tmpUID[0] == $order->user_id)
							{
								$result['content']= JText :: _('CREDIT_CARD_EXPIRE_BEFORE_SUB_ENDS');
							}
							else
							{
								$result['content']= JText :: _('ERROR_CANCELLING_SUB_PLAN').' '.urldecode($order->payment_serial_number);
							}
						}
						else
						{
							$result['content']= $updated['text'];
						}
					}
				}
				break;

			case('eway'):
				if($order->payment_mode == 'a') {
					$updated= $paymentOrder->eWayDeleteProfile($order);
					if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));

						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';
						$result['content']= $updated['text'];//JText :: _('Error cancelling subscription plan! Please contact the web administrator and quote this profile ID: ').urldecode($order->payment_serial_number);
					}
				}
				break;

			case('beanstream') :
				if($order->payment_mode == 'a') {
					$updated= $paymentOrder->BeanStreamDeleteProfile($order);
					if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));

						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';
						$result['content']= $updated['text'];//JText :: _('Error cancelling subscription plan! Please contact the web administrator and quote this profile ID: ').urldecode($order->payment_serial_number);
					}
				}
				break;
			case('epay') :
				if($order->payment_mode == 'a') {
					require_once(OSEMSC_B_PATH.DS.'libraries'.DS.'epaysoap.php');
					$epay = new EpaySoap();
					$config = oseMscConfig::getConfig('payment','obj');
					$merchantnumber = $config->epay_merchantnumber;
					$subscriptionid = $order->payment_serial_number;
					$updated= $epay->deleteSubscription($merchantnumber, $subscriptionid);
					if($updated['deletesubscriptionResult'] == true && $result['epayresponse'] == '-1')

					//$updated= $paymentOrder->BeanStreamDeleteProfile($order);
					//if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));

						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';
						$result['content']= $updated['text'];//JText :: _('Error cancelling subscription plan! Please contact the web administrator and quote this profile ID: ').urldecode($order->payment_serial_number);
					}
				}
				break;
				
			case('2co') :
				if($order->payment_mode == 'a') {	
					$updated= $paymentOrder->twoCheckoutDeleteProfile($order);
					if ($updated['success']==true)
					{
						$email->sendCancelOrderEmail(array('orderInfo'=>$order));

						$result['success']= true;
						$result['title']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY_TITLE');
						$result['content']= JText :: _('SUB_PLAN_CANCEL_SUCCESSFULLY');
					}
					else
					{
						$result['success']= false;
						$result['title']= 'Error';
						$result['content']= $updated['text'];//JText :: _('Error cancelling subscription plan! Please contact the web administrator and quote this profile ID: ').urldecode($order->payment_serial_number);
					}
				}
			break;	
			default :
				$orderItems = $paymentOrder->getOrderItems($order->order_id,'obj');
				foreach($orderItems as $key=>$orderItem)
				{
					if($orderItem->entry_type == 'msc')
					{
						$msc_id = $orderItem->entry_id;

						$params= oseRegistry :: call('member')->getAddonParams($msc_id, $user_id, $order_id);
						$updated= $msc->runAddonAction('member.msc.cancelMsc', $params);
						if(!$updated['success']) {
							return $updated;
						}
					}
				}

				break;
		}

		if ($result['success']==true)
		{
			$orderItems = $paymentOrder->getOrderItems($order->order_id,'obj');
			foreach($orderItems as $key=>$orderItem)
			{
				if($orderItem->entry_type == 'msc')
				{
					$msc_id = $orderItem->entry_id;

					$arr = array('allow_work'=>true,'msc_id'=>$msc_id,'member_id'=>$user_id,'master'=>true);
					oseMscAddon::runAction('join.history.manualCancelOrder', $arr);
				}
			}

	    	$paymentOrder->updateOrder($order_id, 'cancelled', $params= array('payment_mode'=>'a'));
	    	//$paymentOrder->updateMembership($msc_id, $user_id, $order_id, 'm');
		}
		return $result;
	}
}
?>