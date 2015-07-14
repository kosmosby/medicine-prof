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


class JFormFieldExternalLink extends JFormField
{
	
	protected $type = 'externallink';

	
	protected function getInput()
	{
		
		$html      = '';
		$size      = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		if ($this->value)
		{
			if ($this->isInternal($this->value))
			{
				$html .= "<span class=\"internal-file\" title=\"" . JText::_("COM_JUDOWNLOAD_INTERNAL_FILE") . "\" >" . JText::_("COM_JUDOWNLOAD_INTERNAL_FILE") . "</span>";
				if (stripos($this->value, JUri::root()) === 0)
				{
					$path = JPATH_ROOT . "/" . str_replace(JUri::root(), "", $this->value);
				}
				else
				{
					$path = JPATH_ROOT . "/" . $this->value;
				}

				if (!JFile::exists($path))
				{
					$html .= "<span class=\"missing-file\" title=\"" . JText::_("COM_JUDOWNLOAD_MISSING_FILE") . "\" >" . JText::_("COM_JUDOWNLOAD_MISSING_FILE") . "</span>";
				}
			}
		}

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
		. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>' . $html;
	}

	public function isInternal($url)
	{
		$uri  = JUri::getInstance($url);
		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));
		if (stripos($base, JUri::root()) !== 0 && !empty($host))
		{
			return false;
		}

		return true;
	}

}
