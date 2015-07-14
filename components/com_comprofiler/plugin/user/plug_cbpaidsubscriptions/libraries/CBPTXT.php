<?php
/**
 * @version $Id: CBPTXT.php 1550 2012-12-03 10:03:14Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Application\Application;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/*
 */
/**
 * CBSubs translation class (extends CBTxt)
 * Use CBSubs's translation class so we can add features and fix bugs if needed:
 */
class CBPTXT extends CBTxt {
	/**
	 * Parse the string through static::T.
	 * That is, for a particular string find the corresponding translation.
	 * Variable subsitution is performed for the $args parameter.
	 *
	 * @param string   $english  the string to translate
	 * @param array    $args     a strtr-formatted array of string substitutions
	 * @return string
	 */
	public static function P( $english, $args = array() )
	{
		if ( $args === null ) {
			$args		=	array();
		}
		return parent::T( $english, null, $args );
	}

	/**
	 * Parse the string through static::Th.
	 * That is, for a particular string find the corresponding translation.
	 * Variable subsitution is performed for the $args parameter.
	 *
	 * @param string   $english  the string to translate
	 * @param array    $args     a strtr-formatted array of string substitutions
	 * @return string
	 */
	public static function Ph( $english, $args = array() )
	{
		if ( $args === null ) {
			$args		=	array();
		}
		return parent::Th( $english, null, $args );
	}

	/**
	 * Gives translated date()
	 * Right now only supports formats 'F' and 'j F'
	 *
	 * @param  string  $format     Format like PHP's date()
	 * @param  int     $timestamp  Unix Timestamp
	 * @return string
	 */
	public static function Tdate( $format, $timestamp ) {
		// $monthName		=	self::T( date( 'F', $timestamp ) );
		$monthName		=	self::T( 'UE_MONTHS_' . date( 'n', $timestamp ) );

		if ( $format == 'F' ) {
			return $monthName;
		}

		if ( $format == 'j F' ) {
			$day		=	date( 'j', $timestamp );
			return self::P( "[DAY] [MONTHNAME]", array( '[DAY]' => $day, '[MONTHNAME]' => $monthName ) );
		}

		return 'UNHANDLED FORMAT IN CBPTxt::Tdate: ' . $format;
	}
	/**
	 * addslashes for Javascript
	 * @static
	 * @param $str
	 * @return string
	 */
	public static function jsAddSlashes($str) {
		return addcslashes($str,"\\'\"\n\r");
	}
	/**
	 * Translates, prepares the HTML $htmlText with triggering CMS Content Plugins, replaces CB substitutions and extra HTML and non-HTML substitutions
	 * @see CBuser::replaceUserVars
	 *
	 * @param  string      $mainText
	 * @param  int         $user_id
	 * @param  boolean     $html
	 * @param  boolean     $translateMainText
	 * @param  boolean     $prepareHtmlContentPlugins
	 * @param  array|null  $extraHtmlStrings
	 * @param  array|null  $extraNonHtmlStrings
	 * @return string
	 */
	public static function replaceUserVars( $mainText, $user_id, $html, $translateMainText = true,
											$prepareHtmlContentPlugins = false,
											$extraHtmlStrings = null, $extraNonHtmlStrings = null )
	{
		if ( $translateMainText ) {
			$mainText		=	$html ? parent::Th( $mainText ) : parent::T( $mainText );
		}

		if ( $prepareHtmlContentPlugins ) {
			$mainText		=	Application::Cms()->prepareHtmlContentPlugins( $mainText );

			if ( ! $html ) {
				$mainText	=	strip_tags( $mainText );
			}
		}

		$cbUser				=	CBuser::getInstance( (int) $user_id );

		if ( ! $cbUser ) {
			$cbUser			=	CBuser::getInstance( null );
		}

		$mainText			=	$cbUser->replaceUserVars( $mainText, true, false, $extraNonHtmlStrings, false );

		if ( $extraHtmlStrings ) {
			foreach ( $extraHtmlStrings as $k => $v ) {
				$mainText	=	str_replace( "[$k]", $html ? $v : strip_tags( $v ), $mainText );
			}
		}

		return $mainText;
	}
}
