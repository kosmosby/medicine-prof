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



defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('imagelist');


class JFormFieldJUImageList extends JFormFieldImageList
{
	
	public $type = 'JUImageList';

	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$directory = JUDownloadFrontHelper::getDirectory((string) $this->element['rel'], (string) $this->element['defaultRel']) . "default/";
			
			$this->directory = $directory;
		}

		return $return;
	}
}
