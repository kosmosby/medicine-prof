<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorCommandable extends KControllerBehaviorCommandable
{
    protected function _beforeGet(KCommandContext $context)
    {
        parent::_beforeGet($context);

        if ($context->caller->getIdentifier()->name === 'submit'
            || ($context->caller->getIdentifier()->name === 'document' && $context->caller->getRequest()->layout === 'form'))
        {
            // Load the language strings for toolbar button labels
            JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

            $this->getView()->setToolbar($this->getToolbar());
        }

        // Only display add document button if the user has access to add documents
        if ($context->caller->getIdentifier()->name === 'list') {
            $params = JFactory::getApplication()->getMenu()->getActive()->params;
            if ($params->get('show_add_document_button')) {
                $params->set('show_add_document_button', $this->canAdd());
            }
        }
    }

    protected function _afterRead(KCommandContext $context)
    {
        if ($this->_toolbar) {
            $is_allowed = true;

            //Add the notice if the row is locked
            if (isset($context->result)) {
                if ($context->caller->getRequest()->layout === 'form' && $context->result->isLockable() && $context->result->locked()) {
                    $is_allowed = false;
                    JFactory::getApplication()->enqueueMessage($context->result->lockMessage(), 'notice');
                }
            }

            if ($context->caller->getIdentifier()->name === 'submit') {
                $this->getToolbar()
                    ->addCommand('save', array('is_allowed' => $is_allowed));
            } elseif ($context->caller->getIdentifier()->name === 'document' && $context->caller->getRequest()->layout === 'form')
            {
                $this->getToolbar()
                    ->addCommand('apply', array('is_allowed' => $is_allowed))
                    ->addCommand('save', array('is_allowed' => $is_allowed))
                    ->addCommand('cancel',  array('attribs' => array('data-novalidate' => 'novalidate')));
            }
        }
    }

    protected function _afterBrowse(KCommandContext $context)
    {
    }
}
