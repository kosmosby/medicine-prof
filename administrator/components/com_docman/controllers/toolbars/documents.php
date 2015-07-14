<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerToolbarDocuments extends ComDocmanControllerToolbarDefault
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'icon'  => 'article'
        ));

        parent::_initialize($config);
    }

    public function getCommands()
    {
        $this->setTitle('COM_DOCMAN_SUBMENU_DOCUMENTS');

        return parent::getCommands();
    }
}
