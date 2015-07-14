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

JFormHelper::loadFieldClass('list');

class JFormFieldStyle extends JFormFieldList
{
	
	protected $type = 'style';

	
	protected function getOptions()
	{
		$document = JFactory::getDocument();

		if (JUDownloadHelper::isJoomla3x())
		{
			$triggerJqueryChosen = '$("#jform_parent_id").trigger("liszt:updated");';
		}
		else
		{
			$triggerJqueryChosen = '';
		}

		$script = '
			jQuery(document).ready(function($){
				$("#jform_template_id").change(function(){
					var objData = {};
					objData.value = $(this).val();
					$.ajax({
						type: "POST",
						url : "index.php?option=com_judownload&task=style.changeTemplateId",
						data: objData
					}).done(function (data) {
						$("option","#jform_parent_id").remove();
						$("#jform_parent_id").append(data);
						' . $triggerJqueryChosen . '
					});
				});
			});
		';
		$document->addScriptDeclaration($script);

		$db = JFactory::getDbo();

		
		$options = array();

		$styleId = $this->form->getValue('id');

		if ($styleId)
		{
			$templateId = $this->form->getValue('template_id');
			$query      = $db->getQuery(true);
			$query->select('parent_id');
			$query->from('#__judownload_templates');
			$query->where('id = ' . (int) $templateId);
			$db->setQuery($query);
			$templateParentId = $db->loadResult();
		}
		else
		{
			$templateParentId = 0;
		}

		$query = $db->getQuery(true);
		$query->select('*');
		$query->select('title AS text');
		$query->select('id AS value');
		$query->from('#__judownload_template_styles');
		$query->where('template_id = ' . (int) $templateParentId);
		$query->order('lft ASC');

		
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
