<?php
/**
* CBSubs (TM): Community Builder Paid Subscriptions Plugin: cbsubscbfield
* @version $Id: cbsubs.cbfield.php 1583 2012-12-24 02:48:46Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage cbsubs.cbfield.php
* @author Beat
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onCPayUserStateChange', 'onCPayUserStateChange', 'getcbsubscbfieldTab' );

/**
 * CBSubs CB Fields integration plugin
 */
class getcbsubscbfieldTab extends cbTabHandler {

	/**
	* gets object with name, title and table corresponding to a field id
	*
	* @param  int  $fieldId
	* @return StdClass|null    null if no match
	*/
	protected function _getFieldInfo( $fieldId ) {
		global $_CB_database;
		
		if ( $fieldId == '0' ) {
			return null;
		}

		$query							=	'SELECT '	. $_CB_database->NameQuote( 'name' )
										.	', '		. $_CB_database->NameQuote( 'table' )
										.	', '		. $_CB_database->NameQuote( 'type' )
										.	"\n FROM "	. $_CB_database->NameQuote( '#__comprofiler_fields' )
										.	"\n WHERE "	. $_CB_database->NameQuote( 'published' ) . " = 1"
										.	"\n AND "	. $_CB_database->NameQuote( 'fieldid' ) . " = "	. (int) $fieldId
										;
		$_CB_database->setQuery( $query );
		$fieldObj						=	null;
		$_CB_database->loadObject( $fieldObj );
		
		return $fieldObj;
	}

	/**
	 * Remove field value from user
	 *
	 * @param  UserTable            $user
	 * @param  FieldTable|StdClass  $field
	 * @param  string               $value
	 * @param  int                  $increment
	 * @param  boolean              $fieldRemoveContent
	 * @return void
	 */
	protected function _removeField( &$user, $field, $value, $increment, $fieldRemoveContent ) {
		global $_CB_database;
		
		$fnam								=	$field->name;
		$f									=	$_CB_database->NameQuote( $fnam );
		
		if ( in_array( $field->type, array( 'multiselect', 'multicheckbox' ) ) ) {
			$valArray						=	explode( '|*|', $value );
			// do directly atomically in database:
			foreach ( $valArray as $v ) {
				$v							=	$_CB_database->getEscaped( $v );
				$query						=	'UPDATE '	. $_CB_database->NameQuote( $field->table )
											.	"\n SET "	. $f . " = "
											.	" IF ( ( LOCATE('" . $v . "', " . $f . ') = 1 ) '
											.	'     AND ( LENGTH(' . $f . ") = LENGTH('" . $v . "')), "
											.	'  INSERT(' . $f . ", 1, LENGTH('" . $v . "'),''), "		// in fact a remove...
											.	"  IF ( LOCATE('" . $v . "|*|', " . $f . ') = 1, '
											.	'    INSERT(' . $f . ", 1, LENGTH('" . $v . "|*|'),''), "	// in fact a remove...
											.	"    IF ( LOCATE('|*|" . $v . "', " . $f . '), '
											.	'		INSERT(' . $f . ", LOCATE('|*|" . $v . "', " . $f . '), '
											.					"LENGTH('|*|" . $v . "'),''), "				// in fact a remove...
											.	$f . ') '
											.	' ) )'
											.	"\n WHERE "	. $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->id
											;
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			}
			// do same in $user structure:
			$fieldValArray					=	explode( '|*|', $user->$fnam );
			foreach ( $valArray as $va ) {
				if ( in_array( $va, $fieldValArray ) ) {
					$k						=	array_search( $va, $fieldValArray );
					unset( $fieldValArray[$k] );
				}
			}
			$user->$fnam					=	implode( '|*|', $fieldValArray );
		} else {
			if ( $fieldRemoveContent ) {
				$fieldvalue					=	$fieldRemoveContent;
			} else {
				switch ( $increment ) {
					case 1:
						$fieldvalue			=	$user->$fnam - $value;
						break;
					case 2:
						/** @noinspection PhpWrongStringConcatenationInspection */
						$fieldvalue			=	$user->$fnam + $value;
						break;
					case 3:
						$fieldvalue			=	$user->$fnam / $value;
						break;
					case 4:
						$fieldvalue			=	$user->$fnam * $value;
						break;
					case 5:
						if ( substr( $user->$fnam, 0, strlen( $value ) ) == $value ) {
							$fieldvalue		=	substr( $user->$fnam, strlen( $value ) );
						} else {
							$fieldvalue		=	$user->$fnam;
						}
						break;
					case 6:
						if ( substr( $user->$fnam, -strlen( $value ) ) == $value ) {
							$fieldvalue		=	substr( $user->$fnam, 0, -strlen( $value ) );
						} else {
							$fieldvalue		=	$user->$fnam;
						}
						break;
					default:
						if ( $field->type == 'integer' ) {
							if ( $user->$fnam == (int) $value ) {
								$fieldvalue	=	'';
							} else {
								$fieldvalue	=	$user->$fnam;
							}
						} elseif ( $field->type == 'checkbox' ) {			// tinyint
							if ( $user->$fnam == 1 ) {
								$fieldvalue	=	0;
							} else {
								$fieldvalue	=	1;
							}
						} elseif ( $field->type == 'date' ) {			// date type
							if ( $user->$fnam == (int) $value ) {
								$fieldvalue	=	'0000-00-00';
							} else {
								$fieldvalue	=	$user->$fnam;
							}
						} else {
							if ( $user->$fnam == $value ) {
								$fieldvalue	=	'';
							} else {
								$fieldvalue	=	$user->$fnam;
							}
						}
						break;
				}
			}
			$query							=	'UPDATE '	. $_CB_database->NameQuote( $field->table )
											.	"\n SET "	. $f . " = " . $_CB_database->Quote( $fieldvalue )
											.	"\n WHERE "	. $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->id
											;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			if ( $user->$fnam == $fieldvalue ) {
				$user->$fnam				=	'';
			}
		}
	}
	
