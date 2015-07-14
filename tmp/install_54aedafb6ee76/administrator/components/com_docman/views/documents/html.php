<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewDocumentsHtml extends ComDocmanViewHtml
{
    protected function _fetchData(KViewContext $context)
    {
        //Categories list
        $context->data->categories = $this->getObject('com://admin/docman.controller.category')->limit(0)->sort('title')->browse();
        $context->data->categories->setDocumentCount();

        parent::_fetchData($context);
    }
}
