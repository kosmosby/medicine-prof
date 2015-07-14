<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorCommandable extends ComDefaultControllerBehaviorCommandable
{
    protected function _afterRead(KCommandContext $context)
    {
        if ($this->_toolbar) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            $name    = strtolower($context->caller->getIdentifier()->name);
            $key     = $this->getModel()->getState()->isUnique() ? 'Edit %item_type%' : 'Create new %item_type%';
            $title   = $translator->translate($key, array('%item_type%' => $name));
            $allowed = $this->{$context->result->isNew() ? 'canAdd' : 'canEdit'};

            if ($context->result->isLockable() && $context->result->locked()) {
                $allowed = false;
            }

            $this->getToolbar()
                 ->setTitle($title)
                 ->addCommand('apply', array('is_allowed' => $allowed))
                 ->addCommand('save', array('is_allowed' => $allowed));

            if (in_array($context->caller->getIdentifier()->name, array('category', 'document'))) {
                $this->getToolbar()->addCommand('save2new', array('is_allowed' => $allowed));
            }

            $this->getToolbar()->addCommand('cancel', array('attribs' => array('data-novalidate' => 'novalidate')));
        }
    }

    public function _afterGet(KCommandContext $context)
    {
        if ($this->_toolbar) {
            $layout = $context->caller->getView()->getLayout();

            if ($context->caller->getIdentifier()->name == 'file') {
                if ($layout === 'default') {
                    $this->getToolbar()
                        ->addUpload(array('label' => 'Upload', 'is_allowed' => $this->canAdd()))
                        ->addNew(array('label' => 'New Folder', 'is_allowed' => $this->canAdd()))
                        ->addDelete(array('is_allowed' => $this->canDelete()))
                        ->addSeparator()
                        ->addCommand('create-documents', array('label' => 'Create Documents', 'icon' => 'icon-32-save-new', 'is_allowed' => $this->canAdd()))
                        ->addRefresh();
                        ;

                    if ($this->canAdmin()) {
                           $this->getToolbar()->addSeparator()->addOptions();
                       }
                } elseif ($layout === 'form')
                {
                    if ($this->canAdd()) {
                        $this->getToolbar()->addApply(array('is_allowed' => $this->canAdd()));
                    }

                    $this->getToolbar()->addBack();

                    // Hide menubar for these layouts
                    $key = array_search('menubar', $this->_render);
                    if ($key) {
                        unset($this->_render[$key]);
                    }
                }
            }
        }

        parent::_afterGet($context);
    }

    protected function _afterBrowse(KCommandContext $context)
    {
        if ($this->_toolbar) {
            $identifier = $context->caller->getIdentifier();
            $request = $context->caller->getRequest();

            $new_link = 'index.php?option=com_'.$identifier->package.'&view='.$identifier->name;

            if ($identifier->name === 'document' && $request->category) {
                $new_link .= '&category='.$request->category;
            }

            $this->getToolbar()
                ->addCommand('new', array(
                    'attribs' => array(
                         'href' => JRoute::_($new_link)
                     ),
                     'is_allowed' => $this->canAdd()
                ))
                ->addCommand('delete', array('is_allowed' => $this->canDelete()))
                ->addSeparator()
                ->addPublish(array('is_allowed' => $this->canEdit()))
                ->addUnpublish(array('is_allowed' => $this->canEdit()));

               if ($this->canAdmin()) {
                   $this->getToolbar()->addSeparator()->addOptions();
               }
        }
    }
}
