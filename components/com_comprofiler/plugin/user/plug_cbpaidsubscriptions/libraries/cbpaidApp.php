<?php
/**
 * @version $Id: cbpaidApp.php 1610 2013-01-09 23:29:17Z brunner $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * This class allows to use pluginconfig:permission as type in XML.
 * It is a temporary workaround to missing type "permission" in phptypes for IFs.
 */
class cbpaidParamsConfig extends Registry
{
	/**
	 * Gets a param value
	 *
	 * @param  string|string[]        $key      Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param  mixed|GetterInterface  $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param  string|array           $type     [optional] default: null: raw. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return string|array
	 *
	 * @throws \InvalidArgumentException        If namespace doesn't exist
	 */
	public function get( $key, $default = null, $type = null )
	{
		if ( substr( $key, 0, 11 ) == 'permission:' ) {
			$parts	=	explode( ':', substr( $key, 11 ) );
			if ( count( $parts ) < 2 ) {
				return cbpaidApp::authoriseAction( $parts[0] ) ? 1 : 0;
			} else {
				return cbpaidApp::authoriseAction( $parts[1], $parts[0] ) ? 1 : 0;
			}
		}
		return parent::get( $key, $default );
	}
}

/**
 * CBSubs Paid Subscriptions Main Application Class for handling the CBSubs api
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @author Beat
 */
