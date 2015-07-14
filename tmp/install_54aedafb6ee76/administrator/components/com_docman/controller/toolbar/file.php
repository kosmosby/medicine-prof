<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerToolbarFile extends ComDocmanControllerToolbarActionbar
{
    public function getCommands()
    {
        $controller = $this->getController();
        $layout     = $controller->getView()->getLayout();

        if ($layout == 'default')
        {
            $this->addUpload(array(
                'label' => 'Upload', 'allowed' => $controller->canAdd(),
                'attribs' => array(
                    'class' => array('btn-success')
                )
            ));
            $this->addNewfolder(array(
                'label' => 'New Folder',
                'allowed' => $controller->canAdd(),
                'icon' => 'icon-32-new'
            ));
            $this->addDelete(array('allowed' => $controller->canDelete()));
            $this->addSeparator();
            $this->addCommand('create-documents', array('label' => 'Create Documents', 'icon' => 'icon-32-save-new', 'allowed' => $controller->canAdd()));
            $this->addRefresh();

            if ($controller->canAdmin()) {
                $this->addSeparator()->addOptions();
            }
        }

        if ($layout == 'form')
        {
            $this->addApply(array('allowed' => $controller->canAdd()));
            $this->addSave(array('allowed' => $controller->canAdd()));

            $this->addCancel();
        }

        return parent::getCommands();
    }
}
