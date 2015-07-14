<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerToolbarDefault extends ComDefaultControllerToolbarDefault
{
    protected function _commandOptions(KControllerToolbarCommand $command)
    {
        $command->append(array(
            'attribs' => array(
                'href' => JRoute::_('index.php?option=com_docman&view=config')
            )
        ));
    }
}
