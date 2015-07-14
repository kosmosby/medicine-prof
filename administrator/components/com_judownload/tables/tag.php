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


class JUDownloadTableTag extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_tags', 'id', $db);
	}

	
	public function bind($array, $ignore = array())
	{
		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	
	public function check()
	{
		
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

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

		if (trim(str_replace('&nbsp;', '', $this->description)) == '')
		{
			$this->description = '';
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
		
		$table = JTable::getInstance('Tag', 'JUDownloadTable');

		if ($table->load(array('title' => $this->title)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_TAG_TITLE_MUST_BE_UNIQUE'));

			return false;
		}

		
		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_TAG_ALIAS_MUST_BE_UNIQUE'));

			return false;
		}

		return parent::store($updateNulls);
	}

	public function delete($pk = null)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (parent::delete($pk))
		{
			
			$db    = JFactory::getDbo();
			$query = "DELETE FROM #__judownload_tags_xref WHERE tag_id = $pk";
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		return false;
	}
}
