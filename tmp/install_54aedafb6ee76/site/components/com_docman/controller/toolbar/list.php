<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerToolbarList extends ComKoowaControllerToolbarActionbar
{
    public function getCommands()
    {
        // Only display add document button if the user has access to add documents and if the button should be shown
        $params = JFactory::getApplication()->getMenu()->getActive()->params;

        if($this->getController()->canAdd() && $params->get('show_add_document_button')) {
            $this->addCommand('new');
        }

        return parent::getCommands();
    }

    protected function _commandNew(KControllerToolbarCommand $command)
    {
        $category       = $this->getController()->getModel()->fetch();
        $command->href  = 'view=document&layout=form&slug=&category_slug=' . ($category->slug ? $category->slug : '');
        $command->label = 'Add new document';
    }
}
