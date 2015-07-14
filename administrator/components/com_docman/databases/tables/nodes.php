<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseTableNodes extends KDatabaseTableDefault
{
    protected $_relation_table;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        if (empty($config->relation_table)) {
            throw new KDatabaseTableException('Relation table cannot be empty');
        }

        $this->setRelationTable($config->relation_table);
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array('com://admin/docman.database.behavior.node')
        ));

        parent::_initialize($config);
    }

    public function getRelationTable()
    {
        return $this->_relation_table;
    }

    public function setRelationTable($table)
    {
        $this->_relation_table = $table;

        return $this;
    }
}
