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
defined('_JEXEC') or die(";)");
class oseUser {
	protected $user_id = null;
	protected $task = array();
	protected $instance = array();
	function __construct() {
		$this->setRegisteredTasks();
		$this->setRegisteredInstances();
		$instance = self::getInstanceByVersion();
		$this->user_id = $instance->get('id');
	}
	function instance($user_id) {
		$this->user_id = $user_id;
	}
	function __toString() {
		return get_class($this);
	}
	protected function registerTask($task, $funcName) {
		$this->task[$task] = $funcName;
	}
	protected function setRegisteredTasks() {
		// NULL
	}
	protected function registerInstance($task, $instanceName) {
		$this->instance[$task] = $instanceName;
	}
	protected function setRegisteredInstances() {
		// NULL
		$this->registerInstance('self', 'JUser');
	}
	public function getInstanceByVersion($params = array()) {
		static $instance;
		if (!empty($instance)) {
			return $instance;
		}
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		if ($version == '1.5') {
			$instance = JFactory::getUser();
		} else {
			$instance = JFactory::getUser();
		}
		return $instance;
	}
	function getUserInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_userinfo_view` WHERE user_id = ". (int)$this->user_id;
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		if (empty($item)) {
			$query = " SELECT * FROM `#__osemsc_userinfo` WHERE user_id = ". (int)$this->user_id;
			$db->setQuery($query);
			$item = oseDB::loadItem($type);
			if (empty($item)) {
				$query = " INSERT INTO `#__osemsc_userinfo` (user_id) VALUES (". (int)$this->user_id.")";
				$db->setQuery($query);
				$db->query();
				$item = self::getUserInfo($type);
			}
		}
		return $item;
	}
	function getBillingInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT c.*,u.username,u.name,u.name AS jname, u.email AS user_email,u.id  FROM `#__users` AS u" 
				. " LEFT JOIN `#__osemsc_billinginfo` AS c ON c.user_id = u.id"
				. " WHERE u.id = ".(int)$this->user_id;
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		$item = oseObject::setValue($item, 'user_id', oseObject::getValue($item, 'id', 0));
		return $item;
	}
	function getBasicInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT id,username,name,email,params " . " FROM `#__users` " . " WHERE id = {$this->user_id}";
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function init($user_id = null) {
		if (empty($user_id)) {
			$user = JFactory::getUser();
		} else {
			$user = JFactory::getUser($user_id);
		}
		return $user;
	}
	function getProfile() {
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$items = oseDB::loadList('obj');
		$return = array();
		$i = 0;
		if (!empty($items)) {
			foreach ($items as $item) {
				$return[$i]['name'] = 'custom.' . strtolower(str_replace(" ", "_", $item->name));
				$query = "SELECT value FROM `#__osemsc_fields_values` WHERE `field_id` = " . (int) $item->id . " AND `member_id` = " . (int) $this->user_id;
				$db->setQuery($query);
				$return[$i]['value'] = $db->loadResult();
				$i++;
			}
		}
		return $return;
	}
}