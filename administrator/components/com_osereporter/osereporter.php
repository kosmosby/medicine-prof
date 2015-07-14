<?php
/**
  * @version       1.0 +
  * @package       Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Reporter - com_osereporter
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 24-May-2011
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
// Require the define file
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osereporter'.DS.'define.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
// Require the base controller
require_once(OSEREPORTER_CONTROLLER.DS.'controller.php');
require_once(OSEREPORTER_MODEL.DS.'model.php');
require_once(OSEREPORTER_VIEW.DS.'view.php');

JLoader :: register('OsemscHelper', dirname(__FILE__).DS.'helpers'.DS.'osemsc.php');
OsemscHelper :: addSubmenu();
// Create the Controller
$_c= new osereporterController();
$_c->initControl();
// Perform the Request task
$_c->executeTask(JRequest :: getCmd('task', null));
// Redirect if set by the controller
$_c->redirectE();
?>