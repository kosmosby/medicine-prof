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

$document->addScript(JURI::base().'components/com_flexpaper/js/jquery.colorbox-min.js');
$document->addStyleSheet(JURI::base().'components/com_flexpaper/css/colorbox.css');
$document->addScript(JURI::base().'components/com_flexpaper/js/certificate.js');
     

if ($params->def('prepare_content', 1))
{
	JPluginHelper::importPlugin('content');
	$module->content = JHtml::_('content.prepare', $module->content, '', 'mod_flexpaper.content');
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_flexpaper', $params->get('layout', 'default'));
