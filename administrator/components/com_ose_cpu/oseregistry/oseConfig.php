<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
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
 */
defined('_JEXEC') or die(";)");
class oseConfig {
	public static function load($app = 'msc', $config_type = null, $type = 'array') {
		static $sApp, $objs;
		if ($sApp != $app) {
			$db = oseDB::instance();
			$where = array();
			if (!empty($config_type)) {
				if (is_array($config_type)) {
					$values = array();
					foreach ($config_type as $configType) {
						$values[] = $db->Quote($configType);
					}
				} else {
				}
			}
			$where[] = '`app`=' . $db->Quote($app);
			$where = oseDB::implodeWhere($where);
			$query = " SELECT * FROM `#__ose_app_config` " . $where;
			$db->setQuery($query);
			$objs = oseDB::loadList('obj');
		}
		if ($type == 'array') {
			$config = array();
		} else {
			$config = new stdClass();
		}
		if (empty($objs)) {
			return false;
		}
		foreach ($objs as $obj) {
			if (is_array($config_type)) {
				if (!in_array($obj->type, $config_type)) {
					continue;
				}
			} else {
				if (($obj->type != $config_type) && !empty($config_type)) {
					continue;
				}
			}
			if (is_float($obj->value)) {
				$config = oseSetValue($config, $obj->key, (float) $obj->value);
			} elseif (is_numeric($obj->value)) {
				$config = oseSetValue($config, $obj->key, (int) $obj->value);
			} else {
				$config = oseSetValue($config, $obj->key, $obj->value);
			}
		}
		$config = oseSetValue($config, 'id', 1);
		$sApp = $app;
		return $config;
	}
	public static function save($app = 'msc', $config_type, $vals) {
		$db = oseDB::instance();
		$config = self::load($app, $config_type, 'obj');
		$updated = true;
		foreach ($vals as $k => $v) {
			$where = array();
			$where[] = '`app`=' . $db->Quote($app);
			$where[] = '`type`=' . $db->Quote($config_type);
			$where[] = '`key`=' . $db->Quote($k);
			$where = oseDB::implodeWhere($where);
			$query = " SELECT COUNT(id) FROM `#__ose_app_config`" . $where;

			$db->setQuery($query);
			$total = $db->loadResult();
			if ($total > 0) {
				$iVals = array();
				$iVals['id'] = $total;
				$iVals['value'] = $v;
				$updated = oseDB::update('#__ose_app_config', 'id', $iVals);
			} else {
				$iVals = array();
				$iVals['app'] = $app;
				$iVals['type'] = $config_type;
				$iVals['key'] = $k;
				$iVals['value'] = $v;
				$updated = oseDB::insert('#__ose_app_config', $iVals);
			}
		}
		return $updated;
	}
	public static function getAdminGroup($app) {
		$config = self::load($app, '', 'obj');
		if (oseGetValue($config, 'admin_group', false) != false) {
			$mode = oseJson::decode($item->value);
		} else {
			$version = oseHTML::getJoomlaVersion();
			if ($version >= '1.6') {
				$db = JFactory::getDBO();
				$db->setQuery("SELECT id FROM #__usergroups");
				$groups = $db->loadObjectList();
				$admin_groups = array();
				foreach ($groups as $group)
				{
					if (JAccess::checkGroup($group->id, 'core.login.admin'))
					{
						$admin_groups[] = $group->id;
					}
					elseif (JAccess::checkGroup($group->id, 'core.admin'))
					{
						$admin_groups[] = $group->id;
					}
				}
				$admin_groups = array_unique($admin_groups);
				return $admin_groups; 
				
			} else {
				$mode = '[24,25]';
				$mode = oseJson::decode($mode);
			}
		}
		return $mode;
	}
}
class oseAppConfig {
	public static function load($app, $config = array()) {
		oseRegistry::register('registry', 'oseregistry');
		oseRegistry::call('registry');
		switch ($app) {
		case ('msc'):
		case ('mscv5'):
		case ('msc1v5'):
			oseRegistry::register('remote', 'remote');
			oseRegistry::register('email', 'email');
			oseRegistry::register('msc', 'membership');
			oseRegistry::register('user', 'user');
			oseRegistry::quickRequire('user');
			oseRegistry::register('member', 'member'); // default
			oseRegistry::register('payment', 'payment');
			oseRegistry::quickRequire('payment');
			oseRegistry::register('content', 'content');
			break;
		case ('mscv6'):
			oseRegistry::register('form', 'form');
			oseAppConfig::load('payment');
			oseRegistry::quickRequire('payment2');
			oseRegistry::register('payment2', 'paymentMsc');
			oseRegistry::register('msc', 'msc');
			oseRegistry::register('content', 'content');
			break;
		case ('credit'):
			oseAppConfig::load('payment');
			oseRegistry::register('form', 'form');
			oseRegistry::quickRequire('payment2');
			oseRegistry::register('payment2', 'paymentCredit');
			oseRegistry::register('credit', 'credit');
			oseRegistry::register('content2', 'content2');
			break;
		case ('ecash'):
			oseAppConfig::load('payment');
			oseRegistry::quickRequire('payment2');
			oseRegistry::register('payment2', 'paymentEcash');
			break;
		case ('contract'):
			oseRegistry::register('form', 'form');
			oseRegistry::register('contract', 'contract');
			oseRegistry::register('payment2', 'paymentContract');
			break;
		case ('ftable'):
			oseRegistry::register('ftable', 'ftable');
			break;
		case ('mart'):
			self::load('mscv6');
			self::load('contract');
			self::load('lic');
			oseRegistry::register('form', 'form');
			oseRegistry::register('mart', 'mart');
			oseRegistry::register('payment2', 'paymentMart');
			break;
		case ('commerce'):
		case ('payment'):
			oseRegistry::register('remote', 'remote');
			oseRegistry::register('email', 'email');
			oseRegistry::register('user2', 'user2');
			oseRegistry::quickRequire('user2');
			oseRegistry::register('locale', 'locale');
			oseRegistry::register('form', 'form');
			oseRegistry::register('event', 'event');
			oseRegistry::register('payment2', 'payment2');
			oseRegistry::quickRequire('payment2');
			break;
		case ('lic'):
		case ('license'):
			oseAppConfig::load('payment');
			oseRegistry::quickRequire('payment2');
			oseRegistry::register('payment2', 'paymentLic');
			oseRegistry::register('lic', 'lic');
			oseRegistry::register('form', 'form');
			break;
		case ('migration'):
			oseRegistry::register('user2', 'user2');
			oseRegistry::quickRequire('user2');
			self::load('mscv6');
			break;
		default:
			oseRegistry::register('user', 'user');
			break;
		}
	}
}
?>