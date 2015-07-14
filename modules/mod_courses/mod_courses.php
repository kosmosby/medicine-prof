<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_custom
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$document =& JFactory::getDocument();

$document->addScript(JURI::base().'components/com_flexpaper/js/jquery.ui.js');
$document->addScript(JURI::base().'components/com_flexpaper/js/jquery.rotate.js');
$document->addStyleSheet(JURI::base().'components/com_flexpaper/css/jquery-ui.css');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$list = &modCoursesHelper::getList();

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_courses', $params->get('layout', 'default'));
