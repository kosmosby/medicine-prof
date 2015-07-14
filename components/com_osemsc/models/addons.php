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
class osemscModelAddons extends oseMscModel {
	function __construct() {
		parent::__construct();
	}
	function getAddonCats($addon_type) {
		if (empty($addon_type)) {
			return false;
		}
		$user = &JFactory::getUser();
		if ($user->guest) {
			return false;
		}
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php');
		$db = oseDB::instance();
		$content_ids = oseRegistry::call('content')->getRestrictedContent($addon_type, 'category');
		$content_ids = (!empty($content_ids)) ? "(" . implode(",", $content_ids) . ")" : "(0)";
		$db = oseDB::instance();
		switch ($addon_type) {
		case 'phoca': 
			$query = " SELECT c.title as mscTitle, b.content_id, b.entry_id, cat.title "
					. " FROM `#__osemsc_member` as a, `#__osemsc_acl` as c, `#__osemsc_content` as b, `#__phocadownload_categories` AS cat"
					. " WHERE a.member_id = {$user->id} AND a.msc_id=b.entry_id " . " AND b.entry_type='msc' AND b.content_type = 'category' "
					. " AND b.type='phoca' AND b.content_id = cat.id " . " AND a.msc_id = c.id AND a.status = 1 AND cat.published=1" . " AND b.status = 1 "
					. " ORDER BY b.entry_id ASC ";
			break;
		default:
			$query = '';
			break;
		}
		if (!empty($query)) {
			$db->setQuery($query);
			$items = $db->loadObjectList();
			$items = self::reArrangeArray($items);
			return $items;
		} else {
			return false;
		}
	}
	function getAddonInfo($addon_type) {
		if (empty($addon_type)) {
			return false;
		}
		$user = &JFactory::getUser();
		if ($user->guest) {
			return false;
		}
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php');
		$db = oseDB::instance();
		switch ($addon_type) {
		case 'roku':
			$query = " SELECT roku.*, mem.status FROM `#__osemsc_extRoku` as roku " . " LEFT JOIN  `#__osemsc_member` as mem ON mem.member_id = roku.userID "
					. " WHERE mem.member_id = " . (int) $user->id;
			$db->setQuery($query);
			$results = $db->loadObject();
			break;
		default:
			$query = '';
			$results = '';
			break;
		}
		if (!empty($results)) {
			return $results;
		} else {
			return false;
		}
	}
	function registerCode($regCode, $userID) {
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_extRoku` WHERE `regCode` = '{$regCode}' LIMIT 1";
		$db->setQuery($query);
		$results = $db->loadObject();
		if (!empty($results)) {
			if (!empty($results->userID)) {
				return "registered";
			} else {
				$query = "UPDATE `#__osemsc_extRoku` SET `userID` = '{$userID}' WHERE `regCode` ='{$regCode}' LIMIT 1 ;";
				$db->setQuery($query);
				if ($db->query()) {
					return "success";
				}
			}
		} else {
			return false;
		}
	}
	function hasMembership($userID, $status = 1) {
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_member` WHERE member_id = " . (int) $userID . "  AND status = " . (int) $status;
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}
	function reArrangeArray($items) {
		if (empty($items)) {
			return false;
		}
		$return = array();
		$msc_id = 0;
		$i = 0;
		foreach ($items as $item) {
			if ($msc_id != $item->entry_id) {
				$msc_id = $item->entry_id;
				$i = 0;
			}
			$return[$msc_id][$i]['msc_id'] = $item->entry_id;
			$return[$msc_id][$i]['content_id'] = $item->content_id;
			$return[$msc_id][$i]['title'] = $item->title;
			$return[$msc_id][$i]['mscTitle'] = $item->mscTitle;
			$i++;
		}
		return $return;
	}
}
?>
