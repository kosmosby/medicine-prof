<?php
/**
* CBSubs (TM): Community Builder Paid Subscriptions Plugin: cbsubsemail
* @version $Id: cbpaidSqlCleanQuote.php 1534 2012-11-23 17:56:51Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage cb.xml.sql.php
* @author Beat
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

use CBLib\Registry\ParamsInterface;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Utility class to clean-quote for SQL queries the param
 */
class cbpaidSqlCleanQuote {
	/**
	 * Cleans the field value by type in a secure way for SQL
	 *
	 * @param  mixed                          $fieldValue
	 * @param  string                         $type           const,sql,param : string,int,float,datetime,formula
	 * @param  ParamsInterface                $pluginParams
	 * @param  CBdatabase|null                $db
	 * @param  array|null                     $extDataModels
	 * @return string|boolean                                 STRING: sql-safe value, Quoted or type-casted to int or float, or FALSE in case of type error
	 */
	public static function sqlCleanQuote( $fieldValue, $type, $pluginParams, &$db = null, $extDataModels = null ) {
		if ( $db === null ) {
			global $_CB_database;
			$db			=&	$_CB_database;
		}
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		if ( $typeArray[0] == 'param' ) {
			$fieldValue	=	$pluginParams->get( $fieldValue );
		} elseif ( in_array( $typeArray[0], array( 'request', 'get', 'post', 'cookie', 'cbcookie', 'session', 'server', 'env' ) ) ) {
			$fieldValue	=	self::_globalConv( $typeArray[0], $fieldValue );
		} elseif ( $typeArray[0] == 'ext' ) {
			if ( isset( $typeArray[2] ) && $extDataModels && isset( $extDataModels[$typeArray[2]] ) ) {
				if ( is_object( $extDataModels[$typeArray[2]] ) ) {
					if ( isset( $extDataModels[$typeArray[2]]->$fieldValue ) ) {
						$fieldValue		=	$extDataModels[$typeArray[2]]->$fieldValue;
					}
				} elseif ( is_array( $extDataModels[$typeArray[2]] ) ) {
					if ( isset( $extDataModels[$typeArray[2]][$fieldValue] ) ) {
						$fieldValue		=	$extDataModels[$typeArray[2]][$fieldValue];
					}
				} else {
					$fieldValue		=	$extDataModels[$typeArray[2]];
				}
			} else {
				trigger_error( 'SQLXML::sqlCleanQuote: ERROR: ext valuetype "' . htmlspecialchars( $type ).'" has not been setExternalDataTypeValues.', E_USER_NOTICE );
			}
			// } elseif ( ( $typeArray[0] == 'const' ) || ( $cnt_valtypeArray[0] == 'sql' ) {
			//	$fieldValue	=	$fieldValue;
		}

		switch ( $typeArray[1] ) {
			case 'int':
				$value		=	(int) $fieldValue;
				break;
			case 'float':
				$value		=	(float) $fieldValue;
				break;
			case 'formula':
				$value		=	$fieldValue;
				break;
			case 'datetime':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'date':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9]/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'time':
				if ( preg_match( '/-?[0-9]{1,3}(:[0-5][0-9]){2}/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'string':
				$value		=	$db->Quote( $fieldValue );
				break;
			case 'null':
				$value		=	'NULL';
				break;

			default:
				trigger_error( 'SQLXML::sqlCleanQuote: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	$db->Quote( $fieldValue );	// false;
				break;
		}
		return $value;
	}
	/**
	 * Gets a cleaned value from a PHP global
	 *
	 * @param  string $arn
	 * @param  string $name
	 * @param  mixed  $def
	 * @return mixed
	 */
	protected static function _globalConv( $arn, $name, $def = null ) {
		switch ( $arn ) {
			case 'request':
				global $_REQUEST;
				$value	=	cbGetParam( $_REQUEST, $name, $def );
				break;
			case 'get':
				global $_GET;
				$value	=	cbGetParam( $_GET, $name, $def );
				break;
			case 'post':
				global $_POST;
				$value	=	cbGetParam( $_POST, $name, $def );
				break;
			case 'cookie':
				global $_COOKIE;
				$value	=	cbGetParam( $_COOKIE, $name, $def );
				break;
			case 'cbcookie':
				cbimport( 'cb.session' );
				$value	=	CBCookie::getcookie( $name, $def );
				break;
			case 'session':
				global $_SESSION;
				$value	=	cbGetParam( $_SESSION, $name, $def );
				break;
			case 'server':
				global $_SERVER;
				$value	=	cbGetParam( $_SERVER, $name, $def );
				break;
			case 'env':
				global $_ENV;
				$value	=	cbGetParam( $_ENV, $name, $def );
				break;
			default:
				trigger_error( sprintf( 'SQLXML::globalconv error: unknown type %s for %s.', $arn, $name ), E_USER_NOTICE );
				$value	=	null;
				break;
		}
		return stripslashes( $value );
	}
}
