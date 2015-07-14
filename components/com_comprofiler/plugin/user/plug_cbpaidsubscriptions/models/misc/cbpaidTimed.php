<?php
/**
 * @version $Id: cbpaidTimed.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Base class for timed items
 *
 */
abstract class cbpaidTimed extends cbpaidTable {
	public $validity;
	public $bonustime;
	/**
	 * Constructor
	 *
	 *	@param string      $table  name of the table in the db schema relating to child class
	 *	@param string      $key    name of the primary key field in the table
	 *	@param CBdatabase  $db     CB Database object
	 */
	public function __construct(  $table, $key, &$db = null ) {
		parent::__construct(  $table, $key, $db );
	}
	/**
	 * Get an attribute of this timed item
	 *
	 * @param  string  $column
	 * @param  string  $default
	 * @return mixed
	 */
	public function get( $column, $default = null ) {
//		if ( ! property_exists( $this, $column ) ) {
//			trigger_error( 'cbpaidTimed::get ("' . htmlspecialchars( $column ) . '") innexistant attribute.', E_USER_ERROR );
//		}
		return parent::get( $column, $default );
	}
	/**
	 * Set the value of the attribute of this timed item
	 *
	 * @param  string  $column  Name of attribute
	 * @param  mixed   $value   Nalue to assign to attribute
	 */
	public function set( $column, $value ) {
		if ( ! property_exists( $this, $column ) ) {
			trigger_error( 'cbpaidTimed::set ("' . htmlspecialchars( $column ) . '") innexistant attribute.', E_USER_ERROR );
		}

		parent::set( $column, $value );
	}
	/**
	 * Fix variable name 'first_validity' to 'validity' if there is no different first period
	 *
	 * @param  string  $varName   'first_validity' or 'validity'   !!! CHANGES (FIXES) THAT VAR NAME
	 */
	abstract public function fixVarName( &$varName );
	/**
	 * TIMING METHODS:
	 */
	/**
	 * Transforms a SQL-formatted datetime to a unix time
	 *
	 * @param  string   $sqlDateStr  'YYYY-MM-DD HH:II:SS' or NULL
	 * @return int                   unix-time
	 */
	public function strToTime( $sqlDateStr ) {
		//TBD later: $param being missing, can't do this way:
		// $cbpaidTimes	=&	cbpaidTimes::getInstance();
		// return $cbpaidTimes->strToTime( $sqlDateStr );

		if ( ( $sqlDateStr === null ) || ( $sqlDateStr === '0000-00-00 00:00:00' ) ) {
			$time = null;
		} else {
			list($y, $c, $d, $h, $m, $s) = sscanf($sqlDateStr, '%d-%d-%d %d:%d:%d');
			$time = mktime($h, $m, $s, $c, $d, $y);			// we do NOT use PHP strtotime, which is broken
		}
		return $time;
	}
	/**
	 * Computes the real start and expiry time of the timed item based on start-time, first or succeeding periods, and,
	 * if succeeding periods the number of occurrences.
	 * WARNING: changes input $startTime by reference to reflect real starting time.
	 *
	 * @param  int     $startTime    RETURNS ALSO: IN: Unix-time of start OUT: Unix-time of REAL START (BONUS TIMES APPLY)
	 * @param  string  $varName      'first_validity' or 'validity'
	 * @param  int     $occurrences  number of occurrences
	 * @param  string  $reason       payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return int                   Unix-time
	 */
	public function getExpiryTime( &$startTime, $varName, $occurrences, $reason ) {
		global $_CB_framework;

		$this->fixVarName( $varName );

		if ( $this->isLifetimeValidity() ) {
			// lifetime:
			$expiryTime		=	null;
		} elseif ( $this->isCalendarValidity( $varName ) ) {
			// Calendar-based expiries:
			$offset			=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
			$startTime		=	$startTime + $offset + 1;	// +1 to compensate for expiry at 23:59:59 instead of 00:00:00.
			list($yn, $cn, $dn, $hn, $mn, $sn)	=	sscanf( date( 'Y-m-d H:i:s', $startTime ), '%d-%d-%d %d:%d:%d' );

			if ( $reason != 'R' ) {
				$bonustime = $this->get( 'bonustime' );
				// advance startTime by bonusTime, it should change expiry only when it really should:
				list($yy, $cc, $dd, $hh, $mm, $ss)	=	sscanf( $bonustime, '%d-%d-%d %d:%d:%d' );
				$yn = $yn + $yy;	$cn = $cn + $cc;	$dn = $dn + $dd;
				$hn = $hn + $hh;	$mn = $mn + $mm;	$sn = $sn + $ss;
				$startTime		=	mktime($hn, $mn, $sn, $cn, $dn, $yn) - $offset;		// adjusts startTime to the real start time.
				list($yn, $cn, $dn, $hn, $mn, $sn)	=	sscanf( date( 'Y-m-d H:i:s', $startTime ), '%d-%d-%d %d:%d:%d' );
			}
			list($y, $c, $d, /* $h */, /* $m */, /* $s */ )		=	$this->getValidity( $varName ); // = sscanf( substr( $this->get( $varName ), 2 ), '%d-%d-%d %d:%d:%d');
			if ( $occurrences !== 1 ) {
				$y *= $occurrences; $c *= $occurrences; $d *= $occurrences; /* $h *= $occurrences; $m *= $occurrences; $s *= $occurrences; */
			}
			if ( $y ) {			// calendar years:			//TODO: specified MONTH or, better DATE
				list( $cs, $ds )	=	explode( '-', $this->calendarYearStart( $varName ) );
				if ( ( $cs == 1 ) && ($ds == 1 ) ) {
					// don't break what works:
					$ye = $yn + ( $y - 1 + 1 );
					$ce = 1;   $de = 0; $he = 23; $me = 59; $se = 59;		// with mktime, this will return december of previous year
					$cn = 1;   $dn = 1; $hn = 0;  $mn = 0;  $sn = 0;			// jan 1st of current year
				} else {
					if ( ( $cs < $cn ) || ( ( $cs == $cn ) && ( $ds <= $dn ) ) ) {
						// calendar year start before current month/day:
						;		// same algo as above
					} else {
						// calendar year start after current month/day: means we are in the previous calendar year, starting last year:
						$yn--;
					}
					$ye = $yn + ( $y - 1 + 1 );
					$ce = $cs; $de = $ds-1; $he = 23; $me = 59; $se = 59;	// with mktime, this will return last second of previous year
					$cn = $cs; $dn = $ds;   $hn = 0;  $mn = 0;  $sn = 0;		// 1st day of current calendar year
				}
			} elseif ( $c ) {	// calendar months:
				$ce = $cn + ( $c - 1 + 1 );
				$ye = $yn;            $de = 0; $he = 23; $me = 59; $se = 59;		// with mktime, this will return last day of previous month
				$dn = 1; $hn = 0;  $mn = 0;  $sn = 0;			// 1st of current month
			} elseif ( $d ) {	// calendar days:
				$de = $dn + ( $d - 1 );
				$ye = $yn; $ce = $cn;          $he = 23; $me = 59; $se = 59;		// this will just be end of day
				$hn = 0;  $mn = 0;  $sn = 0;			// start of current day
			} else {
				trigger_error( 'getExpiryTime detected Calendar duration of less than a day', E_USER_NOTICE );
				// Well, let's guess 1 calendar year in this case, so we don't have undefineds:
				$ye = $yn + 1;
				$ce = 1;   $de = 0; $he = 23; $me = 59; $se = 59;					// with mktime, this will return december of previous year
			}

			$expiryTime		=	mktime($he, $me, $se, $ce, $de, $ye) - $offset;
			$startTime		=	mktime($hn, $mn, $sn, $cn, $dn, $yn) - $offset;		// adjusts startTime to the real start time.
		} else {
			// Subscription-time based expiries:
			list($y, $c, $d, $h, $m, $s)	=	$this->getValidity( $varName );	// = sscanf($this->get( $varName ), '%d-%d-%d %d:%d:%d');
			if ( $occurrences !== 1 ) {
				$y *= $occurrences; $c *= $occurrences; $d *= $occurrences; $h *= $occurrences; $m *= $occurrences; $s *= $occurrences;
			}
			if ($y+$c+$d+$h+$m+$s == 0) {
				$expiryTime =	null; 	// Lifetime subscription
			} else {
				$offset			=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
				$localStartTime	=	$startTime + $offset + 1;	// +1 to compensate for expiry at 23:59:59 instead of 00:00:00.
				$expiryTime		=	mktime(date('H', $localStartTime)+$h, date('i', $localStartTime)+$m, date('s', $localStartTime)+$s,
					date('m', $localStartTime)+$c, date('d', $localStartTime)+$d, date('Y', $localStartTime)+$y);
				$expiryTime		=	$expiryTime - $offset - 1;	// -1 to compensate for expiry at 23:59:59 instead of 00:00:00.
			}
		}
		return $expiryTime;
	}
	/**
	 * Enter description here...
	 *
	 * @param  string  $validity  time to subsctract ( 'Y-m-d H:i:s' format)
	 * @param  int     $time      UNIX-time
	 * @return int                UNIX-time
	 */
	public function substractValidityFromTime( $validity, $time ) {
		global $_CB_framework;

		if ( ( ! $validity ) || ( $validity == '0000-00-00 00:00:00' ) ) {
			return $time;
		} else {
			list($y, $c, $d, $h, $m, $s)	=	sscanf( $validity, '%d-%d-%d %d:%d:%d' );
			$offset				=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
			$startTime			=	$time + $offset + 1;	// +1 to compensate for expiry at 23:59:59 instead of 00:00:00.
			$expiryTime			=	mktime( date('H', $startTime) - $h, date('i', $startTime) - $m, date('s', $startTime) - $s,
				date('m', $startTime) - $c, date('d', $startTime) - $d, date('Y', $startTime) - $y );
			$expiryTime			=	$expiryTime - $offset - 1;	// -1 to compensate for expiry at 23:59:59 instead of 00:00:00.
			return $expiryTime;
		}
	}
	/*
	 * Returns the number of days in a month (handles leap-years)
	 *
	 * @param  int $someYear    Year  1900 - 9999
	 * @param  int $someMonth   Month 1 - 12
	 * @return int              Days in that month (28-31)
	public function getDayInMonthsCount( $someYear, $someMonth) {
		static $calMonthDays	=	array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		if ( ( $someMonth == 2 ) && ( ( ( $someYear % 400 ) == 0 ) || ( ( ( $someYear % 4 ) == 0 ) && ( ( $someYear % 100 ) != 0 ) ) ) ) {
			return 29;
		} else {
			return $calMonthDays[$someMonth - 1];
		}
	}
	 */

