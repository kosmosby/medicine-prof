<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelPayment extends oseMscAddon
{
	public static function save($params = array())
	{
		$db = oseDB::instance();
		$result = array();
		$post = JRequest::get('post');

		$id = JRequest::getCmd('id',null);
		$msc_id = JRequest::getInt('msc_id',0);

		if( empty($id) )
		{
			$id = uniqid();
		}

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'payment','obj');
		$extItem->params = empty($extItem->params)?'{}':$extItem->params;

		$items = oseJson::decode($extItem->params,true);

		if(empty($post['payment_has_trial']))
		{
			$post['payment_has_trial'] = 0;
		}

		if(empty($post['payment_eternal']))
		{
			$post['payment_eternal'] = 0;
		}
		else
		{
			$post['payment_p1'] = 0;
			$post['payment_p3'] = 0;
		}

		if(empty($post['payment_isFree']))
		{
			$post['payment_isFree'] = 0;
		}

		if(empty($post['payment_a3']))
		{
			$post['payment_a3'] = 0;
		}

		if(empty($post['payment_p3']))
		{
			$post['payment_p3'] = 0;
		}

		if(empty($post['payment_t3']))
		{
			$post['payment_t3'] = 'day';
		}

		if(empty($post['payment_start_date']))
		{
			$post['payment_start_date'] = 0;
		}

		// check form value
		if($post['payment_isFree'] == 0 && ($post['payment_a3'] == 0 ))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLEASE_SET_THE_PRICE');

			return $result;
		}
		if($post['payment_isFree'] == 1 && ($post['payment_p3'] == 0 && $post['payment_start_date'] == 0 && $post['payment_eternal']!=1))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLEASE_SELECT_ONE_MODE_TO_SET_BILLING_PERIOD');

			return $result;
		}

		if($post['payment_eternal'] == 1 && ($post['payment_a3'] == 0 && $post['payment_isFree'] == 0))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLEASE_SET_THE_PRICE_IN_THE_RECURRENCE_MODE_FOR_THE_MEMBERSHIP_OR_SET_IT_FOR_FREE');

			return $result;
		}
		// end
		
		if(empty($post['payment_optionname']))
		{
			if ($post['payment_recurrence_mode'] == 'period')
			{
				if (oseObject::getValue($post,'payment_eternal',false))
				{
					if(oseObject::getValue($post,'payment_isFree',false))
					{
						$post['payment_optionname'] = JText::_('LIFETIME_FREE_MEMBERSHIP');//JText::_('LIFETIME_FREE_MEMBERSHIP');
					}
					else
					{
						$post['payment_optionname'] = JText::_('LIFETIME_MEMBERSHIP');//JText::_('LIFETIME_FREE_MEMBERSHIP');
					}
				}
				else
				{
					$post['payment_optionname'] = ($post['payment_p3']==1)?$post['payment_p3']."-".$post['payment_t3']:$post['payment_p3']."-".$post['payment_t3']."s";
					if(oseObject::getValue($post,'payment_isFree',false))
					{
						$post['payment_optionname'] .= ' '.JText::_('FREE_MEMBERSHIP');//JText::_('LIFETIME_FREE_MEMBERSHIP');
					}
					else
					{
						$post['payment_optionname'] .= ' '.JText::_('PAID_MEMBERSHIP');
					}
					
				}
			}
			else
			{
				$post['payment_optionname'] = $post['payment_start_date'].' - '.$post['payment_expired_date'];
				if(oseObject::getValue($post,'payment_isFree',false))
				{
					$post['payment_optionname'] .= ' '.JText::_('FREE_MEMBERSHIP');//JText::_('LIFETIME_FREE_MEMBERSHIP');
				}
				else
				{
					$post['payment_optionname'] .= ' '.JText::_('PAID_MEMBERSHIP');
				}
			}

			$post['payment_optionname'] = ucwords(strtolower($post['payment_optionname']));
			/*if($post['payment_recurrence_mode'] == 'period')
			{
				if ($post['payment_eternal']==true)
				{
					$post['optionname'] = JText::_("Lifetime");
				}
				else
				{
					$post['optionname'] = ($post['payment_p3']==1)?$post['payment_p3']."-".$post['payment_t3']:$post['payment_p3']."-".$post['payment_t3']."s";
				}
			}
			else
			{

				$post['optionname'] = $post['payment_start_date'].' - '.$post['payment_expired_date'];

			}*/
		}
		$post['payment_payment_mode'] = oseRegistry::call('msc')->getConfig('global','obj')->payment_mode;

		$post['payment_price'] = $post['payment_a3'];
		$post['payment_recurrence_num'] = $post['payment_p3'];
		$post['payment_recurrence_unit'] = $post['payment_t3'];

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

		$where[] = 'type = '. $db->Quote('payment');

		$params = array();
		$prefix = 'payment_';
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

		$i = 1;
		foreach($items as $key => $item)
		{
			if(!oseObject::getValue($item,'ordering',0))
			{
				$item = oseObject::setValue($item,'ordering',$i);
				$items[$key] = $item;
			}
			else
			{
				$items[$key] = $item;
			}
			//$item = oseObject::setValue($item,'ordering',1);

			$i++;
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
					." ({$msc_id},".$db->Quote('payment').",{$newParams}) "
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
					." AND type = ".$db->Quote('payment')
					;

			$db->setQuery($query);
		}

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
			
			//oseMscHelper::generatePlanJs();
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
		$msc_id = JRequest::getInt('msc_id',0);

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'payment','obj');
		//oseExit($extItem);
		$items = oseJson::decode($extItem->params,true);

		if(empty($items))
		{
			$items = array();
		}

		$i = 1;
		$nItems = array();
		foreach($items as $key => $item)
		{
			if(!oseObject::getValue($item,'ordering',0))
			{
				$item = oseObject::setValue($item,'ordering',$i);
				$items[$key] = $item;
			}
			else
			{
				break;
			}
			//$item = oseObject::setValue($item,'ordering',1);
			$i++;
		}
		foreach($items as $key => $item)
		{
			$ordering = oseObject::getValue($item,'ordering');
			$nItems[$ordering] = $item;
		}

		ksort($nItems);
		$items = $nItems;
		$items = array_values($items);

		foreach ($items as $key => $item)
		{
			if(empty($item['optionname']))
			{
				if($items[$key]['recurrence_mode']=='period')
				{
					if ($items[$key]['eternal']==true)
					{
						$items[$key]['optionname'] = JText::_("LIFETIME");
					}
					else
					{
						$items[$key]['optionname'] = ($items[$key]['p3']==1)?$items[$key]['p3']."-".$items[$key]['t3']:$items[$key]['p3']."-".$items[$key]['t3']."s";
					}
				}
				else
				{
	
					$items[$key]['optionname'] = $items[$key]['start_date'].' - '.$items[$key]['expired_date'];
	
				}
			}
			$items[$key]['idurl']="<a href='".JURI::root()."index.php?option=com_osemsc&view=register&msc_id={$msc_id}&amp;msc_option=".$items[$key]['id']."' target='_blank'>".$items[$key]['id']."</a>";
		}
		$result = array();
		$result['total'] = count($items);
		$result['results'] = $items;

		return $result;
	}

	function remove()
	{
		$db = oseDB::instance();
		$ids = JRequest::getVar('ids',array());

		//$id = JRequest::getString('id',0);

		$msc_id = JRequest::getInt('msc_id',0);

		$paymentItems = oseRegistry::call('msc')->getExtInfo($msc_id,'payment','array');
		foreach($ids as $id)
		{
			unset($paymentItems[$id]);
		}


		$newParams = $db->Quote(oseJson::encode($paymentItems));

		$query = " UPDATE `#__osemsc_ext` "
				." SET "
				." `params` = {$newParams} "
				." WHERE `id` = $msc_id"
				." AND `type` = ".$db->Quote('payment')
				;

		$db->setQuery($query);

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
			
			//oseMscHelper::generatePlanJs();
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
				." AND `type` = ".$db->Quote('payment')
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

	private function ordering($id,$msc_id,$orderingChange)
	{
		$db = oseDB::instance();

		//$post = JRequest::get('post');

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'payment','obj');
		$extItem->params = empty($extItem->params)?array():$extItem->params;

		$items = oseJson::decode($extItem->params,true);

		// if no ordering exists, resort it.
		$i = 1;
		foreach($items as $key => $item)
		{
			if(!oseObject::getValue($item,'ordering',0))
			{
				$item = oseObject::setValue($item,'ordering',$i);
				$items[$key] = $item;
			}
			else
			{
				break;
			}

			$i++;
		}

		if(oseObject::getValue($items[$id],'ordering') == 1 && $orderingChange < 0)
		{
			return true;
		}

		if(oseObject::getValue($items[$id],'ordering') == count($items) && $orderingChange > 0)
		{
			return true;
		}

		// get new array by ordering
		$nItems = array();
		foreach($items as $key => $item)
		{
			$ordering = oseObject::getValue($item,'ordering');
			$nItems[$ordering] = $item;
		}

		ksort($nItems);

		$curOrdering = oseObject::getValue($items[$id],'ordering');
		$curItem = $nItems[$curOrdering];

		$toOrdering = $curOrdering + $orderingChange;
		$toItem = $nItems[$toOrdering];

		// resort the ordering
		$i = 1;
		$rItems = array();
		foreach($nItems  as $key => $item)
		{
			if($i == $curOrdering)
			{
				$rItems[$i] = $toItem;
			}
			elseif($i == $toOrdering)
			{
				$rItems[$i] = $curItem;
			}
			else
			{
				$rItems[$i] = $item;
			}

			$i++;
		}

		// restore key: id
		$i = 1;
		$items = array();
		foreach($rItems  as $key => $item)
		{
			$nkey = oseObject::getValue($item,'id');
			$items[$nkey] = oseObject::setValue($item,'ordering',$key);
		}

		$where = array();

		$where[] = 'id = '. $db->Quote($msc_id);
		$where[] = 'type = '. $db->Quote('payment');

		$newParams = $db->Quote(oseJson::encode($items));
		$where = oseDB::implodeWhere($where);


		$query = " UPDATE `#__osemsc_ext` "
				." SET "
				." params = {$newParams} "
				. $where
				;

		$db->setQuery($query);


		if (oseDB::query())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function up()
	{
		$id = JRequest::getCmd('id',null);
		$msc_id = JRequest::getInt('msc_id',0);
		$orderingChange = '-1';

		if(empty($msc_id) || empty($id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$updated = $this->ordering($id,$msc_id,$orderingChange);

		if ($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		return $result;
	}

	function down()
	{
		$id = JRequest::getCmd('id',null);
		$msc_id = JRequest::getInt('msc_id',0);
		$orderingChange = '+1';

		if(empty($msc_id) || empty($id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$updated = $this->ordering($id,$msc_id,$orderingChange);

		if ($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		return $result;
	}
}
?>