	/**
	 * Adds field value to user
	 *
	 * @param  UserTable            $user
	 * @param  FieldTable|StdClass  $field
	 * @param  string               $value
	 * @param  int                  $increment
	 */
	protected function _addField( &$user, $field, $value, $increment ) {
		global $_CB_database;
		
		$fnam							=	$field->name;
		$f								=	$_CB_database->NameQuote( $fnam );
				
		if ( in_array( $field->type, array( 'multiselect', 'multicheckbox' ) ) ) {
			$valArray					=	explode( '|*|', $value );
			// do directly in database:
			foreach ( $valArray as $vn ) {
				$v						=	$_CB_database->getEscaped( $vn );
				$query					=	'UPDATE '	. $_CB_database->NameQuote( $field->table )
										.	"\n SET "	. $f . " = "
										.	" IF ( (( LOCATE('" . $v . "', " . $f . ') = 1 ) '
										.	'     AND ( LENGTH(' . $f . ") = LENGTH('" . $v . "'))) "
										.	" OR ( LOCATE('" . $v . "|*|', " . $f . ') = 1 ) '
										.	" OR LOCATE('|*|" . $v . "', " . $f . ') , '
										.	$f . ', '
										.	'  IF ( ( ' . $f . ' IS NULL) OR ( LENGTH(' . $f . ') = 0 ), '
										.	"      '" . $v . "', "
										.	'      CONCAT(' . $f . ", '|*|" . $v . "') ) "
										.	' )'
										.	"\n WHERE "	. $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->id
										;
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			}

			// do same in $user structure:
			$fieldValArray				=	explode( '|*|', $user->$fnam );
			foreach ( $valArray as $va ) {
				if ( ! in_array( $va, $fieldValArray ) ) {
					$fieldValArray[]	=	$va;
				}
			}
			$user->$fnam				=	implode( '|*|', $fieldValArray );
		} else {
			switch ( $increment ) {
				case 1:
					/** @noinspection PhpWrongStringConcatenationInspection */
					$fieldvalue			=	$user->$fnam + $value;
					break;
				case 2:
					$fieldvalue			=	$user->$fnam - $value;
					break;
				case 3:
					$fieldvalue			=	$user->$fnam * $value;
					break;
				case 4:
					$fieldvalue			=	$user->$fnam / $value;
					break;
				case 5:
					$fieldvalue			=	$value . $user->$fnam;
					break;
				case 6:
					$fieldvalue			=	$user->$fnam . $value;
					break;
				default:
					$fieldvalue			=	$value;
					break;
			}
			$query						=	'UPDATE '	. $_CB_database->NameQuote( $field->table )
										.	"\n SET "	. $f . " = " . $_CB_database->Quote( $fieldvalue )
										.	"\n WHERE "	. $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->id
										;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$user->$fnam				=	$fieldvalue;
		}
	}

	/**
	 * Called at each change of user subscription state due to a plan activation or deactivation
	 *
	 * @param  UserTable        $user
	 * @param  string           $status
	 * @param  int              $planId
	 * @param  int              $replacedPlanId
	 * @param  ParamsInterface  $integrationParams
	 * @param  string           $cause            'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied'
	 * @param  string           $reason           'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  int              $now              Unix time
	 * @param  cbpaidSomething  $subscription     Subscription/Donation/Merchandise record
	 * @param  int              $autorenewed      0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	public function onCPayUserStateChange( &$user, $status, /** @noinspection PhpUnusedParameterInspection */ $planId, /** @noinspection PhpUnusedParameterInspection */ $replacedPlanId, &$integrationParams, /** @noinspection PhpUnusedParameterInspection */ $cause, /** @noinspection PhpUnusedParameterInspection */ $reason, /** @noinspection PhpUnusedParameterInspection */ $now, &$subscription, /** @noinspection PhpUnusedParameterInspection */ $autorenewed ) {
		if ( ! is_object( $user ) ) {
			return;
		}

		$cbUser							=&	CBuser::getInstance( $user->id );

		if ( ! $cbUser ) {
			$cbUser						=&	CBuser::getInstance( null );
		}

		$extraStrings					=	$subscription->substitutionStrings( false );
		
		for ( $i = 1; $i <= 10; $i++ ) {
			$fieldId					=	$integrationParams->get( 'cbfields_fieldid' . $i, 0 );
			$increment					=	$integrationParams->get( 'cbfields_increment' . $i, 0 );
			$fieldContent				=	$cbUser->replaceUserVars( $integrationParams->get( 'cbfields_contentoffield' . $i, null ), false, false, $extraStrings, false );
			$fieldRemoveOnDeact			=	$integrationParams->get( 'cbfields_removeondeact' . $i, 1 );
			$fieldRemoveContent			=	$integrationParams->get( 'cbfields_removecontent' . $i, 1 );
			$field						=	$this->_getFieldInfo( $fieldId );
			if ( $field !== null ) {
				if ( ( ( $field->type != 'integer' ) && in_array( $increment, array( 1, 2, 3, 4 ) ) ) || ( in_array( $field->type, array( 'multiselect', 'multicheckbox' ) ) && in_array( $increment, array( 5, 6 ) ) ) ) {
					$increment			=	0;
				}

				if ( $status == 'A' ) {
					$this->_addField( $user, $field, $fieldContent, $increment );
				} else {
					if ( $fieldRemoveOnDeact ) {
						$this->_removeField( $user, $field, $fieldContent, $increment, $fieldRemoveContent );
					}
				}
			}
		}
	}
}
