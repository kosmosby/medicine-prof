<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewDocumentHtml extends ComDocmanViewHtml
{
    protected function _fetchData(KViewContext $context)
    {
        $document = $this->getModel()->fetch();

        if ($this->getLayout() !== 'form')
        {
            $params                       = $this->getParameters();
            $context->data->event_context = 'com_docman.document';

            $this->prepareDocument($document, $params, $context->data->event_context);

            //Settings
            $query = $this->getActiveMenu()->query;
            if ($query['view'] === 'document' && $query['slug'] === $document->slug) {
                $context->data->show_delete = false;
            }
        }
        else
        {
            $view = $this->getActiveMenu()->query['view'];

            if ($view === 'userlist' && $document->isPermissible() && !$document->canPerform('manage')) {
                $context->data->hide_owner_field = true;
            }
        }

        parent::_fetchData($context);
    }

    protected function _generatePathway($category = null, $document = null)
    {
        $document = $this->getModel()->fetch();

        if ($this->getLayout() === 'form')
        {
            $translator = $this->getObject('translator');

            if($document->isNew()) {
                $text = $translator->translate('Add new document');
            } else {
                $text = $translator->translate('Edit document {title}', array('title' => $document->title));
            }

            $this->getPathway()->addItem($text, '');
        }
        else
        {
            $category = $this->getObject('com://site/docman.model.categories')
                              ->id($document->docman_category_id)
                              ->fetch();

            parent::_generatePathway($category, $document);
        }
    }

    /**
     * If the current page is not to a document menu item, use the current document title
     */
    protected function _setPageTitle()
    {
        if ($this->getName() !== $this->getActiveMenu()->query['view'])
        {
            $document = $this->getModel()->fetch();

            $this->getParameters()->set('page_heading', $document->title);
            $this->getParameters()->set('page_title',   $document->title);
        }

        parent::_setPageTitle();
    }

    /**
     * If the current page is not to a document menu item, set metadata
     */
    protected function _preparePage()
    {
        if ($this->getName() !== $this->getActiveMenu()->query['view'])
        {
            $helper   = $this->getTemplate()->createHelper('string');
            $document = $this->getModel()->fetch();
            $this->getParameters()->{'menu-meta_description'} = $helper->truncate(array(
                'text'   => $document->description,
                'length' => 140
            ));
        }

        parent::_preparePage();
    }
}
