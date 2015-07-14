<?php
/**
* @version $Id: cbpaidXmlHandler.php 1560 2012-12-20 15:02:34Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Input\Input;
use CBLib\Output\Output;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Frontend XML AHA-WOW UI specifically for CBSubs
 */
class cbpaidXmlHandler extends cbpaidBaseClass {
	/**
	 * Renders a form in frontend (or backend maybe later)
	 * Usage:
	 *	$options			=	array(	$baseClass->_getPagingParamName( 'basket' )			=> $this->id,
	 *									$baseClass->_getPagingParamName( 'bck' )			=> $this->checkHashUser()
	 *									);
	 *
	 * @param  string              $actionType
	 * @param  string              $action
	 * @param  cbpaidTable         $dataModel
	 * @param  array               $options
	 * @param  int                 $user_id
	 * @return string
	 */
	public static function render( $actionType, $action, $dataModel, $options = array(), $user_id = null ) {
		global $_CB_framework;

		if ( $options === null ) {
			$options		=	array();
		}
		$di					=	Application::DI();

		$input				=	new Input( (array) $dataModel );
		$output				=	Output::createNew( 'html', array() );

		$getParams		=	array(	'option'	=> 'com_comprofiler',
										 'view'		=> 'pluginclass',
										// 'tab'	=> strtolower( get_class( $baseClass ) ),
										 'plugin'	=> 'cbpaidsubscriptions',
										 'user'		=> $user_id,
								);
		if ( $_CB_framework->getUi() == 1 ) {
			$itemid		=	getCBprofileItemid( 0 );
			if ( $itemid ) {
				$getParams['Itemid']	=	$itemid;
			}
		}

		$route					=	array( 'option' => 'com_comprofiler',
										   'view' => $action,
										   'action' => $actionType,
										   'method' => 'edit'
									//		,
									//	   'act' => $input->get( 'act' )
										 );

		if ( $route['view'] == '' ) {
			$route['view']		=	'pluginclass';
		}

		/** @var \CBLib\AhaWow\Controller\Controller $ahaWowController */
		$ahaWowController	=	$di->get( 'CBLib\AhaWow\Controller\Controller',
										  array( 'input' => $input, 'output' => $output, 'options' => $options, 'getParams' => $getParams, 'data' => $dataModel ) );

		self::registerXml( $action, $actionType );

		$ahaWowController->dispatchRoute( $route );

		return (string) $output;
	}
	/**
	 * Binds results of a form in frontend (or backend maybe later) to the data model
	 *
	 * @param  string       $actionType
	 * @param  string       $action
	 * @param  cbpaidTable  $dataModel    CHANGED with data from the form
	 * @return string|null                NULL: success, STRING: validation error
	 */
	public static function bindToModel( $actionType, $action, &$dataModel ) {

		$di					=	Application::DI();

		$route				=	array( 'option' => 'com_comprofiler',
														'view' => $action,
														'action' => $actionType,
														'method' => 'save' );

		/** @var \CBLib\AhaWow\Controller\Controller $ahaWowController */
		$ahaWowController	=	$di->get( 'CBLib\AhaWow\Controller\Controller', array( 'options' => array(), 'getParams' => array(), 'data' => $dataModel ) );

		self::registerXml( $action, $actionType );

		$ahaWowController->dispatchRoute( $route );

		return null;
	}

	private static function registerXml( $action, $actionType )
	{
		global $_PLUGINS;

		$di					=	Application::DI();

		$extensionPath		=	$_PLUGINS->getPluginPath();

		/** @var \CBLib\AhaWow\AutoLoaderXml $autoLoaderXml::_construct() */
		$autoLoaderXml		=	$di->get( 'CBLib\AhaWow\AutoLoaderXml' );

		$autoLoaderXml->registerMap( 'com_comprofiler/' . $action . '/' .$actionType, $extensionPath . '/xml/edit.front.' . preg_replace( '/[^A-Za-z0-9_\.-]/', '', $actionType ) . '.xml' );
	}
}
