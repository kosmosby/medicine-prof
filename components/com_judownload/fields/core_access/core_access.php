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

class JUDownloadFieldCore_access extends JUDownloadFieldBase
{
	protected $field_name = 'access';
	protected $fieldvalue_column = 'vl.title';

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		
		if ($value === "")
		{
			$value = 1;
		}

		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	public function getPredefinedValuesHtml()
	{
		$default_predefined = $this->getDefaultPredefinedValues();

		return JHtml::_('access.level', 'jform[predefined_values]', $default_predefined, null, array(), 'jform_predefined_values');
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('value', $defaultValue);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search)
		{
			$where[] = "vl.id = " . (int) $search;
		}
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search)
		{
			$db      = JFactory::getDbo();
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
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

		if (!$this->value)
		{
			return "";
		}

		$value = $this->getAccessTitle($this->value);
		$this->setVariable('value', $value);

		return $this->fetch('output.php', __CLASS__);
	}

	protected function getAccessTitle($access)
	{
		$storeId = md5(__METHOD__ . "::" . $access);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('title');
			$query->from('#__viewlevels');
			$query->where('id = ' . (int) $access);
			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadResult();
		}

		return self::$cache[$storeId];
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'vl.title AS access_title');
		$this->appendQuery($query, 'left join', '#__viewlevels AS vl ON d.access = vl.id');

		return array('ordering' => 'access_title', 'direction' => $this->priority_direction);
	}
}

?>