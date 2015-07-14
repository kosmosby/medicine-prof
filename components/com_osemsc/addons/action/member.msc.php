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
class oseMscAddonActionMemberMsc {
	public static function getItems($params = array()) {
		$my = JFactory::getUser();
		$member_id = $my->id;
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$items = $member->getMemberOwnedMscInfo(false, 1, 'obj');
		foreach ($items as $item) {
			$mscInfo = oseRegistry::call('msc')->getInfo($item->msc_id, 'obj');
			$item = oseObject::setValue($item, 'msc_name', $mscInfo->title);
		}
		$result = array();
		if (count($items) < 1) {
			$result['total'] = 0;
			$result['results'] = '';
		} else {
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		return $result;
	}
	public static function getMscInfo($params = array()) {
		$my = JFactory::getUser();
		$msc_id = JRequest::getInt('msc_id', 0);
		$member_id = $my->id;
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member`" . " WHERE member_id = {$member_id} AND msc_id = {$msc_id}";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		$memParams = oseJson::decode($item->params);
		//order
		$orderId = oseObject::getValue($memParams, 'order_id');
		$orderInfo = oseRegistry::call('payment')->getInstance('Order')->getOrder(array('order_id = ' . $orderId), 'obj');
		//order item
		$orderItemId = oseObject::getValue($memParams, 'order_item_id', 'm');
		$orderItemInfo = oseRegistry::call('payment')->getInstance('Order')->getOrderItem(array('0' => 'order_item_id = ' . $orderItemId), 'obj');
		$orderItemInfoParams = oseJson::decode($orderItemInfo->params);
		$msc = oseRegistry::call('msc');
		$node = $msc->getInfo($msc_id, 'obj');
		$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
		$paymentInfo = $paymentInfos[oseObject::getValue($orderItemInfoParams, 'msc_option')];
		$option = oseMscPublic::generatePriceOption($node, array($paymentInfo), 'USD');
		if (oseObject::getValue($memParams, 'payment_mode', 'm') == 'm') {
			$titlePaymentMode = JText::_('MANUAL_BILLING');
			$query = " SELECT acl.*,mem.*, (SELECT DATEDIFF(mem.expired_date,NOW())) AS days_left " . " ,mem.`expired_date` AS `real_expired_date`"
					. " FROM `#__osemsc_member` AS mem " . " INNER JOIN `#__osemsc_acl` AS acl ON acl.id = mem.msc_id"
					. " WHERE mem.member_id = {$member_id} AND mem.msc_id = {$msc_id}";
		} else {
			$titlePaymentMode = JText::_('AUTOMATIC_BILLING');
			$query = " SELECT acl.*,mem.*, (SELECT DATEDIFF(mem.expired_date,NOW()) - 1) AS days_left " . " ,DATE_SUB(mem.`expired_date`,INTERVAL 1 DAY) AS `real_expired_date`"
					. " FROM `#__osemsc_member` AS mem " . " INNER JOIN `#__osemsc_acl` AS acl ON acl.id = mem.msc_id"
					. " WHERE mem.member_id = {$member_id} AND mem.msc_id = {$msc_id}";
		}
		$db->setQuery($query);
		$item = oseDB::loadItem();
		$globalConfig = oseRegistry::call('msc')->getConfig('global', 'obj');
		if ($item['start_date'] != '0000-00-00 00:00:00' && !empty($globalConfig->DateFormat)) {
			$item = oseObject::setValue($item, 'start_date', date($globalConfig->DateFormat, strtotime($item['start_date'])));
		}
		if ($item['real_expired_date'] != '0000-00-00 00:00:00' && !empty($globalConfig->DateFormat)) {
			$item = oseObject::setValue($item, 'real_expired_date', date($globalConfig->DateFormat, strtotime($item['real_expired_date'])));
		}
		if (oseObject::getValue($item, 'eternal', false)) {
			$item = oseObject::setValue($item, 'real_expired_date', JText::_('LIFE_TIME'));
			$item = oseObject::setValue($item, 'days_left', JText::_('INFINITED'));
		}
		$result = array();
		if (empty($item)) {
			$result['total'] = 0;
			$result['result'] = array();
		} else {
			$item['title'] = oseObject::getValue($option[0], 'title') . "({$titlePaymentMode})";
			$item['msc_option'] = oseObject::getValue($orderItemInfoParams, 'msc_option');
			$item['expired_date'] = $item['real_expired_date'];
			$item['params'] = oseJson::decode($item['params']);
			if ($item['status'] == 1) {
				$exp_text = '';
				if (oseObject::getValue($memParams, 'payment_mode', 'm') == 'a') {
					if (in_array($orderInfo->order_status, array('cancelled'))) {
						$exp_text = ' until ' . $item['expired_date'];
					}
				}
				$item['status'] = JText::_('ACTIVE') . ' ' . $exp_text;
			} else {
				$item['status'] = JText::_('INACTIVE');
			}
			$item['payment_mode_text'] = ($item['params']->payment_mode == 'm') ? JText::_('MANUAL_RENEWING') : JText::_('AUTOMATIC_RENEWING');
			if (empty($paymentInfo)) {
				$item['interval'] = '--';
			} else {
				$item['interval'] = oseObject::getValue($paymentInfo, 'p3') . ' ' . JText::_(strtoupper(oseObject::getValue($paymentInfo, 't3'))) . '(s)';
			}
			$result['total'] = 1;
			$result['result'] = $item;
		}
		return $result;
	}
	public static function getPaymentMode() {
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'payment', 'obj');
		$items = array();
		if ($ext->payment_mode == 'a') {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('AUTOMATIC_RENEWING'));
		} elseif ($ext->payment_mode == 'm') {
			$items[] = array('id' => 1, 'value' => 'm', 'text' => JText::_('MANUAL_RENEWING'));
		} else {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('AUTOMATIC_RENEWING'));
			$items[] = array('id' => 2, 'value' => 'm', 'text' => JText::_('MANUAL_RENEWING'));
		}
		$result = array();
		if (empty($items)) {
			$result['total'] = 0;
			$result['results'] = array();
		} else {
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		return $result;
	}
	public static function save() {
		$post = JRequest::get('post');
		$member_id = JRequest::getInt('member_id', 0);
		return '';
	}
	public static function joinMsc($params = array()) {
		$member_id = $params['member_id'];
		if (empty($member_id)) {
			return false;
		}
		$db = oseDB::instance();
		switch ($params['join_from']) {
		case ('lic'):
			return self::joinFromLic($params);
			break;
		case ('license'):
		case ('payment'):
		default:
			return self::joinFromPayment($params);
			break;
		}
	}
	public static function renewMsc($params = array()) {
		if (empty($params)) {
			return array('success' => false);
		}
		$member_id = $params['member_id'];
		if (empty($member_id)) {
			return array('success' => false);
		}
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array();
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon::getAddonList('renew', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon::getActionName($addon, 'renew', 'renew');
				$result = oseMscAddon::runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self::cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			//Auto reucrring email control
			$emailConfig = oseMscConfig::getConfig('email', 'obj');
			$send = true;
			$where = array();
			$where[] = '`order_id` = ' . $db->Quote($order_id);
			$orderInfo = oseRegistry::call('payment')->getInstance('Order')->getOrder($where, 'obj');
			if ($orderInfo->payment_mode == 'a' && oseObject::getValue($emailConfig, 'sendWelOnlyOneTime', false)) {
				$send = false;
			}
			if (!empty($ext) && isset($ext->wel_email) && $send) {
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($ext->wel_email, 'obj');
				$variables = $email->getEmailVariablesWelcome($order_id, $msc_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $userInfo->email);
				if ($emailConfig->sendWel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result = array();
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Joining Membership.');
		}
		return $result;
	}
	public static function activateMsc($params = array()) {
		if (empty($params)) {
			return array('success' => false);
		}
		$member_id = $params['member_id'];
		if (empty($member_id)) {
			return false;
		}
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array();
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon::getAddonList('renew', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon::getActionName($addon, 'activate', 'renew');
				$result = oseMscAddon::runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self::cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			if ($ext->wel_email) {
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($ext->wel_email, 'obj');
				$variables = $email->getEmailVariablesWelcome($order_id, $msc_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig::getConfig('email', 'obj');
				if ($emailConfig->sendWel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result = array();
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Joining Membership.');
		}
		return $result;
	}
	private static function joinFromLic($params) {
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$result = array();
		if (empty($msc_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Please Select A Membership First');
			return $result;
		}
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon::getAddonList('join', false, null, 'obj');
			foreach ($list as $addon) {
				$action_name = 'join.' . $addon->name . '.save';
				$result = oseMscAddon::runAction($action_name, $params);
				if (!$result['success']) {
					self::cancelMsc($params);
					return $result;
				}
			}
			$result = array();
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Joining Membership.');
		}
		return $result;
	}
	public static function cancelMsc($params = array()) {
		$post = JRequest::get('post');
		$result = array();
		if (empty($params)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error.');
			return $result;
		}
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');
		$updated = $member->cancelMsc($msc_id);
		// Email 1 => get Content First
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
		if ($ext->cancel_email) {
			$order_id = $params['order_id'];
			$email = $member->getInstance('email');
			$emailTempDetail = $email->getDoc($ext->cancel_email, 'obj');
			$variables = $email->getEmailVariablesCancel($member_id, $msc_id);
			$emailParams = $email->buildEmailParams($emailTempDetail->type);
			$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
		}
		// Email 1 End
		if ($updated) {
			$list = oseMscAddon::getAddonList('join', false, 1, 'obj');
			foreach ($list as $addon) {
				$result = oseMscAddon::runAction('join.' . $addon->name . '.cancel', $params, true, false);
				if (!$result['success']) {
					return $result;
				}
			}
			// Email 2 => Send Out
			if ($ext->cancel_email) {
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig::getConfig('email', 'obj');
				if ($emailConfig->sendCancel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			// Email 2 End
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Canceled Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Cancelling Membership.');
		}
		return $result;
	}
	public static function expireMsc($params = array()) {
		$post = JRequest::get('post');
		$result = array();
		if (empty($params)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error.');
			return $result;
		}
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');
		$updated = $member->cancelMsc($msc_id);
		// Email 1 => get Content First
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
		if (!empty($ext->exp_email)) {
			$order_id = $params['order_id'];
			$email = $member->getInstance('email');
			$emailTempDetail = $email->getDoc($ext->exp_email, 'obj');
			$variables = $email->getEmailVariablesExpire($member_id, $msc_id);
			$emailParams = $email->buildEmailParams($emailTempDetail->type);
			$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
		}
		// Email 1 End
		if ($updated) {
			$list = oseMscAddon::getAddonList('join', false, 1, 'obj');
			foreach ($list as $addon) {
				$result = oseMscAddon::runAction('join.' . $addon->name . '.cancel', $params, true, false);
				if (!$result['success']) {
					return $result;
				}
			}
			// Email 2 => Send Out
			if (!empty($ext->exp_email)) {
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig::getConfig('email', 'obj');
				if ($emailConfig->sendExp2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			// Email 2 End
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Canceled Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Cancelling Membership.');
		}
		return $result;
	}
	// params['order_id']
	/*
	 * @params msc_id
	 * @params member_id
	 * @params order_id
	 * @params allow_work
	 * @params join_from
	 */
	private static function joinFromPayment($params) {
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array();
		if (empty($msc_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Please Select A Membership First');
			return $result;
		}
		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon::getAddonList('join', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon::getActionName($addon, 'save', 'join');
				$result = oseMscAddon::runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self::cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			if (oseObject::GetValue($ext, 'wel_email', false)) {
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($ext->wel_email, 'obj');
				$variables = $email->getEmailVariablesWelcome($order_id, $msc_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig::getConfig('email', 'obj');
				if ($emailConfig->sendWel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result = array();
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Joined Membership.');
		} else {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Joining Membership.');
		}
		return $result;
	}
}
?>