<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * VIEW: User's subscriptions (somethings) view class
 *
 */
class cbpaidusersubscriptionsView extends cbpaidTemplateHandler {
	public $htmlSubscribed;
	public $htmlTabTitle;
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
	 * @param  UserTable  $user
	 * @param  boolean    $itsmyself
	 * @param  string     $htmlSubscribed
	 * @return string
	 */
	public function drawUserSomethings( $user, $itsmyself, $htmlSubscribed ) {
		global $_CB_framework, $ueConfig;

		$this->htmlSubscribed			=	$htmlSubscribed;
		$subscriptions					=	$this->_model;

		if ( count( $subscriptions ) == 1 ) {
			$subTxt						=	CBPTXT::T( cbpaidApp::settingsParams()->get( 'subscription_name', 'subscription' ) );
		} else {
			$subTxt						=	CBPTXT::T( cbpaidApp::settingsParams()->get( 'subscriptions_name', 'subscriptions' ) );
		}

		if ( $itsmyself ) {
			$userName					=	null;
		} else {
			$userName					=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );
		}
		if ( $_CB_framework->getUi() == 1 ) {
			if ( $itsmyself ) {
				$this->htmlTabTitle		=	sprintf( CBPTXT::Th("Your current %s"), $subTxt );
			} else {
				$this->htmlTabTitle		=	sprintf( CBPTXT::Th("%s's current %s"), $userName, $subTxt );
			}
		} else {
			if ( $itsmyself ) {
				$this->htmlTabTitle		=	sprintf( CBPTXT::Th("Your current and past %s"), $subTxt );
			} else {
				$this->htmlTabTitle		=	sprintf( CBPTXT::Th("%s's current and past %s"), $userName, $subTxt );
			}
		}

		return $this->display();
	}

}

