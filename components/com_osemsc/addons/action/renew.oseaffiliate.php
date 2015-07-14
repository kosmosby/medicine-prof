<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewOSEAffiliate
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
		
		if(oseObject::GetValue($config, 'oseaffiliate_enabled',true))
		{
			if(empty($curOrderParams->oseaffiliateID) || empty($curOrderParams->osebannerID))
			{
				return true;
			}
			else
			{
				//$memParams->oseaffiliateID = $curOrderParams->oseaffiliateID;
				//$memParams->osebannerID = $curOrderParams->osebannerID;
			}
		
			$file = JPATH_ADMINISTRATOR.DS.'com_ose_affiliates'.DS.'helpers'.DS.'oseaffiliates_helper.php';
			if ( JFile::exists($file) )
			{
				require_once($file);
			}
			else
			{
				return false;
			}
		
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
				
			$osePaymentCurrency = $curOrder->payment_currency;
		
			$options = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			$product = "{$node->title}-{$options[0]['title']}";
		
			$helper = new oseAffiliatesHelper();
			$helper->setTotalCost($curOrder->payment_price);
			$helper->setOrderId($order_id);
			$helper->setProduct($product);
			$helper->setCurrency($osePaymentCurrency);
			$helper->setAffiliateId($curOrderParams->oseaffiliateID);
			$helper->setBannerId($curOrderParams->osebannerID);
			if(!$helper->createSale())
			{
				return false;
			}
		}
		
		return $result;
	}
	
	public static function activate($params)
	{
		return self::renew($params);
	}
}
?>