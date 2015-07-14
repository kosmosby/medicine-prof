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
class oseMscAddon {
	public static function loadAddons($objs, $type = null) {
		$path = null;
		foreach ($objs as $obj) {
			self::loadAddon($obj, $type);
		}
	}
	public static function loadAddon($obj, $type = null) {
		$path = null;
		if (empty($type)) {
			$type = $obj->type;
		}
		$path = oseMscMethods::getAddonPath("{$obj->name}.js", $type);
		if (JFile::exists(JPATH_SITE . DS . $path)) {
			if (!empty($obj->addon_name)) {
				oseHTML::script($path, '1.5');
			}
		}
	}
	public static function addAddons($module_name, $objs) {
		$js = array();
		foreach ($objs as $obj) {
			if (empty($obj->addon_name)) {
				continue;
			}
			$js[] = self::addAddon($module_name, $obj);
		}
		$js = implode('', $js);
		return $js;
	}
	public static function addAddon($module_name, $obj) {
		return $module_name . '.add' . '(' . $obj->addon_name . ');' . "\r\n";
	}
	public static function getAddonList($xtype, $isBackend = true, $action = null, $type = 'array', $key = null) {
		$db = oseDB::instance();
		$where = array();
		$xtype = $db->Quote($xtype);
		$where[] = "type = {$xtype}";
		if ($isBackend) {
			$where[] = 'backend = 1';
			$where[] = 'backend_enabled = 1';
		} else {
			$where[] = 'frontend = 1';
			$where[] = 'frontend_enabled = 1';
		}
		if ($action != null) {
			$where[] = ' `action` NOT IN ("0","") AND `action` IS NOT NULL';
		}
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_addon` " . $where . " ORDER BY ordering ASC ";
		$db->setQuery($query);
		$objs = oseDB::loadList($type, $key);
		return $objs;
	}
	public static function getActionName($addon, $action, $type = null) {
		if (empty($type)) {
			$type = oseObject::getValue($addon, 'type');
		}
		$actionName = oseObject::getValue($addon, 'action', 1);
		$addonName = oseObject::getValue($addon, 'name');
		if ($actionName == 1) {
			$action_name = "{$type}.{$addonName}.{$action}";
		} else {
			$action_name = "{$type}.{$actionName}.{$action}";
		}
		return $action_name;
	}
	public static function runAction($action_name, $params, $manual = false, $backend = true) {
		$part = explode('.', $action_name);
		if ($manual) {
			$path = $backend ? OSEMSC_B_ADDON : OSEMSC_F_ADDON;
		} else {
			$app = JFactory::getApplication();
			$path = $app->isAdmin() ? OSEMSC_B_ADDON : OSEMSC_F_ADDON;
		}
		if (count($part) < 3) {
			oseExit('Addon Fatal Error');
		}
		$actionName = null;
		$filename = array();
		for ($i = 1; $i < (count($part) - 1); $i++) {
			$filename[] = $part[$i];
			$actionName .= ucfirst($part[$i]);
		}
		$filename = implode('.', $filename);
		if (JFile::exists($path . DS . 'action' . DS . "{$part[0]}" . '.php')) {
			require_once($path . DS . 'action' . DS . "{$part[0]}" . '.php');
		}
		require_once($path . DS . 'action' . DS . "{$part[0]}.{$filename}" . '.php');
		$className = 'oseMscAddonAction' . ucfirst($part[0]) . $actionName;
		$class = new $className();
		if (method_exists($class, $part[count($part) - 1])) {
			return call_user_func(array($class, $part[count($part) - 1]), $params);
		} else {
			return array('success' => true);
		}
	}
	public function runActions($addons, $type, $action, $params) {
		foreach ($addons as $addon) {
			$action_name = "{$type}.{$addon->name}.{$action}";
			$updated = oseMscAddon::runAction($action_name, $params);
			if (!$updated['success']) {
				return $updated;
			}
		}
		return array('success' => true);
	}
	public static function quickSavePanel($prefix, $post, $actionInfo = null) {
		$db = oseDB::instance();
		if (isset($post['action'])) {
			$actionInfo = self::parseAction($post['action']);
		}
		$where = array();
		$msc_id = isset($post['msc_id']) ? $post['msc_id'] : null;
		if (empty($msc_id)) {
			return false; // No membership exists in the addon
		} else {
			unset($post['msc_id']);
			$where[] = 'id = ' . $db->Quote($msc_id);
		}
		$where[] = 'type = ' . $db->Quote($actionInfo->name);
		$params = array();
		$count = 1;
		foreach ($post as $key => $value) {
			if (strstr($key, $prefix)) {
				$newKey = preg_replace("/{$prefix}/", '', $key, $count);
				$params[$newKey] = $value;
			}
		}
		$params = $db->Quote(oseJson::encode($params));
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_ext` " . $where;
		$db->setQuery($query);
		$obj = oseDB::loadItem('obj');
		if (empty($obj)) {
			$query = " INSERT INTO `#__osemsc_ext` " . " (id,type,params)" . " VALUES " . " ({$msc_id}," . $db->Quote($actionInfo->name) . ",{$params}) ";
			$db->setQuery($query);
		} else {
			$query = " UPDATE `#__osemsc_ext` " . " SET " . " params = {$params} " . " WHERE id = {$obj->id}" . " AND type = " . $db->Quote($actionInfo->name);
			$db->setQuery($query);
		}
		return oseDB::query();
	}
	public static function parseAction($action) {
		$obj = new stdClass();
		$part = explode('.', $action);
		$obj->type = $part[0];
		$obj->name = $part[1];
		$obj->action = $part[2];
		return $obj;
	}
	public static function getExtInfoByQuery($msc_id, $xtype, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_ext` " . " WHERE id={$msc_id} AND type= " . $db->Quote($xtype);
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		$data = ($type == 'array') ? array() : new stdClass();
		if (empty($item)) {
			return $data;
		}
		$params = array();
		$extParams = oseObject::getValue($item, 'params');
		parse_str($extParams, $params);
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$data = oseObject::setValue($data, $key, $value);
			}
		}
		$data = oseObject::setValue($data, 'id', $msc_id);
		return $data;
	}
	public static function getExtInfo($msc_id, $xtype, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_ext` " . " WHERE id={$msc_id} AND type= " . $db->Quote($xtype);
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		$isData = ($type == 'array') ? true : false;
		$extParams = oseObject::getValue($item, 'params');
		$data = oseJson::decode($extParams, $isData);
		$data = oseObject::setValue($data, 'id', $msc_id);
		return $data;
	}
	public static function getExtInfoItem($msc_id, $xtype, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_ext` " . " WHERE id={$msc_id} AND type= " . $db->Quote($xtype);
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	public static function getPost($prefix, $post) {
		$params = array();
		foreach ($post as $key => $value) {
			if (strstr($key, $prefix)) {
				$newKey = str_replace($prefix, '', $key);
				$params[$newKey] = $value;
			}
		}
		return $params;
	}
	public static function enableAddon($addon_id, $isBackend = false) {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_addon`" . " WHERE id = {$addon_id}";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		if ($isBackend) {
			$value = ($item->backend_enabled == 1) ? 0 : 1;
			$set = "SET backend_enabled = {$value}";
		} else {
			$value = ($item->frontend_enabled == 1) ? 0 : 1;
			$set = "SET frontend_enabled = {$value}";
		}
		$query = " UPDATE `#__osemsc_addon`" . $set . " WHERE id = {$addon_id}";
		$db->setQuery($query);
		return oseDB::query();
	}
	public static function orderChange($node, $ordering) {
		$db = oseDB::instance();
		$current = $node;
		$type = $db->Quote($current->type);
		oseDB::lock('#__osemsc_addon');
		$query = " SELECT * FROM `#__osemsc_addon`" . " WHERE type={$type} AND ordering= {$ordering} ";
		$db->setQuery($query);
		// get the Current Msc paranet ID
		$obj = $db->loadObject();
		if ($current->ordering > $ordering) // After
		{
			$query = " UPDATE `#__osemsc_addon` " . " SET ordering= ordering+1 " . " WHERE ordering >= {$ordering} AND ordering < {$current->ordering} " . " AND type={$type}";
			$db->setQuery($query);
			if (!oseDB::query()) {
				oseDB::unlock();
				return false;
			}
			$query = " UPDATE `#__osemsc_addon` " . " SET ordering= {$ordering} " . " WHERE id = {$current->id} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				oseDB::unlock();
				return false;
			}
		} else {
			$query = " UPDATE `#__osemsc_addon` " . " SET ordering= ordering-1 " . " WHERE ordering <= {$ordering} AND ordering > {$current->ordering} " . " AND type={$type}";
			$db->setQuery($query);
			if (!oseDB::query()) {
				oseDB::unlock();
				return false;
			}
			$query = " UPDATE `#__osemsc_addon` " . " SET ordering= {$ordering} " . " WHERE id = {$current->id} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				oseDB::unlock();
				return false;
			}
		}
		oseDB::unlock();
		return true;
	}
	public static function reorder($type = null) {
		$db = oseDB::instance();
		$where = array();
		$where[] = "type = " . $db->Quote($type);
		$where = oseDB::implodeWhere($where);
		// Get the primary keys and ordering values for the selection.
		$query = " SELECT * FROM `#__osemsc_addon`" . $where . " ORDER BY ordering ASC";
		$db->setQuery($query);
		$rows = oseDB::loadList('obj');
		// Check for a database error.
		if ($db->getErrorNum()) {
			$result = JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $db->getErrorMsg());
			oseExit($result);
			return false;
		}
		// Compact the ordering values.
		foreach ($rows as $i => $row) {
			// Make sure the ordering is a positive integer.
			if ($row->ordering >= 0) {
				// Only update rows that are necessary.
				if ($row->ordering != $i + 1) {
					// Update the row ordering field.
					$query = " UPDATE `#__osemsc_addon` " . " SET ordering={$i}+1" . " WHERE id = {$row->id}";
					$db->setQuery($query);
					// Check for a database error.
					if (!oseDB::query()) {
						return false;
					}
				}
			}
		}
		return true;
	}
	public static function getAddon($addon_id, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_addon`" . " WHERE id = {$addon_id}";
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	public static function getAddonbyTitle($addon_title, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_addon`" . " WHERE title = {$addon_title}";
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	public static function getAddonbyName($addon_title, $xtype, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_addon`" . " WHERE name = '{$addon_title}' AND type = '{$xtype}'";
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	public function remove($addon_id) {
		$db = oseDB::instance();
		$query = " DELETE FROM `#__osemsc_addon`" . " WHERE id = {$addon_id}";
		$db->setQuery($query);
		return oseDB::query();
	}
	public static function uniqueUserName($username, $user_id) {
		$result = array();
		$result['success'] = false;
		$result['script'] = "('username').focus()";
		if (empty($username)) {
			$result['result'] = JText::_('This field is required');
		} else {
			$isValid = oseRegistry::call('member')->uniqueUserName($username, $user_id);
			if ($isValid) {
				$result['success'] = true;
			} else {
				$result['result'] = JText::_('This username has been registered by other user.');
			}
		}
		return $result;
	}
	public static function updateAddonSimple() {
		$addonName = JRequest::getString('addonname');
		$status = JRequest::getInt('status');
		self::updateAddon($addonName, $status);
	}
	public static function updateAddon($addonName, $status) {
		$functionname = 'update' . $addonName;
		self::$functionname($status);
	}
	public static function updateaddon_license_keymode($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('bridge', 'lic', 'License Bridge', 0, $status);
		$return = self::updateRecord('join', 'licuser', 'Join License User to Membership', $status, 0);
		$return = self::updateRecord('join', 'license', 'Join License Keys', $status, $status);
		$return = self::updateRecord('renew', 'license', 'Renew License Keys', $status, $status);
		$return = self::updateRecord('member_user', 'lickeys', 'License Keys', $status, 0);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_license_usermode($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('bridge', 'lic', 'License Bridge', 0, $status);
		$return = self::updateRecord('join', 'lic_msc', 'Join License Staff to Membership', $status, 0);
		$return = self::updateRecord('renew', 'lic_msc', 'Renew License Staff to Membership', $status, 0);
		$return = self::updateRecord('join', 'license', 'Join License Keys', $status, $status);
		$return = self::updateRecord('renew', 'license', 'Renew License Keys', $status, $status);
		$return = self::updateRecord('member_user', 'licuser', 'Additional Licensed User Accounts', $status, 0);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_k2item($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('k2item');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'k2item', 'K2 Item Management', 0, 1, 6, 'content', '1', 'oseMscAddon.k2item', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'k2item', 'K2 Item Management', 0, $status);
		}
		$installed = self::checkAddon('k2cat');
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'k2cat', 'K2 Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.k2cat', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'k2cat', 'K2 Category Control', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "k2";
			$destPath = JPATH_SITE . DS . "components" . DS . "com_k2";
			//$src[0] = $osePath.DS."models".DS."item.php";
			//$src[1] = $osePath.DS."models".DS."itemlist.php";
			//$dest[0] = $destPath.DS."models".DS."item.php";
			//$dest[1] = $destPath.DS."models".DS."itemlist.php";
			$src[0] = $osePath . DS . "views" . DS . 'itemlist' . DS . "view.feed.php";
			$dest[0] = $destPath . DS . "views" . DS . 'itemlist' . DS . "view.feed.php";
			if ($status == 1) {
				self::patchFiles($src, $dest);
			} else {
				self::restoreFiles($dest);
			}
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_k2group($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('k2group');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'k2group', 'K2 User Group Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.k2group', " . (int) $status . ", 0),"
					. " (NULL, 'k2group', 'Join: K2 Group', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'k2group', 'Renew: K2 Group', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'k2group', 'K2 User Group Bridge', 0, $status);
			$return = self::updateRecord('join', 'k2group', 'Join: K2 Group', $status, $status, 1);
			$return = self::updateRecord('renew', 'k2group', 'Renew: K2 Group', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_phpbb($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('phpbb');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'phpbb', 'PHPBB User Sync', 1, 1, 3, 'usersync', '1', NULL, " . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'phpbb', 'PHPBB Bridge', 0, 1, 4, 'bridge', '1', 'oseMscAddon.phpbb', " . (int) $status . ", 0), "
					. " (NULL, 'phpbb', 'Join PHPBB', 1, 1, 9, 'join', '1', '', " . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'phpbb', 'Renew PHPBB', 1, 1, 8, 'renew', '1', '', " . (int) $status . ", " . (int) $status . ");";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'phpbb', 'PHPBB Bridge', 0, $status);
			$return = self::updateRecord('usersync', 'phpbb', 'PHPBB User Sync', $status, $status, 1);
			$return = self::updateRecord('join', 'phpbb', 'Join PHPBB', $status, $status, 1);
			$return = self::updateRecord('renew', 'phpbb', 'Renew PHPBB', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_vm($status) {
		$db = oseDB::instance();
		require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'version.php');
		$VMVERSION = new vmVersion();
		if (isset($VMVERSION->RELEASE) && $VMVERSION->RELEASE <= 2.0) {
			$version = 1;
		} else {
			$version = 2;
		}
		if ($version == 2) {
			$installed = self::checkAddon('vm2');
			$return = true;
			if ($installed == false) {
				$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
				$db->setQuery($query);
				$order = $db->loadResult();
				$joinOrder = $order + 1;
				$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
				$db->setQuery($query);
				$order = $db->loadResult();
				$renewOrder = $order + 1;
				$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
						. " VALUES " . " (NULL, 'vm2', 'Virturmart Bridge', 0, 1, 2, 'bridge', '1', 'oseMscAddon.vm2', " . (int) $status . ", 0), "
						. " (NULL, 'vm2', 'Join: Virturmart Shopper Group', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "), "
						. " (NULL, 'vm2', 'Renew: Virturmart Shopper Group', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . ") ";
				$db->setQuery($query);
				if (!$db->query()) {
					$return = false;
				}
			} else {
				$return = self::updateRecord('bridge', 'vm2', 'Virturmart Bridge', 0, $status, 1);
				$return = self::updateRecord('join', 'vm2', 'Join: Virturmart Shopper Group', $status, $status, 1);
				$return = self::updateRecord('renew', 'vm2', 'Renew: Virturmart Shopper Group', $status, $status, 1);
			}
			if ($return == false) {
				self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
			} else {
				$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "vm2";
				$destPath = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . "models";
				$src[0] = $osePath . DS . "orders.php";
				$dest[0] = $destPath . DS . "orders.php";
				if ($status == 1) {
					self::patchFiles($src, $dest);
				} else {
					self::restoreFiles($dest);
				}
				self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
			}
		} else {
			$installed = self::checkAddon('vm');
			$return = true;
			if ($installed == false) {
				$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
				$db->setQuery($query);
				$order = $db->loadResult();
				$joinOrder = $order + 1;
				$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
				$db->setQuery($query);
				$order = $db->loadResult();
				$renewOrder = $order + 1;
				$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
						. " VALUES " . " (NULL, 'vm', 'Virturmart Bridge', 0, 1, 2, 'bridge', '1', 'oseMscAddon.vm', " . (int) $status . ", 0), "
						. " (NULL, 'vm', 'Join: Virturmart Shopper Group', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "), "
						. " (NULL, 'vm', 'Renew: Virturmart Shopper Group', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "), "
						. " (NULL, 'vm', 'VirtueMart UserSync', 1, 1, 1, 'usersync', '', '', " . (int) $status . ", " . (int) $status . ");";
				$db->setQuery($query);
				if (!$db->query()) {
					$return = false;
				}
			} else {
				$return = self::updateRecord('bridge', 'vm', 'Virturmart Bridge', 0, $status, 1);
				$return = self::updateRecord('join', 'vm', 'Join: Virturmart Shopper Group', $status, $status, 1);
				$return = self::updateRecord('renew', 'vm', 'Renew: Virturmart Shopper Group', $status, $status, 1);
				$return = self::updateRecord('usersync', 'vm', 'VirtueMart UserSync', $status, $status);
			}
			if ($return == false) {
				self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
			} else {
				$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "vm";
				$destPath = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . "classes";
				$src[0] = $osePath . DS . "ps_order.php";
				$src[1] = $osePath . DS . "ps_checkout.php";
				$dest[0] = $destPath . DS . "ps_order.php";
				$dest[1] = $destPath . DS . "ps_checkout.php";
				if ($status == 1) {
					self::patchFiles($src, $dest);
				} else {
					self::restoreFiles($dest);
				}
				self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
			}
		}
	}
	public static function updateaddon_jspt($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('jspt');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'jspt', 'Join: JSPT', 1, 1, 10, 'join', '1', '', " . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'jspt', 'Renew: JSPT', 1, 1, 9, 'renew', '1', '', " . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'jspt', 'JSPT Bridge', 0, 1, 3, 'bridge', '1', '', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'jspt', 'JSPT Bridge', 0, $status);
			$return = self::updateRecord('join', 'jspt', 'Join: JSPT', $status, $status, 1);
			$return = self::updateRecord('renew', 'jspt', 'Renew: JSPT', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_acymailing($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('acymailing');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'acymailing', 'AcyMailing Bridge', 0, 1, 3, 'bridge', '1', 'oseMscAddon.acymailing', " . (int) $status . ", 0),"
					. " (NULL, 'acymailing', 'Join: AcyMailing', 1, 1, 8, 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'acymailing', 'Renew: AcyMailing', 1, 1, 7, 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'acymailing', 'AcyMailing Bridge', 0, $status);
			$return = self::updateRecord('join', 'acymailing', 'Join: AcyMailing', $status, $status, 1);
			$return = self::updateRecord('renew', 'acymailing', 'Renew: AcyMailing', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_billinginfo_var1($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_body', 'billinginfo_var1', 'Billing Information', $status, 0, 1, 'oseMscAddon.billinginfo_var1');
		$status2 = ($status == 1) ? 0 : 1;
		$return = self::updateRecord('registerOS_body', 'billinginfo', 'Billing Information', $status2, 0, 1, 'oseMscAddon.billinginfo');
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_coupon($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_footer', 'coupon', 'Coupon', $status, 0);
		$return = self::updateRecord('join', 'coupon', 'Join: Coupon', $status, 0, 1);
		$return = self::updateRecord('renew', 'coupon', 'Renew: Coupon', $status, 0, 1);
		//$return = self::updateRecord('paymentOS', 'coupon',  'Coupon', $status, 0);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_login($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_header', 'login', 'Existing User? Please Login', $status, 0);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_login_fb($status) {
		$db = oseDB::instance();
		$return = true;
		$loginbox_status = ($status==1)?0:1;
		self::updateRecord('registerOS_header', 'login', 'Existing User? Please Login', $loginbox_status, 0);
		$return = self::updateRecord('registerOS_header', 'login_fb', 'Please login with your account or use Facebook to login', $status, 0, 0, 'oseMscAddon.login_fb');
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_payment($status) {
		if ($status == true) {
			$name = 'payment';
		} else {
			$name = 'payment_var5';
		}
		$db = oseDB::instance();
		$query = " UPDATE `#__osemsc_addon` SET " . " `name` = " . $db->Quote($name)
				. " WHERE `type`='registerOS_body' AND `title` = 'Payment' AND `addon_name` = 'oseMscAddon.payment'";
		$db->setQuery($query);
		if (!$db->query()) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_billinginfo($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_body', 'billinginfo', 'Billing Information', $status, 0, '1');
		$return = self::updateRecord('member_billing', 'billinginfo', 'Billing Information', $status, $status, '1', 'oseMscAddon.billinginfo');
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_msc_list_var7($status) {
		$result = self::updateaddon_msc_list_var($status, 'msc_list_var7');
		if ($result == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_msc_list_var8($status) {
		$result = self::updateaddon_msc_list_var($status, 'msc_list_var8');
		if ($result == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_msc_list_var($status, $var) {
		if ($status == true) {
			$name = $var;
			$addon_name = 'oseMscAddon.' . $var;
		} else {
			$name = 'msc_list';
			$addon_name = 'oseMscAddon.msc_list';
		}
		$db = oseDB::instance();
		$query = " UPDATE `#__osemsc_addon` SET " . " `name` = " . $db->Quote($name) . ", `addon_name` = " . $db->Quote($addon_name)
				. " WHERE `type`='registerOS_header' AND `title` = 'Membership List'";
		$db->setQuery($query);
		return $db->query();
	}
	public static function updateaddon_profile($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_body', 'profile', 'Additional Information', $status, 0, 1);
		$return = self::updateRecord('member_user', 'profile', 'Additional Information', $status, $status, '1', 'oseMscAddon.profile');
		//$return = self::updateRecord('paymentOS', 'coupon',  'Coupon', $status, 0);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_creditcardupdate($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('member_user', 'creditcardupdate', 'CREDITCARDUPDATE', $status, $status, '1', 'oseMscAddon.creditcardupdate');
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_payment_mode($status) {
		$db = oseDB::instance();
		$return = true;
		$return = self::updateRecord('registerOS_body', 'payment_mode', 'Membership Renewal Preference', $status, 0, 1);
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_pap($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('pap');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'pap', 'Join: PAP', 1, 1, 10, 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'pap', 'Renew: PAP', 1, 1, 9, 'renew', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'pap', 'Order: PAP', 1, 0, 1, 'register_order', '1', '', 0, " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('register_order', 'pap', 'Order: PAP', $status, 0, 1);
			$return = self::updateRecord('join', 'pap', 'Join: PAP', $status, $status, 1);
			$return = self::updateRecord('renew', 'pap', 'Renew: PAP', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_idev($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('idev');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'idev', 'Join: iDevAffiliate', 1, 1, 10, 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'idev', 'Renew: iDevAffiliate', 1, 1, 9, 'renew', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'idev', 'Order: iDevAffiliate', 1, 0, 1, 'register_order', '1', '', 0, " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('register_order', 'idev', 'Order: iDevAffiliate', $status, 0, 1);
			$return = self::updateRecord('join', 'idev', 'Join: iDevAffiliate', $status, $status, 1);
			$return = self::updateRecord('renew', 'idev', 'Renew: iDevAffiliate', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_phoca($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('phoca');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'phoca', 'PhocaDownload Mangement', 0, 1, 5, 'content', '1', 'oseMscAddon.phoca', " . (int) $status . ", 0); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'phoca', 'PhocaDownload Mangement', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "phocadownload";
			$destPath = JPATH_SITE . DS . "components" . DS . "com_phocadownload" . DS . "phocadownload";
			$src[0] = $osePath . DS . "models" . DS . "file.php";
			$src[1] = $osePath . DS . "models" . DS . "category.php";
			$dest[0] = $destPath . DS . "models" . DS . "file.php";
			$dest[1] = $destPath . DS . "models" . DS . "category.php";
			if ($status == 1) {
				self::patchFiles($src, $dest);
			} else {
				self::restoreFiles($dest);
			}
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function patchFiles($src, $dest) {
		jimport('joomla.filesystem.file');
		$i = 0;
		$results = true;
		foreach ($src as $srcfile) {
			if (JFile::exists($srcfile) && JFile::exists($dest[$i])) {
				if (!file_exists($dest[$i] . ".obk")) {
					$results = JFile::copy($dest[$i], $dest[$i] . ".obk");
					$results = JFile::copy($srcfile, $dest[$i]);
				}
			}
			$i++;
		}
		if ($results != true) {
			self::ajaxResponse(JText::_('ERROR'), $results . JText::_("Please manually copy the files from this folder: ") . dirname($src[0]) . " --> " . dirname($dest[0]));
		}
	}
	public static function restoreFiles($dest) {
		jimport('joomla.filesystem.file');
		$i = 0;
		$results = true;
		foreach ($dest as $destfile) {
			if (JFile::exists($destfile) && JFile::exists($destfile . ".obk")) {
				$results = JFile::delete($destfile);
				$results = JFile::move($destfile . ".obk", $destfile);
			}
			$i++;
		}
		if ($results != true) {
			//self::ajaxResponse("ERROR", $results. JText::_("Please manually restore all '.obk' files to the origifrom this folder: ").dirname($src[0])." --> ".dirname($dest[0]) );
		}
	}
	public static function updateaddon_jomsocial($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('jomsocial');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'jomsocial', 'Jomsocial User Information', 1, 0, 5, 'registerOS_body', '1', 'oseMscAddon.jomsocial', 0, " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('registerOS_body', 'jomsocial', 'Jomsocial User Information', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_rokdownload($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('rokdownload');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'rokdownload', 'Rokdownload Category Management', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.rokdownload', " . (int) $status
					. ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'rokdownload', 'Rokdownload Category Management', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_mtree($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('mtree');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'mtree', 'MosetTree Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.mtree', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'mtree', 'MosetTree Category Control', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_hwdvideo($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('hwdvideo');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'hwdvideo', 'HWDVideoShare Category Management', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.hwdvideo', " . (int) $status
					. ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'hwdvideo', 'HWDVideoShare Category Management', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_sobi2($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('sobi2');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'sobi2', 'SOBI2 Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.sobi2', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'sobi2', 'SOBI2 Category Control', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_sobipro($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('sobipro');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'sobipro', 'SOBIPro Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.sobipro', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'sobipro', 'SOBIPro Category Control', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_docman($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('docman');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'docman', 'DocMan Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.docman', " . (int) $status . ", 0),"
					. " (NULL, 'docman', 'Join: DocMan', 1, 1, 8, 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'docman', 'Renew: DocMan', 1, 1, 7, 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'docman', 'DocMan Bridge', 0, $status);
			$return = self::updateRecord('join', 'docman', 'Join: DocMan', $status, $status, 1);
			$return = self::updateRecord('renew', 'docman', 'Renew: DocMan', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_zoocat($status) {
		$db = oseDB::instance();
		$return = true;
		$installed = self::checkAddon('zoocat');
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'zoocat', 'Zoo Management', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.zoocat', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'zoocat', 'Zoo Management', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "zoo";
			$destPath = JPATH_SITE . DS . "components" . DS . "com_zoo";
			$src[0] = $osePath . DS . "controllers" . DS . "default.php";
			$dest[0] = $destPath . DS . "controllers" . DS . "default.php";
			if ($status == 1) {
				self::patchFiles($src, $dest);
			} else {
				self::restoreFiles($dest);
			}
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_osecredit($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('osecredit');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'osecredit', 'OSE Credit Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.osecredit', " . (int) $status . ", 0),"
					. " (NULL, 'osecredit', 'Join: OSE Credit', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'osecredit', 'Renew: OSE Credit', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'osecredit', 'OSE Credit Bridge', 0, $status);
			$return = self::updateRecord('join', 'osecredit', 'Join: OSE Credit', $status, $status, 1);
			$return = self::updateRecord('renew', 'osecredit', 'Renew: OSE Credit', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_ariquizcat($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('ariquizcat');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'ariquizcat', 'ARI Quiz Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.ariquizcat', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'ariquizcat', 'ARI Quiz Category Control', $status, 0);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_mtree_submit($status) {
		$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "mtree";
		$destPath = JPATH_SITE . DS . "components" . DS . "com_mtree";
		$src[0] = $osePath . DS . "mtree.php";
		$src[1] = $osePath . DS . "mtree.tools.php";
		$dest[0] = $destPath . DS . "mtree.php";
		$dest[1] = $destPath . DS . "mtree.tools.php";
		if ($status == 1) {
			self::patchFiles($src, $dest);
		} else {
			self::restoreFiles($dest);
		}
		self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
	}
	public static function updateaddon_mailchimp($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('mailchimp');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'mailchimp', 'MailChimp Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.mailchimp', " . (int) $status . ", 0),"
					. " (NULL, 'mailchimp', 'Join: MailChimp', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'mailchimp', 'Renew: MailChimp', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'mailchimp', 'MailChimp Bridge', 0, $status);
			$return = self::updateRecord('join', 'mailchimp', 'Join: MailChimp', $status, $status, 1);
			$return = self::updateRecord('renew', 'mailchimp', 'Renew: MailChimp', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_jdownloads($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('jdownloads');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'jdownloads', 'JDownloads Management', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.jdownloads', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'jdownloads', 'JDownloads Management', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_easyblog($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('easyblog');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'easyblog', 'EasyBlog Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.easyblog', " . (int) $status . ", 0),"
					. " (NULL, 'easyblog', 'Join: EasyBlog', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'easyblog', 'Renew: EasyBlog', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'easyblog', 'EasyBlog Bridge', 0, $status);
			$return = self::updateRecord('join', 'easyblog', 'Join: EasyBlog', $status, $status, 1);
			$return = self::updateRecord('renew', 'easyblog', 'Renew: EasyBlog', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_jshopping($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('jshopping');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'jshopping', 'JoomShopping Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.jshopping', " . (int) $status . ", 0), "
					. " (NULL, 'jshopping', 'Join: JoomShopping User Group', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "), "
					. " (NULL, 'jshopping', 'Renew: JoomShopping User Group', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . ") ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'jshopping', 'JoomShopping Bridge', 0, $status);
			$return = self::updateRecord('join', 'jshopping', 'Join: JoomShopping User Group', $status, $status, 1);
			$return = self::updateRecord('renew', 'jshopping', 'Renew: JoomShopping User Group', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			$osePath = JPATH_SITE . DS . "components" . DS . "com_osemsc" . DS . "crossover" . DS . "jshopping";
			$destPath = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_jshopping" . DS . "controllers";
			$src[0] = $osePath . DS . "orders.php";
			$dest[0] = $destPath . DS . "orders.php";
			$destPath2 = JPATH_SITE . DS . "components" . DS . "com_jshopping" . DS . "controllers";
			$src2[0] = $osePath . DS . "checkout.php";
			$dest2[0] = $destPath2 . DS . "checkout.php";
			if ($status == 1) {
				self::patchFiles($src, $dest);
				self::patchFiles($src2, $dest2);
			} else {
				self::restoreFiles($dest);
				self::restoreFiles($dest2);
			}
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_cb($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('cb');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'cb', 'Community Builder Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.cb', " . (int) $status . ", 0),"
					. " (NULL, 'cb', 'Join: Community Builder', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'cb', 'Renew: Community Builder', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'cb', 'Community Builder Bridge', 0, $status);
			$return = self::updateRecord('join', 'cb', 'Join: Community Builder', $status, $status, 1);
			$return = self::updateRecord('renew', 'cb', 'Renew: Community Builder', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_osedownload($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('osedownload');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'osedownload', 'OSE Download™ Management', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.osedownload', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'osedownload', 'OSE Download™ Management', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_eventbooking($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('eventbooking');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'eventbooking', 'Event Booking Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.eventbooking', " . (int) $status
					. ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'eventbooking', 'Event Booking Category Control', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_hidepayment($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('hidepayment');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'panel'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$panelOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'hidepayment', 'Payment Method Control', 0, 1, '{$panelOrder}', 'panel', '1', 'oseMscAddon.hidepayment', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('panel', 'hidepayment', 'Payment Method Control', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_hwdmedia($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('hwdmedia');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'content'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$newOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'hwdmedia', 'HWDMediaShare Category Control', 0, 1, '{$newOrder}', 'content', '1', 'oseMscAddon.hwdmedia', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('content', 'hwdmedia', 'HWDMediaShare Category Control', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_profilecontrol($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('profilecontrol');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'panel'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$panelOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'profilecontrol', 'Custom Fields Control', 0, 1, '{$panelOrder}', 'panel', '1', 'oseMscAddon.hidepayment', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('panel', 'profilecontrol', 'Custom Fields Control', 0, $status);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_juserbill($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('juserbill');
		$return = true;
		if ($installed == false) {
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'juserbill', 'My Account/Billing Information', 0, 1, '1', 'member_user', '1', 'oseMscAddon.juserbill', " . (int) $status . ", 0)";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('member_user', 'juserbill', 'My Account/Billing Information', 0, $status);
		}
		if (!empty($status)) {
			self::updateRecord('member_user', 'juser', 'My Account Information', 1, 0, 1);
			self::updateRecord('member_billing', 'billinginfo', 'Billing Information', 1, 0, 1);
		} else {
			self::updateRecord('member_user', 'juser', 'My Account Information', 1, 1, 1);
			self::updateRecord('member_billing', 'billinginfo', 'Billing Information', 1, 1, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateaddon_acymailing2($status) {
		$db = oseDB::instance();
		$installed = self::checkAddon('acymailing2');
		$return = true;
		if ($installed == false) {
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'bridge'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$bridgeOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'join'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$joinOrder = $order + 1;
			$query = "SELECT max(ordering) FROM `#__osemsc_addon` WHERE `type` = 'renew'";
			$db->setQuery($query);
			$order = $db->loadResult();
			$renewOrder = $order + 1;
			$query = " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) "
					. " VALUES " . " (NULL, 'acymailing2', 'AcyMailing Bridge', 0, 1, '{$bridgeOrder}', 'bridge', '1', 'oseMscAddon.acymailing2', " . (int) $status . ", 0),"
					. " (NULL, 'acymailing2', 'Join: AcyMailing', 1, 1, '{$joinOrder}', 'join', '1', ''," . (int) $status . ", " . (int) $status . "),"
					. " (NULL, 'acymailing2', 'Renew: AcyMailing', 1, 1, '{$renewOrder}', 'renew', '1', '', " . (int) $status . ", " . (int) $status . "); ";
			$db->setQuery($query);
			if (!$db->query()) {
				$return = false;
			}
		} else {
			$return = self::updateRecord('bridge', 'acymailing2', 'AcyMailing Bridge', 0, $status);
			$return = self::updateRecord('join', 'acymailing2', 'Join: AcyMailing', $status, $status, 1);
			$return = self::updateRecord('renew', 'acymailing2', 'Renew: AcyMailing', $status, $status, 1);
		}
		if ($return == false) {
			self::ajaxResponse(JText::_('ERROR'), $db->getErrorMsg());
		} else {
			self::ajaxResponse(JText::_('DONE'), JText::_('ADDON_IS_UPDATED_SUCCESSFULLY'), true);
		}
	}
	public static function updateRecord($type, $name, $title, $front, $back, $action = 0, $addon_name = null) {
		$db = oseDB::instance();
		$query = " SELECT count(id) FROM `#__osemsc_addon` " . " WHERE `type`='{$type}' AND `name`='{$name}'";
		$db->setQuery($query);
		$result = $db->loadResult();
		if (empty($result)) {
			$query = " INSERT INTO `#__osemsc_addon` "
					. " (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) VALUES "
					. " (NULL, '{$name}', '{$title}', " . (int) $front . ", " . (int) $back . ", '', '{$type}', $action, '{$addon_name}', " . (int) $back . ", " . (int) $front
					. ");";
		} else {
			$query = " UPDATE `#__osemsc_addon` SET " . " `backend_enabled` =" . (int) $back . ", `frontend_enabled` =" . (int) $front . ", `action` =" . (int) $action
					. " WHERE `type`='{$type}' AND `name`='{$name}' ";
		}
		$db->setQuery($query);
		if (!$db->query()) {
			return false;
		} else {
			return true;
		}
	}
	public static function checkAddon($addonName) {
		$addonName = str_replace("addon_", "", $addonName);
		$db = oseDB::instance();
		$query = "SELECT count(*) FROM `#__osemsc_addon` WHERE `name`= '{$addonName}'";
		$db->setQuery($query);
		$return = $db->loadResult();
		return $return;
	}
	public static function ajaxResponse($status, $message, $success = false) {
		$return['status'] = $status;
		$return['result'] = $message;
		if ($success == true) {
			$return['success'] = $success;
		} else {
			$return['success'] = false;
		}
		echo oseJSON::encode($return);
		exit;
	}
}
?>