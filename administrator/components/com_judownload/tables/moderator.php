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


class JUDownloadTableModerator extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_moderators', 'id', $db);
	}

	
	public function check()
	{
		
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		return parent::check();
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

		return 'com_judownload.moderator.' . (int) $this->$k;
	}

	
	protected function _getAssetTitle()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('username');
		$query->from('#__users');
		$query->where('id = ' . $this->user_id);
		$db->setQuery($query);
		$username = $db->loadResult();

		return $username;
	}

	
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		
		$assetId = null;

		$db = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__assets');
		$query->where('name = "com_judownload"');

		
		$db->setQuery($query);
		if ($result = $db->loadResult())
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
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (parent::delete($pk))
		{
			$db = JFactory::getDbo();
			
			$query = "DELETE FROM #__judownload_moderators_xref WHERE mod_id=" . $db->quote($pk);
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		return false;
	}

	
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			
			$this->modified    = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			
			
			if (!intval($this->created))
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}

}
