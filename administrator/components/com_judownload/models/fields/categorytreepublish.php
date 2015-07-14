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

require_once JPATH_SITE . '/components/com_judownload/helpers/helper.php';

class JFormFieldCategoryTreePublish extends JFormField
{
	protected $type = 'CategoryTreePublish';

	protected function getInput()
	{
		
		$attr             = $this->multiple ? ' multiple="multiple" size="7"' : '';
		$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive(1, false, true, true);
		$options          = array();
		foreach ($nestedCategories AS $categoryObj)
		{
			$options[] = JHtml::_('select.option', $categoryObj->id, str_repeat('|—', $categoryObj->level) . $categoryObj->title);
		}
		$html = JHtml::_('select.genericList', $options, $this->name, $attr, 'value', 'text', $this->value);

		return $html;
	}
}

?>