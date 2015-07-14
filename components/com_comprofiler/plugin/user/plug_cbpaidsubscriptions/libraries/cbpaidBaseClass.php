<?php
/**
 * @version $Id: cbpaidBaseClass.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CLASS REPRODUCING BASIC cbPluginHandler stuff from the CB tab and also implementing methods for https redirect:
 *
 */
class cbpaidBaseClass {
	/** Base class
	 *  @var getcbpaidsubscriptionsTab $baseClass */
	protected $baseClass;
	/**
	 * Plugin params
	 * @var ParamsInterface
	 */
	protected $params;
	/**
	 * Constructor
	 */
	public function __construct( ) {
		$this->baseClass	=&	cbpaidApp::getBaseClass();
		$this->params		=&	cbpaidApp::settingsParams();
	}
	/**
	 * gets an ESCAPED and urldecoded request parameter for the plugin
	 * you need to call stripslashes to remove escapes, and htmlspecialchars before displaying.
	 *
	 * @param  string  $name     name of parameter in REQUEST URL
	 * @param  string  $def      default value of parameter in REQUEST URL if none found
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string|array      value of the parameter (urldecode processed for international and special chars) and ESCAPED! and ALLOW HTML!
	 */
	public function _getReqParam( $name, $def=null, $postfix='') {
		return $this->baseClass->_getReqParam( $name, $def, $postfix );
	}
	/**
	 * Gets the name input parameter for search and other functions
	 *
	 * @param  string  $name     name of parameter of plugin
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            value of the name input parameter
	 */
	public function _getPagingParamName($name='search', $postfix='') {
		return $this->baseClass->_getPagingParamName($name, $postfix);
	}
	/**
	 * Gives the URL of a link with plugin parameters.
	 *
	 * @param  array    $paramArray        array of string with key name of parameters
	 * @param  string   $task              cb task to link to (default: userProfile)
	 * @param  boolean  $sefed             TRUE to call cbSef (default), FALSE to leave URL unsefed
	 * @param  array    $excludeParamList  of string with keys of parameters to not include
	 * @param  string   $format            'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	 * @return string value of the parameter
	 */
	public function _getAbsURLwithParam( $paramArray, $task = 'userProfile', $sefed = true, $excludeParamList = null, $format = 'html' ) {
		if ( $excludeParamList === null ) {
			$excludeParamList	=	array();
		}
		return $this->baseClass->_getAbsURLwithParam($paramArray, $task, $sefed, $excludeParamList, $format );
	}
	/**
	 * Gives the URL of a link with plugin parameters, as HTTPS if global CBSubs setting is to use HTTPS for Forms (PCI-DSS compliance).
	 *
	 * @param  array    $paramArray        array of string with key name of parameters
	 * @param  string   $task              cb task to link to (default: userProfile)
	 * @param  boolean  $sefed             TRUE to call cbSef (default), FALSE to leave URL unsefed
	 * @param  array    $excludeParamList  of string with keys of parameters to not include
	 * @param  string   $format            'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	 * @return string value of the parameter
	 */
	public function getHttpsAbsURLwithParam( $paramArray, $task = 'userProfile', $sefed = true, $excludeParamList = null, $format = 'html' ) {
		$url	=	$this->_getAbsURLwithParam( $paramArray, $task, $sefed, $excludeParamList, $format );
		if ( cbpaidApp::settingsParams()->get( 'https_posts', 0 ) ) {
			return preg_replace( '/^https?:/', 'https:', $url );
		} else {
			return $url;
		}
	}
	/**
	 * PRIVATE method: sets the text of the last error
	 * @access private
	 *
	 * @param  string   $msg   error message
	 * @return boolean         true
	 */
	public function _setErrorMSG( $msg ) {
		return $this->baseClass->_setErrorMSG( $msg );
	}
	/**
	 * Gets the text of the last error
	 *
	 * @param  string  $separator   Separator between the errors which are imploded from array
	 * @return string               Text for error message
	 */
	public function getErrorMSG( $separator = "\n" ) {
		return $this->baseClass->getErrorMSG( $separator );
	}
	/**
	 * Outputs cbpaidsubscriptions registration template CSS file
	 *
	 * @access private
	 */
	public function _outputRegTemplate( ) {
		$this->baseClass->outputRegTemplate();
	}
}	// class cbpaidBaseClass
