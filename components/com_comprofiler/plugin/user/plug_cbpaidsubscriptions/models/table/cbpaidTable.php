<?php
/**
 * @version $Id: cbpaidTable.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payment item database table class
 */
abstract class cbpaidTable extends comprofilerDBTable {
	/** History logger object
	 * @var cbpaidHistory
	 * @access private */
	private $_history__Logger		=	null;
	private $_history__Message		=	null;
	private $_history__Previous		=	array();
	/**
	 * parameters of the object by column name
	 * @var ParamsInterface[]
	 */
	protected $_params;
	/**
	 * Constructor
	 *
	 *	@param string      $table  name of the table in the db schema relating to child class
	 *	@param string      $key    name of the primary key field in the table
	 *	@param CBdatabase  $db     CB Database object
	 */
	public function __construct(  $table, $key, &$db = null ) {
		if ( is_null( $db ) ) {
			global $_CB_database;
			$db			=&	$_CB_database;
		}
		$this->comprofilerDBTable(  $table, $key, $db );
	}
	/**
	 * Sets the history logger for this object
	 * @access protected
	 *
	 * @param  string $logger
	 */
	protected function _historySetLogger( $logger = 'cbpaidHistory' ) {
		$this->_history__Logger				=	$logger;
	}
	/**
	 * Sets history message
	 *
	 * @param  string  $message
	 * @return void
	 */
	public function historySetMessage( $message ) {
		$this->_history__Message				=	$message;
	}
	/**
	 * Logs current object as previous object
	 *
	 * @return void
	 */
	protected function _historyLogPrevious( ) {
		if ( $this->_history__Logger ) {
			$this->_history__Previous				=	array();
			foreach ( get_object_vars( $this ) as $k => $v ) {
				if( substr( $k, 0, 1 ) != '_' ) {
					$this->_history__Previous[$k]	=	$this->$k;
				}
			}
		}
	}
	/**
	 * Loads previous state if object with key id $storeId is not already loaded
	 *
	 * @param  int  $storeId
	 * @return void
	 */
	protected function _historyCheckLoadedPrevious( $storeId ) {
		if ( $storeId ) {
			$logger							=	$this->_history__Logger;
			if ( $logger && ( count( $this->_history__Previous ) == 0 ) ) {
				$query						=	"SELECT *"
					. "\n FROM "  . $this->_db->NameQuote( $this->_tbl )
					. "\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . ( is_numeric( $storeId ) ? (int) $storeId : $this->_db->Quote( $storeId ) );
				;
				$this->_db->setQuery( $query );
				$this->_history__Previous		=	$this->_db->loadAssoc();
				if ( $this->_history__Previous === null ) {
					$this->_history__Previous	=	array();
				}
			}
		}
	}
	/**
	 * Logs the changes made
	 *
	 * @param  int|null     $storeId
	 * @param  string|null  $changeType
	 * @return void
	 */
	protected function _historyLogChanges( $storeId, $changeType = null ) {
		$logger							=	$this->_history__Logger;
		if ( $logger ) {
			$tbl_key					=	$this->_tbl_key;
			if ( $changeType == 'delete' ) {
				$oldValue				=	$this->asXML();
				/** @var $logObject cbpaidHistory */
				$logObject				=	new $logger( $this->_db );
				$logObject->logChangeEvent( $this->_history__Message, $changeType, $this->_tbl, $this->$tbl_key, '', $oldValue, null );
			} elseif ( $storeId == 0 ) {
				$newValue				=	$this->asXML();
				/** @var $logObject cbpaidHistory */
				$logObject				=	new $logger( $this->_db );
				$logObject->logChangeEvent( $this->_history__Message, 'insert', $this->_tbl, $this->$tbl_key, '', null, $newValue );
			} else {
				if ( count( $this->_history__Previous ) > 0 ) {
					$changedFieldsOld	=	array();
					$changedFieldsNew	=	array();
					foreach ( get_object_vars( $this ) as $k => $v ) {
						if ( ( substr( $k, 0, 1 ) != '_' ) && ( ! is_object( $v ) ) && ( ! is_array( $v ) ) ) {
							if  ( ( (string) $this->$k ) !== ( (string) $this->_history__Previous[$k] ) ) {
								if ( $this->_history__Previous[$k] !== null ) {
									$changedFieldsOld[$k]	=	$this->_history__Previous[$k];
								}
								$changedFieldsNew[$k]		=	$v;
							}
						}
					}
					if ( count( $changedFieldsNew ) > 0 ) {
						/** @var $logObject cbpaidHistory */
						$logObject		=	new $logger( $this->_db );
						$logObject->logChangeEvent( $this->_history__Message, 'update', $this->_tbl, $this->$tbl_key, '', $this->_fieldsAsXML( $changedFieldsOld, 'fields' ), $this->_fieldsAsXML( $changedFieldsNew, 'fields' ) );
					}
				}
			}
			$this->_history__Message	=	null;
		}
	}
	/**
	 *	Binds an array/hash from database to this object
	 *
	 *	@param  int $oid  optional argument, if not specifed then the value of current key is used
	 *	@return mixed     any result from the database operation
	 */
	public function load( $oid = null ) {
		$result		=	parent::load( $oid );
		if ( $result ) {
			$this->_historyLogPrevious();
		}
		return $result;
	}
	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		$k			=	$this->_tbl_key;
		$storeId	=	$this->$k;
		$this->_historyCheckLoadedPrevious( $storeId );
		$result		=	parent::store( $updateNulls );
		if ( $result ) {
			$this->_historyLogChanges( $storeId );
		}
		return $result;
	}
	/**
	 * After store() this function may be called to get a result information message to display. Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore( ) {
		return null;		// Override
	}
	/**
	 * Deletes this record (no checks)
	 *
	 * @param  int      $oid   Key id of row to delete (otherwise it's the one of $this)
	 * @return boolean         TRUE if OK, FALSE if error
	 */
	public function delete( $oid=null ) {
		//FIXME: delete also all children subscriptions of this subscription (where parent_subscription and parent_plan match this one)
		$result			=	parent::delete( $oid );

		if ( $result ) {
			$this->_historyLogChanges( null, 'delete' );
		}

		return $result;
	}
	/**
	 * Loads an array of typed objects of a given class (same class as current object by default)
	 *
	 * @param  string $class [optional] class name
	 * @param  string $key [optional] key name in db to use as key of array
	 * @param  array  $additionalVars [optional] array of string additional key names to add as vars to object
	 * @return array  of objects of the same class (empty array if no objects)
	 */
	public function & loadTrueObjects( $class = null, $key = '', $additionalVars = null ) {
		if ( $additionalVars === null ) {
			$additionalVars		=	array();
		}
		/** @var $objectsArray cbpaidTable[] */
		$objectsArray			=&	parent::loadTrueObjects( $class, $key, $additionalVars );
		foreach ( $objectsArray as $k => $v ) {
			$objectsArray[$k]->_historyLogPrevious();
		}
		return $objectsArray;
	}

	/**
	 * Loads this object with the matching database record
	 *
	 * @param  array    $fields      array of column names to fetch (* = all)
	 * @param  array    $conditions  column => value  pairs OR array( column => array( operator, value ) ) where value int, float, string, or array of int (array will implode & operator = becomes IN), or object cbSqlQueryPart
	 * @param  array    $ordering    column => dir (ASC/DESC)
	 * @param  int      $offset      The offset to start selection
	 * @param  int      $limit       LIMIT statement (0=no limit)
	 * @return boolean               true if load succeeded, false otherwise
	 */
	public function setMatchingQuery( $fields, $conditions, $ordering = null, $offset = 0, $limit = 0 ) {
		if ( $ordering === null ) {
			$ordering			=	array();
		}
		$tableReferences		=	array( $this->_tbl => 'm' );
		$joinsSQL				=	array();

		foreach ( $fields as $k => $f ) {
			if ( $f != '*' ) {
				$fields[$k]		=	'm.' . $this->_db->NameQuote( $f );
			}
		}
		$where					=	array();
		foreach ( $conditions as $k => $v ) {
			if ( $k ) {
				if ( is_array( $v ) ) {
					$operator	=	$v[0];
					$val		=	$v[1];
				} else {
					$operator	=	'=';
					$val		=	$v;
				}
				$k				=	trim( $k );
				if ( is_int( $val )  ) {
					$where[]	=	'm.' . $this->_db->NameQuote( $k ) . ' ' . $operator . ' ' . (int) $val;
				} elseif ( is_float( $val ) ) {
					$where[]	=	'm.' . $this->_db->NameQuote( $k ) . ' ' . $operator . ' ' . (float) $val;
				} elseif ( is_array( $val ) ) {
					foreach ( $val as $kk => $vv ) {
						if ( ! ( is_int( $vv ) || is_float( $vv ) ) ) {
							$val[$kk]	=	 $this->_db->Quote( $vv );
						}
					}
					if ( $operator == '=' ) {
						$operator	=	'IN';
					}
					$where[]	=	'm.' . $this->_db->NameQuote( $k ) . ' ' . $operator . ' (' . implode( ',', $val ) . ')';
				} elseif ( is_object( $val ) ) {
					/** @var $val cbSqlQueryPart */
					$where[]	=	$val->reduceSqlFormula( $tableReferences, $joinsSQL, false );

					//TODO: add a $val === null case and use ISNULL or ignore ??? TO BE TESTED TOROUGHLY before implementing
				} else {
					$where[]	=	'm.' . $this->_db->NameQuote( $k ) . ' ' . $operator . ' ' . $this->_db->Quote( $val );
				}
			}
		}
		$orderby				=	array();
		foreach ( $ordering as $k => $v ) {
			if ( $k ) {
				$orderby[]		=	'm.' . $this->_db->NameQuote( $k ) . ( strtoupper( $v ) == 'DESC' ? ' DESC' : '' );
			}
		}
		$query = "SELECT " . implode( ", ", $fields )
			. "\n FROM " . $this->_db->NameQuote( $this->_tbl ) . ' AS m'
			. ( count( $joinsSQL ) ? "\n " . implode( "\n ", $joinsSQL ) : '' )
			. "\n WHERE " . implode( "\n AND ", $where )
			. ( count( $orderby ) ? ( "\n ORDER BY " . implode( ", ", $orderby ) ) : '' )
		;
		$this->_db->setQuery( $query, $offset ? (int) $offset : 0, $limit ? (int) $limit : 0 );
	}
	/*
	 * Loads a list of $key columns matching $value
	 *
	 * @param  string  $key     column name to match
	 * @param  string  $value   string content to match
	 * @return array   of object of the true class of this object
	 *
	public function loadMatchingList( $key, $value ) {
		$query = "SELECT *"
		. "\n FROM " . $this->_db->NameQuote( $this->_tbl )
		. "\n WHERE " . $this->_db->NameQuote( $key ) . " = " . ( is_int( $value ) ? (int) $value : $this->_db->Quote( $value ) )
		;
		$this->_db->setQuery( $query );

		return $this->loadTrueObjects( null, $key );
	}
	*/
	/**
	 * Loads a list of $key columns matching $value (indexed by key of this table)
	 *
	 * @param  array    $conditions  column => value  pairs
	 * @param  array    $ordering    column => dir (ASC/DESC)
	 * @param  int      $offset      The offset to start selection
	 * @param  int      $limit       LIMIT statement (0=no limit)
	 * @return array    of object of the true class of this object
	 */
	public function loadThisMatchingList( $conditions, $ordering = null, $offset = 0, $limit = 0 ) {
		if ( $ordering === null ) {
			$ordering			=	array();
		}
		$this->setMatchingQuery( array( '*' ), $conditions, $ordering, $offset, $limit );
		return $this->loadTrueObjects( null, $this->_tbl_key );
	}
	/**
	 * resets variable fully but not history.
	 *
	 */
	protected function _resetFull() {
		$class_vars				=	get_class_vars( get_class( $this ) );
		$reserved				=	array( '_db', '_tbl', '_tbl_key', $this->_tbl_key );
		foreach ($class_vars as $name => $value) {
			if ( ! ( in_array( $name, $reserved ) || ( substr( $name, 0 , 10 ) == '_history__' ) ) ) {
				$this->$name	=	$value;		// init uninitialized vars with null to make them visible to reset()
			}
		}
		$this->reset();		// init all public vars with null...
	}
	/**
	 * Loads this object with the matching database record
	 *
	 * @param  array    $conditions  column => value  pairs
	 * @param  array    $ordering    column => dir (ASC/DESC)
	 * @return boolean  true if load succeeded, false otherwise
	 */
	public function loadThisMatching( $conditions, $ordering = null ) {
		if ( $ordering === null ) {
			$ordering			=	array();
		}
		$this->_resetFull();		// init all public vars with null...
		$this->setMatchingQuery( array( '*' ), $conditions, $ordering, 0, 1 );
		$result					=	$this->_db->loadObject( $this );
		if ( $result ) {
			$this->_historyLogPrevious();
		}
		return $result;
	}
	/**
	 * Loads this object with the matching database record
	 *
	 * @param  string   $k    column name to match
	 * @param  int      $v    integer content to match
	 * @param  string   $k2   optional 2nd column name to match
	 * @param  int      $v2   optional integer content to match with 2nd column
	 * @return boolean  true if load succeeded, false otherwise
	 */
	public function loadThisMatchingInt( $k, $v, $k2=null, $v2=null ) {
		return $this->loadThisMatching( array( $k => (int) $v, $k2 => (int) $v2 ) );
	}
	/**
	 * Counts the rows of the table of this object matching the SQL $condtion
	 *
	 * @param string|null  $condition well-formed SQL WHERE statement. !!! NO ESCAPING !!!
	 * @return int         number of rows
	 */
	public function countRows( $condition ) {
		$query		=	"SELECT COUNT(*)"
			.	"\n FROM " . $this->_tbl;
		if ( $condition ) {
			$query	.=	"\n WHERE " . $condition;
		}
		$this->_db->setQuery( $query );

		return $this->_db->loadResult();
	}
	/**
	 * Export $this to xml <record>
	 *
	 * @return string   XML string '<record ..... /> (without xml header)
	 */
	public function asXML( ) {
		$key		=	$this->_tbl_key;
		$xml		=	'<record'
			.	' type="sql:row"'
			.	' table="'	. htmlspecialchars( $this->_tbl ) . '"'
			.	' class="'	. htmlspecialchars( get_class( $this ) ) . '"'
			.	' key="'	. htmlspecialchars( $key ) . '"';
		if ( $this->$key ) {
			$xml	.=	' value="'	. htmlspecialchars( $this->$key ) . '"'
				.	' valuetype="const:' . ( is_int( $this->$key ) ? 'int' : 'string' ) . '"';
		}
		$xml		.=	">\n";

		$xml		.=	$this->_fieldsAsXML( get_object_vars( $this ) );

		$xml		.=	"</record>\n";

		return $xml;
	}
	/**
	 * generates individual <field name="" value="" /> from $fields named array.
	 *
	 * @param  array    $fields
	 * @param  string   $enclosingTag  tag to enclose <field..., e.g. 'fields' for <fields>
	 * @return string|null             NULL if no $fields
	 */
	protected function _fieldsAsXML( $fields, $enclosingTag = null ) {
		$xml		=	null;
		if ( count( $fields ) ) {

			foreach ( $fields as $k => $v ) {
				if ( ( $v === null ) || ( $k[0] == '_' ) || ( $k == $this->_tbl_key ) || is_array( $v ) || is_object( $v ) ) {
					continue;
				}
				// Make sure value is UTF-8 encoded cleanly: (never overwrite $v to avoid PHP bug https://bugs.php.net/bug.php?id=66961 )
				$vClean	=	@iconv( 'UTF-8', 'UTF-8//IGNORE', $v );
				$xml	.=	"\t<field name=\"" . htmlspecialchars( $k ) . '" value="' . htmlspecialchars( $vClean ) . "\" />\n";
			}

			if ( $enclosingTag ) {
				$enclTH	=	htmlspecialchars( $enclosingTag );
				$xml	=	"<" . $enclTH . ">\n"
					.	$xml
					.	"</" . $enclTH . ">\n";
			}
		}
		return $xml;
	}
	/**
	 * Get an attribute of this stored object
	 *
	 * @param  string    $paramName     The name of the parameter
	 * @param  mixed     $default       The default value of the parameter
	 * @param  string    $paramColumn   The storage column in the $this object
	 * @return mixed
	 */
	public function getParam( $paramName, $default, $paramColumn = 'params' ) {
		$params								=&	$this->getParams( $paramColumn );
		return $params->get( $paramName, $default );
	}
	/**
	 * Sets an attribute of this stored object
	 *
	 * @param  string    $paramName     The name of the parameter
	 * @param  string    $value         The value of the parameter
	 * @param  string    $paramColumn   The storage column in the $this object
	 * @return string    The set value
	 */
	public function setParam( $paramName, $value, $paramColumn = 'params' ) {
		$params								=&	$this->getParams( $paramColumn );
		return $params->set( $paramName, $value );
	}
	/**
	 * Get attributes stored object
	 *
	 * @param  string           $paramColumn
	 * @return ParamsInterface
	 */
	public function & getParams( $paramColumn = 'params' ) {
		if ( ! isset( $this->_params[$paramColumn] ) ) {
			$this->_params[$paramColumn]	=	new Registry( $this->get( $paramColumn ) );
		}
		return $this->_params[$paramColumn];
	}
	/**
	 * Stores attributes of $paramColumn (or by default all) stored objects into the SQL columns
	 *
	 * @param  string  $paramColumn (optional)
	 */
	public function storeParams( $paramColumn = null ) {
		if ( is_array( $this->_params ) ) {
			if ( $paramColumn ) {
				if ( isset( $this->_params[$paramColumn] ) ) {
					$this->$paramColumn		=	$this->_params[$paramColumn]->asJson();
				}
			} else {
				foreach ( array_keys( $this->_params ) as $colName ) {
					$this->$colName			=	$this->_params[$colName]->asJson();
				}
			}
		}
	}
}	// cbpaidTable
/**
 * Basic database class with ordering column (but not by type)
 */
abstract class cbpaidOrderedTable extends cbpaidTable {
	/**
	 * @var int
	 */
	public $ordering;
}
/**
 * Basic database class with ordering column (by type column)
 */
abstract class cbpaidOrderedTypedTable extends cbpaidOrderedTable {
	/**
	 * @var string
	 */
	public $type;
}
/**
 * Complete database class with ordering column (by type column) with Enable, Publish, Checkout functionality
 */
abstract class cbpaidCompleteTable extends  cbpaidOrderedTypedTable {
	public $name;
	public $alias;
	public $published;
}
/**
 * Complete database class with ordering column (by type column) with Enable, Publish, Checkout functionality
 */
abstract class cbpaidCompleteCheckoutableTable extends  cbpaidOrderedTypedTable {
	public $checked_out;
}
