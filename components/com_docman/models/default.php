<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelDefault extends ComDefaultModelDefault
{
    /**
     * @var ComDocmanModelPages
     */
    protected static $_pages_model;

    /**
     * Constructor
     *
     * @param KConfig $config Configuration options
     */
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        self::$_pages_model = $this->getService('com://site/docman.model.pages');

        $this->_state
            ->insert('page', 'int')
            ->insert('page_conditions', 'raw', array())
            ->insert('current_user', 'int')
            ->insert('access', 'int')
            ->insert('status', 'cmd')
        ;
    }

    /**
     * Returns all component pages
     *
     * @return array
     */
    protected function _getPages()
    {
        $pages = self::$_pages_model->reset()->getList();

        return $pages;
    }

    /**
     * Returns pages that point to the list view with a root category selected
     *
     * @return array
     */
    protected function _getCategoryPages()
    {
        $pages = self::$_pages_model->reset()->view('list')->getList();

        foreach ($pages as $i => $page) {
            if (empty($page->query['slug'])) {
                unset($pages[$i]);
            }
        }

        return $pages;
    }

    /**
     * Returns pages that point to the list view without a root category
     *
     * @return array
     */
    protected function _getCategoriesPages()
    {
        $pages = self::$_pages_model->reset()->view('list')->getList();

        foreach ($pages as $i => $page) {
            if (!empty($page->query['slug'])) {
                unset($pages[$i]);
            }
        }

        return $pages;
    }

    /**
     * Finds the first available page for a row
     *
     * If the $row is a document, it will look for a document page first
     * before looking at list view pages.
     *
     * @param KDatabaseRowInterface $row
     *
     * @return int
     */
    protected function _findPage(KDatabaseRowInterface $row)
    {
        $itemid = 0;
        $key = $row->docman_category_id ? $row->docman_category_id : $row->id;

        if ($row instanceof ComDocmanDatabaseRowDocument)
        {
            $document_pages = self::$_pages_model->reset()->view('document')->getList();
            foreach ($document_pages as $page)
            {
                if (!empty($this->_state->page) && !in_array($page->id, (array) $this->_state->page)) {
                    continue;
                }

                if (isset($page->query['slug']) && $row->slug === $page->query['slug']) {
                    $itemid = $page->id;
                    break;
                }
            }
        }

        if (!$itemid)
        {
            $category_pages = $this->_getCategoryPages();
            foreach ($category_pages as $page)
            {
                if (!empty($this->_state->page) && !in_array($page->id, (array) $this->_state->page)) {
                    continue;
                }

                if (in_array($key, $page->children)) {
                    $itemid = $page->id;
                    break;
                }
            }
        }

        if (!$itemid && $row instanceof ComDocmanDatabaseRowDocument)
        {
            $documents_pages = self::$_pages_model->reset()->view('filteredlist')->getList();
            foreach ($documents_pages as $page)
            {
                if (!empty($this->_state->page) && !in_array($page->id, (array) $this->_state->page)) {
                    continue;
                }

                $categories = (array) $page->params->get('category');
                $created_by = (array) $page->params->get('created_by');

                if ((empty($categories) || in_array($key, $categories))
                    && (empty($created_by) || in_array($row->created_by, $created_by))
                ) {
                    $itemid = $page->id;
                    break;
                }
            }
        }

        if (!$itemid)
        {
            foreach ($this->_getCategoriesPages() as $page)
            {
                $itemid = $page->id;
                break;
            }
        }

        return $itemid;
    }

    /**
     * Set the model state properties
     *
     * Overloaded to automatically set page_category state if page state is passed
     *
     * @param  string|array|object $property The name of the property, an associative array or an object
     * @param  mixed               $value    The value of the property
     * @return KModelTable
     */
    public function set($property, $value = null)
    {
        $source = null;

        // If the page is passed in an object or array we unset it since
        // it's gonna get set by calling setPageState() method
        if (!empty($property->page))
        {
            $source = $property->page;
            unset($property->page);
        }
        elseif (is_array($property) && !empty($property['page']))
        {
            $source = $property['page'];
            unset($property['page']);
        }
        elseif ($property === 'page')
        {
            $source = $value;
        }

        // We have a page state to handle
        if ($source !== null)
        {
            $this->_setPageState($source);

            // We already set it above in _setPageState
            if ($property === 'page') {
                return $this;
            }
        }

        return parent::set($property, $value);
    }

    /**
     * @param $pages int|array Page IDs
     */
    protected function _setPageState($pages)
    {
        if ($pages === 'all') {
            $pages = array_keys($this->_getPages());
        }

        // Get rid of KConfig
        $pages      = (array) KConfig::unbox($pages);
        $conditions = array();

        if ($pages)
        {
            $all_pages = $this->_getPages();
            $categories = array();

            foreach ($pages as $id)
            {
                if (!isset($all_pages[$id])) {
                    continue;
                }

                $page = $all_pages[$id];

                if ($page->query['view'] === 'list')
                {
                    // If we have a category view to the root category everything is reachable
                    if (empty($page->query['slug']))
                    {
                        $categories = array();
                        $conditions = array();
                        break;
                    }

                    $categories = array_merge($categories, $page->children);
                }
                elseif ($page->query['view'] === 'filteredlist')
                {
                    $conditions[] = array('documents', array(
                        'created_by' => $page->params->get('created_by'),
                        'categories' => $page->params->get('category')
                    ));
                }
            }

            if ($categories) {
                $conditions[] = array('categories', array_unique($categories));
            }
        }

        parent::set('page', $pages);
        parent::set('page_conditions', $conditions);
    }

    /**
     * Method to get a item object which represents a table row
     *
     * Overridden to add itemid to the returned results
     *
     * @return KDatabaseRowInterface
     */
    public function getItem()
    {
        $item = parent::getItem();

        if ($item) {
            $item->itemid = $this->_findPage($item);
        }

        return $item;
    }

    /**
     * Get a list of items which represents a table rowset
     *
     * Overridden to add itemid to the returned results
     *
     * @return KDatabaseRowsetInterface
     */
    public function getList()
    {
        $list = parent::getList();

        if ($list)
        {
            foreach ($list as $item) {
                $item->itemid = $this->_findPage($item);
            }
        }

        return $list;
    }
}
