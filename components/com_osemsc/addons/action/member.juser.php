<?php
defined('_JEXEC') or die(";)");
class oseMscAddonActionMemberJuser {
	public static function getItem($params = array ()) {
		$oseUser = new oseUser();
		$info = $oseUser->getUserInfo();
		$result['total'] = 1;
		$result['result'] = $info;
		return $result;
	}
	public static function save() {
		$post = JRequest :: get('post');
		$result = array ();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText :: _('Member Information Updated');
		$user = JFactory :: getUser();
		$member_id = $user->id;
		$array = array ();
		$array['username'] = $post['username'];
		$array['name'] = $post['firstname'] . ' ' . $post['lastname'];
		$array['password'] = $post['password'];
		$array['password2'] = $post['password2'];
		$array['email'] = $post['email'];
		if ($user->guest) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('You are not a member!');
			$isNew = true;
			return $result;
		} else {
			$isNew = false;
			$member_id = $user->id;
		}
		$exists = $this->checkEmail($array['email'], $member_id);
		if ($exists == true) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Email already exists.');
			$isNew = true;
			return $result;
		}
		$user_id = $member_id;
		$username = $post['username'];
		$updated = oseMscPublic :: uniqueUserName($username, $user_id);
		oseRegistry :: call('msc')->runAddonAction('member.billinginfo.save');
		if (!$updated['success']) {
			return $updated;
		}
		$uid = self :: jvsave($member_id, $array);
		if ($isNew) {
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText :: _('Member Information Updated');
			$result['member_id'] = $uid;
			return $result;
		} else {
			$member = oseRegistry :: call('member');
			$member->instance($member_id);
			$updated = $member->updateUserInfo($post);
			if (!$updated) {
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _('Failed Updating Member Information');
				$result['member_id'] = '';
			} else {
				$list = oseMscAddon :: getAddonList('usersync', false, null, 'obj');
				$params = array ();
				$params['member_id'] = $member_id;
				$params['allow_work'] = true;
				foreach ($list as $addon) {
					$action_name = 'usersync.' . $addon->name . '.juserSave';
					$result = oseMscAddon :: runAction($action_name, $params);
					if (!$result['success']) {
						return $result;
					}
				}
			}
			return $result;
		}
	}
	function checkEmail($email, $member_id) {
		$db = JFactory :: getDBO();
		$query = "SELECT `email` FROM `#__users` WHERE `id` NOT IN (" . (int) $member_id . ") AND `email` = " . $db->Quote($email);
		$db->setQuery($query);
		$result = $db->loadResult();
		return (!empty ($result)) ? true : false;
	}
	function formValidate() {
		$user = JFactory :: getUser();
		$username = JRequest :: getString('username', null);
		$result = array ();
		$updated = oseMscPublic :: uniqueUserName($username, $user->id);
		if ($updated['success']) {
			$result['result'] = $updated['success'];
		} else {
			$result['result'] = JText :: _('This username has been registered by other user.');
		}
		return $result;
	}
	private function jvsave($member_id, $post) {
		$mainframe = JFactory :: getApplication();
		$option = JRequest :: getCmd('option');
		// Initialize some variables
		$msg = "";
		$me = & JFactory :: getUser();
		$MailFrom = $mainframe->getCfg('mailfrom');
		$FromName = $mainframe->getCfg('fromname');
		$SiteName = $mainframe->getCfg('sitename');
		// Create a new JUser object
		$user = new JUser($member_id);
		$original_gid = $user->get('gid');
		if (!$user->bind($post)) {
			$result = array ();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Failed Updating Member Information');
			$result = oseJSON :: encode($result);
			oseExit($result);
		}
		// Are we dealing with a new user which we need to create?
		$isNew = ($user->get('id') < 1);
		if (!$isNew) {
			// if group has been changed and where original group was a Super Admin
			if ($user->get('gid') != $original_gid && $original_gid == 25) {
				// count number of active super admins
				$query = 'SELECT COUNT( id )' .				' FROM #__users' .				' WHERE gid = 25' .				' AND block = 0';
				$this->db->setQuery($query);
				$count = $this->db->loadResult();
				if ($count <= 1) {
					$result = array ();
					$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = JText :: _('Failed Updating Member Information');
					$result = oseJSON :: encode($result);
					oseExit($result);
				}
			}
		}
		/*
			 * Lets save the JUser object
			 */
		if (!$user->save()) {
			$result = array ();
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = $user->getError();
			$result = oseJSON :: encode($result);
			oseExit($result);
		}
		// For new users, email username and password
		// Capture the new user id
		if ($isNew) {
			$newUserId = $user->get('id');
		} else {
			$newUserId = null;
		}
		return $newUserId;
	}
}
?>