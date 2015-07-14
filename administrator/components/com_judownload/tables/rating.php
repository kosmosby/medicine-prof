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


class JUDownloadTableRating extends JTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_rating', 'id', $db);
	}

	
	public function delete($pk = null)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (parent::delete($pk))
		{
			
			$db    = JFactory::getDbo();
			$query = "DELETE FROM #__judownload_criterias_values WHERE rating_id = $pk";
			$db->setQuery($query);
			$db->execute();

			
			
			JUDownloadHelper::deleteLogs('rating', $pk);

			return true;
		}

		return false;
	}
}
