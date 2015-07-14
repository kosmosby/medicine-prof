<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelAcymailing2 extends oseMscAddon
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

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'acymailing2','obj');
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

		$where[] = 'type = '. $db->Quote('acymailing2');

		$params = array();
		$prefix = 'acymailing2_';

		foreach($post as $key => $value)
		{
			if(strstr($key,$prefix))
			{
				$newKey = preg_replace("/{$prefix}/",'',$key,1);
				$params[$newKey] = $value;
			}
		}

		ksort($params);

		$params['id'] = $id;
		$items[$id] = $params;


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
			." ({$msc_id},".$db->Quote('acymailing2').",{$newParams}) "
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
			." AND type = ".$db->Quote('acymailing2')
			;

			$db->setQuery($query);
		}

		if (oseDB::query())
		{
			$result['success'] = true;
			$result['title'] = JText::_('Finished');
			$result['content'] = JText::_('Save Successfully!');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error in Saving License Parameters');
		}
		return $result;
		

	}


	public static function getList($params = array())
	{
		$db = oseDB::instance();
		
		$query = "SELECT * FROM `#__acymailing_list` WHERE `type`= 'list'";
		$db->setQuery($query);
		$planArray = oseDB::loadList();

		$result = array();

		if(count($planArray) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($planArray);
			$result['results'] = $planArray;
		}

		return $result;
	}

	public static function getMscList()
	{
		return oseMscPublic::getMscList();
	}

	public static function getMscOptions()
	{
		return oseMscPublic::getMscOptions();
	}
	
	function getOptions()
	{
		$msc_id = JRequest::getInt('msc_id');
		$result = oseRegistry::call('msc')->getInstance('Addon')->runAction('panel.payment.getOptions',array());
	
		$items = $result['results'];
	
		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'acymailing2','obj');
	
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
				$item['enable_license'] = 0;
				$item['lic_id'] = 0;
				//$item['enable_cs'] = 'b';
			}
			$items[$key] = $item;
		}
	
		$result['total'] = count($items);
		$result['results'] = $items;
	
		return $result;
	}
}
?>