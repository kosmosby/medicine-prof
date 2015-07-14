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
jimport('joomla.application.component.helper');
require_once(JPATH_COMPONENT_SITE . DS . 'init.php');
// Require the com_content helper library
require_once(OSEMSC_F_HELPER . DS . 'query.php');
require_once(OSEMSC_F_HELPER . DS . 'route.php');
require_once(OSEMSC_F_HELPER . DS . 'osecontrol.php');
require_once(OSEMSC_F_HELPER . DS . 'oseMscPublic.php');
// Require the base controller
if (JOOMLA30 == true) {
	require_once(OSEMSC_F_CONTROLLER . DS . 'controller.php');
	require_once(OSEMSC_F_MODEL . DS . 'model.php');
	require_once(OSEMSC_F_VIEW . DS . 'view.php');
} else {
	require_once(JPATH_COMPONENT . DS . 'legacy' . DS . 'controller.php');
	require_once(JPATH_COMPONENT . DS . 'legacy' . DS . 'model.php');
	require_once(JPATH_COMPONENT . DS . 'legacy' . DS . 'view.php');
}
// load mobile detect
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'mobile_detect.php');
// Create the Controller
$_c = new osemscController();
$_c->initControl();
// Perform the Request task
$_c->executeTask(JRequest::getCmd('task', null));
// Redirect if set by the controller
$_c->redirectE();
?>