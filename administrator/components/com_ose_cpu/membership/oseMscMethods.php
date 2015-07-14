<?php
defined('_JEXEC') or die(";)");

class oseMscMethods
{
	public static function getJsModPath($name,$type=null)
	{
		$app = JFactory::getApplication();
		$path = null;
		if($app->isAdmin())
		{
			$path = 'administrator/';
		}
		$path .= 'components/com_osemsc/modules/';
		if(empty($type))
		{
			$path .=  "ext.{$name}.js";
		}
		else
		{
			$path .= $type."/ext.{$type}.{$name}.js";
		}
		return $path;
	}

	public static function getAddonPath($name,$type=null)
	{
		$app = JFactory::getApplication();
		$path = null;
		if($app->isAdmin())
		{
			$path = 'administrator/';
		}
		$path .= 'components/com_osemsc/addons/';
		if(empty($type))
		{
			$path .=  "{$name}";
		}
		else
		{
			$path .= $type."/{$name}";
		}
		return $path;
	}

	/*
	 *  3 Parts

	function parseAction($action_name)
	{
	$part = explode('.',$action_name);
	$name = 'oseMscAddonAction'.ucfirst($part[0]).ucfirst($part[1]);
	}

	function runAction($action_name)
	{
	require_once(OSEMSC_B_ADDON.DS.'action'.DS.$action_name.'.php');

	$part = explode('.',$action_name);
	$className = 'oseMscAddonAction'.ucfirst($part[0]).ucfirst($part[1]);

	return call_user_method($part[count($part)-1],$className);
	}
	*/

	function parseParam($params)
	{
		$array = array();
		parse_str($params,$array);
		return $array;
	}

	function buildParam($params)
	{
		return http_build_query($params);
	}

	static function getCountry()
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_country`";
		$db->setQuery($query);
		$items = oseDB::loadList();
		$total = count($items);
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;
	}

	static function getState()
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_state`";
		$db->setQuery($query);
		$items = oseDB::loadList();
		$total = count($items);
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;
	}

	function getDefState()
	{
		$db = oseDB::instance();
		$where = array();
		$query = "SELECT value FROM `#__osemsc_configuration` WHERE `key` = 'default_country'";
		$db->setQuery($query);
		$country_id = $db->loadResult();

		if(!empty($country_id))
		{
			$where[] = "`country_id` = {$country_id}";
		}
		$where = oseDB::implodeWhere($where);
		$query = "SELECT * FROM `#__osemsc_state` ".$where;
		$db->setQuery($query);
		$items = oseDB::loadList();
		$total = count($items);
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;
	}
}
?>