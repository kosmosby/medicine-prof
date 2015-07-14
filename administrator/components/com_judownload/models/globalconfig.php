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


jimport('joomla.application.component.modeladmin');


class JUDownloadModelGlobalconfig extends JModelAdmin
{
	protected $cache = array();

	
	public function getTable($type = 'Globalconfig', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.globalconfig', 'globalconfig', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	
	public function getItem($pk = null)
	{
		$storeId = md5(__METHOD__);
		if (!isset($this->cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('config_params');
			$query->from('#__judownload_categories');
			$query->where('parent_id = 0');
			$query->where('level = 0');
			$db->setQuery($query);
			$config_params = $db->loadResult();
			$registry      = new JRegistry;
			$registry->loadString($config_params);
			$globalConfig = $registry->toObject();
			foreach ($globalConfig AS $key => $value)
			{
				if (is_object($value))
				{
					$registry = new JRegistry;
					$registry->loadObject($value);
					$globalConfig->$key = $registry->toArray();
				}
			}

			$this->cache[$storeId] = $globalConfig;
		}

		return $this->cache[$storeId];
	}

	
	public function getScript()
	{
		return 'administrator/components/com_judownload/models/forms/globalconfig.js';
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.globalconfig.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
