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


abstract class JImageFilter
{
	
	protected $handle;

	
	public function __construct($handle)
	{
		
		if (!function_exists('imagefilter'))
		{
			
			JLog::add('The imagefilter function for PHP is not available.', JLog::ERROR);
			throw new RuntimeException('The imagefilter function for PHP is not available.');

			
		}

		
		if (!is_resource($handle) || (get_resource_type($handle) != 'gd'))
		{
			JLog::add('The image handle is invalid for the image filter.', JLog::ERROR);
			throw new InvalidArgumentException('The image handle is invalid for the image filter.');
		}

		$this->handle = $handle;
	}

	
	abstract public function execute(array $options = array());
}
