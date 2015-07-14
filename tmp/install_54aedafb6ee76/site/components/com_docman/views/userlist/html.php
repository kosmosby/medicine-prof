<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewUserlistHtml extends ComDocmanViewListHtml
{
    protected function _fetchData(KViewContext $context)
    {
        $context->data->append(array(
            'event_context' => 'com_docman.userlist'
        ));

        // Turn off owner label as every document is owned by the user anyway
        $this->getParameters()->show_document_owner_label = false;

        parent::_fetchData($context);
    }
}
