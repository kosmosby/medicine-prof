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

class JFormFieldRatingStatic extends JFormFieldList
{
	protected $type = 'ratingstatic';

	protected function getOptions()
	{
		
		$options = array();

		foreach ($this->element->children() AS $option)
		{
			
			if ($option->getName() != 'option')
			{
				continue;
			}

			
			$disabled = $option['disabled'] == 'true';
			if (!$disabled && $option['value'] == 'bycriteria' && !JUDownloadHelper::hasMultiRating())
			{
				$disabled = true;
			}
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $disabled)
			);

			
			$tmp->class = (string) $option['class'];

			
			$tmp->onclick = (string) $option['onclick'];

			
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}

?>