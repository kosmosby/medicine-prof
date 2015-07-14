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

class JUDownloadFieldCore_modified extends JUDownloadFieldCore_approved_time
{
	protected $field_name = 'modified';
	protected $filter = 'UNSET';

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$date = JFactory::getDate();
		if (!$this->is_new)
		{
			$value = $date->toSql();

			return parent::storeValue($value, $type, $inputData);
		}

		return true;
	}
}

?>