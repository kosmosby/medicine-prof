<?php
/**
 * @version $Id: cbpaidGatewayAccounthostedpage.php 1579 2012-12-24 02:19:02Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccounthostedpage extends cbpaidGatewayAccount {
	/**
	 * Gets payment mean handler : Overridde to phpdocument return of correct class
	 *
	 * @param  string                     $methodCheck
	 * @return cbpaidHostedPagePayHandler
	 */
	public function & getPayMean( $methodCheck = null ) {
		return parent::getPayMean( $methodCheck );
	}
	/**
	 * USED by XML interface ONLY !!! Renders URL for successful returns
	 *
	 * @param  string              $value   Variable value ( 'successurl', 'cancelurl', 'notifyurl' )
	 * @param  ParamsInterface     $params
	 * @param  string              $name    The name of the form element
	 * @param  CBSimpleXMLElement  $node    The xml element for the parameter
	 * @return string                       HTML to display
	 */
	public function renderUrl( /** @noinspection PhpUnusedParameterInspection */ $value, $params, $name, $node ) {
		return str_replace( 'http://', 'https://', $this->getPayMean()->adminUrlRender( $node->attributes( 'value' ) ) );
	}
	/**
	 * USED by XML interface ONLY !!! Renders URL for site returns
	 *
	 * @param  string           $gatewayId  Id of gateway
	 * @param  ParamsInterface  $params     Params of gateway
	 * @return string                       HTML to display
	 */
	public function renderSiteUrl( /** @noinspection PhpUnusedParameterInspection */ $gatewayId, $params ) {
		global $_CB_framework;

		return $_CB_framework->getCfg( 'live_site' );
	}
}
