<?php
/**
 * @version $Id: cbpaidConfig.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CB paid subscriptions configuration (in sql table)
 *
 */
class cbpaidConfig extends cbpaidTable {
	public $id;
	public $user_id;
	public $type;
	public $last_updated_date;
	public $sequencenumber			=	0;
	public $params;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_config', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 * Removes history logging which is on by default
	 */
	public function noHistoryLogger( ) {
		$this->_historySetLogger( null );
	}
	/*
		/*
		*	Binds an array/hash from database to this object
		*
		*	@param  int $oid  optional argument, if not specifed then the value of current key is used
		*	@return mixed     any result from the database operation
		*
		public function loadThisAsParams( $oid = null ) {
			$result				=	parent::load( $oid );
			if ( $result ) {
				$sqlParams		=	new Registry( $this->params );
				$data			=	$sqlParams->_params;
				unset( $this->params );
				foreach ( array_keys( get_object_vars( $data ) ) as $k ) {
					$this->$k	=	$data->$k;
				}
			}
			return $result;
		}
		/*
		*	binds a named array/hash to this object
		*
		*	@param  array        $hash  named array
		*	@return null|string	        null is operation was satisfactory, otherwise returns an error
		*
		public function bindThisAsParams( $array, $ignore = '' ) {
			$result			=	parent::bind( $array, $ignore );

			unset ( $array['id'] );
			unset ( $array['last_updated_date'] );
			$ignoreArray	=	explode( ' ', $ignore );
			foreach ( $ignoreArray as $v ) {
				$trimedV	=	trim( $v );
				if ( $trimedV ) {
					unset( $array[$trimedV] );
				}
			}

			$id				=	$this->id;

			foreach ( get_object_vars( $this ) as $k => $v ) {
				if( substr( $k, 0, 1 ) != '_' ) {
					unset( $this->$k );
				}
			}

			$this->params				=	cbParamsEditorController::getRawParamsUnescaped( $array, true );
			$this->last_updated_date	=	date( 'Y-m-d H:i:s' );

			if ( $id ) {
				$this->id				=	(int) $id;
			} else {
				$oldConfig				=	new cbpaidConfig( $this->_db );
				if ( $oldConfig->load( 1 ) ) {
					$this->id			=	1;
				}
			}
			return $result;
		}
	*/
}	// class cbpaidConfig
