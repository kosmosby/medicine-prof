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


jimport('joomla.form.formfield');

class JFormFieldCollectionIcon extends JFormField
{

	
	protected $type = 'CollectionIcon';

	
	protected function getInput()
	{
		$params     = JUDownloadHelper::getParams();
		$max_upload = ini_get('upload_max_filesize');
		$src        = JUri::root() . JUDownloadFrontHelper::getDirectory("collection_icon_directory", "media/com_judownload/images/collection/", true) . $this->value;

		$html = "<div class=\"avatar\" style=\"float: left;\">";
		if ($this->value)
		{
			$html .= "<div class=\"clearfix\"><img src=\"" . $src . "\" width=\"" . $params->get('collection_icon_width', 100) . "px\" height=\"" . $params->get('collection_icon_height', 100) . "px\" /></div>";
			$html .= "<label><input type=\"checkbox\" name=\"remove_icon\" value=\"1\" />&nbsp;" . JText::_('COM_JUDOWNLOAD_REMOVE_ICON') . "</label>";
		}
		$html .= "<div class=\"clearfix\"><input type=\"file\" name=\"collection_icon\"  id=\"" . $this->id . "\" />";
		$html .= "<input type=\"hidden\" name=\"" . $this->name . "\" value=\"" . $this->value . "\" /></div>";
		$html .= "<div class=\"clearfix\"><i>" . JText::_('COM_JUDOWNLOAD_MAX_UPLOAD_FILESIZE') . " <strong>" . JUDownloadHelper::formatBytes($this->convertBytes($max_upload)) . "</strong></i></div>";
		$html .= "</div>";

		return $html;
	}

	protected function convertBytes($value)
	{
		if (is_numeric($value))
		{
			return $value;
		}
		else
		{
			$value_length = strlen($value);
			$qty          = substr($value, 0, $value_length - 1);
			$unit         = strtolower(substr($value, $value_length - 1));
			switch ($unit)
			{
				case 'k':
					$qty *= 1024;
					break;
				case 'm':
					$qty *= 1048576;
					break;
				case 'g':
					$qty *= 1073741824;
					break;
			}

			return $qty;
		}
	}

}
