<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * VIEW: User Profile Tab view class
 *
 */
class cbpaiduserprofilesubstabView extends cbpaidTemplateHandler {
	public $htmlSubscriptionsAndUpgrades;
	public $htmlInvoicesLink;
	public $htmlTabTitle;
	public $htmlTabDescription;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the user profile tab "Subscriptions" (if enabled, user is the profile owner, and something to display.
	 * 
	 * @param  string  $htmlSubscriptionsAndUpgrades
	 * @param  string  $htmlInvoicesLink
	 * @param  string  $tabTitleText
	 * @param  string  $htmlTabDescription
	 * @return string
	 */
	public function drawTab( $htmlSubscriptionsAndUpgrades, $htmlInvoicesLink, $tabTitleText, $htmlTabDescription ) {
		global $ueConfig;

		$this->htmlSubscriptionsAndUpgrades	=	$htmlSubscriptionsAndUpgrades;
		$this->htmlInvoicesLink				=	$htmlInvoicesLink;
		$this->htmlTabDescription			=	$htmlTabDescription;

		$user								=&	$this->_model;

		$title								=	cbReplaceVars( CBPTXT::Th( cbUnHtmlspecialchars( $tabTitleText ) ), $user );
		if ( $title ) {
			$name							=	getNameFormat($user->name,$user->username,$ueConfig['name_format']);
			$title							=	sprintf($title, $name);
		}
		$this->htmlTabTitle					=	$title;

		return $this->display();
	}

}

