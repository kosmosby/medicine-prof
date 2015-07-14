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

class JUDownloadFieldCore_publish_down extends JUDownloadFieldDateTime
{
	protected $field_name = 'publish_down';

	public function PHPValidate($values)
	{
		$date           = JFactory::getDate();
		$publishedField = new JUDownloadFieldCore_published();
		$publishUpField = new JUDownloadFieldCore_publish_up();
		$publishUpValue = $this->fields_data[$publishUpField->id];
		if ($this->fields_data[$publishedField->id] == 1 && intval($publishUpValue) == 0)
		{
			$publishUpValue = $date->toSql();
		}

		if (intval($values) > 0 && intval($publishUpValue) > 0 && $values < $publishUpValue)
		{
			return JText::_('COM_JUDOWNLOAD_START_PUBLISH_AFTER_FINISH');
		}

		return true;
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (intval($this->value) == 0)
		{
			$this->setVariable('value', $this->value);

			return $this->fetch('output.php', __CLASS__);
		}
		else
		{
			return parent::getOutput();
		}
	}
}

?>