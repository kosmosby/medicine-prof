<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelCategories extends ComDocmanModelNodes
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state
            ->insert('access', 'int')
            ->insert('enabled', 'int');
    }

    protected function _buildQueryColumns(KDatabaseQuery $query)
    {
        $query->select('viewlevel.title AS access_title')
            ->select('creator.name AS created_by_name');

        parent::_buildQueryColumns($query);
    }

    protected function _buildQueryJoins(KDatabaseQuery $query)
    {
        $query->join('LEFT', 'viewlevels AS viewlevel', 'tbl.access = viewlevel.id')
            ->join('LEFT', 'users AS creator', 'tbl.created_by = creator.id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->_state;

        $query->where('1 = 1');

        if (is_array($state->page_conditions))
        {
            $where = array();
            foreach ($state->page_conditions as $condition)
            {
                if ($condition[0] === 'categories') {
                    $cats = array_map('intval', (array) $condition[1]);
                    $where[] = sprintf('tbl.docman_category_id IN (%s)', implode(', ', $cats));
                }
            }

            if ($where) {
                $where = '('.implode(' OR ', $where).')';

                $query->where[] = array(
                    'property' => '',
                    'condition' => 'AND '.$where
                );
            }
        }

        $query->where('1 = 1');

        if ($state->search) {
            $query->where('tbl.title', 'LIKE', '%'.$state->search.'%');
        }

        if (is_numeric($state->enabled)) {
            $query->where('tbl.enabled','=', $state->enabled);
        }

        if ($state->access)
        {
            $user_clause = '';
            // Logged in users see their categories regardless of the access level
            if ($state->current_user) {
                $user_clause = sprintf('(tbl.created_by = %d) OR', $state->current_user);
            }

            $access = implode(', ', (array) $state->access);
            $query->where[] = array(
                'property' => '',
                'condition' => sprintf('AND (%s tbl.access IN (%s))', $user_clause, $access)
            );
        }
    }
}
