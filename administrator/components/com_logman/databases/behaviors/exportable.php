<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Exportable Database Behavior Class
 *
 * Provides a way for exporting data in sets or batches by using a column offset mechanism.
 */
class ComLogmanDatabaseBehaviorExportable extends KDatabaseBehaviorAbstract
{
    /**
     * The offset column (used for determining which are the next records to be exported)
     *
     * @var string
     */
    protected $_offset_column;

    /**
     * The table alias as set by the model.
     *
     * @var string
     */
    protected $_table_alias;

    /**
     * The current offset value.
     *
     * @var mixed
     */
    protected $_offset;

    /**
     * The maximum amount of records to be returned.
     *
     * @var int
     */
    protected $_limit;

    /**
     * The offset value of the last exported item
     *
     * @var mixed
     */
    protected $_last;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        if (!$config->offset_column)
        {
            $config->offset_column = $this->getMixer()->getIdentityColumn();
        }

        $this->_table_alias   = $config->table_alias;
        $this->_limit         = $config->limit;
        $this->_offset        = $config->offset;
        $this->_offset_column = $config->offset_column;
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array('table_alias' => 'tbl'));
    }

    protected function _beforeTableSelect(KCommandContext $context)
    {
        $query = $context->query;

        $query->where($this->_table_alias . '.' . $this->_offset_column, '>', (int) $this->_offset);

        if (!$query->count)
        {
            unset($query->order);
            $query->order($this->_table_alias . '.' . $this->_offset_column, 'ASC');
            $query->limit($this->_limit);
        }
    }

    protected function _afterTableSelect(KCommandContext $context)
    {
        $query = $context->query;

        if (!$query->count)
        {
            $data        = $context->data;
            $last        = end($data->toArray());
            $column      = $this->getMixer()->mapColumns($this->_offset_column, true);
            $this->_last = is_array($last) ? $last[$column] : 0;
        }
    }

    /**
     * Returns the offset value of the last exported item (used for determining the next batch starting point).
     *
     * @return mixed
     */
    public function getLast()
    {
        return $this->_last;
    }
}