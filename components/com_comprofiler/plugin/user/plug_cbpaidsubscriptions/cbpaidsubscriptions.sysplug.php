<?php
/**
* @version $Id: cbpaidsubscriptions.sysplug.php 428 2010-01-26 11:11:34Z brunner $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$memMax				=	trim( @ini_get( 'memory_limit' ) );
if ( $memMax ) {
	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
	switch( $last ) {
		case 'g':
			$memMax	*=	1024 * 1024 * 1024;
			break;
		case 'm':
			$memMax	*=	1024 * 1024;
			break;
		case 'k':
			$memMax	*=	1024;
			break;
	}
	if ( $memMax < 48000000 ) {
		@ini_set( 'memory_limit', '48M' );
	}
}


if ( ! is_readable( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
	JFactory::getApplication()->enqueueMessage( "Mandatory Community Builder package not installed!", 'error');
	return;
}
/** @noinspection PhpIncludeInspection */
include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';

/** @noinspection PhpIncludeInspection */
include_once( Application::CBFramework()->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php');


jimport( 'joomla.plugin.plugin' );
$app		=	JFactory::getApplication();
$app->registerEvent( 'onAfterRoute', 'cbpaidSysPlugin_onAfterStart');

/**
 * CBSubs System Plugin class
 */
class cbpaidSysPlugin {
	/**
	 * Paid subscriptions manager
	 * @var cbpaidSubscriptionsMgr
	 */
	public $paidsubsManager			=	null;

	/**
	 * Constructor
	 */
	public function __construct( ) {
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$ueConfig;		// needed for the includes below, incl. ue_config.php

		$_CB_joomla_path		=	JPATH_SITE;
		$_CB_joomla_adminpath	=	JPATH_ADMINISTRATOR;

		$_CB_adminpath				=	$_CB_joomla_adminpath. "/components/com_comprofiler";

		if ( ! file_exists( $_CB_adminpath . '/plugin.class.php' )) {
			if ( is_callable( array( 'JError', 'raiseWarning' ) ) ) {
				JError::raiseNotice( 'SOME_ERROR_CODE', 'Paid Subscriptions bot detected that Community Builder is not installed.', '' );
			} else {
				trigger_error( 'Paid Subscriptions bot detected that Community Builder is not installed.', E_USER_WARNING );
			}
			return;
		}
		if ( ! file_exists( $_CB_joomla_path . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php' )) {
			if ( is_callable( array( 'JError', 'raiseWarning' ) ) ) {
				JError::raiseNotice( 'SOME_ERROR_CODE', 'Paid Subscriptions bot detected that Community Builder Paid Subscriptions plugin is not installed.', '' );
			} else {
				trigger_error( 'Paid Subscriptions bot detected that Community Builder Paid Subscriptions plugin is not installed.', E_USER_WARNING );
			}
			return;
		}
		/** @noinspection PhpIncludeInspection */
		include_once( $_CB_adminpath . '/plugin.foundation.php' );
		cbimport( 'cb.plugins' );
		//cbimport( 'cb.tabs' );		// comprofiler.class.php is not needed for sure.
		cbimport( 'cb.database' );
		cbimport( 'cb.tables' );
		/** @noinspection PhpIncludeInspection */
		include_once( $_CB_joomla_path . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php' );

		$this->paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();
	}
	/**
	 * Checks if the logged-in user needs to be expired
	 */
	public function checkExpireMe( ) {
		if ( $this->paidsubsManager ) {
			$this->paidsubsManager->checkExpireMe( 'system_mambot', null, true );
		}
	}
	/**
	 * Register a function with a priority for onAlittleMoreAfterStart execution
	 * @static
	 *
	 * @param  mixed  $function
	 * @param  int    $priority  1..100 : default 50, max: 1, min: 100
	 */
	public static function registerOnRealStart( $function, $priority = 50 ) {
		static $registeredFunctions	=	array();
		if ( $function ) {
			$registeredFunctions[$priority][]	=	$function;
		} elseif ( $function === false ) {
			foreach ($registeredFunctions as $fncts ) {
				foreach ( $fncts as $f ) {
					call_user_func_array( $f, array() );
				}
			}
		}
	}
	/**
	 * Triggers the system plugins
	 */
	public function triggerSysPlugins() {
		// now check for content plugin:
		self::registerOnRealStart( false );
		//TODO LATER: make it more universal.
		// $_PLUGINS->trigger( 'onCPayHostSystemStart', array() );		// not needed here as plugins are not loaded depending on triggers yet
	}
}


/**
 * Event handler for onAfterStart
 */
function cbpaidSysPlugin_onAfterStart( ) {
	$_CBPAID_SYSPLUG			=	new cbpaidSysPlugin();
	$_CBPAID_SYSPLUG->checkExpireMe();
	$_CBPAID_SYSPLUG->triggerSysPlugins();
}

//TODO this is temporary as we don't have yet a general CB method:

global $mosConfig_absolute_path;
if ( $mosConfig_absolute_path ) {
	// Mambo and Joomla 1.0 case:
	define( '_CBSUBS_CONTENT_BOT_FILE', $mosConfig_absolute_path
	. '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/plugin/cbsubscontent/cbsubs.content_access.php' );
} else {
	// Other cases when the above fails:
	define( '_CBSUBS_CONTENT_BOT_FILE', dirname( __FILE__ ) . DIRECTORY_SEPARATOR
	. 'plugin' . DIRECTORY_SEPARATOR
	. 'cbsubscontent' . DIRECTORY_SEPARATOR
	. 'cbsubs.content_access.php' );
}
if ( is_readable( _CBSUBS_CONTENT_BOT_FILE ) ) {
	/** @noinspection PhpIncludeInspection */
	include_once _CBSUBS_CONTENT_BOT_FILE;
}
