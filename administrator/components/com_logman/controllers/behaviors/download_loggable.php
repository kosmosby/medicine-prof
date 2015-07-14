<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerBehaviorDownload_loggable extends ComLogmanControllerBehaviorLoggable
{
    protected function _initialize(KConfig $config)
    {
        $config->actions = array('after.read');

        parent::_initialize($config);
    }

    protected function _getStatus(KDatabaseRowAbstract $row, $action)
    {
        $status = $row->getStatus();

        // Only log download events for logged in users
        if ($action == 'after.read' && empty($status)) {
            $status = JFactory::getUser()->guest ? null : 'downloaded';
        }

        return $status;
    }

    protected function _getActivityData(KDatabaseRowAbstract $row, $status, KCommandContext $context)
    {
        $data = parent::_getActivityData($row, $status, $context);

        // Change the action from read to download for download events
        if ($data['name'] === 'download') {
            $data['name'] = 'document';
            $data['action'] = 'download';
        }

        return $data;
    }
}