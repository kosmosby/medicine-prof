<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseTableDocuments extends KDatabaseTableAbstract
{
    protected function _initialize(KConfig $config)
    {
        $timezonable = $this->getBehavior('timezonable')->setFields(array('publish_on', 'unpublish_on'));

        $config->append(array(
            'command_chain' => $this->getService('com://admin/docman.command.chain'),
            'behaviors' => array(
                'aclable',
                'configurable',
                'lockable',
                'creatable',
                'modifiable',
                'sluggable',
                'identifiable',
                $timezonable
            ),
            'filters' => array(
                'storage_type' => array('com://admin/docman.filter.identifier'),
                'description' => array('html')
            )
        ));

        parent::_initialize($config);
    }
}
