<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDispatcher extends ComDefaultDispatcher
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback('before.dispatch', array($this, 'beforeDispatch'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'controller' => 'list',
            'behaviors'  => array(
                'com://admin/docman.controller.behavior.routable'
            )
        ));

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        if ($request->alias && !$request->slug) {
            $request->slug = array_pop(explode('-', $request->alias, 2));
        }

        $menu = JFactory::getApplication()->getMenu()->getActive();
        if ($menu) {
            $request->Itemid = $menu->id;
        }

        if (JFactory::getUser()->authorise('core.manage', 'com_docman') !== true)
        {
            // Can't use executable behavior here as it calls getController which in turn calls this method
            $request->enabled = 1;
            $request->status = 'published';
        }

        $request->access = JFactory::getUser()->getAuthorisedViewLevels();
        $request->page = $request->Itemid;

        // These are read-only for outsiders
        unset($request->page_conditions);

        $request->current_user = JFactory::getUser()->id;

        return $request;
    }

    public function beforeDispatch(KCommandContext $context)
    {
        if (!$this->_checkMenu()) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            throw new KDispatcherException($translator->translate('Invalid menu item'));
        }
    }

    /**
     * Check if we have a valid menu item
     *
     * @return bool
     */
    protected function _checkMenu()
    {
        $menu = JFactory::getApplication()->getMenu()->getActive();

        if (!$menu) {
            return $this->getRequest()->view === 'doclink';
        }

        return $menu->query['option'] === 'com_docman';
    }
}
