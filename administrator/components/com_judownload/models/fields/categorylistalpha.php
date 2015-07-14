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

class JFormFieldCategoryListAlpha extends JFormField
{
	protected $type = 'categoryListAlpha';

	protected function getInput()
	{
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query->select('id, title, level');
		$query->from('#__judownload_categories');
		$query->where('(level = 0 OR level = 1)');
		$query->where('published = 1');

		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');
		$query->order('lft ASC');
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$options = array();
		if (count($categories) > 0)
		{
			foreach ($categories AS $category)
			{
				$options[] = JHtml::_('select.option', $category->id, str_repeat('- ', $category->level) . $category->title);
			}
		}

		$html = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value);

		return $html;
	}
}

?>