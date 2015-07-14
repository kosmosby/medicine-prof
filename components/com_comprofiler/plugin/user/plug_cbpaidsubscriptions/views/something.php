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
 * VIEW Something: This View itself follows a MVC Model:
 * =====================================================
 */


/**
 * VIEW CONTROLLER: Controls the rendering of a user subscription
 *
 */
class cbpaidSomethingView extends cbpaidTemplateHandler {
	/** product
	 * TODO: Once we also display purchased merchandises or donations, this class should be come generic for cbpaidSomething as model
	 * @var cbpaidUsersubscriptionRecord */
	public $_model;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws a subscription's name and description...
	 *
	 * @param  int|null $now                     unix time for the expiration times (null: now)
	 * @param  string   $insertAfterDescription  HTML text to insert after this item as sub-items
	 * @param  boolean  $showStateCheckMark
	 * @return string
	 */
	public function drawSomethingNameDescription( $now, $insertAfterDescription, $showStateCheckMark = true ) {
		global $_CB_framework, $_PLUGINS;

		/** @var $subscription cbpaidUsersubscriptionRecord */
		$subscription		=&	$this->_model;
		$params				=&	cbpaidApp::settingsParams();
		$showtime			=	( $params->get( 'showtime', '1' ) == '1' );
		$subTxt				=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );

		$titlesTexts		=	array(  'A' =>	sprintf( CBPTXT::T("Active %s"), $subTxt ),				// non-expiring one
										'AA' => sprintf( CBPTXT::T("Active %s"), $subTxt ),				// local state: expiring one
										'R' =>	sprintf( CBPTXT::T("%s not yet paid"), ucfirst( $subTxt ) ),
										'U' =>	sprintf( CBPTXT::T("Upgraded %s"), $subTxt ),
										'C' =>	sprintf( CBPTXT::T("Unsubscribed %s"), $subTxt ),
										'X' =>	sprintf( CBPTXT::T("Expired %s"), $subTxt ),
										'XX' =>	sprintf( CBPTXT::T("Inactive (parent %s not active)"), $subTxt ),	// local state
										'ZZ' =>	sprintf( CBPTXT::T("Unknown state of %s"), $subTxt ) );	// local state

		//TODO use getFormattedExpirationDateText()
		$stateTexts			=	array(  'A' => CBPTXT::T("Active"),
										'AA' => CBPTXT::T("Active, expiring on %s"),			// local state
										'R' => CBPTXT::T("Not yet paid"),
										'U' => CBPTXT::T("Upgraded to higher plan on %s"),
										'C' => CBPTXT::T("Unsubscribed on %s"),
										'X' => CBPTXT::T("Expired %s"),
										'XX' =>	sprintf( CBPTXT::T("Inactive (parent %s not active)"), $subTxt ),	// local state
										'ZZ' =>	sprintf( CBPTXT::T("Unknown state of %s"), $subTxt ) );	// local state

		$autoRenewingText	=	CBPTXT::T("%s, auto-renewing");
		$autoRenewingXtimes	=	CBPTXT::T("%s, auto-renewing %s more times until %s");

		// check if active and if parents are active:
		$realStatus				=	$subscription->realStatus( $now );
		$subActive				=	$subscription->checkIfValid( $now );
		$subAndParentsActive	=	$subscription->checkIfThisAndParentSubscriptionIsValid( $now );
		if ( $subActive && ! $subAndParentsActive ) {
			$status				=	'XX';
		} else {
			// compute local pseudo status, which is subscription status and 2 local states: AA and ZZ:
			$status				=	$realStatus;
			if ( ! array_key_exists( $realStatus, $titlesTexts ) ) {
				$status			=	'ZZ';
			}
			if ( ( $status == 'A' ) && ! $subscription->isLifetimeValidity() ) {
				$status			=	'AA';
			}
		}

		if ( ( $status == 'ZZ' ) && ( $_CB_framework->getUi() == 1 ) ) {
			// if status is unknown, don't display it in frontend, only in backend !
			return $insertAfterDescription;
		}

		$viewModel				=	new cbpaidSomethingViewModel();
		$viewModel->subscription =	$subscription;
		$viewModel->name		=	$subscription->getPlan()->getPersonalized( 'name', $subscription->user_id, true );
		$viewModel->description	=	$subscription->getPlan()->getPersonalized( 'description', $subscription->user_id, true );
		$viewModel->cssclass	=	$subscription->getPlanAttribute( 'cssclass' );
		
		$viewModel->active		=	$subAndParentsActive;
		
		$viewModel->validity	=	htmlspecialchars( $subscription->getFormattedValidityRemaining() );

