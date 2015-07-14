<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinMsc
{
	public static function save($params)
	{
		$result = array();
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		//oseExit($params);
		
		
		if( $params['join_from'] != 'payment' )
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Join Msc: No Msc ID");
			return $result;
		}
		
		$msc = oseRegistry::call('msc');
		
		//$exts = $msc->getExtInfo($msc_id,'payment','obj');
		/*
		if(empty($ext))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Joining Membership.');
			
			return $result; 
		}
		*/
		$where = array();
		$where[] = 'order_id = '.$db->Quote($order_id);
		
		$orderInfo = oseRegistry::call('payment')->getOrder($where,'obj');
		
		if($orderInfo->payment_mode == 'm')
		{
			$updated = self::joinInManualMode($msc_id,$member_id,$orderInfo);
		}
		else // equals a 
		{
			$updated = self::joinInAutomaticMode($msc_id,$member_id,$orderInfo);
		}
		
		return $updated;
		
	}
	
	public static function cancel($params)
	{
		$db = oseDB::instance();
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
		
		if(empty($params['master']))
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		
		$tmpl_var_instant = true;
		$msc_id = $db->Quote($params['msc_id']);
		$member_id = $db->Quote($params['member_id']);
		
		if($tmpl_var_instant)
		{
			
			$query = " UPDATE `#__osemsc_member` "
					." SET expired_date = ".$db->Quote(oseHtml::getDateTime())
					." WHERE msc_id = {$msc_id} AND member_id = {$member_id}"
					;
			$db->setQuery($query);
			
			if(!oseDB::query())
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_("Cancel Msc Error");
				
			}
		}
		 
		return $result;
		
	}
	
	private static function joinInManualMode($msc_id,$member_id,$orderInfo)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		$orderParams = oseJSON::decode($orderInfo->params);
		
		// If it is set to Fixed Date
		if($orderParams->recurrence_mode == 'fixed')
		{
			/*
			$start_date = $ext->start_date.' 00:00:00';
			$start_date = $db->Quote($start_date);
			
			$expired_date = $ext->expired_date.' 23:59:59';
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
			*/
		}
		else
		{
			if($orderInfo->entry_type == 'msc_list')
			{
				$paymentOrder = oseRegistry::call('payment')->getInstance('Order');
				$paymentOrder->__construct('#__osemsc_order_item');
				
				$where = array();
				$where[] = '`order_id` = '.$db->Quote($orderInfo->order_id);
				$where[] = '`entry_id` = '.$db->Quote($msc_id);
				$where[] = '`entry_type` = '.$db->Quote('msc');
				$orderItemInfo = $paymentOrder->getOrder($where,'obj');
				
				$orderItemInfoParams = oseJSON::decode($orderItemInfo->params);
				$recurrence_num = $orderItemInfoParams->p3;
				$recurrence_unit = $orderItemInfoParams->t3;
			}
			else
			{
				$recurrence_num = $orderParams->p3;
				$recurrence_unit = $orderParams->t3;
			}
			
			//oseExit($ext);
			$start_date = oseHTML::getDateTime();
			$start_date = $db->Quote($start_date);
			
			if(!empty($orderParams->eternal))
			{
				$expired_date = '0000-00-00 00:00:00';
				$expired_date = $db->Quote($expired_date);
			}
			else
			{
				$expired_date = " DATE_ADD($start_date,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
			}
			
			$query = " UPDATE `#__osemsc_member` "
					." SET  `start_date` = {$start_date} , `expired_date` = {$expired_date}"
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
	
	private static function joinInAutomaticMode($msc_id,$member_id,$orderInfo)
	{
		$db = oseDB::instance();
		
		$result = array();
		$result['success'] = true;
		
		$orderParams = oseJSON::decode($orderInfo->params);
		
		$start_date = oseHTML::getDateTime();
		$start_date = $db->Quote($start_date);
		
		if($orderParams->has_trial)
		{
			$recurrence_num = $orderParams->p1+1;
			$recurrence_unit = $orderParams->t1;
		}
		else
		{
			$recurrence_num = $orderParams->p3+1;
			$recurrence_unit = $orderParams->t3;
		}
		
		$expired_date = " DATE_ADD($start_date,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
		
		$query = " UPDATE `#__osemsc_member` "
				." SET  start_date = {$start_date} , expired_date = {$expired_date}"
				." WHERE member_id = {$member_id} AND msc_id = {$msc_id} "
				;
		$db->setQuery($query);
		
		
		if(!oseDB::query())
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_(' Fail Updated Member\'s Recurrence ');
			
			return $result;
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