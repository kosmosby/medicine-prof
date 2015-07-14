<?php
/**
 * @version $Id: cbpaidUsersubscriptionRecord.php 1605 2012-12-29 02:04:26Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Subscriptions database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidUsersubscriptionRecord extends cbpaidSomething {
	public $replaces_plan;
	public $replaces_subscription;
	public $subscription_date;
	public $last_renewed_date;
	/** datetime of subscription exiry or term of current recurring payments autorenewals subscription
	 * @var string */
	public $expiry_date;
	/** 0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @var int */
	public $autorenew_type;
	/** 0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @var int */
	public $autorecurring_type;
	/** Total recurrings allowed for this subscription (0 for unlimitted)
	 * @var int */
	public $regular_recurrings_total;
	/** Total recurrings used (means done and processed/subscribed at payment processor) for this subscription (can be higher than the recurrings allowed, e.g. if they are 0 for unlimitted)
	 * @var int */
	public $regular_recurrings_used;

	public $previous_expiry_date;
	public $previous_status;
	public $previous_recurrings_used;
	// public $raw_result;
	public $ip_addresses;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db  A database connector object
	 */
	public function __construct( &$db = null) {
		parent::__construct( '#__cbsubs_subscriptions', 'id', $db );
	}
	/**
	 * Returns the human name of the record (not translated)
	 *
	 * @return string
	 */
	public function recordName( ) {
		return 'Subscription';
	}
	/**
	 * Returns subscription part of article number
	 *
	 * @return string   'Sxxxx' where xxxx is the subscription id.
	 */
	public function getArtNoSubId( ) {
		return 'S' . $this->id;
	}
	/**
	 * Returns the name of the column/variable for the latest purchase/renewal depending on class
	 *
	 * @return string
	 */
	public function latestDatetimeColumnName( ) {
		return 'last_renewed_date';
	}
	/**
	 * Stores subscription only if needed, according to global setting createAlsoFreeSubscriptions
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		global $_CB_framework;

		$params								=&	cbpaidApp::settingsParams();
		$plan								=	$this->getPlan();

		if ( ( $params->get( 'createAlsoFreeSubscriptions') == 0 ) && $plan && $plan->isLifetimeValidity() && $plan->isFree() ) {
			$registeredUserGroup			=	$_CB_framework->getCfg( 'new_usertype' );
			if ( in_array( $plan->get( 'usergroup' ), array( $registeredUserGroup, 0 ) ) ) {
				// do not create subscriptions for lifetime free registered-level subscriptions:
				$k							=	$this->_tbl_key;
				$this->$k					=	0;
				return true;
			}
		}
		return parent::store( $updateNulls );
	}

	/**
	 * create a new subscription object and corresponding object in database
	 *
	 * @param int|null       $user_id                 CB user id
	 * @param cbpaidProduct  $plan                    plan object of this subscription
	 * @param array|null     $replacesSubscriptionId  array( planId, subscriptionId ) or NULL of the replaced subscription
	 * @param array|null     $parentSubId             array( PlanId, SubscriptionId ) or NULL of the parent subscription
	 * @param string         $status                  like status class variable
	 * @param boolean        $store                   true (default) if should be stored into db
	 * @param int            $subscriptionTime        time of subscription
	 */
	public function createSubscription( $user_id, &$plan, $replacesSubscriptionId = null, $parentSubId = null, $status = 'R', $store = true, $subscriptionTime = null ) {
		global $_CB_framework, $_CB_database;

		if ( $subscriptionTime === null ) {
			$subscriptionTime				=	$_CB_framework->now();
		}

		$this->reset();

		$this->user_id						=	$user_id;
		$this->plan_id						=	$plan->get( 'id' );
		if ( $replacesSubscriptionId ) {
			$this->replaces_plan			=	$replacesSubscriptionId[0];
			$this->replaces_subscription	=	$replacesSubscriptionId[1];
		}
		if ( $parentSubId ) {
			$this->parent_plan				=	$parentSubId[0];
			$this->parent_subscription		=	$parentSubId[1];
		} else {
			$this->parent_plan				=	0;
			$this->parent_subscription		=	0;
		}

		$this->subscription_date			=	date( 'Y-m-d H:i:s', $subscriptionTime );
		$this->last_renewed_date			=	$this->subscription_date;
		$this->expiry_date					=	null;
		$this->status						=	$status;
		$this->autorenew_type				=	0;			// will be changed later at payment time
		$this->autorecurring_type			=	0;			// will be changed later at payment time
		$this->regular_recurrings_total		=	$plan->get( 'recurring_max_times' );
		$this->regular_recurrings_used		=	0;
		$this->previous_recurrings_used		=	0;
		if ( is_object( $plan->_integrations ) ) {
			$this->integrations				=	$plan->_integrations->asJson();
		} else {
			$this->integrations				=	'';
		}

		if ( $store ) {
			$this->ip_addresses				=	cbpaidRequest::getIPlist();
			$this->historySetMessage( 'User subscription record created with status: ' . $status );
			if (!$this->store() ) {
				trigger_error( 'subscription store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
			}
		}
		$this->_plan						=	$plan;
	}
	/**
	 * loads the latest user subscription and checks if it's valid.
	 *
	 * @param  int          $user_id    User id
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if found and valid
	 */
	public function loadValidUserSubscription( $user_id, $time = null ) {
		$result			=	 $this->loadThisMatching( array(	'plan_id'	=>	(int) $this->plan_id,
				'user_id'	=>	(int) $user_id,
				'status'	=>	'A' ),
			array(	'last_renewed_date'		=>	'DESC' ) );
		if ( $result ) {
			$result		=	$this->checkIfValid( $time );
		}
		return $result;
	}

	/**
	 * create a new (or find an existing) subscription object and corresponding object in database and links to subscription to be replaced.
	 *
	 * @param  UserTable      $user                    CB base user object
	 * @param  cbpaidProduct  $plan                    payment plan object of this subscription
	 * @param  array          $replacesSubscriptionId  array( planId, subscriptionId ) or NULL of subscription to replace
	 * @param  array          $existingSubscriptionId  array( planId, subscriptionId ) or NULL of existing subscription
	 * @param  string         $status                  [Optional default='R'] 'N' = new, 'R' = renewal
	 * @param  boolean        $store                   [Optional default=true] if object to be stored into database
	 * @param  int            $subscriptionTime        [Optional default=time function]
	 * @param  string         $reason                  payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  array          $parentSubId             parent subscription's id,   if this subscription depends of a parent subscription
	 * @return float                                   remaing value (in plan's currency) of existing plan
	 */
	public function createOrLoadReplacementSubscription( &$user, &$plan, $replacesSubscriptionId = null, $existingSubscriptionId = null, $status = 'R', $store = true, $subscriptionTime = null, $reason = 'N', $parentSubId = null ) {
		global $_CB_framework, $_CB_database;

		if ( $parentSubId ) {
			$this->parent_plan				=	$parentSubId[0];
			$this->parent_subscription		=	$parentSubId[1];
		}

		if ( $subscriptionTime === null ) {
			$subscriptionTime				=	$_CB_framework->now();
		}

		if ( $existingSubscriptionId === null ) {
			// first tries to find if there is already an existing one, otherwise creates a new one:
			if ( $replacesSubscriptionId ) {
				if ( ! $this->loadThisMatching( array( 'replaces_subscription' => (int) $replacesSubscriptionId[1], 'replaces_plan' => (int) $replacesSubscriptionId[0], 'plan_id' => (int) $plan->get( 'id' ) ) ) ) {
					$this->createSubscription( $user->id, $plan, $replacesSubscriptionId, $parentSubId, $status, $store, $subscriptionTime );
				} else {
					/* only single pair of replace(subscription+plan) allowed ! 'user_id' => (int) $user->id, 'plan_id' => (int) $plan->get( 'id' ) : */
					if ( $this->user_id != $user->id ) {
						trigger_error( sprintf( 'Subscription::Loaded subscription id %d for different user (sub-user: %d, user: %d).', $this->id, $this->user_id, $user->id ), E_USER_WARNING );
					}
				}
			} else {
				if ( $plan->get( 'multiple' ) || ! $this->loadThisMatchingInt( 'user_id', (int) $user->id, 'plan_id', (int) $plan->get( 'id' ) ) ) {
					$this->createSubscription( $user->id, $plan, $replacesSubscriptionId, $parentSubId, $status, $store, $subscriptionTime );
				}
			}
		} else {
			if ( $plan->get( 'id' ) == $existingSubscriptionId[0] ) {
				if ( ! $this->load( (int) $existingSubscriptionId[1] ) ) {
					trigger_error( sprintf( 'Subscription::createOrLoadReplacementSubscription: subscription id %d load error: %s', $existingSubscriptionId[1], htmlspecialchars($_CB_database->getErrorMsg()) ), E_USER_NOTICE );
					$result			=	 $this->loadThisMatching( array(	'plan_id'	=>	(int) $plan->id,
							'user_id'	=>	(int) $user->id,
							'status'	=>	'I' ),
						array(	'subscription_date'	=>	'DESC'	) );
					if ( ! $result ) {
						trigger_error( sprintf( 'Subscription::createOrLoadReplacementSubscription: subscription with plan id %d user_id %d not found either', $plan->id, $user->id ), E_USER_NOTICE );
						$this->createSubscription( $user->id, $plan, $replacesSubscriptionId, $parentSubId, $status, $store, $subscriptionTime );
					}
				} else {
					if ( $this->replaces_subscription ) {
						$replacesSubscriptionId	=	array( $this->replaces_plan, $this->replaces_subscription );
					}
				}
			} else {
				trigger_error( sprintf( 'Subscription::createOrLoadReplacementSubscription: existingSubscription id %d / Plan id %d function param does not match plan param id %d error:', $existingSubscriptionId[0], $existingSubscriptionId[1], $plan->get( 'id' ) ), E_USER_ERROR );
				exit;
			}
		}

		$remainingValue				=	0;
		if ( $replacesSubscriptionId != null ) {
			if ( $this->getPlan() ) {
				$remainingValue			=	$this->getRemainingValueOfReplacedSubscription( $this->getPlan()->currency(), $replacesSubscriptionId[0], $replacesSubscriptionId[1], $subscriptionTime );
			}
		}
		return $remainingValue;
	}

	/**
	 * Computes remaining rates of the replaced subscriptions
	 *
	 * @uses   $this->_c
	 * @param  string  $currency_code
	 * @param  int     $planId
	 * @param  int     $subscriptionId
	 * @param  int     $subscriptionTime
	 * @return float
	 */
	private function getRemainingValueOfReplacedSubscription( $currency_code, $planId, $subscriptionId, $subscriptionTime ) {
		$remainingValue				=	0;
		$paidSomethingMgr			=	cbpaidSomethingMgr::getInstance();
		$replacedSubscription		=	$paidSomethingMgr->loadSomething( $planId, $subscriptionId );
		if ( $replacedSubscription ) {
			$replacedPlan			=	$replacedSubscription->getPlan();
			if ( $replacedPlan ) {
				$remainingValue		=	$replacedPlan->_priceConvert( $currency_code, $replacedSubscription->remainingRateValue( $subscriptionTime ) );
			}
		}
		return $remainingValue;
	}
	/**
	 * Compute rates and validities for payment item for a payment basket to be created
	 *
	 * @param  int                          $quantity          Quantity
	 * @param  string                       $currency_code     The currency of the payment basket (so the payment item must be converted into that currency
	 * @param  string                       $reason            payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int                          $now               unix time of now (single now time for consistency in db)
	 * @param  boolean                      $tryAutorecurring  try to build the payment basket as autorecurring
	 * @return array ( $first_rate, $first_validity, $rate, $validity, $autorecurring, $recurring_max_times )
	 */
	private function computeItemRatesAndValidity( $quantity, $currency_code, $reason, $now, $tryAutorecurring ) {
		if ( $tryAutorecurring ) {
			$autorecurring			=	$this->getPlanAttribute( 'autorecurring' );					// can be overridden by user depending on value and payment processor
			$recurring_max_times	=	$this->getPlanAttribute( 'recurring_max_times' );
		} else {
			$autorecurring			=	0;
			$recurring_max_times	=	0;
		}

		$remainingValueInPlanCur	=	0;
		if ( $this->replaces_plan && $this->replaces_subscription ) {
			global $_CB_framework;
			if ( $this->getPlan() ) {
				$subscriptionTime	=	$_CB_framework->now();
				$remainingValueInPlanCur =	$this->getRemainingValueOfReplacedSubscription( $this->getPlan()->currency(), $this->replaces_plan, $this->replaces_subscription, $subscriptionTime );
			}
		}
		$itemPrice					=	$this->getPriceOfNextPayment( $currency_code, $now, $quantity, $reason, $remainingValueInPlanCur );

		if ( $itemPrice === false ) {
			if ( $this->getPlanAttribute( 'auto_compute_upgrade' ) == 0 ) {
				$itemPrice			=	0;
			} else {
				return array( false, null, null, null, null, null, null );
			}
		}

		// Compute the prorate_discount for payment item record and displaying:
		if ( $remainingValueInPlanCur ) {
			$priceIfNoRemaingValue	=	$this->getPriceOfNextPayment( $currency_code, $now, $quantity, $reason, 0 );
			$prorate_discount		=	round( $priceIfNoRemaingValue - $itemPrice, 5 );
		} else {
			$prorate_discount		=	0;
		}

		if ( $autorecurring ) {
			// autorecurring possible:
			$validity				=	$this->getPlanAttribute( 'validity' );
			$first_validity			=	$this->getPlanAttribute( 'first_validity' );
			if ( $reason == 'R' ) {
				// renewal without special condition, means standard rate:
				$rate				=	$itemPrice;
				$first_rate			=	null;
				$first_validity		=	null;
			} else {
				// new or upgrade: can have first_rate or first_validity: $itemPrice is already discounted by prorateDiscount:
				$rate				=	$this->getPriceOfFollowUpPayments( $currency_code, $now, $quantity, $reason );	// N=New, (not R=Renew), U = Upgrade
				$first_rate			=	$itemPrice;
				if ( ( $this->getPlan()->getPlanVarName( $reason, 0, 'rate' ) == 'rate' ) && ( $this->getPlan()->getPlanVarName( $reason, 0, 'validity' ) == 'validity' ) ) {
					// no special first_rate or validity in plan:
					if ( $itemPrice == $rate ) {
						// if no prorateDiscount, $rate is already $itemPrice, so nothing special as first rate:
						$first_rate		 		=	null;
						$first_validity	 		=	null;
					} else {
						// there is a prorateDiscount, but no special first_rate, so we need to create one:
						$first_rate		 		=	$itemPrice;
						$first_validity	 		=	$validity;
						if ( $recurring_max_times ) {
							// there is a limit on recurring_max_times on standard rate, so as we took one for first rate, there is one less for standard rates:
							$recurring_max_times--;
							if ( $recurring_max_times == 0 ) {
								// if there was only 1 recurring, then we finally don't need the special first rate, but adapt the rate to the prorate_discount-ed itemPrice:
								$first_rate		 =	null;
								$first_validity	 =	null;
								$rate			 =	$itemPrice;
							}
						}
					}
				}
				// if both first rate and price match by any chance the subsquent ones, simplify payment item, and adapt recurring_max_time, if there was one:
				if ( ( $validity == $first_validity ) && ( $rate == $first_rate ) ) {
					$first_rate					=	null;
					$first_validity				=	null;
					if ( $recurring_max_times ) {
						$recurring_max_times++;
					}
				}
			}
		} else {
			// auto-recurring not allowed:
			if ( $reason == 'R' ) {
				// renewal without special condition, means standard rate:
				$validity			=	$this->getPlanAttribute( 'validity' );
			} else {
				// new or upgrade: can have first_rate or first_validity: $itemPrice is already discounted by prorateDiscount:
				$validity			=	$this->getPlanAttribute( $this->getPlan()->getPlanVarName( $reason, 0, 'validity' ) );
			}
			// the itemPrice already includes proRateDiscount, and the right first_rate or rate, so we can just apply it to rate, and clear first_rate and first_validity:
			$rate					=	$itemPrice;
			$first_rate				=	null;
			$first_validity			=	null;
		}
		return array( $first_rate, $first_validity, $rate, $validity, $prorate_discount, $autorecurring, $recurring_max_times );
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

		$bonustime					=	$this->getPlanAttribute( 'bonustime' );
		$planId						=	$this->plan_id;
		// $expiryTime				=	$this->computeExpiryTimeIfActivatedNow( $now, $reason );			//$this->expiry_date : this value is NULL at this stage here, so of no real use...
		// $expiry_date				=	( $expiryTime === null ? null : date( 'Y-m-d H:i:s', $expiryTime ) );
		$startTime					=	$this->computeStartTimeIfActivatedNow( $now, $reason );
		$start_date					=	date( 'Y-m-d H:i:s', $startTime );

		list( $first_rate, $first_validity, $rate, $validity, $prorate_discount, $autorecurring, $recurring_max_times )	=	$this->computeItemRatesAndValidity( $quantity, $currency_code, $reason, $now, $tryAutorecurring );

		if ( $first_rate === false ) {
			$false					=	false;
			return $false;
		}

		$stopTime					=	$this->computeExpiryTimeIfActivatedNow( $now, $reason, 1 );
		$stop_date					=	$stopTime ? date( 'Y-m-d H:i:s', $stopTime ) : '0000-00-00 00:00:00';
		$secondStopTime				=	$this->computeExpiryTimeIfActivatedNow( $now, $reason, 2 );
		$second_stop_date			=	$secondStopTime ? date( 'Y-m-d H:i:s', $secondStopTime ) : '0000-00-00 00:00:00';

		// build item description line, default:	[ITEM_ALIAS]	and		[PREFIX_TEXT] [PLANS_TITLE]: [ITEM_NAME][VALIDITY_IF_NOT_AUTORECURRING] for [USERNAME]
		cbimport( 'cb.tabs' );					// cbFormatDate needs this and cbreplaceVars too
		$extraStrings				=	$this->substitutionStringsForItemDetailed( false, $reason, $autorecurring );

		$cbUser						=	CBuser::getInstance( $this->user_id );
		$itemDescription			=	$cbUser->replaceUserVars( CBPTXT::T( $this->getPlanAttribute( 'item_text' ) ), false, false, $extraStrings, false );
		$itemAlias					=	$cbUser->replaceUserVars( CBPTXT::T( $this->getPlanAttribute( 'item_alias' ) ), false, false, $extraStrings, false );

		$item->createItem(	'usersubscription',
			$quantity,
			$artNumber,
			$itemDescription,
			$itemAlias,
			$currency_code,
			$rate,
			$this->getPlanAttribute( 'owner' ),
			$reason
		);
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
	public function updatePaymentItem( &$item, $paymentBasket , $quantity = null, $currency_code = null ) {
		$item->callIntegrations( 'beforeUpdatePaymentItem', $this, $paymentBasket );

		if ( $quantity === null ) {
			$quantity				=	$item->quantity;
		}
		if ( $currency_code === null ) {
			$currency_code			=	$item->currency;
		}
		$reason						=	$item->reason;
		$tryAutorecurring			=	$item->autorecurring;
		$start_time					=	strtotime( $item->start_date );
		list( $first_rate, /* $first_validity */, $rate, /* $validity */, $prorate_discount, /* $autorecurring */, /* $recurring_max_times */ )	=	$this->computeItemRatesAndValidity( $quantity, $currency_code, $reason, $start_time, $tryAutorecurring );
		if ( $first_rate !== false ) {
			$item->quantity			=	$quantity;
			$item->currency			=	$currency_code;
			$item->first_rate		=	$first_rate;
			$item->rate				=	$rate;
			$item->prorate_discount	=	$prorate_discount;
		}
		$item->callIntegrations( 'afterUpdatePaymentItem', $this, $paymentBasket );
	}
	/**
	 * computes start time if this subscription would be activated at $now time.
	 *
	 * @param  int     $now          Unix time to activate (and expire the previous subscription if it's an upgrade)
	 * @param  string  $reason       [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @return int                   Unix-time of expiry
	 */
	public function computeStartTimeIfActivatedNow( $now, $reason = 'N' ) {
		global $_CB_framework;

		$offset					=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$startTime				=	$now + $offset;

		$plan					=	$this->getPlan();
		if ( $reason != 'R' || ( $this->expiry_date == null ) || ( $this->expiry_date == '0000-00-00 00:00:00')) {
			;	// New Subscription (or renewal of a lifetime subscription :-S ???): keep $startTime as now.
		} elseif ( ( $reason == 'R' ) && $plan ) {
			// Renewal:
			$currentExpiryTime	=	$plan->strToTime( $this->expiry_date );
			$startTime			=	$this->selectRenewalTime( $currentExpiryTime, $startTime );
		}
		return $startTime;
	}
	/**
	 * computes expiry time if this subscription would be activated at $now time.
	 *
	 * @param  int           $now            Unix time to activate (and expire the previous subscription if it's an upgrade)
	 * @param  string        $reason         [optional] 'N' new subscription, 'R' renewal, 'U' update )
	 * @param  int           $occurrences    renewal occurrences
	 * @param  int           $autorenewed    0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 * @return int|null                      Unix-time of expiry or NULL for lifetime
	 */
	public function computeExpiryTimeIfActivatedNow( $now, $reason = 'N', $occurrences = 1, $autorenewed = 0 ) {
		$plan					=	$this->getPlan();
		if ( $plan ) {
			if ( $reason != 'R' || ( $this->expiry_date == null ) || ( $this->expiry_date == '0000-00-00 00:00:00')) {
				// New Subscription (or renewal of a lifetime subscription :-S ???):
				$varName			=	$plan->getPlanVarName( $reason, $this->getOccurrence(), 'validity' );
				$expiry				=	$plan->getExpiryTime( $now, $varName, 1, $reason );						// WARNING: adjusts $now to the real Start-time
				if ( ( $occurrences > 1 ) && $expiry ) {
					$expiry			=	$plan->getExpiryTime( $expiry, 'validity', $occurrences - 1, 'R' );		// WARNING: adjusts $expiry to the real Start-time, which is wanted here and ok here
				}
			} elseif ( $reason == 'R' ) {
				// Renewal:
				$currentExpiryTime	=	$plan->strToTime( $this->expiry_date );
				$expiryFromCurrExp	=	$plan->getExpiryTime( $currentExpiryTime, 'validity', $occurrences, $reason );
				if ( $autorenewed ) {
					$expiry			=	$expiryFromCurrExp;
				} else {
					$expiryFromNow	=	$plan->getExpiryTime( $now, 'validity', $occurrences, $reason );

					$expiry			=	$this->selectRenewalTime( $expiryFromCurrExp, $expiryFromNow );
				}
			} else {
				// This case never happens, but let's make lint happy:
				$expiry				=	0;
			}
		} else {
			$expiry					=	$now;
		}
		return $expiry;
	}

	/**
	 * Select time relative to last expiry or time relative to now depending on plan's renewal_start setting
	 *
	 * @param  mixed  $timeFromExpiry
	 * @param  mixed  $timeFromNew
	 * @return mixed
	 */
	protected function selectRenewalTime( $timeFromExpiry, $timeFromNew )
	{
		$plan					=	$this->getPlan();

		switch ( $plan->get( 'renewal_start' ) ) {
			case 3:
				// Renewal starts at expiration date or at payment date - whichever happens first (allow overlap but no free gap)
				return min( $timeFromExpiry, $timeFromNew );
			case 2:
				// Renewal starts at payment date (allows overlap or free gap)
				return $timeFromNew;
			case 1:
				// Renewal extends expiration date (without gap or overlap)
				return $timeFromExpiry;
			case 0:
			default:
				// Renewal starts at expiration date or at payment date - whichever happens last (allows free gap)
				return max( $timeFromExpiry, $timeFromNew );
		}
	}

	/**
	 * Returns remaining value of the subscription at $time in the currency of the plan
	 *
	 * @param  int           $time
	 * @return float         value
	 */
	public function remainingRateValue( $time ) {
		$plan				=	$this->getPlan();
		if ( $plan ) {
			$upgraded_sub	=	( $this->replaces_subscription != 0 );
			return $plan->remainingPriceValue( $time, $this->expiry_date, $this->regular_recurrings_used, $upgraded_sub, $this );
		} else {
			return 0;
		}
	}
	/**
	 * says if validity is unlimitted
	 *
	 * @return boolean	             true if active and lifetime, false if limitted time or inactive
	 */
	public function isLifetimeValidity( ) {
		return ( ( $this->status == 'A' ) && ( $this->expiry_date == '0000-00-00 00:00:00' || $this->expiry_date == null ) );
	}
	/**
	 * Checks if the subscription is valid given its current status, expiry date and a given time
	 *
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfValid( $time = null ) {
		global $_CB_framework;

		if ( $this->status == 'A' ) {
			if ( $this->expiry_date == '0000-00-00 00:00:00' ) {
				// lifetime subscription:
				return true;
			}
			$plan					=	$this->getPlan();
			if ( $plan ) {
				$valid				=	$plan->checkValid( $this->expiry_date, $time );
				if ( $valid ) {
					return true;
				}
				// check for the case of a free autorecurring plan:
				if ( $plan->get( 'autorecurring' ) ) {
					$max_occur		=	$plan->get( 'recurring_max_times' );
					if ( $max_occur != 0 ) {
						$occurence	=	$this->getOccurrence();
						if ( $occurence >= $max_occur ) {
							return false;
						}
					}
					$now			=	$_CB_framework->now();
					$params			=&	cbpaidApp::settingsParams();
					$currency_code	=	$params->get( 'currency_code', 'USD' );
					$rate			=	$this->getPriceOfNextPayment( $currency_code, $now, 1, 'R' );	// N=New, R=Renew + U = Upgrade
					if ( ( $rate !== false ) && ( $rate == 0 ) ) {
						$cbUser		=	CBuser::getInstance( (int) $this->user_id );
						if ( $cbUser ) {
							$user	=	$cbUser->getUserData();
						} else {
							$user	=	new UserTable( $this->_db );
						}
						$this->activate( $user, $now, true, 'R', 1, 2, 2 );
						return true;
						//					trigger_error('WOULD RENEW NOW', E_USER_NOTICE );	//		$this->activate();
					}
				}
			}
		}
		return false;
	}
	/**
	 * Gets the subscription date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getSubscriptionDate() {
		return $this->subscription_date;
	}
	/**
	 * Gets the subscription last renewal date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getLastRenewDate() {
		return $this->last_renewed_date;
	}
	/**
	 * Gets the subscription expiry date field of the subscription
	 *
	 * @return null|string  NULL if lifetime, SQL-datetime format in all other cases
	 */
	public function getExpiryDateField( ) {
		return $this->expiry_date;
	}
	/**
	 * Gets the subscription expiry date if subscription is valid at a given time
	 *
	 * @param  int                  $time  UNIX-formatted time at which to check (default: now).
	 * @return null|string|boolean         NULL if lifetime, SQL-datetime format if valid (not expired), FALSE if expired
	 */
	public function getExpiryDate( $time = null ) {
		$plan = $this->getPlan();
		if ( $this->checkIfValid( $time ) ) {
			if ( $plan && $plan->isLifetimeValidity() ) {
				$expiryDate = null;
			} else {
				$expiryDate = $this->expiry_date;
			}
		} else {
			$expiryDate = false;
		}
		return $expiryDate;
	}

	/**
	 * Checks if the subscription is renewable given its current status, expiry date and a given time depending on its plan
	 *
	 * @param  int|null      $time      UNIX-formatted time (default null: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfRenewable( $time = null ) {
		$plan											=	$this->getPlan();

		$result											=	false;
		if ( $plan ) {

			$realStatus									=	$this->status;
			if ( ( $this->status == 'A' ) && ( ! $this->checkIfValid( $time ) ) ) {
				$realStatus								=	'X';
			}

			switch ( $realStatus ) {
				case 'A':		// Active
					if ( $this->autorecurring_type != 2 && ( ( $this->regular_recurrings_total == 0 ) || ( $this->regular_recurrings_used < $this->regular_recurrings_total ) ) ) {
						$result							=	$plan->checkIfRenewable( $this->expiry_date, $time );
					}
					break;
				case 'R':		// Registered but unpaid
					$result								=	true;
					break;
				case 'X':		// Expired
				case 'C':		// Cancelled=unsubscribed
					if ( ( $this->regular_recurrings_total == 0 ) || ( $this->regular_recurrings_used < $this->regular_recurrings_total ) ) {
						if ( $plan->isPlanRenewable() ) {
							$paidUserExtension			=&	cbpaidUserExtension::getInstance( $this->user_id );
							$subscriptions				=	$paidUserExtension->getUserSubscriptions( null, false );
							foreach ( $subscriptions as $s ) {
								if ( $s->id != $this->id ) {
									if ( $s->plan_id == $this->plan_id ) {
										if ( $plan->get( 'multiple' ) ) {
											continue;
										}
										if ( $s->expiry_date > $this->expiry_date ) {
											// check if this is the latest subscribed plan of this type: no:
											return false;
										}
									}
									if ( $plan->get( 'exclusive' ) && ( $s->parent_plan == $this->parent_plan ) && $s->checkIfValid( $time ) ) {
										$sPlan			=	$s->getPlan();
										if ( $sPlan->get( 'exclusive' ) ) {
											// check if any other exclusive subscription with same parent plan is active:
											return false;
										}
									}
								}
							}

							$result						=	true;
						}
					}
					break;
				default:
					break;
			}
		}
		return $result;
	}
	/**
	 * Returns the current occurrence of the subscription:
	 *
	 * @return int  Number of occurrences being or having been active
	 */
	public function getOccurrence( ) {
		$plan				=	$this->getPlan();
		if ( $plan ) {
			$firstrateName	=	$plan->getPlanVarName( 'N', 0, 'rate' );
			if ( $firstrateName != 'rate' ) {
				if ( $this->regular_recurrings_used >= 1 ) {
					return $this->regular_recurrings_used + 1;
				} else {
					if ( in_array( $this->status, array( 'A', 'C', 'X' ) ) ) {
						return 1;
					} else {
						return 0;
					}
				}
			}
		}
		return $this->regular_recurrings_used;
	}
	/**
	 * Checks if the subscription is expired given its current status, expiry date and current time depending on its plan
	 * Adds the grace period before really expiring !
	 *
	 * @return boolean                  TRUE if just expired, FALSE otherwise (valid)
	 */
	public function expireIfExpired( ) {
		global $_CB_framework;

		$plan					=	$this->getPlan();
		if ( $plan ) {
			$nowGraceTimeAgo	=	$plan->substractValidityFromTime( $plan->get( 'graceperiod' ), $_CB_framework->now() );
		} else {
			$nowGraceTimeAgo	=	$_CB_framework->now();
		}
		$justExpired			=	! $this->checkIfValid( $nowGraceTimeAgo );
		if ( $justExpired ) {
			$user				=	CBuser::getUserDataInstance( (int) $this->user_id );
			$this->deactivate( $user, 'X' );
		}
		return $justExpired;
	}
	/**
	 * activates subscription
	 * and STORES into database
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
		$plan								=	$this->getPlan();
		$this->previous_expiry_date			=	$this->expiry_date;
		$this->previous_status				=	$this->status;
		if ( $completedNow ) {
			$expiry							=	$this->computeExpiryTimeIfActivatedNow( $now, $reason, $occurrences, $autorenewed );
			// First (and last) time payment Completed:
			if ( $reason != 'R' || ( $this->expiry_date == null ) || ( $this->expiry_date == '0000-00-00 00:00:00') ) {
				// New Subscription (or renewal of a lifetime subscription :-S ???):
			} elseif ( $reason == 'R' ) {
				// Renewal:
				$this->last_renewed_date	=	date( 'Y-m-d H:i:s', $now );
			}
			$this->expiry_date				=	( $expiry === null ? null : date( 'Y-m-d H:i:s', $expiry ) );
			$this->autorecurring_type		=	$autorecurring_type;
			$this->autorenew_type			=	$autorenew_type;
		} else {
			// Cancelled_Reversal, Horray we won the reversal dispute ! :-) : reopen the account (without changing the expiry):
			if ( $this->previous_expiry_date !== null ) {
				$this->expiry_date			=	$this->previous_expiry_date;
			} else {
				$this->last_renewed_date	=	date( 'Y-m-d H:i:s', $now );
				$expiry						=	$this->computeExpiryTimeIfActivatedNow( $now, $reason, $occurrences, $autorenewed );
				$this->expiry_date			=	( $expiry === null ? '0000-00-00 00:00:00' : date( 'Y-m-d H:i:s', $expiry ) );
			}
		}
		$this->previous_recurrings_used		=	$this->regular_recurrings_used;
		if ( $plan && $plan->getPlanVarName( $reason, $this->regular_recurrings_used, 'validity' ) != 'validity' ) {
			$occurrences--;
		}
		$this->regular_recurrings_used		+=	$occurrences;
		if ( ( $this->regular_recurrings_total ) && ( $this->regular_recurrings_used >= $this->regular_recurrings_total ) && $plan && ( $plan->get( 'action_at_last_recurring' ) == 1 ) ) {
			$this->expiry_date				=	'0000-00-00 00:00:00';		// Subscription stays free for rest of lifetime
		}
		$this->status						=	'A';	// Active
		$this->historySetMessage( 'User subscription activated as '
			. ( $reason == 'N' ? 'New' : ( $reason == 'R' ? 'Renewed' : ( $reason == 'U' ? 'Upgrade' : $reason ) ) )
			. ( $autorecurring_type ? ' autorecurring ' . ( $autorecurring_type == 2 ? 'with' : 'without' ) . ' notifications' : '' ) );
		if ( ! $this->store( true ) ) {			// store NULLs also
			global $_CB_database;
			trigger_error( 'activate subscription store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
			exit();
		}

		$replacedPlanId						=	null;
		$replacedSubscription				=	null;
		if ( $this->replaces_plan && $this->replaces_subscription ) {
			$paidSomethingMgr				=&	cbpaidSomethingMgr::getInstance();
			$replacedSubscription			=&	$paidSomethingMgr->loadSomething( $this->replaces_plan, $this->replaces_subscription );
			if ( $replacedSubscription ) {
				$replacedPlanId				=	$replacedSubscription->plan_id;
				$replacedSubscription->deactivate( $user, 'U' );	// Upgraded
				$replacedSubscription->stopAutoRecurringPayments();
				$causeDeact					=	'Upgraded';
				$reasonDeact				=	'U';
				$replacedSubscription->triggerIntegrations( $user, $causeDeact, $replacedPlanId, $replacedSubscription->id, $reasonDeact, $autorenewed );
			}
		}
		// do user unblocking and fire CB events:
		if ( $completedNow ) {
			$cause							=	'PaidSubscription';
		} else {
			$cause							=	'SubscriptionActivated';
		}
		$this->setBlockPaidUser( $user, $cause, $replacedPlanId, $replacedSubscription, $reason, $autorenewed );
	}
	/**
	 * deactivates subscription: saves previous status and expiry_date, expires now
	 * and STORES into database
	 *
	 * @param  UserTable  $user
	 * @param  string     $newStatus  Status to set: e.g. 'U': Upgraded: no user blocking or event fired
	 */
	public function deactivate( &$user, $newStatus ) {
		global $_CB_framework, $_CB_database;

		$this->previous_expiry_date		=	$this->expiry_date;
		$this->previous_status			=	$this->status;
		$this->autorecurring_type		=	0;
		$dateNow						=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
		if ( ( $this->expiry_date == '0000-00-00 00:00:00' ) || ( $dateNow < $this->expiry_date ) ) {
			$this->expiry_date			=	$dateNow;		// don't extend existing expiry date, but set it if lifetime, and shorten it if deactivated before expiry
		}
		$this->status					=	$newStatus;
		$this->historySetMessage( 'User subscription deactivated with status ' . ( $newStatus == 'C' ? 'Cancelled' : ( $newStatus == 'X' ? 'Expired' : ( $newStatus == 'U' ? 'Upgraded' : $newStatus ) ) ) );
		if ( ! $this->store( true ) ) {			// store NULLs also
			trigger_error( 'deactivate subscription store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
		}
		if ( $newStatus != 'U' ) {
			if ( in_array( $newStatus, array( 'C', 'X' ) ) && $this->replaces_plan && $this->replaces_subscription ) {
				// if the deactivated subscription is because of cancellation or expiry, but was the upgrade of a previous one, revert the previous one:
				$paidSomethingMgr		=&	cbpaidSomethingMgr::getInstance();
				$subscription			=&	$paidSomethingMgr->loadSomething( $this->replaces_plan, $this->replaces_subscription );
				if ( $subscription->status == 'U' ) {
					$subscription->revert( $user, 'Processed' );
				}
			}
			$cause						=	'Denied';
			$this->setBlockPaidUser( $user, $cause, null, null, null, 0 );
		}
	}
	/**
	 * reverts subscription state and expiry to previous values
	 * and STORES into database
	 *
	 * @param  UserTable  $user
	 * @param  string     $unifiedStatus   Payment/Subscription status ('PaidSubscription', 'Denied', 'RegistrationCancelled', NOT allowed here: 'Completed', 'Processed', 'Pending', 'In-Progress'
	 * @return string                      NULL: not applicable, 'I': Innexistant, 'R': Registered but not activated, 'A': Active, 'X': Expired  (all subscriptions in that chain, except for 'A')
	 */
	public function revert( &$user, $unifiedStatus ) {
		global $_CB_framework, $_CB_database;

		if ( in_array( $this->status, array( 'I', 'R' ) ) ) {
			// subscription never activated: simply delete it from db:
			$this->historySetMessage( 'User subscription deleted because it was reverted to unused status: ' . $this->status );
			if ( ! $this->delete() ) {
				trigger_error( 'subscription denied delete error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
			}
			$result							=	'I';		// Innexistant	and in this case do not look further replaced subscriptions are still active and don't need reversal !
		} else {
			// subscription has already been activated: revert status and date:
			$saveForRevertRevert			=	array( $this->expiry_date, $this->regular_recurrings_used, $this->status );
			$this->expiry_date				=	$this->previous_expiry_date;
			$this->regular_recurrings_used	=	$this->previous_recurrings_used;
			$this->status					=	$this->previous_status;
			$this->autorecurring_type		=	0;
			$this->previous_expiry_date		=	null;
			$this->previous_status			=	'I';		// Innexistant
			if ( $this->status == 'A' ) {
				if ( ! $this->checkIfValid() ) {
					// subscription expired in mean time: store it as expired, and return expired status
					$this->status			=	'X';		// Expired
				}
			}
			$this->historySetMessage( 'User subscription reverted due to new payment status: ' . $unifiedStatus );
			if ( $this->status == 'I' ) {					// do not delete if status was 'R' because if firest payment gets reverted and then reversal gets cancelled, we don't have the subscription anymore !
				// if back to unknown status, simply delete subscription.
				if ( ! $this->delete() ) {
					// could not delete (in backend maybe due to existing children subscriptions: revert:
					list( $this->expiry_date, $this->regular_recurrings_used, $this->status )		=	$saveForRevertRevert;
					if ( $_CB_framework->getUi() != 2 ) {
						trigger_error( 'subscription deletion error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_WARNING );
					}
					return $this->status;
				}
			} else {
				if ( $this->status == 'R' ) {
					$this->status			=	'C';		// Cancelled
					$this->expiry_date		=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
				}
				if ( ! $this->store( true ) ) {			// store NULLs also
					trigger_error( 'subscription reverted store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
				}
			}
			$result							=	$this->status;
			if ( $this->replaces_plan && $this->replaces_subscription && ( $this->status != 'A' ) ) {
				// this subscription was replacing a previous one: try reactivating the previous one if this one is not active now:
				$paidSomethingMgr			=&	cbpaidSomethingMgr::getInstance();
				$replacedSubscription		=&	$paidSomethingMgr->loadSomething( $this->replaces_plan, $this->replaces_subscription );
				if ( $replacedSubscription ) {
					$result					=	$replacedSubscription->revert( $user, $unifiedStatus );		// recurse
				}
			} else {
				$replacedSubscription		=	null;
			}

			// do user (un)blocking and fire CB events:
			if ( $this->status == 'A' ) {
				$cause						=	'SubscriptionActivated';
			} else {
				$cause						=	'SubscriptionDeactivated';
			}
			$this->setBlockPaidUser( $user, $cause, null, null, null, 0 );
		}
		return $result;
	}
	/**
	 * Notification from gateway that the automated auto-renewal has been cancelled or failed
	 * Stores this new auto-recurring status with subscription so that subscription can be re-activated or renewed
	 *
	 * @param  UserTable  $user
	 * @param  string     $unifiedStatus
	 * @param  string     $event_type      type of subscription cancellation event (paypal type): 'subscr_cancel', 'subscr_failed'
	 */
	public function autorecurring_cancelled( &$user, $unifiedStatus, $event_type ) {
		global $_CB_database;

		$this->autorecurring_type		=	0;
		$this->historySetMessage( 'User subscription autorecurring cancelled due to new payment status: ' . $unifiedStatus . ' and event ' . $event_type );
		if ( ! $this->store( true ) ) {			// store NULLs also
			trigger_error( 'autorecurring_cancelled store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
		}
	}
	/**
	 * Blocks or unblocks a given user's login and sends appropriate email
	 *
	 * @param  UserTable        $user
	 * @param  string           $cause                 'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied' 		//TBD: FIX PLUGINS OLD WAS: (one of: 'UserRegistration', 'UserConfirmation', 'UserApproval', 'NewUser', 'UpdateUser')
	 * @param  int              $replacedPlanId        [optional] id of old cancelled plan (for upgrade or blocking user)
	 * @param  cbpaidSomething  $replacedSubscription  [optional] replaced subscription which is getting deactivated
	 * @param  string           $reason                [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  int              $autorenewed          0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	protected function setBlockPaidUser( &$user, $cause = null, $replacedPlanId = null, $replacedSubscription = null, $reason = null, $autorenewed = 0 ) {
		$deactivate					=	( $this->status != 'A' );
		if  ( $deactivate ) {
			$deactivatedSub			=&	$this;
		} elseif ( $replacedSubscription !== null ) {
			$deactivatedSub			=	$replacedSubscription;
		} else {
			$deactivatedSub			=	null;
		}

		// First trigger integrations for this new/expired subscription:
//		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
//		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
//		$_PLUGINS->trigger( 'onCPayUserStateChange', array( &$user, (int) $deactivate, (int) ! $deactivate, $cause, $this->plan_id, $replacedPlanId, $reason, &$this ) );
		$this->triggerIntegrations( $user, $cause, $replacedPlanId, $replacedSubscription ? $replacedSubscription->id : null, $reason, $autorenewed );

		// Then check all user's subscriptions for consistency:
		$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
		$paidUserExtension->checkUserSubscriptions( ! $deactivate, $deactivatedSub, $reason );

		$this->sendNewStatusEmail( $user, $cause, $reason, $autorenewed );
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
	public function substitutionStringsForItemDetailed( $html, $reason, $autorecurring ) {
		global $_CB_framework;

		$params						=	cbpaidApp::settingsParams();
		$user						=	CBuser::getUserDataInstance( $this->user_id );

		$prefixText					=	'';
		if ( $reason == 'R' ) {
			$prefixText				=	CBPTXT::T("Renew");
		} elseif ( ( $reason == 'U' ) && $this->replaces_subscription ) {
			$prefixText				=	CBPTXT::T("Upgrade");
		}

		$validityText				=	$this->getFormattedValidityIfRenewed( $reason );
		$showtime					=	( $params->get( 'showtime', '1' ) == '1' );
		if ( $this->expiry_date && ( $this->expiry_date != '0000-00-00 00:00:00' ) ) {
			$expiryText				=	cbFormatDate( $this->expiry_date, 1, $showtime );
		} elseif ( $this->isLifetimeValidity() ) {
			$expiryText				=	CBPTXT::T( $params->get( 'regtextLifetime', 'Lifetime Subscription' ) );
		} else {
			$expiryText				=	'';
		}
		$validityIfNotAutoRec		=	'';
		$expiryTextIfNotAutoRec		=	'';
		if ( ! $autorecurring ) {
			$validityIfNotAutoRec	=	': ' . $validityText;
			if ( $expiryText ) {
				$expiryTextIfNotAutoRec	=	' ' . sprintf( CBPTXT::T("expiring on %s"), $expiryText );
			} else {
				$expiryTextIfNotAutoRec	=	'';
			}
		}

		$extraStrings				=	array(
			'ITEM_NAME'				=>	$this->getPlan()->getPersonalized( 'name', $this->user_id, false ),		//	CBPTXT::T( $this->getText( 'name' ) ),
			'ITEM_ALIAS'			=>	CBPTXT::T( $this->getText( 'alias' ) ),
			'ITEM_DESCRIPTION'		=>	$this->getPlan()->getPersonalized( 'description', $this->user_id, false ),		//	strip_tags( CBPTXT::T( $this->getText( 'description' ) ) ),
			'SITENAME'				=>	$_CB_framework->getCfg( 'sitename' ),
			'SITEURL'				=>	$_CB_framework->getCfg( 'live_site' ),
			'PLANS_TITLE'			=>	strip_tags( CBPTXT::T( $params->get( 'regTitle' ) ) ),
			'EMAILADDRESS'			=>	$user->email,
			'PREFIX_TEXT'			=>	$prefixText,
			'VALIDITY'				=>	$validityText,
			'SUBSCRIPTION_VALIDITY_PERIOD_IF_RENEWED'	=>	$validityText,	// alias of VALIDITY
			'SUBSCRIPTION_EXPIRY_DATE_IF_RENEWED'		=>	$this->getFormattedExpiryDateIfRenewed( $reason ),
			'SUBSCRIPTION_VALIDITY_PERIOD_REMAINING'	=>	$this->getFormattedValidityRemaining(),
			'VALIDITY_IF_NOT_AUTORECURRING'				=>	$validityIfNotAutoRec,
			'EXPIRY'				=>	$expiryText,
			'EXPIRING_IF_NOT_AUTORECURRING'	=>	$expiryTextIfNotAutoRec,
			'SUBSCRIPTION_EXPIRY_DATE'					=>	$expiryText,		// alias of EXPIRY
			'SUBSCRIPTION_SIGNUP_DATE'					=>	( $this->subscription_date ? cbFormatDate( $this->subscription_date, 1, $showtime ) : '' ),
			'SUBSCRIPTION_LAST_RENEWAL_DATE'			=>	( $this->last_renewed_date ? cbFormatDate( $this->last_renewed_date, 1, $showtime ) : '' ),
			'SUBSCRIPTION_RENEWAL_PRICE'				=>	$this->displayPeriodPrice( 'R', $html ),
		);
		return $extraStrings;
	}
	/**
	 * Returns substitution strings
	 *
	 * @see cbpaidSomething::substitutionStrings()
	 *
	 * @param  boolean  $html                              HTML or TEXT return
	 * @param  boolean  $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @return array
	 */
	public function substitutionStrings( $html, $runContentPluginsIfAllowedByPlan = true ) {
		$reason										=	$this->status == 'C' ? 'N' : 'R';
		$autorecurring								=	false;
		$strings									=	array_merge( parent::substitutionStrings( $html, $runContentPluginsIfAllowedByPlan ), $this->substitutionStringsForItemDetailed( $html, $reason, $autorecurring ) );
		// Fix according to meaning outside an order:
		unset( $strings['VALIDITY_IF_NOT_AUTORECURRING'] );
		unset( $strings['EXPIRY'] );
		unset( $strings['VALIDITY'] );
		return $strings;
	}
	/**
	 * Get a correct display of the formatted validity of a plan: override if needed
	 *
	 * @return string                     Formatted text giving validity of this subscription
	 */
	public function getFormattedExpirationDateText() {
		$params					=&	cbpaidApp::settingsParams();

		$expDate				=	$this->getExpiryDate();
		if ( $expDate === null ) {
			$text				=	CBPTXT::T( $params->get( 'regtextLifetime', 'Lifetime Subscription' ) );
		} else {
			$showtime			=	( $params->get( 'showtime', '1' ) == '1' );
			$dateFormatted		=	cbFormatDate( $this->expiry_date, 1, $showtime );

			if ( $expDate !== false ) {
				$text			=	$dateFormatted;
			} else {
				//TODO merge with drawSubscriptionNameDescription
				switch ( $this->status ) {
					case 'X':
						$text	=	CBPTXT::T('Expired %s');
						break;
					case 'U':
						$text	=	CBPTXT::T('Upgraded %s');
						break;
					case 'C':
						$text	=	CBPTXT::T('Unsubscribed %s');
						break;
					case 'R':
						$subTxt	=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
						$text	=	sprintf( CBPTXT::T("%s not yet paid"), ucfirst( $subTxt ) );
						break;
					default:
						$subTxt	=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
						$text	=	sprintf( CBPTXT::T("Unknown state of %s"), $subTxt );
						break;
				}
				$text			=	sprintf( $text, $dateFormatted );
			}
		}
		return $text;
	}
	/**
	 * Get a correct display of the formatted validity of a plan
	 *
	 * @return string                     Formatted text giving validity of this subscription
	 */
	public function getFormattedValidityRemaining() {
		if ( ( $this->status == 'A' ) && ( $this->expiry_date == '0000-00-00 00:00:00' ) ) {
			$params				=&	cbpaidApp::settingsParams();
			return CBPTXT::T( $params->get( 'regtextLifetime', 'Lifetime Subscription' ) );
		} else {
			$occurrence			=	$this->regular_recurrings_used;
			if ( $occurrence > 0 ) {
				$reason			=	'R';
			} elseif ( $this->replaces_plan && $this->replaces_subscription ) {
				$reason			=	'U';
			} else {
				$reason			=	'N';
			}
			$varName			=	$this->getPlan()->getPlanVarName( $reason, $occurrence, 'validity' );
			return $this->getPlan()->getFormattedValidity( $this->getPlan()->strToTime( $this->expiry_date ), $this->getPlan()->strToTime( $this->subscription_date ), $varName );
		}
	}
	/**
	 * Get a correct display of the formatted validity of a plan
	 *
	 * @param  string        $reason      [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @return string                     Formatted text giving validity of this subscription if renewed
	 */
	public function getFormattedValidityIfRenewed( $reason ) {
		$occurrence			=	$this->getOccurrence() + 1;
		$varName			=	$this->getPlan()->getPlanVarName( $reason, $occurrence, 'validity' );
		$occurrences		=	1;
		return $this->getPlan()->getFormattedValidity( null, $this->getPlan()->strToTime( $this->expiry_date ), $varName, $reason, $occurrences );
	}
	/**
	 * Get a correct display of the formatted validity of a plan
	 *
	 * @param  string        $reason      [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @return string                     Formatted text giving validity of this subscription if renewed
	 */
	public function getFormattedExpiryDateIfRenewed( $reason ) {
		$occurrence			=	$this->getOccurrence() + 1;
		$varName			=	$this->getPlan()->getPlanVarName( $reason, $occurrence, 'validity' );
		$occurrences		=	1;
		return $this->getPlan()->getFormattedExpiryDate( $this->getPlan()->strToTime( $this->expiry_date ), $varName, $reason, $occurrences );
	}
	/**
	 * Stop auto-recuring payments for $this Something subscription
	 *
	 * @return boolean|string  TRUE: Success, string: error message
	 */
	public function stopAutoRecurringPayments( ) {
		if ( isset( $this->autorecurring_type ) && ( $this->autorecurring_type > 0 ) ) {
			// get the most recent payment item for that user, plan and subscription:
			$paymentItem		=	$this->loadLatestPaymentItem( );
			if ( $paymentItem ) {
				$result			=	$paymentItem->stopAutorecurringPayments();
			} else {
				// No payment basket Found: probably a free subscription:
				$result			=	true;
			}
		} else {
			$result				=	true;
		}
		return $result;
	}
}	// class cbpaidUsersubscriptionRecord
