<?php
/**
 * @version $Id: cbpaidProduct.php 1608 2012-12-29 04:12:52Z beat $
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
 * Base class for a product
 */
abstract class cbpaidProduct extends cbpaidItem {
	//inherited:	public $id							=	null;
	//inherited:	public $item_type;
	public $name;
	public $alias;
	public $description;
	public $runcontentplugins;
	public $item_text;
	public $item_alias;
	public $published;
	public $allow_newsubscriptions		=	1;
	public $allow_registration			=	1;
	public $allow_upgrade_to_this		=	1;
	public $allow_frontend				=	1;
	public $propose_registration		=	1;
	public $propose_upgrade				=	1;
	public $auto_compute_upgrade		=	1;
	public $multiple;
	// This vars group is from cbpaidTimed and cbpaidItem:
	//inherited:	public $validity;
	public $calstart;
	//inherited:	public $bonustime;
	public $graceperiod;
	//inherited:	public $currency;
	//inherited:	public $rate;
	public $first_different;
	//inherited:	public $first_rate;
	public $first_calstart;
	//inherited:	public $first_validity;
	public $renewableinadvanceby;
	public $pricedisplay;
	public $prorate;
	public $renewal_start;
	//inherited:	public $autorecurring;
	//inherited:	public $recurring_max_times;
	public $action_at_last_recurring	=	0;
	public $access;
	public $default;
	public $hideregistrationfields;
	public $confirmed;
	public $approved;
	public $viewaccesslevel				=	1;
	public $usergroup;
	public $owner						=	0;
	public $thankyoutextcompleted;
	public $thankyoutextpending;
	public $thankyouemailsubject;
	public $thankyouemailbody;
	public $thankyouemailhtml;
	public $thankyouemailcc;
	public $thankyouemailbcc;
	public $thankyouemailattachments;
	public $renewalemailsubject;
	public $renewalemailbody;
	public $renewalemailhtml;
	public $renewalemailcc;
	public $renewalemailbcc;
	public $renewalemailattachments;
	public $expirationemailsubject;
	public $expirationemailbody;
	public $expirationemailhtml;
	public $expirationemailcc;
	public $expirationemailbcc;
	public $expirationemailattachments;
	public $cancelledemailsubject;
	public $cancelledemailbody;
	public $cancelledemailhtml;
	public $cancelledemailcc;
	public $cancelledemailbcc;
	public $cancelledemailattachments;
	public $parent;
	public $ordering;
	public $exclusive;
	public $hidechildren;
	public $firstloginurl;
	public $eachloginurl;
	public $template;
	public $cssclass;
	public $tax_rule_id;
	public $reg_conds;							// sql:tinyint(4)
	public $reg_conds_plans_required;			// sql:varchar(255)
	public $reg_conds_plans_not_required;		// sql:varchar(255)
	public $reg_conds_plans_disallowing;		// sql:varchar(255)
	public $reg_conds_fields_required;			// sql:varchar(255)
	public $reg_conds_fields_not_required;		// sql:varchar(255)
	public $reg_conds_fields_disallowing;		// sql:varchar(255)
	public $reg_conds_cbfield_1;				// sql:int(11)
	public $reg_conds_cbfield_1_operator;		// sql:varchar(7)
	public $reg_conds_value_1;					// sql:varchar(255)
	public $cond_1_operator;					// sql:varchar(7)
	public $cond_1_plans_required;				// sql:varchar(1024)
	public $cond_1_plans_status;				// sql:varchar(40)
	public $cond_1_purchase_ok;					// sql:tinyint(4)
	public $cond_1_date_1;						// sql:varchar(20)
	public $cond_1_date_cbfield_1;				// sql:int(11)
	public $cond_1_value_1;						// sql:varchar(1024)
	public $cond_1_dates_diff_a;				// sql:varchar(21)
	public $cond_1_dates_diff_b;				// sql:varchar(21)
	public $cond_1_date_2;						// sql:varchar(20)
	public $cond_1_date_cbfield_2;				// sql:int(11)
	public $cond_1_value_2;						// sql:varchar(1024)
	public $cond_2_operator;					// sql:varchar(7)
	public $cond_2_plans_required;				// sql:varchar(1024)
	public $cond_2_plans_status;				// sql:varchar(40)
	public $cond_2_purchase_ok;					// sql:tinyint(4)
	public $cond_2_date_1;						// sql:varchar(20)
	public $cond_2_date_cbfield_1;				// sql:int(11)
	public $cond_2_value_1;						// sql:varchar(1024)
	public $cond_2_dates_diff_a;				// sql:varchar(21)
	public $cond_2_dates_diff_b;				// sql:varchar(21)
	public $cond_2_date_2;						// sql:varchar(20)
	public $cond_2_date_cbfield_2;				// sql:int(11)
	public $cond_2_value_2;						// sql:varchar(1024)
	public $upgrade_conds;						// sql:tinyint(4)
	public $upgrade_conds_plans_required;		// sql:varchar(255)
	public $upgrade_conds_plans_disallowing;	// sql:varchar(255)
	public $integrations;
	public $params;
	/**
	 * Product viewer
	 * @var cbpaidProductView
	 */
	protected $_viewer;
	/**
	 * options choosen of the product at registration or upgrade time
	 * @var ParamsInterface
	 */
	public $_options;
	/**
	 * for edit/upgrade subscriptions view: array of array( plan_id, subscription_id, $discountedPrice ) which can be updated
	 *
	 * @var array
	 */
	public $_subscriptionToUpdate;
	public $_drawOnlyAsContainer;
	/**
	 * @var ParamsInterface
	 */
	public $_integrations;
	private $_overides	=	null;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_plans', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 * Get an attribute of this timed item
	 *
	 * @param  string  $column
	 * @param  string  $default
	 * @return mixed
	 */
	public function get( $column, $default = null ) {
		if ( isset( $this->_overides[$column] ) ) {
			return $this->_overides[$column];
		} else {
			if ( ! property_exists( $this, $column ) ) {
				trigger_error( 'cbpaidProduct::get ("' . htmlspecialchars( $column ) . '") innexistant attribute.', E_USER_ERROR );
			}

			return parent::get( $column, $default );
		}
	}
	/**
	 * Set the value of the attribute of this timed item
	 *
	 * @param  string  $column  Name of attribute
	 * @param  mixed   $value   Nalue to assign to attribute
	 */
	public function set( $column, $value ) {
//		if ( ! property_exists( $this, $column ) ) {
//			trigger_error( 'cbpaidProduct::set ("' . htmlspecialchars( $column ) . '") innexistant attribute.', E_USER_ERROR );
//		}
		parent::set( $column, $value );
	}
	/**
	 * Sets a temporary override of object attribute
	 *
	 * @param  string  $column  Name of attribute
	 * @param  mixed   $value   Nalue to assign to attribute
	 */
	public function setOverride( $column, $value ) {
		if ( $value !== null ) {
			$this->_overides[$column]	=	$value;
		} else {
			unset( $this->_overides[$column] );
		}
	}
	/**
	 * Returns pricedisplay setting if exists, otherwise NULL
	 *
	 * @return string|null
	 */
	protected function getPriceDisplay( ) {
		return $this->get( 'pricedisplay' );
	}
	/**
	 * Outputs the product's template CSS
	 *
	 * @return string  Template name
	 */
	public function getTemplateOutoutCss( ) {
		static $defaultTemplate		=	null;
		if ( ! $defaultTemplate ) {
			$defaultTemplate		=	cbpaidApp::settingsParams()->get( 'template', 'default' );
		}
		$template					=	$this->get( 'template' );
		if ( $template == '' ) {
			$template				=	$defaultTemplate;
		} elseif ( $template != $defaultTemplate ) {
			cbpaidApp::getBaseClass()->outputRegTemplate( $template );
		}
		return $template;
	}
	/**
	 * Gets the viewer class for the rendering, keeps it in cache.
	 *
	 * @return cbpaidProductView
	 */
	public function & getViewer( ) {
		if ( ! isset( $this->_viewer ) ) {
			$template		=	$this->getTemplateOutoutCss();
			$view			=	'product' . $this->item_type;
			$output			=	'html';			// For now...
			$this->_viewer	=	cbpaidTemplateHandler::getViewer( $template, $view, $output );
			$this->_viewer->setModel( $this );
		}
		return $this->_viewer;
	}
	/**
	 * loads the latest user subscription
	 *
	 * @param  int|null        $user_id    User id
	 * @param  string          $status     Status of subscription (null=any)
	 * @return cbpaidSomething             if found or null
	 */
	public function loadLatestSomethingOfUser( $user_id, $status = 'A' ) {
		$something			=	$this->newSubscription();
		if ( $something->loadLatestSomethingOfUser( $user_id, $status ) ) {
			return $something;
		}
		return null;
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
		$query = "SELECT COUNT(*)"
			. "\n FROM " . $this->_db->NameQuote( $this->_tbl )
			. "\n WHERE `parent` = ". (int) $this->$k
		;
		$this->_db->setQuery( $query );

		$obj = null;
		$count = $this->_db->loadResult($obj);
		if ( $count > 0 ) {
			$this->setError( CBPTXT::T("This plan has children plans and can not be deleted") );
			return false;
		}
		return true;		// override !
	}
	/**
	 * Checks if plan can be shown and subscribed to at registration time
	 *
	 * @return boolean
	 */
	public function isPlanAllowingNewSubscriptions() {
		return (   ( $this->get( 'published' ) == 1 )
			&& ( $this->get( 'allow_newsubscriptions' ) == 1 )
		);
	}
	/**
	 * Checks if plan can be shown and subscribed to at registration time
	 *
	 * Also implemented directly in SQL in cbpaidPlansMgr::loadPublishedPlans, but still calls this method
	 *
	 * @return boolean
	 */
	public function isPlanAllowingRegistration() {
		global $_CB_framework;

		return (     $this->isPlanAllowingNewSubscriptions()
			&& ( $this->get( 'allow_registration' ) == 1 )
			&& ( ( $_CB_framework->getUi() != 1 ) || ( $this->get( 'allow_frontend' ) == 1 ) )
		);
	}
	/**
	 * Checks if $this plan applies based on $userId 's existing subscriptions and CB fields conditions 1 and 2.
	 *
	 * @param  int      $userId   User id
	 * @param  array    $resultTexts  (returned appended)
	 * @return boolean
	 */
	protected function checkActiveConditions( $userId, &$resultTexts ) {
		$resultTexts					=	array();
		if ( $this->cond_1_operator ) {
			$r							=	cbpaidCondition::checkConditionsOfObject( $this, $userId, $resultTexts, null );
		} else {
			$r							=	true;
		}
		return $r;
	}
	/**
	 * Checks if plan can be shown and subscribed to when upgrading from another plan.
	 *
	 * @param  int      $userId   User id
	 * @param  array    $resultTexts  (returned appended)
	 * @return boolean
	 */
	public function isPlanAllowingUpgradesToThis( $userId, &$resultTexts ) {
		global $_CB_framework;

		return (     $this->isPlanAllowingNewSubscriptions()
			&& ( $this->get( 'allow_upgrade_to_this' ) == 1 )
			&& ( ( $_CB_framework->getUi() != 1 ) || ( ( $this->get( 'allow_frontend' ) == 1 ) && $this->checkActiveConditions( $userId, $resultTexts ) ) )
		);
	}
	/**
	 * Tells if plan is time limited and can expire
	 *
	 * @return boolean	True if product can expire, False if it can not
	 */
	public function isProductWithExpiration() {
		return false;		// override !
	}
	/**
	 * Checks if plan is renewable in advance at all by its params
	 *
	 * @return boolean	True if renewable at all
	 */
	public function isPlanRenewable() {
		return false;		// override !
	}
	/**
	 * Checks if the subscription is renewable given its current $expiryDate and a given $time
	 *
	 * @param  string   $expiryDate     SQL-formatted expiry date or NULL for non-expiring item
	 * @param  int|null $time           UNIX-formatted time (default null: now)
	 * @return boolean	                TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfRenewable( /** @noinspection PhpUnusedParameterInspection */ $expiryDate, $time = null ) {
		return false;		// override !
	}
	/**
	 * Gives upgrade possibilities for plans and discount in case of renewal.
	 * Only exclusive plans can be upgraded to other exclusive plans with the same father.
	 *
	 * @param  int             $ui              1: frontend user, 2: backend admin
	 * @param  UserTable       $user            user being displayed
	 * @param  cbpaidSomething $subscription    subscriptions being proposed for upgrade
	 * @param  float           $remainingValue  remaining value of the subscription (for prorate upgrades) in currency of this plan
	 * @param  float           $quantity        Quantity purchased
	 * @param  int             $now             unix time
	 * @return array
	 */
	public function upgradePlansPossibilities( /** @noinspection PhpUnusedParameterInspection */ $ui, UserTable $user, &$subscription, $remainingValue, $quantity, $now ) {
		$result			=	array();
		if ( $this->get( 'exclusive' ) == 1 ) {
			$plansMgr						=&	cbpaidPlansMgr::getInstance();
			$plans							=&	$plansMgr->loadPublishedPlans( $user, true, 'upgrade', null );		//TBD LATER: implement restriction on owner
			$remainingValueCurrencies		=	array( $this->currency()	=>	$remainingValue );
			foreach ( $plans as $p ) {
				if ( ( $p->get( 'exclusive' ) == 1 ) && ( $p->id != $this->id ) && ( $p->parent == $this->parent ) ) {
					if ( $remainingValue == 0 ) {
						$pCurRemVal			=	0;
					} else {
						if ( ! isset( $remainingValueCurrencies[$p->currency()] ) ) {
							$remainingValueCurrencies[$p->currency()]	=	$this->_priceConvert( $p->currency(), $remainingValue );
						}
						$pCurRemVal			=	$remainingValueCurrencies[$p->currency()];
					}

					$discountedPrice		=	$p->getPrice( null, $subscription, 'U', 0, $now, $pCurRemVal, $quantity );
					if ( ( $discountedPrice == false ) && ( $p->get( 'auto_compute_upgrade' ) == 0 ) ) {
						$discountedPrice	=	0;
					}
					if ( $discountedPrice !== false ) {
						$result[$p->id]		=	$discountedPrice;
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Creates a productSomething object of the appropriate class to the product
	 *
	 * @return cbpaidSomething     // cbpaidUsersubscriptionRecord
	 */
	abstract public function & newSubscription( );
	/**
	 * Creates a subscription object and computes price in the plan's currency
	 *
	 * @param  UserTable           $user
	 * @param  array               $postdata          $_POST array for the parameters of the subscription plans
	 * @param  string              $reason            payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  string              $status            subscription status: 'R'=registered (default) , 'I'=illegal, 'A'=active, etc.
	 * @param  array|null          $replacesSubId     array( planid, subid ) or NULL : In fact: the existing one in all cases, except if new to be created.
	 * @param  array|null          $existingSubId     array( planid, subid ) or NULL : In fact: the new one in case of upgrade !
	 * @param  int                 $subscriptionTime  Unix time
	 * @param  float|boolean|null  $price             RETURNED value: price in the plan's currency... return FALSE if can't be purchased/subscribed
	 * @param  float|boolean|null  $recurringPrice    RETURNED value: price of the next occurence in the plan's currency... return FALSE if can't be purchased/subscribed
	 * @param  array               $parentSubId       parent plan and subscription's id,   if this subscription depends of a parent subscription
	 * @return cbpaidSomething                        check FALSE for $price too.
	 */
	public function & createProductThing( $user, /** @noinspection PhpUnusedParameterInspection */ $postdata, $reason, $status, $replacesSubId, $existingSubId, $subscriptionTime, &$price, &$recurringPrice, $parentSubId  ) {
		$paidSomethingMgr			=&	cbpaidSomethingMgr::getInstance();
		if ( $reason == 'R' ) {
			// renew: $subscription loads $replacesSubscriptionId (must exist for a renewal):
			if ( $replacesSubId ) {
				$subscription		=	$paidSomethingMgr->loadSomething( $replacesSubId[0], $replacesSubId[1] );
				if ( ! $subscription ) {
					global $_CB_database;
					trigger_error( 'createProductThing::load subscription for renewal error:' . htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_NOTICE );
				}
			} else {
				trigger_error( 'createProductThing::renewal error: reason is R but replaceSub is undefined', E_USER_ERROR );
				exit();
			}
			$remainingValue			= 0;
		} else {
			// new or upgrade: create or load $subscription:
			$subscription			=	$this->newSubscription();
			$remainingValue			=	$subscription->createOrLoadReplacementSubscription( $user, $this, $replacesSubId, $existingSubId, $status, true, $subscriptionTime, $reason, $parentSubId );
		}
		// $price and $recurringPrice are returned by reference: do not remove:
		$occurrence					=	$subscription->getOccurrence();
		$price						=	$this->getPrice( $this->currency(), $subscription, $reason, $occurrence,     $subscriptionTime, $remainingValue, 1 );
		$recurringPrice				=	$this->getPrice( $this->currency(), $subscription, $reason, $occurrence + 1, $subscriptionTime, 0, 1 );
		if ( $this->auto_compute_upgrade == 0 ) {
			if ( $price === false ) {
				$price				=	0;
			}
		}
		return $subscription;
	}
	/**
	 * Returns remaining value of a subscription with this plan in the plan's currency
	 * at $time when it's expiring at $expiryDate
	 *
	 * @param  int                 $time          Unix-time
	 * @param  string              $expiryDate    SQL datetime
	 * @param  int                 $occurrence    = 0 : first occurrence, >= 1: next occurrences
	 * @param  boolean             $upgraded_sub  TRUE if the underlying subscription is an upgrade from another subscription
	 * @param  cbpaidSomething     $subscription  the subscription
	 * @return float                              value
	 */
	public function remainingPriceValue( /** @noinspection PhpUnusedParameterInspection */ $time, $expiryDate, $occurrence, $upgraded_sub, &$subscription ) {
		return 0;		// Override if needed
	}
	/**
	 * PRICING METHODS:
	 */
	/**
	 * returns price (rate) in the given currency.
	 *
	 * @param  string          $currency_code  ISO currency
	 * @param  float           $price          price to convert
	 * @return float|null                      returns $price in $currency_code or null if it can not convert.
	 */
	public function _priceConvert( $currency_code, $price ) {
		return cbpaidMoney::getInstance()->convertPrice( $price, $this->currency(), $currency_code, true, true );
	}

	/**
	 * Gives plan's price depending on a number of parameters
	 *
	 * @param  string           $currency_code   Currency of price needed
	 * @param  cbpaidSomething  $subscription    subscriptions being proposed for upgrade
	 * @param  string           $reason          Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int              $occurrence      = 0 : first occurrence, >= 1: next occurrences
	 * @param  int              $startTime       Unix time starting time of plan
	 * @param  float|int        $remainingValue  Remaining value of previous plan to deduct depending on settings
	 * @param  float|int        $quantity        Quantity purchased
	 * @return float|boolean                     Price if possible to determine, FALSE if can't be purchased/subscribed
	 */
	public function getPrice( $currency_code, $subscription, $reason = 'N', $occurrence = 0, $startTime = null, $remainingValue = 0, $quantity = 1 ) {
		global $_PLUGINS;

		$varRate				=	$this->getPlanVarName( $reason, $occurrence, 'rate' );
		$price					=	$this->get( $varRate );
		$_PLUGINS->trigger( 'onCPayBeforeGetProductPrice', array( $this, $subscription, $reason, $currency_code, &$price, $occurrence, $startTime, $remainingValue, $quantity ) );
		// First take quantity and remove remaining value of upgraded subscription, of any and configured to do so:
		if ( $price !== null ) {
			$price				=	$price * $quantity;
			if ( ( $reason == 'U' ) && ( $this->get( 'exclusive' ) == 1 ) && ( $this->get( 'prorate' ) == 1 ) && $remainingValue ) {
				$price			-=	$remainingValue;
				if ( $price < 0 ) {
					$price		=	false;
				}
			}
		} else {
			$price				=	false;
		}
		// Then convert:
		if ( $price !== false ) {
			$price					=	$this->_priceConvert( $currency_code, $price );
			if ( $price === null ) {
				$price				=	false;
			}
		}
		$_PLUGINS->trigger( 'onCPayAfterGetProductPrice', array( $this, $subscription, $reason, $currency_code, &$price, $occurrence, $startTime, $remainingValue, $quantity ) );
		return $price;
	}
	/**
	 * Fix variable name 'first_validity' to 'validity' if there is no different first period
	 * Overrides cbpaidTimed to take in account first_different of products
	 *
	 * @param  string  $varName   'first_validity' or 'validity'   !!! CHANGES (FIXES) THAT VAR NAME
	 */
	public function fixVarName( &$varName ) {
		if ( ( $varName === 'first_validity' ) && ( $this->get( 'first_different' ) == 0 ) ) {		//FIXME: Shouldn't this check for the event (registration or upgrade) ?
			$varName	=	'validity';
			trigger_error( 'first_validity passed while not a first_validity type of subscription !', E_USER_ERROR );		//FIXME: remove this function and all calls to it and inheritances.
			exit;
		}
	}
	/**
	 * Gives Calendar Year start
	 * OVERRIDES the base method from cbpaidTimed class
	 *
	 * @param  string  $varName      'first_validity' or 'validity'
	 * @return string                'month-day', e.g. '01-01'
	 */
	public function calendarYearStart( $varName ) {
		if ( $varName == 'first_validity' ) {
			$calstart	=	$this->first_calstart;
		} else {
			$calstart	=	$this->calstart;
		}
		if ( preg_match( '/^((0[13578]|1[02])-(0[1-9]|[12]\d|3[01])|(0[469]|11)-(0[1-9]|[12]\d|30)|02-(0[1-9]|[12]\d))$/', $calstart ) ) {
			return $calstart;
		} else {
			return '01-01';
		}
	}
	/**
	 * says if validity is unlimitted
	 * OVERRIDES the base method from cbpaidTimed class
	 *
	 * @return boolean	             true if lifetime, false if limitted time
	 */
	public function isLifetimeValidity() {
		return ( ( $this->get( 'first_different' ) == 0 ) && ( $this->get( 'validity' ) == '0000-00-00 00:00:00' ) );
	}
	/**
	 * says if plan is free
	 *
	 * @return boolean	             true if free, false if not entirely free
	 */
	public function isFree() {
		return ( ( ( $this->get( 'first_different' ) == 0 ) || ( $this->get( 'first_rate' ) == 0 ) ) && ( $this->get( 'rate' ) == 0 ) );

	}
	/**
	 * Gets the prefix before 'rate' and 'validity' for this plan depending on reason and occurrences
	 *
	 * @param  string        $reason      Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int           $occurrence  = 0 : first occurrence, >= 1: next occurrences
	 * @param  string        $variable    Name of main recurring variable ( 'rate' or 'validity' )
	 * @return string                     'rate' or 'first_rate' or 'validity' or 'first_validity'
	 */
	public function getPlanVarName( $reason, $occurrence, $variable ) {
		$varRate		=	'';
		if ( $occurrence == 0 ) {
			switch ( $reason ) {
				case 'N':
					if ( in_array( $this->get( 'first_different' ), array( 1, 3 ) ) ) {
						$varRate	=	'first_';
					}
					break;
				case 'R':
					break;
				case 'U':
					if ( in_array( $this->get( 'first_different' ), array( 2, 3 ) ) ) {
						$varRate	=	'first_';
					}
					break;
				default:
					trigger_error( 'Product::getRatePrefix: uknown reason: ' . $reason, E_USER_NOTICE );
					break;
			}
		}
		return $varRate . $variable;
	}
	/**
	 * Returns text for button for upgrade, renewals, etc.
	 *
	 * @param  string  $type  'upgrade', 'pay', 'renew', 'reactivate', 'resubscribe', 'unsubscribe', 'delete', default is Apply
	 * @return string         translated button text (without htmlspecialchars, it will be applied on the returned text.
	 */
	public function buttonText( $type ) {
		switch ( $type ) {
			case 'upgrade':
				return CBPTXT::T("Upgrade");
			case 'pay':
				return CBPTXT::T("Pay Now");
			case 'renew':
				return CBPTXT::T("Renew Now");
			case 'reactivate':
				return CBPTXT::T("Reactivate");
			case 'resubscribe':
				return CBPTXT::T("Resubscribe");
			case 'unsubscribe':
				return CBPTXT::T("Unsubscribe");
			case 'delete':
				return CBPTXT::T("Delete");
			default:
				return CBPTXT::T("Apply");
		}
	}
	/**
	 * Checks all subscriptions of this plan for mass-expiries
	 *
	 * @param  int           $limit   limit of number of users to expire ( 0 = no limit )
	 * @return int                    Count of subscriptions expired for this plan.
	 */
	public function checkAllSubscriptions( /** @noinspection PhpUnusedParameterInspection */ $limit ) {
		// override !
		return 0;
	}
	/**
	 * Checks all subscriptions of this plan for mass-expiries
	 *
	 * @return float                  Total turnover in the currency of the plan
	 */
	public function getTurnover() {
		// override !
		return 0.0;
	}
	/**
	 * Transforms a PHP CB |*|-delimited string into a Javascript array of int string
	 *
	 * @param  string  $str  CB string a|*|b
	 * @return string        Javascript string [a,b]
	 */
	private function _cbArStrToJsArr( $str ) {
		if ( $str ) {
			$arr		=	explode( '|*|', $str );
			cbArrayToInts( $arr );
			return '[' . implode( ',', $arr ) . ']';
		} else {
			return '[]';
		}
	}
	/**
	 * Outputs Javascript code needed to help plan conditions at registration and upgrades
	 *
	 * @param  string  $reason  payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 */
	public function addJsCodeIfNeeded( $reason ) {
		global $_CB_framework;

		if ( $this->reg_conds && ( $reason == 'N' ) ) {
			$reg_conds_plans_required		=	$this->_cbArStrToJsArr( $this->reg_conds_plans_required );
			$reg_conds_plans_disallowing	=	$this->_cbArStrToJsArr( $this->reg_conds_plans_not_required );
			$reg_conds_fields_required		=	$this->_cbArStrToJsArr( $this->reg_conds_fields_required );
			$reg_conds_fields_disallowing	=	$this->_cbArStrToJsArr( $this->reg_conds_fields_not_required );

			$reg_conds_cbfield_1			=	(int) $this->reg_conds_cbfield_1;
			$reg_conds_value_1				=	$this->reg_conds_value_1;
			if ( $reg_conds_cbfield_1 === '' ) {
				$regexp						=	'';
			} else {
				$pregStr					=	preg_quote( $reg_conds_value_1, '/' );
				switch ( $this->reg_conds_cbfield_1_operator ) {
					case '=':
						$regexp				=	'/^' . $pregStr . '$/';
						break;
					case '!=':
						$regexp				=	'/^(?!' . $pregStr . ')$/';
						break;
					case '<':
						$regexp				=	'<' . $reg_conds_value_1;
						break;
					case '>':
						$regexp				=	'<' . $reg_conds_value_1;
						break;
					case 'E':
						$regexp				=	'/' . $pregStr . '/';
						break;
					case '!E':
						$regexp				=	'/(?!' . $pregStr . ')/';
						break;
					case 'regexp':
						$regexp				=	$reg_conds_value_1;
						break;
					case '!regexp':
						$regexp				=	preg_replace( '/^\\/(.*)\\/(.*)$/', '/^(?!\\1)$/\\2', $reg_conds_value_1 );
						break;
					case '':
					default:
						$regexp				=	'';
						break;
				}
			}
		}
		elseif ( $this->upgrade_conds && ( $reason == 'U' ) ) {
			$reg_conds_plans_required		=	$this->_cbArStrToJsArr( $this->upgrade_conds_plans_required );
			$reg_conds_plans_disallowing	=	$this->_cbArStrToJsArr( $this->upgrade_conds_plans_disallowing );
			$reg_conds_fields_required		=	'[]';
			$reg_conds_fields_disallowing	=	'[]';
			$reg_conds_cbfield_1			=	0;
			$regexp							=	'';
		} else {
			return;
		}

		$js		=	'$.cbpaidsubs.paidsubsPlanConditions(' . ( (int) $this->id ) . ',' . $reg_conds_plans_required . ',' . $reg_conds_plans_disallowing . ',' . $reg_conds_fields_required . ',' . $reg_conds_fields_disallowing . ',' . ( (int) $reg_conds_cbfield_1 ) . ",'" . addslashes( $regexp ) . "');";
		$_CB_framework->outputCbJQuery( $js );	// , 'cbpaidsubscriptions' );
	}
	/**
	 * Checks if that there is at least a string of $needles in the array $arr
	 *
	 * @param  array  $needles
	 * @param  array  $arr
	 * @return boolean
	 */
	private static function _anyArrInArr( $needles, $arr ) {
		foreach ( $needles as $n ) {
			if ( in_array( $n, $arr ) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Explodes a CB-encoded string as an array
	 * @param  string    $string  string "xxx|*|yyy"
	 * @return string[]           array( 'xxx', 'yyy' )
	 */
	private static function _cbexplode( $string ) {
		if ( $string ) {
			return explode( '|*|', $string );
		} else {
			return array();
		}
	}
	/**
	 * Gets value of field $fieldId from $cbUser record
	 *
	 * @param  CBuser  $cbUser
	 * @param  int     $fieldId
	 * @return mixed
	 */
	private static function _getFieldValue( $cbUser, $fieldId ) {
		$user			=	$cbUser->getUserData();
		if ( $user && isset( $user->id ) ) {
			return cbpaidUserExtension::getInstance( $user->id )->getFieldValue( $fieldId, true );
		} else {
			return null;
		}
	}
	/**
	 * Checks that all field ids of $arr are set in $cbUser
	 *
	 * @param  CBuser          $cbUser
	 * @param  int[]|string[]  $arr
	 * @return boolean
	 */
	private static function _allFieldsInArr( $cbUser, $arr ) {
		foreach ( $arr  as $fieldId ) {
			$fieldValue		=	self::_getFieldValue( $cbUser, $fieldId );
			if ( ! $fieldValue ) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Checks that at least one field id of $arr is set in $cbUser
	 *
	 * @param  CBuser          $cbUser
	 * @param  int[]|string[]  $arr
	 * @return boolean
	 */
	private static function _anyFieldsInArr( $cbUser, $arr ) {
		foreach ( $arr  as $fieldId ) {
			$fieldValue		=	self::_getFieldValue( $cbUser, $fieldId );
			if ( $fieldValue ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Checks if $this plan matches conditions for $reason
	 *
	 * @param  UserTable  $user             reflecting the user being registered
	 * @param  string     $reason           payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  array      $selectedPlanIds
	 * @return boolean
	 */
	public function checkActivateConditions( $user, $reason, $selectedPlanIds ) {
		global $_POST;

		if ( $this->upgrade_conds && ( $reason == 'U' ) ) {

			if ( $this->upgrade_conds_plans_required && ! self::_anyArrInArr( $selectedPlanIds, self::_cbexplode( $this->upgrade_conds_plans_required ) ) ) {
				// none of required plans is selected simultaneously:
				return false;
			}
			if ( $this->upgrade_conds_plans_disallowing && self::_anyArrInArr( $selectedPlanIds, self::_cbexplode( $this->upgrade_conds_plans_disallowing ) ) ) {
				// any of the plans that are not allowed is selected simultaneously:
				return false;
			}

		} elseif ( $this->reg_conds && ( $reason == 'N' ) ) {

			if ( $this->reg_conds_plans_required && ! self::_anyArrInArr( $selectedPlanIds, self::_cbexplode( $this->reg_conds_plans_required ) ) ) {
				// none of required plans is selected simultaneously:
				return false;
			}
			if ( $this->reg_conds_plans_not_required && self::_anyArrInArr( $selectedPlanIds, self::_cbexplode( $this->reg_conds_plans_not_required ) ) ) {
				// any of the plans that are not allowed is selected simultaneously:
				return false;
			}

			if ( $user ) {
				// display of registration form does not pass $user. But no need to check fields there.
				if ( $user->id ) {
					$cbUser		=	CBuser::getInstance( $user->id );
				} else {
					$cbUser		=	new CBuser();
					$cbUser->loadCbRow( $user );
				}
				if ( $this->reg_conds_fields_required && ! self::_allFieldsInArr( $cbUser, self::_cbexplode( $this->reg_conds_fields_required ) ) ) {
					// not all of required fields are filled-in:
					return false;
				}
				if ( $this->reg_conds_fields_not_required && self::_anyFieldsInArr( $cbUser, self::_cbexplode( $this->reg_conds_fields_not_required ) ) ) {
					// any field that needed to remain empty is not:
					return false;
				}

				$reg_conds_value_1				=	$this->reg_conds_value_1;
				if ( $this->reg_conds_cbfield_1 ) {
					$fieldValue					=	self::_getFieldValue( $cbUser, (int) $this->reg_conds_cbfield_1 );
					$pregStr					=	preg_quote( $reg_conds_value_1, '/' );
					switch ( $this->reg_conds_cbfield_1_operator ) {
						case '=':
							$regexp				=	'/^' . $pregStr . '$/';
							break;
						case '!=':
							$regexp				=	'/^(?!' . $pregStr . ')$/';
							break;
						case '<':
							if ( ( $fieldValue === '' ) || ( $fieldValue === null ) ) {
								return false;
							}
							if ( ! ( $fieldValue < $reg_conds_value_1 ) ) {
								return false;
							}
							$regexp				=	false;
							break;
						case '>':
							if ( ( $fieldValue === '' ) || ( $fieldValue === null ) ) {
								return false;
							}
							if ( ! ( $fieldValue > $reg_conds_value_1 ) ) {
								return false;
							}
							$regexp				=	false;
							break;
						case 'E':
							$regexp				=	'/' . $pregStr . '/';
							break;
						case '!E':
							$regexp				=	'/(?!' . $pregStr . ')/';
							break;
						case 'regexp':
							$regexp				=	$reg_conds_value_1;
							break;
						case '!regexp':
							$regexp				=	preg_replace( '/^\\/(.*)\\/(.*)$/', '/^(?!\\1)$/\\2', $reg_conds_value_1 );
							break;
						case '':
						default:
							$regexp				=	null;
							break;
					}
					if ( $regexp ) {
						if ( ! preg_match( $regexp, $fieldValue ) ) {
							return false;
						}
					}
				}
			}
		}
		return true;
	}
	/**
	 * Returns substitution strings for display substitutions
	 *
	 * @param  boolean  $html                              HTML or TEXT return
	 * @param  int      $user_id                           User id for whom this product is displayed
	 * @param  boolean  $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @return array
	 */
	public function substitutionStrings( $html, $user_id, $runContentPluginsIfAllowedByPlan = true ) {
		global $_CB_framework;

		$strings										=	array();
		$strings['PLAN_NAME']							=	$html ? CBPTXT::Th( $this->name )		 : strip_tags( CBPTXT::T( $this->name ) );
		$strings['PLAN_ALIAS']							=	$html ? CBPTXT::Th( $this->alias )		 : strip_tags( CBPTXT::T( $this->alias ) );
		$strings['PLAN_DESCRIPTION']					=	$html ? CBPTXT::Th( $this->description ) : strip_tags( CBPTXT::T( $this->description ) );
		$strings['PLAN_REGISTRATION_PRICE']				=	$this->displayPeriodPrice( 'N', 0, null, null, $html, true, true );
		$strings['PLAN_UPGRADE_PRICE']					=	$this->displayPeriodPrice( 'U', 0, null, null, $html, true, true );
		$strings['PLAN_PRICE']							=	$_CB_framework->myId() ? $strings['PLAN_UPGRADE_PRICE'] : $strings['PLAN_REGISTRATION_PRICE'];
		$strings['PLAN_RATE']							=	sprintf( '%.2f', $this->rate );
		$strings['PLAN_FIRST_RATE']						=	sprintf( '%.2f', ( $this->first_different ? $this->first_rate : $this->rate ) );

		$strings['PLAN_NAME']							=	CBPTXT::replaceUserVars( $strings['PLAN_NAME'],		   $user_id, $html, false, $this->get( 'runcontentplugins' ) && $runContentPluginsIfAllowedByPlan );
		$strings['PLAN_DESCRIPTION']					=	CBPTXT::replaceUserVars( $strings['PLAN_DESCRIPTION'], $user_id, $html, false, $this->get( 'runcontentplugins' ) && $runContentPluginsIfAllowedByPlan );

		return $strings;
	}
	/**
	 * Get a personalized name or description for this product.
	 *
	 * @param  string     $property                          Of subscription's plan: e.g. '...emailbody' OR Text
	 * @param  int        $user_id                           User id for whom this product is displayed
	 * @param  boolean    $html                              TRUE: HTML output, FALSE: text output
	 * @param  boolean    $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @param  array|null $extraStrings                      If needed, more strings
	 * @param  boolean    $isPropretyAndNotText              (optional) TRUE: it's a plan proprety FALSE: $proprety is a string and not a proprety
	 * @return string
	 */
	public function getPersonalized( $property, $user_id, $html, $runContentPluginsIfAllowedByPlan = true, $extraStrings = null, $isPropretyAndNotText = true ) {
		$substitutionStrings		=	$this->substitutionStrings( $html, $user_id, $runContentPluginsIfAllowedByPlan );
		if ( $extraStrings ) {
			$substitutionStrings	=	array_merge( $substitutionStrings, $extraStrings );
		}
		return CBPTXT::replaceUserVars( $isPropretyAndNotText ? $this->get( $property ) : $property, $user_id, $html, true, $runContentPluginsIfAllowedByPlan && $this->get( 'runcontentplugins' ), $substitutionStrings );
	}

	/**
	 * BACKEND-ONLY XML RENDERING METHODS:
	 */

	/**
	 * USED by XML interface ONLY !!! Renders plan rate (either first or recurring one)
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderRate( $value, /** @noinspection PhpUnusedParameterInspection */ &$params ) {
		if ( ( $value === null ) || ( $value === '' ) || ( $value < 0 ) ) {
			$html		=	'-';
		} else {
			$temp		=	$this->get( 'rate' );
			$this->set( 'rate', $value );
			$currency	=	null;
			$html		=	$this->renderPrice( $value, $currency, true );
			$this->set( 'rate', $temp );
		}
		return $html;
	}
	/**
	 * USED by XML interface ONLY !!! Renders plan validity
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderValidity( /** @noinspection PhpUnusedParameterInspection */ $value, &$params ) {
		$html		=	'-';		// override
		return $html;
	}
	/**
	 * USED by XML interface ONLY !!! Renders class Name for the hierarchy
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderNameOfIndentCssClassIfHasParent( /** @noinspection PhpUnusedParameterInspection */ $value, &$params ) {
		if ( $this->parent ) {
			$cssClass		=	'cbpaidAdminParentLevel1';
		} else {
			$cssClass		=	'cbpaidAdminParentLevel0';
		}
		return $cssClass;
	}
	/**
	 * USED by XML interface ONLY !!! Renders url for the product
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderUrlOfProduct( /** @noinspection PhpUnusedParameterInspection */ $value, &$params ) {
		global $_CB_framework;

		$url				=	'index.php?option=com_comprofiler&amp;task=pluginclass&amp;plugin=cbpaidsubscriptions&amp;do=displayplans&amp;plans=' . (int) $this->get( 'id' );
		return '<a href="' . $_CB_framework->getCfg( 'live_site' ) . '/' . $url . '" target="_blank">' . $url . '</a>';
	}
}	// class cbpaidProduct

/**
 * Product upgrades class
 *
 *
	class cbpaidProductUpgrades extends cbpaidProduct {
	public $id;
	public $from_plan;
	public $to_plan;
	public $kind;
	public $description;
	public $published;
	/** if takes in account old value
 * @var boolean *
	public $prorate;
	public $discount;
	public $currency;
	public $upgradefee;

	public $access;
	public $ordering;
	public $cssclass;
	public $conditions;	//???
	public $params;
	/**
 * parameters of the product by column name
 * @access private
 * @var ParamsInterface[]
 *
	protected $_params;
	/**
 * options choosen of the product at registration or upgrade time
 * @access private
 * @var ParamsInterface
 *
	protected $_options;
	/**
 * Constructor
 *
 * @param  CBdatabase  $db
 *
	public function _contruct( &$db = null ) {
	$this->cbpaidProduct( $db );
	}
}
*/