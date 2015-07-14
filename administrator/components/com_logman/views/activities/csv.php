<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanViewActivitiesCsv extends KViewCsv
{
    protected $_columns_map = array(
        'uuid'            => 'id',
        'application'     => 'application',
        'action'          => 'verb',
        'package'         => 'component',
        'name'            => 'object_type',
        'row'             => 'object_id',
        'title'           => 'object_name',
        'created_on'      => 'published',
        'created_by'      => 'actor_id',
        'created_by_name' => 'actor_name');

    /**
     * Return the views output
     *
     * @return string    The output of the view
     */
    public function display()
    {
        $rows    = '';
        $columns = array_keys($this->_columns_map);

        //Create the rows
        foreach ($this->getModel()->getList() as $row)
        {
            $data = array();

            foreach ($columns as $column)
            {
                $data[$column] = $row->{$column};
            }

            $rows .= $this->_arrayToString(array_values($data)) . $this->eol;
        }
        // Set the output
        $this->output = $rows;

        return $this->output;
    }

    public function getHeader()
    {
        return implode(',', array_values($this->_columns_map)) . $this->eol;
    }
}