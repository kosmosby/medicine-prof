<?php
/**
* @version 1.5.0
* @package JOMWall Groups
* @author  AWDsolution.com. All rights reserved.
* @link http://www.AWDsolution.com
* @Copyrighted Commercial Software by  AWDsolution.com
* @license Proprietary (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
error_reporting(0);
// Include the syndicate functions only once
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'models' . DS . 'group.php');
require_once (dirname(__FILE__).DS.'helper.php');

$list =JomWallGroupsHelper::getList($params);
require(JModuleHelper::getLayoutPath('mod_jomwallgroups'));
