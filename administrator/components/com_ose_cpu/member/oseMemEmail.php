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
class oseMemEmail {
	function buildEmailParams($type) {
		$params = array();
		switch ($type) {
		case ('pay_offline'):
		case ('cancelorder_email'):
		case ('receipt'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.jname';
			$params['user.email'] = 'user.email';
			$params['user.user_status'] = 'user.block';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.primary_contact';
			$params['user.company'] = 'user.company';
			$params['user.address1'] = 'user.addr1';
			$params['user.address2'] = 'user.addr2';
			$params['user.city'] = 'user.city';
			$params['user.state'] = 'user.state';
			$params['user.country'] = 'user.country';
			$params['user.postcode'] = 'user.postcode';
			$params['user.telephone'] = 'user.telephone';
			$params['user.user_id'] = 'user.user_id';
			$params['order.order_id'] = 'order.order_id';
			$params['order.order_number'] = 'order.order_number';
			$params['order.order_status'] = 'order.order_status';
			$params['order.vat_number'] = 'order.vat_number';
			$params['order.subtotal'] = 'order.subtotal';
			$params['order.total'] = 'order.total';
			$params['order.gross_tax'] = 'order.gross_tax';
			$params['order.discount'] = 'order.discount';
			$params['order.itemlist'] = 'order.itemlist';
			$params['order.payment_method'] = 'order.payment_method';
			$params['order.date'] = 'order.create_date';
			$params['order.payment_mode'] = 'order.payment_mode';
			$params['order.recurring_price'] = 'order.recurring_price';
			$params['order.recurring_frequency'] = 'order.recurring_frequency';
			$params['member.start_date'] = 'member.start_date';
			$params['member.msc_title'] = 'member.msc_title';
			$params['member.msc_des'] = 'member.msc_des';
			$params['order.current_date'] = 'order.current_date';
			break;
		case ('wel_email'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.jname';
			$params['user.email'] = 'user.email';
			$params['user.user_status'] = 'user.block';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.primary_contact';
			$params['user.company'] = 'user.company';
			$params['user.address1'] = 'user.addr1';
			$params['user.address2'] = 'user.addr2';
			$params['user.city'] = 'user.city';
			$params['user.state'] = 'user.state';
			$params['user.country'] = 'user.country';
			$params['user.postcode'] = 'user.postcode';
			$params['user.telephone'] = 'user.telephone';
			$params['member.start_date'] = 'member.start_date';
			$params['member.expired_date'] = 'member.real_expired_date';
			$params['member.period'] = 'member.period';
			$params['member.msc_title'] = 'member.msc_title';
			$params['member.msc_des'] = 'member.msc_des';
			$params['order.order_id'] = 'order.order_id';
			$params['order.order_number'] = 'order.order_number';
			$params['order.order_status'] = 'order.order_status';
			$params['order.subtotal'] = 'order.subtotal';
			$params['order.total'] = 'order.total';
			$params['order.discount'] = 'order.discount';
			$params['order.table'] = 'order.table';
			$params['order.payment_method'] = 'order.payment_method';
			$params['order.date'] = 'order.create_date';
			break;
		case ('reg_email'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.name';
			$params['user.password'] = 'user.password';
			$params['user.email'] = 'user.email';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.primary_contact';
			$params['user.user_status'] = 'user.block';
			break;
		case ('cancel_email'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.jname';
			$params['user.email'] = 'user.email';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.primary_contact';
			$params['member.start_date'] = 'member.start_date';
			$params['member.expired_date'] = 'member.expired_date';
			$params['member.msc_title'] = 'member.msc_title';
			$params['member.msc_des'] = 'member.msc_des';
			break;
		case ('exp_email'):
		case ('invitation'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.jname';
			$params['user.email'] = 'user.email';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.block';
			$params['member.start_date'] = 'member.start_date';
			$params['member.expired_date'] = 'member.expired_date';
			$params['member.msc_title'] = 'member.msc_title';
			$params['member.msc_des'] = 'member.msc_des';
			break;
		case ('notification'):
			$params['user.username'] = 'user.username';
			$params['user.name'] = 'user.jname';
			$params['user.email'] = 'user.email';
			$params['user.firstname'] = 'user.firstname';
			$params['user.lastname'] = 'user.lastname';
			$params['user.primary_contact'] = 'user.block';
			$params['member.start_date'] = 'member.start_date';
			$params['member.expired_date'] = 'member.expired_date';
			$params['member.msc_title'] = 'member.msc_title';
			$params['member.msc_des'] = 'member.msc_des';
			break;
		default:
			$params = array();
			break;
		}
		return $params;
	}
	function getEmailVariablesRegistration($user_id, $order_id = null) {
		$db = oseDB::instance();
		$content = array();
		if (empty($order_id)) {
			$member = oseRegistry::call('member');
			$member->instance($user_id);
			$userInfo = $member->getBillingInfo('obj');
			$userInfo->email = $userInfo->user_email;
			$content['user'] = $userInfo;
		} else {
			$member = oseRegistry::call('member');
			$member->instance($user_id);
			$userInfo = $member->getBillingInfo('obj');//oseDB::loadItem('obj');
			$userInfo->email = $userInfo->user_email;
			$content['user'] = $userInfo;
		}
		return $content;
	}
	function getEmailVariablesWelcome($order_id, $msc_id) {
		$db = oseDB::instance();
		$content = array();
		// get Order Info
		$payment = oseRegistry::call('payment');
		$where = array();
		$where[] = "order_id = {$order_id}";
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		// get User Info
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$userInfo = $member->getBillingInfo('obj');
		$userInfo->email = $userInfo->user_email;
		// get Member Info
		$query = " SELECT *,DATE_SUB(`expired_date`,INTERVAL 1 DAY) AS `real_expired_date` " . " FROM `#__osemsc_member`"
				. " WHERE member_id = {$orderInfo->user_id} AND msc_id={$msc_id}";
		$db->setQuery($query);
		$memberInfo = oseDB::loadItem('obj');
		$memberInfoParams = oseJson::decode($memberInfo->params);
		switch ($orderInfo->entry_type) {
		case ('msc_list'):
			$orderInfo->subtotal = $orderInfoParams->subtotal;
			$orderInfo->total = $orderInfoParams->total;
			$orderInfo->discount = $orderInfoParams->discount;
			$where = array();
			$where[] = "`order_item_id` = '{$memberInfoParams->order_item_id}'";
			$orderItemInfo = $payment->getOrderItem($where, 'obj');
			$orderItemParams = oseJson::decode($orderItemInfo->params);
			if ($orderInfo->payment_mode == 'm') {
				$orderInfo->payment_mode = JText::_('Manual Billing');
				if ($orderItemParams->recurrence_mode == 'fixed') {
					$memberInfo->period = JText::_('Period: From ') . $paymentInfo->start_date;
					$memberInfo->period .= JText::_(' To ') . $paymentInfo->expired_date;
				} else {
					$memberInfo->period = JText::_('Period: ');
					$memberInfo->period .= $orderItemParams->p3 . ' ' . $orderItemParams->t3;
				}
				if ($memberInfo->eternal) {
					$memberInfo->period = 'Life Time';
				}
			} else {
				$orderInfo->payment_mode = JText::_('Automatic Billing');
				$price = null;
				if ($orderItemParams->has_trial) {
					$price = 'Trial: ' . $orderInfo->payment_currency . ' ' . $orderItemParams->a1;
					$hasS1 = ($orderItemParams->p1 > 1) ? 's' : '';
					$memberInfo->period = JText::_("Trial: {$orderItemParams->p1} {$orderItemParams->t1}{$hasS1}");
				}
				$price .= ' Recurring: ' . $orderInfo->payment_currency . ' ' . $orderItemParams->a3;
				$hasS3 = ($orderItemParams->p3 > 1) ? 's' : '';
				$memberInfo->period = JText::_("Recurrence: {$orderItemParams->p3} {$orderItemParams->t3}{$hasS3}");
				$orderInfo->price = JText::_('Automatic Billing:') . '' . $price;
			}
			break;
		default:
		case ('msc'):
			$orderInfo->subtotal = $orderInfoParams->a3;
			$orderInfo->total = $orderInfoParams->a3;
			$orderInfo->discount = 0;
			if ($orderInfo->payment_mode == 'm') {
				$orderInfo->payment_mode = JText::_('Manual Billing');
				if ($orderInfoParams->recurrence_mode == 'fixed') {
					$memberInfo->period = JText::_('Period: From ') . $paymentInfo->start_date;
					$memberInfo->period .= JText::_(' To ') . $paymentInfo->expired_date;
				} else {
					$memberInfo->period = JText::_('Period: ');
					$memberInfo->period .= $orderInfoParams->p3 . ' ' . $orderInfoParams->t3;
				}
				if ($memberInfo->eternal) {
					$memberInfo->period = 'Life Time';
				}
			} else {
				$orderInfo->payment_mode = JText::_('Automatic Billing');
				$price = null;
				if ($params->has_trial) {
					$price = 'Trial: ' . $orderInfo->payment_currency . ' ' . $orderInfoParams->a1;
					$hasS1 = ($orderInfoParams->p1 > 1) ? 's' : '';
					$memberInfo->period = JText::_("Trial: {$orderInfoParams->p1} {$orderInfoParams->t1}{$hasS1}");
				}
				$price .= ' Recurring: ' . $orderInfo->payment_currency . ' ' . $orderInfoParams->a3;
				$hasS3 = ($orderInfoParams->p3 > 1) ? 's' : '';
				$memberInfo->period = JText::_("Recurrence: {$orderInfoParams->p3} {$orderInfoParams->t3}{$hasS3}");
				$orderInfo->price = JText::_('Automatic Billing:') . '' . $price;
			}
			break;
		}
		$query = "SELECT * FROM `#__osemsc_acl` WHERE `id` = {$msc_id}";
		$db->setQuery($query);
		$mscInfo = oseDB::loadItem('obj');
		$memberInfo->msc_title = $mscInfo->title;
		$memberInfo->msc_des = $mscInfo->description;
		$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
		if (!empty($globalConfig->DateFormat)) {
			$orderInfo->create_date = date($globalConfig->DateFormat, strtotime($orderInfo->create_date));
			$memberInfo->start_date = date($globalConfig->DateFormat, strtotime($memberInfo->start_date));
			$memberInfo->expired_date = date($globalConfig->DateFormat, strtotime($memberInfo->expired_date));
			$memberInfo->real_expired_date = date($globalConfig->DateFormat, strtotime($memberInfo->real_expired_date));
		}
		$content['user'] = $userInfo;
		$content['order'] = $orderInfo;
		$content['member'] = $memberInfo;
		$content['profile'] = $member->getProfile();
		return $content;
	}
	function getEmailVariablesCancel($user_id, $msc_id) {
		$db = oseDB::instance();
		$content = array();
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$memMscInfo = $member->getMembership($msc_id, 'obj');
		$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
		if (!empty($globalConfig->DateFormat)) {
			$memMscInfo->start_date = date($globalConfig->DateFormat, strtotime($memMscInfo->start_date));
			$memMscInfo->expired_date = date($globalConfig->DateFormat, strtotime($memMscInfo->expired_date));
		}
		$query = "SELECT * FROM `#__osemsc_acl` WHERE `id` = {$msc_id}";
		$db->setQuery($query);
		$mscInfo = oseDB::loadItem('obj');
		$memMscInfo->msc_title = $mscInfo->title;
		$memMscInfo->msc_des = $mscInfo->description;
		$userInfo = $member->getBillingInfo('obj');
		$userInfo->email = $userInfo->user_email;
		$content['user'] = $userInfo;
		$content['member'] = $memMscInfo;
		$payment = oseRegistry::call('payment');
		return $content;
	}
	function getEmailVariablesExpire($user_id, $msc_id) {
		$db = oseDB::instance();
		$content = array();
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$memMscInfo = $member->getMembership($msc_id, 'obj');
		$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
		if (!empty($globalConfig->DateFormat)) {
			$memMscInfo->start_date = date($globalConfig->DateFormat, strtotime($memMscInfo->start_date));
			$memMscInfo->expired_date = date($globalConfig->DateFormat, strtotime($memMscInfo->expired_date));
		}
		$query = "SELECT * FROM `#__osemsc_acl` WHERE `id` = {$msc_id}";
		$db->setQuery($query);
		$mscInfo = oseDB::loadItem('obj');
		$memMscInfo->msc_title = $mscInfo->title;
		$memMscInfo->msc_des = $mscInfo->description;
		$userInfo = $member->getBillingInfo('obj');
		$userInfo->email = $userInfo->user_email;
		$content['user'] = $userInfo;
		$content['member'] = $memMscInfo;
		$payment = oseRegistry::call('payment');
		return $content;
	}
	public static function transEmail($email, $content_item, $params) {
		$db = JFactory::getDBO();
		$tEmailSubject = $email->subject;
		$tEmailBody = $email->body;
		foreach ($params as $key => $param) {
			$arr = explode('.', $param);
			$valueType = $arr[0];
			$valueName = $arr[1];
			if (isset($content_item[$valueType]->{$valueName})) {
				$replace = $content_item[$valueType]->{$valueName};
				if ($valueName === 'payment_method' || $valueName === 'order_status') {
					$replace = JText::_(strtoupper($replace));
				}
			} else {
				$replace = null;
			}
			$tKey = $key;
			$tEmailSubject = str_replace("[{$tKey}]", $replace, $tEmailSubject);
			$tEmailBody = str_replace("[{$tKey}]", $replace, $tEmailBody);
		}
		$jroot = JURI::root();
		$jroot = explode("components", $jroot);
		$jroot = $jroot[0];
		$tEmailBody = str_replace("../", $jroot, $tEmailBody);
		// Convert Profile Fields; custom.
		if (!empty($content_item['profile'])) {
			foreach ($content_item['profile'] as $profile) {
				$tEmailBody = str_replace('[' . $profile['name'] . ']', $profile['value'], $tEmailBody);
			}
		}
		$email->subject = $tEmailSubject;
		$email->body = $tEmailBody;
		return $email;
	}
	function getDoc($id, $type = 'array') {
		if (empty($id))
		{
			return; 
		}
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_email` WHERE id = ". (int)$id;
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function getEmails($email_type, $start, $limit, $type = 'array') {
		$db = oseDB::instance();
		$where = array();
		$items = array();
		$item = ($type == 'array') ? array() : new stdClass();
		$item = oseObject::setValue($item, 'id', '0');
		$item = oseObject::setValue($item, 'subject', 'None');
		$items[] = $item;
		if (!empty($email_type)) {
			$where[] = ' type = ' . $db->Quote($email_type);
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_email` " . $where;
		$db->setQuery($query, $start, $limit);
		$items = oseDB::loadList($type);
		$result = array();
		$result['total'] = $this->getTotal($email_type);
		$result['results'] = $items;
		return $result;
	}
	function getTotal($email_type) {
		$db = oseDB::instance();
		$where = array();
		$items = array();
		if (!empty($email_type)) {
			$where[] = ' type = ' . $db->Quote($email_type);
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT COUNT(*) FROM `#__osemsc_email` " . $where;
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
	function getTerms($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_email` " . " WHERE type = 'term' ";
		$db->setQuery($query);
		return oseDB::loadList($type);
	}
	function getTerm($id, $type = 'array') {
		$db = oseDB::instance();
		$id = $db->Quote($id);
		$query = " SELECT * FROM `#__osemsc_email` " . " WHERE type = 'term' AND id = {$id} ";
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	protected function send($subject, $body, $address) {
		$mail = JFactory::getMailer();
		$mail->addRecipient($address);
		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->IsHTML(true);
		$mail->Send();
	}
	function sendEmail($email, $emailAddress = null) {
		if (empty($emailAddress)) {
			return false;
		}
		return self::send($email->subject, $email->body, $emailAddress);
	}
	function save($post) {
		$db = oseDB::instance();
		$id = isset($post['id']) ? $post['id'] : 0;
		unset($post['id']);
		$filter = array();
		if (empty($id)) {
			foreach ($post as $key => $value) {
				$filter[$key] = $db->Quote($value);
			}
			$values = implode(',', $filter);
			$query = " INSERT INTO `#__osemsc_email`" . " (body,subject,type,msc_id,params)" . " VALUES" . " ( $values)";
			$db->setQuery($query);
			if (oseDB::query()) {
				return $db->insertid();
			} else {
				return false;
			}
		} else {
			foreach ($post as $key => $value) {
				$filter[$key] = $key . '=' . $db->Quote($value);
			}
			$values = implode(',', $filter);
			$query = " UPDATE `#__osemsc_email`" . " SET $values" . " WHERE id = {$id}";
			$db->setQuery($query);
			if (oseDB::query()) {
				return $id;
			} else {
				return false;
			}
		}
	}
	function remove($email_id) {
		$db = oseDB::instance();
		$query = " DELETE FROM `#__osemsc_email`" . " WHERE id = {$email_id}";
		$db->setQuery($query);
		return oseDB::query();
	}
	function sendToAdminGroup($email, $group) {
		$db = oseDB::instance();
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		if ($version == '1.5') {
			$query = " SELECT * FROM `#__users` AS u " . " WHERE gid IN ( {$group} ) AND sendEmail =1";
		} else {
			$query = " SELECT u.* FROM `#__users` AS u " . " INNER JOIN `#__user_usergroup_map` AS g ON g.user_id = u.id" . " WHERE g.group_id IN ( {$group} )  AND sendEmail =1";
		}
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		foreach ($objs as $obj) {
			self::sendEmail($email, $obj->email);
		}
		return true;
	}
	function sendReceipt($orderInfo, $email) {
		$emailDetail = $this->getReceipt($orderInfo);
		return $this->sendEmail($emailDetail, $email);
	}
	function getReceipt($orderInfo) {
		$db = oseDB::instance();
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		$query = " SELECT * FROM `#__osemsc_email` " . " WHERE id = {$emailConfig->default_receipt}";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		$emailTempDetail = $this->getDoc($item->id, 'obj');
		$variables = $this->getEmailVariablesReceipt($orderInfo->order_id);
		$emailParams = $this->buildEmailParams('receipt');
		$emailDetail = $this->transEmail($emailTempDetail, $variables, $emailParams);
		return $emailDetail;
	}
	function getOrderNotification($orderInfo) {
		$db = oseDB::instance();
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		$query = " SELECT * FROM `#__osemsc_email` " . " WHERE id = {$emailConfig->order_notification}";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		$emailTempDetail = $this->getDoc($item->id, 'obj');
		$variables = $this->getEmailVariablesReceipt($orderInfo->order_id);
		$emailParams = $this->buildEmailParams('receipt');
		$emailDetail = $this->transEmail($emailTempDetail, $variables, $emailParams);
		return $emailDetail;
	}
	function generateOrderTable($order_id, $user_id) {
		$db = oseDB::instance();
		$payment = oseRegistry::call('payment');
		$where = array();
		$where[] = "order_id = {$order_id}";
		$orderInfo = $payment->getOrder($where,'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$query = " SELECT * FROM `#__osemsc_order_item`" . " WHERE `order_id` = '{$order_id}'";
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		$table = array();
		$tHeads = array();
		$tHeads[] = '<thead>';
		$tHeads[] = '<tr>';
		$tHeads[] = '<th>' . JText::_('MEMBERSHIP_PLAN') . '</th>';
		$tHeads[] = '<th>' . JText::_('START_DATE') . '</th>';
		$tHeads[] = '<th>' . JText::_('EXPIRED_DATE') . '</th>';
		$tHeads[] = '<th>' . JText::_('PRICE') . '</th>';
		$tHeads[] = '</tr>';
		$tHeads[] = '</thead>';
		$tBody = array();
		$tBody[] = '<tbody>';
		foreach ($objs as $obj) {
			switch ($obj->entry_type) {
			case ('msc'):
				$obj->payment_price = $orderInfoParams->recurrence_times >1 ?$orderInfoParams->next_total:$orderInfoParams->total;
				$msc_id = $obj->entry_id;
				break;
			case ('license'):
				$params = oseJson::decode($obj->params);
				$msc_id = $params->msc_id;
				break;
			}
			$query = " SELECT *,DATE_SUB(`expired_date`,INTERVAL 1 DAY) AS `real_expired_date` " . " FROM `#__osemsc_member`"
					. " WHERE member_id = {$user_id} AND msc_id={$msc_id}";
			$db->setQuery($query);
			$memberInfo = oseDB::loadItem('obj');
			$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
			if (!empty($globalConfig->DateFormat)) {
				$memberInfo->start_date = date($globalConfig->DateFormat, strtotime($memberInfo->start_date));
				if ($memberInfo->expired_date != '0000-00-00 00:00:00') {
					$memberInfo->expired_date = date($globalConfig->DateFormat, strtotime($memberInfo->expired_date));
				}
				if ($memberInfo->real_expired_date != '0000-00-00 00:00:00') {
					$memberInfo->real_expired_date = date($globalConfig->DateFormat, strtotime($memberInfo->real_expired_date));
				}
			}
			$query = " SELECT * FROM `#__osemsc_acl`" . " WHERE id = '{$msc_id}'";
			$db->setQuery($query);
			$item = oseDB::loadItem('obj');
			$tr = '<tr>';
			$tr .= "<td>{$item->title}</td>";
			$tr .= "<td>{$memberInfo->start_date}</td>";
			if ($obj->payment_mode == 'a') {
				$tr .= "<td>{$memberInfo->real_expired_date}</td>";
			} else {
				if ($memberInfo->eternal) {
					$memberInfo->expired_date = 'Life Time';
				}
				$tr .= "<td>{$memberInfo->expired_date}</td>";
			}
			$tr .= "<td>{$obj->payment_currency} {$obj->payment_price}</td>";
			$tr .= '</tr>';
			$tBody[] = $tr;
		}
		$tBody[] = '</tbody>';
		$table[] = '<table width="100%">';
		$table[] = implode("\r\n", $tHeads);
		$table[] = implode("\r\n", $tBody);
		$table[] = '</table>';
		$html = implode("\r\n", $table);
		return $html;
	}
	function getEmailVariablesReceipt($order_id, $user_id = null) {
		$db = oseDB::instance();
		$content = array();
		// get Order Info
		$payment = oseRegistry::call('payment');
		$where = array();
		$where[] = "order_id = {$order_id}";
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams = oseJson::decode($orderInfo->params);
		$paymentInfo = oseMscAddon::getExtInfo($orderInfo->entry_id, 'payment', 'obj');
		$member = oseRegistry::call('member');
		$member->instance($orderInfo->user_id);
		$userInfo = $member->getBillingInfo('obj');
		$userInfo->email = $userInfo->user_email;
		$orderInfo->subtotal = $orderInfoParams->subtotal;
		$orderInfo->total = $orderInfoParams->recurrence_times >1 ?$orderInfoParams->next_total:$orderInfoParams->total;
		$orderInfo->discount = (isset($orderInfoParams->discount)) ? $orderInfoParams->discount : 0;
		$gw = $payment->getInstance('GateWay');
		$gwInfo = $gw->getGWInfo($orderInfo->payment_method);
		if (!empty($gwInfo)) {
			if ($gwInfo->is_cc) {
				$orderInfo->payment_method = 'Credit Card';
			}
		}
		$orderInfo->itemlist = $this->generateOrderTable($order_id, $orderInfo->user_id);
		if ($orderInfo->payment_mode == 'm') {
			$orderInfo->payment_mode = JText::_('Manual Billing');
			$orderInfo->gross_tax = oseObject::getValue($orderInfoParams, 'gross_tax', '0.00');
			if ($orderInfo->gross_tax > 0) {
				$orderInfo->vat_number = oseObject::getValue($orderInfoParams, 'vat_number');
			}
		} else {
			$orderInfo->payment_mode = JText::_('Automatic Billing');
			$params = oseJson::decode($orderInfo->params);
			$price = null;
			if (oseObject::getValue($orderInfoParams, 'has_trial', 0)) {
				if (oseObject::getValue($orderInfoParams, 'recurrence_times', 0) <= 1) {
					$orderInfo->gross_tax = oseObject::getValue($orderInfoParams, 'gross_tax', '0.00');
				} else {
					$orderInfo->gross_tax = oseObject::getValue($orderInfoParams, 'next_gross_tax', '0.00');
				}
			} else {
				if (oseObject::getValue($orderInfoParams, 'recurrence_times', 0) <= 2) {
					$orderInfo->gross_tax = oseObject::getValue($orderInfoParams, 'gross_tax', '0.00');
				} else {
					$orderInfo->gross_tax = oseObject::getValue($orderInfoParams, 'next_gross_tax', '0.00');
				}
			}
			$orderInfo->vat_number = oseObject::getValue($orderInfoParams, 'vat_number');
			$orderInfo->recurring_price = $orderInfo->payment_currency . ' ' . $orderInfoParams->next_total;
			$orderInfo->recurring_frequency = $orderInfoParams->p3 . ' ' . $orderInfoParams->t3;
		}
		$query = "SELECT entry_id FROM `#__osemsc_order_item` WHERE `order_id` = " . $order_id;
		$db->setQuery($query);
		$msc_id = $db->loadResult();
		$query = " SELECT *,DATE_SUB(`expired_date`,INTERVAL 1 DAY) AS `real_expired_date` " . " FROM `#__osemsc_member`"
				. " WHERE member_id = {$orderInfo->user_id} AND msc_id={$msc_id}";
		$db->setQuery($query);
		$memberInfo = oseDB::loadItem('obj');
		$query = "SELECT * FROM `#__osemsc_acl` WHERE `id` = {$msc_id}";
		$db->setQuery($query);
		$mscInfo = oseDB::loadItem('obj');
		$memberInfo->msc_title = $mscInfo->title;
		$memberInfo->msc_des = $mscInfo->description;
		$orderInfo->current_date = oseHTML::getDateTime();
		$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
		if (!empty($globalConfig->DateFormat)) {
			$memberInfo->start_date = date($globalConfig->DateFormat, strtotime($memberInfo->start_date));
			$orderInfo->create_date = date($globalConfig->DateFormat, strtotime($orderInfo->create_date));
			$orderInfo->current_date = date($globalConfig->DateFormat, strtotime($orderInfo->current_date));
		}
		$content['user'] = $userInfo;
		$content['order'] = $orderInfo;
		$content['profile'] = $member->getProfile();
		$content['member'] = $memberInfo;
		return $content;
	}
	function sendCancelOrderEmail($params) {
		$orderInfo = oseObject::getValue($params, 'orderInfo');
		$user_id = $orderInfo->user_id;
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$userInfo = $member->getBasicInfo('obj');
		$msc_id = $orderInfo->entry_id;
		$emailConfig = oseRegistry::call('msc')->getConfig('email', 'obj');
		if ($emailConfig->default_cancelorder_email) {
			$emailTempDetail = $this->getDoc($emailConfig->default_cancelorder_email, 'obj');
			$variables = $this->getEmailVariablesReceipt($orderInfo->order_id);
			$emailParams = $this->buildEmailParams($emailTempDetail->type);
			$emailDetail = $this->transEmail($emailTempDetail, $variables, $emailParams);
			$this->sendEmail($emailDetail, $userInfo->email);
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			$this->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
		}
	}
	function sendCancelEmail($params) {
		$orderItem = oseObject::getValue($params, 'orderItem');
		$user_id = oseObject::getValue($params, 'user_id');
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$userInfo = $member->getBasicInfo('obj');
		$msc_id = $orderItem->entry_id;
		$emailConfig = oseRegistry::call('msc')->getConfig('email', 'obj');
		if ($emailConfig->cancelorder_email) {
			$emailTempDetail = $this->getDoc($ext->cancel_email, 'obj');
			$variables = $this->getEmailVariablesCancel($user_id, $msc_id);
			$emailParams = $this->buildEmailParams($emailTempDetail->type);
			$emailDetail = $this->transEmail($emailTempDetail, $variables, $emailParams);
			$this->sendEmail($emailDetail, $userInfo->email);
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			if ($emailConfig->sendCancel2Admin) {
				$this->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
			}
		}
	}
	function sendWelcomeEmail($params) {
		$orderItem = oseObject::getValue($params, 'orderItem');
		$user_id = oseObject::getValue($params, 'user_id');
		$member = oseRegistry::call('member');
		$member->instance($user_id);
		$userInfo = $member->getBasicInfo('obj');
		$msc_id = $orderItem->entry_id;
		$emailConfig = oseRegistry::call('msc')->getConfig('email', 'obj');
		if ($emailConfig->cancelorder_email) {
			$emailTempDetail = $this->getDoc($ext->cancel_email, 'obj');
			$variables = $this->getEmailVariablesCancel($user_id, $msc_id);
			$emailParams = $this->buildEmailParams($emailTempDetail->type);
			$emailDetail = $this->transEmail($emailTempDetail, $variables, $emailParams);
			$this->sendEmail($emailDetail, $userInfo->email);
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			if ($emailConfig->sendCancel2Admin) {
				$this->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
			}
		}
	}
}
?>