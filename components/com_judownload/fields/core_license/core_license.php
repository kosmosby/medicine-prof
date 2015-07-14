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

class JUDownloadFieldCore_license extends JUDownloadFieldBase
{
	protected $field_name = 'license_id';
	protected $fieldvalue_column = 'l.title';

	
	public function parseValue($value)
	{
		if ($value && is_numeric($value))
		{
			return JUDownloadFrontHelper::getLicense($value, '*', false);
		}

		return null;
	}

	public function getPredefinedValuesHtml()
	{
		$option = JUDownloadHelper::getLicenseOptions();
		array_unshift($option, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_LICENSE')));

		$default_predefined = $this->getDefaultPredefinedValues();

		return JHtml::_("select.genericlist", $option, "jform[predefined_values]", null, "value", "text", $default_predefined, $this->getId());
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options = JUDownloadHelper::getLicenseOptions();
		array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_LICENSE')));
		$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value->id : '');

		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('value', $value);
		$this->setVariable('options', $options);

		return $this->fetch('input.php', __CLASS__);
	}

	public function canView($options = array())
	{
		$storeId = md5(__METHOD__ . "::" . $this->doc_id . "::" . $this->id . "::" . serialize($options));

		if (!isset(self::$cache[$storeId]))
		{
			if (!$this->isPublished())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			if (isset($this->doc) && $this->doc->cat_id)
			{
				$params = JUDownloadHelper::getParams($this->doc->cat_id);
			}
			else
			{
				$params = JUDownloadHelper::getParams(null, $this->doc_id);
			}

			$show_empty_field = $params->get('show_empty_field', 0);
			
			if ($this->doc_id && !$show_empty_field)
			{
				$app = JFactory::getApplication();
				if ($app->isSite())
				{
					if (!is_object($this->value) || !$this->value->published)
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
				else
				{
					if (!is_object($this->value))
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
			}

			self::$cache[$storeId] = parent::canView($options);

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (is_object($this->value))
		{
			$app = JFactory::getApplication();

			if ($app->isSite())
			{
				if (!$this->value->published)
				{
					return '';
				}
			}

			$this->setVariable('options', $options);
			$this->setVariable('value', $this->value);

			return $this->fetch('output.php', __CLASS__);
		}

		return '';
	}

	public function getBackendOutput()
	{
		if (is_object($this->value))
		{
			$license = $this->value;

			$html = '<a href="index.php?option=com_judownload&task=license.edit&id=' . $license->id . '">' . $license->title . '</a>';

			return $html;
		}
		else
		{
			return "";
		}
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options = JUDownloadHelper::getLicenseOptions();
		array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_LICENSE')));

		$this->setVariable('options', $options);
		$this->setVariable('value', $defaultValue);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search)
		{
			$query->JOIN('LEFT', '#__judownload_licenses AS l ON l.id = d.license_id AND l.published = 1');
			$where[] = "l.id = " . (int) $search;
		}
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search)
		{
			$db = JFactory::getDbo();
			$query->JOIN('LEFT', '#__judownload_licenses AS l ON l.id = d.license_id AND l.published = 1');
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
		}
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'l.title AS license_title');
		$this->appendQuery($query, 'left join', '#__judownload_licenses AS l ON d.license_id = l.id');

		return array('ordering' => 'license_title', 'direction' => $this->priority_direction);
	}
}

?>