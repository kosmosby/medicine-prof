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

class JFormFieldTagSelector extends JFormField
{
	protected $type = 'tagSelector';

	protected function getInput()
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_tags');
		$query->where('published = 1');
		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		$options = array();
		if (!empty($tags))
		{
			$options[] = JHtml::_('select.option', "", JText::_('JALL'));
			foreach ($tags AS $tag)
			{
				$options[] = JHtml::_('select.option', $tag->id, $tag->title);
			}
		}
		$html = JHtml::_('select.genericlist', $options, $this->name . "[]", 'class="inputbox" multiple="multiple" size="8"', 'value', 'text', '', "");

		return $html;
	}
}

?>