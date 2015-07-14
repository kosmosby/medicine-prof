<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanControllerPermissionExtension extends ComKoowaControllerPermissionAbstract
{
    /**
     * Only people who are able to manage EXTman can see it
     *
     * @return bool
     */
    public function canRender()
    {
        return $this->canManage();
    }

    /**
     * Only people who are able to manage EXTman can delete extensions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canDelete()
    {
        return $this->canManage() && parent::canDelete();
    }
}
