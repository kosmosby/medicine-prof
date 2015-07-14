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

JLoader::register('JFormFieldList', JPATH_LIBRARIES . '/joomla/form/fields/list.php');

class JFormFieldPlugins extends JFormFieldList
{
	protected $type = 'plugins';

	protected function getInput()
	{
		$plugintype = $this->element['plugintype'];
		if ($plugintype == 'field')
		{
			$pluginId = $this->form->getValue("plugin_id", null, 0);
			$db       = JFactory::getDbo();
			$query    = "SELECT COUNT(*) FROM #__judownload_plugins WHERE id = " . $pluginId . " AND core = 1";
			$db->setQuery($query);
			$isCore = $db->loadResult();
			
			if ($isCore)
			{
				$query = "SELECT id, title FROM #__judownload_plugins WHERE id = " . $this->form->getValue("plugin_id", null, 0);
				$db->setQuery($query);
				$plugin = $db->loadObject();
				$html   = '<span class="readonly">' . $plugin->title . '</span>';
				$html .= "<input type=\"hidden\" name=\"" . $this->name . "\" value=\"" . $plugin->id . "\" />";

				return $html;
			}
			else
			{
				return parent::getInput();
			}
		}
		else
		{
			return parent::getInput();
		}
	}

	protected function getOptions()
	{
		
		$options = array();

		foreach ($this->element->children() AS $option)
		{
			
			if ($option->getName() != 'option')
			{
				continue;
			}

			
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			
			$tmp->class = (string) $option['class'];

			
			$tmp->onclick = (string) $option['onclick'];

			
			$options[] = $tmp;
		}

		$plugintype = $this->element['plugintype'];
		$_options   = JUDownloadHelper::getPluginOptions($plugintype, 0);

		reset($options);

		if ($_options)
		{
			$options = array_merge($options, $_options);
		}

		return $options;
	}
}

?>