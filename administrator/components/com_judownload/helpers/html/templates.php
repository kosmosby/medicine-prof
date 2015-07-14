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

class JHtmlTemplates
{
	
	public static function thumb($template, $type = null)
	{
		$template = strtolower($template);
		$basePath = JPATH_SITE . '/components/com_judownload/templates/' . $template;
		$baseUrl  = JUri::root(true) . '/components/com_judownload/';

		if ($type == 'thumbnail')
		{
			$fileList = JFolder::files($basePath . '/');

			if (!empty($fileList))
			{
				foreach ($fileList AS $fileName)
				{
					$explodeArray = explode('.', $fileName);
					if ($explodeArray[0] == 'template_thumbnail')
					{
						$extThumbnail = end($explodeArray);
						break;
					}
				}
			}

			if (isset($extThumbnail))
			{
				$thumb = $basePath . '/template_thumbnail.' . $extThumbnail;
			}
			if (isset($extThumbnail))
			{
				$preview = $basePath . '/template_thumbnail.' . $extThumbnail;
			}
		}
		elseif ($type == 'preview')
		{
			$fileList = JFolder::files($basePath . '/');

			if (!empty($fileList))
			{
				foreach ($fileList AS $fileName)
				{
					$explodeArray = explode('.', $fileName);
					if ($explodeArray[0] == 'template_preview')
					{
						$extPreview = end($explodeArray);
						break;
					}
				}
			}

			if (isset($extPreview))
			{
				$thumb = $basePath . '/template_preview.' . $extPreview;
			}

			if (isset($extPreview))
			{
				$preview = $basePath . '/template_preview.' . $extPreview;
			}
		}
		else
		{
			$fileList = JFolder::files($basePath . '/');
			if (!empty($fileList))
			{
				foreach ($fileList AS $fileName)
				{
					$explodeArray = explode('.', $fileName);
					if ($explodeArray[0] == 'template_thumbnail')
					{
						$extThumbnail = end($explodeArray);
					}
					elseif ($explodeArray[0] == 'template_preview')
					{
						$extPreview = end($explodeArray);
					}
				}
			}

			if (isset($extThumbnail))
			{
				$thumb = $basePath . '/template_thumbnail.' . $extThumbnail;
			}
			if (isset($extPreview))
			{
				$preview = $basePath . '/template_preview.' . $extPreview;

			}
		}

		$thumbIsImage = false;
		if (isset($thumb))
		{
			if (@is_array(getimagesize($thumb)))
			{
				$thumbIsImage = true;
			}
		}

		$previewIsImage = false;
		if (isset($preview))
		{
			if (@is_array(getimagesize($preview)))
			{
				$previewIsImage = true;
			}
		}

		$html = '';

		if (isset($thumb) && $thumbIsImage && file_exists($thumb))
		{
			if (JUDownloadHelper::isJoomla3x())
			{
				JHtml::_('bootstrap.tooltip');
			}
			JHtml::_('behavior.modal');

			if ($type == 'thumbnail')
			{
				$thumb = 'components/com_judownload/templates/' . $template . '/template_thumbnail.' . $extThumbnail;
				$html  = JHtml::_('image', $thumb, JText::_('COM_JUDOWNLOAD_PREVIEW'), array('width' => '200px', 'height' => '200px'));
			}
			elseif ($type == 'preview')
			{
				$thumb = 'components/com_judownload/templates/' . $template . '/template_preview.' . $extPreview;
				$html  = JHtml::_('image', $thumb, JText::_('COM_JUDOWNLOAD_PREVIEW'), array('width' => '200px', 'height' => '200px'));
			}
			else
			{
				$thumb = 'components/com_judownload/templates/' . $template . '/template_thumbnail.' . $extThumbnail;
				$html  = JHtml::_('image', $thumb, JText::_('COM_JUDOWNLOAD_PREVIEW'));
			}

			if (isset($preview) && $previewIsImage && file_exists($preview))
			{
				if ($type == 'thumbnail')
				{
					$preview = $baseUrl . '/templates/' . $template . '/template_thumbnail.' . $extThumbnail;
				}
				elseif ($type == 'preview')
				{
					$preview = $baseUrl . '/templates/' . $template . '/template_preview.' . $extPreview;
				}
				else
				{
					$preview = $baseUrl . '/templates/' . $template . '/template_preview.' . $extPreview;
				}

				if (JUDownloadHelper::isJoomla3x())
				{
					$html = '<a href="' . $preview . '" class="thumbnail pull-left modal hasTooltip" title="' . JHtml::tooltipText('COM_JUDOWNLOAD_CLICK_TO_ENLARGE') . '">' . $html . '</a>';
				}
				else
				{
					$html = '<a href="' . $preview . '" class="thumbnail pull-left modal hasTip" title="' . JText::_('COM_JUDOWNLOAD_CLICK_TO_ENLARGE') . '" style="width:200px;">' . $html . '</a>';
				}
			}
		}

		return $html;
	}
}
