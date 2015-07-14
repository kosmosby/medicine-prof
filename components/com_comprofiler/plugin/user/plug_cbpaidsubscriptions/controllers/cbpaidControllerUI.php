<?php
/**
* @version $Id: cbpaidControllerUI.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Subscriptions GUI and MVC classes
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Frontend GUI class for CBSubs frontend
 */
class cbpaidControllerUI extends cbpaidBaseClass {
	/**
	 * returns HTML to display the registration tab/area for the form content to choose a plan
	 * (frontend)
	 *
	 * @param  UserTable  $user             reflecting the user being displayed (here null)
	 * @param  string     $plansTitle       Field-name for the plan choice (for labels)
	 * @param  string     $reason           payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  boolean    $noPlan           show a 'No plan' choice additionally
	 * @return string                       HTML for display
	 */
	public function getShowRegistrationPlans( $user, $plansTitle, $reason, $noPlan = false ) {
		$plansMgr				=&	cbpaidPlansMgr::getInstance();
		$plans					=&	$plansMgr->loadPublishedPlans( null, true, 'registration', null );
		$allPlans				=	$plansMgr->loadPublishedPlans( null, true, null, null );

		$specificChoice			=	$this->_unsetIrrelevantPlans( $plans );
		$this->_unsetNotProposedRegistrationPlans( $plans );
		if ( $specificChoice ) {
			$chosenPlans		=	$this->_chooseMaxPlans( $plans );
		} else {
			$chosenPlans		=	$this->_planGetAndCheckReqParamArray( $user, 'plan', $plans, true, $reason );
		}
		if ( ! is_array( $chosenPlans ) ) {
			$chosenPlans		=	array();		// no need to display errors here.
		}
		$return					=	$this->_getFormattedPlans( $user, $plans, $plansTitle, $chosenPlans, $reason, $noPlan );

		// finally generate JS code if needed, taking in account hidden fields of all plans:
		$this->_addJsCodeIfNeeded( $allPlans, $reason );

		return $return;
	}

