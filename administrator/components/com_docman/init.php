<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

$words = array(
    'event' => 'events',
    'controller' => 'controllers',
    'behavior' => 'behaviors',
    'model' => 'models',
    'table' => 'tables',
    'database' => 'databases',
    'command' => 'commands',
    'object' => 'objects',
    'adapter' => 'adapters',
    'filter' => 'filters',
    'view' => 'views',
    'template' => 'templates',
    'helper' => 'helpers',
    'row' => 'rows',
    'rowset' => 'rowsets',
    'mixin' => 'mixins',

    'parameter' => 'parameters',
    'category' => 'categories',
    'document' => 'documents',
    'config' => 'configs',
    'container' => 'containers',
    'file' => 'files',
    'http' => 'https',
    'local' => 'locals'
);

foreach ($words as $singular => $plural) {
    KInflector::addWord($singular, $plural);
}