		$viewModel->title		=	htmlspecialchars( $titlesTexts[$status] );

		// Prepare the exact description text for the status:
		if ( $subscription->expiry_date
		     && ! ( ( $subscription->status == 'X' ) && $subActive )
		   )
		{
			$expDate 			=	cbFormatDate( $subscription->expiry_date, 1, $showtime );
		} else {
			$expDate			=	'';
		}

		$stateText				=	sprintf( $stateTexts[$status], $expDate );

		// add information of auto-renewals if autorecurring and all autorecurrings are not yet done:
		if ( ( $subscription->autorecurring_type > 0 )		// 1/2: auto-renewing without/with processor notifications updating $expiry_date
		  && ( $status == 'AA' )
		  && ( ( $subscription->regular_recurrings_total == 0 ) || ( $subscription->regular_recurrings_used < $subscription->regular_recurrings_total ) ) )
		{
			if ( ( $subscription->autorecurring_type == 2 ) && ( $subscription->regular_recurrings_total ) && ( $subscription->regular_recurrings_used < $subscription->regular_recurrings_total ) ) {
				$occurrences	=	$subscription->regular_recurrings_total - $subscription->regular_recurrings_used;
				$finalExpTime	=	$subscription->computeExpiryTimeIfActivatedNow( $now, 'R', $occurrences );
				$finalExpTxt	=	cbFormatDate( $finalExpTime, 1, $showtime );
				// '%s, auto-renewing %s more times until %s' :
				$stateText		=	sprintf( $autoRenewingXtimes, $stateText, $occurrences, $finalExpTxt );
			} else {
				// '%s, auto-renewing' :
				$stateText		=	sprintf( $autoRenewingText, $stateText );
			}
		}

		$viewModel->stateText	=	htmlspecialchars( $stateText );

		//TBD ???? $plan->displayPeriodPrice( 'R', $sub->getOccurrence() + 1, null, $plan->strToTime( $sub->expiry_date ), false );

		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

		$insertBeforeValidity	=	implode( '', $_PLUGINS->trigger( 'onCPayBeforeDrawSubscription', array( &$viewModel, &$subscription, &$insertAfterDescription ) ) );

		/** @var $view cbpaidsomethingusersubscriptionView */
		$view					=	cbpaidTemplateHandler::getViewer( $this->templateToUse(), 'somethingusersubscription', 'html' );		//TBD extend to any Something (merchandises, donations)
		$view->setModel( $viewModel );
		return $view->drawSomething( $insertBeforeValidity, $insertAfterDescription, $showStateCheckMark );
	}
}	// class cbpaidSubscriptionViewController

/**
 * VIEW MODEL: Data of subscription to display
 *
 */
class cbpaidSomethingViewModel {
	/**
	 * @var cbpaidSomething
	 */
	public $subscription;
	public $name;
	public $description;
	public $active;
	public $validity;
	public $title;
	public $stateText;
	public $cssclass;
	/**
	 * Getter
	 *
	 * @param  string  $attr
	 * @return mixed
	 */
	public function get( $attr ) {
		return $this->$attr;
	}
}

/**
 * VIEW's GENERIC VIEW: Something view class (subscription, merchandise, donation, ...)
 *
 */
class cbpaidSomethingViewView extends cbpaidTemplateHandler {
	public $_insertBeforeValidity;
	public $_insertAfterDescription;
	public $_icon;
	/**
	 * @var $_model cbpaidSomethingViewModel
	 */
	public $_model;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the subscription for registrations and profile views
	 *
	 * @param  string                       $insertBeforeValidity    HTML text to insert after this item description but before validity
	 * @param  string                       $insertAfterDescription  HTML text to insert after this item as sub-items
	 * @param  boolean                      $showStateCheckMark      If check/cross-marks for ->active state should be drawn
	 * @return string
	 */
	public function drawSomething( $insertBeforeValidity, $insertAfterDescription, $showStateCheckMark = true ) {
		global $_CB_framework;

		$this->_insertBeforeValidity	=	$insertBeforeValidity;
		$this->_insertAfterDescription	=	$insertAfterDescription;

		// Checkmark / Cross in front of title of subscription:
		if ( $showStateCheckMark ) {
			if ( $this->_model->active ) {
				$iconFile	=	'ok_b_16.gif';
			} else {
				$iconFile	=	'close_b_16.gif';
			}
			$iconsPath		=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/normal';
			$this->_icon	=	'<img src="' . $iconsPath . '/' . $iconFile . '" width="16px" height="16px" alt="' . $this->_model->get( 'title' ) . '" title="' . $this->_model->get( 'title' ) . '" />';
		} else {
			$this->_icon	=	null;
		}
	}
}
