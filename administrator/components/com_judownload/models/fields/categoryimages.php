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

class JFormFieldCategoryImages extends JFormField
{
	protected $type = 'CategoryImages';

	protected function getInput()
	{
		$params = JUDownloadHelper::getParams($this->form->getValue('id'));
		if ($this->element['directory'] == "intro")
		{
			$path     = $params->get('category_intro_image_directory', 'media/com_judownload/images/category/intro/');
			$document = JFactory::getDocument();
			$script   = 'jQuery(document).ready(function($){
								$("#use-detail-image").change(function(){
									if($(this).is(":checked")){
										$(this).parent().find("input[type=\'file\']").attr("disabled", true);
										$(this).parent().find("#remove-image-intro").prop("checked", false).attr("disabled", true);
									}else{
										$(this).parent().find("input[type=\'file\']").attr("disabled", false);
										$(this).parent().find("#remove-image-intro").prop("checked", true).attr("disabled", false);
									}
								});
							})';
			$document->addScriptDeclaration($script);
			$html = '<input type="file" name="images[intro]" class="validate-images" />';
		}
		else
		{
			$html = '<input type="file" name="images[detail]" class="validate-images" />';
			$path = $params->get('category_detail_image_directory', 'media/com_judownload/images/category/detail/');
		}

		$html .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';
		if (!empty($this->value))
		{
			$src = JUri::root() . $path . $this->value;
			$html .= '<label></label><img style="border: 5px solid #c0c0c0; max-width:100px; max-height:100px;" src="' . $src . '" />';
			$html .= '<div><input id="remove-image-' . $this->element['directory'] . '" type="checkbox" name="remove_' . $this->id . '" value="1" style="float: left; margin-right: 5px;"/>';
			$html .= '<label for="remove-image-' . $this->element['directory'] . '" >' . JText::_('COM_JUDOWNLOAD_REMOVE_THIS_IMAGE') . '</label></div>';
		}

		if ($this->element['directory'] == "intro")
		{
			$html .= '<div><input id="use-detail-image" type="checkbox" name="use_detail_image" value="1" style="float: left; margin-right: 5px;"/>';
			$html .= '<label class="hasTip" title="' . JText::_('COM_JUDOWNLOAD_USE_DETAIL_IMAGE_AS_INTRO_IMAGE') . '::' . JText::_('COM_JUDOWNLOAD_USE_DETAIL_IMAGE_AS_INTRO_IMAGE_DESC') . '" for="use-detail-image">';
			$html .= JText::_('COM_JUDOWNLOAD_USE_DETAIL_IMAGE_AS_INTRO_IMAGE');
			$html .= '</label></div>';
		}

		return $html;
	}
}

?>