<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerBehaviorCommandable extends ComDefaultControllerBehaviorCommandable
{
    protected function _afterBrowse(KCommandContext $context)
    {
        if($this->getMixer()->getIdentifier()->name == 'activity' && $this->_toolbar)
        {
        	if ($this->canDelete()) {
                $this->getToolbar()->addDelete();
                $this->getToolbar()->addPurge();
            }

            $this->getToolbar()->addExport();

            if (version_compare(JVERSION, '1.6', '<') || JFactory::getUser()->authorise('core.admin', 'com_logman'))
            {
            	$enabled = $this->getMixer()->checkPlugin();
		        $command = $enabled ? 'disable' : 'enable';
		    	$this->getToolbar()->addCommand($command, array(
		            'label' => $command,
		            'attribs' => array(
		                'data-novalidate' => 'novalidate',
		                'data-action' => 'editPlugin'
		            )
		        ));
            }

        	if (version_compare(JVERSION, '1.6', '<') || JFactory::getUser()->authorise('core.admin', 'com_logman'))
        	{
        	    $this->getToolbar()->addOptions();
            }
        }
    }
}