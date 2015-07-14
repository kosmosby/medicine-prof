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

class JFormFieldTemplate extends JFormFieldList
{

	
	protected $type = 'template';

	
	protected function getOptions()
	{
		$ignore = $this->element["ignore"];

		
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('plg.title AS text');
		$query->select('tpl.id AS value');
		$query->select('tpl.lft,tpl.rgt,tpl.parent_id,tpl.level');
		$query->from('#__judownload_templates AS tpl');
		$query->join('LEFT', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
		if ($ignore == "root")
		{
			$query->where('lft > 0');
		}
		else
		{
			$templateId = $this->form->getValue('id');
			if (!$templateId)
			{
				$query->where('lft > 0');
			}
		}
		$query->order('tpl.lft ASC');

		
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
			if ($options[$i]->level == 0)
			{
				$options[$i]->text = 'Root';
			}
			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
			}
		}

		
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
