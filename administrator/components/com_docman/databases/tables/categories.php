<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseTableCategories extends ComDocmanDatabaseTableNodes
{
    protected function  _initialize(KConfig $config)
    {
        $config->append(array(
            'command_chain' => $this->getService('com://admin/docman.command.chain'),
            'relation_table' => 'docman_category_relations',
            'behaviors' => array(
                'aclable',
                'configurable',
                'lockable',
                'sluggable',
                'creatable',
                'modifiable',
                'identifiable',
                'orderable'
            ),
            'filters' => array(
                'description' => array('html')
            )
        ));

        parent::_initialize($config);
    }
}
