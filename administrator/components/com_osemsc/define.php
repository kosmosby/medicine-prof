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

// Define some properties
define('OSEMSC','com_osemsc');
define('OSEMSCTITLE','OSE MEMBERSHIP™');
define('OSEMSCVERSION','6.2.0');
define('OSEMSCFOLDER','components/'.OSEMSC);

define('OSEMSC_B_PATH',JPATH_ADMINISTRATOR.DS.'components'.DS.OSEMSC);
define('OSEMSC_B_CONTROLLER',OSEMSC_B_PATH.DS.'controllers');
define('OSEMSC_B_MODEL',OSEMSC_B_PATH.DS.'models');
define('OSEMSC_B_VIEW',OSEMSC_B_PATH.DS.'views');
define('OSEMSC_B_LIB',OSEMSC_B_PATH.DS.'libraries');
define('OSEMSC_B_HTML',OSEMSC_B_PATH.DS.'html');
define('OSEMSC_B_URL',JURI::root().'/administrator/'.OSEMSCFOLDER);
define('OSEMSC_B_ADDON',OSEMSC_B_PATH.DS.'addons');

define('OSEMSC_F_PATH',JPATH_SITE.DS.'components'.DS.OSEMSC);
define('OSEMSC_F_CONTROLLER',OSEMSC_F_PATH.DS.'controllers');
define('OSEMSC_F_MODEL',OSEMSC_F_PATH.DS.'models');
define('OSEMSC_F_VIEW',OSEMSC_F_PATH.DS.'views');
define('OSEMSC_F_HELPER',OSEMSC_F_PATH.DS.'helpers');
define('OSEMSC_F_URL',JURI::root().OSEMSCFOLDER);
define('OSEMSC_F_ADDON',OSEMSC_F_PATH.DS.'addons');

if (!defined('OSEMSC_F_URL'))
{
	define('OSEMSC_F_URL',JURI::root().OSEMSCFOLDER);
}

// Define the table Name
define('OSEMSC_ACL','#__OSEMSC_acl');
define('OSEMSC_EXT','#__OSEMSC_ext');
define('OSEMSC_CONTENT_B','#__OSEMSC_content_basic');
define('OSEMSC_CONTENT_E','#__OSEMSC_content_ext');
define('OSEMSC_MEM','#__OSEMSC_member');
define('OSEMSC_MEM_EXP','#__OSEMSC_member_exp');
define('OSEMSC_ADDON','#__OSEMSC_addons');

$version = new JVersion();
$version = substr($version->getShortVersion(),0,3);
if(!defined('JOOMLA16'))
{
	$value = ($version >= '1.6')?true:false;
	define('JOOMLA16',$value);
}
if(!defined('JOOMLA17'))
{
	$value = ($version == '1.7')?true:false;
	define('JOOMLA17',$value);
}
if(!defined('JOOMLA25'))
{
	$value = ($version == '2.5')?true:false;
	define('JOOMLA25',$value);
}
if(!defined('JOOMLA30'))
{
	$value = ($version >= '3.0')?true:false;
	define('JOOMLA30',$value);
}
?>
