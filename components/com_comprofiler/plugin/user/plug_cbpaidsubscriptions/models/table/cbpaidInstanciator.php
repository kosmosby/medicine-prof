<?php
/**
 * @version $Id: cbpaidInstanciator.php 1596 2012-12-28 00:28:56Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to manage multiple types of instances of objects
 *
 */
abstract class cbpaidInstanciator extends cbpaidTable {
	protected $_classnameField;			// override !
	protected $_classnamePrefix;				// override !
	protected $_classLibraryPrefix;		// override !
	protected $_classLibrarySubfolders	=	false;	// override !
	/**
	 * Constructor
	 *
	 *	@param string      $table  name of the table in the db schema relating to child class
	 *	@param string      $key    name of the primary key field in the table
	 *	@param CBdatabase  $db     CB Database object
	 */
	public function __construct( $table, $key, &$db = null ) {
		parent::__construct(  $table, $key, $db );
	}
	/**
	 * Gets a single instance of the class
	 *
	 * Example implementation:
	static $singleInstance	=	null;
	if ( $singleInstance === null ) {
	$singleInstance		=	new self( $db );
	}
	return $singleInstance;
	 *
	 * @param  CBdatabase  $db
	 * @return stdClass
	 */
	public static function & getInstance( /** @noinspection PhpUnusedParameterInspection */ &$db = null ) {
		trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' called while it should be overridden.' );		// because PHP 5.4.4 (and 5.3.3 doesn't alliow abstract+static functions https://bugs.php.net/bug.php?id=53081 )
	}
	/**
	 * Loads a plan and returns an object of the corresponding class
	 *
	 * @param  int  $id  Id of plan
	 * @return cbpaidProduct
	 */
	public function & getObject( $id ) {
		static $_objects			=	array();

		$id							=	(int) $id;

		if ( ! isset( $_objects[$this->_tbl][$id] ) ) {
			if ( $id ) {
				$sql				=	"SELECT a.* FROM `" . $this->_tbl . "` AS a"
					.	"\n WHERE a.`" . $this->_tbl_key . "` = " . (int) $id;
				$this->_db->setQuery( $sql );
				$results			=	$this->_loadTrueObjects( $this->_tbl_key );
				if ( count( $results ) == 1 ) {
					$_objects[$this->_tbl][$id]	=	$results[$id];
				} else {
					$_objects[$this->_tbl][$id]	=	null;
					// trigger_error( 'getObject of object types ' . $this->_classPrefix . ' id ' . $id . ' failed.', E_USER_NOTICE );
					cbpaidApp::setLogErrorMSG( 5, null, sprintf( 'getObject of object types %s id %s failed.', $this->_classnamePrefix, $id ), null );
				}

			} else {
				$_objects[$this->_tbl][$id]		=	new $this->_classnamePrefix( $this->_db );
			}
		}
		return $_objects[$this->_tbl][$id];
	}
	/**
	 * Loads an array of typed objects of a given class (same class as current object by default)
	 *
	 * @param  string  $key  [optional] key name in db to use as key of array
	 * @return array         of objects of the same class (empty array if no objects)
	 */
	protected function & _loadTrueObjects( $key = '' ) {
		$resultsArray				=	$this->_db->loadAssocList( $key );
		if ( is_array( $resultsArray ) ) {
			$objectsArray			=&	$this->getObjects( $resultsArray );
		} else {
			$objectsArray			=	array();
		}
		return $objectsArray;
	}
	/**
	 * Maps array of arrays to an array of new objects of the corresponding class for each row
	 *
	 * @param  array|int      $resultsArray  array of a row of database to convert | int id of row to load
	 * @return cbpaidTable[]
	 */
	public function & getObjects( &$resultsArray ) {
		$objectsArray				=	array();
		if ( ! is_array( $resultsArray ) ) {
			$objectsArray[]			=	$this->getObject( $resultsArray );
		} else {
			foreach ( $resultsArray as $k => $value ) {
				$classSuffix		=	( isset( $value[$this->_classnameField] ) ? $value[$this->_classnameField] : '' );
				$lastDotPos			=	strrpos( $classSuffix, '.' );
				if ( $lastDotPos !== false ) {
					$classSuffix	=	substr( $classSuffix, $lastDotPos + 1 );
				}
				$class				=	$this->_classnamePrefix . ucfirst( $classSuffix );
				if ( ! class_exists( $class ) ) {
					// This is only needed during upgrade of 2.x to 3.x:
					if ( $this->_classLibrarySubfolders ) {
						// replace group.name by group.name.name , or name by name.name :
						$libName	=	preg_replace( '/^((.*)\.)*(.*)$/', '\1\3.\3', $value[$this->_classnameField] );
					} else {
						$libName	=	$value[$this->_classnameField];
					}
					cbpaidApp::import( $this->_classLibraryPrefix . $libName );
				}
				if ( class_exists( $class ) ) {

					// Now check case of abstract class for a new object to create:
					if ( ( ! isset( $value['id'] ) ) || ( $value['id'] == 0 ) || ( $value['id'] == '' ) ) {
						$reflection	=	new ReflectionClass( $class );
						if ( $reflection->isAbstract() ) {
							if ( class_exists( $class . 'Undefined' ) ) {
								$class		.=	'Undefined';
							} else {
								trigger_error( sprintf('%s:%s: Class %2s is abstract and cannot be instanciated and class %2sUndefined does not exist.', __CLASS__, __FUNCTION__, $class ), E_USER_ERROR );
							}
						}
					}

					// Ok, we can instanciate safely:
					$objectsArray[$k]	=	new $class( $this->_db );
					foreach ( $value as $kk => $vv ) {
						$objectsArray[$k]->$kk	=	$vv;
					}
				}
			}
		}
		return $objectsArray;
	}
	/**
	 * Loads a list of $key columns matching $value (indexed by key of this table)
	 *
	 * @param  array    $conditions  column => value  pairs
	 * @param  array    $ordering    column => dir (ASC/DESC)
	 * @param  int      $offset      The offset to start selection
	 * @param  int      $limit       LIMIT statement (0=no limit)
	 * @return static[]              Array of object of the true class of this object
	 */
	public function loadThisMatchingList( $conditions, $ordering = null, $offset = 0, $limit = 0 ) {
		if ( $ordering === null ) {
			$ordering			=	array();
		}
		$this->setMatchingQuery( array( '*' ), $conditions, $ordering, 0, $limit );
		return $this->_loadTrueObjects( $this->_tbl_key );
	}
}	// class cbpaidInstanciator
