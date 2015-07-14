<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewSubmitHtml extends ComDocmanViewHtml
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->auto_fetch = false;

        parent::_initialize($config);
    }

    protected function _fetchData(KViewContext $context)
    {
        if ($this->getLayout() != 'success')
        {
            $page = JFactory::getApplication()->getMenu()->getActive();

            $context->data->document        = $this->getModel()->fetch();
            $context->data->show_categories = $page->params->get('category_children');

            if ($context->data->show_categories)
            {
                $context->data->category = $this->getObject('com://site/docman.model.categories')
                    ->id($page->params->get('category_id'))
                    ->fetch();

                $categories = $this->getObject('com://site/docman.model.categories')->setState(array(
                    'parent_id' => $context->data->category->id,
                    'access'    => $this->getObject('user')->getRoles(),
                    'enabled'   => true
                ))->fetch();

                if (!count($categories)) {
                    $context->data->show_categories = false;
                }
            }

        }
        else $context->data->page = JFactory::getApplication()->getMenu()->getDefault();

        parent::_fetchData($context);
    }
}
