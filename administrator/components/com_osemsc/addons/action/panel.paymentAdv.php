<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelPaymentAdv
{
	public static function save($params = array())
	{
		$db = oseDB::instance();

		$post = JRequest::get('post');

		$id = JRequest::getCmd('id',null);
		$msc_id = JRequest::getInt('msc_id',0);

		if( empty($id) )
		{
			$id = uniqid();
		}

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'paymentAdv','obj');
		
		$extItem->params = empty($extItem->params)?'{}':$extItem->params;

		$items = oseJson::decode($extItem->params,true);
		
		$where = array();

		$msc_id = isset($msc_id)?$msc_id:null;

		if(empty($msc_id))
		{
			return false; // No membership exists in the addon
		}
		else
		{
			unset($post['msc_id']);
			$where[] = 'id = '. $db->Quote($msc_id);
		}

		$where[] = 'type = '. $db->Quote('paymentAdv');

		$params = array();
		$prefix = 'paymentAdv_';
		
		if((int)$post['paymentAdv_renew_discount'] <=0)
		{
			$post['paymentAdv_renew_discount'] = 0;
		}
		
		foreach($post as $key => $value)
		{
			if(strstr($key,$prefix))
			{
				$newKey = preg_replace("/{$prefix}/",'',$key,1);
				$params[$newKey] = $value;
			}
		}

		ksort($params);

		/*
		if($id < 0)
		{
			$items[] = $params;
		}
		else
		{
			$items[$id] = $params;
		}
		*/
		$params['id'] = $id;
		$items[$id] = $params;

		foreach($items as $key => $item)
		{
			//$item['id'] = $id;
			//$items[$key] = $item;
		}
		//oseExit($items);
		//oseExit($items);

		$newParams = $db->Quote(oseJson::encode($items));
		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_ext` "
				. $where
				;
		$db->setQuery($query);
		$obj = oseDB::loadItem('obj');

		if(empty($obj))
		{
			$query = " INSERT INTO `#__osemsc_ext` "
					." (id,type,params)"
					." VALUES "
					." ({$msc_id},".$db->Quote('paymentAdv').",{$newParams}) "
					;
			$db->setQuery($query);
			//oseExit($db->_sql);

		}
		else
		{
			$query = " UPDATE `#__osemsc_ext` "
					." SET "
					." params = {$newParams} "
					." WHERE id = {$obj->id}"
					." AND type = ".$db->Quote('paymentAdv')
					;

			$db->setQuery($query);
		}

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR_IN_SAVING_MSC_PARAMETERS');
		}
		return $result;
	}

	function getOptions()
	{
		$msc_id = JRequest::getInt('msc_id');
		$result = oseRegistry::call('msc')->getInstance('Addon')->runAction('panel.payment.getOptions',array());
		
		$items = $result['results'];
		
		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'paymentAdv','obj');

		$advItems = oseJson::decode(oseObject::getvalue($extItem,'params','{}'),true);
		$advItems = empty($advItems)?array():$advItems;

		foreach($items as $key => $item)
		{
			if(!empty($advItems[$item['id']]))
			{
				$item = array_merge($item,$advItems[$item['id']]);
			}
			else
			{
				$item['renew_discount'] = 0;
				$item['renew_discount_type'] = 'rate';
				$item['payment_mode'] = 'b';
			}
			$items[$key] = $item;
		}
		
		$result['total'] = count($items);
		$result['results'] = $items;

		return $result;
	}

	function remove()
	{
		$db = oseDB::instance();

		$id = JRequest::getString('id',0);

		$msc_id = JRequest::getInt('msc_id',0);

		$paymentItems = oseRegistry::call('msc')->getExtInfo($msc_id,'paymentAdv','array');
		unset($paymentItems[$id]);

		$newParams = $db->Quote(oseJson::encode($paymentItems));

		$query = " UPDATE `#__osemsc_ext` "
				." SET "
				." `params` = {$newParams} "
				." WHERE `id` = $msc_id"
				." AND `type` = ".$db->Quote('paymentAdv')
				;

		$db->setQuery($query);

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR_IN_REMOVING_PAYMENT_PARAMETERS');
		}
		return $result;
	}

	function removeAll()
	{
		$db = oseDB::instance();

		$msc_id = JRequest::getInt('msc_id',0);



		$query = " UPDATE `#__osemsc_ext` "
				." SET "
				." `params` = ''"
				." WHERE `id` = $msc_id"
				." AND `type` = ".$db->Quote('paymentAdv')
				;

		$db->setQuery($query);

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('RESET_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR_IN_RESETTING_PAYMENT_PARAMETERS');
		}
		return $result;
	}
}
?>