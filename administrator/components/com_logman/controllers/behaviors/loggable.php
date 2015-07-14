<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanControllerBehaviorLoggable extends ComActivitiesControllerBehaviorLoggable
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array('activity_controller' => array('identifier' => 'com://admin/logman.controller.activity')));
        parent::_initialize($config);
    }
}