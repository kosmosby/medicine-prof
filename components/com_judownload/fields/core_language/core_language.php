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

class JUDownloadFieldCore_language extends JUDownloadFieldBase
{
	protected $field_name = 'language';

	public function getPredefinedValuesHtml()
	{
		$items = self::getLanguages();

		return JHtml::_("select.genericlist", $items, "jform[predefined_values]", null, 'value', 'text', $this->value, $this->getId());
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$items = $this->getLanguages();
		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('items', $items);
		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	protected function getLanguages()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		
		$query->select('a.lang_code AS value, a.title AS text, a.title_native');
		$query->from('#__languages AS a');
		$query->where('a.published >= 0');
		$query->order('a.title');

		
		$db->setQuery($query);
		$items = $db->loadObjectList();
		array_unshift($items, new JObject(array('value' => '*', 'text' => JText::alt('JALL', 'language'))));

		return $items;
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options           = self::getLanguages();
		$options[0]->value = '';
		$options[0]->text  = JText::_('COM_JUDOWNLOAD_SELECT_LANGUAGE');

		$this->setVariable('defaultValue', $defaultValue);
		$this->setVariable('options', $options);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$db  = JFactory::getDbo();
			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$tag     = JFactory::getLanguage()->getTag();
				$where[] = $this->fieldvalue_column . ' IN (' . $db->quote($search) . ',' . $db->quote($tag) . ',' . $db->quote('*') . ',"")';
			}
			else
			{
				$where[] = $this->fieldvalue_column . ' IN (' . $db->quote($search) . ',' . $db->quote('*') . ',"")';
			}
		}
	}

	public function getBackendOutput()
	{
		return $this->getOutput();
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = $this->value;

		$this->setVariable('value', $value);

		return $this->fetch('output.php', __CLASS__);
	}
}

?>