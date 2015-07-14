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
abstract class oseInit {
	protected $task = array();
	protected $instance = array();
	protected function registerTask($task, $funcName) {
		$this->task[$task] = $funcName;
	}
	protected function registerInstance($task, $instanceName) {
		$this->instance[$task] = $instanceName;
	}
	function __call($name, $args) {
		if (isset($this->task[$name])) {
			return call_user_func_array(array($this, $this->task[$name]), $args);
		} else {
			oseExit($name . 'Error');
		}
	}
	function get($key, $default = null) {
		if (isset($this->{$key})) {
			return $this->{$key};
		}
		return $default;
	}
	function set($key, $value = null) {
		$oldkey = $this->get($key);
		$this->{$key} = $value;
		return $oldkey;
	}
	function __toString() {
		return get_class($this);
	}
	protected function toArray() {
		$vars = get_class_vars(get_class($this));
		unset($vars['task']);
		unset($vars['instance']);
		unset($vars['prefix']);
		unset($vars['table']);
		unset($vars['isNew']);
		foreach ($vars as $key => $value) {
			$vars[$key] = $this->get($key);
		}
		return $vars;
	}
	function create($xtype = false, $config = array()) {
		$vals = $this->toArray();
		unset($vals['id']);
		$vals['params'] = oseJson::encode($vals['params']);
		return oseDB::insert($this->table, $vals);
	}
	function update() {
		$vals = $this->toArray();
		$vals['params'] = oseJson::encode($vals['params']);
		return oseDB::update($this->table, 'id', $vals);
	}
	function delete() {
		$where = array();
		$where['id'] = $this->get('id');
		return oseDB::delete($this->table, $where);
	}
	function __construct($info = array()) {
		$params = oseGetValue($info, 'params');
		if (!empty($info)) {
			if (empty($params)) {
				$info = oseSetValue($info, 'params', '{}');
			}
			foreach ($info as $key => $value) {
				if (in_array($key, array('params', 'data', 'transactions'))) {
					if (!is_array($value) && !is_object($value) && is_string($value)) {
						if ($key == 'data') {
							$this->set($key, oseJson::decode($value, true));
						} else {
							$this->set($key, oseJson::decode($value));
						}
					} else {
						if (empty($value)) {
							$this->set($key, $this->get($key));
						} else {
							$this->set($key, $value);
						}
					}
				} else {
					$this->set($key, $value);
				}
			}
		} else {
			if (empty($params)) {
				$params = new stdClass();
				$this->set('params', $params);
			}
		}
	}
}
?>
