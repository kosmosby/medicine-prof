<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class cbautoactionsActionLoginlogout extends cbPluginHandler
{

	/**
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable $user
	 */
	public function execute( $trigger, $user )
	{
		$params					=	$trigger->getParams()->subTree( 'loginlogout' );

		cbimport( 'cb.authentication' );

		$cbAuthenticate			=	new CBAuthentication();

		$isHttps				=	( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );
		$returnUrl				=	'http' . ( $isHttps ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];

		if ( ( ! empty( $_SERVER['PHP_SELF'] ) ) && ( ! empty( $_SERVER['REQUEST_URI'] ) ) ) {
			$returnUrl			.=	$_SERVER['REQUEST_URI'];
		} else {
			$returnUrl			.=	$_SERVER['SCRIPT_NAME'];

			if ( isset( $_SERVER['QUERY_STRING'] ) && ( ! empty( $_SERVER['QUERY_STRING'] ) ) ) {
				$returnUrl		.=	'?' . $_SERVER['QUERY_STRING'];
			}
		}

		$returnUrl				=	cbUnHtmlspecialchars( preg_replace( '/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', preg_replace( '/eval\((.*)\)/', '', htmlspecialchars( urldecode( $returnUrl ) ) ) ) );

		if ( preg_match( '/index.php\?option=com_comprofiler&task=confirm&confirmCode=|index.php\?option=com_comprofiler&view=confirm&confirmCode=|index.php\?option=com_comprofiler&task=login|index.php\?option=com_comprofiler&view=login/', $returnUrl ) ) {
			$returnUrl			=	'index.php';
		}

		$redirect				=	$trigger->getSubstituteString( $params->get( 'redirect', null, GetterInterface::STRING ), array( 'cbautoactionsClass', 'escapeURL' ) );

		if ( $redirect ) {
			$returnUrl			=	$redirect;
		}

		$message				=	$trigger->getSubstituteString( CBTxt::T( $params->get( 'message', null, GetterInterface::RAW ) ), false );

		if ( $params->get( 'mode', 1, GetterInterface::BOOLEAN ) ) {
			$messagesToUser		=	array();
			$alertMessages		=	array();

			if ( $params->get( 'method', 1, GetterInterface::BOOLEAN ) ) {
				$credentials	=	$trigger->getSubstituteString( $params->get( 'username', null, GetterInterface::STRING ) );
				$method			=	0;
			} else {
				$credentials	=	$trigger->getSubstituteString( $params->get( 'email', null, GetterInterface::STRING ) );
				$method			=	1;
			}

			$resultError		=	$cbAuthenticate->login( $credentials, false, 0, 1, $returnUrl, $messagesToUser, $alertMessages, $method );

			if ( $redirect ) {
				cbRedirect( $redirect, ( $resultError ? $resultError : ( $message ? $message : ( $alertMessages ? stripslashes( implode( '<br />', $alertMessages ) ) : null ) ) ), ( $resultError ? 'error' : 'message' ) );
			}
		} else {
			$resultError		=	$cbAuthenticate->logout( $returnUrl );

			if ( $redirect ) {
				cbRedirect( $redirect, ( $resultError ? $resultError : ( $message ? $message : CBTxt::T( 'LOGOUT_SUCCESS', 'You have successfully logged out' ) ) ), ( $resultError ? 'error' : 'message' ) );
			}
		}
	}
}