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

class JUDownloadFieldLink extends JUDownloadFieldBase
{
	protected $regex = "/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/";

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setAttribute("type", "text", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = $this->value;

		if ($value == "")
		{
			return "";
		}

		if ($this->params->get("link_text", ""))
		{
			$linkText = $this->params->get("link_text", "");
		}
		else
		{
			$linkText = urldecode($value);

			if ($this->params->get("strip_http", 1))
			{
				$linkText = str_replace("http://", "", $linkText);
			}

			$trim_long_url     = $this->params->get("trim_long_url", 0);
			$front_portion_url = $this->params->get('front_portion_url', 0);
			$back_portion_url  = $this->params->get('back_portion_url', 0);

			if ($trim_long_url > 0 && ($front_portion_url > 0 || $back_portion_url > 0) && strlen($linkText) > $trim_long_url)
			{
				$frontStr = $front_portion_url > 0 ? substr($linkText, 0, $front_portion_url) : '';
				$backStr  = $back_portion_url > 0 ? substr($linkText, (int) (0 - $back_portion_url)) : '';
				$linkText = $frontStr . '...' . $backStr;
			}
		}

		if ($this->params->get("link_counter", 0))
		{
			$value = 'index.php?option=com_judownload&task=document.redirecturl&doc_id=' . $this->doc_id . '&field_id=' . $this->id;
		}

		$this->setVariable('link', $value);
		$this->setVariable('linkText', $linkText);

		return $this->fetch('output.php', __CLASS__);
	}
}

?>