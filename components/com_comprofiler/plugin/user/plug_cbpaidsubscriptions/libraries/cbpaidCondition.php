<?php
/**
* @version $Id: cbpaidCondition.php 1541 2012-11-23 22:21:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
* Conditions handling class
*/
class cbpaidCondition {
	public $plans_required;		// sql:varchar(1024)
	public $plans_status;			// sql:varchar(40)
	public $purchase_ok;			// sql:tinyint(4)
	public $date_1;				// sql:varchar(20)
	public $date_cbfield_1;		// sql:int(11)
	public $value_1;				// sql:varchar(1024)
	public $dates_diff_a;			// sql:varchar(21)
	public $dates_diff_b;			// sql:varchar(21)
	public $date_2;				// sql:varchar(20)
	public $date_cbfield_2;		// sql:int(11)
	public $value_2;				// sql:varchar(1024)
	/**
	 * Get list of plan_id of all items purchased simultaneously
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return array of int planids array( plan_id => quantity )
	 */
	public static function getPlansQuantityofBasket( $paymentBasket ) {
		$sameTimePurchasingPlansIds						=	array();
		foreach ( $paymentBasket->loadPaymentItems() as $item ) {
			if ( ! isset( $sameTimePurchasingPlansIds[$item->plan_id] ) ) {
				$sameTimePurchasingPlansIds[$item->plan_id]		=	0;
			}
			$sameTimePurchasingPlansIds[$item->plan_id]			+=	$item->quantity;
		}
		return $sameTimePurchasingPlansIds;
	}
	/**
	 * Checks if $this promotion applies based on $plansIds purchased same time that must ALL be purchased simultaneously
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return boolean
	 */
	private function checkIfBoughtSameTime( $paymentBasket ) {
		$plansIds			=	self::getPlansQuantityofBasket( $paymentBasket );
		foreach ( $this->_getArray( $this->plans_required ) as $planIdRequired ) {
			if ( isset( $plansIds[$planIdRequired] ) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * utility function checking if ANY of the required plans in $requiredIdsString are being in $activePlansIds
	 * 
	 * @param  array  $activePlansIds  of int
	 * @param  string $requiredIdsString  (array imploded with '|*|')
	 * @return boolean
	 */
	private static function _checkAny( $activePlansIds, $requiredIdsString ) {
		if ( $requiredIdsString ) {
			foreach ( explode( '|*|', $requiredIdsString ) as $activePlanRequired ) {
				if ( isset( $activePlansIds[$activePlanRequired] ) ) {
					return true;
				}
			}
			return false;
		}
		return true;
	}
	/**
	 * Gets all subscriptions of $user for $plans having $statuses
	 * $plans and $statuses can be null or empty array() meaning that condition is ignored
	 * 
	 * @param  int                   $userId
	 * @param  int[]|null     $plans
	 * @param  string[]|null  $statuses
	 * @return cbpaidSomething[]
	 */
	private function _getUsersSubscriptions( $userId, $plans, $statuses ) {
		$subsOfPlansStatus						=	array();
		if ( $userId ) {
			// get list of plan_id of all active and inactive subscriptions:
			$user								=	CBuser::getUserDataInstance( $userId );
			$subsByPlanType						=	cbpaidSomethingMgr::getAllSomethingOfUser( $user, null );
			foreach ( $subsByPlanType as $subs ) {
				foreach ( $subs as $subscription ) {
					// $subscription = NEW cbpaidSomething();
					if ( ( ( $plans == null )    || in_array( $subscription->plan_id, $plans ) )
					&&   ( ( $statuses == null ) || in_array( $subscription->status, $statuses ) ) ) {
						$subsOfPlansStatus[]	=	$subscription;
					}
				}
			}
		}
		return $subsOfPlansStatus;
	}
	/**
	 * Returns array depending on CBSubs array storage in $string
	 * 
	 * @param  string  $string
	 * @return array of string
	 */
	private function _getArray( $string ) {
		if ( $string === '' || $string === null ) {
			return array();
		} else {
			return explode( '|*|', $string );
		}
	}
	/**
	 * Gets/Computes data values
	 *
	 * @param  int                $userId
	 * @param  cbpaidSomething[]  $subscriptions
	 * @param  string             $dateType
	 * @param  int                $cbFieldId
	 * @param  string             $value
	 * @return array
	 */
	private function getDateValues( $userId, $subscriptions, $dateType, $cbFieldId, $value ) {
		switch ( $dateType ) {
			case 'now':
				$values				=	array( date( 'Y-m-d H:i:s' ) );
				break;
			case 'cbfield':
				$values				=	cbpaidUserExtension::getInstance( $userId )->getFieldValue( $cbFieldId, true );
				if ( ! is_array( $values ) ) {
					// Not multi-valued field:
					$values			=	array( $values );
				}
				break;
			case 'value':		// (incl. CB substitutions):
				$cbUser				=	CBuser::getInstance( $userId );
				if ( $cbUser ) {
					$extraStrings	=	null;
					$values			=	array( $cbUser->replaceUserVars( $value, false, true, $extraStrings, false ) );
				} else {
					$values			=	array( '' );
				}
				break;
			case 'subscription_date':
			case 'last_renewed_date':
			case 'expiry_date':
			case 'payment_date':
				$values				=	array();
				foreach ( $subscriptions as $sub ) {
					if ( isset( $sub->$dateType ) ) {
						$values[]	=	$sub->$dateType;
					}
				}
				break;

			default:
				$values				=	array();
			break;
		}
		return $values;
	}
	/**
	 * Adds/Substracts a $duration to/from $date and returns it in Unix time
	 *
	 * @param  string  $duration  Duration of the form '-0000-01-00 00:00:00'
	 * @param  string  $date      Date of the form '2012-03-04 05:06:07'
	 * @return int                Unix time
	 */
	private function _timeAddSubDurationToDate( $duration, $date ) {
		global $_CB_framework;

		list($sign, $y, $c, $d, $h, $m, $s)		=	sscanf($duration, '%1s%d-%d-%d %d:%d:%d');

		if ( $sign == '-' ) {
			$y = -$y; $c = -$c; $d = -$d; $h = -$h; $m = -$m; $s = -$s;
		}

		$startTime		=	cbpaidTimes::getInstance()->strToTime( $date );
		$offset			=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$localStartTime	=	$startTime + $offset;
		$expiryTime		=	mktime(date('H', $localStartTime)+$h, date('i', $localStartTime)+$m, date('s', $localStartTime)+$s, 
								   date('m', $localStartTime)+$c, date('d', $localStartTime)+$d, date('Y', $localStartTime)+$y);
		$expiryTime		=	$expiryTime - $offset;
		return $expiryTime;
	}
	/**
	 * Compares $v1 to $v2 using $diffOperator and of $diffOperator is a duration-date (e.g. '-0000-00-01 00:00:00'), $diffLarger will give the underlying operator
	 * @param  string  $v1            Value 1
	 * @param  string  $v2            Value 2
	 * @param  string  $diffOperator  =,<,>,!=,E,!E,regexp,!regexp,birthday,+0000-00-01 00:00:00
	 * @param  string  $diffLarger
	 * @return boolean
	 */
	private function _compareValuesWithOperator( $v1, $v2, $diffOperator, $diffLarger ) {
		global $_CB_database;

		switch ( $diffOperator ) {
			case '=':
				return ( $v1 == $v2 );
				break;
			case '<':
				return ( $v1 < $v2 );
				break;
			case '>':
				return ( $v1 > $v2 );
				break;
			case '!=':
				return ( $v1 != $v2 );
				break;
			case 'E':
				return ( stripos( $v1, $v2 ) !== false );
				break;
			case '!E':
				return ( stripos( $v1, $v2 ) === false );
				break;
			case 'regexp':
				return preg_match( $v1, $v2 );
				break;
			case '!regexp':
				return ! preg_match( $v1, $v2 );
				break;
			case 'birthday':
				return ( ( strlen( $v1 ) >= 10 ) && ( strlen( $v2 ) >= 10 ) )
						&& ( substr( $v1, 5, 5 ) == substr( $v2, 5, 5 ) )		// same month and day
						&& ( substr( $v1, 0, 4 ) != substr( $v2, 0, 4 ) );		// but not same year
				break;
			default:
				if ( strlen($diffOperator ) == 20 ) {
					//TODO make it work for dates < 1970 !!!! AND for DATE without TIME
					if ( ( strlen( $v1 ) == 19 ) && ( $v1 != $_CB_database->getNullDate() ) ) {
						$t1		=	cbpaidTimes::getInstance()->strToTime( $v1 );
					} else {
						$t1		=	0;
					}
					if ( ( strlen( $v2 ) == 19 ) && ( $v2 != $_CB_database->getNullDate() ) ) {
						$t2		=	$this->_timeAddSubDurationToDate( $diffOperator, $v2 );
					} else {
						$t2		=	0;
					}
					return ( $diffLarger ? ( $t1 >= $t2 ) : ( $t1 <= $t2 ) );
				} else {
					// no condition means this condition is OK:
					return true;
				}
				break;
		}
	}
	/**
	 * Compares $v1 to $v2 using $this->dates_diff_a and $this->dates_diff_b comparison operators
	 * @param  string  $v1
	 * @param  string  $v2
	 * @return boolean
	 */
	private function _compareValues( $v1, $v2 ) {
		return	$this->_compareValuesWithOperator( $v1, $v2, $this->dates_diff_a, true )
		&&		( ( ! in_array( substr( $this->dates_diff_a , 0, 1 ), array( '+', '-' ) ) ) || $this->_compareValuesWithOperator( $v1, $v2, $this->dates_diff_b, false ) );
	}
	/**
	 * Checks date/CBfield/values with CB substitutions comparison function
	 * 
	 * @param  int                $userId
	 * @param  cbpaidSomething[]  $subscriptions
	 * @return boolean
	 */
	private function checkDatesFieldsValues( $userId, $subscriptions ) {
		if ( $this->date_1 == '' ) {
			return true;
		}
		$values1	=	$this->getDateValues( $userId, $subscriptions, $this->date_1, $this->date_cbfield_1, $this->value_1 );
		$values2	=	$this->getDateValues( $userId, $subscriptions, $this->date_2, $this->date_cbfield_2, $this->value_2 );
		foreach ( $values1 as $v1 ) {
			foreach ( $values2  as $v2 ) {
				if ( $this->_compareValues( $v1, $v2 ) ) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Checks if conditions do apply to user
	 * 
	 * @param  int                       $userId
	 * @param  cbpaidPaymentBasket|null  $paymentBasket (optional
	 * @return boolean
	 */
	public function checkCondition( $userId, $paymentBasket = null ) {
		if ( $this->plans_required ) {
			$subscriptions		=	$this->_getUsersSubscriptions( $userId, $this->_getArray( $this->plans_required ), $this->_getArray( $this->plans_status ) );
			$subsStateOk		=	( count( $subscriptions ) > 0 );
			if ( ( ! $subsStateOk ) && $paymentBasket && $this->purchase_ok ) {
				$subsStateOk	=	$this->checkIfBoughtSameTime( $paymentBasket );
			}
		} else {
			$subscriptions		=	array();
			$subsStateOk		=	true;
		}
		if ( $subsStateOk ) {
			$subsStateOk		=	$this->checkDatesFieldsValues( $userId, $subscriptions );
		}
		return $subsStateOk;
	}
	/**
	 * Checks if $this promotion applies based on $userId 's existing subscriptions and CB fields conditions 1 and 2. 
	 *
	 * @param  object                    $obj
	 * @param  int                       $userId
	 * @param  array                     $resultTexts  (returned appended)
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @return boolean
	 */
	public static function checkConditionsOfObject( $obj, $userId, &$resultTexts, $paymentBasket ) {
		if ( $obj->cond_1_operator ) {
			$cond1						=	new cbpaidCondition();
			$cond1->plans_required		=	$obj->cond_1_plans_required;
			$cond1->plans_status		=	$obj->cond_1_plans_status;
			$cond1->purchase_ok			=	$obj->cond_1_purchase_ok;
			$cond1->date_1				=	$obj->cond_1_date_1;
			$cond1->date_cbfield_1		=	$obj->cond_1_date_cbfield_1;
			$cond1->value_1				=	$obj->cond_1_value_1;
			$cond1->dates_diff_a		=	$obj->cond_1_dates_diff_a;
			$cond1->dates_diff_b		=	$obj->cond_1_dates_diff_b;
			$cond1->date_2				=	$obj->cond_1_date_2;
			$cond1->date_cbfield_2		=	$obj->cond_1_date_cbfield_2;
			$cond1->value_2				=	$obj->cond_1_value_2;
			$c1result					=	$cond1->checkCondition( $userId, $paymentBasket );
			if ( $obj->cond_1_operator == 'not' ) {
				$c1result				=	! $c1result;
			}
			$combination				=	$obj->cond_2_operator;
			if ( $combination ) {
				$cond2					=	new cbpaidCondition();
				$cond2->plans_required	=	$obj->cond_2_plans_required;
				$cond2->plans_status	=	$obj->cond_2_plans_status;
				$cond2->purchase_ok		=	$obj->cond_2_purchase_ok;
				$cond2->date_1			=	$obj->cond_2_date_1;
				$cond2->date_cbfield_1	=	$obj->cond_2_date_cbfield_1;
				$cond2->value_1			=	$obj->cond_2_value_1;
				$cond2->dates_diff_a	=	$obj->cond_2_dates_diff_a;
				$cond2->dates_diff_b	=	$obj->cond_2_dates_diff_b;
				$cond2->date_2			=	$obj->cond_2_date_2;
				$cond2->date_cbfield_2	=	$obj->cond_2_date_cbfield_2;
				$cond2->value_2			=	$obj->cond_2_value_2;
				$c2result				=	$cond2->checkCondition( $userId, $paymentBasket );
				switch ( $combination ) {
					case 'and':
						$r				=	$c1result && $c2result;
						break;
					case 'or':
						$r				=	$c1result || $c2result;
						break;
					case 'xor':
						$r				=	( $c1result xor $c2result );
						break;
					case 'andnot':
						$r				=	$c1result && ! $c2result;
						break;
	
					default:
						$r				=	$c1result;
					break;
				}
			} else {
				$r						=	$c1result;
			}
		} else {
			$r							=	true;
		}
		if ( ! $r ) {
			$resultTexts[]				=	CBPTXT::T("Promotion applies depending on other subscriptions or conditions.");
		}
		return $r;
	}
}
