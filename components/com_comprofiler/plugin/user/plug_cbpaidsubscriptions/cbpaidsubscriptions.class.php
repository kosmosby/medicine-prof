<?php
/**
* @version $Id: cbpaidsubscriptions.class.php 1580 2012-12-24 02:27:07Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// This file needs to stay minimal and independant as it's include-once by module, mambot and cbplugin.

/**
 * Autoloader class for CBSubs
 */
abstract class cbpaidAutoLoader {
	private static $classes				=	array(	'CBplug_cbpaidsubscriptions'			=>	'controllers/cbpaidControllerCBComponent.php',
													'cbpaidTotalizertypeCompoundable'		=>	'models/totalizer/cbpaidCrossTotalizer.php',
													'cbpaidPaymentTotalizerCompoundable'	=>	'models/totalizer/cbpaidCrossTotalizer.php',
													'cbpaidCrossTotalizer'					=>	'models/totalizer/cbpaidCrossTotalizer.php',
													'cbpaidCurrency'						=>	'models/currency/cbpaidCurrency.php',

											);

	private static $maps				=	array(	'/^(cbpaidController.*|cbpaid.*Handler)$/'						=>	'controllers/$1.php',
													'/^(cbpaid.*(?:OrdersMgr|PaymentBasket|PaymentItem).*)$/'		=>	'models/order/$1.php',
													'/^(cbpaidGateway.*)$/'											=>	'models/gateway/$1.php',
													'/^(cbpaid.*Payment.*)$/'										=>	'models/payment/$1.php',
													'/^(cbpaid.*(?:History|Instanciator|Table).*)$/'				=>	'models/table/$1.php',
													'/^(cbpaid.*(?:Product|ProductUndefined))$/'					=>	'models/product/$1.php',
													'/^(cbpaid.*(?:NonRecurringSomething|Something).*)$/'			=>	'models/something/$1.php',
													'/^(cbpaid.*Totalizer.*)$/'										=>	'models/totalizer/cbpaidPaymentTotalizer.php',
													'/^(cbpaid.*(?:Config|Item|Timed|SubscriptionsImporter).*)$/'	=>	'models/misc/$1.php',
													'/^(cbpaid.*|CBPTXT)$/'											=>	'libraries/$1.php',
													'/^(cbpaid.*Table)$/'											=>	'libraries/cbpaidTable.php',
													'/^(cbpaidProduct(.+)|cbpaid(.+)Record)$/'						=>	'products/$2$3/$1.php'	);
	/**
	 * Registers a $file name for class of name $className
	 * @param  string  $className
	 * @param  string  $file
	 * @return void
	 */
	public static function registerClass( $className, $file ) {
		self::$classes[$className]		=	$file;
	}
	/**
	 * Registers a Mapping Regexp mapping
	 * @param  string  $classNameRegexp  Matching Regular expression for the class name
	 * @param  string  $folderRegexp     Replacing Regexp for the corresponding folder (using regexp substitutions
	 * @return void
	 */
	public static function registerMap( $classNameRegexp, $folderRegexp ) {
		self::$maps[$classNameRegexp]	=	$folderRegexp;
	}
	/**
	 * Uses the autoloader registration function available for this PHP version
	 *
	 * @return void
	 */
	public static function autoloadRegister( ) {
		if ( function_exists( 'spl_autoload_register' ) ) {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}
			spl_autoload_register( array( 'cbpaidAutoLoader', 'autoloader' ) );
		/*
		* Core CBSubs doesn't use it yet, so no fatal error:
		* } else {
		*	$err						=	'PHP 5.1.2 minimum is prerequisite for spl_autoload_register used by CBSubs. ';
		*	echo $err;
		*	trigger_error( $err, E_USER_ERROR );
		*/
		}
	}
	/**
	 * DO NOT CALL: The Autoloader function called by PHP to load an unknown class name $className
	 *
	 * @param  string  $className
	 * @return void
	 */
	public static function autoloader( $className ) {
		if ( ( substr( $className, 0, 6 ) != 'cbpaid' ) && ! in_array( $className, array( 'CBplug_cbpaidsubscriptions', 'CBPTXT' ) ) ) {
			return;
		}
		if ( isset( self::$classes[$className] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once str_replace( '\\', '/', dirname( __FILE__ ) ) . '/' . self::$classes[$className];
			return;
		}
		foreach ( self::$maps as $classNameRegexp => $folderRegexp ) {
			if ( preg_match( $classNameRegexp, $className ) ) {
				$file					=	str_replace( '\\', '/', dirname( __FILE__ ) ) . '/' . self::lcFolders( preg_replace( $classNameRegexp, $folderRegexp, $className ) );
				if ( is_readable( $file ) ) {
					/** @noinspection PhpIncludeInspection */
					include_once $file;
					return;
				}
			}
		}
	}

	/**
	 * Lowercases all folders but keeps case of filename
	 *
	 * @param  string  $string  Filename with path
	 * @return string
	 */
	protected static function lcFolders( $string ) {
		$posLastSlash	=	strrpos( $string, '/' );
		if ( $posLastSlash !== false ) {
			$string		=	strtolower( substr( $string, 0, $posLastSlash ) )
						.	substr( $string, $posLastSlash );
		}
		return $string;
	}
}

// Registers the autoloader above at load time once:
cbpaidAutoLoader::autoloadRegister();


/*
 * backwards compatibility to easy upgrade process:
 */
/**
 * Returns version
 * @deprecated 2.1
 *
 * @return string
 */
function cbpaidVersion( ) {
	return cbpaidApp::version();
}
/**
 * DO NOT USE ANYMORE
 * @obsolete since CBSubs 1.1
 *
 * @return ParamsInterface
 */
function & cbpaidParams( ) {
	return cbpaidApp::settingsParams();
}
/**
 * DO NOT USE ANYMORE
 * @obsolete since CBSubs 1.1
 *
 * @ param  void  $name
 */
function & cbpaid_GetInstance( /* $name */ ) {
	cbpaidApp::getCurrenciesConverter();
}
/**
 * DO NOT USE ANYMORE
 * @obsolete since CBSubs 1.1
 *
 * @param  boolean  $absolute
 * @return string
 */
function cbpaidPath( $absolute = true ) {
	return cbpaidApp::getAbsoluteFilePath( '', $absolute );
}
/**
 * Gets an array of IP addresses taking in account the proxys on the way.
 * An array is needed because FORWARDED_FOR can be facked as well.
 *
 * @obsolete since CBSubs 2.1 (but was still used in CBSubs 3.0 by PayPalPro gateway and cbpaidRequest)
 *
 * @return array of IP addresses, first one being host, and last one last proxy (except fackings)
 */
function cbpaidGetIParray() {
	return cbpaidRequest::getIParray();
}
/**
 * Gets a comma-separated list of IP addresses taking in account the proxys on the way.
 * An array is needed because FORWARDED_FOR can be facked as well.
 *
 * @obsolete since CBSubs 2.1
 *
 * @return string of IP addresses, first one being host, and last one last proxy (except fackings)
 */
function cbpaidGetIPlist() {
	return cbpaidRequest::getIPlist();
}

