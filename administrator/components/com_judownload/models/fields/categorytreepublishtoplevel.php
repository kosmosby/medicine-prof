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

class JFormFieldCategoryTreePublishTopLevel extends JFormField
{
	protected $type = 'CategoryTreePublishTopLevel';

	protected function getInput()
	{
		
		$attr     = $this->multiple ? ' multiple="multiple" size="7"' : '';
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_categories');
		$query->where('level IN (0,1)');

		
		$query->where('published = 1');
		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');

		
		$query->where('access IN (' . $levelsStr . ')');
		$query->order('lft asc');

		$db->setQuery($query);
		$nestedCategories = $db->loadObjectList();
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