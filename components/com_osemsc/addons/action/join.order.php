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
class oseMscAddonActionJoinOrder {
	public static function save($params) {
		$result = array();
		$result['success'] = true;
		if (empty($params['allow_work'])) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.1');
			return $result;
		}
		unset($params['allow_work']);
		if ($params['join_from'] != 'payment') {
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done Join.Order');
			return $result;
		}
		$db = oseDB::instance();
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];
		$where = array();
		$where[] = "order_id = {$order_id}";
		$payment = oseRegistry::call('payment');
		$curOrder = $payment->getOrder($where, 'obj');
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$memParams = $member->getMemberInfo($msc_id, 'obj')->memParams;
		if (empty($memParams)) {
			$memParams = new stdClass();
		} else {
			$memParams = oseJSON::decode($memParams);
		}
		$memParams->order_id = $order_id;
		$memParams->payment_mode = $curOrder->payment_mode;
		$memParams->order_item_id = $order_item_id;
		// Order problem for system add
		$memParams = oseJSON::encode($memParams);
		$query = " UPDATE `#__osemsc_member`" . " SET `params` = " . $db->Quote($memParams) . " WHERE msc_id = {$msc_id} AND member_id = {$member_id}";
		$memConfig = oseMscConfig::getConfig('register', 'obj');
		$db->setQuery($query);
		if (!oseDB::query()) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Order.2');
		}
		// User activation function;
		$params = JComponentHelper::getParams('com_users');
		$useractivation = $params->get('useractivation');
		if (oseObject::getValue($memConfig, 'disabled_non_paid', false) == true) {
			if ($useractivation == 0) {
				$query = " UPDATE `#__users` SET `block` = 0 where `id` = " . $member_id;
				$db->setQuery($query);
				$db->query();
			}
		}
		return $result;
	}
	public static function cancel($params) {
		$result = array();
		$result['success'] = true;
		if (empty($params['allow_work'])) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error Join.Order');
			return $result;
		}
		unset($params['allow_work']);
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");
		return $result;
	}
	public function remove($params) {
		$result = array();
		$result['success'] = true;
		if (empty($params['allow_work'])) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		return $result;
	}
}
?>