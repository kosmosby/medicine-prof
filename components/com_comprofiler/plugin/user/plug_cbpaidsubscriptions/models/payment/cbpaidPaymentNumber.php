<?php
/**
* @version $Id: cbpaidPaymentNumber.php 1541 2012-11-23 22:21:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to manage unique numbers for invoices, proforma-invoices, quotes, orders and so on.
 * params contain:
 * lastrestart
 */
class cbpaidPaymentNumber extends cbpaidConfig {
	protected $_type;
	protected $_format;

	/**
	 * Database store
	 *
	 * @param  boolean $updateNulls  Update null fields ?
	 * @return boolean
	 */
	public function store( $updateNulls = false ) {
		$this->storeParams();
		return parent::store( $updateNulls );
	}
	/**
	 * Returns a unique incremented number formatted with $format for $type
	 * $format has supported replacements: [NUMBER:n], [YEAR], [YEARSHORT], [MONTH], [DAY], [SITENAME], [SITEURL], [UNIQ_ID] and all $extraStrings and $user_id normal CB replacement strings [cb_fieldname]
	 * 
	 * @param  string  $type          Type of number ('proformainvoice', 'invoice', later: 'quote')
	 * @param  string  $format        Format of number ( e.g. '[NUMBER:1]' )
	 * @param  int     $user_id       User id
	 * @param  array   $extraStrings  Extra strings
	 * @param  int     $increment     Increment for next invoice
	 * @return string                 Formatted number (or NULL if it failed for some reason)
	 */
	public static function generateUniqueNumber( $type, $format, $user_id, $extraStrings, $increment = 1 ) {
		$curNum				=	new self();
		$numberFormat		=	$curNum->_internal_generateUniqueNumber( $type, $format, $increment );
		return self::replaceCbVars( $numberFormat, $user_id, $extraStrings );
	}
	/**
	 * Sets for $type the new starting number $number as the next value
	 * Used by XML with: <param ... onsave="cbpaidPaymentNumber::setStartingNumber" key="invoice" nosave="true">
	 * 
	 * @param  string  $type          Type of number ('proformainvoice', 'invoice', later: 'quote')
	 * @param  int     $number        Number for the next invoice
	 */
	public static function setStartingNumber( $type, $number ) {
		if ( $number ) {
			$curNum				=	new self();
			$curNum->setNextNumber( $type, (int) $number );
		}
	}
	/**
	 * Replaces CB variables and general variables left.
	 * 
	 * @param  string       $numberFormat  Format for the replacements
	 * @param  int          $user_id       User id for user-variables replacements
	 * @param  array        $extraStrings  Set of extra-strings for additional replacements
	 * @return string|null
	 */
	protected static function replaceCbVars( $numberFormat, $user_id, $extraStrings ) {
		global $_CB_framework;

		if ( $numberFormat ) {
			list( $year, $month, $day )	=	self::_getYearMonthDay();

			$uniqid				=	uniqid( '' );

			$extraExtraStrings	=	array(	'YEAR'					=>	$year,
											'YEARSHORT'				=>	substr( $year, 2 ),
											'MONTH'					=>	$month,
											'DAY'					=>	$day,
											'SITENAME'				=>	$_CB_framework->getCfg( 'sitename' ),
											'SITEURL'				=>	$_CB_framework->getCfg( 'live_site' ),
											'UNIQ_ID'				=>	hexdec( substr( $uniqid, 7 ) ) . hexdec( substr( $uniqid, 0, 7 ) ),
											'GROWING_ID'			=>	$uniqid
										 );
			return trim( CBuser::getInstance( $user_id )->replaceUserVars( $numberFormat, false, false, array_merge( $extraExtraStrings, $extraStrings ), false ) );
		} else {
			return null;
		}
	}
	/**
	 * Returns today's date in an array
	 *
	 * @return array  array( year, month, day )
	 */
	protected static function _getYearMonthDay( ) {
		global $_CB_framework;

		return explode( '-', date( 'Y-m-d', $_CB_framework->now() + ( 3600 * $_CB_framework->getCfg( 'offset' ) ) ) );
	}
	/**
	 * INTERNAL METHOD Returns a unique incremented number formatted for $this->_type
	 * @access protected
	 *
	 * @param  string  $type       Type of number ('proformainvoice', 'invoice', later: 'quote')
	 * @param  string  $format     Format of number ( e.g. '[NUMBER:1]' )
	 * @param  int     $increment  Increment for next invoice
	 * @return string              Formatted number (or NULL if it failed for some reason)
	 */
	public function _internal_generateUniqueNumber( $type, $format, $increment ) {
		global $_CB_framework;

		static $maxrecurences	=	4;
		if ( $maxrecurences-- < 0 ) {
			trigger_error( __CLASS__ . '::' . __FUNCTION__ . ': max uses or recursion !', E_USER_WARNING );
			return null;
		}

		$this->_type		=	$type;
		$this->_format		=	$format;

		if ( $this->getNumberFormat() === null ) {
			// No sequential number: no need to increment and store special state for nothing:
			return $format;
		}

		$exists				=	true;
		$changed			=	false;

		$db					=&	$this->_db;

		$maxIterations		=	5;
		while ( $exists && ! $changed && --$maxIterations ) {
			// First check if entry exists (take the first one with lowest id in case of doubt):
			$exists			=	$this->loadThisMatching( array( 'type' => 'number.' . $type ), array( 'id' => 'ASC' ) );
			// loadThisMatching resets object, so re-initialize it:
			$this->_type	=	$type;
			$this->_format	=	$format;
			if ( $exists ) {
				$nextNumber	=	$this->nextNumber();
				// If exists: try to update the entry state that we just got:
				$sql		=	'UPDATE ' . $db->NameQuote( $this->_tbl )
							.	' SET '   . $db->NameQuote( 'sequencenumber' ) . ' = ' . ( $nextNumber ? (int) $nextNumber + $increment : $db->NameQuote( 'sequencenumber' ) . ' + ' . (int) $increment )
							.	', last_updated_date = ' . $db->Quote( date( 'Y-m-d H:i:s', $_CB_framework->now() ) )
							.	( $nextNumber ? ', params = ' . $db->Quote( $this->params ) : '' )
							.	' WHERE ' . $db->NameQuote( 'type' ) . ' = ' . $db->Quote( $this->type )
							.	' AND ' . $db->NameQuote( 'sequencenumber' ) . ' = ' . (int) $this->sequencenumber;
				$db->setQuery( $sql );
				$db->query();

				// Check if we did update:
				$changed	=	$db->getAffectedRows();

				if ( $changed ) {
					if ( $nextNumber ) {
						$this->sequencenumber	=	$nextNumber;
					}
				} else {
					// If it exists AND we didn't change it: loop in while: we re-fetch new current entry and re-try updating.
					usleep( rand( 1, 100 ) * 10000 );		// sleep 0.01 to 1 second randomly, hoping to get our turn
				}
			}
		}

		$inserted			=	false;
		if ( ( ! $exists ) && $maxIterations ) {
			// Entry never existed, create new one:
			$this->user_id				=	0;
			$this->type					=	'number.' . $type;
			$this->last_updated_date	=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
			$this->sequencenumber		=	$this->generateNewNumber();
			$this->initParams();

			$maxIterations				=	5;
			while ( ( ! $inserted ) && $maxIterations-- ) {
				// Let's try to store it with next sequence number to use:
				$this->sequencenumber	+=	$increment;
				$this->store();
				$this->sequencenumber	-=	$increment;

				// Let's see how many we have:
				$sql					=	'SELECT COUNT(' . $db->NameQuote( 'id' ) . ') AS ' . $db->Quote( 'numberscount' ) . ', MIN(' . $db->NameQuote( 'id' ) . ') AS ' . $db->Quote( 'winningid' )
										.	' FROM ' . $db->NameQuote( $this->_tbl )
										.	' WHERE ' . $db->NameQuote( 'type' ) . ' = ' . $db->Quote( $this->type );
				$db->setQuery( $sql );
				$object					=	null;
				if ( $db->loadObject( $object ) ) {
					/** @var StdClass $object */
					if ( $object->numberscount == 1 ) {
						// Inserted uniquely: All ok:
						$inserted		=	true;
					} elseif ( $object->numberscount > 1 ) {
						if ( $object->winningid == $this->id ) {
							// Not inserted uniquely, but we are winning:
							$inserted	=	true;

							// We won, delete the others just in case to be sure:
							$sql		=	'DELETE FROM ' . $db->NameQuote( $this->_tbl )
										.	' WHERE ' . $db->NameQuote( 'type' ) . ' = ' . $db->Quote( $this->type )
										.	' AND ' . $db->NameQuote( 'id' ) . ' > ' . (int) $this->id;
							$db->setQuery( $sql );
						} else {
							// We lost concurrent insert: delete $this and try to update (calling ourselves recursively):
							$this->delete();
							$this->id	=	null;
							return $this->_internal_generateUniqueNumber( $type, $format, $increment );
						}
					} else {
						// Something strange happened: we stored a number but then didn't find ANY !
						trigger_error( __CLASS__ . '::' . __FUNCTION__ . ': Inserted number but cannot find it', E_USER_WARNING );
						// Try again inserting...
					}
				} else {
					// Could not load counting object: Fatal error.
					trigger_error( __CLASS__ . '::' . __FUNCTION__ . ': Error on count: ' . $db->getErrorMsg(), E_USER_ERROR );
				}

				if ( ! $inserted ) {
					// Loop again if inserted not happened.
					usleep( rand( 1, 100 ) * 10000 );		// sleep 0.01 to 1 second randomly, hoping to get our turn
				}
			}
		}
		if ( $changed || $inserted ) {
			// We successfully have a unique number in $this->sequencenumber: Let's get it in a formatted way: 
			return $this->formatNumber( $this->sequencenumber );
		} else {
			return null;
		}
	}
	/**
	 * Sets as next number $nextNumber for $type
	 * 
	 * @param  string  $type        Type of number ('proformainvoice', 'invoice', later: 'quote')
	 * @param  int     $nextNumber  [optional] Force next number to this
	 * @return boolean              Result of store() in database
	 */
	protected function setNextNumber( $type, $nextNumber ) {
		global $_CB_framework;

		$exists			=	$this->loadThisMatching( array( 'type' => 'number.' . $type ), array( 'id' => 'ASC' ) );
		if ( ! $exists ) {
			// Entry never existed, create new one:
			$this->user_id			=	0;
			$this->type				=	'number.' . $type;
		}
		$this->last_updated_date	=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
		$this->sequencenumber		=	(int) $nextNumber;
		$this->initParams();
		return $this->store();
	}
	/**
	 * returns the initial number for $this->_type depending on the settings
	 * 
	 * @return int            Initial number
	 */
	protected function generateNewNumber( ) {
		$numberTemplate		=	$this->getNumberFormat();
		if ( $numberTemplate === null ) {
			return 1;
		} else {
			return (int) $numberTemplate;
		}
	}
	/**
	 * Initializes params if needed
	 */
	protected function initParams( ) {
		$nowYmd			=	self::_getYearMonthDay();
		$nowYmdString	=	sprintf('%s-%s-%s', $nowYmd[0], $nowYmd[1], $nowYmd[2] );
		$this->setParam( 'lastrestart', $nowYmdString );
		$this->storeParams();
	}
	/**
	 * Checks if sequence needs to be restarted, and if yes, gives the new sequence number.
	 *
	 * @return  int|null  Next sequence number, or NULL: just continue with sequence incrementing
	 */
	protected function nextNumber( ) {
		$refreshrate		=	$this->getNumberFormat( true );
		if ( $refreshrate ) {
			$nowYmd			=	self::_getYearMonthDay();
			$nowYmdString	=	sprintf('%s-%s-%s', $nowYmd[0], $nowYmd[1], $nowYmd[2] );
			$lastYmd		=	explode( '-', $this->getParam( 'lastrestart', $nowYmdString ) );

			$index			=	array( 'YEARLY' => 0, 'MONTHLY' => 1, 'DAILY' => 2 );
			for ( $i = $index[$refreshrate] ; $i >= 0 ; $i-- ) {
				if ( $lastYmd[$i] != $nowYmd[$i] ) {
					// We need a new number and to update the params:
					$this->setParam( 'lastrestart', $nowYmdString );
					$this->storeParams();
					return (int) $this->getNumberFormat();
				}
			}
		}
		// just continue with sequence:
		return null;
	}
	/**
	 * Returns number format (e.g. 1, or 000001)
	 *
	 * @param  boolean      $getRefreshRate
	 * @return string|null
	 */
	protected function getNumberFormat( $getRefreshRate = false ) {
		$newNumberFormat	=	$this->getNumberParam();
		$matches			=	null;
		if ( preg_match( '/\[NUMBER(?::(\d+))?(?::RESTART:(YEARLY|MONTHLY|DAILY))?\]/', $newNumberFormat, $matches ) ) {
			$ret			=	isset( $matches[$getRefreshRate ? 2 : 1] ) ? $matches[$getRefreshRate ? 2 : 1] : null;
			if ( ( ! $getRefreshRate ) && ( $ret == null ) ) {
				$ret		=	1;
			}
			return $ret;
		} else {
			return null;
		}
	}
	/**
	 * Formats the number according to settings
	 * 
	 * @param  int     $number   Number to put
	 * @return string            Formatted number
	 */
	protected function formatNumber( $number ) {
		$format				=	'%0' . strlen( $number ) . 'd';		// So 0001 formats 12 as 0012 e.g.
		$templatedNumber	=	sprintf( $format, $number );
		$numberFormat		=	$this->getNumberParam();
		$numberFormated		=	preg_replace( '/\[NUMBER(?::\d+)?(?::RESTART:(?:YEARLY|MONTHLY|DAILY))?\]/', $templatedNumber, $numberFormat );
		return $numberFormated;
	}
	/**
	 * Gets the formatting string for the number of $this->_type
	 * 
	 * @return string           Number format
	 */
	protected function getNumberParam( ) {
		return $this->_format;	// This is typically the param of 'invoice_number_format', 'proformainvoice_number_format'
	}
}
