<?php
/**
 * @version $Id: cbpaidNonRecurringSomething.php 1551 2012-12-03 10:52:03Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Base class for Merchandises and for Donations:
 */
abstract class cbpaidNonRecurringSomething extends cbpaidSomething {
	public $payment_date;
	/** Donation amount
	 * @var float */
	public $amount;
	/** Currency of donation
	 * @var string */
	public $currency;
	public $ip_addresses;
	/**
	 * Returns the name of the column/variable for the latest purchase/renewal depending on class
	 *
	 * @return string
	 */
	public function latestDatetimeColumnName( ) {
		return 'payment_date';
	}
	/**
	 * create a new subscription object and corresponding object in database
	 *
	 * @param int|null      $user_id                 CB user id
	 * @param cbpaidProduct $plan                    plan object of this subscription
	 * @param string        $status                  like status class variable
	 * @param boolean       $store                   true (default) if should be stored into db
	 * @param int           $subscriptionTime        time of subscription
	 */
	protected function createMerchandiseRecord( $user_id, &$plan, $status = 'R', $store = true, $subscriptionTime = null ) {
		global $_CB_framework;

		if ( $subscriptionTime === null ) {
			$subscriptionTime			=	$_CB_framework->now();
		}

		$this->reset();

		$this->user_id					=	$user_id;
		$this->plan_id					=	$plan->get( 'id' );
		$this->payment_date				=	date( 'Y-m-d H:i:s', $subscriptionTime );
		$this->getCurrencyAmount( $plan );
		$this->status					=	$status;
		if ( is_object( $plan->_integrations ) ) {
			$this->integrations				=	$plan->_integrations->asJson();
		} else {
			$this->integrations				=	'';
		}
		if ( $store ) {
			$this->ip_addresses			=	cbpaidRequest::getIPlist();
			$this->historySetMessage( $this->recordName() . ' record created' );
			$this->store();
		}
		$this->_plan 					=	$plan;
	}
	/**
	 * loads the latest user subscription and checks if it's valid.
	 *
	 * @param  int          $user_id    User id
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if found and valid
	 */
	public function loadValidUserSubscription( $user_id, $time = null ) {
		// A merchandise / donation is not a subscription:
		return false;
	}

