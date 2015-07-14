<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");


class oseMscControllerConfig extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function save()
	{
		$model = $this->getModel('config');

		$post = JRequest::get('post');

		unset($post['option']);
		unset($post['controller']);
		unset($post['task']);

		$updated = $model->save($post);

		$result = array();

		if($updated)
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

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getConfig()
	{
		$model = $this->getModel('config');
		$type = JRequest::getWord('config_type',null);
		$config = $model->getConfig($type);

		$result = array();

		if(empty($config))
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $config;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getGroupList()
	{
		$model = $this->getModel('config');

		$list = $model->getGroupList();

		oseExit($list);
	}

	function getEmailAdminGroupList()
	{
		$model = $this->getModel('config');

		$list = $model->getEmailAdminGroupList();

		oseExit($list);
	}

	function getCurrencyTypes()
	{

		$configItemName = 'primary_currency';//JRequest::getCmd('name',null);

		$config_type = 'currency';//JRequest::getCmd('config_type',null);


		$item = oseRegistry::call('msc')->getInstance('Config')->getConfigItem($configItemName,$config_type,'obj');


		$currencyInfos = oseJson::decode($item->default,true);
		//oseExit($currencyInfos);
		if(empty($currencyInfos))
		{
			$currencyInfos = array();
		}
		//$item->default = array_values($currencyInfos);
		//$item = oseJson::encode($item);
		$data = array_values($currencyInfos);
		$result = array();
		$result['total'] = count($data);
		$result['results'] = $data;
		$result = oseJson::encode($result);
		oseExit($result);
	}

	function saveMCurrency()
	{
		$currency = JRequest::getCmd('currency',null);
		$rate = JRequest::getCmd('rate',0);

		if(empty($currency))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');

			$result = oseJson::encode($result);
			oseExit($result);
		}

		$model = $this->getModel('config');

		$updated = $model->saveMCurrency($currency,$rate);

		if($updated)
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

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function removeCurrency()
	{
		$db = oseDB::instance();

		$currency = JRequest::getCmd('currency',null);

		if(empty($currency))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_NEED_TO_REMOVE');

			$result = oseJson::encode($result);
			oseExit($result);
		}

		$model = $this->getModel('config');

		$updated = $model->removeCurrency($currency);

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$result = oseJson::encode($result);
		oseExit($result);
	}


	function removeAllCurrency()
	{
		$db = oseDB::instance();

		$model = $this->getModel('config');

		$updated = $model->removeAllCurrency();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function getTaxList()
	{
		$db = oseDB::instance();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$query = " SELECT COUNT(*) FROM `#__osemsc_tax`";
		$db->setQuery($query,$start,$limit);
		$total = $db->loadResult();

		$query = " SELECT * FROM `#__osemsc_tax`";
		$db->setQuery($query,$start,$limit);
		$objs = oseDB::loadList('obj');

		$result['success'] = true;
		$result['total'] = $total;
		$result['results'] = $objs;

		$result = oseJson::encode($result);
		oseExit($result);
	}


	function saveTax()
	{
		$db = oseDB::instance();

		$post = JRequest::get('post');

		$id = JRequest::getInt('id',0);

		$country_3_code = JRequest::getCmd('country_3_code',null);
		$state_2_code = JRequest::getCmd('state_2_code',null);


		if(!empty($post['file_control']))
		{
			$post['has_file_control'] = 1;
		}
		else
		{
			$post['has_file_control'] = 0;
		}

		$where = array();
		$where[] = '`country_3_code` ='.$db->Quote($country_3_code);
		$where[] = '`state_2_code` ='.$db->Quote($state_2_code);
		$where = oseDB::implodeWhere($where);

		$query = "SELECT * FROM `#__osemsc_tax`"
				.$where
				.' LIMIT 1'
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		if(!empty($item))
		{
			$id = $item->id;
		}
		else
		{
			$id = $id;
		}

		if(empty($id))
		{
			$updated = oseDB::insert('#__osemsc_tax',$post);
		}
		else
		{
			$updated = oseDB::update('#__osemsc_tax','id',$post);
		}

		if($updated)
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

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function removeTax()
	{
		$db = oseDB::instance();

		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('REMOVE_SUCCESSFULLY');

		$ids = JRequest::getVar('ids',array(),'post','array');

		foreach($ids as $id)
		{
			$query = " DELETE FROM `#__osemsc_tax`"
					." WHERE `id` = {$id}"
					;
			$db->setQuery($query);

			if(!$db->query())
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR');

				break;
			}
		}

		$result = oseJson::encode($result);
		oseExit($result);
	}
	////////////////
	function getCountryList()
	{
		$db = oseDB::instance();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$search = JRequest::getCmd('search',null);
		$db = oseDB::instance();

		$where = array();

		if(!empty($search))
		{
			$where[] = ' `country_name` LIKE '.$db->Quote("%{$search}%")
					  .' OR `country_3_code` LIKE '.$db->Quote("%{$search}%")
					  .' OR `country_2_code` LIKE '.$db->Quote("%{$search}%")
						;
		}
		$where = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) FROM `#__osemsc_country` ".$where;

		$db->setQuery($query);
		$total = $db->loadResult();

		$query = " SELECT * FROM `#__osemsc_country` ".$where;

		$db->setQuery($query,$start,$limit);
		$items = oseDB::loadList();



		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;

		$result = oseJson::encode($result);
		oseExit($result);
	}


	function saveCountry()
	{
		$db = oseDB::instance();

		$post = JRequest::get('post');

		$country_id = JRequest::getInt('country_id',0);

		$country_name = JRequest::getCmd('country_name',null);
		$country_3_code = JRequest::getCmd('country_3_code',null);
		$country_2_code = JRequest::getCmd('country_2_code',null);


		$where = array();
		//$where[] = '`country_3_code` ='.$db->Quote($country_3_code);
		//$where[] = '`country_2_code` ='.$db->Quote($country_2_code);
		$where[] = '`country_id` ='.$db->Quote($country_id);
		$where = oseDB::implodeWhere($where);

		$query = "SELECT * FROM `#__osemsc_country`"
				.$where
				.' LIMIT 1'
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		if(!empty($item))
		{
			$id = $item->country_id;
		}
		else
		{
			$id = $country_id;
		}

		if(empty($id))
		{
			$updated = oseDB::insert('#__osemsc_country',$post);
		}
		else
		{
			$updated = oseDB::update('#__osemsc_country','country_id',$post);
		}

		if($updated)
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

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function removeCountry()
	{
		$db = oseDB::instance();

		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('REMOVE_SUCCESSFULLY');

		$ids = JRequest::getVar('ids',array(),'post','array');

		foreach($ids as $id)
		{
			$query = " DELETE FROM `#__osemsc_country`"
					." WHERE `country_id` = {$id}"
					;
			$db->setQuery($query);

			if(!$db->query())
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR');

				break;
			}
		}

		$result = oseJson::encode($result);
		oseExit($result);
	}

	////////////////
	function getStateList()
	{
		$db = oseDB::instance();

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		$country_id = JRequest::getInt('country_id',0);
		$db = oseDB::instance();

		if(empty($country_id))
		{
			$result = array();
			$result['total'] = 0;
			$result['results'] = array();
		}
		else
		{
			$query = " SELECT COUNT(*) FROM `#__osemsc_state`"
					." WHERE `country_id` = '{$country_id}'"
					;

			$db->setQuery($query);
			$total = $db->loadResult();

			$query = " SELECT * FROM `#__osemsc_state`"
					." WHERE `country_id` = '{$country_id}'"
					;
			$db->setQuery($query,$start,$limit);
			$items = oseDB::loadList();



			$result = array();
			$result['total'] = $total;
			$result['results'] = $items;
		}


		$result = oseJson::encode($result);
		oseExit($result);
	}


	function saveState()
	{
		$db = oseDB::instance();

		$post = JRequest::get('post');

		$state_id = JRequest::getInt('state_id',0);
		$country_id = JRequest::getInt('country_id',0);
		$state_name = JRequest::getCmd('state_name',null);
		$state_3_code = JRequest::getCmd('state_3_code',null);
		$state_2_code = JRequest::getCmd('state_2_code',null);


		$where = array();
		//$where[] = '`State_3_code` ='.$db->Quote($State_3_code);
		//$where[] = '`State_2_code` ='.$db->Quote($State_2_code);
		$where[] = '`state_id` ='.$db->Quote($state_id);
		$where = oseDB::implodeWhere($where);

		$query = "SELECT * FROM `#__osemsc_state`"
				.$where
				.' LIMIT 1'
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		if(!empty($item))
		{
			$id = $item->state_id;
		}
		else
		{
			$id = $state_id;
		}

		if(empty($id))
		{
			$updated = oseDB::insert('#__osemsc_state',$post);
		}
		else
		{
			$updated = oseDB::update('#__osemsc_state','state_id',$post);
		}

		if($updated)
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

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function removeState()
	{
		$db = oseDB::instance();

		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('REMOVE_SUCCESSFULLY');

		$ids = JRequest::getVar('ids',array(),'post','array');

		foreach($ids as $id)
		{
			$query = " DELETE FROM `#__osemsc_state`"
					." WHERE `state_id` = {$id}"
					;
			$db->setQuery($query);

			if(!$db->query())
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR');

				break;
			}
		}

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function loadDefCanTax()
	{
		$db = oseDB::instance();

		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('SUCCESSFULLY');

		$arrays = array();
		$array = array();
		$array['scode'] = '--';
		$array['rate'] = '5';
		$arrays[] = $array;
		$array['scode'] = 'ON';
		$array['rate'] = '13';
		$arrays[] = $array;
		$array['scode'] = 'NB';
		$array['rate'] = '13';
		$arrays[] = $array;
		$array['scode'] = 'NL';
		$array['rate'] = '13';
		$arrays[] = $array;
		$array['scode'] = 'NS';
		$array['rate'] = '15';
		$arrays[] = $array;
		$array['scode'] = 'BC';
		$array['rate'] = '12';
		$arrays[] = $array;
		$array['scode'] = 'QC';
		$array['rate'] = '13.93';
		$arrays[] = $array;
		$array['scode'] = 'MB';
		$array['rate'] = '12';
		$arrays[] = $array;
		$array['scode'] = 'PE';
		$array['rate'] = '15.5';
		$arrays[] = $array;
		$array['scode'] = 'SK';
		$array['rate'] = '10';
		$arrays[] = $array;
		foreach($arrays as $arr)
		{
			$query = "SELECT count(*) FROM `#__osemsc_tax` WHERE `country_3_code` = 'CAN' AND `state_2_code` = '{$arr['scode']}'";
			$db->setQuery($query);
			$res = $db->loadResult();
			if(empty($res))
			{
				$query = "INSERT INTO `#__osemsc_tax` (`country_3_code`, `state_2_code`, `rate`, `file_control`, `has_file_control`, `ordering`, `lft`, `rgt`)"
						." VALUES "
						." ('CAN', '{$arr['scode']}', '{$arr['rate']}', '', 0, 0, 0, 0)";
			}else{
				$query = "UPDATE `#__osemsc_tax` SET `rate` = '{$arr['rate']}' WHERE `country_3_code` = 'CAN' AND `state_2_code` = '{$arr['scode']}'";
			}
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = $db->getErrorMsg();

				$result = oseJson::encode($result);
				oseExit($result);
			}
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
}