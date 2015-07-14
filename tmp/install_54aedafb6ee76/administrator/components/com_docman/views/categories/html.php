<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewCategoriesHtml extends ComDocmanViewHtml
{
    protected function _fetchData(KViewContext $context)
    {
        parent::_fetchData($context);

        $context->data->categories->setDocumentCount();

        // For the sidebar tree
        $context->data->all_categories = $this->getObject('com://admin/docman.controller.category')->limit(0)->sort('title')->browse();
    }
}
