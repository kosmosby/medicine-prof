<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

abstract class ComDocmanModelAbstract extends KModelDatabase
{
    /**
     * @var ComDocmanModelPages
     */
    protected static $_pages_model;

    /**
     * A key/value cache of different page sets
     *
     * @var array
     */
    protected static $_page_cache = array();

    /**
     * A key/value cache of row itemids
     *
     * @var array
     */
    protected static $_itemid_cache = array();

    protected $_page_conditions = array();

    /**
     * Constructor
     *
     * @param KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('page', 'raw')
            ->insert('current_user', 'int')
        ;
    }

    public function getPagesModel()
    {
        if (!self::$_pages_model) {
            self::$_pages_model = $this->getObject('com://admin/docman.model.pages');

            $view = $this->getObject('request')->query->view;
            if (JFactory::getApplication()->isAdmin() || $view === 'doclink' || $view === 'documents') {
                self::$_pages_model->language('all');
            }
        }

        return self::$_pages_model;
    }

    /**
     * Returns all component pages
     *
     * @return array
     */
    protected function _getPages()
    {
        if (!isset(self::$_page_cache['all']))
        {
            self::$_page_cache['all'] = $this->getPagesModel()->reset()
                    ->view(array('list', 'userlist', 'filteredlist', 'document'))->fetch();
        }

        return self::$_page_cache['all'];
    }

    /**
     * Returns pages that point to the list view with a root category selected
     *
     * @return array
     */
    protected function _getCategoryPages()
    {
        if (!isset(self::$_page_cache['category']))
        {
            $pages = $this->getPagesModel()->reset()->view('list')->fetch();

            foreach ($pages as $page)
            {
                if (empty($page->query['slug'])) {
                    $pages->remove($page);
                }
            }

            self::$_page_cache['category'] = $pages;
        }

        return self::$_page_cache['category'];
    }

    /**
     * Returns pages that point to the list view without a root category
     *
     * @return array
     */
    protected function _getCategoriesPages()
    {
        if (!isset(self::$_page_cache['categories']))
        {
            $pages = $this->getPagesModel()->reset()->view('list')->fetch();

            foreach ($pages as $page) {
                if (!empty($page->query['slug'])) {
                    $pages->remove($page);
                }
            }

            self::$_page_cache['categories'] = $pages;
        }

        return self::$_page_cache['categories'];
    }

    /**
     * Returns pages that point to a document view
     *
     * @return array
     */
    protected function _getDocumentPages()
    {
        if (!isset(self::$_page_cache['document'])) {
            self::$_page_cache['document'] = $this->getPagesModel()->reset()->view('document')->fetch();
        }

        return self::$_page_cache['document'];
    }

    /**
     * Returns pages that point to a filtered list view
     *
     * @return array
     */
    protected function _getFilteredlistPages()
    {
        if (!isset(self::$_page_cache['filteredlist'])) {
            self::$_page_cache['filteredlist'] = $this->getPagesModel()->reset()->view('filteredlist')->fetch();
        }

        return self::$_page_cache['filteredlist'];
    }

    /**
     * Returns pages that point to a user list view
     *
     * @return array
     */
    protected function _getUserlistPages()
    {
        if (!isset(self::$_page_cache['userlist'])) {
            self::$_page_cache['userlist'] = $this->getPagesModel()->reset()->view('userlist')->fetch();
        }

        return self::$_page_cache['userlist'];
    }

    /**
     * Finds the first available page for an entity
     *
     * If $entity is a document, it will look for a document page first before looking at list view pages.
     *
     * @param KModelEntityInterface $entity
     * @return int Page id
     */
    public function findPage(KModelEntityInterface $entity)
    {
        // Can't use $entity->getHandle() here as it can return the same ID for different entity types
        $hash = spl_object_hash($entity);

        if (!isset(self::$_itemid_cache[$hash]))
        {
            $pages  = $this->findPages($entity, true);
            $itemid = empty($pages) ? 0 : $pages[0];

            self::$_itemid_cache[$hash] = $itemid;
        }

        return self::$_itemid_cache[$hash];
    }

