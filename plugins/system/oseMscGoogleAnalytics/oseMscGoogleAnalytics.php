<?php
/**
 * @version 5.0 +
 * @package Open Source Membership Control - com_osemsc
 * @subpackage Open Source Access Control - com_osemsc
 * @author Open Source Excellence (R) {@link http://www.opensource-excellence.com}
 * @author Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');
class plgSystemoseMscGoogleAnalytics extends JPlugin {
	var $_db = null;
	function plgSystemoseMscGoogleAnalytics(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	function onAfterDispatch() {
		$app = JFactory::getApplication('SITE');
		if ($app->isAdmin()) {
			return;
		}
		$document = JFactory::getDocument();
		if (!JFile::exists(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php')) {
			return false;
		} else {
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'init.php');
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_osemsc' . DS . 'helpers' . DS . 'oseMscPublic.php');
		}
		$config = oseMscConfig::getConfig('thirdparty', 'obj');
		$account = oseObject::getValue($config, 'gag_account');
		$standard_type = oseObject::getValue($config, 'gag_domain_mode');
		$domain = oseObject::getValue($config, 'gag_domain');
		$order_id = JRequest::getCmd('orderID', null);
		$code = oseMscPublic::htmlTrack($account, $standard_type, $domain, $order_id);
		$document->addScriptDeclaration($code);
	}
}
