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
if (class_exists('oseJSON')) { return ; }
class oseJSON {
	function generateQueryWhere() {
		$db = oseDB::instance();
		$filters = JRequest::getVar('filter', null);
		// GridFilters sends filters as an Array if not json encoded
		if (is_array($filters)) {
			$encoded = false;
		} else {
			$encoded = true;
			$filters = json_decode($filters);
		}
		$where = array();
		// loop through filters sent by client
		if (is_array($filters)) {
			for ($i = 0; $i < count($filters); $i++) {
				$filter = $filters[$i];
				// assign filter data (location depends if encoded or not)
				if ($encoded) {
					$field = $filter->field;
					$value = $filter->value;
					$compare = isset($filter->comparison) ? $filter->comparison : null;
					$filterType = $filter->type;
				} else {
					$field = $filter['field'];
					$value = $filter['data']['value'];
					$compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
					$filterType = $filter['data']['type'];
				}
			}
			switch ($filterType) {
			case 'string':
				$where[] = $field . " LIKE '%" . $db->Quote($value) . "%'";
				break;
			case 'list':
				if (strstr($value, ',')) {
					$fi = explode(',', $value);
					for ($q = 0; $q < count($fi); $q++) {
						$fi[$q] = $db->Quote($fi[$q]);
					}
					$value = implode(',', $fi);
					$where[] = $field . " IN (" . $value . ")";
				} else {
					$where[] = "{$field} = " . $db->Quote($value);
				}
				break;
			}
		}
		return $where;
	}
	public static function encode($arr) {
		if (version_compare(PHP_VERSION, "5.2", "<")) {
			if (file_exists(dirname(__FILE__) . DS . "Services/JSON.php")) {
				require_once(dirname(__FILE__) . DS . "Services/JSON.php"); //if php<5.2 need JSON class
			}
			$json = new Services_JSON(); //instantiate new json object
			$data = $json->encode($arr); //encode the data in json format
		} else {
			$data = json_encode($arr); //encode the data in json format
		}
		return $data;
	}
	public static function decode($json, $assoc = false) {
		if (version_compare(PHP_VERSION, "5.2", "<")) {
			if (file_exists(dirname(__FILE__) . DS . "Services/JSON.php")) {
				require_once(dirname(__FILE__) . DS . "Services/JSON.php"); //if php<5.2 need JSON class
			}
			$Services_json = new Services_JSON(); //instantiate new json object
			$data = $Services_json->decode($json, $assoc); //encode the data in json format
		} else {
			$data = json_decode($json, $assoc); //encode the data in json format
		}
		return $data;
	}
}
class oseJsonGrid {
	var $data = array();
	var $keyName = null;
	// data json structure
	function __construct($keyName) {
		$this->keyName = $keyName;
	}
	function load($data) {
		$i = 1;
		foreach ($data as $key => $item) {
			$ordering = oseGetValue($item, 'ordering', false);
			if (!$ordering) {
				if ($ordering != $i) {
					$item = oseSetValue($item, 'ordering', $i);
					$data[$key] = $item;
				}
			} else {
				$item = oseSetValue($item, 'ordering', $i);
				$data[$key] = $item;
			}
			$i++;
		}
		$this->data = $data;
	}
	function find($keyValue) {
		if (isset($this->data[$keyValue])) {
			return $this->data[$keyValue];
		} else {
			return array();
		}
	}
	function addRow($data = array()) {
		$ordering = count($this->data) + 1;
		$data['ordering'] = $ordering;
		$this->data[$data[$this->keyName]] = $data;
		return true;
	}
	function updateRow($oldKeyValue, $data = array()) {
		$row = $this->find($oldKeyValue);
		if (empty($row)) {
			return false;
		} else {
			$nData = array();
			foreach ($this->data as $k => $item) {
				if ($oldKeyValue == $k) {
					$nData[$data[$this->keyName]] = $data;
				} else {
					$nData[$k] = $item;
				}
			}
			$this->data = $nData;
		}
		return true;
	}
	function removeRow($keyValue) {
		$result = array();
		unset($this->data[$keyValue]);
		return true;
	}
	function changeRowOrder($ordering, $up = true) {
		$arrayKeyOrdering = array();
		foreach ($this->data as $k => $item) {
			$arrayKeyOrdering[$item['ordering']] = $item;
		}
		ksort($arrayKeyOrdering);
		// resort the ordering
		$toOrdering = ($up) ? $ordering - 1 : $ordering + 1;
		$rItems = array();
		$toOrderingEntry = $arrayKeyOrdering[$ordering];
		$fromOrderingEntry = $arrayKeyOrdering[$toOrdering];
		foreach ($arrayKeyOrdering as $key => $item) {
			if ($key == $ordering) {
				$rItems[$fromOrderingEntry[$this->keyName]] = $fromOrderingEntry;
				$rItems[$fromOrderingEntry[$this->keyName]]['ordering'] = $key;
			} elseif ($key == $toOrdering) {
				$rItems[$toOrderingEntry[$this->keyName]] = $toOrderingEntry;
				$rItems[$toOrderingEntry[$this->keyName]]['ordering'] = $key;
			} else {
				$rItems[$item[$this->keyName]] = $item;
			}
		}
		$this->data = $rItems;
		return true;
	}
	function output() {
		return array_values($this->data);
	}
}
class oseJsonGridRow extends oseObject {
	protected $ordering = 0;
	protected $_keyName = null;
	function __construct($info = array()) {
		foreach ($info as $k => $v) {
			$this->set($k, $v);
		}
	}
	function create() {
	}
	function update() {
	}
	function delete() {
	}
}
?>