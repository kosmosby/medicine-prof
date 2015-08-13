<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanControllerToolbarExtension extends ComKoowaControllerToolbarActionbar
{
	protected function _commandDelete(KControllerToolbarCommand $command)
    {
        $command->icon  = 'icon-32-delete';
        $command->label = 'Uninstall';

        $command->append(array(
            'attribs' => array(
                'data-action' => 'delete'
            )
        ));
    }

    protected function _afterBrowse(KControllerContextInterface $context)
    {
        $controller = $this->getController();

        if ($controller->canDelete()) {
            $this->addDelete();
        }

        if ($controller->canAdmin()) {
            $this->addOptions();
        }
    }
}