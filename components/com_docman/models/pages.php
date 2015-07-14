<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelPages extends KModelAbstract
{
    /**
     * Pages pointing to this component
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
     * @param KConfig $config Configuration options
     */
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state
            ->insert('id', 'int', null, true)
            ->insert('alias', 'cmd', null, true)
            ->insert('view', 'cmd');
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
            $component	= JComponentHelper::getComponent('com_'.$this->getIdentifier()->package);
            $items		= JApplication::getInstance('site')->getMenu()->getItems('component_id', $component->id);

            foreach ($items as $item)
            {
                $item = clone $item;
                $item->children = array();

                self::$_pages[$item->id] = $item;
            }

            foreach (self::$_pages as &$item)
            {
                // Assign categories and their children to pages
                if ($item->query['view'] === 'list') {
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
            if (!in_array($page->query['view'], $value)) {
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
            $table = clone $this->getService('com://admin/docman.database.table.categories');
            $query = $table->getDatabase()->getQuery();

            // Gather the category slugs of active pages
            $category_slugs = array();
            $pages          = $this->_getPages();
            foreach ($pages as $page)
            {
                if ($page->query['view'] === 'list') {
                    $category_slugs[] = $page->query['slug'];
                }
            }

            // Get a list of categories and their children
            if ($category_slugs)
            {
                $query->select('tbl.slug, r.ancestor_id, r.descendant_id')
                    ->from($table->getRelationTable().' AS r')
                    ->join('left', $table->getName().' AS tbl', 'tbl.docman_category_id = r.ancestor_id')
                    ->where('tbl.slug', 'IN', $category_slugs);

                self::$_categories = $table->getDatabase()->select($query, KDatabase::FETCH_OBJECT_LIST);
            }
            else self::$_categories = array();
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
                $return[] = (int) $category->descendant_id;
            }
        }

        return $return;
    }

    /**
     * Method to get a item
     *
     * @return object
     */
    public function getItem()
    {
        return array_shift($this->getList());
    }

    /**
     * Get a list of items
     *
     * @return object
     */
    public function getList()
    {
        $pages = $this->_getPages();
        $state = $this->getState();

        if ($state->view) {
            $this->_filterPagesByView($pages, (array) $state->view);
        }

        if ($state->id) {
            $this->_filterPages($pages, 'id', (array) $state->id);
        }

        if ($state->alias) {
            $this->_filterPages($pages, 'alias', (array) $state->alias);
        }

        return $pages;
    }

    /**
     * Get the total amount of items
     *
     * @return int
     */
    public function getTotal()
    {
        return count($this->_getPages());
    }
}
