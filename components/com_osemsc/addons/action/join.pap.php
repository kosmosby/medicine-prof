<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinPap
{
	public static function save($params)
	{
		if(!class_exists('oseMscPublic'))
		{
			require_once(OSEMSC_F_HELPER.DS.'oseMscPublic.php');
		}
		$result = array();
		$result['success'] = true;
		//oseExit($params);
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.PAP');
			
			return $result;
		}
		unset($params['allow_work']);
		
		/*  Offline payments should be counted as well; removed from v 6.0.5
		if( $params['join_from'] != 'payment')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done Join.Order');
			
			return $result;
		}
		*/
		$oseMscConfig = oseRegistry::call('msc')->getConfig('thirdparty','obj');
		
		if(empty($oseMscConfig->pap_enable))
		{
			return $result;
		}
		
		if(empty($oseMscConfig->pap_url))
		{
			return $result;
		}
		
		$db = oseDB::instance();
		//$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];
		
		$where = array();
		$where[] = "order_id = {$order_id}";
		
		$payment = oseRegistry::call('payment');
		$curOrder = $payment->getOrder($where,'obj');
		$curOrderParams = oseJson::decode($curOrder->params);
		$recurrence_times = ($curOrderParams->has_trial)?$curOrderParams->recurrence_times+1:$curOrderParams->recurrence_times;
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');
		
		$memParams = $member->getMemberInfo($msc_id,'obj')->memParams;
		$memParams = oseJSON::decode($memParams);
		$memParams->first_ip = oseObject::getValue($curOrderParams,'first_ip');;
		
		if(empty($curOrderParams->pap_visitorid))
		{
			return $result;
			//$memParams->pap_visitorid = 'paypal';
		}
		else
		{
			$memParams->pap_visitorid = $curOrderParams->pap_visitorid;
			//$memParams->pap_visitorid = $_COOKIE['PAPVisitorId'];
		}
		
		$memParams_encode = oseJSON::encode($memParams);
			
		$query = " UPDATE `#__osemsc_member`"
				." SET params = ".$db->Quote($memParams_encode)
				." WHERE msc_id = {$msc_id} AND member_id = {$member_id}"
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.2');
			return $result;
		}
			
		try {  
			
			require_once(OSEMSC_B_LIB.DS.'PapApi.class.php');
			
	        $saleTracker = new Pap_Api_SaleTracker($oseMscConfig->pap_url.'/scripts/sale.php');
	        $saleTracker->setAccountId(oseObject::getValue($oseMscConfig,'pap_account_id','default1'));
	        $saleTracker->setVisitorId($memParams->pap_visitorid);
	        $sale1 = $saleTracker->createSale();
			
			$msc = oseRegistry::call('msc');
			
			$where = array();
			$where[] = "`order_item_id` = '{$order_item_id}'";
			
			$curOrderItem = $payment->getInstance('Order')->getOrderItem($where,'obj');
			$curOrderItemParams = oseJson::decode($curOrderItem->params);
			
			$node = $msc->getInfo($msc_id,'obj');
			$paymentInfos = $msc->getExtInfo($msc_id,'payment');
			foreach($paymentInfos as $key => $paymentInfo)
			{
				if($key != $curOrderItemParams->msc_option)
				{
					unset($paymentInfos[$key]);
				}
			}
			$cart = oseMscPublic::getCart();

	    	$osePaymentCurrency = $cart->get('currency');

	    	$items = $cart->get('items');
			$options = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			
			$sale1->setTotalCost($curOrder->payment_price);
			$sale1->setOrderID($order_id);
			$sale1->setProductID("{$node->title}-{$options[0]['title']}");
			$sale1->setData1($userInfo->email);
			$sale1->setData2($userInfo->jname);
			$sale1->setData3($memParams->first_ip);
			$sale1->setData4($curOrder->payment_serial_number);
			$sale1->setData5("{$curOrder->order_id}-{$node->title}-{$options[0]['title']}-#recurrence:{$recurrence_times}");
			$saleTracker->register();
			
			//$memParams->pap_affiliate_id = $sale1->getAffiliateId();
	        //$memParams->pap_campaign_id = $sale1->getCampaignId();
	        //$memParams->pap_banner_id = $sale1->getBannerId();
			// Order problem for system add
			return $result;
	    } catch (Exception $e) {
	    	return $result;
	    }
	}
	
	public static function cancel($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error Join.PAP');
			
			return $result;
		}
		unset($params['allow_work']);
		
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		
		$oseMscConfig = oseRegistry::call('msc')->getConfig('thirdparty','obj');
		
		if(empty($oseMscConfig->pap_enable))
		{
			return $result;
		}
		
		require_once(OSEMSC_B_LIB.DS.'PapApi.class.php');
		$session = new Gpf_Api_Session($oseMscConfig->pap_url.'/scripts/server.php');
		if(empty($oseMscConfig->pap_username) || empty($oseMscConfig->pap_password))
		{
			return $result;
		}
		if(!$session->login(oseObject::getValue($oseMscConfig,'pap_username'), oseObject::getValue($oseMscConfig,'pap_password'))) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = $session->getMessage();
			return $result;
		}
		
		//loading transaction by orderid
		$transaction = new Pap_Api_Transaction($session);
		$transaction->setOrderId($order_id);
		$transaction->setType('S');
		//$transaction->setAccountId(oseObject::getValue($oseMscConfig,'pap_account_id','default1'));
		try {
			if(!$transaction->load()) {
		    	echo 'Cannot load transaction, error: '.$transaction->getMessage();
		  	} else {
			    $response = $transaction->refund('note for affiliate'); // or $transaction->chargeBack('note for affiliate');
			    if ($response->isError()) {
					$result['success'] = false;
					$result['title'] = JText::_('Error');
					$result['content'] = JText::_("PAP.cancel.error1");
			    } else {
			        //echo 'chargeback OK';
			       
					$result['success'] = true;
					$result['title'] = JText::_('Done');
					$result['content'] = JText::_("Done");
			    } 
		  	}
		} catch (Exception $e) {
		  	$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_("PAP.cancel.error2");
		}
		
		
		
		return $result;
	}
	
	
	public function remove($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		return $result;
		
	}
	
}
?>