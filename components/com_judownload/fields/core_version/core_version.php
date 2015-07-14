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

class JUDownloadFieldCore_version extends JUDownloadFieldText
{
	protected $field_name = 'version';

	protected $filter = 'CMD';

	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$updatedField         = new JUDownloadFieldCore_updated(null, $this->doc);
		$DBUpdatedFieldValue  = $updatedField->doc->updated;
		$newUpdatedFieldValue = isset($this->fields_data[$updatedField->id]) ? $this->fields_data[$updatedField->id] : "";

		
		if (!$this->is_new && ($newUpdatedFieldValue == $DBUpdatedFieldValue || $newUpdatedFieldValue == ""))
		{
			
			if ($this->doc->version != $value)
			{
				$date         = JFactory::getDate();
				$updatedValue = $date->toSql();
				$updatedField->storeValue($updatedValue);
			}
		}

		parent::storeValue($value, $type, $inputData);
	}
}

?>