    /**
     * Finds all pages that can link to an entity
     *
     * @param KModelEntityInterface $entity
     * @param bool                  $find_one Returns the first available page if true
     * @return array                An array of page IDs
     */
    public function findPages(KModelEntityInterface $entity, $find_one = false)
    {
        $state = $this->getState();
        $pages = array();
        $key   = $entity->docman_category_id ? $entity->docman_category_id : $entity->id;

        if ($entity instanceof ComDocmanModelEntityDocument)
        {
            $document_pages = $this->_getDocumentPages();
            foreach ($document_pages as $page)
            {
                if (!empty($state->page) && !in_array($page->id, (array) $state->page)) {
                    continue;
                }

                if (isset($page->query['slug']) && $entity->slug === $page->query['slug']) {
                    $pages[] = $page->id;

                    if ($find_one) {
                        return $pages;
                    }
                }
            }
        }

        $category_pages = $this->_getCategoryPages();
        foreach ($category_pages as $page)
        {
            if (!empty($state->page) && !in_array($page->id, (array) $state->page)) {
                continue;
            }

            if (in_array($key, $page->children)) {
                $pages[] = $page->id;

                if ($find_one) {
                    return $pages;
                }
            }
        }

        if ($entity instanceof ComDocmanModelEntityDocument)
        {
            $documents_pages = $this->_getFilteredlistPages();
            foreach ($documents_pages as $page)
            {
                if (!empty($state->page) && !in_array($page->id, (array) $state->page)) {
                    continue;
                }

                $categories = (array) $page->params->get('category');
                $created_by = (array) $page->params->get('created_by');

                if ((empty($categories) || in_array($key, $categories))
                    && (empty($created_by) || in_array($entity->created_by, $created_by))
                ) {
                    $pages[] = $page->id;

                    if ($find_one) {
                        return $pages;
                    }
                }
            }
        }

        foreach ($this->_getCategoriesPages() as $page)
        {
            if (!empty($state->page) && !in_array($page->id, (array) $state->page)) {
                continue;
            }

            $pages[] = $page->id;

            if ($find_one) {
                return $pages;
            }
        }

        $user_id = $this->getObject('user')->getId();
        if ($user_id)
        {
            foreach ($this->_getUserlistPages() as $page)
            {
                if (!empty($state->page) && !in_array($page->id, (array) $state->page)) {
                    continue;
                }

                if ($entity instanceof ComDocmanModelEntityCategory || $entity->created_by == $user_id)
                {
                    if (!$page->children || in_array($key, $page->children)) {
                        $pages[] = $page->id;

                        if ($find_one) {
                            return $pages;
                        }
                    }
                }
            }
        }

        return $pages;
    }

    protected function _afterReset(KModelContextInterface $context)
    {
        $modified = (array) KObjectConfig::unbox($context->modified);
        if (in_array('page', $modified))
        {
            $state = $this->getState();

            if ($state->page === 'all') {
                $state->page = array_keys($this->_getPages()->toArray());
            }

            $this->_page_conditions = $this->_getPageConditions($state->page);
        }
    }

    /**
     * Page conditions getter.
     *
     * @param $pages int|array Page IDs
     *
     * @return array An array containing page conditions.
     */
    protected function _getPageConditions($pages)
    {
        $pages      = (array) KObjectConfig::unbox($pages);
        $conditions = array();
        $categories = array();

        $all_pages  = $this->_getPages();

        if (empty($pages) || !array_intersect($pages, array_keys($all_pages->toArray())))
        {
            // No page exists, set a condition that is impossible to meet
            $conditions[] = array('categories', array(-1));
        }

        foreach ($pages as $id)
        {
            $page = $all_pages->find($id);

            if (empty($page)) {
                continue;
            }

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
            elseif ($page->query['view'] === 'userlist')
            {
                $conditions[] = array('documents', array(
                    'categories' => $page->children,
                    'created_by' => $this->getObject('user')->getId()
                ));
            }
            elseif ($page->query['view'] === 'filteredlist')
            {
                $created_by = $page->params->get('created_by');
                $category   = $page->params->get('category');

                if (empty($created_by) && empty($category))
                {
                    $categories = array();
                    $conditions = array();
                    break;
                }

                $conditions[] = array('documents', array(
                    'created_by' => $created_by,
                    'categories' => $category
                ));
            }
            elseif ($page->query['view'] === 'document')
            {
                $conditions[] = array('document', array(
                    'slug' => $page->query['slug']
                ));
            }
        }

        if ($categories) {
            $conditions[] = array('categories', array_unique($categories));
        }

        return $conditions;
    }
    /**
     * Get a list of items which represents a table rowset
     *
     * Overridden to add itemid to the returned results
     */
    protected function _actionFetch(KModelContext $context)
    {
        $result = parent::_actionFetch($context);

        if ($result && $this->getState()->page)
        {
            foreach ($result as $item) {
                $item->itemid = $this->findPage($item);
            }
        }

        return $result;
    }
}
