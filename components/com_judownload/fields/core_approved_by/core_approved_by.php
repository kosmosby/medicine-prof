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

class JUDownloadFieldCore_approved_by extends JUDownloadFieldCore_created_by
{
	protected $field_name = 'approved_by';
	protected $fieldvalue_column = "ua2.name";

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
		$this->addAttribute("class", $this->getInputClass(), "input");
		$this->addAttribute("class", "readonly", "input");

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}
		$this->setAttribute("readonly", "readonly", "input");

		$this->setVariable('value', $value);
		$this->setVariable('user', $user);

		return $this->fetch('input.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$query->JOIN("LEFT", "#__users AS ua2 ON d.created_by = ua2.id");
			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$db      = JFactory::getDbo();
				$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
			}
			else
			{
				$where[] = "ua2.id = " . (int) $search;
			}
		}
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$db = JFactory::getDbo();
			$query->JOIN("LEFT", "#__users AS ua2 ON d.created_by = ua2.id");
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
		}
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			if ($value)
			{
				return parent::storeValue($value, $type, $inputData);
			}
		}

		return true;
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'ua2.name AS approved_by_name');
		$this->appendQuery($query, 'left join', '#__users AS ua2 ON d.approved_by = ua2.id');

		return array('ordering' => 'approved_by_name', 'direction' => $this->priority_direction);
	}
}

?>