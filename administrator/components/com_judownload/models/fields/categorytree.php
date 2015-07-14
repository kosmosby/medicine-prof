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

JLoader::register('JFormFieldList', JPATH_LIBRARIES . '/joomla/form/fields/list.php');

class JFormFieldCategoryTree extends JFormFieldList
{
	protected $type = 'CategoryTree';

	protected function getOptions()
	{
		$app      = JFactory::getApplication();
		$view     = $app->input->getCmd('view', '');
		$parentId = $app->input->getInt('parent_id', 1);
		$catId    = $app->input->getInt('id', 0);

		
		$options  = array();
		$_options = array();

		foreach ($this->element->children() AS $option)
		{
			
			if ($option->getName() != 'option')
			{
				continue;
			}

			
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			
			$tmp->class = (string) $option['class'];

			
			$tmp->onclick = (string) $option['onclick'];

			
			$options[] = $tmp;
		}

		JLoader::register('JUDownloadFrontHelperCategory', JPATH_SITE . '/components/com_judownload/helpers/category.php', false);
		JLoader::register('JUDownloadHelper', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/judownload.php', false);

		$checkPublished = ($this->element['checkpublished'] == 'true' || $this->element['checkpublished'] == '1') ? true : false;

		$checkCreatePermission = false;
		if (isset($this->element['checkcreatepermissiononcat']))
		{
			if ($this->element['checkcreatepermissiononcat'] == 'true' || $this->element['checkcreatepermissiononcat'] == '1')
			{
				$checkCreatePermission = 'category';
			}
		}
		else
		{
			if ($this->element['checkcreatepermissionondoc'] == 'true' || $this->element['checkcreatepermissionondoc'] == '1')
			{
				$checkCreatePermission = 'document';
			}
		}

		$getSelf    = ($this->element['fetchself'] == 'true' || $this->element['fetchself'] == '1') ? true : false;
		$startLevel = $this->element['startlevel'] ? $this->element['startlevel'] : 0;
		$separation = $this->element['separation'] ? $this->element['separation'] : '|—';
		$ignorecat  = $this->element['ignorecat'] ? explode(',', $this->element['ignorecat']) : array();

		if ($view == 'category')
		{
			if ($catId)
			{
				$_options = JUDownloadHelper::getCategoryOptions(1, $getSelf, $checkCreatePermission, $checkPublished, array($catId), $startLevel, $separation);
			}
			elseif ($parentId)
			{
				$_options = JUDownloadHelper::getCategoryOptions(1, $getSelf, $checkCreatePermission, $checkPublished, $ignorecat, $startLevel, $separation);
				if ($this->value == "")
				{
					$this->value = $parentId;
				}
			}
		}
		else
		{
			$_options = JUDownloadHelper::getCategoryOptions(1, $getSelf, $checkCreatePermission, $checkPublished, $ignorecat, $startLevel, $separation);
		}

		reset($options);

		$options = array_merge($options, $_options);

		return $options;
	}
}

?>