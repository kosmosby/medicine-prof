<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelCategories extends ComDocmanModelAbstract
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('access'    , 'int')
            ->insert('created_by', 'int')
            ->insert('enabled'   , 'int');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'nestable',
                'searchable' => array('columns' => array('title', 'description'))
            )
        ));

        parent::_initialize($config);
    }

    protected function _buildQueryColumns(KDatabaseQueryInterface $query)
    {
        $query->columns(array('access_title' => 'viewlevel.title'));

        parent::_buildQueryColumns($query);
    }

    protected function _buildQueryJoins(KDatabaseQueryInterface $query)
    {
        $query->join(array('viewlevel' => 'viewlevels'), 'tbl.access = viewlevel.id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

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
                    if (!empty($condition[1]['categories']))
                    {
                        $where[] = "tbl.docman_category_id IN :category_id$i";
                        $query->bind(array("category_id$i" => (array) $condition[1]['categories']));
                    }
                }

                $i++;
            }

            if ($categories) {
                $where[] = '(tbl.docman_category_id IN :page_categories)';
                $query->bind(array('page_categories' => (array) $categories));
            }

            if ($where) {
                $where = '('.implode(' OR ', $where).')';

                $query->where($where);
            }
        }

        if (is_numeric($state->enabled)) {
            $query->where('tbl.enabled = :enabled')
                ->bind(array('enabled' => $state->enabled));
        }

        if ($state->created_by)
        {
            $query->where('tbl.created_by IN :created_by')
                ->bind(array('created_by' => (array) $state->created_by));
        }

        if ($state->access)
        {
            $access = (array) $state->access;
            $user_access_clause = '';

            // Logged in users see their documents regardless of the published status
            if ($state->current_user !== null) {
                $access = array_merge($state->access, $this->getUserLevels($state->current_user));
                $user_access_clause = 'tbl.created_by = :current_user OR';
            }

            $query->where(sprintf('(%s tbl.access IN :access)', $user_access_clause))
                ->bind(array(
                    'access' => $access,
                    'current_user' => $state->current_user
                ));
        }
    }

    protected function _getPageConditions($pages)
    {
        $pages = (array) KObjectConfig::unbox($pages);

        $document_pages = array_intersect($pages, array_keys($this->_getDocumentPages()->toArray()));

        // Return an empty list of categories if the model is filtered against single document pages only.
        if (count($document_pages) === count($pages)) {
            $conditions = array(array('categories', array(-1)));
        } else {
            $conditions = parent::_getPageConditions($pages);
        }

        return $conditions;
    }
}
