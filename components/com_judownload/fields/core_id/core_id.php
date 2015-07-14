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

class JUDownloadFieldCore_id extends JUDownloadFieldBase
{
	protected $field_name = 'id';

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '</span>';
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setAttribute("type", "text", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : 0);

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->addAttribute("class", "readonly", "input");
		$this->setAttribute("readonly", "readonly", "input");

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$where[] = "d.id = " . (int) $search;
		}
	}

}

?>