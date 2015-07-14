<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerBehaviorExecutable extends ComActivitiesControllerBehaviorExecutable
{
    public function canGet()
    {
        $result = true;

        if (version_compare(JVERSION, '1.6.0', 'ge'))
        {
            if ($this->isDispatched())
            {
                $result = JFactory::getUser()->authorise('core.manage', 'com_logman') === true;
            }
        }
        else
        {
            $result = JFactory::getUser()->get('gid') > 22;
        }

        return $result;
    }

    public function canAdd()
    {
        $result = false;

        if ($this->getMixer()->getIdentifier()->name == 'activity') {
            $result = parent::canAdd();
        }

        return $result;
    }

	public function canDelete()
	{
        $result = false;

        if ($this->getMixer()->getIdentifier()->name == 'activity') {
            $result = $this->canGet();

            if ($result) {
                if(version_compare(JVERSION,'1.6.0','ge')) {
                    $result = JFactory::getUser()->authorise('core.delete', 'com_logman') === true;
                } else {
                    $result = JFactory::getUser()->get('gid') > 22;
                }
            }
        }

		return $result;
	}
	
	public function canPurge()
	{
	    return $this->canDelete();
	}
}
