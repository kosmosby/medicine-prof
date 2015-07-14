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
class oseMscModelMemlist extends oseMscModel {
	public function __construct() {
		parent::__construct();
	}
	public function getModMemlist() {
		$search = JRequest::getString('search', null);
		$start = JRequest::getInt('start', 0);
		$limit = JRequest::getInt('limit', 20);
		if (JOOMLA16 || JOOMLA17) {
			$module = JModuleHelper::getModule('osememlist');
			$params = oseJSON::decode($module->params);
			$msc_id = $params->msc;
			$status = $params->status;
		} else {
			$module = JModuleHelper::getModule('osememlist');
			$params = new JParameter($module->params);
			$msc_id = $params->get('msc');
			$status = $params->get('status');
		}
		$db = oseDB::instance();
		$where = array();
		$where[] = ' m.msc_id = ' . $msc_id;
		if (isset($status) && $status != 'all') {
			$where[] = ' m.status = ' . $status;
		}
		if (!empty($search)) {
			$searchQuery = $db->Quote('%' . $search . '%');
			$where[] = "m.username LIKE {$searchQuery} OR m.name LIKE {$searchQuery} OR m.email LIKE {$searchQuery}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) " . " FROM `#__osemsc_member_view` AS m " . " INNER JOIN `#__osemsc_billinginfo` AS b ON m.member_id = b.user_id" . $where;
		$db->setQuery($query);
		$total = $db->loadResult();
		$query = " SELECT * " . " FROM `#__osemsc_member_view` AS m " . " INNER JOIN `#__osemsc_billinginfo` AS b ON m.member_id = b.user_id" . $where;
		$db->setQuery($query, $start, $limit);
		$items = oseDB::loadList();
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;
	}
	public function getMemlist() {
		$search = JRequest::getString('search', null);
		$start = JRequest::getInt('start', 0);
		$limit = JRequest::getInt('limit', 20);
		$msc_id = JRequest::getInt('msc');
		$status = JRequest::getVar('status');
		$db = oseDB::instance();
		$where = array();
		$where[] = ' m.msc_id = ' . $msc_id;
		if (isset($status) && $status != 'all') {
			$where[] = ' m.status = ' . $status;
		}
		if (!empty($search)) {
			$searchQuery = $db->Quote('%' . $search . '%');
			$where[] = "m.username LIKE {$searchQuery} OR m.name LIKE {$searchQuery} OR m.email LIKE {$searchQuery}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) " . " FROM `#__osemsc_member_view` AS m " . " INNER JOIN `#__osemsc_billinginfo` AS b ON m.member_id = b.user_id" . $where;
		$db->setQuery($query);
		$total = $db->loadResult();
		$query = " SELECT * " . " FROM `#__osemsc_member_view` AS m " . " INNER JOIN `#__osemsc_billinginfo` AS b ON m.member_id = b.user_id" . $where;
		$db->setQuery($query, $start, $limit);
		$items = oseDB::loadList();
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;
	}
}
?>