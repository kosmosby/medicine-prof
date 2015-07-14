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

class JUDownloadFieldCore_featured extends JUDownloadFieldCore_published
{
	protected $field_name = 'featured';

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options    = array();
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->setAttribute("type", "radio", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		$this->setVariable('options', $options);
		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}
}

?>