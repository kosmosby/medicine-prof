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


class JUDownloadTableField extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_fields', 'id', $db);
	}

	

	public function bind($array, $ignore = array())
	{
		
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_judownload.field.' . (int) $this->$k;
	}

	
	protected function _getAssetTitle()
	{
		return $this->caption;
	}

	
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		
		$assetId = null;

		
		if ($this->group_id > 0)
		{
			
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('asset_id'));
			$query->from($this->_db->quoteName('#__judownload_fields_groups'));
			$query->where($this->_db->quoteName('id') . ' = ' . $this->group_id);

			
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		
		if (!$assetId)
		{
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('id'));
			$query->from($this->_db->quoteName('#__assets'));
			$query->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_judownload'));

			
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
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

	
	public function rePriority($where = '')
	{
		
		if (!property_exists($this, 'priority'))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_PRIORITY', get_class($this)));
			$this->setError($e);

			return false;
		}

		
		$k = $this->_tbl_key;

		
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key . ', priority');
		$query->from($this->_tbl);
		$query->where('priority >= 0');
		$query->order('priority');
		
		if ($where)
		{
			$query->where($where);
		}
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REPRIORITY_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		foreach ($rows AS $i => $row)
		{
			
			if ($row->priority >= 0)
			{
				
				if ($row->priority != $i + 1)
				{
					
					$query = $this->_db->getQuery(true);
					$query->update($this->_tbl);
					$query->set('priority = ' . ($i + 1));
					$query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$k));
					$this->_db->setQuery($query);

					
					if (!$this->_db->execute())
					{
						$e = new JException(
							JText::sprintf('JLIB_DATABASE_ERROR_REPRIORITY_UPDATE_ROW_FAILED', get_class($this), $i, $this->_db->getErrorMsg())
						);
						$this->setError($e);

						return false;
					}
				}
			}
		}

		return true;
	}

	
	public function movePriority($delta, $where = '')
	{
		
		if (!property_exists($this, 'priority'))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_PRIORITY', get_class($this)));
			$this->setError($e);

			return false;
		}

		
		if (empty($delta))
		{
			return true;
		}

		
		$k     = $this->_tbl_key;
		$row   = null;
		$query = $this->_db->getQuery(true);

		
		$query->select($this->_tbl_key . ', priority');
		$query->from($this->_tbl);

		
		if ($delta < 0)
		{
			$query->where('priority < ' . (int) $this->priority);
			$query->order('priority DESC');
		}
		
		elseif ($delta > 0)
		{
			$query->where('priority > ' . (int) $this->priority);
			$query->order('priority ASC');
		}

		
		if ($where)
		{
			$query->where($where);
		}

		
		$this->_db->setQuery($query, 0, 1);
		$row = $this->_db->loadObject();

		
		if (!empty($row))
		{
			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('priority = ' . (int) $row->priority);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('priority = ' . (int) $this->priority);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			
			$this->priority = $row->priority;
		}
		else
		{
			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('priority = ' . (int) $this->priority);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}
		}

		return true;
	}

	
	public function reBLVOrder($where = '')
	{
		
		if (!property_exists($this, 'backend_list_view_ordering'))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_BLV_ORDERING', get_class($this)));
			$this->setError($e);

			return false;
		}

		
		$k = $this->_tbl_key;

		
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key . ', backend_list_view_ordering');
		$query->from($this->_tbl);
		$query->where('backend_list_view_ordering >= 0');
		$query->order('backend_list_view_ordering');
		
		if ($where)
		{
			$query->where($where);
		}
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBLVORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		foreach ($rows AS $i => $row)
		{
			
			if ($row->backend_list_view_ordering >= 0)
			{
				
				if ($row->backend_list_view_ordering != $i + 1)
				{
					
					$query = $this->_db->getQuery(true);
					$query->update($this->_tbl);
					$query->set('backend_list_view_ordering = ' . ($i + 1));
					$query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$k));
					$this->_db->setQuery($query);

					
					if (!$this->_db->execute())
					{
						$e = new JException(
							JText::sprintf('JLIB_DATABASE_ERROR_REBLVORDER_UPDATE_ROW_FAILED', get_class($this), $i, $this->_db->getErrorMsg())
						);
						$this->setError($e);

						return false;
					}
				}
			}
		}

		return true;
	}

	
	public function moveBLVOrdering($delta, $where = '')
	{
		
		if (!property_exists($this, 'backend_list_view_ordering'))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_BLV_ORDREING', get_class($this)));
			$this->setError($e);

			return false;
		}

		
		if (empty($delta))
		{
			return true;
		}

		
		$k     = $this->_tbl_key;
		$row   = null;
		$query = $this->_db->getQuery(true);

		
		$query->select($this->_tbl_key . ', backend_list_view_ordering');
		$query->from($this->_tbl);

		
		if ($delta < 0)
		{
			$query->where('backend_list_view_ordering < ' . (int) $this->backend_list_view_ordering);
			$query->order('backend_list_view_ordering DESC');
		}
		
		elseif ($delta > 0)
		{
			$query->where('backend_list_view_ordering > ' . (int) $this->backend_list_view_ordering);
			$query->order('backend_list_view_ordering ASC');
		}

		
		if ($where)
		{
			$query->where($where);
		}

		
		$this->_db->setQuery($query, 0, 1);
		$row = $this->_db->loadObject();

		
		if (!empty($row))
		{
			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('backend_list_view_ordering = ' . (int) $row->backend_list_view_ordering);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('backend_list_view_ordering = ' . (int) $this->backend_list_view_ordering);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			
			$this->backend_list_view_ordering = $row->backend_list_view_ordering;
		}
		else
		{
			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('backend_list_view_ordering = ' . (int) $this->backend_list_view_ordering);
			$query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$k));
			$this->_db->setQuery($query);

			
			if (!$this->_db->execute())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}
		}

		return true;
	}

	
	public function delete($pk = null)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		$this->load($pk);

		$pk = (is_null($pk)) ? $this->id : $pk;
		$db = JFactory::getDbo();

		
		$fieldObj = JUDownloadFrontHelperField::getField($pk);
		$fieldObj->onDelete(true);

		
		$query = "DELETE FROM #__judownload_fields_ordering WHERE field_id = $pk";
		$db->setQuery($query);
		$db->execute();

		
		$query = "DELETE FROM #__judownload_fields_values WHERE field_id = $pk";
		$db->setQuery($query);
		$db->execute();

		return parent::delete($pk);
	}

	
	public function check()
	{
		if(!$this->id)
		{
			return false;
		}
		
		if (trim($this->caption) == '')
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_TITLE_MUST_NOT_BE_EMPTY'));

			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->caption;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		
		
		if (!empty($this->metakeyword))
		{
			
			$bad_characters = array("\n", "\r", "\"", "<", ">"); 
			$after_clean    = JString::str_ireplace($bad_characters, "", $this->metakeyword); 
			$keys           = explode(',', $after_clean); 
			$clean_keys     = array();

			foreach ($keys AS $key)
			{
				if (trim($key))
				{
					
					$clean_keys[] = trim($key);
				}
			}
			$this->metakeyword = implode(", ", $clean_keys); 
		}

		return true;

	}

	
	public function store($updateNulls = false)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		
		$table = JTable::getInstance('Field', 'JUDownloadTable');
		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_FIELD_ALIAS_MUST_BE_UNIQUE'));

			return false;
		}

		return parent::store($updateNulls);
	}
}
