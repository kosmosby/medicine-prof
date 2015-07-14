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
class osemscControllerCreditCardUpdate extends osemscController {
	function __construct() {
		parent::__construct();
	}
	public function getOrders() {
		$start = JRequest::getInt('start', 0);
		$limit = JRequest::getInt('limit', 0);
		$type = JRequest::getInt('type', 0);
		$db = oseDB::instance();
		$user = JFactory::getUser();
		$where = array();
		$filterStatus = JRequest::getString('filter_status', null);
		$where[] = "o.entry_type IN ('msc','msc_list')";
		$where[] = "o.payment_from != 'system_admin'";
		$where[] = "o.`user_id` = '{$user->id}'";
		$where[] = "o.`payment_method` IN ('beanstream','authorize','paypal_cc')";
		$where[] = "o.`payment_mode` = 'a' ";
		$where[] = "o.`order_status`='confirmed'";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) " . " FROM `#__osemsc_order` AS o " . " INNER JOIN `#__users` AS u ON u.id = o.user_id" . $where;
		$db->setQuery($query);
		$total = $db->loadResult();
		$query = " SELECT CONCAT('Order:',o.order_id) AS title, u.username,u.name, o.* " . " FROM `#__osemsc_order` AS o " . " INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where . " ORDER BY o.create_date DESC";
		$db->setQuery($query, $start, $limit);
		$items = oseDB::loadList();
		$return = array();
		$i = 0;
		foreach ($items as $item) {
			$item['mscTitle'] = self::getMSCTitle($item['order_id']);
			$item['name'] = $item['user_id'] . ' - ' . $item['name'];
			$item['title'] = $item['title'] . ' - ' . $item['mscTitle'];
			$return[$i] = $item;
			$i++;
		}
		$result = array();
		$result['total'] = $total;
		$result['results'] = $return;
		$result = oseJson::encode($result);
		oseExit($result);
	}
	private function getMSCTitle($orderID) {
		$db = oseDB::instance();
		$query = " SELECT acl.title FROM `#__osemsc_acl` AS acl, `#__osemsc_order_item` AS oitem " . " WHERE oitem.order_id = " . (int) $orderID . " AND acl.id = oitem.entry_id";
		$db->setQuery($query);
		$titles = $db->loadObjectlist();
		$return = array();
		foreach ($titles as $title) {
			$return[] = $title->title;
		}
		$return = implode("<br />", $return);
		return $return;
	}
	function update() {
		$db = oseDB::instance();
		$user = JFactory::getUser();
		$query = " SELECT * FROM `#__osemsc_order`" . " WHERE `user_id` = '{$user->id}' AND `payment_method` IN ('beanstream','authorize','paypal_cc')"
				. " AND `payment_mode` = 'a' AND `order_status`='confirmed'";
		$db->setQuery($query);
		$list = oseDB::loadList();
		if (count($list) < 1) {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error');
			$result = oseJson::encode($result);
		}
		$creditInfo = array();
		$post = JRequest::get('post');
		$order_id = JRequest::getInt('order_id');
		$creditInfo['creditcard_type'] = $post['creditcard_type'];
		$creditInfo['creditcard_name'] = $post['creditcard_name'];
		$creditInfo['creditcard_owner'] = $creditInfo['creditcard_name'];
		$creditInfo['creditcard_number'] = JRequest::getCmd('creditcard_number');
		$creditInfo['creditcard_year'] = $post['creditcard_year'];
		$creditInfo['creditcard_month'] = $post['creditcard_month'];
		$creditInfo['creditcard_expirationdate'] = $post['creditcard_year'] . '-' . $post['creditcard_month'];
		$creditInfo['creditcard_cvv'] = $post['creditcard_cvv'];
		$payment = oseRegistry::call('payment');
		$pOrder = new osePaymentOrder();
		$orderInfo = $pOrder->getOrder(array('`order_id` = ' . $order_id), 'obj');
		switch ($orderInfo->payment_method) {
		case ('beanstream'):
			$updated = $pOrder->BeanStreamModify($orderInfo, $creditInfo);
			break;
		case ('authorize'):
			$updated = $pOrder->AuthorizeARBUpdateProfile($orderInfo, $creditInfo);
			$result = array();
			if ($updated['success']) {
				$result['success'] = true;
				$result['title'] = JText::_('Success');
				$result['content'] = JText::_('Updated!');
			} else {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = $updated['content'];
			}
			$result = oseJson::encode($result);
			oseExit($result);
			break;
		case ('paypal_cc'):
			$updated = $pOrder->PaypalAPIUpdateCreditCard($orderInfo, $creditInfo);
			$result = array();
			if ($updated['success']) {
				$result['success'] = true;
				$result['title'] = JText::_('Success');
				$result['content'] = JText::_('Updated!');
			} else {
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = $updated['content'];
			}
			$result = oseJson::encode($result);
			oseExit($result);
			break;
		default:
			break;
		}
		$result = array();
		if ($updated) {
			$result['success'] = true;
			$result['title'] = JText::_('Success');
			$result['content'] = JText::_('Updated!');
		} else {
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error');
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
?>