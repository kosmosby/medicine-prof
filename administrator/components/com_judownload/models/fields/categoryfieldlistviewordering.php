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

class JFormFieldCategoryFieldListviewOrdering extends JFormField
{
	protected $type = 'categoryFieldListviewOrdering';

	protected function getInput()
	{
		JHtml::_('behavior.modal');

		$document = JFactory::getDocument();
		$script   = "jQuery(document).ready(function($){
						$('#" . $this->id . " li select.blv').change(function(){
							$(this).parent().find('input.blv-value').val($(this).val());
						});

						$(\"#" . $this->id . "\").dragsort({ dragSelector: \"li\", dragEnd: saveOrder, placeHolderTemplate: \"<li class='placeHolder'><div></div></li>\", dragSelectorExclude: \"select, .chzn-container\"});
						function saveOrder() {
			                var data = jQuery('#" . $this->id . " li').map(function() { return $(this).data('itemid'); }).get();
		                }
					});";

		$document->addScriptDeclaration($script);

		$fields = JUDownloadHelper::getCatFields();
		$values = $this->value;
		$html   = '';
		$html .= '<ul class="category-fieldorder" id="' . $this->id . '">';
		if ($values)
		{
			foreach ($values AS $key => $value)
			{
				if (isset($fields[$key]))
				{
					$html .= '<li>';
					$html .= '<div>';
					$html .= '<span class="field-name">' . $fields[$key] . '</span>';
					$html .= '<select class="blv">';
					$html .= '<option value="0" ' . ($key == 'title' ? 'disabled ' : '') . ($value == 0 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_HIDE') . '</option>';
					$html .= '<option value="1" ' . ($key == 'title' ? 'disabled ' : '') . ($value == 1 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_LIST_VIEW') . '</option>';
					$html .= '<option value="2" ' . ($value == 2 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_LIST_VIEW_AND_SHOW') . '</option>';
					$html .= '</select>';
					$html .= '<input type="hidden" class="blv-value" value="' . $value[0] . '" name="' . $this->name . '[' . $key . ']" />';
					$html .= '</div>';
					$html .= '</li>';
					unset($fields[$key]);
				}
			}
		}

		
		if ($fields)
		{
			foreach ($fields AS $key => $field)
			{
				$value = 0;
				$html .= '<li>';
				$html .= '<div>';
				$html .= '<span class="field-name" title="Field name">' . $fields[$key] . '</span>';
				$html .= '<select class="blv">';
				$html .= '<option value="0" ' . ($key == 'title' ? 'disabled ' : '') . ($value == 0 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_HIDE') . '</option>';
				$html .= '<option value="1" ' . ($key == 'title' ? 'disabled ' : '') . ($value == 1 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_LIST_VIEW') . '</option>';
				$html .= '<option value="2" ' . ($value == 2 ? 'selected' : '') . '>' . JText::_('COM_JUDOWNLOAD_LIST_VIEW_AND_SHOW') . '</option>';
				$html .= '</select>';
				$html .= '<input type="hidden" class="blv-value" value="' . $value . '" name="' . $this->name . '[' . $key . ']" />';
				$html .= '</div>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';

		return $html;
	}
}

?>