	/**
	 * create a new (or find an existing) subscription object and corresponding object in database and links to subscription to be replaced.
	 *
	 * @param  UserTable      $user                    CB user object
	 * @param  cbpaidProduct  $plan                    payment plan object of this subscription
	 * @param  array          $replacesSubscriptionId  array( planId, subscriptionId ) or NULL
	 * @param  array          $existingSubscriptionId  array( planId, subscriptionId ) or NULL
	 * @param  string         $status                  [Optional default='R'] 'N' = new, 'R' = renewal
	 * @param  boolean        $store                   [Optional default=true] if object to be stored into database
	 * @param  int            $subscriptionTime        [Optional default=time function]
	 * @param  string         $reason                  payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  array|null     $parentSubId             parent subscription's id,   if this subscription depends of a parent subscription
	 * @return float|int                               remaing value (in plan's currency) of existing plan
	 */
	public function createOrLoadReplacementSubscription( &$user, &$plan, $replacesSubscriptionId = null, $existingSubscriptionId = null, $status = 'R', $store = true, $subscriptionTime = null, $reason = 'N', $parentSubId = null ) {
		global $_CB_database;

		if ( $replacesSubscriptionId ) {
			trigger_error( $this->recordName() . '::createOrLoadReplacementSubscription: replace subscription not possible: id: ' . $replacesSubscriptionId[1], E_USER_ERROR );
		}
		if ( $existingSubscriptionId ) {
			// first tries to find if there is already an existing one, otherwise creates a new one:
			if ( ! $this->load( (int) $existingSubscriptionId[1] ) ) {
				trigger_error( 'Merchandise::createOrLoadReplacementSubscription: subscription load error:' . htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_ERROR );
			}
		}
		$this->createMerchandiseRecord( $user->id, $plan, $status, $store, $subscriptionTime );

		$this->getPlan();
		$remainingValue						=	0;
		return $remainingValue;
	}
	/**
	 * Create a payment item for a payment basket to be created
	 *
	 * @param  int                          $quantity          Quantity
	 * @param  string                       $currency_code     The currency of the payment basket (so the payment item must be converted into that currency
	 * @param  string                       $artNumber         Text for the article number
	 * @param  string                       $prefixText        Text to prefix before the item descriptions								//TBD this should be on a per-item basis
	 * @param  string                       $reason            payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int                          $now               unix time of now (single now time for consistency in db)
	 * @param  boolean                      $tryAutorecurring  try to build the payment basket as autorecurring
	 * @return cbpaidPaymentItem                               return FALSE if can't be purchased/subscribed
	 */
	public function & createPaymentItem( $quantity, $currency_code, $artNumber, $prefixText, $reason, $now, $tryAutorecurring ) {
		$item						=	new cbpaidPaymentItem( $this->_db );

		$item->callIntegrations( 'beforeCreatePaymentItem', $this, null );

		$planId						=	$this->plan_id;
		$start_date					=	date( 'Y-m-d H:i:s', $now );
		$rate						=	$this->getPriceOfNextPayment( $currency_code, $now, $quantity, $reason );	// N=New, R=Renew + U = Upgrade
		if ( $rate === false ) {
			$false					=	false;
			return $false;
		}

		// build item description line, default:	[ITEM_ALIAS]	and		[PREFIX_TEXT] [PLANS_TITLE]: [ITEM_NAME][VALIDITY_IF_NOT_AUTORECURRING] for [USERNAME]
		cbimport( 'cb.tabs' );					// cbFormatDate needs this and cbreplaceVars too
		$extraStrings				=	$this->substitutionStringsForItemDetailed( false, $reason, false );

		$cbUser						=	CBuser::getInstance( $this->user_id );
		$itemDescription			=	$cbUser->replaceUserVars( CBPTXT::T( $this->getPlanAttribute( 'item_text' ) ), false, false, $extraStrings, false );
		$itemAlias					=	$cbUser->replaceUserVars( CBPTXT::T( $this->getPlanAttribute( 'item_alias' ) ), false, false, $extraStrings, false );

		$item->createItem(	strtolower( $this->recordName() ),
			$quantity,
			$artNumber,
			$itemDescription,
			$itemAlias,
			$currency_code,
			$rate,
			$this->getPlanAttribute( 'owner' ),
			$reason
		);

		$validity					=	'0000-00-00 00:00:00';
		$stop_date					=	'0000-00-00 00:00:00';
		$second_stop_date			=	'0000-00-00 00:00:00';
		$autorecurring				=	0;
		$recurring_max_times		=	0;
		$first_rate					=	null;
		$first_validity				=	null;
		$bonustime					=	'0000-00-00 00:00:00';
		$prorate_discount			=	0.0;
		$item->setSubscriptionVars(	$this->id, $planId,
			$validity, $start_date, $stop_date, $second_stop_date,
			$autorecurring, $recurring_max_times,
			$first_rate, $first_validity, $bonustime,
			$prorate_discount
		);

		$item->callIntegrations( 'afterCreatePaymentItem', $this, null );

		return $item;
	}
	/**
	 * Updates the payment item corresponding to this Something
	 *
	 * @param  cbpaidPaymentItem    $item
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  int                  $quantity          Quantity
	 * @param  string               $currency_code     The currency of the payment basket (so the payment item must be converted into that currency
	 * @return void
	 */
	public function updatePaymentItem( &$item, $paymentBasket,  $quantity = null, $currency_code = null ) {
		$item->callIntegrations( 'beforeUpdatePaymentItem', $this, $paymentBasket );

		if ( $quantity === null ) {
			$quantity				=	$item->quantity;
		}
		if ( $currency_code === null ) {
			$currency_code			=	$item->currency;
		}
		$start_time					=	strtotime( $item->start_date );
		$rate						=	$this->getPriceOfNextPayment( $currency_code, $start_time, $quantity, $item->reason );
		if ( $rate === false ) {
			$rate					=	0;
		}

		$item->currency				=	$currency_code;
		$item->rate					=	$rate;
		$item->quantity				=	$quantity;

		$item->callIntegrations( 'afterUpdatePaymentItem', $this, $paymentBasket );
	}
	/**
	 * Get price value of the next payment to subscribe or renew this plan
	 *
	 * @param  string        $currency_code
	 * @param  int           $now             unix time of now (single now time for consistency in db)
	 * @param  int           $quantity
	 * @param  string        $reason          [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  float|int     $remainingValue  Remaining value (in the plan's default currency) to deduct of the price
	 * @return float|boolean                  price of next payment for this subscription in currency. Return FALSE if can't be purchased/subscribed
	 */
	public function getPriceOfNextPayment( $currency_code, $now, $quantity = 1, $reason = 'N', $remainingValue = 0 ) {
		$rate						=	$this->amount;

		if ( $rate !== null ) {
			$rate				=	$rate - $remainingValue;
			if ( $rate < 0 ) {
				$rate			=	false;
			}
		} else {
			$rate				=	false;
		}

		if ( ( $rate !== false ) && ( $currency_code != $this->currency ) ) {
			$rate				=	cbpaidMoney::getInstance()->convertPrice( $rate, $this->currency, $currency_code, true, true );
		}
		return $rate;
	}
	/**
	 * Gets the subscription date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getSubscriptionDate() {
		return $this->payment_date;
	}
	/**
	 * Gets the subscription last renewal date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getLastRenewDate() {
		return $this->payment_date;
	}
	/**
	 * activates subscription
	 *
	 * @param UserTable  $user
	 * @param int        $now                 Unix time to activate (and expire the previous subscription if it's an upgrade): WARNING: can be in the past, in case of imports!
	 * @param boolean    $completedNow        [optional] True if first time completed, False if not first time (e.g. cancelled reversal)
	 * @param string     $reason              [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @param int        $occurrences         renewal occurrences
	 * @param int        $autorecurring_type  0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param int        $autorenew_type      0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @param int        $autorenewed         0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	public function activate( &$user, $now, $completedNow = true, $reason='N', $occurrences = 1, $autorecurring_type = 0, $autorenew_type = 0, $autorenewed = 0 ) {
		$this->payment_date				=	date( 'Y-m-d H:i:s', $now );
		$this->status					=	'A';	// Active
		$this->historySetMessage( $this->recordName() . ' paid and activated' );
		$this->store( true );

		if ( $completedNow ) {
			$cause							=	'PaidSubscription';
		} else {
			$cause							=	'SubscriptionActivated';
		}
		$this->triggerIntegrations( $user, $cause, null, null, $reason, $autorenewed );
		$this->sendNewStatusEmail( $user, $cause, $reason, $autorenewed );
	}
	/**
	 * deactivates subscription: saves previous status and expiry_date, expires now
	 * and STORES into database
	 *
	 * @param  UserTable  $user
	 * @param  string     $newStatus  Status to set: e.g. 'U': Upgraded: no user blocking or event fired
	 */
	public function deactivate( &$user, $newStatus ) {
		$this->status				= $newStatus;
		$this->historySetMessage( $this->recordName() . ' deactivated with new status: ' . $newStatus );
		$this->store( true );

		$cause						=	'SubscriptionDeactivated';
		$this->triggerIntegrations( $user, $cause, null, null, null, 0 );
	}
	/**
	 * reverts subscription state and expiry to previous values
	 *
	 * @param  UserTable  $user
	 * @param  string     $unifiedStatus  Payment/Subscription status ('PaidSubscription', 'Denied', 'RegistrationCancelled', NOT allowed here: 'Completed', 'Processed', 'Pending', 'In-Progress'
	 * @return string                     NULL: not applicable, 'I': Innexistant, 'R': Registered but not activated, 'A': Active, 'X': Expired  (all subscriptions in that chain, except for 'A')
	 */
	public function revert( &$user, $unifiedStatus ) {
		$this->deactivate( $user, 'C' );
		return 'C';
	}
	/**
	 * SUBSCRIPTION PRESENTATION METHODS:
	 */
	/**
	 * Returns substitution strings
	 *
	 * @see cbpaidSomething::substitutionStringsForItemDetailed()
	 *
	 * @param  boolean  $html           HTML or TEXT return
	 * @param  string   $reason         'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  boolean  $autorecurring  TRUE: is autorecurring, no real expiration date, FALSE: is not autorecurring
	 * @return array
	 */
	public function substitutionStringsForItemDetailed( /** @noinspection PhpUnusedParameterInspection */ $html, $reason, $autorecurring ) {
		global $_CB_framework;

		$user						=	CBuser::getUserDataInstance( $this->user_id );

		$prefixText					=	'';

		$params						=&	cbpaidApp::settingsParams();

		$extraStrings				=	array(	'ITEM_NAME'				=>	$this->getPlan()->getPersonalized( 'name', $this->user_id, false ),		//	CBPTXT::T( $this->getText( 'name' ) ),
			'ITEM_ALIAS'			=>	CBPTXT::T( $this->getText( 'alias' ) ),
			'ITEM_DESCRIPTION'		=>	$this->getPlan()->getPersonalized( 'description', $this->user_id, false ),	//strip_tags( CBPTXT::T( $this->getText( 'description' ) ) ),
			'SITENAME'				=>	$_CB_framework->getCfg( 'sitename' ),
			'SITEURL'				=>	$_CB_framework->getCfg( 'live_site' ),
			'PLANS_TITLE'			=>	strip_tags( CBPTXT::T( $params->get( 'regTitle' ) ) ),
			'EMAILADDRESS'			=>	$user->email,
			'PREFIX_TEXT'			=>	$prefixText
		);
		return $extraStrings;
	}
	/**
	 * Sets amount and currency of the record depending on plan (and on input options if applicable)
	 *
	 * @param  cbPaidProduct  $plan
	 */
	public function getCurrencyAmount( &$plan ) {
		$this->amount					=	$plan->get( 'rate' );
		$this->currency					=	$plan->currency();
	}
	/**
	 * BACKEND RENDERING METHODS:
	 */
	/**
	 * USED by XML interface ONLY !!! Renders amount
	 *
	 * @param  string           $price
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderAmount( $price, &$params ) {
		if ( $price ) {
			$cbpaidMoney			=&	cbpaidMoney::getInstance();
			$priceRoundings			=	$params->get('price_roundings', 100 );
			$priceRounded			=	$cbpaidMoney->renderNumber( round( $price * $priceRoundings ) / $priceRoundings );
		} else {
			$priceRounded			= '-';
		}
		return $priceRounded;
	}
}
