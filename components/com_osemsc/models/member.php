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
class osemscModelMember extends oseMscModel {
	function __construct() {
		parent::__construct();
	}
	function getOwnMsc() {
		$member = oseRegistry::call('member');
		$user = JFactory::getUser();
		$member->instance($user->id);
		$db = oseDB::instance();
		$query = " SELECT acl.*,mem.*, (SELECT DATEDIFF(mem.expired_date,NOW())) AS days_left FROM `#__osemsc_member` AS mem "
				. " INNER JOIN `#__osemsc_acl` AS acl ON acl.id = mem.msc_id" . " WHERE member_id = {$user->id}";
		$db->setQuery($query);
		return oseDB::loadList();
	}
	function getPaymentMode($msc_id) {
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'payment', 'obj');
		$items = array();
		if ($ext->payment_mode == 'a') {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('Automatic Renewing'));
		} elseif ($ext->payment_mode == 'm') {
			$items[] = array('id' => 1, 'value' => 'm', 'text' => JText::_('Manual Renewing'));
		} else {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('Automatic Renewing'));
			$items[] = array('id' => 2, 'value' => 'm', 'text' => JText::_('Manual Renewing'));
		}
		return $items;
	}
	function isMaster() {
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		$member->instance($user->id);
		$isMember = $member->isMember(null);
		if ($isMember) {
			if ($isMember == 'master') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function getAddons($addon_type) {
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		$view = $member->getInstance('PanelView');
		$member->instance($user->id);
		$result = $member->getMemberPanelView('Member', $addon_type);
		if ($result['addons']) {
			$items = array_values($result['addons']);
			return $items;
		}
	}
	function uniqueUserName($username) {
		$db = oseDB::instance();
		$user = JFactory::getUser();
		$where = array();
		$username = $db->Quote(strtolower($username));
		$where[] = "LOWER(username) = {$username}";
		$where[] = "id != {$user->id}";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) FROM `#__users`" . $where;
		$db->setQuery($query);
		$isValid = ($db->loadResult() > 0) ? false : true;
		return $isValid;
	}
}
?>