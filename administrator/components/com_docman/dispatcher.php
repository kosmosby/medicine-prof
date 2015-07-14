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

        $this->registerCallback('before.dispatch', array($this, 'checkManageRights'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'controller' => 'document',
            'behaviors'  => array(
                'com://admin/docman.controller.behavior.routable'
            )
        ));

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        // If Itemid is passed load the default model from frontend to do filtering based on pages
        if ($request->Itemid) {
            $request->page = $request->Itemid;
            $this->getService('koowa:loader')->loadIdentifier('com://site/docman.model.default');
        }

        return $request;
    }

    public function checkManageRights(KCommandContext $context)
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_docman')) {
            JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }
    }
}
