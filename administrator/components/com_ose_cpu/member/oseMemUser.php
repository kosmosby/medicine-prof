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
class oseMemUser {
	function __construct($user_id = 0) {
		$this->instance($user_id);
	}
	function instance($user_id) {
		$this->user_id = $user_id;
	}
	function getUserInfo() {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_userinfo_view` WHERE `user_id` = ". (int)$this->user_id;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		if (empty($item)) {
			$query = " INSERT INTO `#__osemsc_userinfo` (`user_id`) VALUES (".(int)$this->user_id.")";
			$db->setQuery($query);
			oseDB::query();
			$item = self::getUserInfo();
		}
		return $item;
	}
	function getBillingInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT c.*,u.username, u.email AS user_email FROM `#__users` AS u" 
				. " LEFT JOIN `#__osemsc_billinginfo` AS c ON c.user_id = u.id"
				. " WHERE c.user_id = ". (int)$this->user_id;
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function getBasicInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT id,username,name,email,params " 
			   . " FROM `#__users` WHERE id = ". (int)$this->user_id;
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function uniqueUserName($username, $user_id = 0) {
		$db = oseDB::instance();
		$where = array();
		$username = $db->Quote(strtolower($username));
		$where[] = "LOWER(username) = {$username}";
		if (!empty($user_id)) {
			$where[] = "id != {$user_id}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) FROM `#__users`" . $where;
		$db->setQuery($query);
		$isValid = ($db->loadResult() > 0) ? false : true;
		return $isValid;
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
?>