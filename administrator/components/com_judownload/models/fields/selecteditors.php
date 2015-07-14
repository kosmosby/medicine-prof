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


class JFormFieldSelectEditors extends JFormFieldList
{
	
	protected $type = 'SelectEditors';

	
	protected function getOptions()
	{
		
		$folder = $this->element['folder'];

		if (!empty($folder))
		{
			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('element AS value, name AS text');
			$query->from('#__extensions');
			$query->where('folder = ' . $db->q($folder));
			$query->where('enabled = 1');
			$query->order('ordering, name');
			$db->setQuery($query);

			$options = $db->loadObjectList();

			$lang = JFactory::getLanguage();
			foreach ($options AS $i => $item)
			{
				$source    = JPATH_PLUGINS . '/' . $folder . '/' . $item->value;
				$extension = 'plg_' . $folder . '_' . $item->value;
				$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
				|| $lang->load($extension . '.sys', $source, null, false, false)
				|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
				|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false);
				$options[$i]->text = JText::_($item->text);
			}

			if ($db->getErrorMsg())
			{
				JError::raiseWarning(500, JText::_('JFRAMEWORK_FORM_FIELDS_PLUGINS_ERROR_FOLDER_EMPTY'));

				return '';
			}
		}
		else
		{
			JError::raiseWarning(500, JText::_('JFRAMEWORK_FORM_FIELDS_PLUGINS_ERROR_FOLDER_EMPTY'));
		}

		
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
