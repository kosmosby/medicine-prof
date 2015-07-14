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

class JUDownloadFrontHelperLanguage
{
	
	protected static $cache = array();

	
	public static function loadLanguageForTopLevelCat()
	{
		$app  = JFactory::getApplication();
		$view = $app->input->getString('view', '');
		$id   = $app->input->getInt('id', 0);

		if (isset($view))
		{
			switch ($view)
			{
				case 'category':
					$topLevelCats = JUDownloadHelper::getCatsByLevel(1, $id);
					break;

				case 'document':
					$catId        = JUDownloadFrontHelperCategory::getMainCategoryId($id);
					$topLevelCats = JUDownloadHelper::getCatsByLevel(1, $catId);
					break;
			}

			if (!empty($topLevelCats))
			{
				$topLevelCat = $topLevelCats[0];
				if ($view == 'category' || $view == 'document')
				{
					JUDownloadFrontHelperLanguage::loadLanguageFile('com_judownload_' . $topLevelCat->id, JPATH_SITE);
				}
			}
		}

		return;
	}

	
	public static function loadLanguageFile($name, $basePath = JPATH_BASE, $reload = false, $default = true)
	{
		$lang  = JFactory::getLanguage();
		$files = array();

		if (is_string($name))
		{
			$tags = $lang->getKnownLanguages();

			if (!empty($tags))
			{
				foreach ($tags AS $tag => $value)
				{
					if (isset($files[$tag]))
					{
						$files[$tag][] = $name;
					}
					else
					{
						$files[$tag] = array($name);
					}
				}
			}
		}
		elseif ($name instanceof SimpleXMLElement)
		{
			if (!$name || !count($name->children()))
			{
				return 0;
			}
			$lang     = JFactory::getLanguage();
			$elements = $name->children();

			foreach ($elements AS $element)
			{
				if ($element)
				{
					
					$first_pos = strpos($element, '.');
					$last_pos  = strrpos($element, '.');
					$extension = substr($element, $first_pos + 1, $last_pos - $first_pos - 1);

					$tag = (string) $element->attributes()->tag;

					if (isset($files[$tag]))
					{
						$files[$tag][] = $extension;
					}
					else
					{
						$files[$tag] = array($extension);
					}
				}
			}
		}
		else
		{
			return false;
		}

		if (!empty($files))
		{
			foreach ($files AS $language => $file_names)
			{
				if (!empty($file_names))
				{
					foreach ($file_names AS $name)
					{
						$lang->load($name, $basePath, $language, $reload, $default);
					}
				}
			}
		}

		return true;
	}
}