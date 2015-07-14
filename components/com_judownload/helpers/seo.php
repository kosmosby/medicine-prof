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

class JUDownloadFrontHelperSeo
{
	
	protected static $cache = array();

	protected $type;
	protected $item;

	

	public static function seo($parent, $seoData = array())
	{
		$document = JFactory::getDocument();
		
		if(isset($parent->item->metatitle) && trim($parent->item->metatitle))
		{
			$document->setTitle($parent->item->metatitle);
		}
		elseif(isset($parent->item->title))
		{
			$document->setTitle($parent->item->title);
		}
		
		if(isset($parent->item->metadescription) && trim($parent->item->metadescription))
		{
			$document->setMetaData('description', $parent->item->metadescription);
		}
		
		if(isset($parent->item->metakeyword) && trim($parent->item->metakeyword))
		{
			$document->setMetaData('keywords', $parent->item->metakeyword);
		}
	}

	}