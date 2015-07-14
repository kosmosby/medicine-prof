<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanControllerToolbarMenubar extends ComKoowaControllerToolbarMenubar
{
    public function getCommands()
    {
        $name = $this->getController()->getIdentifier()->name;

        $this->addCommand('Your Extensions', array(
            'href'    => 'option=com_extman&view=extensions',
            'active'  => $name === 'extension'
        ));

        $this->addCommand('Install More', array(
            'href'    => 'option=com_installer&view=install',
            'active'  => false
        ));

        return KControllerToolbarAbstract::getCommands();
    }
}