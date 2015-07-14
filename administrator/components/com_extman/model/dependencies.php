<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanModelDependencies extends KModelDatabase
{
	protected function _buildQueryWhere(KDatabaseQueryInterface $query)
	{
		$state = $this->getState();

		if ($state->extman_extension_id) {
			$query->where('tbl.extman_extension_id = :extension_id')->bind(array('extension_id' => $state->extman_extension_id));
		}

		if ($state->dependent_id) {
			$query->where('tbl.dependent_id = :dependent_id')->bind(array('dependent_id' => $state->dependent_id));
		}
	}
}