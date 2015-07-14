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


jimport('joomla.application.component.modeladmin');


class JUDownloadModelPluginViewer extends JModelAdmin
{

	
	public function getTable($type = 'Plugin', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		return true;
	}

	
	public function getPluginTemplate($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('folder');
		$query->from('#__judownload_plugins');
		$query->where('id = ' . $id);
		$query->where('type = ' . $db->quote('plugin'));

		$db->setQuery($query);
		$folder = $db->loadResult();

		if ($folder)
		{
			
			$pluginFolderPath = JPATH_SITE . '/components/com_judownload/plugins/' . $folder;

			$installer = JInstaller::getInstance();

			
			$installer->setPath("source", $pluginFolderPath);

			
			$installer->findManifest();

			
			$xml            = $installer->getManifest();
			$templateFolder = null;

			if (isset($xml->templateFolder))
			{
				$templateFolder = $xml->templateFolder->__toString();
			}
			else
			{
				$templateFolder = 'tmpl';
			}

			if (JFolder::exists($pluginFolderPath . '/' . $templateFolder . '/'))
			{
				
				$asset_file = $pluginFolderPath . '/load_assets.php';
				if (JFILE::exists($asset_file))
				{
					include $asset_file;
				}

				return $pluginFolderPath . '/' . $templateFolder . '/';
			}
			else
			{
				return false;
			}
		}

		return false;
	}
}
