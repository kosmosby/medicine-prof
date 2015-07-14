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
// Define some properties
define('COMNAME', 'com_osereporter');
define('COMPONENT', 'components'.DS.COMNAME);
define('COMPONENTVER', '1.0.6');
define('OSEREPORTER_ADMIN_PATH', JPATH_ADMINISTRATOR.DS.'components'.DS.COMNAME);
define('OSEREPORTER_CONTROLLER', OSEREPORTER_ADMIN_PATH.DS.'controllers');
define('OSEREPORTER_MODEL', OSEREPORTER_ADMIN_PATH.DS.'models');
define('OSEREPORTER_VIEW', OSEREPORTER_ADMIN_PATH.DS.'views');

?>