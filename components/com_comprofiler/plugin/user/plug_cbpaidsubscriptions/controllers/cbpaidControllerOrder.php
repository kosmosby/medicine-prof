<?php
/**
 * @version $Id: cbpaidControllerOrder.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CBSubs Order controller
 */
class cbpaidControllerOrder {
	/**
	 * Creates a new (or loads an existing) subscription, and if it's non-free:
	 * Creates a payment basket if there is not already one
	 * ---- and then displays payment processing mask, button, or redirects
	 * If it's free: activates account and does not return anything.
	 *
	 * @param  UserTable           $user
	 * @param  cbpaidProduct[]     $chosenPlans              array of cbpaidProduct : Chosen plans to pay
	 * @param  array               $postdata                 $_POST array for the parameters of the subscription plans
	 * @param  array|null          $replacesSubscriptionIds  In fact: the existing one in all cases, except if new to be created.
	 * @param  array|null          $existingSubscriptionIds  In fact: the new one in case of upgrade !
	 * @param  string              $status 					 subscription status: 'R'=registered (default) , 'I'=illegal, 'A'=active, etc.
	 * @param  string              $prefixText 				 text to prefix the payment items (default: null)
	 * @param  string              $reason 					 payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  string              $payment                  'now' (default), 'free'
	 * @return cbpaidPaymentBasket|string                    object if something to pay, otherwise HTML text for message.
	 */
	public static function createSubscriptionsAndPayment( &$user, $chosenPlans, $postdata, $replacesSubscriptionIds = null, $existingSubscriptionIds = null, $status = 'R', $prefixText = null, $reason='N', $payment = 'now' ) {
		global $_CB_framework, $_CB_database;

		$subscriptionTime				=	$_CB_framework->now();

		/** @var cbpaidSomething[] $subscriptions */
		$subscriptions					=	array();
		$needToPay						=	false;

		//TBD LATER: Handle fully payment baskets as baskets, adding/replacing the content with the new items:
		$paymentBasket					=&	cbpaidPaymentBasket::getInstanceBasketOfUser( $user->id, false );			//TBD could be true to avoid old baskets ?
		if ( $paymentBasket->id ) {
			// otherwise show existing basket:
			return $paymentBasket;
		}

		if ( ! ( $chosenPlans && ( count( $chosenPlans ) > 0 ) ) ) {
			trigger_error( 'createSubscriptionsAndPayment:: called without plans chosen !', E_USER_ERROR );
		}
		// 1. add subscription records if not existing: pass 1: parents, pass 2: children:
		$pass							=	0;
		while ( ++$pass <= 2 ) {
			foreach ( $chosenPlans as $plan ) {
				$parentPlan							=	$plan->get( 'parent' );
				if ( ( ( $pass == 1 ) && ( $parentPlan == 0 ) ) || ( ( $pass == 2 ) && ( $parentPlan != 0 ) ) ) {
					$planId							=	$plan->get( 'id' );

					/* $reasonInCaseExpired			=	$reason; */
					// find replaced subscription id:
					$replacesSubId					=	null;
					if ( $replacesSubscriptionIds && isset( $replacesSubscriptionIds[$planId] ) ) {
						$replacesSubId				=	$replacesSubscriptionIds[$planId];
						/*		//TBD later: need to check if we really want to renew an existing subscription when there is an interruption.
						if ( $reason == 'R' ) {
							$paidSomethinMgr		=&	cbpaidSomethingMgr::getInstance();
							$replacesSub			=&	$paidSomethinMgr->loadSomething( $replacesSubId[0], $replacesSubId[1] );
							if ( $replacesSub ) {
								if ( ! $replacesSub->checkIfValid( $subscriptionTime ) ) {

								}
							}
						}
						*/
					} else {
						if ( $reason == 'R' ) {
							trigger_error( 'createSubscriptionsAndPayment::no existing subscription for renewal !', E_USER_ERROR );
							exit;
						}
					}

					// find existing plan+subscription id:
					$existingSubId					=	null;
					if ( $reason != 'R' ) {
						// new or upgrade: create or load $subscription:
						if ( $existingSubscriptionIds && isset( $existingSubscriptionIds[$planId] ) ) {
							$existingSubId			=	$existingSubscriptionIds[$planId];
						}
					}

					// check that subscription is renewable if getting renewed:
					if ( ( $reason == 'R' ) && $replacesSubId ) {
						$paidSomethingMgr						=&	cbpaidSomethingMgr::getInstance();
						$subscription							=	$paidSomethingMgr->loadSomething( $replacesSubId[0], $replacesSubId[1] );
						if ( ! $subscription->checkIfRenewable() ) {
							trigger_error( sprintf( 'createSubscriptionsAndPayment::Renewal not allowed !' ), E_USER_WARNING );
							return 'Unexpected condition: Renewal not allowed !';
						}
					}
					// find parent subscription id if exists:
					$parentSubId									=	null;
					if ( $parentPlan ) {
						switch ( $reason ) {
							case 'R':
								// renew: don't change anything in the existing subscription (no update on NULL):
								// $parentSubId						=	null;
								break;
							case 'U':
								// upgrade: check if parent subscription is upgraded same time:
								if ( isset( $subscriptions[$parentPlan] ) ) {
									$parentSubId					=	array( (int) $subscriptions[$parentPlan]->plan_id, (int) $subscriptions[$parentPlan]->id );
								} else {
									// if not: try to find the existing subscription in database:
									if ( $existingSubId ) {
										$paidSomethinMgr			=&	cbpaidSomethingMgr::getInstance();
										$thisSub					=	$paidSomethinMgr->loadSomething( $existingSubId[0], $existingSubId[1] );
										if ( $thisSub ) {
											// then try to find parent subscription of the existing subscription:
											if ( $thisSub->parent_plan && $thisSub->parent_subscription ) {
												$thisSubParent		=	$paidSomethinMgr->loadSomething( $thisSub->parent_plan, $thisSub->parent_subscription );
												if ( $thisSubParent ) {
													$parentSubId	=	array( $thisSubParent->parent_plan, $thisSubParent->parent_subscription );
												}
											}
										} else {
											trigger_error( sprintf( 'createSubscriptionsAndPayment::no existing subscription id %d found in database for upgraded plan id %d !', $existingSubId[1], $existingSubId[0] ), E_USER_WARNING );
										}
									} else {
										// try finding subscription of parent plan by this user:
										$plansMgr					=&	cbpaidPlansMgr::getInstance();
										$parPlan					=	$plansMgr->loadPlan( $parentPlan );
										if ( $parPlan ) {
											$something				=	$parPlan->newSubscription();
											$foundParent			=	$something->loadLatestSomethingOfUser( $user->id );
											if ( $foundParent ) {
												$parentSubId		=	array( $something->plan_id, $something->id );
											}
										}
									}
								}
								break;

							case 'N':
							default:
								// new: find parent subscription:
								if ( isset( $subscriptions[$parentPlan] ) ) {
									$parentSubId		=	array( (int) $subscriptions[$parentPlan]->plan_id, (int) $subscriptions[$parentPlan]->id );
								} else {
									trigger_error( sprintf( 'createSubscriptionsAndPayment::no existing subscription for parent plan id %d of plan %d in new subscription !', $parentPlan, $planId ), E_USER_WARNING );
								}
								break;
						}
					}

					// creates the subscription of the correct type:
					$price						=	false;		// returned values from next line:
					$recurringPrice				=	false;		// returned values from next line:
					$subscriptions[$planId]		=	$plan->createProductThing( $user, $postdata, $reason, $status, $replacesSubId, $existingSubId, $subscriptionTime, $price, $recurringPrice, $parentSubId );
					if ( ( $price === false ) && ( $recurringPrice === false ) ) {
						unset( $subscriptions[$planId] );		// can't be subscribed/purchased
						unset( $chosenPlans[$planId] );
					} elseif ( ( $price > 0 ) || ( $recurringPrice > 0 ) ) {
						// $lastSubscriptionId	=	$subscriptions[$planId]->id;
						// $lastPlanId			=	$planId;
						$needToPay				=	true;
					}
				}
			}
		}

		// Sort subscriptions, so they are presented in basket in same order as on the plans selection:
		$sortedSubscriptions					=	array();
		foreach ( array_keys( $chosenPlans ) as $id ) {
			$sortedSubscriptions[$id]			=	$subscriptions[$id];
		}

		if ( ( $payment == 'free' ) || ( ! $needToPay ) ) {						//TBD: Should we activate already what can be activated (check for hierarchy) !???
			// Free plan: no payment ! : activate $subscription now:
			$thankYouText						=	array();
			$cbUser								=	CBuser::getInstance( $user->id );
			foreach ( array_keys( $subscriptions ) as $k ) {
				if ( ( $reason != 'R' ) || $subscriptions[$k]->checkIfRenewable() ) {
					$occurrences				=	1;
					$autorecurring_type			=	0;
					$autorenew_type				=	0;
					// bug #1184 fix: this was certainly wrong in backend at least, but in frontend too most likely too, as it would block from renewing imho:
					// $autorecurring_type			=	( ( $chosenPlans[$k]->autorecurring > 0 ) ? 2 : 0 );
					// $autorenew_type				=	( ( $chosenPlans[$k]->autorecurring > 0 ) ? 2 : 0 );
					$subscriptions[$k]->activate( $user, $subscriptionTime, true, $reason, $occurrences, $autorecurring_type, $autorenew_type );
					$extraStrings				=	$subscriptions[$k]->substitutionStrings( true );
					$thankYouText[]				=	trim( $cbUser->replaceUserVars( CBPTXT::Th( $subscriptions[$k]->getText( 'thankyoutextcompleted' ) ), true, true, $extraStrings, false ) );
				}
			}
			if ( count( $thankYouText ) > 0 ) {
				return implode( '<br />', $thankYouText );
			} else {
				return CBPTXT::Th("Chosen plan(s) can not be subscribed") . '.';
			}
		} else {
			// non-free plan:
			// 2. add payment_item and payment_basket records:

			// get the most recent payment basket for $subscription
			$paymentBasket						=	new cbpaidPaymentBasket( $_CB_database );
			/*
						$basketLoaded						=	$paymentBasket->loadLatestBasketOfUserPlanSubscription( $user->id );
						if ( $basketLoaded ) {
							$paymentBasket->delete();
							$paymentBasket					=	new cbpaidPaymentBasket( $_CB_database );
							//TBD LATER: Handle fully payment baskets as baskets, adding/replacing the content with the new items.
						}
			*/
			$hasAmountToPay						=	$paymentBasket->createAndFillCreteSubscriptionsItems( $user, $sortedSubscriptions, $prefixText, $reason, $subscriptionTime );
			if ( ! $hasAmountToPay ) {
				$thankYouText					=	array();
				$cbUser							=	CBuser::getInstance( $user->id );
				foreach ( array_keys( $subscriptions ) as $k ) {
					$subscriptions[$k]->activate( $user, $subscriptionTime, true, $reason );
					$extraStrings				=	$subscriptions[$k]->substitutionStrings( true );
					$thankYouText[]				=	trim( $cbUser->replaceUserVars( CBPTXT::Th( $subscriptions[$k]->getText( 'thankyoutextcompleted' ) ), true, true, $extraStrings, false ) );
				}
				return implode( '<br />', $thankYouText );
			}

			if ( ( $paymentBasket->payment_status === null ) || ( $paymentBasket->payment_status == 'NotInitiated' ) ) {
				return $paymentBasket;
			}
		}
		trigger_error( '_createSubscriptionsAndPayment: Unexpected condition: no payment and no free plan', E_USER_NOTICE );
		return 'Unexpected condition.';
	}
	/**
	 * Creates a new (or loads an existing) subscription, and if it's non-free:
	 * Creates a payment basket if there is not already one
	 * ---- and then displays payment processing mask, button, or redirects
	 * If it's free: activates account and does not return anything.
	 *
	 * @param  UserTable            $user
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $introText
	 * @param  boolean              $ajax           TRUE if AJAX refresh inside #cbregPayMethodsChoice, FALSE: wraps in <div id="cbregPayMethodsChoice">
	 * @return string                               HTML to display
	 * @access private
	 */
	public static function showBasketForPayment( &$user, &$paymentBasket, $introText, $ajax = false ) {
		$result				=	null;
		if ( ( $paymentBasket !== null ) && ( $paymentBasket->id ) ) {
			$paymentStatus	=	$paymentBasket->payment_status;
			if ( ( $paymentStatus === null ) || ( $paymentStatus == 'NotInitiated' ) ) {

				// unused basket: display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
				$result		.=	cbpaidControllerPaychoices::getInstance()->getPaymentBasketPaymentForm( $user, $paymentBasket, $introText, $ajax );

			} elseif ( $paymentStatus == 'Completed' ) {
				$result .= '<p>' . CBPTXT::T("The most recent payment for this item got payment Completed in the mean time.") . '</p>';
			} elseif ( $paymentStatus == 'Pending' ) {
				$result .= sprintf( CBPTXT::T("Payment of this item has currently payment status '%s'") . '. ', $paymentStatus );
				if ( $paymentBasket->pending_reason ) {
					$result .= '<p>';
					if ( $paymentBasket->payment_type ) {
						$result .= sprintf( CBPTXT::T("The payment of type '%s' is pending due to reason: '%s'") . '.', $paymentBasket->payment_type, $paymentBasket->pending_reason );
					} else {
						$result .= sprintf( CBPTXT::T("The payment is pending due to reason: '%s'") . '.', $paymentBasket->pending_reason );
					}
					$result	.=	'</p>';
				}
				$result .=  CBPTXT::T("Please check with your payment provider") . ': '
					. CBPTXT::T("Some payments, using E-checks or bank transfers in particular, may take up to several days to complete.");
			} else {
				$result .= '<p>' . sprintf( CBPTXT::T("The most recent payment for this item has currently payment status '%s'."), $paymentStatus ) . '</p>';
			}
		} else {
			$result = null;			// $paymentBasket === null : nothing to show (maybe due to a free subscription) !
		}
		return $result;
	}

