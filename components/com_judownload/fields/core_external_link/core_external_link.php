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

class JUDownloadFieldCore_external_link extends JUDownloadFieldLink
{
	protected $field_name = 'external_link';

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		$file_detect = 0;

		
		$value = !is_null($fieldValue) ? $fieldValue : $this->value;
		if ($value)
		{
			if (JUri::isInternal($value))
			{
				$file_detect = 1;

				if (stripos($value, JUri::root()) === 0)
				{
					$path = JPATH_ROOT . "/" . str_replace(JUri::root(), "", $value);
				}
				else
				{
					$path = JPATH_ROOT . "/" . $value;
				}

				if (!JFile::exists($path))
				{
					$file_detect = 2;
				}
			}
		}

		$this->setAttribute("type", "text", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->setVariable('file_detect', $file_detect);
		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}
}

?>