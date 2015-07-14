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

class JUDownloadTableTemplate extends JTableNested
{
	public static $getPath;
	public static $getTree;

	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_templates', 'id', $db);
	}

	public function delete($pk = null, $children = true)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$db = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_template_styles');
		$query->where('template_id =' . $pk);
		$db->setQuery($query);
		$styleIds = $db->loadColumn();
		if ($styleIds)
		{
			$styleTable = JTable::getInstance("Style", "JUDownloadTable");
			foreach ($styleIds AS $styleId)
			{
				if (!$styleTable->delete($styleId))
				{
					return false;
				}
			}
		}

		return parent::delete($pk);
	}

	
	public function getPath($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$getPath[$pk][(int) $diagnostic]))
		{
			
			$select = ($diagnostic) ? 'p.' . $k . ', p.parent_id, p.level, p.lft, p.rgt' : 'p.*';
			$query  = $this->_db->getQuery(true)
				->select('(SELECT folder FROM #__judownload_plugins AS plg WHERE p.plugin_id = plg.id) AS folder')
				->select('(SELECT title FROM #__judownload_plugins AS plg WHERE p.plugin_id = plg.id) AS title')
				->select($select)
				->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
				->where('n.lft BETWEEN p.lft AND p.rgt')
				->where('n.' . $k . ' = ' . (int) $pk)
				->order('p.lft');
			$this->_db->setQuery($query);
			self::$getPath[$pk][(int) $diagnostic] = $this->_db->loadObjectList();
		}

		return self::$getPath[$pk][(int) $diagnostic];
	}

	
	public function getTree($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		
		$select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
		$query  = $this->_db->getQuery(true)
			->select($select)
			->select('(SELECT folder FROM #__judownload_plugins AS plg WHERE n.plugin_id = plg.id) AS folder')
			->select('(SELECT title FROM #__judownload_plugins AS plg WHERE n.plugin_id = plg.id) AS title')
			->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('p.' . $k . ' = ' . (int) $pk)
			->order('n.lft');

		return $this->_db->setQuery($query)->loadObjectList();
	}

}
