<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewFilteredlistHtml extends ComDocmanViewHtml
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
        $params = $this->getParameters();

        $context->data->event_context = 'com_docman.documents';
        $context->data->documents = $this->getModel()->fetch();
        $context->data->total     = $this->getModel()->count();

        foreach ($context->data->documents as $document) {
            $this->prepareDocument($document, $params, $context->data->event_context);
        }

        parent::_fetchData($context);

        $context->parameters->total = $this->getModel()->count();
    }
}
