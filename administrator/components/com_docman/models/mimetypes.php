<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelMimetypes extends ComDefaultModelDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state->insert('mimetype', 'string')
            ->insert('extension', 'string');
    }

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->_state;
        if ($state->mimetype) {
            $query->where('mimetype', 'IN', $state->mimetype);
        }

        if ($state->extension) {
            $query->where('extension', 'IN', $state->extension);
        }
    }
}
