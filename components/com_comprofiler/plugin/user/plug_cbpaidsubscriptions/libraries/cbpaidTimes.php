<?php
/**
 * @version $Id: cbpaidTimes.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class handling time and timezones
 */
class cbpaidTimes {
	/** Main params
	 *  @var ParamsInterface */
	protected $params;
	/**
	 * Constructor
	 * @private
	 */
	protected function __construct( ) {
		$this->params			=&	cbpaidApp::settingsParams();
	}
	/**
	 * Gets a single instance of this class
	 *
	 * @return cbpaidTimes
	 */
	public static function & getInstance( ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new cbpaidTimes();
		}
		return $singleInstance;
	}
	/**
	 * Transforms a SQL-formatted datetime to a unix time
	 *
	 * @param  string   $sqlDateStr  'YYYY-MM-DD HH:II:SS' or NULL
	 * @return int                   unix-time
	 */
	public function strToTime( $sqlDateStr ) {
		if ( $sqlDateStr === null ) {
			$time = null;
		} else {
			list($y, $c, $d, $h, $m, $s) = sscanf($sqlDateStr, '%d-%d-%d %d:%d:%d');
			$time = mktime($h, $m, $s, $c, $d, $y);			// we do NOT use PHP strtotime, which is broken
		}
		return $time;
	}
	/**
	 * Returns formatted time period ( xxx weeks , or xxx years xxx months xxx days xxx hours xxx minutes xxx seconds
	 *
	 * @param  array|string  $ycdhmsArray  array  of int = list( $years, $months, $days, $hours, $minutes, $seconds ) or SQL DATETIME string
	 * @param  int           $occurrences  [default: 1] multiply period by the occurrences before displaying
	 * @param  boolean       $displayOne   [default: true] displays also if only 1 unit of something
	 * @param  string        $prefix       text between number and period, e.g. 3 calendar months
	 * @return string
	 */
	public function renderPeriod( $ycdhmsArray, $occurrences = 1, $displayOne = true, $prefix = '' ) {
		$text = '';
		if ( $prefix ) {
			$prefix		=	$prefix . ' ';
		}
		if ( ! is_array( $ycdhmsArray ) ) {
			$ycdhmsArray	=	sscanf( $ycdhmsArray, '%d-%d-%d %d:%d:%d');
		}
		list($y, $c, $d, $h, $m, $s) = $ycdhmsArray;

		if ( $occurrences != 1 ) {
			$s *= $occurrences;
			$m *= $occurrences;
			$h *= $occurrences;
			$d *= $occurrences;
			$c *= $occurrences;
			$y *= $occurrences;
			if ( $c && ( ( $c % 12 ) == 0 ) ) {
				$y += $c / 12;
				$c = 0;
			}
		}

		if ( ( $y == 0 ) && ( $c == 0 ) && ( ( $d != 0 ) && ( ( $d % 7 ) == 0 ) ) && ( $h == 0 ) && ( $m == 0 ) && ( $s == 0 )  ) {
			$w = $d / 7;
			if ( $w == 1 ) {
				if ( $displayOne ) {
					$text = $w . ' ';
				}
				$text .= $prefix . CBPTXT::T("week") . ' ';
			} else {
				$text = $w . ' ' . $prefix . CBPTXT::T("weeks") . ' ';
			}
		} else {
			$text  = $y ? ( $y . ' ' . $prefix . ( $y == 1 ? CBPTXT::T("year")	: CBPTXT::T("years")	) ) . ' ' : '';
			$text .= $c ? ( $c . ' ' . $prefix . ( $c == 1 ? CBPTXT::T("month")  : CBPTXT::T("months")  ) ) . ' ' : '';
			$text .= $d ? ( $d . ' ' . $prefix . ( $d == 1 ? CBPTXT::T("day")	: CBPTXT::T("days")	) ) . ' ' : '';
			$text .= $h ? ( $h . ' ' . $prefix . ( $h == 1 ? CBPTXT::T("hour")	: CBPTXT::T("hours")	) ) . ' ' : '';
			$text .= $m ? ( $m . ' ' . $prefix . ( $m == 1 ? CBPTXT::T("minute") : CBPTXT::T("minutes") ) ) . ' ' : '';
			$text .= $s ? ( $s . ' ' . $prefix . ( $s == 1 ? CBPTXT::T("second") : CBPTXT::T("seconds") ) ) . ' ' : '';
			if ($text == '') {
				$text = CBPTXT::T( $this->params->get( 'regtextLifetime', "Lifetime subscription" ) );
			} elseif ( ! $displayOne ) {
				if ( ( ( $y + $c + $d + $h + $m + $s ) == 1 ) && ( substr( $text, 0, 2 ) == '1 ' ) ) {
					$text = substr( $text, 2 );
				}
			}
		}
		return trim( $text );
	}
}	// class cbpaidTimes
