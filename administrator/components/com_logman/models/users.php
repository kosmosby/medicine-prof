<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanModelUsers extends ComDefaultModelDefault
{
	public function __construct(KConfig $config)
	{
		parent::__construct($config);

        $this->_state
            ->insert('email'      , 'email', null, true)
            ->insert('username'   , 'alnum', null, true);
	}

    protected function _buildQueryWhere(KDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);
        $state = $this->_state;

        if ($state->search) {
            $query->where('tbl.name', 'LIKE', '%' . $state->search . '%')
            ->where('tbl.email', 'LIKE', '%' . $state->search . '%', 'OR');
        }
    }
}