<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Routes requests marked with routed=1 through com_files
 *
 */
class ComDocmanControllerBehaviorRoutable extends KControllerBehaviorAbstract
{
    protected function _beforeDispatch(KCommandContext $context)
    {
        if ($this->getRequest()->container === 'docman-files') {
            KService::setConfig('com://admin/files.controller.file', array(
                'behaviors' => array('com://admin/docman.controller.behavior.file')
            ));
            KService::setConfig('com://admin/files.controller.folder', array(
                'behaviors' => array('com://admin/docman.controller.behavior.folder')
            ));

            KService::setAlias('com://admin/files.model.containers', 'com://admin/docman.model.containers');
            KService::setAlias('com://site/files.model.containers', 'com://admin/docman.model.containers');
        }

        // Use our own ACL instead of com_files'
        KService::setAlias('com://admin/files.controller.behavior.executable', 'com://admin/docman.controller.behavior.executable');

        if ($this->getRequest()->routed)
        {
            $app = JFactory::getApplication();
            $behavior = 'com://admin/docman.controller.behavior.cacheable';

            // If the upload_folder parameter is set, we change the container path so results are different
            if ($app->isSite() && $app->getMenu()->getActive()->params->get('upload_folder')) {
                $behavior = $this->getService($behavior, array('only_clear' => true));
            }

            foreach (array('file', 'folder', 'node', 'thumbnail') as $name)
            {
                KService::setConfig('com://admin/files.controller.'.$name, array(
                    'behaviors' => array($behavior)
                ));
            }

            if (!in_array($this->getRequest()->container, array('docman-files', 'docman-icons', 'docman-images'))) {
                $this->getRequest()->container = 'docman-files';
            }

            if ($this->getRequest()->container === 'docman-icons') {
                KService::setConfig('com://admin/files.controller.file', array(
                    'behaviors' => array('com://admin/docman.controller.behavior.icon')
                ));
            }

            // Work-around the bug here: http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=28249
            JFactory::getSession()->set('com_docman.fix.the.session.bug', microtime(true));

            $context->result = $this->getService('com://admin/files.dispatcher', array(
                'request' => array('container' => $this->getRequest()->container)
            ))->dispatch();

            return false;
        }
    }

    protected function _beforeForward(KCommandContext $context)
    {
        if ($this->getRequest()->routed) {
            return false;
        }
    }
}