class getcbpaidTabHandler /* will be from 1.1.1 onwards: cbpaidApp */ extends cbTabHandler
{
	/**
	 * Returns version
	 * This should never be refactored !
	 *
	 * @return string
	 */
	public static function version( ) {
		return '4.0.0-rc.1';		//CBSUBS_VERSION_AUTOMATICALLY_SET_DO_NOT_EDIT!!!
	}
	/**
	 * Returns global parameters instance for paid subs.
	 *
	 * @return ParamsInterface|cbpaidConfig
	 */
	public static function & settingsParams( ) {
		global $_CB_database;

		static $_params				=	null;

		if ( $_params === null ) {
			$_params				=	new cbpaidConfig( $_CB_database );
			if ( $_params->load( 1 ) ) {
				$_params			=	new cbpaidParamsConfig( $_params->params );
			} else {
				$_params			=	new cbpaidParamsConfig( '' );
			}
		}
		return $_params;
	}
	/**
	 * Override Core method to load parameters to plugin: use #__cbsubs_config instead of params column of comprofiler_plugin
	 *
	 * @param  int|null  $pluginid
	 * @param  string    $extraParams  Extra parameters (e.g. from the tab settings)
	 */
	public function _loadParams( $pluginid, $extraParams = null ) {
		$this->params			=&	cbpaidApp::settingsParams();
		/*
				global $_CB_database;

				static $_params			=	null;

				// $this->params		=	new Registry( $_PLUGINS->_plugins[$pluginid]->params . "\n" . $extraParams );
				if ( $_params === null ) {
					$_params			=	new cbpaidConfig( $_CB_database );
					if ( $_params->load( 1 ) ) {
						$_params		=	new Registry( $_params->params . "\n" . $extraParams );
					} else {
						$_params		=	new Registry( $extraParams );
					}
				}
				$this->params			=&	$_params;
		*/
	}
	/**
	 * includes CB paid subs stuff
	 * --- usage: cbimport('cb.xml.simplexml');
	 *
	 * @param  string  $lib
	 * @return void
	 */
	public static function import( $lib ) {
		static $imported = array();

		// Auto-loader ignore for backwards-compatibility: Added in CBSubs 2.1:
		if ( in_array( $lib, array( 'condition', 'countries', 'userparams', 'guisubs', 'scheduler', 'ctrl', 'crosstotalizer', 'creditcards', 'hostedpage' ) ) ) {
			return;
		}

		if ( ! isset( $imported[$lib] ) ) {
			$imported[$lib]	=	true;

			$liblow			=	str_replace( array( "'", '"', '/', "\\", ".." ), '', strtolower( $lib ) );
			$pathAr			=	explode( '.', $liblow );
			if ( $pathAr[0] === 'plugin' ) {
				if ( $pathAr[2] == 'salestax') {
					// Beat made a mistake for salestax naming folder and file tax:
					$pathAr[1]	=	'tax';
					$pathAr[2]	=	'tax';
				}
				$filename		=	'cbsubs.' . array_pop( $pathAr );
				$pathAr[1]		=	'cbsubs' . $pathAr[1];
			} elseif ( $pathAr[0] === 'products' ) {
				$filename		=	'cbpaidProduct' . ucfirst( array_pop( $pathAr ) );
			} else {
				$filename		=	'cbpaidsubscriptions.' . array_pop( $pathAr );
			}
			$filepath		=	implode( '/', $pathAr ) . (count( $pathAr ) ? '/' : '' ) . $filename . '.php';

			/** @noinspection PhpIncludeInspection */
			include_once cbpaidApp::getAbsoluteFilePath( $filepath );
		}
	}
	/**
	 * Loads and returns currency converter singleton
	 *
	 * @return cbpaidCurrency
	 */
	public static function & getCurrenciesConverter() {
		static $instance			=	null;
		if ( ! isset( $instance ) ) {
			$instance		=	new cbpaidCurrency();
		}
		return $instance;
	}
	/**
	 * Returns single getcbpaidsubscriptionsTab
	 * @static
	 *
	 * @param  getcbpaidsubscriptionsTab  $baseClass
	 * @return getcbpaidsubscriptionsTab
	 */
	public static function & getBaseClass( $baseClass = null ) {
		static $singleClass	=	null;
		if ( $baseClass && ! $singleClass ) {
			$singleClass	=	$baseClass;
		}
		return $singleClass;
	}
	/**
	 * Standard language loading method:
	 * @todo add to CB API
	 *
	 * @param  string  $interface   'admin' to force-load admin languages
	 */
	public static function loadLang( $interface = null ) {
		global $_CB_framework;

		static $loaded					=	false;
		static $adminLoaded				=	false;

		if ( ! $loaded ) {
			$path						=	$_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/language';

			$myLanguageFolder			=	'-' . strtolower( $_CB_framework->getCfg( 'lang_tag' ) );
			$myLanguageFile				=	'language.php';
			$file						=	$path . '/cbpaidsubscriptions' . $myLanguageFolder . '/' . $myLanguageFile;
			if ( ! file_exists( $file ) ) {
				// Old method:
				$myLanguageFolder		=	$_CB_framework->getCfg( 'lang' );
				$myLanguageFile			=	$myLanguageFolder . '.php';
				$file					=	$path . '/cbpaidsubscriptions' . $myLanguageFolder . '/' . $myLanguageFile;
			}
			if ( file_exists( $file ) ) {
				if ( ! $interface ) {
					$CBstrings			=	array();
					/** @noinspection PhpIncludeInspection */
					include_once( $file );		// defines $CBstrings
					CBPTXT::addStrings( $CBstrings );
				}
				if ( ( $_CB_framework->getUi() == 2 ) || ( $interface == 'admin' ) ) {
					if ( ! $adminLoaded ) {
						$file			=	$path . '/cbpaidsubscriptions' . $myLanguageFolder . '/admin_' . $myLanguageFile;
						if ( file_exists( $file ) ) {
							$CBstrings	=	array();
							/** @noinspection PhpIncludeInspection */
							include_once( $file );		// defines $CBstrings
							CBPTXT::addStrings( $CBstrings );
						}
						$adminLoaded	=	true;
					}
				}
			}
			$loaded						=	true;
		}
	}