	/**
	 * Sets to show only plans (or gets the state with FALSE)
	 *
	 * @param  int[]|boolean|null  $plans
	 * @return int[]|null
	 */
	public function setShowOnlyPlans( $plans ) {
		static $state			=	null;

		if ( $plans === false ) {
			return $state;
		}

		if ( is_array( $plans ) ) {
			cbArrayToInts( $plans );
		}
		$state					=	$plans;
		return null;
	}
	/**
	 * Sets selected plan (or gets the state with FALSE)
	 * @param  int[]|boolean  $plans
	 * @return array|null
	 */
	public function setSelectedPlans( $plans ) {
		static $state			=	null;

		if ( $plans === false ) {
			return $state;
		}

		if ( is_array( $plans ) ) {
			cbArrayToInts( $plans );
		}
		$state					=	$plans;
		return null;
	}
	/**
	 * Adds to $plansToShowOnly the $plans 's parents which are not already there (and their parents too)
	 *
	 * @param  cbpaidProduct[]  $plans
	 * @param  int[]            $plansToShowOnly
	 * @return void
	 */
	protected function _includeParents( &$plans, &$plansToShowOnly ) {
		foreach ( $plansToShowOnly as $planId ) {
			$pId							=	$planId;
			while ( isset( $plans[$pId] ) && $plans[$pId]->parent ) {
				$pId						=	$plans[$pId]->parent;
				if ( ! in_array( $pId, $plansToShowOnly ) ) {
					$plansToShowOnly[]		=	$pId;
				}
			}
		}
	}
	/**
	 * Remove from $plans the plans which cannot be proposed for registration
	 *
	 * @param  cbpaidProduct[]  $plans
	 * @return void
	 */
	protected function _unsetNotProposedRegistrationPlans( &$plans ) {
		$plansToShowOnly						=	$this->setShowOnlyPlans( false );
		if ( $plansToShowOnly === null ) {
			$plansToShowOnly					=	array();
		}
		$unsettedParents						=	array();
		do {
			$onemoreLevel						=	false;
			foreach ( $plans as $k => $p ) {
				if ( ( ( $plans[$k]->get( 'propose_registration' ) != 1 ) && ! in_array( $p->id, $plansToShowOnly ) )
					 || isset( $unsettedParents[$plans[$k]->get( 'parent' )] ) )
				{
					unset( $plans[$k] );
					$unsettedParents[$p->id]	=	true;
					$onemoreLevel				=	true;
				}
			}
		} while ( $onemoreLevel );
	}
	/**
	 * Remove from $plans the irrelevant plans (which are not there to show)
	 *
	 * @param  cbpaidProduct[]  $plans
	 * @return boolean
	 */
	protected function _unsetIrrelevantPlans( &$plans ) {
		$plansToShowOnly	=	$this->setShowOnlyPlans( false );
		$includeParents		=	true;
		if ( is_array( $plansToShowOnly ) ) {
			/** @var $plansToShowOnly int[] */
			if ( $includeParents ) {
				$this->_includeParents( $plans, $plansToShowOnly );
			}
			foreach ( $plans as $k => $p ) {
				if ( ! in_array( $p->id, $plansToShowOnly ) ) {
					unset( $plans[$k] );
				}
			}
			return true;
		}
		return false;
	}
	/**
	 * REmove from $subs the subscriptions which are not needed to show a plan from $plans
	 *
	 * @param  cbpaidSomething[]  $subs
	 * @param  cbpaidProduct[]    $plans
	 * @return boolean
	 */
	protected function _unsetIrrelevantSubscriptions( &$subs, &$plans ) {
		$plansToShowOnly	=	$this->setShowOnlyPlans( false );
		$includeParents		=	true;
		if ( is_array( $plansToShowOnly ) ) {
			/** @var $plansToShowOnly int[] */
			if ( $includeParents ) {
				$this->_includeParents( $plans, $plansToShowOnly );
			}
			foreach ( $subs as $k => $s ) {
				$p				=	$s->getPlan();
				if ( ! in_array( $p->id, $plansToShowOnly ) ) {
					unset( $subs[$k] );
				}
			}
			return true;
		}
		return false;
	}
	/**
	 * Unsets non-reactivable subscriptions from $sub as of $now
	 *
	 * @param  cbpaidSomething[]  $subs
	 * @param  int                $now
	 * @return void
	 */
	protected function _unsetNonReactivableSubscriptions( &$subs, $now ) {
			foreach ( $subs as $k => $sub ) {
				if ( in_array( $sub->status, array( 'X', 'C' ) ) && ! ( $sub->checkIfRenewable( $now ) ) ) {
					unset( $subs[$k] );
				}
			}
	}
	/**
	 * Auto-chooses and returns the plans that need to be subscribed
	 *
	 * @param  cbpaidProduct[]  $plans
	 * @return int[]
	 */
	protected function _chooseMaxPlans( &$plans ) {
		$chosenPlans			=	$this->setSelectedPlans( false );
		if ( $chosenPlans === null ) {
			$chosenPlans		=	array();
			$chosenPlansParents	=	array();
			// Method to auto-select minimal number of plans:
			// First check if there is a single plan, select it:
			if ( count( $plans ) == 1 ) {
				$chosenPlans	=	array_keys( $plans );
			} else {
				// Then try to select only exclusive plans:
				foreach ( array_keys( $plans ) as $k ) {
					if ( ( $plans[$k]->exclusive != 0 ) && ! in_array( $plans[$k]->parent, $chosenPlansParents ) ) {
						if ( ( $plans[$k]->parent == 0 ) || in_array( $plans[$k]->parent, $chosenPlans ) ) {
							$chosenPlans[]			=	$k;
							$chosenPlansParents[]	=	$plans[$k]->parent;
						}
					}
				}
				// Otherwise keep the default plans settings make their work.
			}
			/* Old Method of CBSubs 1.0 and 1.1.x to select maximal number of plans:
			$parentsOfExclPlan	=	array();
			foreach ( array_keys( $plans ) as $k ) {
				if ( ( $plans[$k]->exclusive == 0 ) || ! in_array( $plans[$k]->parent, $parentsOfExclPlan ) ) {
					if ( ( $plans[$k]->parent == 0 ) || in_array( $plans[$k]->parent, $chosenPlans ) ) {
						$chosenPlans[]	=	$k;
						if ( $plans[$k]->exclusive != 0 ) {
							$parentsOfExclPlan[]	=	$plans[$k]->parent;
						}
					}
				}
			}
			*/
		}
		return $chosenPlans;
	}
	/**
	* Generates the HTML to display the plans and upgrade possibilities for subscription tab/area
	* (frontend and backend)
	*
	* @param  UserTable  $user        Reflecting the user being displayed (here null)
	* @param  int        $subsAccess  0 has only read access, 1 has user access, 2 reserved for future Super-admin access
	* @return mixed                   either string HTML for tab content, or false if ErrorMSG generated
	*/
	public function getShowSubscriptionUpgrades( $user, $subsAccess ) {
		global $_CB_framework;

		$ui						=	$_CB_framework->getUi();
		$return 				=	'';
		$now					=	$_CB_framework->now();
		
		
		$params				 	=&	$this->params;
		$plansTitle			 	=	$params->get('regTitle');
		$upgradePlansEnabled 	=	( $ui == 2 ) || ( $subsAccess && ( $params->get( 'upgradePlansEnabled', 1 ) == '1' ) );
		$showRenewButtons		=	( $ui == 2 ) || ( $subsAccess && ( $params->get( 'showRenewButtons', '1' ) == '1' ) );
		$showUnsubscribeButtons =	( $ui == 2 ) || ( $subsAccess && ( $params->get( 'showUnsubscribeButtons', '0' ) == '1' ) );
		$plansToShowOnly		=	$this->setShowOnlyPlans( false );

		$subscriptions			=	array();
		if ( ( $ui == 2 ) && ( $user->id == 0 ) ) {
			// creating a new user in backend: propose registration plans:
			$plansMgr			=&	cbpaidPlansMgr::getInstance();
			$plans				=&	$plansMgr->loadPublishedPlans( null, true, 'registration', null );
		} else {
			$paidsubsManager	=&	cbpaidSubscriptionsMgr::getInstance();
			$plans				=	$paidsubsManager->getUpgradeAndRenewalPossibilities( $ui, $user->id, $now, $subscriptions, $plansToShowOnly, $subsAccess );

			$this->_unsetNonReactivableSubscriptions( $subscriptions, $now );
			$this->_unsetIrrelevantSubscriptions( $subscriptions, $plans );
		}
		if ( $this->_unsetIrrelevantPlans( $plans ) ) {
			$chosenPlans		=	$this->_chooseMaxPlans( $plans );
		} else {
			$chosenPlans		=	$this->_planGetAndCheckReqParamArray( $user, 'plan', $plans, false, null );
		}
		if ( ! is_array( $chosenPlans ) ) {
			$chosenPlans		=	array();		// no need to display errors here.
		}
		// display subscriptions and upgrade possibilities:
		if ( ( count( $subscriptions ) > 0 ) || ( $upgradePlansEnabled && ( count( $plans ) > 0 ) ) ) {
			$this->_outputRegTemplate();
			if  ( count( $subscriptions ) > 0 ) {

				// Render subscriptions for renewal/cancellations:

				$htmlSubscribed	=	$this->_getSubscribedPlans( $now, $user, $subscriptions, $plansTitle, $showRenewButtons, $showUnsubscribeButtons );
				$viewer			=	cbpaidTemplateHandler::getViewer( null, 'usersubscriptions' );
				/** @var $viewer cbpaidusersubscriptionsView */
				$viewer->setModel( $subscriptions );
				$return			.=	$viewer->drawUserSomethings( $user, $subsAccess, $htmlSubscribed );
			}
			if ( $upgradePlansEnabled && ( count( $plans ) > 0 ) ) {
				if ( ( $ui == 1 ) && $subsAccess ) {
					$plansDisplayed	=	0;
					$buttonTexts	=	array();
					foreach ( array_keys( $plans ) as $id ) {
						if ( ! ( isset( $plans[$id]->_drawOnlyAsContainer ) && $plans[$id]->_drawOnlyAsContainer ) ) {
							++$plansDisplayed;
							$button	=	$plans[$id]->buttonText( 'upgrade' );		// CBPTXT::T("Upgrade")
							$buttonTexts[$button]	=	$button;
						}
					}
					if ( $plansDisplayed > 0 ) {

						// Render plans for upgrades:

						$htmlUpgrades	=	$this->_getFormattedPlans( $user, $plans, $plansTitle, $chosenPlans, 'U' );
						if ( $htmlUpgrades ) {
							$htmlspecialcharedBaseUrl	=	$this->getHttpsAbsURLwithParam( array( 'Itemid' => 0, 'user' => $user->id ), 'pluginclass' );
							$hiddenFlds	=	'<input type="hidden" name="user" value="' . $user->id . '" />';
		//					$hiddenFlds	.=	'<input type="hidden" name="' . $this->_getPagingParamName("subscription") . '" value="' . $plans[0]->_subscriptionToUpdate . '" />';		//TBD: select which if more than 1...
							$hiddenFlds	.=	'<input type="hidden" name="' . $this->_getPagingParamName("act") . '" value="upgrade" />';
							$hiddenFlds	.=	cbGetSpoofInputTag( 'plugin' );
							$buttonName	=	$this->_getPagingParamName("cbregUpgrade");

							/** @var $viewer cbpaiduserupgradeplansView */
							$viewer		=	cbpaidTemplateHandler::getViewer( null, 'userupgradeplans' );
							$viewer->setModel( $plans );
							$return		.=	$viewer->drawUserUpgradePlans( $user, $plansDisplayed, $htmlUpgrades, $htmlspecialcharedBaseUrl, $hiddenFlds, $buttonTexts, $buttonName );

							$this->_addJsCodeIfNeeded( $plans, 'U' );
						}
					} else {
						// no upgrade possibility
					}
				} elseif ( $ui == 2 ) {
					$subTxt		=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
//					$return		.=	'<input type="hidden" name="' . $this->_getPagingParamName("subscription") . '" value="' . $plans[0]->_subscriptionToUpdate . '" />';		//TBD: select which if more than 1...
					$return		.=	'<input type="hidden" name="' . $this->_getPagingParamName("act") . '" value="upgrade" />';
					
					$return		.=	'<div class="contentheading" id="cbregUpgradePossibilities">';
					if ( count( $plans ) == 1 ) {
						$return	.=	sprintf( CBPTXT::Th("Current %s upgrade possibility:"), $subTxt );
					} else {
						$return	.=	sprintf( CBPTXT::Th("Current %s upgrade possibilities:"), $subTxt );
					}
					$return		.=	"</div>\n";

					$return		.=	$this->_getFormattedPlans( $user, $plans, $plansTitle, $chosenPlans, 'U', true );
					$this->_addJsCodeIfNeeded( $plans, 'U' );
				}
			}
		}
		return  $return;
	}

