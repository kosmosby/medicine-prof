<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDispatcherHttp extends ComKoowaDispatcherHttp
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.get', '_setLimit');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'controller'     => 'list',
            'authenticators' => array('jwt'),
            'behaviors'      => array(
                'com://admin/docman.dispatcher.behavior.routable'
            )
        ));

        parent::_initialize($config);
    }

    /**
     * Sets and override default limit based on page settings parameters.
     *
     * @param KDispatcherContextInterface $context
     * @return KModelEntityInterface
     */
    protected function _setLimit(KDispatcherContextInterface $context)
    {
        $controller = $this->getController();

        if (in_array($controller->getIdentifier()->name, array('list', 'filteredlist', 'userlist')))
        {
            $params = JFactory::getApplication()->getMenu()->getActive()->params;

            if ($limit = $params->get('limit')) {
                $this->getConfig()->limit->default = $limit;
            }

            if (!$params->get('show_document_sort_limit'))
            {
                $this->getRequest()->getQuery()->limit = (int) $this->getConfig()->limit->default;
                $controller->getModel()->getState()->setProperty('limit', 'internal', true);
            }
        }
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        $query = $request->query;

        if ($query->alias && !$query->slug)
        {
            $parts       = explode('-', $query->alias, 2);
            $query->slug = array_pop($parts);
        }

        $menu = JFactory::getApplication()->getMenu()->getActive();
        if ($menu && !in_array($query->view, array('doclink', 'documents'))) {
            $query->Itemid = $menu->id;
        }

        // Can't use executable behavior here as it calls getController which in turn calls this method
        if ($this->getObject('user')->authorise('core.manage', 'com_docman') !== true)
        {
            $query->enabled = 1;
            $query->status  = 'published';
        }

        // Force tmpl=koowa for form layouts
        if ($query->layout === 'form' && $query->view !== 'submit') {
            $query->tmpl = 'koowa';
        }

        $query->access = $this->getObject('user')->getRoles();
        $query->page   = $query->Itemid;
        $query->current_user = $this->getObject('user')->getId();

        return $request;
    }
}
