<?php
/**
 * @version $Id: cbpaidHistory.php 1551 2012-12-03 10:52:03Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Database\Table\TableInterface;
use CBLib\Registry\ParamsInterface;
use CBLib\Xml\SimpleXMLElement;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CB paid logs (in sql table)
 *
 */
class cbpaidHistory extends cbpaidTable {
	public $id;
	/** Type of log: 1: php error, 2: change-log, 3: user activity, 7: payment gateway activity.
	 * @var int */
	public $event_type;
	public $message;
	public $table_name;
	public $table_key_id;
	public $field_changed;
	public $change_type;
	public $old_value;
	public $new_value;
	public $event_time;
	public $user_id;
	public $ip_addresses;
	/** Priority of message (UNIX-type): 0: Emergency, 1: Alert, 2: Critical, 3: Error, 4: Warning, 5: Notice, 6: Info, 7: Debug.
	 * @var int */
	public $log_priority;
	public $log_version;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_history', 'id', $db );
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
		global $_CB_framework;

		$key						=	$this->_tbl_key;
		if ( ! $this->$key ) {
			$this->event_time		=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
			$this->user_id			=	$_CB_framework->myId();
			$this->ip_addresses		=	cbpaidRequest::getIPlist();
			$this->log_version		=	1;
		}
		return parent::store( $updateNulls );
	}
	/**
	 * Logs error into logs database
	 *
	 * @param  int               $log_priority  Priority of message (UNIX-type): 0: Emergency, 1: Alert, 2: Critical, 3: Error, 4: Warning, 5: Notice, 6: Info, 7: Debug
	 * @param  string            $message       The error message (simple non-html text only)
	 * @param  cbpaidTable|null  $object        Object stored in database, so that table name of table and id of key can be stored with the error
	 * @return void
	 */
	public function logError( $log_priority, $message, $object ) {
		$this->event_type			=	1;
		$this->log_priority			=	(int) $log_priority;
		$this->message				=	$message;
		if ( $object instanceof TableInterface ) {
			$this->table_name			=	$object->getTableName();
			$k							=	$object->getKeyName();
			$this->table_key_id			=	(int) $object->$k;
		} elseif ( is_object( $object ) && isset( $object->_tbl ) && isset( $object->_tbl_key ) ) {
			$this->table_name			=	$object->_tbl;
			$k							=	$object->_tbl_key;
			$this->table_key_id			=	(int) $object->$k;
		}
		$errorNum					=	$this->_db->getErrorNum();
		$errorMsg					=	$this->_db->getErrorMsg();
		$this->store();
		$this->_db->setErrorNum( $errorNum );
		$this->_db->setErrorMsg( $errorMsg );
	}
	/**
	 * Logs error into logs database
	 *
	 * @param  string  $message        The error message (simple non-html text only)
	 * @param  string  $change_type    max 16 chars: The type of change
	 * @param  string  $table_name     Table name of table with the prefix ( '#__' )
	 * @param  int     $table_key_id   Primary key id in the table
	 * @param  string  $field_changed  Name of column of the field which has been changed
	 * @param  string  $old_value      Old value
	 * @param  string  $new_value      New value
	 */
	public function logChangeEvent( $message = '', $change_type = '', $table_name = '', $table_key_id = null, $field_changed = '', $old_value = null, $new_value = null ) {
		$this->event_type			=	2;
		$this->log_priority			=	6;
		$this->message				=	$message;
		$this->change_type			=	$change_type;
		$this->table_name			=	$table_name;
		$this->table_key_id			=	(int) $table_key_id;
		$this->field_changed		=	$field_changed;
		$this->old_value			=	$old_value;
		$this->new_value			=	$new_value;
		// $errorNum					=	$this->_db->getErrorNum();
		// $errorMsg					=	$this->_db->getErrorMsg();
		$this->store();
		//protected in j1.6: $this->_db->setErrorNum( $errorNum );
		//protected in j1.6: $this->_db->setErrorMsg( $errorMsg );
	}
	/**
	 * BACKEND-ONLY XML RENDERING METHODS:
	 */

	/**
	 * USED by XML interface ONLY !!! Renders main currency conversion rates
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderDiff( /** @noinspection PhpUnusedParameterInspection */ $value, &$params ) {
		global $_CB_framework;
		$html					=	null;
		if ( $this->old_value || $this->new_value ) {
			$oldxml				=	@new SimpleXMLElement( '<?xml version="1.0" encoding="' . $_CB_framework->outputCharset() . '"?>' . ( $this->old_value ? $this->old_value : '<empty />' ) );
			$newxml				=	@new SimpleXMLElement( '<?xml version="1.0" encoding="' . $_CB_framework->outputCharset() . '"?>' . ( $this->new_value ? $this->new_value : '<empty />' ) );
			if ( is_object( $oldxml ) && is_object( $newxml ) ) {
				$diffArray		=	array();
				foreach ( $newxml->children() as $v ) {
					/** @var $v SimpleXMLElement */
					$diffArray[$v->attributes( 'name')]['n']	=	$v->attributes( 'value' );
				}
				foreach ( $oldxml->children() as $v ) {
					$diffArray[$v->attributes( 'name')]['o']	=	$v->attributes( 'value' );
				}
				if ( count( $diffArray ) > 0 ) {
					$html		=	str_replace( "\n", '', $this->_diffTableHtml( $diffArray ) );
				}
			} else {
				$html			=	'Error in difference XML';
			}
		}
		return $html;
	}
	/**
	 * Renders an HTML table with the differences in $diffArry
	 *
	 * @param  array  $diffArry
	 * @return string
	 */
	protected function _diffTableHtml( $diffArry ) {
		$tr  = '<table summary="' . CBPTXT::T("Changes") . '" class="cbregChangeDiffs">'."\n";
		$tr .= " <thead>\n";
		$tr .= "  <tr>\n";
		$tr .= '    <th scope="col" class="cbregDiffHF">';
		$tr .=			CBPTXT::T("Field");
		$tr .=		"</th>\n";
		$tr .= '    <th scope="col" class="cbregDiffHO">';
		$tr .=			CBPTXT::T("Old value");
		$tr .=		"</th>\n";
		$tr .= '    <th scope="col" class="cbregDiffHN">';
		$tr .=			CBPTXT::T("New value");
		$tr .=		"</th>\n";
		$tr .= "  </tr>\n";
		$tr .= " </thead>\n";
		$tr .= " <tbody>\n";
		foreach ( $diffArry as $k => $v ) {
			$tr .= "  <tr>\n";
			$tr .= '    <th scope="row">';
			$tr .= 			htmlspecialchars( $k );
			$tr .= 		"</th>\n";
			$tr .= '    <td class="cbregDiffold">';
			$tr .= 			( isset( $v['o'] ) ? htmlspecialchars( $v['o'] ) : '' );
			$tr .= 		"</td>\n";
			$tr .= '    <td class="cbregDiffnew">';
			$tr .= 			( isset( $v['n'] ) ? htmlspecialchars( $v['n'] ) : '' );
			$tr .= 		"</td>\n";
			$tr .= "  </tr>\n";
		}
		$tr .= " </tbody>\n";
		$tr .= "</table>\n";
		return $tr;
	}
}	// class cbpaidHistory
