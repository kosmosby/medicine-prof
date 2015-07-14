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


class JUDownloadTableFieldGroup extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_fields_groups', 'id', $db);
	}

	
	public function bind($array, $ignore = array())
	{
		
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_judownload.fieldgroup.' . (int) $this->$k;
	}

	
	protected function _getAssetTitle()
	{
		return $this->name;
	}

	
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		
		$assetId = null;

		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('id'));
		$query->from($this->_db->quoteName('#__assets'));
		$query->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_judownload'));

		
		$this->_db->setQuery($query);
		if ($result = $this->_db->loadResult())
		{
			$assetId = (int) $result;
		}

		
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	
	public function delete($pk = null)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		$db = JFactory::getDbo();

		
		$query = "UPDATE #__judownload_categories SET selected_fieldgroup = 0, fieldgroup_id = 0 WHERE fieldgroup_id = $pk";
		$db->setQuery($query);
		$db->execute();

		
		$query = "SELECT id, caption FROM #__judownload_fields WHERE group_id = " . $pk;
		$db->setQuery($query);
		$extraFields = $db->loadObjectList();
		if ($extraFields)
		{
			$fieldTable = JTable::getInstance("Field", "JUDownloadTable");
			foreach ($extraFields AS $extraField)
			{
				if (!$fieldTable->delete($extraField->id))
				{
					$e = new JException(JText::_('COM_JUDOWNLOAD_CAN_NOT_DELETE_FIELD_X', $extraField->caption));
					$this->setError($e);

					return false;
				}
			}
		}

		
		

		return parent::delete($pk);
	}
}
