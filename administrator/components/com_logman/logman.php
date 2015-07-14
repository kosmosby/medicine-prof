<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die;

if (!class_exists('Koowa'))
{
	if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_extman/extman.php')) {
		$error = JText::_('EXTMAN_ERROR');
	}
	elseif (!JPluginHelper::isEnabled('system', 'koowa')) {
		$link  = version_compare(JVERSION, '1.6.0', '>=') ? '&view=plugins&filter_folder=system' : '&filter_type=system';
		$error = sprintf(JText::_('EXTMAN_PLUGIN_ERROR'), JRoute::_('index.php?option=com_plugins'.$link));
	}

	return JFactory::getApplication()->redirect(JURI::base(), $error, 'error');
}

KService::get('com://admin/logman.aliases')->setAliases();

echo KService::get('com://admin/logman.dispatcher')->dispatch();
