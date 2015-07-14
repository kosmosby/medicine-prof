<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewPap
{
	public static function renew($params)
	{
		if(!class_exists('oseMscPublic'))
		{
			require_once(OSEMSC_F_HELPER.DS.'oseMscPublic.php');
		}
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.Order');
			
			return $result;
		}
		//unset($params['allow_work']);
		
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
		$recurrence_times = ($curOrderParams->has_trial)?oseObject::getValue($curOrderParams,'recurrence_times',0)+1:oseObject::getValue($curOrderParams,'recurrence_times',0);
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');
		
		$memParams = $member->getMemberInfo($msc_id,'obj')->memParams;
		$memParams = oseJSON::decode($memParams);
		
		$usePAPUserId = false;
		require_once(OSEMSC_B_LIB.DS.'PapApi.class.php');
		
		if(!oseObject::GetValue($memParams,'pap_visitorid',oseObject::getValue($curOrderParams,'pap_visitorid',false)))
		{
			
			if(oseObject::GetValue($memParams,'pap_userid',false))
			{
				$usePAPUserId = true;
			}	
			else
			{
				// check whether has table in old version 4.4
				$tableList = $db->getTableList();
				if(in_array($db->replacePrefix('#__osemsc_affiliate_tracking'),$tableList))
				{
					$session = new Gpf_Api_Session($oseMscConfig->pap_url.'/scripts/server.php');
		
					if(!$session->login(oseObject::getValue($oseMscConfig,'pap_username'), oseObject::getValue($oseMscConfig,'pap_password'))) {
						$result['success'] = false;
						$result['title'] = JText::_('Error');
						$result['content'] = $session->getMessage();
						return $result;
					}
					$request = new Pap_Api_TransactionsGrid($session);
					// set filter
					//$request->addFilter('dateinserted', Gpf_Data_Filter::DATERANGE_IS, Gpf_Data_Filter::RANGE_THIS_YEAR);
					$request->addFilter('orderid', Gpf_Data_Filter::EQUALS, $order_id);
					$request->setLimit(0, 30);
					$request->setSorting('orderid', false);
					$request->sendNow();
					$grid = $request->getGrid();
					$recordset = $grid->getRecordset();
					
					if($grid->getTotalCount() > 0)
					{
						$usePAPUserId = true;
						foreach($recordset as $rec) 
						{	
					  		$memParams->pap_userid = $rec->get('userid');
					  		break;
						}
					}
					else
					{
						return $result;
					}
				}
				else
				{
					return $result;
				}
			}
		}
		else
		{
			$memParams->pap_visitorid = oseObject::GetValue($memParams,'pap_visitorid',oseObject::getValue($curOrderParams,'pap_visitorid'));
		}
		//oseObject::GetValue($memParams,'pap_visitorid',oseObject::getValue($curOrderParams,'pap_visitorid',false));
		
		$memParams->first_ip = oseObject::getValue($memParams,'first_ip',oseObject::getValue($curOrderParams,'first_ip',oseMscPublic::getIP()));
		$memParams->pap_url = OSEMSC_B_LIB;
		$memParams_encode = oseJSON::encode($memParams);
			
		$query = " UPDATE `#__osemsc_member`"
				." SET `params` = ".$db->Quote($memParams_encode)
				." WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$member_id}'"
				;
		$db->setQuery($query);
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.2');
			return $result;
		}
		
		try {
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

			$options = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			
	        $saleTracker = new Pap_Api_SaleTracker($oseMscConfig->pap_url.'/scripts/sale.php');
			$saleTracker->setAccountId(oseObject::getValue($oseMscConfig,'pap_account_id','default1'));
			$sale1 = $saleTracker->createSale();
			if($usePAPUserId)
			{
				$sale1->setAffiliateID($memParams->pap_userid);
			}
			else
			{
				$saleTracker->setVisitorId($memParams->pap_visitorid);
			}
			
			$sale1->setTotalCost($curOrder->payment_price);
			$sale1->setOrderID($order_id);
			$sale1->setProductID("{$node->title}-{$options[0]['title']}");
			$sale1->setData1($userInfo->email);
			$sale1->setData2($userInfo->jname);
			$sale1->setData3($memParams->first_ip);
			$sale1->setData4($curOrder->payment_serial_number);
			$sale1->setData5("{$curOrder->order_id}-{$node->title}-{$options[0]['title']}-#recurrence:{$recurrence_times}");
			$saleTracker->register();
			
			return $result;
	    } catch (Exception $e) {
	    	return $result;
	    }
	}
	
	public static function activate($params)
	{
		return self::renew($params);
	}
}
?>