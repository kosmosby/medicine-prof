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

jimport('joomla.plugin.plugin');

class plgExtensionJUDownload extends JPlugin
{
	/**
	 * @var    integer Extension Identifier
	 * @since  1.6
	 */
	private $eid = 0;

	/**
	 * @var    JInstaller Installer object
	 * @since  1.6
	 */
	private $installer = null;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Handle post extension install update sites
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		$this->installer = $installer;
		$this->eid       = $eid;

		// Check if is JUDL plugin
		if ($eid && $this->getPluginType())
		{
			if ($this->isPluginExisted())
			{
				$this->updateExtension();
			}
			else
			{
				$this->installExtension();
			}
		}
	}

	private function updateExtension()
	{
		$this->installExtension(true);
	}

	private function installExtension($update = false)
	{
		if (!$this->installer)
		{
			return false;
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$manifest      = $this->installer->manifest;
		$extensionType = $this->getPluginType();
		$folder        = $manifest->folder->__toString();
		$pluginTable   = JTable::getInstance('Plugin', 'JUDownloadTable');

		if ($update)
		{
			// Load JUDL plugin to table, return false if plugin does not exist
			if (!$pluginTable->load(
					array(
						'type'   => $extensionType,
						'folder' => $folder
					))
				&& !$pluginTable->load(
					array(
						'extension_id' => $this->eid
					)
				)
			)
			{
				return false;
			}
		}
		else
		{
			$pluginTable->id = 0;
		}

		$pluginTable->extension_id = $this->eid;
		$pluginTable->type         = $extensionType;
		$pluginTable->folder       = $folder;
		$pluginTable->title        = $manifest->name->__toString();
		$pluginTable->author       = $manifest->author ? $manifest->author->__toString() : '';
		$pluginTable->email        = $manifest->authorEmail ? $manifest->authorEmail->__toString() : '';
		$pluginTable->website      = $manifest->authorUrl ? $manifest->authorUrl->__toString() : '';
		$pluginTable->license      = $manifest->license ? $manifest->license->__toString() : '';
		$pluginTable->version      = $manifest->version ? $manifest->version->__toString() : '';
		$pluginTable->date         = $manifest->creationDate ? $manifest->creationDate->__toString() : '';
		$pluginTable->description  = $manifest->description ? $manifest->description->__toString() : '';

		if ($pluginTable->check() && $pluginTable->store())
		{
			switch ($this->getPluginType())
			{
				case 'template':
					// Find  the parent template id based on parent folder in xml
					if (isset($manifest->parent) && trim($manifest->parent->__toString()) != '')
					{
						$db    = JFactory::getDBO();
						$query = $db->getQuery(true);
						$query->select('tpl.id');
						$query->from('#__judownload_templates AS tpl');
						$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
						$query->where('plg.folder = ' . $db->quote(trim(strtolower($manifest->parent->__toString()))));
						$db->setQuery($query);
						$parentTemplateId = $db->loadResult();
						if (!$parentTemplateId)
						{
							$parentTemplateId = 1;
						}
					}
					else
					{
						$parentTemplateId = 1;
					}

					// Insert new template
					if (!$update)
					{
						$template = JTable::getInstance('Template', 'JUDownloadTable');
						// Store data to template table
						$template->id        = 0;
						$template->parent_id = $parentTemplateId;
						$template->plugin_id = $pluginTable->id;
						$template->setLocation($parentTemplateId, 'last-child');
						if (!$template->check() || !$template->store())
						{
							return false;
						}
					}
					break;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Get JUDL plugin type
	 * */
	private function getPluginType()
	{
		if ($this->installer && !is_null($this->installer->manifest->attributes()->judlplugintype))
		{
			return $this->installer->manifest->attributes()->judlplugintype->__toString();
		}
		else
		{
			return false;
		}
	}

	/*
	 * Check if plugin record exist
	 * */
	private function isPluginExisted()
	{
		$extensionType = $this->getPluginType();
		$folder        = $this->installer->manifest->folder->__toString();

		if (!$extensionType || !$folder)
		{
			return false;
		}

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_plugins');
		$query->where('(type = ' . $db->Quote($extensionType) . ' AND folder = ' . $db->Quote($folder) . ')', 'OR');
		$query->where('extension_id = ' . $db->Quote($this->eid));
		$db->setQuery($query);

		return $db->loadResult();
	}

	private function UninstallField($plugin)
	{
		$manifestFile = JPATH_SITE . '/components/com_judownload/fields/' . $plugin->folder . '/' . $plugin->folder . '.xml';
		if (!JFile::exists($manifestFile))
		{
			return false;
		}

		$xml = JFactory::getXML($manifestFile);
		if (!$xml)
		{
			return false;
		}

		$db = JFactory::getDBO();

		// Delete fields
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_fields');
		$query->where('plugin_id = ' . $plugin->id);
		$db->setQuery($query);
		$fieldIds = $db->loadColumn();
		if ($fieldIds)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$fieldTable = JTable::getInstance("Field", "JUDownloadTable");
			foreach ($fieldIds AS $fieldId)
			{
				if (!$fieldTable->delete($fieldId))
				{
					return false;
				}
			}
		}

		return true;
	}

	private function UninstallTemplate($plugin)
	{
		$manifestFile = JPATH_SITE . '/components/com_judownload/templates/' . $plugin->folder . '/' . $plugin->folder . '.xml';
		if (!JFile::exists($manifestFile))
		{
			return false;
		}

		$xml = JFactory::getXML($manifestFile);
		if (!$xml)
		{
			return false;
		}

		$db = JFactory::getDBO();

		// Check if plugin id is template of home style
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_plugins AS plg');
		$query->join('', '#__judownload_templates AS tpl ON tpl.plugin_id = plg.id');
		$query->join('', '#__judownload_template_styles AS style ON style.template_id = tpl.id');
		$query->where('style.home = 1');
		$query->where('plg.id = ' . $plugin->id);
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result)
		{
			// Find the default template style
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__judownload_template_styles')
				->where('template_id = 2')
				->order('id ASC');
			$db->setQuery($query, 0, 1);
			$defaultTemplateId = $db->loadResult();
			// If default style found, set it as the home style
			if ($defaultTemplateId)
			{
				$query = $db->getQuery(true);
				$query->update('#__judownload_template_styles')
					->set('home = 1')
					->where('template_id = ' . $defaultTemplateId);
			}
			// Else, set the first style(order by id) as home style
			else
			{
				$query = $db->getQuery(true);
				$query->update('#__judownload_template_styles')
					->set('home = 1')
					->where('id id > 2')
					->order('id ASC');
			}
			$db->setQuery($query, 0, 1);
			$db->execute();
		}

		// Delete data in table templates
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_templates');
		$query->where('plugin_id =' . $plugin->id);
		$db->setQuery($query);
		$templateId = $db->loadResult();
		if ($templateId)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$templateTable = JTable::getInstance('Template', 'JUDownloadTable');
			if (!$templateTable->delete($templateId))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Handle extension uninstall
	 *
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 * @param   integer     $result     Installation result
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		if ($result)
		{
			// Don't use JTable::getInstance here, but require file directly in case conflict if has many tables named "plugin"
			require_once JPATH_ADMINISTRATOR . "/components/com_judownload/tables/plugin.php";
			$db = JFactory::getDbo();
			$pluginTable = new JUDownloadTablePlugin($db);

			// If can not find extension_id in plugin table => return
			if (!$pluginTable || !$pluginTable->load(array('extension_id' => $eid)))
			{
				return;
			}

			$folder = '';
			switch ($pluginTable->type)
			{
				case 'field':
					$this->UninstallField($pluginTable);
					$folder = JPATH_SITE . '/components/com_judownload/fields/' . $pluginTable->folder;
					break;

				case 'template':
					$this->UninstallTemplate($pluginTable);
					$folder = JPATH_SITE . '/components/com_judownload/templates/' . $pluginTable->folder;
					break;

				case 'plugin':
					$folder = JPATH_SITE . '/components/com_judownload/plugins/' . $pluginTable->folder;
					break;
			}

			// Delete folder of each plugin type
			if ($folder && JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}

			$pluginTable->delete();
		}
	}
}

?>