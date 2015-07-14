<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelOSELic extends oseMscAddon
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

		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'oselic','obj');
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

		$where[] = 'type = '. $db->Quote('oselic');

		$params = array();
		$prefix = 'oselic_';

		if((int)$post['oselic_enable_license'] <=0)
		{
			$post['oselic_enable_license'] = 0;
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
			." ({$msc_id},".$db->Quote('oselic').",{$newParams}) "
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
			." AND type = ".$db->Quote('oselic')
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
		
		/*
		if (oseMscAddon::quickSavePanel('oselic_',$post))
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

		return $result;*/
	}

	public static function getLicCS($params = array())
	{
		oseRegistry::register('lic','license');
		$lic = oseRegistry::call('lic');

		$items =  $lic->getLicList('CS');

		$result = array();

		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}

		return $result;
	}



	public static function sync($params = array())
	{
		$result = array();

		$msc_id = JRequest::getInt('msc_id',0);
		$lic_id = JRequest::getInt('lic_lic_id',0);

		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('No Membership Id');
		}

		$licCS = oseRegistry::call('lic')->getInstance($lic_id);

		$licId = oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj')->lic_id;

		if($licId != $lic_id)
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('Please Save First!');
		}

		$updated = $licCS->sync2NewLicense($lic_id,$msc_id);

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['results'] = JText::_('Synchonized!');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('Fail Synchonizing.');
		}

		return $result;
	}

	public static function initMember($params = array())
	{
		$result = array();

		$msc_id = JRequest::getInt('msc_id',0);
		$lic_id = JRequest::getInt('lic_lic_id',0);

		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('No Membership Id');
		}

		$licCS = oseRegistry::call('lic')->getInstance($lic_id);

		$licId = oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj')->lic_id;

		if($licId != $lic_id)
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('Please Save First!');
		}

		$updated = $licCS->initMemberLicense($lic_id,$msc_id);

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['results'] = JText::_('Synchonized!');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['results'] = JText::_('Fail Synchonizing.');
		}

		return $result;
	}

	public static function getLicLicense($params = array())
	{
		$db = oseDB::instance();
		
		$query = " SELECT `id`,CONCAT( REPEAT('|--', level-1),' ', title) AS `title`"
			." FROM `#__oselic_plan`"
			." WHERE `level` > 0 "
			." ORDER BY lft ASC"
			;
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
	
		$extItem = oseRegistry::call('msc')->getExtInfoItem($msc_id,'oselic','obj');
	
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