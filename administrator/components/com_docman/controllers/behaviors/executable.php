<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorExecutable extends ComDocmanControllerBehaviorPermissions
{
    protected $_context;

    public function execute($name, KCommandContext $context)
    {
        /*
         * For config and file controllers, we have specific checks for all actions on them
         */
        $result = true;
        if ($this->_mixer->getIdentifier()->name === 'config') {
            $result = $this->canAdmin();
        }

        if ($this->_mixer->getIdentifier()->name === 'file' || $this->getRequest()->routed)
        {
            if (!in_array($context->action, array('get', 'display', 'read', 'browse'))) {
                $result = JFactory::getUser()->authorise('com_docman.upload', 'com_docman');
            } else {
                $result = $this->canManage() || $this->canChangeAnything();
            }
        }

        if ($result === false) {
            $context->setError(new KControllerException(
                'Action '.ucfirst($context->action).' Not Allowed', KHttpResponse::METHOD_NOT_ALLOWED
            ));

            return false;
        }

        return parent::execute($name, $context);
    }
}
