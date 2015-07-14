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

class JUDownloadFieldCore_modified_by extends JUDownloadFieldCore_approved_by
{
	protected $field_name = 'modified_by';
	protected $filter = "UNSET";
	protected $fieldvalue_column = "ua1.name";

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;
		if ($value > 0)
		{
			$user = JFactory::getUser($this->value);
		}
		else
		{
			$user       = new stdClass();
			$user->id   = 0;
			$user->name = '';
		}

		$this->setAttribute("type", "text", "input");
		$this->setAttribute("readonly", "readonly", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('value', $value);
		$this->setVariable('user', $user);

		return $this->fetch('input.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$query->JOIN("LEFT", "#__users AS ua1 ON d.created_by = ua1.id");
			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$db      = JFactory::getDbo();
				$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
			}
			else
			{
				$where[] = "ua1.id = " . (int) $search;
			}
		}
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$db = JFactory::getDbo();
			$query->JOIN("LEFT", "#__users AS ua1 ON d.created_by = ua1.id");
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
		}
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$user = JFactory::getUser();
		if (!$this->is_new)
		{
			$value = $user->id;

			return parent::storeValue($value, $type, $inputData);
		}

		return true;
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'ua1.name AS modified_by_name');
		$this->appendQuery($query, 'left join', '#__users AS ua1 ON d.modified_by = ua1.id');

		return array('ordering' => 'modified_by_name', 'direction' => $this->priority_direction);
	}
}

?>