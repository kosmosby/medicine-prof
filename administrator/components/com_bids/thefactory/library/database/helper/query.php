<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: library
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryDatabaseQuery extends JObject{

    private $select = array();
	private $from = array();
	private $join = array();
	private $where = array();
	private $group = array();
	private $having = array();
    private $order = array();

    function select($field) {
        $field = (array) $field;
        $this->select = array_merge($this->select,$field);
    }

    function from($tableName,$tableAlias='') {
        $table = array();
        $table['tableName'] = $tableName;
        $table['tableAlias'] = $tableAlias;

        $this->from[] = $table;
    }

    function join($joinType,$tableName,$tableAlias,$joinOn) {
        $table = array();
        $table['tableName'] = $tableName;
        $table['tableAlias'] = $tableAlias;
        $table['joinOn'] = $joinOn;

        $this->join[$joinType][] = $table;
    }

    function where($conditions, $logicalOperator=null) {

        $conditions = (array) $conditions;

        if(!is_null($logicalOperator)) {
            $this->where[] = implode(' '.$logicalOperator.' ', $conditions);
        } else {
            $this->where = array_merge($this->where,$conditions);
        }
    }

    function group($field) {
        $field = (array) $field;
        $this->group = array_merge($this->group,$field);
    }

    function order($field) {
        $field = (array) $field;
        $this->order = array_merge($this->order,$field);
    }

    function having($field) {
        $field = (array) $field;
        $this->having = array_merge($this->having,$field);
    }

    function get($queryPart) {
        $queryParts = array('select','from','join','where','group','having','order');
        if(in_array($queryPart,$queryParts)) {
            return $this->$queryPart;
        }
        return null;
    }
    function set($queryPart,$value=null) {
        $queryParts = array('select','from','join','where','group','having','order');
        if(in_array($queryPart,$queryParts)) {
            if (is_array($value))
                $this->$queryPart=$value;
            elseif($value!==NULL)
                $this->$queryPart=array($value);
            else
                $this->$queryPart=null;
            return $this->$queryPart;
        }
        return null;
    }

    function getQueriedTables() {

        $queriedTables = array();

        $from = $this->from;
        foreach($from as $table) {
            $queriedTables[$table['tableAlias']] = $table['tableName'];
        }
        $joins = $this->join;
        foreach($joins as $joinType=>$tables) {
            foreach($tables as $table) {
                $queriedTables[$table['tableAlias']] = $table['tableName'];
            }
        }

        return $queriedTables;
    }

    function __toString() {

        $sqlQuery = '';

        $br = JDEBUG ? PHP_EOL : '';

        //SELECT
        $sqlQuery .= 'SELECT '. $br . implode(', '.$br,$this->select).$br;


        //FROM
        $sqlQuery .= ' FROM '.$br;
        foreach($this->from as $table) {
            $sqlQuery .= $table['tableName'] . ( $table['tableAlias'] ? (' AS '.$table['tableAlias']) : '' ).$br;
        }

        //JOIN
        foreach($this->join as $joinType=>$tables) {
            foreach($tables as $table) {
                $sqlQuery .= ' '.strtoupper($joinType).' JOIN '.$table['tableName'].' AS '.$table['tableAlias'].' ON '.$table['joinOn'] . $br;
            }
        }

        //WHERE
        if(count($this->where)) {
            $sqlQuery .= ' WHERE '.$br;
            $sqlQuery .= ' (' . implode(') AND'.$br.' (', $this->where) . ') '.$br;
        }

        //GROUP BY
        if(count($this->group)) {
            $sqlQuery .= ' GROUP BY ' . $br;
            $sqlQuery .= implode(', ', $this->group) . $br;
        }

        //HAVING
        if(count($this->having)) {
            $sqlQuery .= ' HAVING ' . $br;
            $sqlQuery .= implode(' AND ', $this->having) . $br;
        }

        //ORDER BY
        if(count($this->order)) {
            $sqlQuery .= ' ORDER BY ' . $br;
            $sqlQuery .= implode(', ', $this->order) . $br;
        }

        return $sqlQuery;
    }
}
