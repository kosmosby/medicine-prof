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
class oseMscAddonActionRegisterMscUser extends oseMscAddon {
	public static function save($params) {
		$result = array();
		$post = JRequest::get('post');
		$member_id = $params['member_id'];
		JRequest::setVar('member_id', $member_id);
		if (empty($member_id)) {
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
			return $result;
		}
		$db = oseDB::instance();
		$firstname = $db->Quote(oseObject::getValue($post, 'juser_firstname', $post['juser_username']));
		$lastname = $db->Quote(oseObject::getValue($post, 'juser_lastname'));
		$query = " SELECT COUNT(*) FROM `#__osemsc_userinfo` WHERE user_id = ". (int)$member_id;
		$db->setQuery($query);
		$exists = ($db->loadResult() > 0) ? true : false;
		if ($exists) {
			return array('success' => true);
		} else {
			$query = " INSERT INTO `#__osemsc_userinfo` (user_id,firstname,lastname)" 
				   . " VALUES" . " ({$member_id},{$firstname},{$lastname})";
			$db->setQuery($query);
			if (!oseDB::query()) {
				$result = array();
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText::_('Fail Saving OSE User Info.');
				return $result;
			}
			return array('success' => true);
		}
	}
}
?>