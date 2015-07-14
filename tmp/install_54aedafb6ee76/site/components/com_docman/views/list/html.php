<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewListHtml extends ComDocmanViewHtml
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'auto_fetch' => false
        ));

        parent::_initialize($config);
    }

    public function isCollection()
    {
        return true;
    }

    protected function _fetchData(KViewContext $context)
    {
        $context->data->append(array(
            'event_context' => 'com_docman.list'
        ));

        $state  = $this->getModel()->getState();
        $params = $this->getParameters();
        $user   = $this->getObject('user');

        //Category
        if ($this->getModel()->getState()->isUnique()) {
            $category = $this->getModel()->fetch();
        }
        else
        {
            $category = $this->getModel()->create();
            $category->title = $params->page_heading ? $params->page_heading : $this->getActiveMenu()->title;
        }

        if ($state->isUnique() && $category->isNew()) {
            throw new KControllerExceptionResourceNotFound('Category not found');
        }

        //Subcategories
        if ($params->show_subcategories)
        {
            $subcategories = $this->getObject('com://site/docman.model.categories')
                ->level(1)
                ->parent_id($category->id)
                ->enabled($state->enabled)
                ->access($state->access)
                ->current_user($user->getId())
                ->page($state->page)
                ->sort($params->sort_categories)
                ->limit(0)
                ->fetch();
        }
        else $subcategories = array();

        //Documents
        if ($category->id)
        {
            $model = $this->getObject('com://site/docman.controller.document')
                ->enabled($state->enabled)
                ->status($state->status)
                ->access($state->access)
                ->current_user($user->getId())
                ->page($state->page)
                ->category($category->id)
                ->limit($state->limit)
                ->offset($state->offset)
                ->sort($state->sort)
                ->direction($state->direction)
                ->getModel();

            $total     = $model->count();
            $documents = $model->fetch();

            foreach ($documents as $document) {
                $this->prepareDocument($document, $params, $context->data->event_context);
            }
        }
        else
        {
            $total     = 0;
            $documents = array();
        }

        $context->data->category      = $category;
        $context->data->documents     = $documents;
        $context->data->total         = $total;
        $context->data->subcategories = $subcategories;

        parent::_fetchData($context);

        $context->parameters->total   = $total;
    }

    protected function _generatePathway($category = null, $document = null)
    {
        $category = $this->getModel()->fetch();

        parent::_generatePathway(($category->id ? $category : null));
    }

    /**
     * If the current page is not the menu category, use the current category title
     */
    protected function _setPageTitle()
    {
        if ($this->getName() === $this->getActiveMenu()->query['view'])
        {
            $category = $this->getModel()->fetch();
            $slug     = isset($this->getActiveMenu()->query['slug']) ? $this->getActiveMenu()->query['slug'] : null;

            if ($category->slug !== $slug) {
                $this->getParameters()->set('page_heading', $category->title);
                $this->getParameters()->set('page_title',   $category->title);
            }
        }

        parent::_setPageTitle();
    }
    /**
     * If the current page is not to a category menu item, set metadata
     */
    protected function _preparePage()
    {
        if ($this->getName() === $this->getActiveMenu()->query['view']) {
            $category = $this->getModel()->fetch();
            $slug     = isset($this->getActiveMenu()->query['slug']) ? $this->getActiveMenu()->query['slug'] : null;

            if ($category->slug !== $slug)
            {
                $helper   = $this->getTemplate()->createHelper('string');
                $this->getParameters()->{'menu-meta_description'} = $helper->truncate(array(
                    'text'   => $category->description,
                    'length' => 140
                ));
            }
        }

        parent::_preparePage();
    }
}
