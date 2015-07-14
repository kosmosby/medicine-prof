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
/*
 *  This Member Class is Under a Premise: One member can only own a membership
 */
class oseMember_J15 extends oseMember {
	function __construct() {
		//parent::__construct();
		$this->setRegisteredTasks();
		$this->setRegisteredInstances();
	}
	protected function registerTask($task, $funcName) {
		$this->task[$task] = $funcName;
	}
	protected function setRegisteredTasks() {
		$this->registerTask('getAddonLicCreatorInfo', 'getUserInfo');
		$this->registerTask('getRenewMscInfo', 'getMembership');
		$this->registerTask('getMemberOwnedMscInfo', 'getAllOwnedMsc');
		$this->registerTask('getBOrderMscInfo', 'getMembership');
	}
	protected function registerInstance($task, $instanceName) {
		$this->instance[$task] = $instanceName;
	}
	protected function setRegisteredInstances() {
		// NULL
	}
	function __invoke() {
	}
	public function instance($id, $type = 'member_id') {
		$this->{$type} = $id;
		$this->checkTableInfo();
	}
	public function __toString() {
		return get_parent_class($this) . ' Version 1.5';
	}
	public function isSpecificMember($msc_id, $isActive = '-1', $where = array()) {
		$db = oseDB::instance();
		$where[] = "member_id={$this->member_id}";
		$where[] = "msc_id ={$msc_id}";
		if ($isActive == 1 || $isActive == 0) {
			$where[] = 'status = ' . $isActive;
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * " . " FROM `#__osemsc_member_view`" . $where;
		$db->setQuery($query);
		$isMember = oseDB::loadItem('obj');
		if (!empty($isMember)) {
			return $isMember;
		} else {
			return false;
		}
	}
	public function hasOwnMsc($all = true, $isActive = 1) {
		$where = array();
		if (!$all) {
			$where[] = 'status = ' . $isActive;
		}
		$where[] = 'member_id = ' . $this->member_id;
		$where = oseDB::implodeWhere($where);
		$db = oseDB::instance();
		$query = " SELECT count(*) " . " FROM `#__osemsc_member_view`" . $where;
		$db->setQuery($query);
		$isMember = $db->loadResult();
		return $isMember;
	}
	/*
	 * first time appearing in view Member
	 * get the first membership, especially for the first one in multiple memberships
	 *
	 * @$index start from 0
	 * @includeExp true means includes expired membership, vice versa
	 */
	public function getOwnMscByIndex($index, $includeExp = false, $type = 'array') {
		$db = oseDB::instance();
		$limit = "LIMIT {$index}, 1";
		$where = array();
		if (!$includeExp) {
			$where[] = 'status = 1';
		}
		$where[] = "member_id={$this->member_id}";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * " . " FROM `#__osemsc_member_view`" . $where . $limit;
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	// for frontend member panel
	public function isMember($msc_id = 0) {
		$db = oseDB::instance();
		$where = array();
		$where[] = "member_id={$this->member_id}";
		if (!empty($msc_id)) {
			$where[] = "msc_id={$msc_id}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT count(*) " . " FROM `#__osemsc_member_view`" . $where;
		$db->setQuery($query);
		$isMember = $db->loadResult();
		return ($isMember > 0) ? true : false;
	}
	public function joinMsc($msc_id) {
		$db = oseDB::instance();
		$query = " SELECT COUNT(*) FROM `#__osemsc_member`" 
			   . " WHERE msc_id !={$msc_id} AND member_id = {$this->member_id}" . " AND status = 1";
		$db->setQuery($query);
		$hasMsc = $db->loadResult();
		if ($hasMsc > 0) {
			//return false;
		}
		$query = " SELECT * FROM `#__osemsc_member`" 
			   . " WHERE msc_id = {$msc_id} AND member_id = {$this->member_id}";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		if (!empty($item)) {
			$query = " UPDATE `#__osemsc_member`" . " SET `status` = 1, `notified` = '0', `notified2` = '0', `notified3` = '0' "
					. " WHERE msc_id = {$msc_id} AND member_id = {$this->member_id}";
			$db->setQuery($query);
			if (!$db->query()) {
				return false;
			}
			return $msc_id;
		} else {
			$exts = oseMscAddon::getExtInfo($msc_id, 'payment');
			foreach ($exts as $ext) {
				$eternal = $db->Quote(oseObject::getValue($ext, 'eternal'));
				break;
			}
			$eternal = (empty($eternal)) ? 0 : $eternal;
			$query = " INSERT INTO `#__osemsc_member` ( msc_id, member_id, eternal ) VALUES " 
				   . " ( {$msc_id},{$this->member_id}," . (int) $eternal . " ) ";
			$db->setQuery($query);
			if (!$db->query()) {
				return false;
			}
			return $db->insertid();
		}
	}
	function getBasicInfo($type = 'array') {
		$user = new oseMemUser($this->member_id);
		return $user->getBasicInfo($type);
	}
	function getCompanyInfo($type = 'array') {
		$user = new oseMemUser($this->member_id);
		return $user->getCompanyInfo($type);
	}
	function getMemberInfo($msc_id = 0, $type = 'array') {
		$db = oseDB::instance();
		$where = array();
		$where[] = "member_id = {$this->member_id}";
		if (!empty($msc_id)) {
			$where[] = "msc_id = {$msc_id}";
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_member_view` " . $where . " ORDER BY id ASC" . " LIMIT 1";
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function getOwnMsc($msc_id = 0, $type = 'array') {
		$db = oseDB::instance();
		$where = array();
		if (!empty($msc_id)) {
			$where[] = "mem.msc_id={$msc_id}";
		}
		$where[] = "mem.member_id= {$this->member_id}";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT mem.* FROM `#__osemsc_member_view` AS mem " . $where;
		$db->setQuery($query);
		return oseDB::loadList($type);
	}
	function cancelMsc($msc_id) {
		$db = oseDB::instance();
		$msc_id = $db->Quote($msc_id);
		$query = " UPDATE `#__osemsc_member` " . " SET `status` = 0, `notified` = '0', `notified2` = '0', `notified3` = '0' "
				. " WHERE member_id ={$this->member_id} AND msc_id = {$msc_id}";
		$db->setQuery($query);
		return $db->query();
	}
	function getMscMembers($msc_id, $post, $type = 'array') {
		$db = oseDB::instance();
		$status = $post['status'];
		$where = array();
		$start = $post['start'];
		$limit = $post['limit'];
		if (!empty($post['search'])) {
			$search = $post['search'];
			$searchEscaped = $db->Quote('%' . $db->getEscaped($search, true) . '%', false);
			$where[] = " mem.username LIKE {$searchEscaped} " . " OR mem.name LIKE {$searchEscaped}  " . " OR mem.email LIKE {$searchEscaped} ";
		}
		$where[] = ' mem.msc_id = ' . $db->Quote($msc_id);
		if ($status == 1 || $status == 0) {
			$where[] = ' mem.status = ' . $db->Quote($status);
		}
		// Generate the where query
		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');
		$result = array();
		$result['results'] = oseMemGroup::getMscMembers($msc_id, $where, $start, $limit);
		$result['total'] = oseMemGroup::getGroupTotal($msc_id, $where);
		return $result;
	}
	function getUsers($post, $type = 'array') {
		$db = oseDB::instance();
		$where = array();
		$search = $post['search'];
		if ($search) {
			$searchQuery = $db->Quote('%' . $search . '%');
			$where[] = "u.username LIKE {$searchQuery} OR u.name LIKE {$searchQuery} OR u.email LIKE {$searchQuery}";
		}
		$msc_id = oseObject::getValue($post, 'msc_id', 0);//JRequest::getInt('msc_id','');
		if (!empty($msc_id)) {
			$msc_id = " AND msc_id = " . (int) $msc_id;
		}
		$where[] = "u.id NOT IN (SELECT member_id FROM `#__osemsc_member` WHERE `status` = '1' {$msc_id})";
		$where = oseDB::implodeWhere($where);
		$start = $post['start'];
		$limit = $post['limit'];
		$result['results'] = oseMemGroup::getUsers($where, $start, $limit);
		$result['total'] = oseMemGroup::getUsersTotal($where);
		return $result;
	}
	function quickLoadMemberInfo($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member_view`" . " WHERE id = {$this->msc_member_id}";
		$db->setQuery($query);
		return oseDB::loadItem($type);
	}
	function getMemberPanelView($view, $addon_type = 'member') {
		$instance = self::getInstance('PanelView');
		return $instance->{'display' . $view}($addon_type);
	}
	function hasMemberAuthority($msc_id) {
		return $this->isSpecifiMember($msc_id, 1);
	}
	function sendReceipt($orderInfo, $email) {
		$emailInstance = $this->getInstance('Email');
		$emailInstance->sendReceipt($orderInfo, $email);
	}
	function getReceipt($orderInfo) {
		$email = $this->getInstance('Email');
		$receipt = $email->getReceipt($orderInfo);
		return $receipt;
	}
	///////////////////////////// NEW /////////////////////////////////////
	function getAllOwnedMsc($all = true, $isActive = 1, $type = 'array') {
		$where = array();
		if (!$all) {
			$where[] = 'status = ' . $isActive;
		}
		$where[] = 'member_id = ' . $this->member_id;
		$objs = $this->getMscList($where, $type);
		return $objs;
	}
	function getMembership($msc_id, $type = 'array') {
		$where = array();
		$where[] = 'member_id = ' . $this->member_id;
		$where[] = 'msc_id = ' . $msc_id;
		$item = $this->getMscItem($where, $type);
		if (empty($item)) {
			return false;
		} else {
			return $item;
		}
	}
	protected function getMscItem($where, $type) {
		$where = oseDB::implodeWhere($where);
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member`" . $where;
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	public function getMscList($where, $type) {
		$where = oseDB::implodeWhere($where);
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member`" . $where;
		$db->setQuery($query);
		$objs = oseDB::loadList($type);
		return $objs;
	}
	/*/////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////
	View.Member Section Start
	///////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////*/
	function getUserInfo($type = 'array') {
		$user = new oseUser();
		$user->instance($this->member_id);
		return $user->getUserInfo($type);
	}
	function getBillingInfo($type = 'array') {
		$user = new oseUser();
		$user->instance($this->member_id);
		return $user->getBillingInfo($type);
	}
	function getProfile($type = 'array') {
		$user = new oseUser();
		$user->instance($this->member_id);
		return $user->getProfile($type);
	}
	function getAddonParams($msc_id, $user_id, $order_id, $params = array()) {
		$params['msc_id'] = $msc_id;
		$params['order_id'] = $order_id;
		$params['member_id'] = $user_id;
		$params['join_from'] = empty($params['join_from']) ? 'payment' : $params['join_from'];
		$params['allow_work'] = empty($params['allow_work']) ? true : $params['allow_work'];
		$params['master'] = empty($params['master']) ? true : $params['master'];
		return $params;
	}
	function updateUserInfo($post) {
		$db = oseDB::instance();
		$member_id = $this->member_id;
		$firstname = $db->Quote($post['firstname']);
		$lastname = $db->Quote($post['lastname']);
		$query = " SELECT COUNT(*) FROM `#__osemsc_userinfo`" . " WHERE user_id = {$member_id}";
		$db->setQuery($query);
		$exists = ($db->loadResult() > 0) ? true : false;
		if ($exists) {
			$set = array();
			if (isset($post['primary_contact'])) {
				$set['primary_contact'] = "primary_contact= " . $db->Quote($post['primary_contact']);
			}
			$set['firstname'] = "firstname= " . $firstname;
			$set['lastname'] = "lastname= " . $lastname;
			$setQuery = implode(',', $set);
			$query = " UPDATE `#__osemsc_userinfo` " . " SET " . $setQuery . " WHERE user_id = {$member_id}";
		} else {
			$primary_contact = $db->Quote(oseObject::getValue($post, 'primary_contact', 1));
			$query = " INSERT INTO `#__osemsc_userinfo`" . " (user_id,firstname,lastname,primary_contact)" . " VALUES"
					. " ({$member_id},{$firstname},{$lastname},{$primary_contact})";
		}
		$db->setQuery($query);
		return $db->query();
	}
	function hasHistory($actions = array('join'), $msc_id, $msc_option = null, $infinity = true) {
		$memHistory = $this->getInstance('History');
		$db = oseDB::instance();
		$where = array();
		$actionValues = array();
		foreach ($actions as $action) {
			$actionValues[] = $db->Quote($action);
		}
		$actionValues = implode(',', $actionValues);
		$where[] = '`action` IN (' . $actionValues . ')';
		$where[] = '`member_id`=' . $db->Quote($this->member_id);
		$where[] = '`msc_id`=' . $db->Quote($msc_id);
		if (!$infinity) {
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_member_history`" . $where . " ORDER BY date DESC";
		$db->setQuery($query, 0, 1);
		$item = oseDB::loadItem();
		if (empty($item)) {
			return false;
		} else {
			return true;
		}
	}
	function getMemberTotalTime($msc_id) {
		$db = oseDB::instance();
		$mscInfo = $this->getMembership($msc_id, 'obj');
		return;
	}
	function checkTableInfo() {
		$db = oseDB::instance();
		// check billing
		$query = " SELECT COUNT(*) FROM `#__osemsc_billinginfo`" . " WHERE `user_id` = '{$this->member_id}'";
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result <= 0) {
			$user = new JUser($this->member_id);
			$names = explode(' ', $user->name);
			$firstname = $names[0];
			$lastname = count($names) > 1 ? $names[1] : '';
			oseDB::insert('#__osemsc_billinginfo', array('user_id' => $this->member_id, 'firstname' => $firstname, 'lastname' => $lastname));
		}
		// check user info
		$query = " SELECT COUNT(*) FROM `#__osemsc_userinfo`" . " WHERE `user_id` = '{$this->member_id}'";
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result <= 0) {
			$user = new JUser($this->member_id);
			$names = explode(' ', $user->name);
			$firstname = $names[0];
			$lastname = count($names) > 1 ? $names[1] : '';
			oseDB::insert('#__osemsc_userinfo', array('user_id' => $this->member_id, 'firstname' => $firstname, 'lastname' => $lastname));
		}
		return true;
	}
}