	/**
	 * Shows an unsubscription confirmation form (frontend)
	 *
	 * @param UserTable     $user
	 * @param string        $introText
	 * @param int           $planId
	 * @param int           $subscriptionId
	 * @return string|null
	 */
	public function showUnsubscribeForm( &$user, $introText, $planId, $subscriptionId ) {
		global $_CB_database, $_CB_framework;

		$ui				=	$_CB_framework->getUi();
		// get the most recent payment basket for that user and plan, and with that subscription if $subscriptionId != null:
		$subscription		=	new cbpaidUsersubscriptionRecord( $_CB_database );
		$subscriptionLoaded	=	$subscription->load( (int) $subscriptionId );
		if ( $subscriptionLoaded && ( $subscription->user_id == $user->id ) ) {
			$base_url = $this->getHttpsAbsURLwithParam( array( 'Itemid' => 0, 'user' => $user->id ), 'pluginclass' );
			$return  = '<form method="post" class="cbregUnsubscribeForm" action="'.$base_url.'">';
			$return	.=	'<div id="cbregUnsubIntro">' . $introText . '</div>';
			$return .= '<input type="hidden" name="user" value="'.$user->id.'" />';
			$return .= '<input type="hidden" name="'.$this->_getPagingParamName("subscription[]").'" value="' . (int) $subscriptionId . '" />';
			$return .= '<input type="hidden" name="'.$this->_getPagingParamName("plan[]").'" value="' . (int) $planId . '" />';
			$return .= '<input type="hidden" name="'.$this->_getPagingParamName("act").'" value="confirm_unsub" />';
			$return .= $this->_drawActionButton( $user, $ui, $subscription, $subscription->plan_id, CBPTXT::T("Yes: Unsubscribe"), 'confirm_unsubscribe', CBPTXT::T("This unsubscription is with immediate effect, without refund, and cannot be undone. Are you sure ?") );
			$return .= $this->_drawActionButton( $user, $ui, $subscription, $subscription->plan_id, CBPTXT::T("No: Stay subscribed"), 'display_subscriptions' );
			$return .= cbGetSpoofInputTag( 'plugin' );
			$return .= '</form>';
		} else {
			$params	=&	cbpaidApp::settingsParams();
			$subTxt	=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
			$this->_setErrorMSG( sprintf( CBPTXT::T("No %s found"), $subTxt ) );
			$return	=	null;
		}
		return $return;
	}
	/**
	 * Gets and checks chosen registration plans
	 *
	 * @param  array      $postdata    $_POST array
	 * @param  UserTable  $user        reflecting the user being registered
	 * @return cbpaidProduct[]|string  chosen plans, checked to be allowed or required corresponding to proposed plans
	 */
	public function & getAndCheckChosenRegistrationPlans( /** @noinspection PhpUnusedParameterInspection */ $postdata, &$user ) {
		$plansMgr		=&	cbpaidPlansMgr::getInstance();
		$plans			=&	$plansMgr->loadPublishedPlans( null, true, 'registration', null );

		// in case we have an error at registration:
		$onlyPlans		=	$this->_getReqParam( 'onlyplans' );
		if ( $onlyPlans && is_string( $onlyPlans ) ) {
			$onlyPlans	=	explode( ',', $onlyPlans );
			$this->setShowOnlyPlans( $onlyPlans );
		}

		$this->_unsetNotProposedRegistrationPlans( $plans );

		$selectedPlans	=&	$this->_planGetAndCheckReqParamArray( $user, 'plan', $plans, true, 'N', true );
		return $selectedPlans;
	}
	/**
	 * Gets and checks chosen upgrade plans
	 *
	 * @param  array      $postdata  $_POST array
	 * @param  UserTable  $user      reflecting the user being registered
	 * @param  int        $now       unix-time
	 * @return int[]                 chosen plans, checked to be allowed or required corresponding to proposed plans
	 */
	public function & getAndCheckChosenUpgradePlans( /** @noinspection PhpUnusedParameterInspection */ $postdata, &$user, $now ) {
		global $_CB_framework;

		$subscriptions			=	array();		// return value of getUpgradeAnd... function below
		$paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();

		$plansToShowOnly		=	$this->setShowOnlyPlans( false );

		$plans					=&	$paidsubsManager->getUpgradeAndRenewalPossibilities( $_CB_framework->getUi(), $user->id, $now, $subscriptions, $plansToShowOnly );

		$selectedPlans			=&	$this->_planGetAndCheckReqParamArray( $user, 'plan', $plans, false, 'U', true );
		return $selectedPlans;
	}
	/**
	* Generates the HTML to display the plans and upgrade possibilities for subscription tab/area
	* (frontend and backend)
	*
	* @param  int        $invoicesNumber   array of cbpaidPaymentBasket  of Completed and Pending baskets
	* @param  string     $invoicesListUrl  URL for the link (sefed)
	* @param  UserTable  $user             reflecting the user being displayed (here null)
	* @param  boolean    $itsmyself        user is logged in user
	* @param  string     $periodText       if non-empty, text of the period showing invoices
	* @return mixed                        either string HTML for tab content, or false if ErrorMSG generated
	*/
	public function showInvoicesListLink( $invoicesNumber, $invoicesListUrl, &$user, $itsmyself, $periodText ) {
		$this->_outputRegTemplate();
		/** @var $renderer cbpaidInvoicesListView */
		$renderer			=	cbpaidTemplateHandler::getViewer( null, 'invoiceslist' );
		$renderer->setModel( array() );
		return $renderer->drawProfileInvoicesView( $invoicesListUrl, $invoicesNumber, $user, $itsmyself, $periodText );
	}
	/**
	* Generates the HTML to display the plans and upgrade possibilities for subscription tab/area
	* (frontend and backend)
	*
	* @param  array      $invoices    array of cbpaidPaymentBasket  of Completed and Pending baskets
	* @param  UserTable  $user        reflecting the user being displayed (here null)
	* @param  boolean    $itsmyself   user is logged in user
	* @param  string     $periodText  if non-empty, text of the period showing invoices
	* @return mixed                   either string HTML for tab content, or false if ErrorMSG generated
	*/
	public function showInvoicesList( &$invoices, &$user, $itsmyself, $periodText ) {
		$return 			=	'';
		$invoicesNumber		=	count( $invoices );
		if ( $invoicesNumber > 0 ) {
			$this->_outputRegTemplate();
			/** @var $renderer cbpaidInvoicesListView */
			$renderer			=	cbpaidTemplateHandler::getViewer( null, 'invoiceslist' );
			$renderer->setModel( $invoices );
			$return			=	$renderer->drawInvoicesList( $invoicesNumber, $user, $itsmyself, $periodText );
		}
		return  $return;
	}
	/**
	* Generates the HTML to display a clickable link to display a printable invoice
	*
	* @param  cbpaidPaymentBasket  $invoice          invoice to display when clicking on the link
	* @param  UserTable            $user             reflecting the user being displayed (here null)
	* @param  boolean              $itsmyself        user is logged in user
	* @param  string               $linkContentHtml  content of the link
	* @return mixed                : either string HTML for tab content, or false if ErrorMSG generated
	*/
	public function getInvoiceShowAhtml( $invoice, /** @noinspection PhpUnusedParameterInspection */ $user, /** @noinspection PhpUnusedParameterInspection */ $itsmyself, $linkContentHtml ) {
		$invoiceDetailsUrl	=	$this->baseClass->getInvoiceUrl( $invoice );
		$invoices			=	array( $invoice );
		/** @var $renderer cbpaidInvoicesListView */
		$renderer			=	cbpaidTemplateHandler::getViewer( null, 'invoiceslist' );
		$renderer->setModel( $invoices );
		return $renderer->drawInvoiceLink( $linkContentHtml, $invoiceDetailsUrl );
	}
	/**
	* Generates the HTML to display a printable invoice in HTML
	* (frontend and backend)
	*
	* @param  cbpaidPaymentBasket  $invoice          array of   of Completed and Pending baskets
	* @param  UserTable            $user             reflecting the user being displayed (here null)
	* @param  boolean              $itsmyself        user is logged in user
	* @param  array                $extraStrings     extra strings for replacements
	* @param  boolean              $displayButtons   Displays the PRINT and CLOSE buttons
	* @return mixed                : either string HTML for tab content, or false if ErrorMSG generated
	*/
	public function showInvoice( &$invoice, &$user, /** @noinspection PhpUnusedParameterInspection */ $itsmyself, &$extraStrings, $displayButtons = true ) {
		$this->_outputRegTemplate();
		/** @var $renderer cbpaidInvoiceView */
		$renderer			=	cbpaidTemplateHandler::getViewer( null, 'invoice' );
		$renderer->setModel( $invoice );
		return $renderer->drawInvoice( $user, $extraStrings, $displayButtons );
	}
	/**
	 * Shows a basket view
	 *
	 * @param  cbpaidPaymentBasket  $basket             Model
	 * @param  string               $summaryText        Text for the table summary
	 * @param  string               $captionText        Text for the table caption
	 * @param  array                $displayColumns     Keyed array columnName => columnTitle
	 * @param  array                $totalizerColumns   Keyed array columnName => columnTitle
	 * @param  array                $itemsLinesCols     Array of keyed arrays: lineIdx => columnName => HTMLcellContent
	 * @param  array                $totalizerLinesCols Array of keyed arrays: lineIdx => columnName => HTMLcellContent
	 * @return string
	 */
	public function showBasket( &$basket, $summaryText, $captionText, $displayColumns, $totalizerColumns, $itemsLinesCols, $totalizerLinesCols ) {
		$this->_outputRegTemplate();
		/** @var $renderer cbpaidBasketView */
		$renderer			=	cbpaidTemplateHandler::getViewer( null, 'basket' );
		$renderer->setModel( $basket );
		return $renderer->drawBasket( $summaryText, $captionText, $displayColumns, $totalizerColumns, $itemsLinesCols, $totalizerLinesCols );
	}
	/**
	 * Draws a form posting to $getParams with CSS class $cssClass around $settings with $warnings at top, and $formHiddens hidden elements. Also add validation languages into head.
	 *
	 * @param  string        $settings
	 * @param  string        $warning
	 * @param  string[]      $formHiddens
	 * @param  string|array  $getParams
	 * @param  string|null   $cssClass
	 * @return string
	 */
	public function drawForm( $settings, $warning, $formHiddens, $getParams, $cssClass = null ) {
		global $_CB_framework;

		$html				=	'';
		if ( $warning ) {
			$html			.=	'<div class="error">' . $warning . '</div>' . "\n";
		}
		if ( is_array( $getParams ) ) {
			$postUrl		=	'index.php';
			if ( $getParams && ( count( $getParams ) > 0 ) ) {
				foreach ( $getParams as $k => $v ) {
				 	$getParams[$k]	=	$k . '=' . htmlspecialchars( urlencode( $v ) );
				 }
				 $postUrl	.=	'?' . implode( '&', $getParams );
			}
		} else {
			$postUrl		=	$getParams;
		}
		if ( $formHiddens !== null ) {
			$html			.=	'<form enctype="multipart/form-data" action="' . cbSef( $postUrl ) . '" method="post" name="adminForm" id="cbAdminFormForm" class="cb_form cbregfrontendform' . ( $cssClass ? ' ' . $cssClass : '' ) . '">' . "\n";
		}
		if ( $formHiddens !== null ) {
			foreach ( $formHiddens as $k => $v ) {
				$html		.=	"\t" . '<input type="hidden" name="' . htmlspecialchars( $k ) . '" value="' . htmlspecialchars( $v ) . '" />' . "\n";
			}
			$html			.=	cbGetSpoofInputTag( 'plugin' );
		}
		$html				.=	$settings;
		if ( $formHiddens !== null ) {
			$html			.=	"</form>\n";
		}

		cbimport( 'language.cbteamplugins' );

		ob_start();
?>
$.extend(jQuery.validator.messages, {
		required: "<?php echo addslashes( CBPTXT::T("This field is required.") ); ?>",
		remote: "<?php echo addslashes( CBPTXT::T("Please fix this field.") ); ?>",
		email: "<?php echo addslashes( CBPTXT::T("Please enter a valid email address.") ); ?>",
		url: "<?php echo addslashes( CBPTXT::T("Please enter a valid URL.") ); ?>",
		date: "<?php echo addslashes( CBPTXT::T("Please enter a valid date.") ); ?>",
		dateISO: "<?php echo addslashes( CBPTXT::T("Please enter a valid date (ISO).") ); ?>",
		number: "<?php echo addslashes( CBPTXT::T("Please enter a valid number.") ); ?>",
		digits: "<?php echo addslashes( CBPTXT::T("Please enter only digits.") ); ?>",
		creditcard: "<?php echo addslashes( CBPTXT::T("Please enter a valid credit card number.") ); ?>",
		equalTo: "<?php echo addslashes( CBPTXT::T("Please enter the same value again.") ); ?>",
		accept: "<?php echo addslashes( CBPTXT::T("Please enter a value with a valid extension.") ); ?>",
		maxlength: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter no more than {0} characters.") ); ?>"),
		minlength: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter at least {0} characters.") ); ?>"),
		rangelength: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter a value between {0} and {1} characters long.") ); ?>"),
		range: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter a value between {0} and {1}.") ); ?>"),
		max: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter a value less than or equal to {0}.") ); ?>"),
		min: $.validator.format("<?php echo addslashes( CBPTXT::T("Please enter a value greater than or equal to {0}.") ); ?>")
});
$('#cbAdminFormForm').validate( {
		errorClass: 'cb_result_warning',
		// debug: true,
		cbIsOnKeyUp: false,

		highlight: function( element, errorClass ) {
			$( element ).parents('.fieldCell').parent().addClass( 'cbValidationError' );	// tables		// .parents('tab-page').sibblings('tab-row');
			$( element ).parents('.cb_form_line').addClass( 'cbValidationError' );				// divs
		},
		unhighlight: function( element, errorClass ) {
			$( element ).parents('.fieldCell').parent().removeClass( 'cbValidationError' );	// tables
			$( element ).parents('.cb_form_line').removeClass( 'cbValidationError' );			// divs
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			element.parent().append( error[0] );		// tables
			element.parent().append( error[0] );			// divs
		},

		onkeyup: function(element) {
			if ( element.name in this.submitted || element == this.lastElement ) {
				// avoid remotejhtml rule onkeyup
				this.cbIsOnKeyUp = true;
				this.element(element);
				this.cbIsOnKeyUp = false;
			}
        }
} );
$('#cbAdminFormForm input:checkbox,#cbAdminFormForm input:radio').click( function() {
	$('#cbAdminFormForm').validate().element( $(this) );
} );
<?php
		
		$cbjavascript	=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->outputCbJQuery( $cbjavascript, array( 'metadata', 'validate' ) );

		return $html;
	}

	/**
	 * returns HTML code to show a renewal button for a  subscribed plan
	 * @access private
	 *
	 * @param  UserTable           $user               reflecting the user being displayed
	 * @param  int                 $ui                 1: frontend, 2: backend
	 * @param  cbpaidUsersubscriptionRecord  $subscription       subscription being paid/renewed/reactivated
	 * @param  int                 $planId             plan id being chosen
	 * @param  string              $renewButtonText    Text to display in button !!!! HTML-safe
	 * @param  string              $renewButtonAction  Action string to post
	 * @param  string              $areYouSureText     Ask question are you sure: text
	 * @return string                                  HTML for display
	 */
	protected function _drawActionButton( &$user, $ui, &$subscription, $planId, $renewButtonText, $renewButtonAction, $areYouSureText = null ) {
		$return = '';
		if ( $ui == 1 ) {
			$base_url = $this->getHttpsAbsURLwithParam( array( 'Itemid' => 0, 'user' => $user->id ), 'pluginclass' );
			$return .= '<form method="post" class="cbregRenewButtonForm" action="'.$base_url.'">';
			$return .= '<input type="hidden" name="user" value="'.$user->id.'" />';
			$return .= '<input type="hidden" name="' . $this->_getPagingParamName("act") . '" value="' . $renewButtonAction . '" />';
			$return .= '<input type="hidden" name="' . $this->_getPagingParamName("plan") . '" value="'. $planId .'" />';
			$return .= '<input type="hidden" name="' . $this->_getPagingParamName("subscriptionid[" . $planId ) . ']" value="' . $subscription->plan_id . ',' . $subscription->id . '" />';
			$return .= '<span class="cb_button_wrapper cpay_button_' . htmlspecialchars( $renewButtonAction ) . '_wrapper">'
//					.	'<input type="submit" class="button" name="' . $this->_getPagingParamName("cbregRenew") . '" value="' . htmlspecialchars( $renewButtonText ) . '" ';
					.	'<button type="submit" class="button" name="' . $this->_getPagingParamName("cbregRenew") . '" value="' . htmlspecialchars( strip_tags( $renewButtonText ) ) . '" ';
			if ( $areYouSureText ) {
				$return .= 'onclick="return confirm( \'' . addslashes( htmlspecialchars( $areYouSureText ) ) . '\' );" ';
			}
//			$return .= '/></span>';
			$return .= '>' . $renewButtonText . '</button></span>';
			$return .= cbGetSpoofInputTag( 'plugin' );
			$return .= "</form>\n";
		} elseif ( $ui == 2 ) {
			$return .= '<input type="checkbox" name="' . $this->_getPagingParamName( "subscription" . $renewButtonAction ) .'[]" id="renewsub_'.$planId.'_'.$subscription->id.'" class="inputbox" value="'.$planId.','.$subscription->id.'" ';
			if ( $areYouSureText ) {
				$return .= 'onchange="if (this.checked == true) { if ( confirm( \'' . addslashes( htmlspecialchars( $areYouSureText ) ) . '\' ) == 0 ) { this.checked = false ; } };" ';
			}
			$return .= '/> ';
			$return .= '<label for="renewsub'.$subscription->id.'">' . $renewButtonText ."</label>\n";
		}
		return $return;
	}
	/**
	 * returns HTML code to show the currently subscribed plans
	 * @access private
	 *
	 * @param  int                             $now                     Unix-time
	 * @param  UserTable                       $user                    Reflecting the user being displayed
	 * @param  cbpaidUsersubscriptionRecord[]  $subscriptions           array of cbpaidUsersubscriptionRecord : subscriptions already checked by getUpgradeAndRenewalPossibilities()
	 * @param  string                          $plansTitle
	 * @param  boolean                         $showRenewButtons        Draw the Renew and Resubscribe buttons
	 * @param  boolean                         $showUnsubscribeButtons  Draw the Unsubscribe button
	 * @return string                                                   HTML for display
	 */
	protected function _getSubscribedPlans( /** @noinspection PhpUnusedParameterInspection */ $now, &$user, &$subscriptions, $plansTitle, $showRenewButtons, $showUnsubscribeButtons ) {	//TBD: most of this belongs to subscriptionsMgr object
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

		$return							=	null;
		if ( count($subscriptions) > 0 ) {
			$now						=	$_CB_framework->now();

			$return .= "\n<div class='regPlansList' id='cbregSubscr'>";

			$subIds						=	array_reverse( array_keys( $subscriptions ) );		// get plans in reverse order to draw all children first
			$children					=	array();
			foreach ( $subIds AS $id ) {
				if ( isset( $children[(int) $subscriptions[$id]->getPlanAttribute( 'id' )] ) ) {
					$childrenRendering	=	implode( "\n", $children[(int) $subscriptions[$id]->getPlanAttribute( 'id' )] );
					unset( $children[(int) $subscriptions[$id]->getPlanAttribute( 'id' )] );
				} else {
					$childrenRendering	=	null;
				}
				
				$controlButtons			=	$this->_drawSubscriptionButtons( $subscriptions[$id], $showRenewButtons, $showUnsubscribeButtons, $now, $user );

				$_PLUGINS->trigger( 'onCPayBeforeDrawSomething', array( &$subscriptions[$id], &$childrenRendering, &$controlButtons, $showRenewButtons, $showUnsubscribeButtons, $now, $user ) );

				$childrenRendering		.=	$controlButtons;
				$template				=	$subscriptions[$id]->getPlan()->getTemplateOutoutCss();
				/** @var $view cbpaidSomethingView */
				$view					=	cbpaidTemplateHandler::getViewer( $template, 'something' );
				$view->setModel( $subscriptions[$id] );
				$render					=	$view->drawSomethingNameDescription( $now, $childrenRendering );

				$_PLUGINS->trigger( 'onCPayAfterDrawSomething', array( &$subscriptions[$id], &$render, $now, $user ) );

				if ( ! isset( $children[(int) $subscriptions[$id]->getPlanAttribute( 'parent' )] ) ) {
					$children[(int) $subscriptions[$id]->getPlanAttribute( 'parent' )]	=	array();
				}
				array_unshift( $children[(int) $subscriptions[$id]->getPlanAttribute( 'parent' )], $render );
			}
			foreach ( array_keys( $children ) as $k ) {
				$children[$k]			=	implode( "\n", $children[$k] );
			}
			$return						.=	implode( "\n", $children )
										.	"\n";
			$return .= "</div>\n";
		}
		return $return;
	}
	/**
	 * returns HTML code for the control buttons for the subscription $sub
	 *
	 * @param  cbpaidUsersubscriptionRecord  $sub
	 * @param  boolean                       $showRenewButtons        Draw the Renew and Resubscribe buttons
	 * @param  boolean                       $showUnsubscribeButtons  Draw the Unsubscribe button
	 * @param  int                           $now                     Unix-time
	 * @param  UserTable                     $user                    Reflecting the user being displayed
	 * @return string
	 */
	protected function _drawSubscriptionButtons( &$sub, /** @noinspection PhpUnusedParameterInspection */ $showRenewButtons, /** @noinspection PhpUnusedParameterInspection */ $showUnsubscribeButtons, /** @noinspection PhpUnusedParameterInspection */ $now, &$user ) {
		global $_CB_framework;

		$return							=	'';
		$ui								=	$_CB_framework->getUi();
		//TBD $plan = $this->_loadPlan( $sub->plan_id ); ???
		$return							.=	"<div class='cbregButton'>";
		foreach ( $sub->_allowedActions as $action => $text ) {
			$return						.=	$this->_drawActionButton( $user, $ui, $sub, $sub->plan_id, $text['button_text'], $action, $text['warning'] );
		}
		$return							.=	"</div>\n";
		return $return;
	}

	/**
	 * returns HTML code for the form content to choose a plan
	 *
	 * @access   private
	 *
	 * @param  UserTable        $user             reflecting the user being displayed (here null)
	 * @param  cbpaidProduct[]  $plans            Please to show
	 * @param  string           $plansTitle       Field-name for the plan choice (for labels)
	 * @param  int[]            $chosenPlans      array of int : plan numbers selected (if === null, will chose default one, if one is default)
	 * @param  string           $reason           payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  boolean          $noPlan           show a 'No plan' choice additionally
	 * @return string                             HTML for display
	 */
	protected function _getFormattedPlans( &$user, &$plans, $plansTitle, $chosenPlans, $reason, $noPlan=false ) {
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

		$return							=	'';
		
		if ( count( $plans ) > 0 ) {
			$user_id					=	( $user && $user->id ? $user->id : null );

			$hiddenFields				=	array();

			$plansToShowOnly			=	$this->setShowOnlyPlans( false );
			if ( is_array( $plansToShowOnly ) ) {
				$hiddenFields[]			=	'<input type="hidden" name="'.$this->_getPagingParamName('onlyplans').'" value="' . htmlspecialchars( implode( ',', $plansToShowOnly ) ) . '" />';
			}

			if ( $noPlan ) {
				global $_CB_database;
				$nonePlan				=	new cbpaidProductUsersubscription( $_CB_database );
				$nonePlan->set( 'id',   0  );
				$nonePlan->set( 'item_type',   'usersubscription'  );
				$nonePlan->set( 'parent',   0  );
				$nonePlan->set( 'name', "None" );
				$nonePlan->set( 'exclusive', 1 );
				$nonePlan->set( 'rate', 0 );
				$nonePlan->set( '_subscriptionToUpdate', 0 );
				$plans[0]				=	$nonePlan;
				$chosenPlans			=	array( 0 );
			}

			$planIds					=	array_reverse( array_keys( $plans ) );		// get plans in reverse order to draw all children first
			$children					=	array();
			// check if there is only one upgrade possibility (to not draw the unneeded checkbox/radio in that case):
			$upgradePlans				=	0;
			foreach ( $planIds as $id ) {
				if ( ! ( isset( $plans[$id]->_drawOnlyAsContainer ) && $plans[$id]->_drawOnlyAsContainer ) ) {
					++$upgradePlans;
				}
			}
			// now renders:
			foreach ( $planIds as $id ) {
				if ( isset( $children[(int) $plans[$id]->id] ) ) {
					$childrenRendering	=	implode( "\n", $children[(int) $plans[$id]->id] );
					unset( $children[(int) $plans[$id]->id] );
				} else {
					$childrenRendering	=	null;
				}
				$render					=	null;
				if ( isset( $plans[$id]->_drawOnlyAsContainer ) && $plans[$id]->_drawOnlyAsContainer ) {
					if ( $childrenRendering ) {
						/** @var $view cbpaidProductView */
						$view			=&	$plans[$id]->getViewer();
						if ( $id ) {
							$insertBeforePrice	=	implode( '', $_PLUGINS->trigger( 'onCPayBeforeDrawPlan', array( &$plans[$id], &$childrenRendering, $reason, $plans[$id]->_drawOnlyAsContainer ) ) );
						} else {
							$insertBeforePrice	=	null;
						}
						$render			=	$view->drawProduct( $plansTitle, null, null, null, $insertBeforePrice, $childrenRendering, true, $reason, false, false, $user_id );
						if ( $id ) {
							$_PLUGINS->trigger( 'onCPayAfterDrawPlan', array( &$plans[$id], &$render ) );
						}
					}
				} else {
					if ( ( $upgradePlans == 1 ) && ( ( $user && ( $user->id ) ) || ( $plans[$id]->get( 'exclusive' ) == 1 ) ) ) {
						$selected		=	2;		// selected but hidden as only one plan to choose from
					} else {
						$selected		=	( in_array( $plans[$id]->get( 'id' ), $chosenPlans )
											|| ( ( count( $chosenPlans ) == 0 )
												&& ( $plans[$id]->get( 'default' ) )
												&& ( ( $reason == 'N' )
													|| ( isset( $plans[$plans[$id]->get( 'parent' )] )
														 && ( ! $plans[$plans[$id]->get( 'parent' )]->get( 'default' ) )
														 && ! ( isset( $plans[$plans[$id]->get( 'parent' )]->_drawOnlyAsContainer ) && $plans[$plans[$id]->get( 'parent' )]->_drawOnlyAsContainer )
													   )
												   )
											   )
											);
					}
					$selectionId		=	'plan' . (int) $id;
					$selectionName		=	'plan' . ( $plans[$id]->get( 'exclusive' ) ? 'E' : 'N' ) . '[' . (int) $plans[$id]->parent . ']';	// . '[' . (int) $id . ']';
					$selectionValue		=	$id;
					$view				=&	$plans[$id]->getViewer();
					if ( $id ) {
						$insertBeforePrice	=	implode( '', $_PLUGINS->trigger( 'onCPayBeforeDrawPlan', array( &$plans[$id], &$childrenRendering, $reason, false ) ) );
					} else {
						$insertBeforePrice	=	null;
					}
					$render				=	$view->drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $childrenRendering, $selected, $reason, true, true, $user_id );
					if ( $id ) {
						$_PLUGINS->trigger( 'onCPayAfterDrawPlan', array( &$plans[$id], &$render ) );
					}
					if ( is_array( $plans[$id]->_subscriptionToUpdate ) ) {
						$hiddenFields[]	=	'<input type="hidden" name="'.$this->_getPagingParamName("subscriptionid[".$id).']" value="'.implode( ',', $plans[$id]->_subscriptionToUpdate ) .'" />';
					}
				}

				if ( $render ) {
					if ( ! isset( $children[(int) $plans[$id]->parent] ) ) {
						$children[(int) $plans[$id]->parent]	=	array();
					}
					array_unshift( $children[(int) $plans[$id]->parent], $render );
				}
			}
			foreach ( array_keys( $children ) as $k ) {
				$children[$k]			=	implode( "\n", $children[$k] );
			}
			if ( ( count( $children ) > 0 ) || ( count( $hiddenFields ) > 0 ) ) {
				$return					.=	implode( "\n", $children )
										.	"\n\t"
										.	implode( "\n\t", $hiddenFields )
										.	"\n";
			}
			if ( $noPlan ) {
				array_pop( $plans );
			}

			if ( $return ) {
				$return					=	"\n<div class='cbregPlansList' id='cbregUpgrades'>\n" . $return . "</div>\n";
			}
		}
		return $return;
	}
	/**
	* gets the chosen plans from the form, and checks if they are allowed for that user
	* also gets the options of the plans
	* In detail:
	* gets an array of array of int as an array of int (removing first level), verifying that if index is not 0 the parent exists
	* In each plan object there is a ->_options variable with a Registry object with the option values of the plan
	*
	* @param  UserTable        $user            Reflecting the user being registered or saved
	* @param  string           $name            name of parameter in REQUEST URL
	* @param  cbpaidProduct[]  $allowedPlans    array of cbpaidProduct  which are allowed
	* @param  boolean          $isRegistration  TRUE: Registration process (guest), or FALSE: upgrade-process (logged-in user)
	* @param  string           $reason          Subscription reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	* @param  boolean          $returnPlans     TRUE: returns plan objects or FALSE: returns plan ids only.
	* @param  string           $postfix         postfix for identifying multiple plans spaces (optional)
	* @return int[]|cbpaidProduct[]|string     ARRAY of int|of cbpaidProducts : Plans which are selected within hierarchy (according to the post, to be rechecked !) or STRING: error message.
	*/
	protected function & _planGetAndCheckReqParamArray( &$user, $name, &$allowedPlans, $isRegistration, $reason, $returnPlans = false, $postfix = '' ) {
		global $_CB_framework, $_POST;

		$params							=&	cbpaidApp::settingsParams();
		$enableFreeRegisteredUser		=	$params->get( 'enableFreeRegisteredUser', 1 );
		$createAlsoFreeSubscriptions	=	$params->get( 'createAlsoFreeSubscriptions', 0 );

		$ui								=	$_CB_framework->getUi();
		if ( ! $isRegistration ) {
			if ( $ui == 1 ) {
				$userId					=	(int) cbGetParam( $_POST, 'user', 0 );
			} else {
				$userId					=	(int) cbGetParam( $_POST, 'id', 0 );
			}
		} else {
			$userId						=	null;
		}

		$selectedPlanIds							=	$this->_plangetReqParamArray( $name, $postfix );
		/// $validSub									=	array();
		// 1. checks that selected plans hierarchy is respected:
		$ok											=	true;
		$plansMgr									=	null;
		foreach ( $selectedPlanIds as $id ) {
			if ( $id != 0 ) {						// ignore "None" plan in backend edit profile
				$ok									=	false;
	
				// foreach ( $allowedPlans as $planid => $p ) {
				if ( isset( $allowedPlans[(int) $id] ) ) {
					$p								=	$allowedPlans[(int) $id];
	
					if ( $id == $p->id ) {
						$parentOk					=	true;
						$parentId					=	$p->get( 'parent' );
						if ( $parentId != 0 ) {
							// the selected plan has a parent plan: check if parent plan is also chosen or already subscribed and active:
							$parentOk				=	false;
							foreach ($selectedPlanIds as $selPlanId ) {
								if ( $parentId == $selPlanId ) {
									$parentOk		=	true;
									break;
								}
							}
							if ( ( ! $isRegistration ) && ( ! $parentOk ) ) {
								// try to see if user is subscribed already to the parent plan:
								if ( $userId ) {
									if ( $plansMgr === null ) {
										$plansMgr	=&	cbpaidPlansMgr::getInstance();
									}
									$plan			=	$plansMgr->loadPlan( $parentId );
									/** @var $plan cbpaidProduct */
									if ( $plan ) {
										// Check if allow free lifetime users without need to create such subscriptions:
										if ( $enableFreeRegisteredUser && ( ! $createAlsoFreeSubscriptions ) && $plan->isLifetimeValidity() && $plan->isFree() ) {
											$parentOk		=	true;
										} else {
											$sub			=	$plan->newSubscription();
											/** @var $sub cbpaidSomething */
											if ( $sub->loadValidUserSubscription( $userId ) ) {
												$parentOk	=	true;
												/// $validSub[$parentId]		=	$sub->id;
											}
										}
									}
								}
							}
						}
						if ( $parentOk ) {
							$ok						=	true;
						}
						break;
					}
				}
				if ( ! $ok ) {
					break;
				}
			}
		}

		if ( ! $ok ) {
			$selectedPlanIds								=	CBPTXT::T("Chosen plans combination is not allowed (you must choose coherent plans selection, e.g. parent subscriptions to a plan must be active).");
		} else {
			// 2. Check that all exclusivities are respected:
			$plansMgr										=&	cbpaidPlansMgr::getInstance();
			// 2.a. build array of exclusive [parent][plan]:
			$exclusiveChildren								=	array();
			// 2.a.1. add the plans just selected now:
			foreach ($allowedPlans as $id => $p ) {
				if ( $p->get( 'exclusive' ) ) {
					$exclusiveChildren[$p->get( 'parent' )][$p->get( 'id' )]	=	( in_array( $id, $selectedPlanIds ) ? 1 : 0 );
				}
			}
			// 2.a.2. add the plans already subscribed with active subscription (if we are not just upgrading that level):
			$ValidUserPlans									=	array();
			$validSubExists									=	array();
			if ( ( ! $isRegistration ) && $userId ) {
				foreach ( $exclusiveChildren as $parentId => $exclPlansArray ) {
					if ( $parentId != 0 ) {
						$plan								=	$plansMgr->loadPlan( $parentId );
						if ( $plan ) {
							$sub							=	$plan->newSubscription();
							$ValidUserPlans[$parentId]		=	( $sub->loadValidUserSubscription( $userId ) );
						} else {
							$selectedPlanIds				=	CBPTXT::T("Chosen plan has a parent plan configured that doesn't exist anymore.");
						}
					}
					$numberOfSelected						=	array_sum( $exclPlansArray );
					if ( $numberOfSelected == 0 ) {
						$firstFreeLifeTime					=	array();
						// foreach ( $exclPlansArray as $childId => $selected )
						foreach ( array_keys( $exclPlansArray ) as $childId ) {
							$plan							=	$plansMgr->loadPlan( $childId );
							if ( ( ! isset( $firstFreeLifeTime[$parentId] ) ) && ( $enableFreeRegisteredUser && ( ! $createAlsoFreeSubscriptions ) && $plan->isLifetimeValidity() && $plan->isFree() ) ) {
								$firstFreeLifeTime[$parentId]	=	$plan->get( 'id' );
							}
							if ( ! isset( $ValidUserPlans[$childId] ) ) {
								$sub						=	$plan->newSubscription();
								$ValidUserPlans[$childId]	=	( $sub->loadValidUserSubscription( $userId ) );
							}
							if ( $ValidUserPlans[$childId] ) {
								$exclusiveChildren[$parentId][$childId]		=	1;
								$validSubExists[$parentId]						=	1;
							}
						}
					}
				}
			}
			// 2.b. check that exactly 1 exclusive plan is selected at each level (including still valid subscribed plans) which matters:
			if ( ! ( ( $ui == 2 ) && ( $selectedPlanIds == array( '0' ) ) ) ) {		// ignore "None" plan in backend edit profile
				foreach ( $exclusiveChildren as $parentId => $exclPlansArray ) {
					$numberOfSelected						=	array_sum( $exclPlansArray );
					if ( $numberOfSelected > 1 ) {
						$selectedPlanIds					=	CBPTXT::T("Chosen plans combination is not allowed (you can't choose more than one mandatory plan).") . $numberOfSelected;
						break;
					}
					elseif ( ( $numberOfSelected == 0 ) && ( ! isset( $validSubExists[$parentId] ) ) && ( ! isset( $firstFreeLifeTime[$parentId] ) ) ) {
						if ( ( $parentId == 0 ) || in_array( $parentId, $selectedPlanIds ) || ( isset( $ValidUserPlans[$parentId] ) && ( $ValidUserPlans[$parentId] == true ) ) )
						{
							$selectedPlanIds				=	CBPTXT::T("Chosen plans combination is not allowed (you must choose coherent plans selection, e.g. mandatory subscription(s) must be active or mandatory plan(s) must be chosen).");
							$names							=	array();
							foreach ( array_keys( $exclPlansArray ) as $childId ) {
								$exclPlan					=&	$plansMgr->loadPlan( $childId );
								/** @var $exclPlan cbpaidProduct */
								$names[]					=	$exclPlan->get( 'name' );
							}
							if ( $parentId ) {
								$parentPlan					=&	$plansMgr->loadPlan( $parentId );
								/** @var $parentPlan cbpaidProduct */
								$parentName					=	$parentPlan->get( 'name' );
								$selectedPlanIds			.=	' ' . CBPTXT::T( sprintf( "'%s' has mandatory options '%s' and none is selected." , $parentName, implode( "', '", $names ) ) );
							}
							break;
						}
					}
				}
			}
			// 3. Checks that all selected plans' conditions are met:
			if ( is_array( $selectedPlanIds ) ) {
				foreach ( $selectedPlanIds as $id ) {
					if ( $id ) {
						$plan								=&	$plansMgr->loadPlan( $id );
						if ( $plan ) {
							if ( ! $plan->checkActivateConditions( $user, $reason, $selectedPlanIds ) ) {
								// Error text will be in selectedPlanIds in case of error returning false:
								break;
							}
						}
					}
				}
			}
			// 4. Checks done:
			if ( $returnPlans && is_array( $selectedPlanIds ) && ( count( $selectedPlanIds ) > 0 ) ) {
				// if returning selected plans, sort them in same order and with same keys as corresponding allowed plans:
				global $_PLUGINS;
				$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
				$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

				$selectedPlans								=	array();
				foreach ($allowedPlans as $id => $p ) {
					if ( in_array( $id, $selectedPlanIds ) ) {
						/** @var cbpaidProduct[] $selectedPlans */
						$selectedPlans[(int) $id]			=	$allowedPlans[$id];
						$selectionId						=	'plan' . $id;
						$selectionName						=	'plan' . ( $selectedPlans[$id]->get( 'exclusive' ) ? 'E' : 'N' ) . '[' . (int) $selectedPlans[$id]->parent . ']';
						$selectionValue						=	$id;
						$view								=	$selectedPlans[$id]->getViewer();

						$paramsOrString						=	$view->getOptions( $selectionId, $selectionName, $selectionValue, $reason );
						if ( is_string( $paramsOrString ) ) {
							$selectedPlans					=	$paramsOrString;		// error message
							break;
						}
						$selectedPlans[(int) $id]->_options		=	$paramsOrString;
						$selectedPlans[(int) $id]->_integrations	=	new Registry( '' );
						$_PLUGINS->trigger( 'onCPayAfterPlanSelected', array( &$selectedPlans[(int) $id], &$selectedPlans[(int) $id]->_integrations , $reason ) );
						if ( $_PLUGINS->is_errors() ) {
							$selectedPlans							=	$_PLUGINS->getErrorMSG();
							break;
						}
					}
				}
				return $selectedPlans;
			}
		}
		return $selectedPlanIds;
	}
	/**
	* Gets plans chosen, verifying parentship.
	* In detail:
	* gets an array of array of int as an array of int (removing first level), verifying that if index is not 0 the parent exists
	*
	* @param  string  $name     name of parameter in REQUEST URL
	* @param  string  $postfix  postfix for identifying multiple plans spaces (optional)
	* @return array   of int    Plans which are selected within hierarchy (according to the post, to be rechecked !)
	*/
	protected function _plangetReqParamArray( $name, $postfix = '' ) {
		$ret							=	array();
		$arrE							=	$this->_getReqParam( $name . 'E', null, $postfix );
		$arrN							=	$this->_getReqParam( $name . 'N', null, $postfix );
		if ( is_array( $arrE ) || is_array( $arrN ) ) {
			$arr						=	array_merge( ( is_array( $arrE ) ? $arrE : array() ), ( is_array( $arrN ) ? $arrN : array() ) );
			foreach ( array_keys( $arr ) as $k ) {
				if ( is_array( $arr[$k] ) && isset( $arr[$k]['selected'] ) && is_array( $arr[$k]['selected'] ) ) {
					foreach ( $arr[$k]['selected'] as $vv ) {
						$ret[]		=	$vv;
					}
				}
			}
		}
		return $ret;
	}
	/*
	* gets an array of int as parameter of this plugin
	*
	* @param  string  $name     name of parameter in REQUEST URL
	* @param  string  $def      default value of parameter in REQUEST URL if none found
	* @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	* @return array             (int) key => array( (int) newPlan => array( (int) plan, (int) sub ) ???
	*
	protected function getPostedHiddenExistingPlanSubscriptionIds( $name, $def = null, $postfix = '' ) {
		$ret										=	array();
		$arr										=	$this->_getReqParam( $name, $def, $postfix );
		if ( is_array( $arr ) ) {
			foreach ( $arr as $k => $v ) {
				if ( is_array( $arr[$k] ) ) {
					foreach ( $arr[$k] as $kk => $vv ) {
						$ret[(int) $k][(int) $kk]	=	(int) $arr[$k][$kk];
					}
				} else {
					$ret[(int) $k]					=	(int) $arr[$k];
				}
			}
		}
		return $ret;
	}
*/
	/**
	* gets an array of plan choices
	*
	* @param  string  $name     name of parameter ( 'pay', 'renew', 'unsubscribe' or 'delete' )
	* @param  string  $def      default value of parameter in REQUEST URL if none found
	* @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	* @return array             Composed of: (int) key => array ( (int) plan, (int) sub )
	*/
	public function getEditPostedBoxes( $name, $def = null, $postfix = '' ) {
		$arr							=	$this->_getReqParam( 'subscription' . $name, $def, $postfix );
		$ret							=	array();
		if ( is_array( $arr ) ) {
			foreach ( $arr as $k => $v ) {
				$parts	=	explode( ',', $v, 3 );
				if ( count( $parts ) == 2 ) {
					$parts[0]			=	(int) $parts[0];
					$parts[1]			=	(int) $parts[1];
					$ret[(int) $k]		=	$parts;
				}
			}
		}
		return $ret;
	}
	/**
	 * Checks if javascript code is needed for hiding registration fields based on plan chosen.
	 *
	 * @param  cbpaidProduct[]  $plans     array of cbpaidProduct
	 * @param  string           $reason    display reason: 'N'=registration, 'U'=update
	 */
	protected function _addJsCodeIfNeeded( &$plans, $reason ) {
		global $_CB_framework;

		$fieldsToHide					=	array();

		if ( $reason == 'N' ) {
			foreach  ( array_keys( $plans ) AS $id ) {
				if ( $plans[$id]->get( 'hideregistrationfields' ) ) {
					$fieldsToHide[$id]	=	explode( '|*|', $plans[$id]->get( 'hideregistrationfields' ) );
				} else {
					$fieldsToHide[$id]	=	array();
				}
			}
		}

		foreach ( $plans as $plan ) {
			$plan->addJsCodeIfNeeded( $reason );
		}

		$js			=	"var cbpayHideFields = new Array();\n";
		foreach ( $fieldsToHide as $id => $nameArray ) {
			$js		.=	"cbpayHideFields[" . $id . "] = [" . implode( ',', $nameArray ) . "];\n";
		}

		$this->addcbpaidjsplugin();
		outputCbJs();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
	}
	/**
	 * Adds CBSubs Front-end Javascript to CB jQuery headers
	 *
	 * @return void
	 */
	public function addcbpaidjsplugin( ) {
		global $_CB_framework;
		static $outputed		=	0;
		if ( ! $outputed++ ) {
			$_CB_framework->addJQueryPlugin( 'cbpaidsubscriptions', '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/js/cbpaidsubscriptions.js', array( -1 => array( 'form', 'metadata' ) ) );
			$_CB_framework->outputCbJQuery( '$.cbpaidsubs.paidsubsInit();', 'cbpaidsubscriptions' );
		}
	}
}	// class cbpaidControllerUI
