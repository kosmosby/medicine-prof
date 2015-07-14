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

class oseMscModelAddons extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function

	function save($post)
	{
		$post = JRequest::get('post');


    	$addon_id = JRequest::getInt('addon_id',0);
    	unset($post['addon_id']);

		$addon = array();

		foreach($post as $key => $value)
		{
			if(strstr($key,'addon_'))
			{
				$billKey = preg_replace('/addon_/','',$key,1);
				$addon[$billKey] = $value;
			}
		}
		//oseExit($company);
    	$db = oseDB::instance();

    	if (empty ($addon_id)) {
    		$keys = array_keys($addon);
    		$keys = '`'.implode('`,`',$keys).'`';
    		$fields = $db->getTableFields('#__osemsc_addon');

    		foreach($addon as $key => $value)
    		{
				if(!isset($fields['#__osemsc_addon'][$key]))
				{
					/*
					$query = "ALTER TABLE `#__osemsc_billinginfo` ADD `{$key}` TEXT NULL DEFAULT NULL";
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
					*/
					unset($addon[$key]);
				}
    		}

    		foreach($addon as $key => $value)
    		{
    			$addon[$key] = $db->Quote($value);
    		}

    		$values = implode(',',$addon);

			$query = "INSERT INTO `#__osemsc_addon` ({$keys}) VALUES ({$values});";

			$db->setQuery($query);

			if (!oseDB::query()) {
				$result = array();

				return false;

			}

			oseMscAddon::reorder($post['addon_type']);
		}
		else
		{

			foreach($addon as $key => $value)
    		{
    			$addon[$key] = "`{$key}`=".$db->Quote($value);
    		}

    		$values = implode(',',$addon);

			$query = " UPDATE `#__osemsc_addon` SET {$values}"
					." WHERE `id` ={$addon_id}"
					;
			$db->setQuery($query);

			if (!oseDB::query()) {
				$result = array();

				return false;

			}

			$ordering = JRequest::getInt('ordering',0);
			$this->updateOrder($addon_id,$ordering);
		}
		//echo $query;exit;

		return true;

	}

	function getList($addonType = null,$start = 0,$limit = 0)
	{
		$db = oseDB::instance();

		$where = array();

		if(!empty($addonType))
		{
			$where[] = ' type = '.$db->Quote($addonType);
		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_addon`"
				.$where
				;

		$this->total = $this->_getListCount($query);

		if(empty($limit))
		{
			$db->setQuery($query);
		}
		else
		{
			$db->setQuery($query,$start,$limit);
		}

		$items = oseDB::loadList();

		return $items;
	}

	function getTotal()
	{
		if(empty($this->total))
		{
			$this->getList();
		}

		return $this->total;
	}

	function enableAddon($addon_id,$isBackend)
	{
		$addon_id = JRequest::getInt('addon_id',0);
		$isBackend = JRequest::getBool('isBackend',false);
		//oseExit($isBackend);
		$updated = oseMscAddon::enableAddon($addon_id,$isBackend);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = ' Successfully';
		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = ' Error';
		}

		return $result;
	}

	function getAddon($addon_id)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_addon`"
				." WHERE id = {$addon_id}"
				;
		$db->setQuery($query);

		return oseDB::loadItem();
	}

	function getOrder($type)
	{
		$db = oseDB::instance();

		$where = array();

		//$where[] = "id = {$addon_id}";
		$where[] = "type = '{$type}'";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_addon`"
				. $where
				." ORDER BY ordering ASC"
				;
		$db->setQuery($query);

		$items = oseDB::loadList();

		foreach($items as $key => $item)
		{
			$item['displayText'] = "({$item['ordering']}){$item['title']}";
			$items[$key] = $item;
		}
		return $items;
	}

	function updateOrder($addon_id,$ordering)
	{
		$node = oseMscAddon::getAddon($addon_id,'obj');

		if(!oseMscAddon::orderChange($node,$ordering))
		{
			return false;
		}
		if(!oseMscAddon::reorder($node->type))
		{
			return false;
		}

		return true;
	}

	function remove($addon_id)
	{

		return oseMscAddon::remove($addon_id);
	}

	function getAddonTypes()
	{
		$db = oseDB::instance();

		$query = " SELECT id,type AS text, type AS value "
				." FROM `#__osemsc_addon`"
				." GROUP BY type"
				;

		$db->setQuery($query);

		$objs =  array();
		$objs[] = array('id'=>0,'text'=>'All','value'=>null);
		$objs = array_merge($objs,oseDB::loadList());

		return $objs;
	}
}


