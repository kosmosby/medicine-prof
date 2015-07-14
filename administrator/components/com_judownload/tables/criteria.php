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


class JUDownloadTableCriteria extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_criterias', 'id', $db);
	}

	
	public function delete($pk = null)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (parent::delete($pk))
		{
			$db = JFactory::getDbo();

			
			$query = "DELETE FROM #__judownload_criterias_values WHERE criteria_id = $pk";
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		return false;
	}

	
	public function required($pks = null, $state = 1, $userId = 0)
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
		$query->set('required = ' . (int) $state);

		
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
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REQUIRED_FAILED', get_class($this), $this->_db->getErrorMsg()));
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
}
