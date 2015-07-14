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



jimport('joomla.database.tablenested');

class JUDownloadTableCategory extends JTableNested
{
	public static $getPath;
	public static $getTree;
	public static $customGetTree;

	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_categories', 'id', $db);
	}

	
	public function getPath($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$getPath[$pk][(int) $diagnostic]))
		{
			self::$getPath[$pk][(int) $diagnostic] = parent::getPath($pk, $diagnostic);
		}

		return self::$getPath[$pk][(int) $diagnostic];
	}

	
	public function getTree($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$getTree[$pk][(int) $diagnostic]))
		{
			self::$getTree[$pk][(int) $diagnostic] = parent::getTree($pk, $diagnostic);
		}

		return self::$getTree[$pk][(int) $diagnostic];
	}

	
	public function customGetTree($pk = null, $diagnostic = false)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$customGetTree[$pk][(int) $diagnostic]))
		{
			
			$query  = $this->_db->getQuery(true);
			$select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
			$query->select($select);
			$query->select('(SELECT a.id FROM #__judownload_categories AS a WHERE n.lft BETWEEN a.lft AND a.rgt
							AND a.level = 1) AS top_cat');
			$query->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p');
			$query->where('n.lft BETWEEN p.lft AND p.rgt');
			$query->where('p.' . $k . ' = ' . (int) $pk);
			$query->order('n.lft');
			$this->_db->setQuery($query);
			$tree = $this->_db->loadObjectList();

			
			if ($this->_db->getErrorNum())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GET_TREE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				$tree = false;
			}

			self::$customGetTree[$pk][(int) $diagnostic] = $tree;
		}

		return self::$customGetTree[$pk][(int) $diagnostic];
	}

	
	public function bind($array, $ignore = array())
	{
		
		if (isset($array['top_category_rules']) && is_array($array['top_category_rules']))
		{
			$rules = new JAccessRules($array['top_category_rules']);
			$this->setRules($rules);
		}
		elseif (isset($array['rules']) && is_array($array['rules']))
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

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_judownload.category.' . (int) $this->$k;
	}

	
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		
		$assetId = null;

		
		if ($this->parent_id > 0)
		{
			
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('asset_id'));
			$query->from($this->_db->quoteName('#__judownload_categories'));
			$query->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

			
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

	
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		
		if ($parentId === null)
		{
			
			$parentId = $this->getRootId();
			if ($parentId === false)
			{
				return false;
			}
		}

		
		if (!isset($this->_cache['rebuild.sql']))
		{
			$query = $this->_db->getQuery(true);
			$query->select($this->_tbl_key . ', alias');
			$query->from($this->_tbl);
			$query->where('parent_id = %d');

			
			if (property_exists($this, 'ordering'))
			{
				$query->order('parent_id, ordering, lft');
			}
			else
			{
				$query->order('parent_id, lft');
			}
			$this->_cache['rebuild.sql'] = (string) $query;
		}

		

		
		$this->_db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));
		$children = $this->_db->loadObjectList();

		
		$rightId = $leftId + 1;

		
		foreach ($children AS $node)
		{
			
			
			
			$rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1);

			
			if ($rightId === false)
			{
				return false;
			}
		}

		
		
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = ' . (int) $leftId);
		$query->set('rgt = ' . (int) $rightId);
		$query->set('level = ' . (int) $level);
		$query->where($this->_tbl_key . ' = ' . (int) $parentId);
		$this->_db->setQuery($query);

		
		if (!$this->_db->execute())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBUILD_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		return $rightId + 1;
	}

	
	public function check()
	{
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_TITLE_MUST_NOT_BE_EMPTY'));

			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '')
		{
			$this->fulltext = '';
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

	
	public function feature($pks = null, $state = 1, $userId = 0)
	{
		
		$k = $this->_tbl_key;

		
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			
			else
			{
				$e = new JException(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				$this->setError($e);

				return false;
			}
		}

		
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('featured = ' . (int) $state);

		
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
		{
			$query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
			$checkin = true;
		}
		else
		{
			$checkin = false;
		}

		
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);

		
		if (!$this->_db->execute())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_FEATURE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			
			foreach ($pks AS $pk)
			{
				$this->checkin($pk);
			}
		}

		
		if (in_array($this->$k, $pks))
		{
			$this->featured = $state;
		}

		$this->setError('');

		return true;
	}


	
	public function store($updateNulls = false)
	{
		
		$categoryTable = JTable::getInstance('Category', 'JUDownloadTable');
		if ($categoryTable->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id)) && ($categoryTable->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::sprintf('COM_JUDOWNLOAD_AN_OTHER_CATEGORY_IN_THE_SAME_PARENT_CATEGORY_HAS_THE_SAME_ALIAS_X', $this->alias));

			return false;
		}

		return parent::store($updateNulls);
	}
}
