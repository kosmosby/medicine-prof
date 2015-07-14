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

class JFormFieldAvatar extends JFormField
{

	
	protected $type = 'Avatar';

	
	protected function getInput()
	{
		$params     = JUDownloadHelper::getParams();
		$max_upload = ini_get('upload_max_filesize');
		if ($this->value)
		{
			$src = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("avatar_directory", "media/com_judownload/images/avatar/", true) . $this->value;
		}
		else
		{
			$src = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("avatar_directory", "media/com_judownload/images/avatar/", true) . "default/" . $params->get('default_avatar', 'default-avatar.png');
		}
		$html = '<div class="avatar" style="float: left;">';
		$html .= '<div class="clearfix"><img src="' . $src . '" alt="Avatar" style="width:' . $params->get("avatar_width", 120) . 'px; height:' . $params->get("avatar_height", 120) . 'px;" /></div>';
		if ($this->value)
		{
			$html .= '<label for="remove-avatar">' . JText::_("COM_JUDOWNLOAD_REMOVE_AVATAR") . '&nbsp;<input id="remove-avatar" type="checkbox" name="remove_avatar" value="1" /></label>';
		}
		$html .= '<div class="clearfix"><input type="file" name="avatar"  id="' . $this->id . '" />';
		$html .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" /></div>';
		$html .= '<div class="clearfix"><i>' . JText::_("COM_JUDOWNLOAD_MAX_UPLOAD_FILESIZE") . ' <strong>' . JUDownloadHelper::formatBytes($this->convertBytes($max_upload)) . '</strong></i></div>';
		$html .= '</div>';

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