	/**
	 * Outputs cbpaidsubscriptions registration template CSS file
	 *
	 * @param  string   $template  Template to use (default is 'default')
	 * @return void
	 */
	public function outputRegTemplate( $template = '' ) {
		global $_CB_framework;

		static $dones			=	array();
		if ( ! isset( $dones[$template] ) ) {
			$inBackend			=	( $_CB_framework->getUi() == 2 );
			if ( ( ! $inBackend ) || ( $template == '' ) ) {
				cbpaidTemplateHandler::getViewer( ( $inBackend ? 'default' : $template ), null )->outputTemplateCss( 'cbpaidsubscriptions' );
				if ( $inBackend ) {
					$_CB_framework->document->addHeadStyleSheet( '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/templates/default/cbpaidsubscriptions.admin.css' );
				}
			}
			$dones[$template]	=	true;
		}
	}
	/**
	 * Returns the absolute path to CBSubs folder or sub-folder/file $subPathAndFile
	 * @param  string   $subPathAndFile  Sub-path and file to add to the returned path
	 * @param  boolean  $absolute        Should the returned path include the site's absolute_path ?
	 * @return string                    Path and file
	 */
	public static function getAbsoluteFilePath( $subPathAndFile, $absolute = true ) {
		global $_CB_framework;

		return ( $absolute ? $_CB_framework->getCfg('absolute_path') . '/' : '' ) . 'components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/' . $subPathAndFile;
	}
	/**
	 * Returns the URL to a given file
	 * @param  string  $subPathAndFile  Sub-path and file
	 * @return string                   URL, including live_site
	 */
	public static function getLiveSiteFilePath( $subPathAndFile ) {
		global $_CB_framework;

		return $_CB_framework->getCfg('live_site') . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/' . $subPathAndFile;
	}
	/**
	 * Sets the text of the last error and logs it to the history logger
	 *
	 * @param  int               $log_priority      Priority of message (UNIX-type): 0: Emergency, 1: Alert, 2: Critical, 3: Error, 4: Warning, 5: Notice, 6: Info, 7: Debug
	 * @param  cbpaidTable|null  $object            Object stored in database, so that table name of table and id of key can be stored with the error
	 * @param  string            $logMessagePrefix  Error message prefix for the logged message (simple non-html text only): will be prepended with ': '
	 * @param  string            $userMessage       Error message for user (simple non-html text only)
	 */
	public static function setLogErrorMSG( $log_priority, $object, $logMessagePrefix, $userMessage ) {
		global $_CB_database;

		$logObject			=	new cbpaidHistory( $_CB_database );
		$logText			=	( $logMessagePrefix ? $logMessagePrefix . ( $userMessage ? ': ' . $userMessage : '' ) : $userMessage );
		$logObject->logError( $log_priority, $logText, $object );

		if ( $userMessage ) {
			cbpaidApp::getBaseClass()->_setErrorMSG( $userMessage );
		}
	}
	/**
	 * backwards compatibility to easy upgrade process:
	 * @deprecated in 1.1.0
	 */
	public function _outputRegTemplate( ) {
		$this->outputRegTemplate();
	}
	/**
	 * Check for authorization to perform an action on an asset.
	 *
	 * $action:
	 * Configure         core.admin
	 * Access component  core.manage
	 * Create            core.create
	 * Delete            core.delete
	 * Edit              core.edit
	 * Edit State        core.edit.state    (e.g. block users and get CB/users administration mails)
	 * Edit Own          core.edit.own
	 *
	 * Baskets:
	 * Pay:              baskets.pay
	 * Record payment    baskets.recordpayment
	 * Refund:           baskets.refund
	 *
	 * $assetname:
	 * 'com_comprofiler.plugin.cbsubs' (default) : For all CBSubs aspects except user management
	 * '.plan.id'                  : For plan number id
	 * 'com_users'                 : For all user management aspects (except core.manage, left for deactivating core Joomla/Mambo User)
	 * null                        : For global super-user rights check: ( 'core.admin', null )
	 *
	 * @since 2.0
	 *
	 * @param  string        $action     Action to perform: core.admin, core.manage, core.create, core.delete, core.edit, core.edit.state, core.edit.own, ...
	 * @param  string        $assetname  OPTIONAL: asset name e.g. "com_comprofiler.plugin.$pluginId" or "com_users", or null for global rights
	 * @return boolean|null              True: Authorized, False: Not Authorized, Null: Default (not authorized
	 */
	public static function authoriseAction( $action, $assetname = 'com_cbsubs' ) {
		if ( Application::MyUser()->isSuperAdmin() ) {
			// Super Admins have all rights:
			return true;
		}
		// Others must be authorized:
		if ( $assetname && ( $assetname[0] == '.' ) ) {
			$assetname		=	'com_cbsubs' . $assetname;
		}

		return Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetname );
	}
}	// class cbpaidApp
/**
 * This is the new class to use !!! :
 */
class cbpaidApp extends getcbpaidTabHandler {
}
