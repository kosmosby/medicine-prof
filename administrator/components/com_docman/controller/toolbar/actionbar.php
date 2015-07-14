<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerToolbarActionbar extends ComKoowaControllerToolbarActionbar
{
    protected function _commandOptions(KControllerToolbarCommand $command)
    {
        $command->href = 'component=docman&view=config';
    }

    protected function _commandBack(KControllerToolbarCommand $command)
    {
        if (version_compare(JVERSION, '3.0', '>=')) {
            $command->icon = 'icon-32-arrow-left';
        }

        if(!isset($command->href))
        {
            $command->append(array(
                'attribs'    => array(
                    'data-action'  => $command->getName()
                )
            ));
        }
    }

    protected function _afterRead(KControllerContextInterface $context)
    {
        parent::_afterRead($context);

        $controller = $this->getController();
        $allowed    = $controller->{$context->result->isNew() ? 'canAdd' : 'canEdit'}();

        if ($context->result->isLockable() && $context->result->isLocked()) {
            $allowed = false;
        }

        $this->removeCommand('save')->removeCommand('apply')->removeCommand('cancel');

        $this->addCommand('apply', array('allowed' => $allowed));
        $this->addCommand('save', array('allowed' => $allowed));

        if (in_array($context->subject->getIdentifier()->name, array('category', 'document'))) {
            $this->addCommand('save2new', array('allowed' => $allowed));
        }

        $this->addCommand('cancel');
    }

    protected function _afterBrowse(KControllerContextInterface $context)
    {
        $controller = $this->getController();
        $identifier = $context->subject->getIdentifier();
        $request    = $context->subject->getRequest();
        $translator = $this->getObject('translator');

        $new_link = 'option=com_'.$identifier->package.'&view='.$identifier->name;

        if ($identifier->name === 'document' && $request->query->category) {
            $new_link .= '&category='.$request->query->category;
        }

        $this->addCommand('new', array(
            'href' => $new_link,
            'allowed' => $controller->canAdd()
        ));

        $this->addCommand('delete', array(
            'allowed' => $controller->canDelete(),
            'attribs' => array(
                'data-prompt' => $translator->translate('Deleted items will be lost forever. Would you like to continue?')
            )
        ));
        $this->addSeparator();
        $this->addPublish(array('allowed' => $controller->canEdit()));
        $this->addUnpublish(array('allowed' => $controller->canEdit()));

        if ($identifier->name === 'document') {
            $this->addMove(array('allowed' => $controller->canEdit()));
        }

        if ($controller->canAdmin()) {
            $this->addSeparator()->addOptions();
        }
    }

    protected function _commandMove(KControllerToolbarCommand $command)
    {
        $command->attribs['href'] = '#';
    }
}
