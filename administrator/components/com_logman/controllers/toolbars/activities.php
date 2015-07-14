<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerToolbarActivities extends ComDefaultControllerToolbarDefault
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'title'  => 'LOGman'
        ));

        parent::_initialize($config);
    }
    
    protected function _commandPurge(KControllerToolbarCommand $command)
    {
        $option = $this->getIdentifier()->package;
        $command->attribs->href = JRoute::_('index.php?option=com_logman&view=activities&layout=purge&tmpl=component', false);
        $command->width = 280;
        $command->height = version_compare(JVERSION, '3.0', '>=') ? 280 : 200;
        
        return $this->_commandModal($command);
    }

    protected function _commandExport(KControllerToolbarCommand $command)
    {
        $state = $this->getController()->getModel()->getState();
        $url   = 'index.php?option=com_logman&view=activities&layout=export&tmpl=component&' . http_build_query($state->getData());
        if (version_compare(JVERSION, '3.0', '>=')) $command->icon = 'download';
        $command->attribs->href = JRoute::_($url, false);
        $command->width         = 280;
        $command->height        = 220;

        return $this->_commandModal($command);
    }
}