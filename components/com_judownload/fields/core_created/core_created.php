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

jimport('joomla.html.html');

class JUDownloadFieldCore_created extends JUDownloadFieldDateTime
{
	protected $field_name = 'created';

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : $this->value;

			$this->setAttribute("type", "text", "input");
			$this->addAttribute("class", $this->getInputClass(), "input");
			$this->addAttribute("class", "readonly", "input");

			if ((int) $this->params->get("size", 32))
			{
				$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
			}
			$this->setAttribute("readonly", "readonly", "input");

			$this->setVariable('value', $value);

			return $this->fetch('input.php', __CLASS__);
		}
		else
		{
			return parent::getInput($fieldValue);
		}
	}

	public function onSaveDocument($value = '')
	{
		
		if ($this->is_new)
		{
			if (intval($value) == 0)
			{
				$date  = JFactory::getDate();
				$value = $date->toSql();
			}
		}

		return $value;
	}
}

?>