	/**
	 * says if validity is unlimitted
	 * OVERRIDE ! this tests only on validity !
	 *
	 * @return boolean	             true if lifetime, false if limitted time
	 */
	public function isLifetimeValidity() {
		return  ( $this->get( 'validity' ) == '0000-00-00 00:00:00' );
	}
	/**
	 * says if validity is calendar-based
	 *
	 * @param  string  $varName     variable name ( 'validity' or 'first_validity' )
	 * @return boolean              true if calendar-based, false if time-based
	 */
	public function isCalendarValidity( $varName ) {
		$this->fixVarName( $varName );
		return  ( strncasecmp( 'U:', $this->get( $varName ), 2 ) == 0 );
	}
	/**
	 * Gives Calendar Year start
	 *
	 * @param  string  $varName      'first_validity' or 'validity'
	 * @return string                'month-day', e.g. '01-01'
	 */
	public function calendarYearStart( /** @noinspection PhpUnusedParameterInspection */ $varName ) {
		return '01-01';
	}
	/**
	 * returns validity period as years, months, days, hours, minutes, seconds.
	 *
	 * @param  string  $varName     variable name ( 'validity' or 'first_validity' )
	 * @return array of int	        list( $years, $months, $days, $hours, $minutes, $seconds )
	 */
	public function getValidity( $varName ) {
		$this->fixVarName( $varName );
		if ( $this->isCalendarValidity( $varName ) ) {
			return sscanf( substr( $this->get( $varName ), 2 ), '%d-%d-%d %d:%d:%d');
		} else {
			// Subscription-time based expiries:
			return sscanf($this->get( $varName ), '%d-%d-%d %d:%d:%d');
		}
	}
	/**
	 * Returns the period of validitiy from startTime on in seconds
	 *
	 * @param  int     $startTime    starting time in unix time.
	 * @param  string  $varName      variable name ( 'validity' or 'first_validity' )
	 * @return int                   seconds of subscription or null for lifetime (no expiration)
	 */
	public function getFullPeriodValidityTime( $startTime = null, $varName ) {
		global $_CB_framework;

		$this->fixVarName( $varName );

		if ( $this->isLifetimeValidity() ) {
			// lifetime:
			$validityPeriodTime = null;
		} else {
			list($y, $c, $d, $h, $m, $s) = $this->getValidity( $varName );
			if ($y+$c+$d+$h+$m+$s == 0) {
				$validityPeriodTime = null; 	// Lifetime subscription
			} else {
				if ( $startTime === null ) {
					$startTime		= $_CB_framework->now();
				}
				$validityPeriodTime = mktime(date('H', $startTime)+$h, date('i', $startTime)+$m, date('s', $startTime)+$s,
					date('m', $startTime)+$c, date('d', $startTime)+$d, date('Y', $startTime)+$y)
					- $startTime;
			}
		}
		return $validityPeriodTime;
	}
	/**
	 * Checks if a given expiry date has passed a given time
	 *
	 * @param  string    $expiryDate   SQL-formatted expiry date or NULL for non-expiring item
	 * @param  int       $time         UNIX-formatted time (default: now)
	 * @return boolean                 TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkValid( $expiryDate, $time = null ) {
		if ( $this->isLifetimeValidity() || ( $expiryDate == null ) ) {
			// lifetime:
			return true;
		} else {
			$expiryTime = $this->strToTime( $expiryDate );
			if ( $time === null ) {
				global $_CB_framework;
				$time	=	$_CB_framework->now();
			}
			return ( $time < $expiryTime);
		}
	}

	/**
	 * RENDERING METHODS:
	 */

