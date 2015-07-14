<?php
/**
* @version $Id: cbsubs.content.access.php 428 2010-01-26 11:11:34Z brunner $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * View class for Content Access Denied messages
 */
class cbpaidContentAccessDeniedView {
	/**
	 * Displays the "Content Access Denied View
	 * 
	 * @param  UserTable                   $user
	 * @param  CBplug_cbpaidsubscriptions  $baseClass
	 * @return string
	 */
	public function display( & $user, /** @noinspection PhpUnusedParameterInspection */ &$baseClass ) {
		global $_CB_framework;

		if ( ! is_callable( array( 'cbpaidBot', 'getInstance' ) ) ) {
			return CBPTXT::T("CBSubs CbpaidSubsBot is not installed, enabled and running or CBSubs Content integration plugin is not up to date. Please ask site administrator to do so.");
		}

		$cbpaidBot				=	cbpaidBot::getInstance();

		$userId						=	$_CB_framework->myId();
		$accesstype					=	cbGetParam( $_GET, 'accesstype' );
		$accessvalue				=	cbGetParam( $_GET, 'accessvalue' );
		$accessurl					=	cbGetParam( $_GET, 'accessurl' );
		switch ( $accesstype ) {
			case 'components':
				$option				=	$accessvalue;
				$accessPlans		=	$cbpaidBot->checkAccess( $userId, $option, 'cpaycontent_components', null, true );
				if ( is_array( $accessPlans ) ) {
					$result			=	array( 'can', CBPTXT::T("component") );	// CBPTXT::T("You can access to this component with following plans: ");													// . implode( ', ', $accessPlans );
					$access			=	false;
				} elseif ( $accessPlans === true ) {
					$result			=	array( 'have', CBPTXT::T("component") );	// CBPTXT::T("You have access now to this component !");
					$access			=	true;
				} else {
					$result			=	array( 'unknown', CBPTXT::T("component"), $accessPlans );	// 'Unknown component access result: ' . var_export( $accessPlans, true );
					$access			=	false;
				}
				break;

			case 'menus':
				$itemid				=	(int) $accessvalue;
				$accessPlans		=	$cbpaidBot->checkAccess( $userId, $itemid, 'cpaycontent_menus' , null, true );
				if ( is_array( $accessPlans ) ) {
					$result			=	array( 'can', CBPTXT::T("menu") );	// CBPTXT::T("You can access to this menu with following plans: ");														// . implode( ', ', $accessPlans );
					$access			=	false;
				} elseif ( $accessPlans === true ) {
					$result			=	array( 'have', CBPTXT::T("menu") );	// CBPTXT::T("You have access now to this menu !");
					$access			=	true;
				} else {
					$result			=	array( 'unknown', CBPTXT::T("menu"), $accessPlans );	// 'Unknown menu access result: ' . var_export( $accessPlans, true );
					$access			=	false;
				}
				break;

			case 'urls':
				$getPostArray		=	$this->_decodeArrayUrl( $accessurl );
				$postsMissingInGetToFindPlans	=	array();
				$accessPlans		=	$cbpaidBot->checkAccessUrl( $userId, $getPostArray, $getPostArray, $postsMissingInGetToFindPlans, 'cpaycontent_urls', true );
				if ( is_array( $accessPlans ) ) {
					$result			=	array( 'can', CBPTXT::T("location") );	// CBPTXT::T("You can access to this location with following plans: ");													// . implode( ', ', $accessPlans );
					$access			=	false;
				} elseif ( $accessPlans === true ) {
					$result			=	array( 'have', CBPTXT::T("location") );	// CBPTXT::T("You have access now to this location !");
					$access			=	true;
				} else {
					$result			=	array( 'unknown', CBPTXT::T("location"), $accessPlans );	// 'Unknown location access result: ' . var_export( $accessPlans, true );
					$access			=	false;
				}
				break;

			case 'sections':					// section list:
				$sectionId			=	(int) $accessvalue;
				$accessPlans		=	$cbpaidBot->checkAccess( $userId, $sectionId, 'cpaycontent_sections', 'cpaycontent_sections_list', true );
				if ( is_array( $accessPlans ) ) {
					$result			=	array( 'can', CBPTXT::T("content section") );	// CBPTXT::T("You can access to this content section with following plans: ");												// . implode( ', ', $accessPlans );
					$access			=	false;
				} elseif ( $accessPlans === true ) {
					$result			=	array( 'have', CBPTXT::T("content section") );	// CBPTXT::T("You have access now to this content section !");
					$access			=	true;
				} else {
					$result			=	array( 'unknown', CBPTXT::T("content section"), $accessPlans );	// 'Unknown content section access result: ' . var_export( $accessPlans, true );
					$access			=	false;
				}
				break;

			case 'categories':
				$categoryId			=	(int) $accessvalue;
				$accessPlans		=	$cbpaidBot->checkAccess( $userId, $categoryId, 'cpaycontent_categories', 'cpaycontent_categories_list', true );

				$sectionId			=	$cbpaidBot->getSectionOfCategory( $categoryId );
				if ( $sectionId ) {
					$accessPlansSection	=	$cbpaidBot->checkAccess( $userId, $sectionId, 'cpaycontent_sections', 'cpaycontent_sections_list', true );
				} else {
					$accessPlansSection	=	array();
				}

				if ( is_array( $accessPlans ) ) {
					if ( is_array( $accessPlansSection ) ) {
						$result		=	array( 'can', CBPTXT::T("content category") . ' ' . CBPTXT::T("or"). ' ' . CBPTXT::T("content section in which this content category is located") );	// CBPTXT::T("You can access to this content category with following plans: ")												// . implode( ', ', $accessPlans )
																			//.	CBPTXT::T("You can access to the whole content section enclosing this content category with following plans: ");			// . implode( ', ', $accessPlansSection );
						$access		=	false;
					} elseif ( $accessPlansSection === null ) {
						$result		=	array( 'can', CBPTXT::T("content category") );	// CBPTXT::T("You can access to this content category with following plans: ");											// . implode( ', ', $accessPlans );
						$access		=	false;
					} elseif ( $accessPlansSection === true ) {
						$result		=	array( 'have', CBPTXT::T("content section in which this content category is located") );	// CBPTXT::T("You have access now to the whole content section in which this content category is located !");
						$access		=	true;
					} else {
						$result		=	array( 'unknown', CBPTXT::T("content section"), $accessPlansSection );	// 'Unknown content section access result: ' . var_export( $accessPlans, true );
						$access		=	false;
					}
				} elseif ( $accessPlans === null ) {
					if ( is_array( $accessPlansSection ) ) {
						$result		=	array( 'can', CBPTXT::T("content section in which this content category is located") );	//CBPTXT::T("You can access to the whole content section enclosing this content category with following plans: ");			// . implode( ', ', $accessPlansSection );
						$access		=	false;
					} elseif ( $accessPlansSection === null ) {
						$result		=	CBPTXT::T("These content categories are not under category or section access control");
						$access		=	true;
					} elseif ( $accessPlansSection === true ) {
						$result		=	array( 'have', CBPTXT::T("content section in which this content category is located") );	//CBPTXT::T("You have access now to the whole content section in which this content category is located !");
						$access		=	true;
					} else {
						$result		=	array( 'unknown', CBPTXT::T("content section"), $accessPlansSection );	// 'Unknown content section access result: ' . var_export( $accessPlans, true );
						$access		=	false;
					}
				} elseif ( $accessPlans === true ) {
					$result			=	array( 'have', CBPTXT::T("content category") );	// CBPTXT::T("You have access now to this content categories !");
					$access			=	true;
				} else {
					$result			=	array( 'unknown', CBPTXT::T("content category"), $accessPlans );	// 'Unknown content categories access result: ' . var_export( $accessPlans, true );
					$access			=	false;
				}
				break;

			case 'contentdisplay':
				$contentId			=	(int) $accessvalue;

				global $_CB_database;

				$row				=	null;
				$sql				=	'SELECT * FROM #__content WHERE id = ' . (int) $contentId;
				$_CB_database->setQuery( $sql );
				$contentExists		=	$_CB_database->loadObject( $row );
				if ( $contentExists ) {
					/** @var StdClass $row */
					$_cbACL			=&	cbpaidBotAclApi::getInstance();
					$accessPlans	=	$_cbACL->_cb_checkMultiAcl_Ok_or_Plans( $_CB_framework->myId(), $row->id, isset( $row->catid ) ? $row->catid : null, isset( $row->sectionid ) ? $row->sectionid : null, -1 );
					if ( is_array( $accessPlans ) ) {
						$accessPlansWrite	=	$_cbACL->_cb_checkMultiAcl( $_CB_framework->myId(), $row->id, isset( $row->catid ) ? $row->catid : null, isset( $row->sectionid ) ? $row->sectionid : null, count( $_cbACL->cbContentAclRights ) -1 );
						if ( $accessPlansWrite === true ) {
							$result	=	CBPTXT::T("You have access to this content item");
							$access	=	true;
						} elseif ( is_array( $accessPlansWrite ) && ( count( $accessPlansWrite ) > 0 ) ) {
							$result	=	array( 'can', CBPTXT::T("content item") );	// CBPTXT::T("You can access to this article with following plans: ")														// . implode( ', ', $accessPlans ) 
																		//.	CBPTXT::T("You can access with write access to this article with following plans: ");									// . implode( ', ', $accessPlansWrite );
							$access	=	false;
						} else {
							$result	=	array( 'can', CBPTXT::T("content item") );	// CBPTXT::T("You can access to this article with following plans: ");														// . implode( ', ', $accessPlans );
							$access	=	false;
						}
					} elseif ( $accessPlans === true ) {
						$result		=	array( 'have', CBPTXT::T("content item") );	// CBPTXT::T("You have access now to this article !");
						$access		=	true;
					} else {
						$result		=	array( 'unknown', CBPTXT::T("content item"), $accessPlans );	// 'Unknown access result: ' . var_export( $accessPlans, true );
						$access		=	false;
					}
				} else {
					$result			=	CBPTXT::T("This content item does not exist");
					$access			=	true;
					$accessPlans	=	array();
				}
				break;

			default:
				// Hacking or PCI-DSS intrusion trial:
				$result			=	CBPTXT::T("This redirection URL is invalid.");
				$access			=	true;
				$accessPlans	=	array();
				break;
		}
		if ( $access ) {
			if ( is_array( $result ) ) {
				switch ( $result[0] ) {
					case 'have':
						$return		=	sprintf( CBPTXT::Th("You have now access to this %s"), $result[1] );
						//TBD later in a safe way (FS#259): or with $_SESSION
						// $realUrl	=	base64_decode( $accessurl );
						// $return		.=	'<a href="' . $_CB_framework->getCfg( 'live_site' ) . '/index.php?' . htmlspecialchars( $realUrl ) . '">Click here to access</a>';
						break;

					default:
						$return		=	"Unknown access allowed result: " . var_export( $result, true );
						break;
				}
			} else {
				$return				=	$result;
			}
		} else {
			if ( is_array( $result ) ) {
				switch ( $result[0] ) {
					case 'can':
						if ( count( $accessPlans ) > 0 ) {
							$params	=&	cbpaidApp::settingsParams();
							$subTxt	=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$result	=	sprintf( CBPTXT::Th("You can access this %s with following %s plans:"), $result[1], $subTxt );

							// CB login return-to after login URL:
							if ( $accessurl ) {
								global $cbSpecialReturnAfterLogin;
								$url =	base64_decode( $accessurl );
								if ( ! preg_match( '#https?://#i', $url ) ) {
									$cbSpecialReturnAfterLogin	=	'index.php?' . $url;
								}
							}

							// We need to also display child plans of the $accessPlans as some might be needed (mandatory):
							$plans	=	cbpaidSubscriptionsMgr::getInstance()->getUpgradablePlansWithChildrensForPlans( $accessPlans, $user );
							$return	=	cbpaidControllerOffer::displaySpecificPlans( $plans, null, $user, $result );
						} else {
							$return	=	sprintf( CBPTXT::Th("You can not access this %s") . '.', $result[1] );
						}
						break;

					case 'unknown':
						$return		=	sprintf( CBPTXT::Th("Unknown %s access result: %s") . '.', $result[1], var_export( $result[2], true ) );
						break;

					default:
						$return		=	"Unknown access allowed result" . ': ' . var_export( $result, true );
						break;
				}
			} else {
				$return				=	$result;
			}
		}
		return $return;
	}
	/**
	 * Utility function to decode a Request array from an base64-encoded urlencoded-string
	 *
	 * @param  string   $accessvalue
	 * @return array
	 */
	protected function _decodeArrayUrl( $accessvalue ) {
		$getArray		=	array();
		$url			=	base64_decode( $accessvalue );
		$urlgetArray	=	explode( '&', $url );
		foreach ( $urlgetArray as $v ) {
			$parts		=	explode( '=', $v );
			if ( count( $parts ) == 2 ) {
				$getArray[urldecode( $parts[0] )]	=	urldecode( $parts[1] );
			}
		}
		return $getArray;
	}
}
