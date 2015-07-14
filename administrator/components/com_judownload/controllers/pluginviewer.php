<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.controllerform');


class JUDownloadControllerPluginViewer extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_PLUGIN_VIEWER';

	
	public function cancel($key = null)
	{
		$this->setRedirect("index.php?option=com_judownload&view=dashboard");
	}

	public function process()
	{
		$app       = JFactory::getApplication();
		$plugin_id = $app->input->getInt('plugin_id', 0);
		$function  = $app->input->get('function', '', 'string');

		if ($plugin_id && $function)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT folder FROM #__judownload_plugins WHERE id  = $plugin_id";
			$db->setQuery($query);
			$folder = strtolower($db->loadResult());

			if ($folder)
			{
				require_once JPATH_SITE . "/components/com_judownload/plugins/$folder/$folder.php";
				$pluginClassName = "JUDownloadPlugin" . ucfirst($folder);
				if (class_exists($pluginClassName))
				{
					$class_methods = get_class_methods($pluginClassName);
					if (in_array($function, $class_methods))
					{
						$pluginClass = new $pluginClassName();
						$pluginClass->$function();
					}
				}
			}
		}
	}
}
