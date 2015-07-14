<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorExecutable extends ComDocmanControllerBehaviorPermissions
{
    public function canAdd()
    {
        $itemid = $this->getRequest()->Itemid;
        $page   = $this->getService('com://site/docman.model.pages')->id($itemid)->getItem();
        $view   = $this->getView()->getName();
        $page_view = $page->query['view'];

        // We only allow these views to have an edit form/add something
        if (!in_array($page_view, array('list', 'document', 'submit'))) {
            return false;
        }

        // User could pass through Joomla menu access checks, so has access
        if ($page->query['view'] === 'submit') {
            return true;
        }

        // If a POST request is made, we need to be sure it's for a document controller
        if (KRequest::method() !== KHttpRequest::GET
            && !in_array($this->getMixer()->getIdentifier()->name, array('document', 'submit'))) {
            return false;
        }

        if ($page_view === 'list')
        {
            // canAdd is being run on a GET request to determine if we should show the add button
            if (KRequest::method() === KHttpRequest::GET)
            {
                // If we are on a certain category make sure user can add something here
                if ($view === 'list' && $this->getRequest()->slug) {
                    $category = $this->getModel()->getItem();

                    return (!$category->isAclable() || $category->canPerform('add'));
                }

                $authorized_categories = $this->getAuthorisedCategories(array('core.create'));

                if ($page->children) {
                    // make sure user can add something to at least one category
                    return (bool) array_intersect($authorized_categories, $page->children);
                }
                else {
                    // top level category link, return true if user can add something to any category
                    return (bool) count($authorized_categories);
                }
            }
            else {
                // POSTing a new document
                // Can only add something to a category reachable by the menu item
                if ($page->children && !in_array($this->_context->data->docman_category_id, $page->children)) {
                    return false;
                }
            }
        }

        return parent::canAdd();
    }

    public function canEdit()
    {
        $itemid = $this->getRequest()->Itemid;
        $page   = $this->getService('com://site/docman.model.pages')->id($itemid)->getItem();

        // Submit view is meant to be used with new items only
        if ($page->query['view'] === 'submit') {
            return false;
        }

        return parent::canEdit();
    }

    public function canDelete()
    {
        $itemid = $this->getRequest()->Itemid;
        $page   = $this->getService('com://site/docman.model.pages')->id($itemid)->getItem();

        // Submit view is meant to be used with new items only
        if ($page->query['view'] === 'submit') {
            return false;
        }

        return parent::canDelete();
    }

    public function canRead()
    {
        $itemid = $this->getRequest()->Itemid;
        $page   = $this->getService('com://site/docman.model.pages')->id($itemid)->getItem();
        $view   = $this->getView()->getName();

        // Submit view is meant to be used with new items only
        if ($page->query['view'] === 'submit' && $this->getModel()->getState()->isUnique()) {
            return false;
        }

        if (in_array($view, array('document', 'list')) && $this->getRequest()->layout !== 'form') {
            return !$this->getModel()->getItem()->isNew();
        }

        // Only display the edit form if user can add/edit stuff
        if ($view === 'document' && $this->getRequest()->layout === 'form') {
            return $this->getModel()->getState()->isUnique() ? $this->canEdit() : $this->canAdd();
        }

        if ($view === 'download') {
            $result = JFactory::getUser()->authorise('com_docman.download', 'com_docman');

            $item = $this->getModel()->getItem();
            if ($item->isAclable()) {
                $result = $item->canPerform('download');
            }

            if (!$result) {
                $result = $item->created_by == JFactory::getUser()->id;
            }

            return (bool) $result;
        }

        return parent::canRead();
    }

    public function canBrowse()
    {
        if ($this->getMixer()->getIdentifier()->name === 'submit') {
            return false;
        }

        return parent::canBrowse();
    }
}
