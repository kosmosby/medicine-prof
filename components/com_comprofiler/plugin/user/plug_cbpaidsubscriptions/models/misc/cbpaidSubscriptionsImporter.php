<?php
/**
* @version $Id: cbpaidSubscriptionsImporter.php 1538 2012-11-23 18:38:47Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Importer class for backend subscriptions imports
 */
class cbpaidSubscriptionsImporter extends cbpaidTable {
	public $id;
	public $import_type;
	public $usergroup;
	public $from_plan;
	public $from_sub_status;
	public $plan;
	public $state;
	public $date;
	public $dryrun;

	protected $_states = array( 'A' => "Active", 'R' => "Registered Unpaid", 'X' => "Expired", 'C' => "Unsubscribed", 'U' => "Upgraded to other", 'I' => "Invalid" );
	protected $_resultOfStore;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( 'NONE', 'id', $db );
	}
	/**
	 * Load function override by dummy
	 *
	 * @param  int|null  $oid
	 * @return mixed           any result from the database operation
	 */
	public function load( $oid = null ) {
		// override by dummy function:
		return null;
	}
	/**
	 * store() function override, instead of storing it imports.
	 *
	 * @param  boolean  $updateNulls
	 * @return boolean
	 */
	public function store( $updateNulls = false ) {
		global $_CB_framework, $ueConfig;
		$return = '';
		
		// Check if file uploads are enabled
		if (!(bool)ini_get('file_uploads')) {
			$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("The importer can't continue before file uploads are enabled in PHP settings.");
			return false;
		}

		if ( ! $this->import_type ) {
			$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No import type selected");
			return false;
		}

		$fromFile			=	cbStartOfStringMatch( $this->import_type, 'file_' );

		if( $fromFile ) {
			$userfile			= $_FILES['userfile'];
	
	
			if ( !$userfile || ( $userfile == null ) ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No file selected");
				return false;
			}
			if ( isset( $userfile['error'] ) && ( $userfile['error'] ) ) {
				$errors_array	=	array(	1 => CBPTXT::T("The uploaded file exceeds the upload_max_filesize directive in php.ini.") ,
											2 => CBPTXT::T("The uploaded file exceeds the maximum size allowed by this form.") ,
											3 => CBPTXT::T("The uploaded file was only partially uploaded.") ,
											4 => CBPTXT::T("No file was selected and uploaded.") ,
											6 => CBPTXT::T("Missing a temporary folder in php.ini.") ,
											7 => CBPTXT::T("Failed to write file to disk.") ,
											8 => CBPTXT::T("File upload stopped by extension.") );
				if ( in_array( $userfile['error'], $errors_array ) ) {
					$fileErrorTxt	=	$errors_array[$userfile['error']];
				} else {
					$fileErrorTxt	=	CBPTXT::T("File upload error number ") . htmlspecialchars( $userfile['error'] );
				}
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . $fileErrorTxt;
				return false;
			}
			if ( ( ! $userfile['tmp_name'] ) || ( ! is_uploaded_file( $userfile['tmp_name'] ) ) ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No temporary file name");
				return false;
			}
			if ( $userfile['size'] == 0 ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("Empty file");
				return false;
			}
		} else {
			$userfile			=	null;
		}

		if ( $this->import_type == 'cms_acl' ) {
			if ( !$this->usergroup ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No usergroup selected");
				return false;
			}
		}
		if ( $this->import_type == 'subscription' ) {
			if ( !$this->from_plan ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No subscription plan selected");
				return false;
			}
			if ( !$this->from_sub_status ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No subscription status selected");
				return false;
			}
		}
		if ( $this->import_type != 'file_uid_plan_exp' ) {
			if ( !$this->plan ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No plan selected");
				return false;
			}
			if ( !$this->state ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No subscription state selected");
				return false;
			}
			if ( !$this->date ) {
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No subscription date selected");
				return false;
			}
		}

		if ( $fromFile ) {
		    $tmpName			=	$userfile['tmp_name'];
			$fileSize			=	(int) $userfile['size'];
			// $fileType = $userfile['type']; 
		} else {
			$tmpName			=	null;
			$fileSize			=	null;
		}

		$planStateDate			=	array();

		switch ($this->import_type ) {
			case 'file_uid':
				$fp				=	fopen( $tmpName, 'r' ); 
				$content		=	fread( $fp, $fileSize );
				fclose( $fp );
				unlink( $tmpName );
				$userIdList		=	explode( ',', trim( $content ) );
				break;
			
			case 'file_uid_plan_exp':
				$userIdList		=	array();

				$fp				=	fopen( $tmpName, 'r' );
				if ( $fp ) {
					$n			=	0;
					while (!feof( $fp ) ) {
						$line	=	trim( str_replace( '"', '', fgets( $fp, 256 ) ) );
						$n		+=	1;
						if ( strlen( $line ) > 0 ) {
							$matches			=	null;
							if ( preg_match( '/([1-9][0-9]*),([1-9][0-9]*),([AXC]),([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9][0-9]:[0-9][0-9]:[0-9][0-9])/', $line, $matches ) ) {
								if ( ! in_array( (int) $matches[1], $userIdList ) ) {
									$userIdList[]	=	(int) $matches[1];
								}
								$planStateDate[(int) $matches[1]][]	=	array( 'plan' => (int) $matches[2], 'status' => $matches[3], 'date' => $matches[4], );
							} else {
								$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . sprintf(CBPTXT::T("Line %s does not match the format userid,planid,status,date, e.g. 63,1,A,2009-01-01 00:00:00, and is instead: %s ."), $n, htmlspecialchars( $line ) );
								fclose( $fp );
								unlink( $tmpName );
								return false;
							}
						}
					}
				}
				fclose( $fp );
				unlink( $tmpName );
				break;

			case 'cms_acl':
				if ( checkJversion() >= 2 ) {
					$sql		=	'SELECT id FROM #__users u'
								.	' JOIN #__user_usergroup_map m ON ( u.id = m.user_id )'
								.	' WHERE m.group_id = ' . (int) $this->usergroup
								;
				} else {
					$sql		=	'SELECT id FROM #__users'
								.	' WHERE gid = ' . (int) $this->usergroup
								;
				}
				$this->_db->setQuery( $sql );
				$userIdList		=	$this->_db->loadResultArray();
				break;

			case 'subscription':
				$statuses		=	$this->from_sub_status;
				foreach (array_keys( $statuses ) as $k ) {
					$statuses[$k]	=	$this->_db->Quote( $statuses[$k][0] );
				}
				$sql			=	'SELECT s.user_id FROM #__cbsubs_subscriptions s'
								.	' JOIN #__users u ON ( u.id = s.user_id AND u.block = 0 )'
								.	' JOIN #__comprofiler c ON ( c.id = s.user_id AND c.confirmed = 1 AND c.approved = 1 )'
								.	' WHERE s.plan_id = ' . (int) $this->from_plan
								.	' AND s.status IN (' . implode( ',', $statuses ) . ')'
								;
				$this->_db->setQuery( $sql );
				$userIdList		=	$this->_db->loadResultArray();
				break;

			default:
				$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("Import type not implemented!");
				return false;
				break;
		}

		if ( count( $userIdList ) == 0 ) {
			$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("No user to import");
			return false;
		}
		$plansMgr					=&	cbpaidPlansMgr::getInstance();

		$offset			=	$_CB_framework->getCfg( 'offset' ) * 3600;

		if ( $this->import_type != 'file_uid_plan_exp' ) {
			$plan					=&	$plansMgr->loadPlan( (int) $this->plan );
	
			$this->date				=	dateConverter( $this->date, $ueConfig['date_format'], 'Y-m-d' );
			$subscriptionTime		=	(int) ( $plan->strToTime( $this->date ) - $offset );
	
			foreach( $userIdList as $key => $value ) {
				if ( ! is_numeric( $value ) ) {
					$this->_error	=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("non-numeric userid value: ") . str_replace( "\n", ' ', htmlspecialchars( $value ) );
					return false;
				}
				$userIdList[$key]	=	(int) $value;
			}
		} else {
			$plan					=	null;
			$subscriptionTime		=	null;
		}

		$this->_db->setQuery( "SELECT u.id, u.username FROM #__comprofiler c, #__users u WHERE c.id=u.id AND u.block = 0 AND c.approved = 1 AND c.confirmed = 1 AND c.id IN (" . implode( ',', $userIdList ) . ")" );
		$users						=	$this->_db->loadObjectList('id');
		if ( count( $userIdList ) != count( $users ) ) {
			if ( is_array( $users ) ) {
				foreach ( $users as $u ) {
					$keys			=	array_keys( $userIdList, $u->id );
					unset( $userIdList[$keys[0]] );
					unset( $planStateDate[(int) $u->id] );
				}
			}
			$idList					=	implode( ', ', $userIdList );
			$this->_error			=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("Not all userId exist, are active (confirmed, approved and enabled) ! innexistant or inactive ids: ") . $idList;
			return false;
		}

		$this->_db->setQuery( "SELECT DISTINCT user_id FROM #__cbsubs_subscriptions WHERE user_id IN (" . implode( ',', $userIdList ) . ")"			//FIXME: API needed to load the right type of subscriptions
							. " ORDER BY user_id" );
		$usersSubscribed			=	$this->_db->loadResultArray();

		$incompatibleUsersSubs		=	array();

		if ( $this->import_type != 'file_uid_plan_exp' ) {
			foreach ( $users as $user ) {
				@set_time_limit( 60 );

				$incompatible						=	false;
				if ( in_array( $user->id, $usersSubscribed ) ) {
					if ( $plan->get( 'exclusive' ) && ( $plan->get( 'item_type' ) == 'usersubscription' ) ) {
						$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
						$subscriptions				=	$paidUserExtension->getUserSubscriptions( null, false );
		 				foreach ( $subscriptions as $s ) {
		 					if ( ( $s->parent_plan == $plan->get( 'parent' ) ) && $s->checkIfValid() ) {
		 						$sPlan				=	$s->getPlan();
		 						if ( $sPlan->get( 'exclusive' ) && ( $sPlan->get( 'item_type' ) == 'usersubscription' ) ) {
			 						// check if any other exclusive subscription with same parent plan is active:
			 						$incompatible	=	true;
			 						break;
		 						}
		 					}
		 				}
					}
				}
				if ( ! $incompatible ) {
					if ( $plan->get( 'parent' ) ) {
						$plansMgr		=&	cbpaidPlansMgr::getInstance();
						$parentPlan		=	$plansMgr->loadPlan( $plan->get( 'parent' ) );
						$parentSub		=	$parentPlan->loadLatestSomethingOfUser( $user->id, null );
						if ( ! $parentSub ) {
							$incompatible	=	true;
						}
					}
				}
 				if ( $incompatible ) {
 					if ( ! in_array( $user->id, $incompatibleUsersSubs ) ) {
 						$incompatibleUsersSubs[]	=	$user->id;
 					}
					continue;
 				}
				if ( ! $this->dryrun ) {
					$userFull			=	CBuser::getUserDataInstance( $user->id );
					$this->createSomething( $plan, $userFull, $this->state, $subscriptionTime );
					CBuser::unsetUsersNotNeeded( array( (int) $user->id ) );
				}
			}
		} else {
			foreach ( $users as $user ) {
				@set_time_limit( 60 );
				
				foreach ( $planStateDate[(int) $user->id] as $psd ) {
					$plan				=&	$plansMgr->loadPlan( (int) $psd['plan'] );
					$status				=	$psd['status'];
					$subscriptionTime	=	$plan->strToTime( $psd['date'] ) - $offset;

					$incompatible						=	false;
					if ( in_array( $user->id, $usersSubscribed ) ) {
						if ( $plan->get( 'exclusive' ) && ( $plan->get( 'item_type' ) == 'usersubscription' ) ) {
							$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
							$subscriptions				=	$paidUserExtension->getUserSubscriptions( null, false );
			 				foreach ( $subscriptions as $s ) {
			 					if ( ( $s->parent_plan == $plan->get( 'parent' ) ) && $s->checkIfValid() ) {
			 						$sPlan				=	$s->getPlan();
			 						if ( $sPlan->get( 'exclusive' ) && ( $sPlan->get( 'item_type' ) == 'usersubscription' ) ) {
				 						// check if any other exclusive subscription with same parent plan is active:
				 						$incompatible	=	true;
				 						break;
			 						}
			 					}
			 				}
						}
					}
					if ( ! $incompatible ) {
						if ( $plan->get( 'parent' ) ) {
							$plansMgr		=&	cbpaidPlansMgr::getInstance();
							$parentPlan		=	$plansMgr->loadPlan( $plan->get( 'parent' ) );
							$parentSub		=	$parentPlan->loadLatestSomethingOfUser( $user->id, null );
							if ( ! $parentSub ) {
								$incompatible	=	true;
							}
						}
					}
					if ( $incompatible ) {
	 					if ( ! in_array( $user->id, $incompatibleUsersSubs ) ) {
	 						$incompatibleUsersSubs[]	=	$user->id;
	 					}
	 					continue;
	 				}
					if ( ! $this->dryrun ) {
						$userFull			=	CBuser::getUserDataInstance( $user->id );
						$this->createSomething( $plan, $userFull, $status, $subscriptionTime );
						CBuser::unsetUsersNotNeeded( array( (int) $user->id ) );
					}
				}
			}
		}
		if ( ( count( $userIdList ) > 0 ) && ( count( $incompatibleUsersSubs ) == 0 ) ) {
			$resultText				=	CBPTXT::T("Success");
		} elseif ( ( count( $userIdList ) > count( $incompatibleUsersSubs ) ) ) {
			$resultText				=	CBPTXT::T("Partial Success");
		} elseif ( ( count( $userIdList ) == count( $incompatibleUsersSubs ) ) ) {
			$resultText				=	CBPTXT::T("Import failed");
		} else {
			$resultText				=	CBPTXT::T("Unknow Result");
		}
		$return						.=	'<h1>' . $resultText . ( $this->dryrun ? ' [' . CBPTXT::T("DRY-RUN - NO REAL SUBSCRIPTION") . ']' : '' ) .  ':</h1>';

		if ( count( $incompatibleUsersSubs ) > 0 ) {
			$idList					=	implode( ', ', $incompatibleUsersSubs );
			$return					.=	'<p>' . CBPTXT::T("Some users have already subscriptions: user ids: ") . $idList. '</p>';
			// $this->_error		=	CBPTXT::T("Importer") . ' - ' . CBPTXT::T("error:") . ' ' . CBPTXT::T("Some users have already subscriptions: user ids: ") . $idList;
			// return false;
		}

		if ( $this->import_type != 'file_uid_plan_exp' ) {
			$return .= '<p>' . sprintf( CBPTXT::T("%d users subscribed to plan: %s , with state: %s"), ( count( $userIdList ) - count( $incompatibleUsersSubs ) ), $plan->get( 'name' ), CBPTXT::T( $this->_states[$this->state] ) ) . '</p>';
			if ( ( count( $userIdList ) - count( $incompatibleUsersSubs ) ) > 0 ) {
				$return .= '<p>' . CBPTXT::T("Users subscribed (usernames):") . '</p>';
				$return .= '<p>';
				foreach ( $users as $user ) {
					if ( ! in_array( $user->id, $incompatibleUsersSubs ) ) {
						$return .= $user->username . ' ';
					}
				}
				$return .= '</p>';
			}
		} else {
			$return .= '<p>' . sprintf( CBPTXT::T("%d users subscribed"), ( count( $userIdList ) - count( $incompatibleUsersSubs ) ) ) . '</p>';
			if ( ( count( $userIdList ) - count( $incompatibleUsersSubs ) ) > 0 ) {
				$return .= '<p>' . CBPTXT::T("Users subscribed (usernames):") . '</p>';
				foreach ( $users as $user ) {
					if ( ! in_array( $user->id, $incompatibleUsersSubs ) ) {
						$return .= '<p>' . $user->username . ' ' . CBPTXT::T("to") . ' ';
						foreach ( $planStateDate[(int) $user->id] as $psd ) {
							$plan				=&	$plansMgr->loadPlan( (int) $psd['plan'] );
							$status				=	$psd['status'];
							$return				.=	sprintf(CBPTXT::T("plan: %s , with state: %s") . ' ', $plan->get( 'name' ), CBPTXT::T( $this->_states[$status] ) );
						}
					}
				}
				$return .= '</p>';
			}
		}
		if ( count( $incompatibleUsersSubs ) > 0 ) {
			$return .= '<p>' . CBPTXT::T("Following Users could not be subscribed (usernames) because either: (A) an exclusive active subscription exists that would conflict with the imported user subscription, or: (B) it is a children plan but the parent plan subscription does not exist:") . '</p>';
			$return .= '<p>';
			foreach ( $incompatibleUsersSubs as $uid ) {
				if ( isset( $users[$uid] ) ) {
					$return .= $users[$uid]->username . ' ';
				}
			}
			$return .= '</p>';
		}
		$this->_resultOfStore	=	$return;
		return true;
	}
	/**
	 * After store() this function may be called to get a result information message to display. Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore( ) {
		return $this->_resultOfStore;
	}
	/**
	 * Creates a something of a plan
	 *
	 * @param  cbpaidProduct  $plan
	 * @param  UserTable      $user
	 * @param  string         $state
	 * @param  int            $subscriptionTime
	 */
	protected function createSomething( $plan, $user, $state, $subscriptionTime ) {
		global $_CB_database;

		$replacesSubId					=	null;
		$reason							=	'N';

		if ( ( $state == 'X' ) && ( $plan->get( 'item_type' ) != 'usersubscription' ) ) {
			// safeguard for donations and merchandises: they do not expire!, so set them as cencelled if import is as expired:
			$state						=	'C';
		}
/*
		if ( $plan->get( 'exclusive' ) && ( $plan->get( 'item_type' ) == 'usersubscription' ) && ( $state == 'A' ) ) {
			$incompatible				=	false;
			$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
			$subscriptions				=	$paidUserExtension->getUserSubscriptions( null, false );
 			foreach ( $subscriptions as $s ) {
 				if ( ( $s->parent_plan == $plan->get( 'parent' ) ) && $s->checkIfValid() ) {
 					$sPlan				=	$s->getPlan();
 					if ( $sPlan->get( 'exclusive' ) && ( $sPlan->get( 'item_type' ) == 'usersubscription' ) ) {
 						// This other exclusive user subscription with same parent plan is active: then it is an upgrade from that subscription
 						$replacesSubId	=	array( $s->plan_id, $s->id );
 						$reason			=	'U';
 						break;
 					}
 				}
 			}
 			if ( $incompatible ) {
				continue;
 			}
		}
*/
		$parentSubId		=	null;
		if ( $plan->get( 'parent' ) ) {
			$plansMgr		=&	cbpaidPlansMgr::getInstance();
			$parentPlan		=	$plansMgr->loadPlan( $plan->get( 'parent' ) );
			$parentSub		=	$parentPlan->loadLatestSomethingOfUser( $user->id, null );
			if ( $parentSub ) {
				$parentSubId =	array( $parentSub->plan_id, $parentSub->id );
			}
		}

		$postdata			=	array();
		$price				=	null;
		$recurringPrice		=	null;
		$subscription		=	$plan->createProductThing( $user, $postdata, $reason, ( $state == 'A' ? 'I' : $state ), $replacesSubId, null, $subscriptionTime, $price, $recurringPrice, $parentSubId );
	
		if ( $state == 'A' ) {
			$subscription->activate( $user, $subscriptionTime );
		} elseif ( $state == 'X' ) {
			/** @var cbpaidUsersubscriptionRecord $subscription */
			if ( is_callable( array( $subscription, 'computeExpiryTimeIfActivatedNow' ) ) ) {
				// Sets expiry_date for expired user subscriptions:
				$expiry		=	$subscription->computeExpiryTimeIfActivatedNow( $subscriptionTime );
				$subscription->expiry_date	=	( $expiry === null ? '0000-00-00 00:00:00' : date( 'Y-m-d H:i:s', $expiry ) );
				if ( ! $subscription->store() ) {
					trigger_error( 'subscription store error:'.htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_ERROR );
				}
			}
		}
	}
}
