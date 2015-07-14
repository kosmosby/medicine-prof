<?php
/**
 * @version $Id: cbpaidSomething.php 1608 2012-12-29 04:12:52Z beat $
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
abstract class cbpaidSomething extends cbpaidTable {
	/** Primary key
	 * @var int */
	public $id							=	null;
	/**
	 * @var string
	 * Status
	 * Possible values:
	 * - null - Uninitialized
	 * - I - Invalid
	 * - R - Registration done, unpaid
	 * - A - Active, paid (if payable)
	 * - U - Upgraded to another Something
	 * - X - Expired
	 * - C - Cancelled (unsubscribed)
	 */
	public $status;
	public $user_id;
	public $plan_id;
	public $parent_plan				=	0;
	public $parent_subscription		=	0;
	public $integrations;

	/** Plan associated with this paidSomething:
	 *  @var cbpaidProduct */
	protected $_plan				=	null;
	// temp vars for upgrades:
	public $_hideItsPlan;
	public $_hideThisSubscription;
	public $_upgradePlansIdsDiscount	=	array();
	public $_allowedActions			=	array();
	public $_reason;

	protected $_displayPeriodPriceRecursionsLimiter	=	1;
	/**
	 * Constructor
	 *
	 *	@param string      $table  name of the table in the db schema relating to child class
	 *	@param string      $key    name of the primary key field in the table
	 *	@param CBdatabase  $db     CB Database object
	 */
	public function __construct( $table, $key, &$db = null ) {
		parent::__construct( $table, $key, $db );
		$this->_historySetLogger();
	}
	/**
	 * Returns the human name of the record (not translated)
	 *
	 * @return string
	 */
	abstract public function recordName( );
	/**
	 * Returns subscription part of article number
	 *
	 * @return string   'Sxxxx' where xxxx is the subscription id.
	 */
	abstract public function getArtNoSubId( );
	/**
	 * Returns the name of the column/variable for the latest purchase/renewal depending on class
	 *
	 * @return string
	 */
	abstract public function latestDatetimeColumnName( );
	/**
	 * Stores subscription only if needed, according to global setting createAlsoFreeSubscriptions
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		// Clears the cache of the subscriptions for that user id, so next fetch is updated as well:
		$paidUserExtension			=&	cbpaidUserExtension::getInstance( $this->user_id );
		$paidUserExtension->getUserSubscriptions( 'clearcache' );

		return parent::store( $updateNulls );
	}
	/**
	 * loads the latest something product instance that has a status (active by default)
	 *
	 * @param  int|null      $user_id     User id (null=any)
	 * @param  string|array  $status      Status of subscription (null=any)
	 * @param  array         $conditions  conditions in the format of loadThisMatchingList(): column => value  pairs OR array( column => array( operator, value ) ) where value int, float, string, or array of int (array will implode & operator = becomes IN)
	 * @param  int           $offset      The offset to start selection
	 * @param  int           $limit       LIMIT statement (0=no limit)
	 * @return cbpaidSomething[]          TRUE if found and valid
	 */
	public function loadAllSomethingsOfUser( $user_id, $status = 'A', $conditions = null, $offset = 0, $limit = 0 ) {
		$conditions['plan_id']		=	(int) $this->plan_id;
		if ( $user_id ) {
			$conditions['user_id']	=	(int) $user_id;
		}
		if ( $status ) {
			$conditions['status']	=	$status;
		}
		return $this->loadThisMatchingList( $conditions, array( 'id' => 'DESC' ), $offset, $limit );
	}
	/**
	 * loads the latest something product instance that has a status (active by default)
	 *
	 * @param  int|null     $user_id    User id
	 * @param  string       $status     Status of subscription (null=any)
	 * @return boolean                  TRUE if found and valid
	 */
	public function loadLatestSomethingOfUser( $user_id, $status = 'A' ) {
		$whereFields				=	array(	'plan_id'	=>	(int) $this->plan_id,
			'user_id'	=>	(int) $user_id );
		if ( $status ) {
			$whereFields['status']	=	$status;
		}
		$result						=	 $this->loadThisMatching( $whereFields, array( 'id' => 'DESC' ) );
		return $result;
	}
	/**
	 *	Check for whether dependancies exist for this object in the db schema
	 *
	 *	@param  int      $oid   Optional key index
	 *	@return boolean         TRUE: OK to delete, FALSE: not OK to delete, error in $this->_error
	 */
	public function canDelete( $oid = null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}
		if ( $this->plan_id && ! $oid ) {
			$plan_id	=	$this->plan_id;
		} else {
			$classname	=	get_class( $this );
			$something	=	new $classname( $this->_db );
			/** @var $something cbpaidSomething */
			if ( $something->load( $this->$k ) ) {
				$plan_id	=	$something->plan_id;
			} else {
				$plan_id	=	null;
			}

		}
		if ( $plan_id ) {
			$query = "SELECT COUNT(*)"
				. "\n FROM " . $this->_db->NameQuote( $this->_tbl )
				. "\n WHERE `parent_plan` = ". (int) $plan_id
				. "\n AND `parent_subscription` = ". (int) $this->$k
			;
			$this->_db->setQuery( $query );

			$obj = null;
			$count = $this->_db->loadResult($obj);
			if ( $count > 0 ) {
				$this->setError( CBPTXT::T("Product instance still has children and can not be deleted") );
				return false;
			}
		}
		return parent::canDelete( $oid );
	}
	/**
	 * loads the plan corresponding to this subscription into internal variable
	 *
	 * @return void
	 */
	protected function loadPlan( ) {
		$plansMgr		=&	cbpaidPlansMgr::getInstance();
		$this->_plan	=&	$plansMgr->loadPlan( $this->plan_id );
	}
	/**
	 * gets the plan corresponding to this subscription (loads it if not loaded)
	 *
	 * @return cbpaidProduct
	 */
	public function getPlan( ) {
		if ( $this->_plan === null ) {
			$this->loadPlan();
		}
		return $this->_plan;
	}
	/**
	 * loads the latest user subscription and checks if it's valid.
	 *
	 * @param  int          $user_id    User id
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if found and valid
	 */
	public function loadValidUserSubscription( /** @noinspection PhpUnusedParameterInspection */ $user_id, $time = null ) {
		// override
		return false;
	}
	/**
	 * loads the latest user subscriptions
	 * can be overridden if needed
	 *
	 * @param  int|null      $user_id   User id
	 * @param  string        $status    'A' by default. Or: null for all statuses
	 * @param  int           $plan_id   null for all by default
	 * @return static[]
	 */
	public function loadTheseSomethingsOfUser( $user_id, $status = 'A', $plan_id = null ) {
		$conditions		=	array(  'user_id'	=>	(int) $user_id );
		if ( $status ) {
			$conditions['status']	=	$status;
		}
		if ( $plan_id ) {
			$conditions['plan_id']	=	$plan_id;
		}
		return $this->loadThisMatchingList( $conditions, array( $this->latestDatetimeColumnName() => 'DESC' ) );
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
	 * @param  array          $parentSubId             parent plan and subscription's id,   if this subscription depends of a parent subscription
	 * @return float                                   remaing value (in plan's currency) of existing plan
	 */
	public function createOrLoadReplacementSubscription(
		&$user,
		&$plan,
		/** @noinspection PhpUnusedParameterInspection */ $replacesSubscriptionId = null,
		/** @noinspection PhpUnusedParameterInspection */ $existingSubscriptionId = null,
		/** @noinspection PhpUnusedParameterInspection */ $status = 'R',
		/** @noinspection PhpUnusedParameterInspection */ $store = true,
		/** @noinspection PhpUnusedParameterInspection */ $subscriptionTime = null,
		/** @noinspection PhpUnusedParameterInspection */ $reason='N', $parentSubId = null )
	{
		$this->reset();

		$this->user_id						=	$user->id;
		$this->plan_id						=	$plan->get( 'id' );

		if ( $parentSubId ) {
			$this->parent_plan				=	$parentSubId[0];
			$this->parent_subscription		=	$parentSubId[1];
		}
		if ( is_object( $plan->_integrations ) ) {
			$this->integrations				=	$plan->_integrations->asJson();
		} else {
			$this->integrations				=	'';
		}
		return 0.0;			// override !
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
	abstract public function & createPaymentItem( $quantity, $currency_code, $artNumber, $prefixText, $reason, $now, $tryAutorecurring );
	/**
	 * Updates the payment item corresponding to this Something
	 *
	 * @param  cbpaidPaymentItem    $item
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  int                  $quantity          Quantity
	 * @param  string               $currency_code     The currency of the payment basket (so the payment item must be converted into that currency
	 * @return void
	 */
	abstract public function updatePaymentItem( &$item, $paymentBasket, $quantity = null, $currency_code = null );

	/**
	 * computes start time if this subscription would be activated at $now time.
	 *
	 * @param  int     $now          Unix time to activate (and expire the previous subscription if it's an upgrade)
	 * @param  string  $reason       [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @return int                   Unix-time of expiry
	 */
	public function computeStartTimeIfActivatedNow( $now, /** @noinspection PhpUnusedParameterInspection */ $reason = 'N' ) {
		global $_CB_framework;

		$offset					=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$startTime				=	$now + $offset;

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
	public function computeExpiryTimeIfActivatedNow( /** @noinspection PhpUnusedParameterInspection */ $now, $reason = 'N', $occurrences = 1, $autorenewed = 0 ) {
		return null;
	}

	/**
	 * Checks if the subscription is valid given its current status, expiry date and a given time
	 *
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfValid( /** @noinspection PhpUnusedParameterInspection */ $time = null ) {
		return ( $this->status == 'A' );
	}
	/**
	 * Checks if the subscription's parent subscription(s) is (are) valid given its (their) current status, expiry date and a given time
	 * If no parent subscription, returns TRUE.
	 * If parent subscription has a parent, check that one too, RECURSING on the parent object(s) until no more parents.
	 *
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfParentSubscriptionIsValid( $time = null ) {
		if ( ( $this->parent_plan == 0 ) && ( $this->parent_subscription == 0 ) ) {
			return true;
		} else {
			$paidSomethingMgr				=&	cbpaidSomethingMgr::getInstance();
			$parentSubscription				=&	$paidSomethingMgr->loadSomething( $this->parent_plan, $this->parent_subscription );
			if ( $parentSubscription ) {
				return $parentSubscription->checkIfThisAndParentSubscriptionIsValid( $time );
			} else {
				return true;
			}
		}
	}
	/**
	 * Checks if the subscription AND it's parent subscription(s) is (are) valid given its (their) current status, expiry date and a given time
	 * If parent subscription has a parent, check that one too, RECURSING on the parent object(s) until no more parents.
	 *
	 * @param  int          $time       UNIX-formatted time (default: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfThisAndParentSubscriptionIsValid( $time = null ) {
		if ( ! $this->checkIfValid( $time ) ) {
			return false;
		} else {
			return $this->checkIfParentSubscriptionIsValid( $time );
		}
	}
	/**
	 * Gives the "should-be" status of the subscription, taking in account the should-be expiration date (and not the bonus time)
	 * @param  int     $time
	 * @return string
	 */
	public function realStatus( $time ) {
		$realStatus			=	$this->status;
		if ( ( $this->status == 'A' ) && ( ! $this->checkIfValid( $time ) ) ) {
			$realStatus		=	'X';
		}
		return $realStatus;
	}
	/**
	 * Displays the period and price for $this cancelled ($reason = 'N'ew) or active or expired ($rason = 'R'enewed) subscription
	 *
	 * @param  string   $reason  'N' or 'R'
	 * @param  boolean  $html    HTML or TEXT result wanted
	 * @return string                       HTML or Text
	 */
	public function displayPeriodPrice( $reason, $html = true ) {
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

		$plan				=	$this->getPlan();
		if ( $this->_displayPeriodPriceRecursionsLimiter-- == 1 ) {
			$_PLUGINS->trigger( 'onCPayBeforeDisplaySubscriptionPeriodPrice', array( &$plan, &$this, $reason ) );
		}

		$now				=	$_CB_framework->now();
		$expiryTime			=	$this->computeStartTimeIfActivatedNow( $now, $reason );
		$htmlText			=	$plan->displayPeriodPrice( $reason, $this->getOccurrence() + 1, null, $expiryTime, $html );

		if ( $this->_displayPeriodPriceRecursionsLimiter == 0 ) {
			$_PLUGINS->trigger( 'onCPayAfterDisplaySubscriptionPeriodPrice', array( &$plan, &$this, &$htmlText, $reason ) );
		}
		++$this->_displayPeriodPriceRecursionsLimiter;

		return $htmlText;
	}
	/**
	 * Checks for renewal and upgrade possibilities for this subscription
	 * sets:
	 *         boolean       $this->_hideItsPlan
	 *         boolean       $this->_hideThisSubscription
	 *
	 * @param  int        $ui          1: frontend user, 2: backend admin
	 * @param  UserTable  $user        user id being displayed
	 * @param  float      $quantity    Quantity purchased
	 * @param  int        $now         unix time
	 * @param  int        $subsAccess  0 has only read access, 1 has user access, 2 reserved for future Super-admin access
	 * @return void
	 */
	public function checkRenewalUpgrade( $ui, $user, $quantity, $now, $subsAccess ) {
		/* see gui		//TBD later: maybe texts should go there...
				$buttonTexts		=	array(  'A' => '',
												'AA' => CBPTXT::T("Renew Now") . ': %s',					// local state
												'R' => CBPTXT::T("Pay now"),
												'U' => '',
												'C' => CBPTXT::T("Resubscribe") . ': %s',
												'X' => CBPTXT::T("Reactivate") . ': %s',
												'XX' => '',									// local state
												'ZZ' => '' );								// local state

				$buttonActions		=	array(  'A' => '',
												'AA' => "renew",							// local state
												'R' => "pay",
												'U' => '',
												'C' => "renew",
												'X' => "renew",
												'XX' => '',									// local state
												'ZZ' => '' );								// local state
		*/
		// actions allowed:
		$this->_allowedActions								=	array();
		$this->_hideItsPlan									=	true;				// in case plan missing from database

		$plan												=	$this->getPlan();
		if ( ! $plan ) {
			cbpaidApp::setLogErrorMSG( 5, $this, sprintf( 'something::checkRenewalUpgrade subid %d cannot load planid %d.', $this->id, $this->plan_id ), null );
			return;
		}

		$params												=&	cbpaidApp::settingsParams();
		$showRenewButtons									=	( $ui == 2 ) || ( $subsAccess && ( $params->get( 'showRenewButtons', '1' )		 == '1' ) );
		$showUnsubscribeButtons								=	( $ui == 2 ) || ( $subsAccess && ( $params->get( 'showUnsubscribeButtons', '0' ) == '1' ) );

		$realStatus											=	$this->realStatus( $now );

		// when we are in frontend: don't show upgraded subscriptions:
		$this->_hideThisSubscription						=	( ( $ui == 1 ) && ( $realStatus == 'U' ) ) || ( ! $this->checkIfParentSubscriptionIsValid( $now ) );
		// remaining value for this subscription (for prorate plans):
		$remainingValue										=	$this->remainingRateValue( $now );

		// upgrade possibilities for this subscription:
		// if ( $this->checkIfThisAndParentSubscriptionIsValid( $now ) ) {
		$this->_upgradePlansIdsDiscount					=	$plan->upgradePlansPossibilities( $ui, $user, $this, $remainingValue, $quantity, $now );
		// } else {
		//	$this->_upgradePlansIdsDiscount					=	array();
		// }
		switch ( $realStatus ) {
			case 'R':
				$this->_allowedActions['pay']				=	array( 'button_text' => $plan->buttonText( 'pay' ), 'warning' => '' );
				break;
			case 'A':
				if ( $showRenewButtons && $this->checkIfRenewable( $now ) ) {
					// $quantity								=	1;
					$periodPrice							=	$this->displayPeriodPrice( 'R' );			// getPriceOfNextPayment( null, $now, $quantity, 'R' );
					$separator								=	( ( $periodPrice && ( trim( str_replace( '&nbsp;', '', $periodPrice ) ) ) ) ? ': ' : '' );
					$this->_allowedActions['renew']			=	array( 'button_text' => $plan->buttonText( 'renew' ) . $separator . $periodPrice, 'warning' => '' );
				}
				break;
			case 'X':
				if ( $showRenewButtons && $this->checkIfRenewable( $now ) ) {
					// $quantity								=	1;
					$periodPrice							=	$this->displayPeriodPrice( 'R' );
					$separator								=	( ( $periodPrice && ( trim( str_replace( '&nbsp;', '', $periodPrice ) ) ) ) ? ': ' : '' );
					$this->_allowedActions['reactivate']	=	array( 'button_text' => $plan->buttonText( 'reactivate' ) . $separator . $periodPrice, 'warning' => '' );
				}
				break;
			case 'C':
				if ( $showRenewButtons && $this->checkIfRenewable( $now ) ) {
					// $quantity								=	1;
					$periodPrice							=	$this->displayPeriodPrice( 'N' );
					$separator								=	( ( $periodPrice && ( trim( str_replace( '&nbsp;', '', $periodPrice ) ) ) ) ? ': ' : '' );
					$this->_allowedActions['resubscribe']	=	array( 'button_text' => $plan->buttonText( 'resubscribe' ) . $separator . $periodPrice, 'warning' => '' );
				}
				break;
			case 'I':
			case 'U':
			default:
				break;
		}
		if ( $showUnsubscribeButtons && $this->checkIfValid( $now ) ) {
			$this->_allowedActions['unsubscribe']			=	array( 'button_text' => $plan->buttonText( 'unsubscribe' ), 'warning' => '' );
		}
		if ( $ui == 2 ) {
			$this->_allowedActions['delete']				=	array( 'button_text' => $plan->buttonText( 'delete' ), 'warning' => CBPTXT::T("This will permanently delete this subscription from the database when you save the user profile. Are you really sure ?") );
		}
		// don't propose plans for Active, Registered and Cancelled subscriptions:
		// $this->_hideItsPlan								=	( in_array( $this->status, array( 'A', 'R', 'C', 'X' ) ) );		// (  count ( $sub->_allowedActions ) == 0 );
		$this->_hideItsPlan									=	( $plan->get( 'multiple' ) == 0 ) && ( in_array( $realStatus, array( 'A', 'R', 'U' ) ) || $this->checkIfRenewable( $now ) );		// (  count ( $sub->_allowedActions ) == 0 );
	}
	/**
	 * Returns remaining value of the subscription at $time in the currency of the plan
	 *
	 * @param  int           $time
	 * @return float         value
	 */
	public function remainingRateValue( /** @noinspection PhpUnusedParameterInspection */ $time ) {
		return 0.0;			// override !
	}
	/**
	 * Gets the subscription date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getSubscriptionDate() {
		return null;		// override !
	}
	/**
	 * Gets the subscription last renewal date if subscription is subscribed
	 *
	 * @return mixed                    NULL if none, SQL-datetime format if valid
	 */
	public function getLastRenewDate() {
		return null;		// override !
	}
	/**
	 * Gets the subscription expiry date field of the subscription
	 *
	 * @return null|string  NULL if lifetime, SQL-datetime format in all other cases
	 */
	public function getExpiryDateField( ) {
		return null;
	}
	/**
	 * Gets the subscription expiry date if subscription is valid at a given time
	 *
	 * @param  int                  $time  UNIX-formatted time at which to check (default: now).
	 * @return null|string|boolean         NULL if lifetime, SQL-datetime format if valid (not expired), FALSE if expired
	 */
	public function getExpiryDate( /** @noinspection PhpUnusedParameterInspection */ $time = null ) {
		return null;		// override !
	}
	/**
	 * Checks if the subscription is renewable given its current status, expiry date and a given time depending on its plan
	 *
	 * @param  int|null      $time      UNIX-formatted time (default null: now)
	 * @return boolean                  TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfRenewable( /** @noinspection PhpUnusedParameterInspection */ $time = null ) {
		return false;		// override !
	}
	/**
	 * Returns the current occurrence of the subscription:
	 *
	 * @return int  Number of occurrences being or having been active
	 */
	public function getOccurrence( ) {
		return 0;			// override !
	}
	/**
	 * PRODUCT ACTIVATION METHODS (TO BE OVERRIDEN)
	 */
	/**
	 * activates subscription
	 *
	 * @param UserTable  $user
	 * @param int        $now                 Unix time to activate (and expire the previous subscription if it's an upgrade): WARNING: can be in the past, in case of imports!
	 * @param boolean    $completedNow        [optional] True if first time completed, False if not first time (e.g. cancelled reversal)
	 * @param string     $reason              [optional] 'N' new subscription, 'R' renewal, 'U'=update )
	 * @param int        $occurrences          renewal occurrences
	 * @param int        $autorecurring_type  0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param int        $autorenew_type      0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @param int        $autorenewed         0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	abstract public function activate( &$user, $now, $completedNow = true, $reason='N', $occurrences = 1, $autorecurring_type = 0, $autorenew_type = 0, $autorenewed = 0 );
	/**
	 * deactivates subscription: saves previous status and expiry_date, expires now
	 * and STORES into database
	 *
	 * @param  UserTable  $user
	 * @param  string              $newStatus  Status to set: e.g. 'U': Upgraded: no user blocking or event fired
	 */
	abstract public function deactivate( &$user, $newStatus );
	/**
	 * reverts subscription state and expiry to previous values
	 *
	 * @param  UserTable  $user
	 * @param  string     $unifiedStatus  Payment/Subscription status ('PaidSubscription', 'Denied', 'RegistrationCancelled', NOT allowed here: 'Completed', 'Processed', 'Pending', 'In-Progress'
	 * @return string                     NULL: not applicable, 'I': Innexistant, 'R': Registered but not activated, 'A': Active, 'X': Expired  (all subscriptions in that chain, except for 'A')
	 */
	abstract public function revert( &$user, $unifiedStatus );
	/**
	 * Notification from gateway that the automated auto-renewal has been cancelled or failed
	 * Stores this new auto-recurring status with subscription so that subscription can be re-activated or renewed
	 *
	 * @param  UserTable  $user
	 * @param  string     $unifiedStatus
	 * @param  string     $event_type      type of subscription cancellation event (paypal type): 'subscr_cancel', 'subscr_failed'
	 */
	public function autorecurring_cancelled( &$user, $unifiedStatus, $event_type ) {
		// override if needed.
	}
	/**
	 * Notifies any IPN/PDT/status change
	 *
	 * @param  string                         $unifiedStatus          Payment/Subscription status ('Completed', 'Processed', 'Pending', 'In-Progress', 'Denied', 'RegistrationCancelled')
	 * @param  string                         $previousUnifiedStatus  Payment/Subscription status ('Completed', 'Processed', 'Pending', 'In-Progress', 'Denied', 'RegistrationCancelled')
	 * @param cbpaidPaymentBasket             $paymentBasket
	 * @param cbpaidPaymentNotification $notification           notification object of the payment
	 * @param  int                            $now
	 * @param  UserTable             $user
	 * @param string                          $eventType              type of event (paypal type): 'web_accept', 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed'
	 * @param string                          $paymentStatus          new status (Completed, RegistrationCancelled)
	 * @param int                             $occurrences            renewal occurrences
	 * @param int                             $autorecurring_type     0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param int                             $autorenew_type         0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 */
	public function notifyPaymentStatus( $unifiedStatus, $previousUnifiedStatus, &$paymentBasket,
										 /** @noinspection PhpUnusedParameterInspection */ &$notification,
										 /** @noinspection PhpUnusedParameterInspection */ $now,
										 &$user,
										 /** @noinspection PhpUnusedParameterInspection */ $eventType,
										 /** @noinspection PhpUnusedParameterInspection */ $paymentStatus,
										 /** @noinspection PhpUnusedParameterInspection */ $occurrences,
										 /** @noinspection PhpUnusedParameterInspection */ $autorecurring_type,
										 /** @noinspection PhpUnusedParameterInspection */ $autorenew_type )
	{
		if ( ( $unifiedStatus == 'Pending' ) && ! in_array( $previousUnifiedStatus, array( 'Pending', 'Completed', 'Processed' ) ) ) {
			$this->triggerIntegrations( $user, 'Pending', null, null, $this->_reason, ( $paymentBasket->recurring == 1 ) );
			$this->sendNewStatusEmail( $user, 'Pending', $this->_reason, ( $paymentBasket->recurring == 1 ) );
		}
	}

	/**
	 * Sends appropriate email depending on status.
	 * Should be called only once upon each change of this something,
	 *
	 * @param  UserTable  $user
	 * @param  string     $cause            'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied'
	 * @param  string     $reason           'N' new subscription, 'R' renewal, 'U'=update )
	 * @param int         $autorenewed      0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	public function sendNewStatusEmail( &$user, $cause, $reason, $autorenewed = 0 ) {
		global $_CB_framework;

		if ( ! is_object( $user ) ) {
			return;
		}

		$emailkind					=	null;
		if ( ( $this->status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason != 'R' ) ) {
			$emailkind				=	'thankyou';
		} elseif ( ( $this->status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason == 'R' ) && ( $autorenewed == 0 ) ) {
			$emailkind				=	'renewal';
		} elseif ( ( $this->status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason == 'R' ) && ( $autorenewed == 1 ) ) {
			$emailkind				=	'autorenewal';
		} elseif ( ( $this->status == 'X' ) && ( $cause == 'Denied' ) ) {
			$emailkind				=	'expiration';
		} elseif ( ( $this->status == 'C' ) && ( $cause == 'Denied' ) && ( $user->id == $_CB_framework->myId() ) && ( $_CB_framework->getUi() == 1 ) ) {
			$emailkind				=	'cancelled';	// by the user only in frontend
		} elseif ( ( $cause == 'Pending' ) && ( $reason != 'R' ) && ( $autorenewed == 0 ) ) {
			$emailkind				=	'pendingfirst';
		} elseif ( ( $cause == 'Pending' ) && ( $reason == 'R' ) && ( $autorenewed == 0 ) ) {
			$emailkind				=	'pendingrenewal';
		}
		if ( $emailkind ) {
			// email to user only if activated for the first time:
			$plan					=	$this->getPlan();
			if ( ! $plan ) {
				return;
			}

			cbimport( 'cb.tabs' );				// for cbNotification and comprofilerMail()
			cbimport( 'language.front' );		// for _UE_EMAILFOOTER translation

			$mailHtml				=	( $plan->get( $emailkind . 'emailhtml' ) == '1' ? 1 : 0 );
			$mailSubject			=	$this->getPersonalized( $emailkind . 'emailsubject', false, false );
			$mailBody				=	$this->getPersonalized( $emailkind . 'emailbody', $mailHtml );
			$mailCC					=	trim( $plan->get( $emailkind . 'emailcc' ) );
			$mailBCC				=	trim( $plan->get( $emailkind . 'emailbcc' ) );
			$mailAttachments		=	trim( $plan->get( $emailkind . 'emailattachments' ) );
			if ( $mailCC != '' ) {
				$mailCC				=	preg_split( '/ *, */', $mailCC );
			} else {
				$mailCC				=	null;
			}
			if ( $mailBCC != '' ) {
				$mailBCC			=	preg_split( '/ *, */', $mailBCC );
			} else {
				$mailBCC			=	null;
			}
			if ( $mailAttachments != '' ) {
				$mailAttachments	=	preg_split( '/ *, */', $mailAttachments );
			} else {
				$mailAttachments	=	null;
			}

			if ( $mailSubject || $mailBody ) {
				$notifier			=	new cbNotification();
				$notifier->sendFromSystem( $user, $mailSubject, $mailBody, true, $mailHtml, $mailCC, $mailBCC, $mailAttachments );
			}
		}			//TBD: 	else email in case of deactivation
	}
	/**
	 * Triggers onCPayUserStateChange integrations for activate, deactivate and revert events
	 * called by derived classes
	 *
	 * @param  UserTable  $user
	 * @param  string     $cause
	 * @param  int|null   $replacedPlanId
	 * @param  int|null   $replacedSubId
	 * @param  string     $reason           only for activate events
	 * @param  int        $autorenewed      0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 */
	public function triggerIntegrations( &$user, $cause, $replacedPlanId, /** @noinspection PhpUnusedParameterInspection */ $replacedSubId , $reason, $autorenewed = 0 ) {
		global $_CB_framework, $_PLUGINS;

		$now						=	$_CB_framework->now();
		$plan						=	$this->getPlan();
		if ( ! $plan ) {
			return;
		}
		$integrationParams			=&	$plan->getParams( 'integrations' );
		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
		$_PLUGINS->trigger( 'onCPayUserStateChange', array( &$user, $this->status, $this->plan_id, $replacedPlanId, &$integrationParams, $cause, $reason, $now, &$this, $autorenewed ) );
	}
	/**
	 * Get a personalized name or description for this subscription (used by email bodies).
	 *
	 * @param  string     $property                          Of plan: e.g. 'email...body' OR Text
	 * @param  boolean    $html                              TRUE: HTML output, FALSE: text output
	 * @param  boolean    $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @param  array|null $extraStrings                      If needed, more strings
	 * @param  boolean    $isPropretyAndNotText              (optional) TRUE: it's a plan proprety FALSE: $proprety is a string and not a proprety
	 * @return string
	 */
	public function getPersonalized( $property, $html, $runContentPluginsIfAllowedByPlan = true, $extraStrings = null, $isPropretyAndNotText = true ) {
		$substitutionStrings		=	$this->substitutionStrings( $html, $runContentPluginsIfAllowedByPlan );
		if ( $extraStrings ) {
			$substitutionStrings	=	array_merge( $substitutionStrings, $extraStrings );
		}

		return CBPTXT::replaceUserVars( $isPropretyAndNotText ? $this->getPlanAttribute( $property ) : $property, $this->user_id, $html, true, $runContentPluginsIfAllowedByPlan && $this->getPlanAttribute( 'runcontentplugins' ), $substitutionStrings );
	}
	/**
	 * Returns substitution strings for display substitutions
	 *
	 * @param  boolean  $html                              HTML or TEXT return
	 * @param  boolean  $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @return array
	 */
	public function substitutionStrings( $html, $runContentPluginsIfAllowedByPlan = true ) {
		$plan											=	$this->getPlan();
		$strings										=	$plan->substitutionStrings( $html, $this->user_id, $runContentPluginsIfAllowedByPlan );

		$reason											=	( $this->status == 'C' ? 'N' : 'R' );
		$strings['PLAN_PRICE']							=	$this->displayPeriodPrice( $reason, $html );

		$extraStrings									=	array();
		$vars											=	get_object_vars( $this );

		/** convert dates to nice display (Warning: due to PHP bug https://bugs.php.net/bug.php?id=66961 $vars can contain references to object's variables, do not touch directly!): */
		foreach ( $vars as $k => $v ) {
			if ( ( $k[0] == '_' ) || is_object( $v ) || is_array( $v ) ) {
				continue;
			}

			$extraStrings[$k]							=	$v;

			if ( is_string( $v ) && preg_match( '/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2} [0-1][0-9]:[0-9][0-9]:[0-9][0-9]$/', $v ) ) {
				$extraStrings[$k]						=	cbFormatDate( $v, 1, false );
			}
		}

		return array_merge( $strings, $extraStrings );
	}
	/**
	 * SUBSCRIPTION PRESENTATION METHODS:
	 */
	/**
	 * Get text for $this subscription
	 *
	 * @param  string   $textType ( 'name', 'alias', 'description', 'thankyoutextcompleted', 'thankyoutextpending', 'thankyouemailsubject', 'thankyouemailbody' )
	 * @return string
	 */
	public function getText( $textType ) {
		$thankYouText = null;

		if ( in_array( $textType, array( 'name', 'alias', 'description', 'thankyoutextcompleted', 'thankyoutextpending', 'thankyouemailsubject', 'thankyouemailbody' ) ) ) {
			$thankYouText = $this->getPlanAttribute( $textType );
		}
		return $thankYouText;
	}
	/**
	 * Get a correct display of the formatted validity of a plan: override if needed
	 *
	 * @return string                     Formatted text giving validity of this subscription
	 */
	public function getFormattedExpirationDateText() {
		if ( $this->status == 'A' ) {
			$params				=&	cbpaidApp::settingsParams();
			return CBPTXT::T( $params->get( 'regtextLifetime', 'Lifetime Subscription' ) );
		} else {
			return CBPTXT::T('Not active');
		}
	}
	/**
	 * Get an attribute of the plan of this subscription
	 *
	 * @param  string    $attribute    Name of a public attribute of plan
	 * @return mixed
	 */
	public function getPlanAttribute( $attribute ) {
		return $this->getPlan()->get( $attribute );
	}

	/**
	 * Get price value of the next payment to subscribe or renew this plan
	 *
	 * @param  string        $currency_code     Currency of value to return
	 * @param  int           $now               Unix time to expire
	 * @param  int           $quantity          Quantity
	 * @param  string        $reason            [optional] 'N' new subscription, 'R' renewal, 'U'=update
	 * @param  float|int     $remainingValue    Remaining value (in the plan's default currency) to deduct of the price
	 * @return boolean|float                    Price of next payment for this subscription in currency. Return FALSE if can't be purchased/subscribed.
	 */
	public function getPriceOfNextPayment( $currency_code, $now, $quantity = 1, $reason = 'N', $remainingValue = 0 ) {
		$occurrence		=	$this->getOccurrence();
		return ( $this->getPlan()->getPrice( $currency_code, $this, $reason, $occurrence, $now, $remainingValue, $quantity ) );
	}
	/**
	 * Get price value of the follow-up payments after the next payment to subscribe or renew this plan
	 *
	 * @param  string        $currency_code
	 * @param  int           $now         Unix time to expire
	 * @param  int           $quantity
	 * @param  string        $reason      [optional] 'N' new subscription, (NOT 'R' renewal), 'U'=update
	 * @return float                      price of next payment for this subscription in currency. Return FALSE if can't be purchased/subscribed.
	 */
	public function getPriceOfFollowUpPayments( $currency_code, $now, $quantity = 1, $reason = 'N' ) {
		$occurrence		=	$this->getOccurrence() + 1;
		// No remaining value since this is for all follouw-up payments:
		return ( $this->getPlan()->getPrice( $currency_code, $this, $reason, $occurrence, $now, 0, $quantity ) );
	}
	/**
	 * Check if $this Something is in a Basket which is in Pending payment state, but which is not $notBasketId
	 *
	 * @param  int  $notBasketId
	 * @return boolean
	 */
	public function hasPendingPayment( $notBasketId ) {
		$paymentBasket		=	new cbpaidPaymentBasket( $this->_db );
		$basketLoaded		=	$paymentBasket->loadLatestBasketOfUserPlanSubscription( $this->user_id, $this->plan_id, $this->id, 'Pending', $notBasketId );
		return $basketLoaded;
	}
	/**
	 * get the most recent payment basket for that user and plan, and with that subscription
	 *
	 * @param  string   $paymentStatus    NULL: search any kind, 'NotInitiated': search only not initiated baskets which is not to old, string: search for particular status.
	 * @return cbpaidPaymentItem  or false
	 */
	public function & loadLatestPaymentItem( $paymentStatus = null ) {
		global $_CB_database;

		$paymentItem	=	new cbpaidPaymentItem( $_CB_database );
		if ( $paymentItem->loadLatestPaymentItemOfUserPlanSubscription( $this->user_id, $this->plan_id, $this->id, $paymentStatus ) ) {
			return $paymentItem;
		} else {
			$false		=	false;
			return $false;
		}
	}
	/**
	 * Stop auto-recuring payments for $this Something subscription
	 *
	 * @return boolean|string  TRUE: Success, string: error message
	 */
	public function stopAutoRecurringPayments( ) {
		return false;		// Overridden in cbpaidUsersubscriptionRecord
	}
}	// class cbpaidSomething
