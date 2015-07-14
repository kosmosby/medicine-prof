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

$app = JFactory::getApplication();
$db  = JFactory::getDbo();

$html = "<div " . $this->getAttribute(null, null, "input") . ">";
$html .= "<ul class=\"main-cat-list nav clearfix\" style=\"margin-left: 0;\">";
$html .= "<li class=\"main-cat\">";
if ($mainCategory->id)
{
	$html .= JUDownloadHelper::generateCategoryPath($mainCategory->id);
}
$html .= "</li>";
$html .= "</ul>";
$html .= "<ul class=\"secondary-cat-list nav clearfix\">";
if ($secondaryCatIds)
{
	foreach ($secondaryCatIds AS $secondaryCatId)
	{
		if ($app->isSite())
		{
			if ($documentObject && is_object($documentObject))
			{
				if ($documentObject->approved <= 0)
				{
					$html .= '<li id="cat-' . $secondaryCatId . '"><a class="drag-icon"></a><span>' . JUDownloadHelper::generateCategoryPath($secondaryCatId) . '</span> <a href="#" onclick="return false" class="remove-secondary-cat" ><i class="icon-minus fa fa-minus-circle"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a></li>';
				}
				else
				{
					if ($params->get('can_change_secondary_categories', 1))
					{
						$html .= '<li id="cat-' . $secondaryCatId . '"><a class="drag-icon"></a><span>' . JUDownloadHelper::generateCategoryPath($secondaryCatId) . '</span> <a href="#" onclick="return false" class="remove-secondary-cat" ><i class="icon-minus fa fa-minus-circle"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a></li>';
					}
					else
					{
						$html .= '<li id="cat-' . $secondaryCatId . '"><a class="drag-icon"></a><span>' . JUDownloadHelper::generateCategoryPath($secondaryCatId) . '</span></li>';
					}
				}
			}
			else
			{
				$html .= '<li id="cat-' . $secondaryCatId . '"><a class="drag-icon"></a><span>' . JUDownloadHelper::generateCategoryPath($secondaryCatId) . '</span> <a href="#" onclick="return false" class="remove-secondary-cat" ><i class="icon-minus fa fa-minus-circle"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a></li>';
			}
		}
		else
		{
			$html .= '<li id="cat-' . $secondaryCatId . '"><a class="drag-icon"></a><span>' . JUDownloadHelper::generateCategoryPath($secondaryCatId) . '</span> <a href="#" onclick="return false" class="remove-secondary-cat" ><i class="icon-minus fa fa-minus-circle"></i> ' . JText::_('COM_JUDOWNLOAD_REMOVE') . '</a></li>';
		}
	}
}
$html .= "</ul>";

if (!$disabled)
{
	if ($app->isSite())
	{
		if ((isset($documentObject->approved) && $documentObject->approved <= 0) || $params->get('can_change_secondary_categories', 1) || $params->get('can_change_main_category', 1))
		{
			$html .= "<a href='#' class='change_categories' onclick='return false;'><i class='icon-folder fa fa-folder-open'></i> " . JText::_('COM_JUDOWNLOAD_CHANGE_CATEGORIES') . "</a>";
		}
	}
	else
	{
		$html .= "<a href='#' class='change_categories' onclick='return false;'><i class='icon-folder fa fa-folder-open'></i> " . JText::_('COM_JUDOWNLOAD_CHANGE_CATEGORIES') . "</a>";
	}

	$html .= '<div class="category_selection" style="display: none;">';
	if ($this->params->get('cat_selection_mode', 'all') == 'drilldown')
	{
		$html .= '<div class="active_pathway">' . JUDownloadHelper::generateCategoryPath($mainCategory->parent_id, 'li') . '</div>';
	}
	$html .= '<input type="hidden" name="' . $this->getName() . '[main]" class="validate-numeric required" id="input-main-cat" value="' . $mainCategory->id . '" />';
	$html .= '<input type="hidden" name="' . $this->getName() . '[secondary]" id="input-secondary-cats" value="' . $secondaryCatIdsStr . '" />';

	
	if ($this->params->get('cat_selection_mode', 'all') == 'all')
	{
		$allCategoryOptions = $this->getAllCategoryOptions();
		$html .= JHtml::_('select.genericlist', $allCategoryOptions, 'browse_cat', 'class="browse_cat" multiple="multiple" size="10"', 'id', 'title', '');
	}
	
	else
	{
		$childCategoryOptions = $this->getChildCategoryOptions($mainCategory->parent_id);
		$html .= JHtml::_('select.genericlist', $childCategoryOptions, 'browse_cat', 'class="browse_cat category_drilldown" multiple="multiple" size="10"', 'id', 'title', '');
	}

	$html .= '<div class="cat_action clearfix">';

	if ($app->isSite())
	{
		if ((isset($documentObject->approved) && $documentObject->approved <= 0) || $params->get('can_change_main_category', 1))
		{
			$html .= '<button class="btn btn-mini btn-primary update-main-cat">' . JText::_('COM_JUDOWNLOAD_UPDATE_CATEGORY') . '</button>';
		}
	}
	
	else
	{
		$html .= '<button class="btn btn-mini btn-primary update-main-cat">' . JText::_('COM_JUDOWNLOAD_UPDATE_CATEGORY') . '</button>';
	}

	
	if ((1 == 0 || 1 > 1) && $this->getTotalCategories() > 1)
	{
		if ($app->isSite())
		{
			if ((isset($documentObject->approved) && $documentObject->approved <= 0) || $params->get('can_change_secondary_categories', 1))
			{
				$html .= '<button class="btn btn-mini btn-info insert-secondary-cats">' . JText::_('COM_JUDOWNLOAD_ALSO_APPEAR_IN_THIS_CATEGORY') . '</button>';
			}
		}
		else
		{
			$html .= '<button class="btn btn-mini btn-info insert-secondary-cats">' . JText::_('COM_JUDOWNLOAD_ALSO_APPEAR_IN_THIS_CATEGORY') . '</button>';
		}
	}

	$html .= "</div>";
	$html .= "</div>";
}

$html .= "</div>";

echo $html;

?>