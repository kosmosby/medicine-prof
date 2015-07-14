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
// Require the com_content helper library
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_osemsc' . DS . 'define.php');
if (!file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ose_cpu' . DS . 'define.php')) {
	$mainframe = &JFactory::getApplication();
	if ($mainframe->isAdmin()) {
		$mainframe->redirect("index.php", "OSE CPU NOT installed");
	} else {
		return false;
	}
}
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ose_cpu' . DS . 'define.php');
require_once(OSECPU_B_PATH . DS . 'oseregistry' . DS . 'oseregistry.php');
oseRegistry::register('registry', 'oseregistry');
oseRegistry::call('registry');
oseRegistry::register('msc', 'membership');
$msc = oseRegistry::call('msc');
$config = oseMscConfig::getConfig(null, 'obj');
if (!empty($config->is_msc_mode_customized)) {
	oseRegistry::quickRequire('msc');
	if (!empty($config->customized_msc_mode)) {
		oseRegistry::register('msc', $config->customized_msc_mode);
	}
} else {
	$config->msc_extend = (isset($config->msc_extend)) ? $config->msc_extend : '';
	switch ($config->msc_extend) {
	case ('license'):
		oseRegistry::quickRequire('msc');
		oseRegistry::register('member', "msc{$config->msc_mode}");
		break;
	default:
		break;
	}
}
oseRegistry::register('user', 'user');
oseRegistry::quickRequire('user');
oseRegistry::register('member', 'member'); // default
if (!empty($config->is_member_mode_customized)) {
	oseRegistry::quickRequire('member');
	if (!empty($config->customized_member_mode)) {
		oseRegistry::register('member', $config->customized_member_mode);
	}
} else {
	$config->member_mode = (isset($config->member_mode)) ? $config->member_mode : '';
	switch ($config->member_mode) {
	case ('multi'):
		oseRegistry::call('member');
		break;
	default:
		oseRegistry::call('member');
		break;
	}
}
oseRegistry::register('payment', 'payment');
oseRegistry::quickRequire('payment');
if (empty($config->payment_system)) {
	$config->register_form = (isset($config->register_form)) ? $config->register_form : 'onestep';
	if ($config->register_form == 'default' || empty($config->register_form)) {
		oseRegistry::register('payment', 'paymentSC');
	}
} else {
	oseRegistry::register('payment', 'payment' . $config->payment_system);
}
oseRegistry::register('remote', 'remote');
oseRegistry::register('lic', 'license');
oseRegistry::register('content', 'content');
oseRegistry::register('debug', 'debug');
oseRegistry::quickRequire('debug');
?>