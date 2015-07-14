<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelDocuments extends ComDocmanModelAbstract
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('access', 'int')
            ->insert('access_raw', 'int')
            ->insert('category', 'int')
            ->insert('category_children', 'boolean')
            ->insert('created_by', 'int')
            ->insert('enabled', 'int')
            ->insert('status', 'cmd')
            ->insert('search', 'string')
            ->insert('storage_type', 'identifier')
            ->insert('storage_path', 'com:files.filter.path')
            ->insert('search_path', 'com:files.filter.path')
            ->insert('search_by', 'string', 'exact')
            ->insert('search_date', 'date')
            ->insert('image', 'com:files.filter.path')
            ->insert('day_range', 'int');
    }

    protected function _buildQueryColumns(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns('c.title AS category_title')
            ->columns('c.slug AS category_slug')
            ->columns('c.access AS category_access')
            ->columns('c.enabled AS category_enabled')
            ->columns('CONCAT_WS(\'-\', tbl.docman_document_id, tbl.slug) AS alias')
            ->columns('tbl.access AS access_raw')
            ->columns('(CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) AS access')
            ->columns('viewlevel.title AS access_title')
            ->columns('IF(tbl.publish_on = 0, tbl.created_on, tbl.publish_on) AS publish_date')
            ->columns('GREATEST(tbl.created_on, tbl.modified_on) AS touched_on')
            ;
    }

    protected function _buildQueryJoins(KDatabaseQueryInterface $query)
    {
    	$query->join(array('c' => 'docman_categories'), 'tbl.docman_category_id = c.docman_category_id')
              ->join(array('viewlevel' => 'viewlevels'), '(CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) = viewlevel.id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        $state = $this->getState();

        if ($this->_page_conditions)
        {
            $categories = array();
            $where = array();
            $i = 0;

            foreach ($this->_page_conditions as $condition)
            {
                if ($condition[0] === 'categories') {
                    $categories = array_merge($categories, (array) $condition[1]);
                }

                if ($condition[0] === 'documents')
                {
                    $str = '';

                    if (!empty($condition[1]['categories']))
                    {
                        $str[] = "tbl.docman_category_id IN :category_id$i";
                        $query->bind(array("category_id$i" => (array) $condition[1]['categories']));
                    }

                    if (!empty($condition[1]['created_by'])
                        || $condition[1]['created_by'] === 0 || $condition[1]['created_by'] === '0')
                    {
                        $str[] = "tbl.created_by IN :created_by$i";
                        $query->bind(array("created_by$i" => (array) $condition[1]['created_by']));
                    }

                    if ($str) {
                        $where[] = '('.implode(' AND ', $str).')';
                    }
                }

                if ($condition[0] === 'document')
                {
                    $where[] = "(tbl.slug = :slug$i)";
                    $query->bind(array("slug$i" => $condition[1]['slug']));
                }

                $i++;
            }

            if ($categories)
            {
                $where[] = '(tbl.docman_category_id IN :page_categories)';
                $query->bind(array('page_categories' => (array) $categories));
            }

            if ($where) {
                $where = '('.implode(' OR ', $where).')';

                $query->where($where);
            }
        }

        $this->_buildQuerySearchKeyword($query);

        parent::_buildQueryWhere($query);

        $categories = (array) $state->category;
        if ($categories)
        {
            $include_children = $state->category_children;

            if ($include_children)
            {
                $query->join(array('r' => 'docman_category_relations'), 'r.descendant_id = tbl.docman_category_id')
                    ->where('r.ancestor_id IN :include_children_categories')
                    ->bind(array('include_children_categories' => $categories))
                    ->group('tbl.docman_document_id');
            }
            else {
                $query->where('tbl.docman_category_id IN :include_children_categories')
                    ->bind(array('include_children_categories' => $categories));
            }
        }

        if (is_numeric($state->enabled))
        {
            $user_enabled_clause = '';
            // Logged in users see their documents regardless of the access level
            if ($state->current_user) {
                $user_enabled_clause = 'tbl.created_by = :current_user OR';
            }

            $query->where(sprintf('(%s tbl.enabled IN :enabled)', $user_enabled_clause))->bind(array(
                'enabled' => (array) $state->enabled,
                'current_user' => $state->current_user
            ));
        }

        if ($state->search_date || $state->day_range) 
        {
        	$date      = $state->search_date ? ':date' : 'NOW()';
            $date_bind = $state->search_date ? $state->search_date : null;
            if ($state->day_range) {
            	$query->where("(tbl.created_on BETWEEN DATE_SUB($date, INTERVAL :days DAY) AND DATE_ADD($date, INTERVAL :days DAY))")
            		  ->bind(array('date' => $date_bind, 'days' => $state->day_range));
            }
        }

        if ($state->status === 'published') 
        {
            $user_status_clause = '';
            // Logged in users see their documents regardless of the published status
            if ($state->current_user) {
                $user_status_clause = 'tbl.created_by = :current_user OR';
            }

            $now = JFactory::getDate()->toSql();

            $query->where(sprintf('(%s (tbl.publish_on = 0 OR tbl.publish_on <= :publish_date))', $user_status_clause))
              	  ->where(sprintf('(%s (tbl.unpublish_on = 0 OR tbl.unpublish_on >= :publish_date))', $user_status_clause))
            	  ->bind(array(
                      'publish_date' => $now,
                      'current_user' => $state->current_user
                  ));
        } 
        elseif ($state->status === 'pending') 
        {
            $now = JFactory::getDate()->toSql();

            $query->where('(tbl.publish_on <> 0 AND tbl.publish_on >= :publish_date)')
	            ->bind(array('publish_date' => $now));
        } 
        elseif ($state->status === 'expired') 
        {
            $now = JFactory::getDate()->toSql();

            $query->where('(tbl.unpublish_on <> 0 AND tbl.unpublish_on <= :publish_date)')
	            ->bind(array('publish_date' => $now));
        }

        if ($state->access)
        {
            $user_access_clause = '';
            // Logged in users see their documents regardless of the published status
            if ($state->current_user) {
                $user_access_clause = 'tbl.created_by = :current_user OR';
            }

            $query->where(sprintf('(%s c.access IN :access)', $user_access_clause))
                  ->where(sprintf('(%s (CASE tbl.access WHEN -1 THEN COALESCE(c.access, 1) ELSE tbl.access END) IN :access)', $user_access_clause))
                  ->bind(array(
                        'access' => (array) $state->access,
                        'current_user' => $state->current_user
                    ));
        }

        if (is_numeric($state->created_by) || !empty($state->created_by)) {
            $query->where('tbl.created_by IN :created_by')->bind(array('created_by' => (array) $state->created_by));
        }

        if ($state->storage_type) {
            $query->where('tbl.storage_type IN :storage_type')->bind(array('storage_type' => (array) $state->storage_type));
        }

        if ($image = $state->image) {
            $query->where('tbl.image IN :image')->bind(array('image' => (array) $image));
        }

        if ($state->storage_path) {
            $query->where('tbl.storage_path IN :storage_path')->bind(array('storage_path' => (array) $state->storage_path));
        }

        if ($state->search_path !== null)
        {
            if ($state->search_path === '')
            {
                $operation = 'NOT LIKE';
                $path = "%/%";
            }
            else
            {
                $operation = 'LIKE';
                $path = $state->search_path;
            }

            $query->where('tbl.storage_path '.$operation. ' :path')->bind(array('path' => $path));
        }
    }

    protected function _buildQuerySearchKeyword(KDatabaseQueryInterface $query)
    {
        $state  = $this->getState();
        $search = $state->search;

        if (!empty($search)) 
        {
            switch ($state->search_by) 
            {
                case 'exact':
                    $query->where('(tbl.title LIKE :search OR tbl.description LIKE :search)')
                        ->bind(array('search' => '%'.$search.'%'));

                    break;
                case 'any':
                    $query->where('(tbl.title RLIKE :search OR tbl.description RLIKE :search)')
                        ->bind(array('search' => implode('|', explode(' ', $search))));

                    break;
                case 'all':
                    $i = 0;
                    foreach (explode(' ', $search) as $keyword) {
                        $query->where("(tbl.title LIKE :search$i OR tbl.description LIKE :search$i)")
                            ->bind(array("search$i" => '%'.$keyword.'%'));
                        $i++;
                    }

                    break;
            }
        }
    }
}
