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
class cbpaiduserupgradeplansView extends cbpaidTemplateHandler {
	public $htmlUpgrades;
	public $htmlspecialcharedBaseUrl;
	public $hiddenFlds;
	public $buttonName;
	public $buttonText;
	public $htmlTitle;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the plans upgrade proposals
	 * 
	 * @param  UserTable  $user
	 * @param  int        $plansDisplayed
	 * @param  string     $htmlUpgrades
	 * @param  string     $htmlspecialcharedBaseUrl
	 * @param  array      $hiddenFlds
	 * @param  array      $buttonTexts
	 * @param  string     $buttonName
	 * @return string
	 */
	public function drawUserUpgradePlans( /** @noinspection PhpUnusedParameterInspection */ $user, $plansDisplayed, $htmlUpgrades, $htmlspecialcharedBaseUrl, $hiddenFlds, $buttonTexts, $buttonName ) {
		$this->htmlUpgrades				=	$htmlUpgrades;
		$this->htmlspecialcharedBaseUrl	=	$htmlspecialcharedBaseUrl;
		$this->hiddenFlds				=	$hiddenFlds;
		$this->buttonName				=	$buttonName;

		$this->buttonText				=	implode( ' / ', $buttonTexts );

		$subTxt							=	CBPTXT::T( cbpaidApp::settingsParams()->get( 'subscription_name', 'subscription' ) );
		if ( $plansDisplayed == 1 ) {
			$this->htmlTitle			=	sprintf( CBPTXT::Th("Your current %s upgrade possibility:"), $subTxt );
		} else {
			$this->htmlTitle			=	sprintf( CBPTXT::Th("Your current %s upgrade possibilities:"), $subTxt );
		}

		return $this->display();
	}

}

