<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class PlgLogmanFiles extends ComLogmanPluginAbstract
{
    /**
     * @var array Files dependencies.
     * @see ComLogmanPluginDefault::$_dependencies
     */
    protected $_dependencies = array(
        'com://admin/files.controller.file'   => 'com://admin/logman.controller.behavior.file_loggable',
        'com://admin/files.controller.folder' => 'com://admin/logman.controller.behavior.file_loggable');
}