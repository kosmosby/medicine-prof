<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerList extends ComDefaultControllerResource
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'model' => 'com://site/docman.model.categories',
            'behaviors' => array('com://site/docman.controller.behavior.list.persistable')
        ));

        parent::_initialize($config);
    }

    /**
     * Overridden to add additional model states to the existing model
     *
     * @see KControllerResource::getModel()
     */
    public function getModel()
    {
        if(!$this->_model instanceof KModelAbstract)
        {
            //Make sure we have a model identifier
            if (!($this->_model instanceof KServiceIdentifier)) {
                $this->setModel($this->_model);
            }

            $model = $this->getService($this->_model);
            $state = $model->getState();

            if (!isset($state->document_sort)) {
                $state->insert('document_sort', 'cmd')
                      ->insert('document_direction', 'cmd')
                      ->insert('document_state', 'raw'); // For passing states to document HMVC call
            }

            $model->set($this->getRequest());

            $this->_model = $model;
        }

        return parent::getModel();
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        if (!isset($request->slug) && isset($request->path)) {
            $request->slug = array_pop(explode('/', $request->path));
        }

        if ($this->isDispatched()) {
            $request->document_state = null;

            $params = JFactory::getApplication()->getMenu()->getActive()->params;
            $request->sort = $params->get('sort_categories');
        }

        return $request;
    }
}
