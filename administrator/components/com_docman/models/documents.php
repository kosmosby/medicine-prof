<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelDocuments extends ComDocmanModelDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state
            ->insert('access', 'int')
            ->insert('access_raw', 'int')
            ->insert('category', 'int')
            ->insert('category_children', 'boolean')
            ->insert('created_by', 'int')
            ->insert('enabled', 'int')
            ->insert('status', 'cmd')
            ->insert('storage_type', 'identifier')
            ->insert('storage_path', 'com://admin/files.filter.path')
            ->insert('search_path', 'com://admin/files.filter.path')
            ->insert('search_by', 'string', 'exact')
            ->insert('search_date', 'date')
            ->insert('day_range', 'int')
            ;
    }

    protected function _buildQueryColumns(KDatabaseQuery $query)
    {
        $query->select('c.title AS category_title')
            ->select('c.slug AS category_slug')
            ->select('CONCAT_WS(\'-\', tbl.docman_document_id, tbl.slug) AS alias')
            ->select('tbl.access AS access_raw')
            ->select('(CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) AS access')
            ->select('creator.name AS created_by_name')
            ->select('viewlevel.title AS access_title')
            ->select('IF(tbl.publish_on = 0, tbl.created_on, tbl.publish_on) AS publish_date')
            ;

        parent::_buildQueryColumns($query);
    }

    protected function _buildQueryJoins(KDatabaseQuery $query)
    {
        $query->join('LEFT', 'docman_categories AS c', 'tbl.docman_category_id = c.docman_category_id')
            ->join('LEFT', 'users AS creator', 'tbl.created_by = creator.id')
            ->join('LEFT', 'users AS modifier', 'tbl.modified_by = modifier.id')
            ->join('LEFT', 'viewlevels AS viewlevel', '(CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) = viewlevel.id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(KDatabaseQuery $query)
    {
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

                if ($condition[0] === 'documents')
                {
                    $q = array();

                    if (!empty($condition[1]['categories']))
                    {
                        $cats    = array_map('intval', (array) $condition[1]['categories']);
                        $q[] = sprintf('tbl.docman_category_id IN (%s)', implode(', ', $cats));
                    }

                    if (!empty($condition[1]['created_by']))
                    {
                        $created_by = array_map('intval', (array) $condition[1]['created_by']);
                        $q[]    = sprintf('tbl.created_by IN (%s)', implode(', ', $created_by));
                    }

                    if ($q) {
                        $where[] = '('.implode(' AND ', $q).')';
                    }
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

        $this->_buildQuerySearchKeyword($query);

        parent::_buildQueryWhere($query);

        $categories = (array) $state->category;
        if ($categories)
        {
            $include_children = $state->category_children;

            if ($include_children)
            {
                $query->join('inner', 'docman_category_relations AS r', 'r.descendant_id = tbl.docman_category_id')
                    ->where('r.ancestor_id', 'IN', $categories)
                    ->group('tbl.docman_document_id');
            }
            else {
                $query->where('tbl.docman_category_id','IN', $categories);
            }
        }

        if (is_numeric($state->enabled))
        {
            $user_enabled_clause = '';
            // Logged in users see their documents regardless of the access level
            if ($state->current_user) {
                $user_enabled_clause = sprintf('(tbl.created_by = %d) OR', $state->current_user);
            }

            $enabled = implode(', ', (array) $state->enabled);
            $query->where[] = array(
                'property' => '',
                'condition' => sprintf('AND (%s (tbl.enabled IN (%d)))', $user_enabled_clause, $enabled)
            );
        }

        if ($state->search_date || $state->day_range) {
            $date = $state->search_date ? "'".$state->search_date."'" : 'NOW()';
            if ($state->day_range) {
                $query->where[] = array(
                    'property' => '',
                    'condition' => 'AND tbl.created_on BETWEEN '.sprintf('DATE_SUB(%1$s, INTERVAL %2$d DAY) AND DATE_ADD(%1$s, INTERVAL %2$d DAY)', $date, $state->day_range
                ));
            }
        }

        if ($state->status === 'published') {
            $user_status_clause = '';
            // Logged in users see their documents regardless of the published status
            if ($state->current_user) {
                $user_status_clause = sprintf('(tbl.created_by = %d) OR', $state->current_user);
            }

            $now = JFactory::getDate()->toSql();

            $query->where[] = array('property' => '', 'condition' => sprintf('AND (%s (tbl.publish_on = 0 OR tbl.publish_on <= \'%s\'))', $user_status_clause, $now));
            $query->where[] = array('property' => '', 'condition' => sprintf('AND (%s (tbl.unpublish_on = 0 OR tbl.unpublish_on >= \'%s\'))', $user_status_clause, $now));
        } elseif ($state->status === 'pending') {
            $now = JFactory::getDate()->toSql();
            $query->where[] = array('property' => '', 'condition' => 'AND '.sprintf('(tbl.publish_on <> 0 AND tbl.publish_on >= \'%s\')', $now));
        } elseif ($state->status === 'expired') {
            $now = JFactory::getDate()->toSql();
            $query->where[] = array('property' => '', 'condition' => 'AND '.sprintf('(tbl.unpublish_on <> 0 AND tbl.unpublish_on <= \'%s\')', $now));
        }

        if ($state->access)
        {
            $user_clause = '';
            // Logged in users see their documents regardless of the access level
            if ($state->current_user) {
                $user_clause = sprintf('(tbl.created_by = %d) OR', $state->current_user);
            }

            $access = implode(', ', (array) $state->access);
            $query->where[] = array(
                'property' => '',
                'condition' => sprintf('AND (%s ((CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) IN (%s)))', $user_clause, $access)
            );

            $query->where[] = array(
                'property' => '',
                'condition' => sprintf('AND (%s (c.access IN (%s)))', $user_clause, $access)
            );
        }

        if ($state->created_by) {
            $query->where('tbl.created_by', 'IN', $state->created_by);
        }

        if ($state->storage_type) {
            $query->where('tbl.storage_type','IN', $state->storage_type);
        }

        if ($state->storage_path) {
            $query->where('tbl.storage_path','IN', $state->storage_path);
        }

        if ($state->search_path !== null) {
            if ($state->search_path === '') {
                $operation = 'NOT LIKE';
                $path = "%/%";
            } else {
                $operation = 'LIKE';
                $path = $state->search_path;
            }
            $query->where('tbl.storage_path', $operation, $path);
        }
    }

    protected function _buildQuerySearchKeyword(KDatabaseQuery $query)
    {
        $state = $this->_state;

        $search = $state->search;

        if (!empty($search)) {
            $search_strings = array();

            switch ($state->search_by) {
                case 'exact':
                    $operation = 'LIKE';
                    $search_strings[] = '%'.$search.'%';
                break;
                case 'any':
                    $operation = 'RLIKE';
                    $search_strings[] = implode('|', explode(' ', $search));
                break;
                case 'all':
                    $operation = 'LIKE';
                    foreach (explode(' ', $search) as $keyword) {
                        $keyword = $this->getTable()->getDatabase()->quoteValue('%'.$keyword.'%');
                        $query->where[] = array(
                            'property' => '',
                            'condition' => 'AND '.sprintf('(tbl.title LIKE %1$s OR tbl.description LIKE %1$s)', $keyword)
                        );
                    }
                break;
            }

            if (count($search_strings))
            {
                // Don't laugh! This is used instead of paranthesis for OR blocks
                // since query builder does not support them for now
                $query->where('1 = 2');

                foreach ($search_strings as $where) {
                    $this->_buildQuerySearchWhere($query, $where, $operation);
                }

                // And this closes the OR block :)
                $query->where('2 = 2');
            }
        }
    }

    protected function _buildQuerySearchWhere(KDatabaseQuery $query, $search, $operation)
    {
        $query->where('tbl.title', $operation,  $search, 'OR');
        $query->where('tbl.description', $operation,  $search, 'OR');
    }
}
