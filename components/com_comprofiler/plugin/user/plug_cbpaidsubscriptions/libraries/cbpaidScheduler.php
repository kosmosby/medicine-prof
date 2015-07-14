<?php
/**
* @version $Id: cbpaidScheduler.php 1542 2012-11-23 22:27:49Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Scheduler
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class that shows which variables are needed to implement a scheduler in a table
 */
abstract class exampleScheduledClass extends cbpaidTable {
	public $period1;
	public $period3;
	public $recur_times;
	public $subscr_date;
	public $reattempt;
	public $retry_at;

	public $recur_times_used;
	public $reattempts_tried;
	public $scheduler_state;
	public $scheduler_next_maturity;

	public $_cbapidScheduler;
}
/**
 * Scheduled table class
 */
class cbpaidSchedule {
	public $retries					=	3;
	public $retryInterval			=	86400;		// 3600*24;
	public $maxExecWaitIterations	=	3;			// max 9 (single char storage)
	/**
	 * Class which is extended
	 * @var exampleScheduledClass
	 */
	private $_baseObject;
	/**
	 * Constructor
	 * @param  exampleScheduledClass  $baseObject
	 */
	public function __construct( $baseObject ) {
		$this->_baseObject									=	$baseObject;
	}
	/**
	 * Schedules an event
	 * Transition !E -> S if there is a maturity (otherwise -> T)
	 *
	 * @param  boolean  $storeObject  calls $this->store()
	 * @return boolean                TRUE: scheduling was needed, FALSE: no scheduling needed
	 */
	public function schedule( $storeObject = true ) {
		$idleObject											=	! in_array( substr( $this->_baseObject->scheduler_state, 0, 1 ), array( 'E' ) );		// Idle, Scheduled, Executing, Terminated, Cancelled
		if ( $idleObject ) {
			$maturity										=	$this->computeMaturity( $this->_baseObject );
			if ( $maturity ) {
				$this->_baseObject->scheduler_state			=	'S';		// Scheduled
				$this->_baseObject->scheduler_next_maturity	=	$maturity;
				$this->_baseObject->reattempts_tried		=	0;
			} else {
				$this->_baseObject->scheduler_state			=	'T';		// Terminated (finished)
				$this->_baseObject->scheduler_next_maturity	=	$this->_baseObject->_db->getNullDate();
			}
			if ( $storeObject ) {
				$this->_baseObject->store();
			}
		}
	}
	/**
	 * Unschedules this object completely
	 * Transition -> C
	 * @param  boolean  $storeObject
	 */
	public function unschedule( /** @noinspection PhpUnusedParameterInspection */ $storeObject = true ) {
		$this->_baseObject->scheduler_state	=	'C';						// Cancelled (finished)
		$this->_baseObject->scheduler_next_maturity			=	$this->_baseObject->_db->getNullDate();
		$this->_baseObject->store();
	}
	/**
	 * When triggered, before starting to make changes this method should be called
	 * Transition: -> E{$substate}
	 * 
	 * @param  string  $substate
	 * @return boolean                TRUE: got lock to perform scheduled task, FALSE: no scheduling needed here (and no transition made)
	 */
	public function attemptScheduledTask( $substate = '' ) {
		if ( substr( $this->_baseObject->scheduler_state, 0, 1 ) == 'E' ) {
			// It was Executing: check for iterations before resetting to 'S' (with error log).

			if ( strlen( $this->_baseObject->scheduler_state ) == 1 ) {
				// Backwards compatibility:
				$this->_baseObject->scheduler_state			.=	'1';
			}

			$iteration										=	(int) $this->_baseObject->scheduler_state[1];
			if ( ++$iteration <= $this->maxExecWaitIterations ) {
				$this->_baseObject->scheduler_state[1]		=	(string) $iteration;
				$this->_baseObject->store();
			} else {
				$this->_baseObject->scheduler_state			=	'S';
				cbpaidApp::setLogErrorMSG( 4, $this->_baseObject, CBPTXT::P("Scheduler for this basket has stayed in execution state for [NUMBER_OF_CRONS] cron cycles. Resetting execution state.", array( '[NUMBER_OF_CRONS]' => $this->maxExecWaitIterations ) ), null );
			}
		}

		// Now normal case:
		if ( $this->_baseObject->scheduler_state == 'S' ) {
			// it was scheduled:
			$this->_baseObject->scheduler_state				=	'E1' . $substate;	// Executing
			return $this->_baseObject->store();
		}
		return false;
	}
	/**
	 * Sets and stores a new sub-state $substate
	 * Transition: Ex -> E{$substate}
	 * 
	 * @param  string  $substate
	 */
	public function setSubState( $substate = '' ) {
		if ( substr( $this->_baseObject->scheduler_state, 0, 1 ) == 'E' ) {
			// it is executing:
			$this->_baseObject->scheduler_state				=	'E1' . $substate;	// Executing
			$this->_baseObject->store();
		}
	}
	/**
	 * Gets current substate
	 *
	 * @return string
	 */
	public function getSubState( ) {
		return substr( $this->_baseObject->scheduler_state, 1 );
	}
	/**
	 * Records a successful attempt of the trigger and schedules the next one if it is needed
	 *
	 * @return boolean  TRUE: new attempt has been scheduled
	 */
	public function attemptScheduledTaskSuccessful( ) {
		if ( substr( $this->_baseObject->scheduler_state, 0, 1 ) == 'E' ) {
			// it was executing:
			++$this->_baseObject->recur_times_used;
			$this->_baseObject->reattempts_tried			=	0;
			if ( ( $this->_baseObject->recur_times == 0 ) || ( $this->_baseObject->recur_times_used < $this->_baseObject->recur_times ) ) {
				$this->_baseObject->scheduler_state				=	'I';		// 'I'dle temporarily in memory, as schedule() won't schedule with 'E'xecuting 
				return $this->schedule( true );
			} else {
				$this->_baseObject->scheduler_state				=	'T';		// Terminated
				$this->_baseObject->scheduler_next_maturity		=	$this->_baseObject->_db->getNullDate();
				$this->_baseObject->store();
			}
		}
		return false;
	}
	/**
	 * Records a failed attempt, and if not fatal and re-trial numbers not exceeded, schedules a retry
	 * NOTE: a permanent error here is not same as a final error in the trigger function:
	 * e.g. a credit-card may fail to be charged today (permanent error), but may be charged again in 2 days
	 * e.g. a credit-card PSP have a transitional error today, and we need to retry but at retry re-check state first.
	 *
	 * @param  boolean  $transientErrorDoReschedule   TRUE: it was a transient error, FALSE: it was a permanent error
	 * @return boolean  TRUE: new attempt has been scheduled
	 */
	public function attemptScheduledTaskFailed( $transientErrorDoReschedule ) {
		if ( substr( $this->_baseObject->scheduler_state, 0, 1 ) == 'E' ) {
			// it was executing:
			$this->_baseObject->scheduler_state				=	'F';		// Failed (finished)
			++$this->_baseObject->reattempts_tried;
			if ( $transientErrorDoReschedule && $this->_baseObject->reattempt && ( $this->_baseObject->reattempts_tried <= $this->retries ) ) {
				// Transient error, reschedule:
				$maturity									=	$this->computeMaturity( $this->_baseObject, true );
				if ( $maturity ) {
					$this->_baseObject->scheduler_state		=	'S';		// Scheduled
					$this->_baseObject->scheduler_next_maturity		=	$maturity;
					$this->_baseObject->store();
					return true;
				}
			} else {
				// Fatal error: failed, stop scheduler:
				$this->_baseObject->scheduler_next_maturity	=	$this->_baseObject->_db->getNullDate();
				$this->_baseObject->store();
			}
		}
		return false;
	}
	/**
	 * Computes SQL DATETIME of next schedule based on $baseObject
	 *
	 * @param  exampleScheduledClass  $baseObject
	 * @param  boolean                $justFailed
	 * @return string
	 */
	public function computeMaturity( $baseObject, $justFailed = false ) {
		global $_CB_framework;

		static $timeWords	=	array(	'S'	=>	'second',
										'I'	=>	'minute',
										'H'	=>	'hours',
										'D'	=>	'day',
										'W'	=>	'week',
										'M'	=>	'month',
										'Y'	=>	'year',
										);
		$offset				=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$localTimeC			=	strtotime( $baseObject->subscr_date ) + $offset;

		$recurrings			=	$baseObject->recur_times_used;

		// first period:
		if ( $baseObject->period1 ) {
			$parray 		=	explode( ' ', $baseObject->period1 );															// e.g. '12 M' => array( '12', 'M' )
			if ( ( count( $parray ) == 2 ) && isset( $timeWords[$parray[1]] ) ) {									// check format
				$localTimeC	=	strtotime( '+' . $parray[0] . ' ' . $timeWords[$parray[1]], $localTimeC );	// e.g. '+ 12 months'
			}
		} else {
			++$recurrings;
		}
		if ( $recurrings ) {
			// reoccuring period:
			$parray			=	explode( ' ', $baseObject->period3 );								// e.g. '12 M' => array( '12', 'M' )
			if ( ( count( $parray ) == 2 ) && isset( $timeWords[$parray[1]] ) ) {		// Format is valid ?
				// Format is valid:
				$number		=	intval( $parray[0] ) * ( $recurrings );
				$localTimeC	=	strtotime( '+' . $number . ' ' . $timeWords[$parray[1]], $localTimeC );		// e.g. '+ 12 months'
			}
		}
		$localTimeC			+=	$baseObject->reattempts_tried * $this->retryInterval;
		$maturityTime		=	$localTimeC - $offset;
		if ( $justFailed ) {
			$tomorrow		=	$_CB_framework->now() + $this->retryInterval;
			if ( $tomorrow > $maturityTime ) {
				$maturityTime	=	$tomorrow;
			}
		}
		return date( 'Y-m-d H:i:s', $maturityTime );
	}
}
/**
* Scheduler class
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
*/
class cbpaidScheduler {
	/**
	 * Returns the scheduler associated to a schedulable object, creates the cbpaidSchedule if needed
	 * 
	 * @param  exampleScheduledClass|cbpaidTable|cbpaidBaseClass  $baseObject
	 * @return cbpaidSchedule
	 */
	public static function & getInstance( $baseObject ) {
		if ( ! isset( $baseObject->_cbapidScheduler ) ) {
			$baseObject->_cbapidScheduler	=	new cbpaidSchedule( $baseObject );
		}
		return $baseObject->_cbapidScheduler;
	}
}

