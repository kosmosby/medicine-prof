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
define("CPUVERSION", "4.1.0");
// Define some properties
define('OSECPU_B_PATH', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ose_cpu');
define('OSECPU_PATH_JS', 'components/');
define('OSECPU_B_PATH_JS', 'administrator/components/');
define('OSECPU_F_PATH_JS', 'components/com_ose_cpu/extjs');
define('OSECPU_F_PATH', JPATH_SITE . DS . 'components' . DS . 'com_ose_cpu');
$version = new JVersion();
$version = substr($version->getShortVersion(),0,3);
if(!defined('JOOMLA16'))
{
	$value = ($version >= '1.6')?true:false;
	define('JOOMLA16',$value);
}
if(!defined('JOOMLA30'))
{
	$value = ($version >= '3.0' && $version <='5.0')?true:false;
	define('JOOMLA30',$value);
}
?>