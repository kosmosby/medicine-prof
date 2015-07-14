<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
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
 */
if (!defined('_JEXEC') && !defined('OSE_ADMINPATH')) {
	die("Direct Access Not Allowed");
}
class oseDB {
	function __construct() {
	}
	public static function instance($type = null) {
		static $instance;
		if (empty($instance)) {
			$instance = JFactory::getDBO();
		}
		return $instance;
	}
	public static function update($table, $keyId, $updateValues) {
		$db = oseDB::instance();
		oseDB::lock($table . ' WRITE');
		$tables = self::getDBFields($table);
		$temp = array();
		if (!empty($tables)) {
			foreach ($tables[$table] as $field => $info) {
				if (isset($updateValues[$field])) {
					$temp[$field] = $updateValues[$field];
				}
			}
			$filterValues = array();
			foreach ($temp as $key => $value) {
				if ($key == $keyId) {
					continue;
				}
				$filterValues[$key] = $db->Quote($value);
			}
			$sql = array();
			foreach ($filterValues as $key => $value) {
				$sql[] = "`{$key}` = {$value}";
			}
			if (empty($sql)) {
				oseDB::unlock();
				return true;
			}
			$query = " UPDATE `{$table}` " . " SET  " . implode(',', $sql) . " WHERE `{$keyId}` = " . $db->Quote($updateValues[$keyId]);
			$db->setQuery($query);
			$result = $db->query();
			oseDB::unlock();
			return $result;
		} else {
			oseDB::unlock();
			return true;
		}
	}
	public static function insert($table, $insertValues) {
		$db = oseDB::instance();
		oseDB::lock($table . ' WRITE');
		$tables = self::getDBFields($table);
		$temp = array();
		if (!empty($tables)) {
			foreach ($tables[$table] as $field => $info) {
				if (isset($insertValues[$field])) {
					$temp[$field] = $insertValues[$field];
				}
			}
			$filterValues = array();
			foreach ($temp as $key => $value) {
				$filterValues[$key] = $db->Quote($value);
			}
			$sql = array();
			$sql1 = '`' . implode('`,`', array_keys($filterValues)) . '`';
			$sql2 = '' . implode(',', $filterValues) . '';
			if (empty($filterValues)) {
				oseDB::unlock();
				return true;
			}
			$query = " INSERT INTO `{$table}` " . " ({$sql1})  " . " VALUES" . " ({$sql2})";
			$db->setQuery($query);
			$result = ($db->query()) ? $db->insertid() : false;
			oseDB::unlock();
			return $result;
		} else {
			oseDB::unlock();
			return true;
		}
	}
	public static function delete($table, $uniqueKeys) {
		$db = oseDB::instance();
		foreach ($uniqueKeys as $k => $v) {
			$where[] = "`{$k}`=" . $db->Quote($v);
		}
		$where = self::implodeWhere($where);
		$query = " DELETE FROM `{$table}`" . $where;
		$db->setQuery($query);
		return $db->query();
	}
	public static function loadList($type = 'array', $key = null) {
		$db = oseDB::instance();
		switch ($type) {
		case ('obj'):
			return $db->loadObjectList($key);
			break;
		default:
			return $db->loadAssocList($key);
			break;
		}
	}
	public static function loadItem($type = 'array', $key = null) {
		$db = oseDB::instance();
		switch ($type) {
		case ('obj'):
			return $db->loadObject();
			break;
		default:
			return $db->loadAssoc();
			break;
		}
	}
	public static function query($unlock = false) {
		$testMode = 1;
		$db = oseDB::instance();
		if (!$db->query()) {
			if ($testMode == 1) {
				oseExit($db->getErrorMsg());
			}
			if ($unlock) {
				oseDB::unlock();
			}
			return false;
		}
		return true;
	}
	public static function lock($query) {
		$db = oseDB::instance();
		/*
		if (preg_match('/READ|WRITE/', $query)==0)
		{
		    $mode = ' WRITE';
		}
		else
		{
		    $mode = ' ';
		}
		 */
		//$db->setQuery("LOCK TABLE ". $query);
		//$db->query();
		return true;
	}
	public static function unlock() {
		$db = oseDB::instance();
		$query = "UNLOCK TABLES";
		$db->setQuery($query);
		return $db->query();
	}
	public static function implodeWhere($where = array()) {
		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');
		return $where;
	}
	public static function getDBFields($table) {
		$db = JFactory::getDBO();
		if (JOOMLA30 == true) {
			$fields = $db->getTableColumns($table);
			$fields[$table] = $fields;
		} else {
			$fields = $db->getTableFields($table);
		}
		return $fields;
	}
	public static function setQuery($sql) {
		$config = new JConfig();
		$return = str_replace('#__', $config->dbprefix, $sql);
		return $return;
	}
}
