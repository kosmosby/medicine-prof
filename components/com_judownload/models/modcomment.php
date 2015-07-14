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

require_once JPATH_ADMINISTRATOR . '/components/com_judownload/models/comment.php';

class JUDownloadModelModComment extends JUDownloadModelComment
{
	
	public function checkin($pks = array())
	{
		$pks   = (array) $pks;
		$table = $this->getTable();
		$count = 0;

		if (empty($pks))
		{
			$pks = array((int) $this->getState($this->getName() . '.id'));
		}

		
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				if ($table->checked_out > 0)
				{
					if (JUDownloadFrontHelperPermission::canCheckInComment($pk))
					{
						
						if (!$table->checkin($pk))
						{
							$this->setError($table->getError());

							return false;
						}

						$count++;
					}
					else
					{
						$this->setError(JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_CHECK_IN_COMMENT'));

						return false;
					}
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return $count;
	}
}
