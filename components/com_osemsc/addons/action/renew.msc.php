<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewMsc
{
	public static function renew($params)
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
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = oseObject::getValue($params,'order_item_id');
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Renew Msc: No Msc ID");
			return $result;
		}
		
		$where = array();
		$where[] = "order_item_id = {$order_item_id}";
		
		$payment = oseRegistry::call('payment');
		
		$curOrder = $payment->getOrderItem($where,'obj');
		
		$curOrderParams = oseJson::decode($curOrder->params);
		
		$msc = oseRegistry::call('msc');
		
		$exts = $msc->getExtInfo($msc_id,'payment','obj');
		$ext = $exts->{$curOrderParams->msc_option};
		
		if(empty($ext))
		{
			$result['success'] = false;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Fail Renewing Membership.');
			
			return $result; 
		}
		
		$config = $msc->getConfig('global','obj');
		$manual_renew_mode = empty($ext->manual_renew_mode)?$config->manual_renew_mode:$ext->manual_renew_mode;
		$manual_to_automatic_mode = empty($ext->manual_to_automatic_mode)?$config->manual_to_automatic_mode:$ext->manual_to_automatic_mode;
		//oseExit($manual_renew_mode.'&'.$manual_to_automatic_mode);
		//oseExit($curOrder);
		//echo $manual_renew_mode."<br>";
		//oseExit($ext);
		if($curOrder->payment_mode == 'm')
		{
			$updated = self::renewInManualMode($msc_id,$member_id,$manual_renew_mode,$curOrder,$ext);
		}
		else // current order payment mode is a!
		{
			$member = oseRegistry::call('member');
			$member->instance($member_id);
			
			$memInfo = $member->getRenewMscInfo($msc_id,'obj');
			//oseExit(oseDB::instance()->_sql);
			$memParams = oseJson::decode($memInfo->params);
			
			if(empty($memParams->order_items_id))
			{
				$params['allow_work'] = true;
				oseMscAddon::runAction('join.order.save',$params,true,false);
			}
			
			$where = array();
			$where[] = "order_item_id = {$memParams->order_item_id}";
		
			$lastOrder = $payment->getOrderItem($where,'obj');
			
			if($lastOrder->payment_mode == 'm') // m 2 a
			{
				$updated = self::renewInAutoMaticMode($msc_id,$member_id,$manual_to_automatic_mode,$curOrder,$ext);
			}
			else // a 2 a extend
			{
				$updated = self::renewInAutoMaticMode($msc_id,$member_id,'extend',$curOrder,$ext);
			}
		}
		
		return $updated;
	}
	
	public static function activate($params)
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
		
		//oseExit($params);
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Renew Msc: No Msc ID");
			return $result;
		}
		
		
		$where = array();
		$where[] = "order_item_id = {$order_item_id}";
		
		$payment = oseRegistry::call('payment');
		
		$curOrder = $payment->getOrderItem($where,'obj');
		
		$curOrderParams = oseJson::decode($curOrder->params);
		
		$msc = oseRegistry::call('msc');
		
		$exts = $msc->getExtInfo($msc_id,'payment','obj');
		$ext = $exts->{$curOrderParams->msc_option};
		
		if(empty($ext))
		{
			$result['success'] = false;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Fail Renewing Membership.');
			
			return $result; 
		}
		
		$config = $msc->getConfig('global','obj');
		$manual_renew_mode = empty($ext->manual_renew_mode)?$config->manual_renew_mode:$ext->manual_renew_mode;
		$manual_to_automatic_mode = empty($ext->manual_to_automatic_mode)?$config->manual_to_automatic_mode:$ext->manual_to_automatic_mode;
		
		
		if($curOrder->payment_mode == 'm')
		{
			$updated = self::activateInManualMode($msc_id,$member_id,$curOrder,$ext);
		}
		else // current order payment mode is a!
		{
			$updated = self::activateInAutoMaticMode($msc_id,$member_id,$curOrder,$ext);
		}
		
		return $updated;
	}
	
	public static function cancel($params)
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
	
	private static function renewInManualMode($msc_id,$member_id,$renew_mode,$orderInfo,$ext)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		
		$memInfo = $member->getMemberInfo($msc_id,'obj');
		
		$orderParams = oseJSON::decode($orderInfo->params);
		
		if($renew_mode == 'renew')
		{
			if(oseObject::getValue($orderParams,'recurrence_mode','period') == 'period')
			{
				$recurrence_num = $orderParams->p3;
				$recurrence_unit = $orderParams->t3;
				
				$set = array();
				$start_date = oseHTML::getDateTime();
				$start_date = $db->Quote($start_date);
				//$set['start_date'] = $start_date;
				
				
				
				if(!empty($orderParams->eternal))
				{
					$expired_date = '0000-00-00 00:00:00';
					$set['expired_date'] = $db->Quote($expired_date);
					$set['eternal'] = 1;
				}
				else
				{
					$set['expired_date'] = " DATE_ADD({$start_date},INTERVAL {$recurrence_num} {$recurrence_unit}) ";
				}
				
				$values = array();
				foreach($set AS $key => $value)
				{
					$values[] = $key.'='.$value;
				}
				$values = implode(',',$values);
				
				$query = " UPDATE `#__osemsc_member` "
						." SET  $values"
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				//oseExit($db->_sql);
				
				if(!oseDB::query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
					
					return $result;
				}
			}elseif(oseObject::getValue($orderParams,'recurrence_mode','period') == 'fixed')
			{
				
				$start_date = $orderParams->start_date;
				$start_date = $db->Quote($start_date);
				
				$expired_date = $orderParams->expired_date;
				$expired_date = $db->Quote($expired_date);
				
				$query = " UPDATE `#__osemsc_member` "
						." SET  start_date = {$start_date} , expired_date = {$expired_date}"
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				//oseExit($db->_sql)
				
				if(!oseDB::query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_('Can Not Update The Start Date AND Expired Date of Member.');
					
					return $result;
				}
			}
			
			return $result;
		}
		else // extend mode:  only active member can use it
		{
			
			if(oseObject::getValue($orderParams,'recurrence_mode','period') == 'period')
			{
				$recurrence_num = $orderParams->p3;
				$recurrence_unit = $orderParams->t3;
				
				if(($memInfo->expired_date == '0000-00-00 00:00:00') || empty($memInfo->expired_date))
				{
					$start_date = 'NOW()';
				}
				else
				{
					$start_date = $db->Quote($memInfo->expired_date);
				}
				
				if(!empty($orderParams->eternal))
				{
					$expired_date = '0000-00-00 00:00:00';
					$set['expired_date'] = $db->Quote($expired_date);
					$set['eternal'] = 1;
				}
				else
				{
					$set['expired_date'] = " DATE_ADD({$start_date},INTERVAL {$recurrence_num} {$recurrence_unit}) ";
					//oseExit($set['expired_date']);
				}
				
				$values = array();
				foreach($set AS $key => $value)
				{
					$values[] = $key.'='.$value;
				}
				$values = implode(',',$values);
				
				$query = " UPDATE `#__osemsc_member` "
						." SET  {$values}"
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				//oseExit($db->_sql);
				
				if(!oseDB::query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
					
					return $result;
				}
			}elseif(oseObject::getValue($orderParams,'recurrence_mode','period') == 'fixed')
			{
				
				$start_date = $orderParams->start_date;
				$start_date = $db->Quote($start_date);
				
				$expired_date = $orderParams->expired_date;
				$expired_date = $db->Quote($expired_date);
				
				$query = " UPDATE `#__osemsc_member` "
						." SET  start_date = {$start_date} , expired_date = {$expired_date}"
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				//oseExit($db->_sql)
				
				if(!oseDB::query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_('Can Not Update The Start Date AND Expired Date of Member.');
					
					return $result;
				}
			}
			
			return $result;
		}
	}
	
	private static function renewInAutomaticMode($msc_id,$member_id,$renew_mode,$orderInfo,$ext)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		
		$memInfo = $member->getMemberInfo($msc_id,'obj');
		
		$orderParams = oseJSON::decode($orderInfo->params);
			
		$recurrence_num = $orderParams->p3;
		$recurrence_unit = $orderParams->t3;
		
		if($renew_mode == 'extend') 
		{
			$start_date = $db->Quote($memInfo->expired_date);
			
			$expired_date = " DATE_ADD({$start_date},INTERVAL {$recurrence_num} {$recurrence_unit}) ";
		}
		else 
		{
			$start_date = oseHTML::getDateTime();
			$start_date = $db->Quote($start_date);
			//$recurrence_num++;
			$expired_date = " DATE_ADD({$start_date},INTERVAL {$recurrence_num} {$recurrence_unit}) ";
		}
		
		$query = " UPDATE `#__osemsc_member` "
				." SET  `expired_date` = {$expired_date}"
				." WHERE `member_id` = ".$db->Quote($member_id)." AND `msc_id` = ".$db->Quote($msc_id)." "
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
			
			return $result;
		}
		
		if($renew_mode == 'renew') 
		{
			if(oseObject::getValue($orderParams,'recurrence_times',0) > 1)
			{
				
			}
			else
			{
				$query = " SELECT expired_date FROM `#__osemsc_member` "
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				$end_date = $db->loadResult();
				$end_date = $db->Quote($end_date);
				$expired_date = " DATE_ADD({$end_date},INTERVAL 1 DAY) ";
				
				$query = " UPDATE `#__osemsc_member` "
						." SET  expired_date = {$expired_date}"
						." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
						;
				$db->setQuery($query);
				//oseExit($db->_sql);
				
				if(!oseDB::query())
				{
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
					
					return $result;
				}
			}
		}
		return $result;
	}
	
	private static function activateInManualMode($msc_id,$member_id,$orderInfo,$ext)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		$orderParams = oseJSON::decode($orderInfo->params);
		
		if($orderParams->recurrence_mode == 'period')
		{
			$recurrence_num = $orderParams->p3;
			$recurrence_unit = $orderParams->t3;
			
			$start_date = oseHTML::getDateTime();
			$start_date = $db->Quote($start_date);
			
			$set = array();
			
			$set['start_date'] = $start_date;
			
			if(!empty($orderParams->eternal))
			{
				$expired_date = '0000-00-00 00:00:00';
				$set['expired_date'] = $db->Quote($expired_date);
				$set['eternal'] = 1;
			}
			else
			{
				$set['expired_date'] = " DATE_ADD($start_date,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
			}
			
			$values = array();
			foreach($set AS $key => $value)
			{
				$values[] = $key.'='.$value;
			}
			$values = implode(',',$values);
			
			$query = " UPDATE `#__osemsc_member` "
					." SET {$values}"
					." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
					;
			$db->setQuery($query);
			//oseExit($db->_sql);
			
			if(!oseDB::query())
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
				
				return $result;
			}
		}elseif(oseObject::getValue($orderParams,'recurrence_mode','period') == 'fixed')
		{
				
			$start_date = $orderParams->start_date;
			$start_date = $db->Quote($start_date);
				
			$expired_date = $orderParams->expired_date;
			$expired_date = $db->Quote($expired_date);
				
			$query = " UPDATE `#__osemsc_member` "
					." SET  start_date = {$start_date} , expired_date = {$expired_date}"
					." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
					;
			$db->setQuery($query);
			//oseExit($db->_sql)
			
			if(!oseDB::query())
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Can Not Update The Start Date AND Expired Date of Member.');
				
				return $result;
			}
		}		
		return $result;
		
	}
	
	private static function activateInAutomaticMode($msc_id,$member_id,$orderInfo,$ext)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		
		$orderParams = oseJSON::decode($orderInfo->params);
		
		$start_date = oseHTML::getDateTime();
		$start_date = $db->Quote($start_date);
		
		$recurrence_num = $orderParams->p3;
		$recurrence_unit = $orderParams->t3;
		
		$expired_date = " DATE_ADD({$start_date},INTERVAL {$recurrence_num} {$recurrence_unit}) ";
		
		$query = " UPDATE `#__osemsc_member` "
				." SET  expired_date = {$expired_date}"
				." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
			
			return $result;
		}
		
		$query = " SELECT expired_date FROM `#__osemsc_member` "
				." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
				;
		$db->setQuery($query);
		$end_date = $db->loadResult();
		$end_date = $db->Quote($end_date);
		$expired_date = " DATE_ADD({$end_date},INTERVAL 1 DAY) ";
		
		if(oseObject::getValue($orderParams,'recurrence_times',0) > 1)
		{
			
		}
		else
		{
			$query = " UPDATE `#__osemsc_member` "
					." SET  expired_date = {$expired_date}"
					." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
					;
			$db->setQuery($query);
			//oseExit($db->_sql);
			
			if(!oseDB::query())
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
				
				return $result;
			}
		}
			
		
		return $result;
	}
}
?>