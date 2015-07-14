<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelPages extends KModelAbstract
{
    /**
     * Pages pointing to this component
     *
     * @var array
     */
    protected static $_pages = array();

    /**
     * An array of categories that are reachable through a page
     *
     * @var array
     */
    protected static $_categories;

    /**
     * Constructor
     *
     * @param KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('id', 'int', null, true)
            ->insert('alias', 'cmd', null, true)
            ->insert('language', 'cmd', null)
            ->insert('view', 'cmd');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'identity_key'     => 'id'
        ));

        parent::_initialize($config);
    }

    /**
     * Returns an array of component pages.
     *
     * Each page includes a children property that contains a list of all categories reachable by the page
     *
     * @return array
     */
    protected function _getPages()
    {
        if (!self::$_pages)
        {
            $component = JComponentHelper::getComponent('com_' . $this->getIdentifier()->package);

            $attributes = array('component_id');
            $values     = array($component->id);

            if ($this->getState()->language !== null)
            {
                $attributes[] = 'language';

                if ($this->getState()->language === 'all') {
                    $values[] = JFactory::getDbo()->setQuery('SELECT DISTINCT language FROM #__menu')->loadColumn();
                } else {
                    $values[] = $this->getState()->language;
                }
            }

            $items = JApplication::getInstance('site')->getMenu()->getItems($attributes, $values);

            foreach ($items as $item)
            {
                $item           = clone $item;
                $item->children = array();

                if ($item->language === '*') {
                    $item->language = '';
                }

                self::$_pages[$item->id] = $item;
            }

            foreach (self::$_pages as &$item)
            {
                // Assign categories and their children to pages
                if (isset($item->query['view']) && $item->query['view'] === 'list' && isset($item->query['slug'])) {
                    $item->children = $this->_getCategoryChildren($item->query['slug']);
                }
            }
            unset($item);
        }

        return self::$_pages;
    }

    /**
     * Filters pages by view
     *
     * @param array $pages Page list
     * @param array $value Allowed views
     */
    protected function _filterPagesByView(&$pages, array $value)
    {
        foreach ($pages as $i => $page)
        {
            if (!isset($page->query['view']) || !in_array($page->query['view'], $value)) {
                unset($pages[$i]);
            }
        }
    }

    /**
     * Filters pages by a given field
     *
     * @param array  $pages Page list
     * @param string $field Field to filter against
     * @param array  $value Allowed values
     */
    protected function _filterPages(&$pages, $field, array $value)
    {
        foreach ($pages as $i => $page)
        {
            if (!in_array($page->$field, $value)) {
                unset($pages[$i]);
            }
        }
    }

    /**
     * Get a list of categories that are reachable by a list view page
     *
     * @return array
     */
    protected function _getCategories()
    {
        if (self::$_categories === null)
        {
            $table = $this->getObject('com://admin/docman.database.table.categories');
            $query = $this->getObject('lib:database.query.select');

            // Gather the category slugs of active pages
            $category_slugs = array();
            $pages          = $this->_getPages();
            foreach ($pages as $page)
            {
                if (isset($page->query['view']) && $page->query['view'] === 'list' && isset($page->query['slug'])) {
                    $category_slugs[] = $page->query['slug'];
                }
            }

            // Get a list of categories and their children
            if ($category_slugs)
            {
                $query->columns('tbl.slug, r.ancestor_id, r.descendant_id')
                    ->table(array('r' => $table->getBehavior('nestable')->getRelationTable()))
                    ->join(array('tbl' => $table->getName()), 'tbl.docman_category_id = r.ancestor_id')
                    ->where('tbl.slug IN :slug')
                    ->bind(array('slug' => (array)$category_slugs));

                self::$_categories = $table->getAdapter()->select($query, KDatabase::FETCH_OBJECT_LIST);

            } else self::$_categories = array();
        }

        return self::$_categories;
    }

    /**
     * Takes a category slug and returns the IDs of itself and all its children
     *
     * @param string $slug
     *
     * @return array
     */
    protected function _getCategoryChildren($slug)
    {
        $return     = array();
        $categories = $this->_getCategories();

        if (empty($slug)) {
            return $return;
        }

        foreach ($categories as $category)
        {
            if ($category->slug === $slug) {
                $return[] = (int)$category->descendant_id;
            }
        }

        return $return;
    }

    protected function _actionFetch(KModelContext $context)
    {
        $pages = $this->_getPages();
        $state = $this->getState();

        if ($state->view) {
            $this->_filterPagesByView($pages, (array)$state->view);
        }

        if ($state->id) {
            $this->_filterPages($pages, 'id', (array)$state->id);
        }

        if ($state->alias) {
            $this->_filterPages($pages, 'alias', (array)$state->alias);
        }

        foreach ($pages as &$page) {
            $page = get_object_vars($page);
        }

        $options = array(
            'data'         => $pages,
            'identity_key' => $context->getIdentityKey()
        );

        $pages = $this->getObject('com://admin/docman.model.entity.pages', $options);

        return $pages;
    }

    protected function _actionCount(KModelContext $context)
    {
        return count($this->_getPages());
    }
}