	/**
	 * Returns formatted time period ( xxx weeks , or xxx years xxx months xxx days xxx hours xxx minutes xxx seconds
	 *
	 * @param  int[]    $ycdhmsArray  = list( $years, $months, $days, $hours, $minutes, $seconds )
	 * @param  int      $occurrences  [default: 1] multiply period by the occurrences before displaying
	 * @param  boolean  $displayOne   [default: true] displays also if only 1 unit of something
	 * @param  string   $prefix       text between number and period, e.g. 3 calendar months
	 * @return string
	 */
	public function renderPeriod( $ycdhmsArray, $occurrences = 1, $displayOne = true, $prefix = '' ) {
		$cbpaidTimes	=&	cbpaidTimes::getInstance();
		return $cbpaidTimes->renderPeriod( $ycdhmsArray, $occurrences, $displayOne, $prefix );
	}
	/**
	 * Renders a calendar or time-period validity period, e.g. Year 2007, March - May 2007, December 2006 - January 2007, etc.
	 *
	 * @param  int      $startTime    Unix-time
	 * @param  string   $varName      variable name ( 'validity' (default) or 'first_validity' )
	 * @param  string   $reason       payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update (needed only if $expiryTime is NULL)
	 * @param  int      $occurrences  number of occurrences (needed only if $expiryTime is NULL)
	 * @return string
	 */
	public function getFormattedExpiryDate( $startTime, $varName, $reason = null, $occurrences = 1 ) {
		global $_CB_framework;

		$params		=	cbpaidApp::settingsParams();

		$this->fixVarName( $varName );

		if ( $startTime === null ) {
			$startTime	=	$_CB_framework->now();
		}
		$expiryTime		=	$this->getExpiryTime( $startTime, $varName, $occurrences, $reason );		// WARNING: adjusts $startTime to the real Start-time, which is wanted here
		if ( $expiryTime ) {
			$showtime	=	( $params->get( 'showtime', '1' ) == '1' );
			$text		=	cbFormatDate( $expiryTime, 1, $showtime );
		} else {
			$text		=	CBPTXT::T( $params->get( 'regtextLifetime', 'Lifetime Subscription' ) );
		}
		return $text;
	}
	/**
	 * Renders a calendar or time-period validity period, e.g. Year 2007, March - May 2007, December 2006 - January 2007, etc.
	 *
	 * @param  int|null  $expiryTime   Unix-time
	 * @param  int|null  $startTime    Unix-time
	 * @param  string    $varName      variable name ( 'validity' (default) or 'first_validity' )
	 * @param  string    $reason       payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update (needed only if $expiryTime is NULL)
	 * @param  int       $occurrences  number of occurrences (needed only if $expiryTime is NULL)
	 * @param  boolean   $displayOne   Display significant 1s also if it's 1: e.g. TRUE: 1 year, FALSE: year
	 * @param  boolean   $html         true: Display for html with non-breaking spaces
	 * @return string
	 */
	public function getFormattedValidity( $expiryTime, $startTime, $varName, $reason = null, $occurrences = 1, $displayOne = true, $html = false ) {
		global $_CB_framework;

		$this->fixVarName( $varName );

		$text = '';
		if ( $this->isCalendarValidity( $varName ) ) {
			$offset		=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
			$now		=	$_CB_framework->now();
			if ( $startTime === null ) {
				$startTime = $now;
			}
			if ( $expiryTime === null ) {
				$expiryTime = $this->getExpiryTime( $startTime, $varName, $occurrences, $reason );		// WARNING: adjusts $startTime to the real Start-time, which is wanted here
			}
			$now		+= $offset;
			$startTime	+= $offset;
			$expiryTime	+= $offset;
			$isValid	= ( /*removed otherwise renew buttons display wrong: ( $startTime <= $now  ) && */ ( $now < $expiryTime ) );
			list( $y, $c, $d, /* $h */, /* $m */, /* $s */ ) = $this->getValidity( $varName );	// = sscanf( substr( $this->get( $varName ), 2 ), '%d-%d-%d %d:%d:%d' );
			list( $yn, $cn, $dn )	=	sscanf( date( 'Y-m-d', $now ),		  '%d-%d-%d' );
			list( $ys, $cs, $ds )	=	sscanf( date( 'Y-m-d', $startTime ),  '%d-%d-%d' );
			list( $ye, $ce, $de )	=	sscanf( date( 'Y-m-d', $expiryTime ), '%d-%d-%d' );
			$calStart	=	$this->calendarYearStart( $varName );
			if ( $y && ( $calStart == '01-01' ) ) {
				if ( ( $y == 1 ) && ( $ye <= ( $ys + 1 ) ) ) {
					$text .= sprintf( $this->_htmlNbsp( CBPTXT::T("Year %s"), $html ), $ye );						// 'Year 2007'
					if ( $ye != $yn && $isValid ) {
						$text .= ' (' . CBPTXT::T("valid from now on") . ')';
					}
				} else {
					$years = $ye - $ys + 1;
					if ( ( ! $isValid ) || ( ( $y == $years ) && ( $ys == $yn ) ) ) {
						$text .= sprintf( $this->_htmlNbsp( CBPTXT::T("Years %s - %s"), $html ), $ys, $ye );		// 'Years 2006 - 2007'
					} else {
						if ( $y == $years ) {
							$text .= sprintf( $this->_htmlNbsp( CBPTXT::T("Years %s - %s"), $html ), $ys, $ye );	// 'Years 2007 - 2008'
						} else {
							$text .= sprintf( $this->_htmlNbsp( CBPTXT::T("Years %s - %s"), $html ), $ys + 1, $ye ); // 'Years 2007 - 2008'
						}
						$text .= ' (' . CBPTXT::T("valid from now on") . ')';
					}
				}
			} elseif ( $c || $y ) {
				if ( ( $calStart != '01-01' ) && ! preg_match( '/$..-01/', $calStart ) ) {
					// $text .= $calStart . date( 'Y-m-d H:i:s', $startTime ) .( $c + ( $y * 12 ) ) . '_' . ($ce - $cs + 1 + ( ( $ye - $ys ) * 12 )) . '_';
					$text .= CBPTXT::Tdate( 'j F', $startTime) . ( ( $ys != $ye ) ? $this->_htmlNbsp( ' ', $html ) . $ys : '' );	// 'January' or 'December 2006'
					$text .= $this->_htmlNbsp( ' - ', $html );														// ' - '
					$text .= CBPTXT::Tdate( 'j F', $expiryTime) . $this->_htmlNbsp( ' ', $html ) . $ye;						// 'February 2007'
					if ( ( ! ( ( $cs == $cn ) && ( $ys == $yn ) ) ) && $isValid ) {
						$text .= ' (' . CBPTXT::T("valid from now on") . ')';
					}
				} else {
					$months = $ce - $cs + 1 + ( ( $ye - $ys ) * 12 );
					if ( ( ( $c == 1 ) && ( $y == 0 ) ) || ( $months == 1 ) ) {
						$text .= CBPTXT::Tdate( 'F', $expiryTime) . $this->_htmlNbsp( ' ', $html ) . $ye;		// 'January 2007'
						if ( $ce != $cn  && $isValid ) {
							$text .= ' (' . CBPTXT::T("valid from now on") . ')';
						}
					} else {
						// if ( ( $c + ( $y * 12 ) ) == $months ) {
						$text .= CBPTXT::Tdate( 'F', $startTime) . ( ( $ys != $ye ) ? $this->_htmlNbsp( ' ', $html ) . $ys : '' );	// 'January' or 'December 2006'
						$text .= $this->_htmlNbsp( ' - ', $html );														// ' - '
						$text .= CBPTXT::Tdate( 'F', $expiryTime) . $this->_htmlNbsp( ' ', $html ) . $ye;						// 'February 2007'
						if ( ( ! ( ( $cs == $cn ) && ( $ys == $yn ) ) ) && $isValid ) {
							$text .= ' (' . CBPTXT::T("valid from now on") . ')';
						}
						// } else {		//TBD: check if this else is still needed
						/*	list($ynn, $cnn, $dnn, $hnn, $mnn, $snn) = sscanf( date( 'Y-m-d H:i:s', $startTime ), '%d-%d-%d %d:%d:%d' );
							$cnn += 2;
							$dnn = 0;
							$nextMonthTime = mktime($hnn, $mnn, $snn, $cnn, $dnn, $ynn);
							$text .= date( 'F', $nextMonthTime) . ( ( $ynn != $ye ) ? $this->_htmlNbsp( ' ', $html ) . $ynn : '' );	// 'January' or 'December 2006'
							$text .= $this->_htmlNbsp( ' - ', $html );														// ' - '
							$text .= date( 'F', $expiryTime) . $this->_htmlNbsp( ' ', $html ) . $ye;						// 'February 2007'
							if ( $isValid ) {
								$text .= ' (' . CBPTXT::T("valid from now on") . ')';
							}
						*/
						// }
					}
				}
			} elseif ( $d ) {
				if ( $de == $dn ) {
					$text .= CBPTXT::T("Today");
				} elseif ( ( $de == ( $dn + 1 ) ) || ( ( $de == 1 ) && ( ( $expiryTime - $now ) < 48*3600 ) ) ) {
					if ( $d == 1 ) {
						$text .= CBPTXT::T("Tomorrow");
						if ( $isValid ) {
							$text .= ' (' . CBPTXT::T("valid from now on") . ')';
						}
					} else {
						$text .= CBPTXT::T("Today and tomorrow");
					}
				} else {
					if ( $isValid ) {
						$days = (int) floor( ( $expiryTime - $now ) / ( 24 * 3600 ) );
						if ( ( $days < $d ) && ( $ds == $dn ) ) {
							$t		=	CBPTXT::T("Today and next %d days");
							if ( $html ) {
								$t	=	str_replace( ' %d ', '&nbsp;%d&nbsp;', $t );
							}
							$text .= sprintf( $t, $days );
						} else {
							$text .= sprintf( $this->_htmlNbsp( CBPTXT::T("Next %d days"), $html ), $days ) . ' (' . CBPTXT::T("in addition of today, valid from now on") . ')';
						}
					} else {
						$showtime	= false;
						cbimport( 'cb.tabs' );		// cbFormatDate is in comprofiler.class.php
						$expText	= cbFormatDate( $expiryTime, 0, $showtime );
						$startText	= cbFormatDate( $startTime, 0, $showtime );
						$text		.= $startText;
						if ( $startText != $expText ) {
							$text	.= $this->_htmlNbsp( ' - ', $html ) . $expText;
						}
					}
				}
			}

		} else {
			$text = $this->_htmlNbsp( $this->renderPeriod( $this->getValidity( $varName ), 1, $displayOne ), $html );
		}
		return trim( $text );
	}
	/**
	 * Utility replacing spaces by %nbsp;
	 *
	 * @param  string   $text
	 * @param  boolean  $html
	 * @return string
	 */
	private function _htmlNbsp( $text, $html ) {
		if ( $html ) {
			$text		=			str_replace( ' ', '&nbsp;', $text );
		}
		return $text;
	}
}	// cbpaidTimed