	/** *** From PAY and onDuringLogin For Now !
	 * Shows a payment form corresponding to the latest payment basket (otpionally of a given subscription) and gives its status
	 *
	 * @param  UserTable             $user
	 * @param  cbpaidProduct[]|null  $chosenPlans      array of cbpaidProduct : Chosen plans to pay
	 * @param  string                $introText
	 * @param  array                 $subscriptionIds  array of int: Subscription ids to pay
	 * @param  string                $paymentStatus    returns one of following: 'noBasketFound', and all cpayPaymentBasket statuses: treated here: null (payment not made), 'Pending', 'Completed'
	 * @return string|null
	 */
	public static function showPaymentForm( &$user, $chosenPlans, $introText, $subscriptionIds, &$paymentStatus ) {
		// get the most recent payment basket for that user and plan, and with that subscription if $subscriptionId != null:
		$paymentBasket		=	new cbpaidPaymentBasket();
		if ( is_array( $chosenPlans ) ) {
			/** @var $lastPlan cbpaidProduct|boolean */
			$lastPlan		=	end( $chosenPlans );
			reset( $chosenPlans );
			if ( $lastPlan === false ) {
				$lastPlanId	=	null;
			} else {
				$lastPlanId	=	$lastPlan->get( 'id' );
			}
		} else {
			$lastPlanId		=	(int) $chosenPlans;
			if ( ! $lastPlanId ) {
				$lastPlanId	=	null;
			}
		}
		if ( is_array( $subscriptionIds ) && ( count( $subscriptionIds ) > 0 ) ) {
			$lastPlanAndSubId	=	end( $subscriptionIds );
			reset( $subscriptionIds );
			if ( count( $lastPlanAndSubId ) == 2 ) {
				list( $lastPlanId, $lastSubId )	=	$lastPlanAndSubId;
			} else {
				$lastSubId	=	null;
			}
		} else {
			$lastSubId		=	null;
		}
		$basketLoaded		=	$paymentBasket->loadLatestBasketOfUserPlanSubscription( $user->id, $lastPlanId, $lastSubId );
		if ( $basketLoaded ) {
			$paymentStatus	=	$paymentBasket->payment_status;
			// display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
			$result			=	self::showBasketForPayment( $user, $paymentBasket, $introText );
		} else {
			$basketLoaded			=	$paymentBasket->loadLatestBasketOfUserPlanSubscription( $user->id, $lastPlanId, $lastSubId, 'Pending' );
			if ( ! $basketLoaded ) {
				// This is an error condition, subscription has been created, and is called for payment but no basket is found in database, so create a new one:
				// $paymentStatus = 'noBasketFound';
				// cbpaidApp::getBaseClass()->_setErrorMSG("No payment basket found, creating new one.");
				// $result = null;
				cbpaidApp::getBaseClass()->_setErrorMSG(CBPTXT::T("No payment basket found, creating new one."));
			}
			if ( $chosenPlans && ( count( $chosenPlans ) > 0 ) ) {
				$paymentBasket		=	cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $chosenPlans, array(), null, $subscriptionIds, null, null );
				if ( is_object( $paymentBasket ) ) {
					if ( $basketLoaded ) {
						cbpaidApp::getBaseClass()->_setErrorMSG( CBPTXT::T("A payment basket is pending in progress for payment for this. Are you sure that you didn't already pay for this ?. Here you can pay again:") );
					}
					$paymentStatus	=	$paymentBasket->payment_status;
					$result			=	self::showBasketForPayment( $user, $paymentBasket, $introText );
				} else {
					$result			=	$paymentBasket;		// display messages as nothing has to be paid.
				}
			} else {
				// can be called with chosenPlans null to try loading a valid payment basket from the user:
				// trigger_error( 'cbpaid:_showPaymentForm: no chosen plans.', E_USER_NOTICE );
				$result				=	null;
			}
		}
		return $result;
	}

	/** *** From UNSUBSCRIBE_CONFIRM in frontend:
	 * Shows an unsubscription confirmation form
	 *
	 * @param UserTable           $user
	 * @param string              $introText
	 * @param int                 $planId
	 * @param int                 $subscriptionId
	 * @return bool|mixed|null|string
	 * @access private
	 */
	public static function doUnsubscribeConfirm( &$user, $introText, $planId, $subscriptionId ) {
		$result						=	null;
		$paidSomethinMgr			=&	cbpaidSomethingMgr::getInstance();
		$subscription				=&	$paidSomethinMgr->loadSomething( $planId, $subscriptionId );
		if ( $subscriptionId && $subscription && ( $subscription->user_id == $user->id ) ) {

			$result					=	$subscription->stopAutoRecurringPayments();
			if ( is_string( $result ) ) {
				cbpaidApp::getBaseClass()->_setErrorMSG( $result );
				$result				=	false;
			}
			if ( $result !== false ) {
				$subscription->deactivate( $user, 'C' );		// cancelled
				if ( strpos( $introText, '%s' ) !== false ) {
					$planName		=	$subscription->getPlanAttribute( 'name' );
					$result			=	str_replace( '%s', $planName, $introText );
				} else {
					$result			=	$introText;
				}

			}
		} else {
			$params					=&	cbpaidApp::settingsParams();
			$subTxt					=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
			cbpaidApp::getBaseClass()->_setErrorMSG( sprintf( CBPTXT::T("No %s found"), $subTxt ) );
			$result					=	false;
		}
		// if ( $result === false ) {
		//		$result				=	cbpaidApp::getBaseClass()->getErrorMSG( '<br />' );
		// }
		return $result;
	}

	/* FOR PAY ONLY For Now !
	 * Shows a payment form corresponding to the latest payment basket (otpionally of a given subscription) and gives its status
	 *
	 * @param  UserTable      $user
	 * @param  cbpaidProduct  $plan
	 * @param  string         $introText
	 * @param  int            $subscriptionId
	 * @param  string         $paymentStatus   returns one of following: 'noBasketFound', and all cpayPaymentBasket statuses: treated here: null (payment not made), 'Pending', 'Completed'
	 * @return string
	 *
	public static function showPaymentForm( &$user, &$plan, $introText, $subscriptionId, &$paymentStatus ) {
		global $_CB_database;

		$result = null;
		$paymentStatus = 'noBasketFound';

		// get the most recent payment basket for that user and plan, and with that subscription if $subscriptionId != null:
		$paymentBasket = new cbpaidPaymentBasket( $_CB_database );
		$basketLoaded	= $paymentBasket->loadLatestBasketOfUserPlanSubscription( $user->id, $plan->get( 'id' ), $subscriptionId );
		if ( $basketLoaded ) {
			$paymentStatus = $paymentBasket->payment_status;
			if ( $paymentStatus == 'Completed' ) {
				$result .= "<p>The most recent payment basket containing this subscription as item got payment Completed in the mean time.</p>";
			} elseif ( ( $paymentStatus !== null ) && ( $paymentStatus != 'NotInitiated' ) ) {
				// 1. find subscription records:
				$result .= "<p>The most recent payment basket containing this subscription as item has currently payment status '"
						.  $paymentStatus . "'.</p>";

				$subscription	= new cbpaidUsersubscriptionRecord( $_CB_database );
				if ( $subscriptionId && $subscription->load( (int) $subscriptionId ) ) {
					//TBD check: create new payment basket ?:
					$newBasket = cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $plan, $postdata, null, $subscriptionId );
					if ( $newBasket !== null ) {
						$result .= "<p>If you wish to proceed with a new payment, please do so:</p>";
						$result .= self::showBasketForPayment( $user, $newBasket, $introText );
					}
				} else {
					if ( ( $paymentStatus == 'Pending' ) && $paymentBasket->pending_reason ) {
						$result .= "<p>The payment ";
						if ( $paymentBasket->payment_type ) {
							$result .= "of type '" . $paymentBasket->payment_type . "' ";
						}
						$result .= "is pending due to reason: '" . $paymentBasket->pending_reason . "'.</p>";
					}
					$result .= "<p>Please check status with your payment provider.</p>";
				}
			} else {
				// display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
				$result .= self::showBasketForPayment( $user, $paymentBasket, $introText );
			}
		}
		return $result;
	}
*/
}