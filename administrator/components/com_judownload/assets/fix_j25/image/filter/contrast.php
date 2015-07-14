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


class JImageFilterContrast extends JImageFilter
{
	
	public function execute(array $options = array())
	{
		
		if (!isset($options[IMG_FILTER_CONTRAST]) || !is_int($options[IMG_FILTER_CONTRAST]))
		{
			throw new InvalidArgumentException('No valid contrast value was given.  Expected integer.');
		}

		
		imagefilter($this->handle, IMG_FILTER_CONTRAST, $options[IMG_FILTER_CONTRAST]);
	}
}
