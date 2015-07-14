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
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'define.php');
class oseUser2MscV5 extends oseUser2Instance {
	protected $active_membership = array ();
	protected $expired_membership = array ();
	protected $suspend_membership = array ();
	protected $_table = '#__osemsc_member';
	public $expired_date = null;
	public $isLicensee = false;
	function __construct($user_id) {
		parent :: __construct($user_id);
		// get License
		$this->getMap();
	}
	function getMap() {
		// get License
		$db = oseDB :: instance();
		// active
		$where = array ();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 1";
		$where = oseDB :: implodeWhere($where);
		$query = " SELECT * FROM `{$this->_table}`" . $where .		" ORDER BY `id` ASC";
		$db->setQuery($query);
		$items = oseDB :: loadList('obj', 'msc_id');
		$this->set('active_membership', $items);
		//expired
		$where = array ();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 0";
		$where = oseDB :: implodeWhere($where);
		$query = " SELECT * FROM `{$this->_table}`" . $where . " ORDER BY `id` ASC";
		$db->setQuery($query);
		$items = oseDB :: loadList('obj', 'msc_id');
		$this->set('expired_membership', $items);
		//suspend
		$where = array ();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 3";
		$where = oseDB :: implodeWhere($where);
		$query = " SELECT * FROM `{$this->_table}`" . $where . " ORDER BY `id` ASC";
		$db->setQuery($query);
		$items = oseDB :: loadList('obj', 'msc_id');
		$this->set('suspend_membership', $items);
	}
	function join($id, $amount = 1, $entry_type = 'msc', $entry_option = null) {
		//oseAppConfig::load('mscv5');
		oseRegistry :: register('email', 'email');
		oseRegistry :: register('msc', 'membership');
		oseRegistry :: register('user', 'user');
		oseRegistry :: quickRequire('user');
		oseRegistry :: register('member', 'member');
		$this->generateOrder($id, $entry_option);
		$msc_id = $id;
		$user_id = $this->user_id;
		$order_id = $this->order_id;
		$order_item_id = $this->order_item_id;
		$member = oseCall('member');
		$member->instance($user_id);
		$params = $member->getAddonParams($msc_id, $user_id, $order_id, array (
			'order_item_id' => $order_item_id
		));
		// is Member, renew... else join.
		$memMscInfo = $member->getMembership($msc_id, 'obj');
		if (empty ($memMscInfo)) {
			$msc = oseCall('msc');
			$updated = $this->joinFromPayment($params);
		} else {
			// renew
			if ($memMscInfo->status) {
				$memParams = oseJson :: decode($memMscInfo->params);
				$msc = oseCall('msc');
				$updated = $this->renewMsc($params);
			} else {
				$msc = oseCall('msc');
				$updated = $this->activateMsc($params);
			}
		}
		$db = oseDB :: instance();
		$query = " UPDATE `#__osemsc_member`" .	" SET `expired_date` = '{$this->expired_date}'" ." WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$user_id}'";
		$db->setQuery($query);
		oseDB :: query();
		return $updated;
	}
	function quit($id, $entry_type = 'msc', $entry_option = null) {
		oseAppConfig :: load('mscv5');
		$msc_id = $id;
		$user_id = $this->user_id;
		$order_id = $this->order_id;
		$order_item_id = $this->order_item_id;
		$params = oseCall('member')->getAddonParams($msc_id, $user_id, $order_id, array (
			'order_item_id' => $order_item_id
		));
		$member = oseCall('member');
		$member->instance($user_id);
		$msc = oseCall('msc');
		$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
		$updated = oseMscAddon :: runAction('member.msc.cancelMsc', $params, true, false);
		return $updated;
	}
	function expire($id, $entry_type = 'msc', $entry_option = null) {
		$db = oseDB :: instance();
		$query = " SELECT * FROM `{$this->_table}` WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'";
		$db->setQuery($query);
		$item = oseDB :: loadItem('obj');
		$mscInfo = $this->find($id);
		if (empty ($info) || !in_array($info->get('status'), array (
				1,
				3
			))) {
			// no need to quit if empty
			$result = array ();
			$result['success'] = false;
			$result['title'] = $result['status'] = JText :: _('ERROR');
			$result['content'] = $result['result'] = JText :: _('NO_MEMBERSHIP_TO_CANCEL');
			return $result;
		}
		$info->set('status', 'expired');
		$updated = $info->update();
		// cancel addon
		if ($updated) {
			$addon = oseCall('msc')->instance('addon', array (
				'user_id' => $this->user_id,
				'mm_id' => $item->id
			));
			$jResult = $addon->runActionGroup('join', 'expire', false);
			$updated = $jResult['success'];
			if ($updated) {
				return $jResult;
			}
		}
		if ($updated) {
			$addon = oseCall('msc')->instance('addon', array (
				'user_id' => $this->user_id,
				'mm_id' => $item->id
			));
			$jResult = $addon->runActionGroup('join', 'cancel', false);
			$updated = $jResult['success'];
			if ($updated) {
				// cancel email
				$msc = oseCall('msc')->instance('plan', array (
					'id' => $id
				));
				$mscExtmsc = $msc->getExt('msc');
				$exp_email = oseGetValue($mscExtmsc, 'exp_email');
				if (empty ($exp_email)) {
					$config = oseConfig :: load($entry_type, 'email', 'obj');
					$exp_email = oseGetValue($config, 'default_exp_email', 0);
				}
				if ($exp_email) {
					$email = oseCall('email');
					$eObj = $email->getEmail($exp_email);
					$content = array ();
					$user = oseCall('user2')->instance($this->user_id);
					$content['user'] = (object) $user->outputPayment($entry_type);
					$mem = new stdClass();
					$content['member'] = $mem;
					$eObj->setEmailVariables($content);
					$eObj->transEmail();
					$eTemp = $eObj->output();
					$email->sendEmail($eTemp, $this->get('email'));
					//
					if (oseGetValue($config, 'sendExp2Admin', false)) {
						$group = explode(',', oseGetValue($config, 'email_admin_group'));
						if (empty ($group)) {
							$group = oseConfig :: getAdminGroup($entry_type);
						}
						$email->sendToGroup($eTemp, $group);
					}
				}
				$result['success'] = true;
				$result['title'] = $result['status'] = JText :: _('SUCCESS');
				$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_CANCEL_SUCCESS');
			}
			$result['success'] = true;
			$result['title'] = $result['status'] = JText :: _('SUCCESS');
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_SUCCEED');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = JText :: _('ERROR');
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	function delete($msc_id = null) {
		$db = oseDB :: instance();
		$where = array ();
		if (!empty ($msc_id)) {
			$where['msc_id'] = $msc_id;
		}
		$where['member_id'] = $this->user->id;
		$updated = oseDB :: delete($this->_table, $where);
		if ($updated) {
			$result['success'] = true;
			$result['title'] = $result['status'] = JText :: _('SUCCESS');
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_QUIT_SUCCEED');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = JText :: _('ERROR');
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_QUIT_FAILED');
		}
		return $result;
	}
	function find($msc_id) {
		// first search data in active memberships
		$memberships = $this->get('active_membership');
		if (isset ($memberships[$msc_id])) {
			return new oseMemberItem($memberships[$msc_id]);
		}
		// then search data in expired memberships
		$memberships = $this->get('expired_membership');
		if (isset ($memberships[$msc_id])) {
			return new oseMemberItem($memberships[$msc_id]);
		}
		// then search data in suspend memberships
		$memberships = $this->get('suspend_membership');
		if (isset ($memberships[$msc_id])) {
			return new oseMemberItem($memberships[$msc_id]);
		}
		return array ();
	}
	function cancel($id, $entry_type = 'msc', $entry_option = null) {
		oseRegistry :: register('email', 'email');
		oseRegistry :: register('msc', 'membership');
		oseRegistry :: register('user', 'user');
		oseRegistry :: quickRequire('user');
		oseRegistry :: register('member', 'member');
		$msc_id = $id;
		$user_id = $this->user_id;
		$db = oseDB :: instance();
		$query = " SELECT * FROM `#__osemsc_member` WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$user_id}'";
		$db->setQuery($query);
		$memInfo = oseDB :: loadItem('obj');
		$memParams = oseJson :: decode($memInfo->params);
		$order_id = oseGetValue($memParams, 'order_id');
		$order_item_id = oseGetValue($memParams, 'order_item_id');
		$params = oseCall('member')->getAddonParams($msc_id, $user_id, $order_id, array (
			'order_item_id' => $order_item_id
		));
		$msc = oseCall('msc');
		$app = JFactory :: getApplication('SITE');
		$controller = JRequest :: getCmd('controller');
		if ($app->isSite()) {
			$updated = oseMscAddon :: runAction('member.msc.cancelMsc', $params, true, false);
		}
		elseif ($controller == 'orders') {
			$updated = oseMscAddon :: runAction('member.msc.cancelMsc', $params, true, false);
		} else {
			$updated = oseMscAddon :: runAction('member.msc.cancelMsc', $params, true, true);
		}
		return $updated;
	}
	function generateOrder($msc_id, $msc_option = null) {
		$vals = array ();
		$params = array ();
		$subtotal = 0.00;
		$order_number = $this->generateOrderNumber($this->user_id);
		$params['entry_type'] = 'msc_list';
		$params['payment_price'] = 0.00;
		$params['payment_currency'] = 'USD';
		$params['order_number'] = $order_number;
		$params['create_date'] = oseHTML2 :: getDateTime(); //date("Y-m-d H:i:s");
		$params['payment_serial_number'] = substr($order_number, 0, 20);
		$params['payment_method'] = 'free';
		$params['payment_mode'] = 'm';
		$entry_type = 'msc';
		$db = oseDB :: instance();
		$query = "SELECT * FROM `#__osemsc_ext`" .		" WHERE `type` = 'payment' AND `id` = '{$msc_id}'";
		$db->setQuery($query);
		$planPayment = oseDB :: loadItem('obj');
		$options = oseJSON :: decode($planPayment->params, true);
		$paymentInfo = oseGetValue($options, $lc->entry_option, array ());
		$price = 0.00;
		$pVals = array ();
		$pVals['recurrence_mode'] = 'period';
		$pVals['a3'] = $price;
		$pVals['p3'] = oseGetValue($paymentInfo, 'p3');
		$pVals['t3'] = oseGetValue($paymentInfo, 't3');
		$pVals['msc_option'] = $msc_option;
		$pVals['recurrence_mode'] = 'fixed';
		$pVals['start_date'] = oseHtml2 :: getDateTime();
		$pVals['expired_date'] = oseGetValue($this, 'expired_date', oseHtml2 :: getDateTime());
		$pVals['isLicensee'] = 1;
		// set licensee true
		$this->set('isLicensee', true);
		$pVals['eternal'] = oseGetValue($paymentInfo, 'eternal');
		$params['params'] = $pVals;
		$params['params']['total'] = $price;
		$params['params']['discount'] = $price;
		$params['params']['subtotal'] = $price;
		$params['params']['next_subtotal'] = $price;
		$params['params'] = oseJSON :: encode($params['params']);
		$updated = oseDB :: insert('#__osemsc_order', $params);
		if ($updated) {
			$order_id = $updated;
			$this->order_id = $order_id;
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('Error');
			return $result;
		}
		$itemParams = array ();
		$itemParams['entry_type'] = $entry_type;
		$itemParams['payment_price'] = $price;
		$itemParams['payment_currency'] = 'USD';
		$itemParams['create_date'] = oseHTML2 :: getDateTime(); //date("Y-m-d H:i:s");
		$itemParams['payment_mode'] = 'm';
		$itemParams['params'] = oseJSON :: encode($pVals);
		$updated = oseDB :: insert('#__osemsc_order_item', $itemParams);
		if ($updated) {
			$this->order_item_id = $updated;
			$result['success'] = true;
			$result['title'] = $result['status'] = JText :: _('Done');
			$result['content'] = $result['result'] = JText :: _('Done');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('Order Generate Error');
		}
		return $result;
	}
	public function generateOrderNumber() {
		$order_number = $this->user_id . "_" . $this->randStr(28, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890");
		return $order_number;
	}
	protected function randStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length = (strlen($chars) - 1);
		// Start our string
		$string = $chars {
			rand(0, $chars_length)
			};
		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string)) {
			// Grab a random character from our list
			$r = $chars {
				rand(0, $chars_length)
				};
			// Make sure the same two characters don't appear next to each other
			if ($r != $string {
				$i -1 })
			$string .= $r;
		}
		// Return the string
		return $string;
	}
	protected function joinFromPayment($params) {
		$db = oseDB :: instance();
		$msc = oseRegistry :: call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array ();
		if (empty ($msc_id)) {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('Please Select A Membership First');
			return $result;
		}
		$member = oseRegistry :: call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon :: getAddonList('join', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon :: getActionName($addon, 'save', 'join');
				$result = oseMscAddon :: runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self :: cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			// get order params
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			if (oseGetValue($ext, 'wel_email', false) && !$this->isLicensee) {
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($ext->wel_email, 'obj');
				$variables = $email->getEmailVariablesWelcome($order_id, $msc_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig :: getConfig('email', 'obj');
				if ($emailConfig->sendWel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result = array ();
			$result['success'] = true;
			$result['title'] = $result['status'] = 'Done';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_SUCCEED');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	protected function renewMsc($params = array ()) {
		if (empty ($params)) {
			return array (
				'success' => false
			);
		}
		$member_id = $params['member_id'];
		if (empty ($member_id)) {
			return array (
				'success' => false
			);
		}
		$db = oseDB :: instance();
		$msc = oseRegistry :: call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array ();
		$member = oseRegistry :: call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon :: getAddonList('renew', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon :: getActionName($addon, 'renew', 'renew');
				$result = oseMscAddon :: runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self :: cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			//Auto reucrring email control
			$emailConfig = oseMscConfig :: getConfig('email', 'obj');
			$send = true;
			$where = array ();
			$where[] = '`order_id` = ' . $db->Quote($order_id);
			$orderInfo = oseRegistry :: call('payment')->getInstance('Order')->getOrder($where, 'obj');
			if ($orderInfo->payment_mode == 'a' && oseObject :: getValue($emailConfig, 'sendWelOnlyOneTime', false)) {
				$send = false;
			}
			if ($ext->wel_email && $send && !$this->isLicensee) {
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
			$result = array ();
			$result['success'] = true;
			$result['title'] = $result['status'] = 'Done';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_SUCCEED');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	protected function activateMsc($params = array ()) {
		if (empty ($params)) {
			return array (
				'success' => false
			);
		}
		$member_id = $params['member_id'];
		if (empty ($member_id)) {
			return false;
		}
		$db = oseDB :: instance();
		$msc = oseRegistry :: call('msc');
		$member_id = $params['member_id'];
		$msc_id = $params['msc_id'];
		$order_id = $params['order_id'];
		$result = array ();
		$member = oseRegistry :: call('member');
		$member->instance($member_id);
		$updated = $member->joinMsc($msc_id);
		if ($updated) {
			$list = oseMscAddon :: getAddonList('renew', false, 1, 'obj');
			foreach ($list as $addon) {
				$action_name = oseMscAddon :: getActionName($addon, 'activate', 'renew');
				$result = oseMscAddon :: runAction($action_name, $params, true, false);
				if (!$result['success']) {
					self :: cancelMsc($params);
					return $result;
				}
			}
			$userInfo = $member->getBasicInfo('obj');
			$ext = $msc->getExtInfo($msc_id, 'msc', 'obj');
			if ($ext->wel_email && !$this->isLicensee) {
				$email = $member->getInstance('email');
				$emailTempDetail = $email->getDoc($ext->wel_email, 'obj');
				$variables = $email->getEmailVariablesWelcome($order_id, $msc_id);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $userInfo->email);
				$emailConfig = oseMscConfig :: getConfig('email', 'obj');
				if ($emailConfig->sendWel2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
			$result = array ();
			$result['success'] = true;
			$result['title'] = $result['status'] = 'Done';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_SUCCEED');
		} else {
			$result['success'] = false;
			$result['title'] = $result['status'] = 'Error';
			$result['content'] = $result['result'] = JText :: _('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
}
class oseMemberItem extends oseObject {
	public $id = 0;
	public $msc_id = 0;
	public $member_id = 0;
	public $status = 0;
	public $eternal = 0;
	public $start_date = 0;
	public $expired_date = 0;
	public $notified = 0;
	public $notified2 = 0;
	public $notified3 = 0;
	public $params = '';
	protected $_table = '#__osemsc_member';
	protected $_isNew = false;
	function __construct($p = array ()) {
		parent :: __construct($p);
	}
	function create() {
		$vals = $this->getProperties();
		return oseDB :: insert($this->_table, $vals);
	}
	function set($key, $value = null) {
		if ($key == 'status') {
			if (!is_numeric($value)) {
				$db = oseDB :: instance();
				$query = "  SELECT * FROm `#__osemsc_member_status`" ." WHERE `name` = '{$value}'";
				$db->setQuery($query);
				$item = oseDB :: loadItem('obj');
				parent :: set($key, $item->id);
			} else {
				parent :: set($key, $value);
			}
		} else {
			parent :: set($key, $value);
		}
	}
	function update() {
		$vals = $this->getProperties();
		return oseDB :: update($this->_table, 'id', $vals);
	}
	function delete() {
		$vals = $this->getProperties();
		return oseDB :: delete($this->_table, array (
			'id' => $this->id
		));
	}
}
?>