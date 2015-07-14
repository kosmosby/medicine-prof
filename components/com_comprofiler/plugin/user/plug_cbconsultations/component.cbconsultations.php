<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;
use CBLib\Application\Application;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class CBplug_cbconsultations
 * CB Components-type class for CB consultations
 */
class CBplug_cbconsultations extends cbPluginHandler
{
	/**
	 * @param  TabTable   $tab       Current tab
	 * @param  UserTable  $user      Current user
	 * @param  int        $ui        1 front, 2 admin UI
	 * @param  array      $postdata  Raw unfiltred POST data
	 * @return string                HTML
	 */
	public function getCBpluginComponent( /** @noinspection PhpUnusedParameterInspection */ $tab, $user, $ui, $postdata )
	{
		global $_CB_framework;

		outputCbJs( 1 );
		outputCbTemplate( 1 );

		$plugin					=	cbconsultationsClass::getPlugin();
		$model					=	cbconsultationsClass::getModel();
		$action					=	$this->input( 'action', null, GetterInterface::STRING );
		$function				=	$this->input( 'func', null, GetterInterface::STRING );
		$id						=	$this->input( 'id', null, GetterInterface::INT );
		$user					=	CBuser::getUserDataInstance( $_CB_framework->myId() );

		$tab					=	new TabTable();

		$tab->load( array( 'pluginid' => (int) $plugin->id ) );

		$profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ), false, 'cbconsultationsTab' );

		if ( ! ( $tab->enabled && Application::MyUser()->canViewAccessLevel( $tab->viewaccesslevel ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		ob_start();
		switch ( $action ) {
			case 'consultations':
				switch ( $function ) {
					case 'new':
						$this->showconsultationEdit( null, $user, $model, $plugin );
						break;
					case 'edit':
						$this->showconsultationEdit( $id, $user, $model, $plugin );
						break;
					case 'save':
						cbSpoofCheck( 'plugin' );
						$this->saveconsultationEdit( $id, $user, $model, $plugin );
						break;
					case 'publish':
						$this->stateconsultation( 1, $id, $user, $model, $plugin );
						break;
					case 'unpublish':
						$this->stateconsultation( 0, $id, $user, $model, $plugin );
						break;
					case 'delete':
						$this->deleteconsultation( $id, $user, $model, $plugin );
						break;
					case 'show':
					default:
						if ( $model->type != 2 ) {
							cbRedirect( cbconsultationsModel::getUrl( (int) $id, false ) );
						} else {
							$this->showconsultation( $id, $user, $model, $plugin );
						}
						break;
				}
				break;
			default:
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
				break;
		}
		$html		=	ob_get_contents();
		ob_end_clean();

		$class		=	$plugin->params->get( 'general_class', null );

		$return		=	'<div id="cbconsultations" class="cbconsultations' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
					.		'<div id="cbconsultationsInner" class="cbconsultationsInner">'
					.			$html
					.		'</div>'
					.	'</div>';

		echo $return;
	}

	/**
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	public function showconsultation( $id, $user, $model, $plugin )
	{
		global $_CB_framework;

		$row					=	new cbconsultationsconsultationTable();

		$profileUrl				=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' );

		if ( ! ( ( (int) $id ) && $row->load( (int) $id ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		if ( ! ( ( $row->get( 'user' ) == $user->get( 'id' ) )
				|| ( Application::MyUser()->canViewAccessLevel( $row->get( 'access' ) ) && $row->get( 'published' ) )
			    || Application::User( (int) $user->get( 'id' ) )->isGlobalModerator()
			   )
		)
		{
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		cbconsultationsClass::getTemplate( 'consultation_show' );

    $bids = null;
    if($row->get('user')==$user->get('id')){
        $database = &JFactory::getDBO();
        $database->setQuery("SELECT u.name, u.id as user_id, u.email, b.bid_price, b.modified as bid_date
                         FROM #__bid_auctions a
                         INNER JOIN #__bids b on b.auction_id=a.id
                         INNER JOIN #__users u ON u.id=b.userid
                         WHERE a.consultationid=".$database->quote($id)."
                         ORDER BY bid_date DESC");
        $bids = $database->loadObjectList();
    }
		HTML_cbconsultationsconsultation::showconsultation( $row, $user, $model, $plugin, $bids );
	}

	/**
	 * @param  null|int     $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 * @param  null|string  $message
	 * @param  null|string  $messageType
	 */
	public function showconsultationEdit( $id, $user, $model, $plugin, $message = null, $messageType = 'error' )
	{
		global $_CB_framework;

		$consultationLimit						=	(int) $plugin->params->get( 'consultation_limit', null );
		$consultationMode						=	$plugin->params->get( 'consultation_mode', 1 );
		$cbModerator					=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$row							=	new cbconsultationsconsultationTable();

		$canAccess						=	false;

		if ( $row->load( (int) $id ) ) {
			if ( ! $row->get( 'id' ) ) {
				if ( $cbModerator ) {
					$canAccess			=	true;
				} elseif ( $user->get( 'id' ) && Application::MyUser()->canViewAccessLevel( $plugin->params->get( 'consultation_create_access', 2 ) ) ) {
					if ( ( ! $consultationLimit ) || ( $consultationLimit && ( cbconsultationsModel::getconsultationsTotal( null, $user, $user, $plugin ) < $consultationLimit ) ) ) {
						$canAccess		=	true;
					}
				}
			} elseif ( $cbModerator || ( $row->get( 'user' ) == $user->get( 'id' ) ) ) {
				$canAccess				=	true;
			}
		}

		$profileUrl						=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' );

		if ( $canAccess ) {
			cbconsultationsClass::getTemplate( 'consultation_edit' );

			$input						=	array();

			$publishedTooltip			=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select publish status of the consultation. Unpublished consultations will not be visible to the public.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['published']			=	moscomprofilerHTML::yesnoSelectList( 'published', 'class="form-control"' . ( $publishedTooltip ? ' ' . $publishedTooltip : null ), (int) $this->input( 'post/published', $row->get( 'published', ( $cbModerator || ( ! $plugin->params->get( 'consultation_approval', 0 ) ) ? 1 : 0 ) ), GetterInterface::INT ) );

			$categoryTooltip			=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select consultation category. Select the category that best describes your consultation.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$listCategory				=	cbconsultationsModel::getCategoriesList();
			$input['category']			=	moscomprofilerHTML::selectList( $listCategory, 'category', 'class="form-control"' . ( $categoryTooltip ? ' ' . $categoryTooltip : null ), 'value', 'text', $this->input( 'post/category', $row->get( 'category' ), GetterInterface::STRING ), 1, false, false );

			$accessTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select access to consultation; all groups above that level will also have access to the consultation.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$listAccess					=	Application::CmsPermissions()->getAllViewAccessLevels( true, Application::MyUser() );
			$input['access']			=	moscomprofilerHTML::selectList( $listAccess, 'access', 'class="form-control"' . ( $accessTooltip ? ' ' . $accessTooltip : null ), 'value', 'text', (int) $this->input( 'post/access', $row->get( 'access', $plugin->params->get( 'consultation_access_default', 1 ) ), GetterInterface::INT ), 1, false, false );

			$titleTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input consultation title. This is the title that will distinguish this consultation from others. Suggested to input something unique and intuitive.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['title']				=	'<input type="text" id="title" name="title" value="' . htmlspecialchars( $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) ) . '" class="required form-control" size="30"' . ( $titleTooltip ? ' ' . $titleTooltip : null ) . ' />';

			if ( in_array( $consultationMode, array( 1, 2 ) ) ) {
				$consultationIntro				=	$_CB_framework->displayCmsEditor( 'consultation_intro', $this->input( 'post/consultation_intro', $row->get( 'consultation_intro' ), GetterInterface::HTML ), 400, 200, 40, 7 );

				$input['consultation_intro']	=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input HTML supported consultation intro contents. Suggested to use minimal but well formatting for easy readability.' ), null, null, null, $consultationIntro, null, 'style="display:block;"' );
			}

			if ( in_array( $consultationMode, array( 1, 3 ) ) ) {
				$consultationFull				=	$_CB_framework->displayCmsEditor( 'consultation_full', $this->input( 'post/consultation_full', $row->get( 'consultation_full' ), GetterInterface::HTML ), 400, 200, 40, 7 );

				$input['consultation_full']		=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input HTML supported consultation contents. Suggested to use minimal but well formatting for easy readability.' ), null, null, null, $consultationFull, null, 'style="display:block;"' );
			}

			$userTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input owner of consultation as single integer user_id.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['user']				=	'<input type="text" id="user" name="user" value="' . (int) ( $cbModerator ? $this->input( 'post/user', $row->get( 'user', $user->get( 'id' ) ), GetterInterface::INT ) : $user->get( 'id' ) ) . '" class="digits required form-control" size="4"' . ( $userTooltip ? ' ' . $userTooltip : null ) . ' />';

			if ( $message ) {
				$_CB_framework->enqueueMessage( $message, $messageType );
			}

			HTML_cbconsultationsconsultationEdit::showconsultationEdit( $row, $input, $user, $model, $plugin );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	/**
	 * @param  null|int     $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function saveconsultationEdit( $id, $user, $model, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$consultationLimit					=	(int) $plugin->params->get( 'consultation_limit', null );
		$cbModerator				=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$row						=	new cbconsultationsconsultationTable();

		$canAccess					=	false;

		if ( $row->load( (int) $id ) ) {
			if ( ! $row->get( 'id' ) ) {
				if ( $cbModerator ) {
					$canAccess		=	true;
				} elseif ( $user->get( 'id' ) && Application::MyUser()->canViewAccessLevel( $plugin->params->get( 'consultation_create_access', 2 ) ) ) {
					if ( ( ! $consultationLimit ) || ( $consultationLimit && ( cbconsultationsModel::getconsultationsTotal( null, $user, $user, $plugin ) < $consultationLimit ) ) ) {
						$canAccess	=	true;
					}
				}
			} elseif ( $cbModerator || ( $row->get( 'user' ) == $user->get( 'id' ) ) ) {
				$canAccess			=	true;
			}
		}

		$profileUrl					=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' );

		if ( $canAccess ) {
			if ( $plugin->params->get( 'consultation_captcha', 0 ) && ( ! $row->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$_PLUGINS->loadPluginGroup( 'user' );

				$_PLUGINS->trigger( 'onCheckCaptchaHtmlElements', array() );

				if ( $_PLUGINS->is_errors() ) {
					$row->setError( CBTxt::T( $_PLUGINS->getErrorMSG() ) );
				}
			}

			$new					=	( $row->get( 'id' ) ? false : true );

			if ( ! $row->bind( $_POST ) ) {
				$this->showconsultationEdit( $id, $user, $model, $plugin, CBTxt::T( 'consultation_FAILED_TO_BIND_ERROR_ERROR', 'consultation failed to bind! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}

			if ( ! $row->check() ) {
				$this->showconsultationEdit( $id, $user, $model, $plugin, CBTxt::T( 'consultation_FAILED_TO_VALIDATE_ERROR_ERROR', 'consultation failed to validate! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}

			if ( $row->getError() || ( ! $row->store() ) ) {
				$this->showconsultationEdit( $id, $user, $model, $plugin, CBTxt::T( 'consultation_FAILED_TO_SAVE_ERROR_ERROR', 'consultation failed to save! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}
                        //Creating the Auction
                        $saveAuctionResult = $this->saveAuction($row->get('id'),
                            $row->get('user'),
                            $new,
                            $row->get('title'),
                            $this->input( 'datetime', null, GetterInterface::STRING ),
                            $row->get('published'));
			if ( $saveAuctionResult!=null ) {
				$this->showconsultationEdit( $id, $user, $model, $plugin, CBTxt::T( 'consultation_FAILED_TO_SAVE_ERROR_ERROR', 'consultation failed to save! Error: [error]', array( '[error]' => $saveAuctionResult ) ) ); return;
			}

			if ( $new && ( ! $row->get( 'published' ) ) && $plugin->params->get( 'approval_notify', 1 ) && ( ! $cbModerator ) ) {
				$cbUser				=	CBuser::getInstance( (int) $row->get( 'user' ), false );

				$extraStrings		=	array(	'site_name' => $_CB_framework->getCfg( 'sitename' ),
												'site' => '<a href="' . $_CB_framework->getCfg( 'live_site' ) . '">' . $_CB_framework->getCfg( 'sitename' ) . '</a>',
												'consultation_id' => (int) $row->get( 'id' ),
												'consultation_title' => $row->get( 'title' ),
												'consultation_intro' => $row->get( 'consultation_intro' ),
												'consultation_full' => $row->get( 'consultation_full' ),
												'consultation_created' => $row->get( 'consultation_created' ),
												'consultation_user' => (int) $row->get( 'user' ),
												'consultation_url' => cbconsultationsModel::getUrl( $row ),
												'consultation_tab_url' => $_CB_framework->viewUrl( 'userprofile', false, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbconsultationsTab' ) ),
												'user_name' => $cbUser->getField( 'formatname', null, 'html', 'none', 'profile' ),
												'user' => '<a href="' . $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ) ) ) . '">' . $cbUser->getField( 'formatname', null, 'html', 'none', 'profile' ) . '</a>'
											);
				$subject			=	$cbUser->replaceUserVars( CBTxt::T( 'consultations - New consultation Created!' ), false, true, $extraStrings, false );
				$message			=	$cbUser->replaceUserVars( CBTxt::T( '[user] created [consultation_title] and requires <a href="[consultation_tab_url]">approval</a>!' ), false, true, $extraStrings, false );

				$notifications		=	new cbNotification();

				$notifications->sendToModerators( $subject, $message, false, 1 );
			}

			cbRedirect( $profileUrl, CBTxt::T( 'consultation saved successfully!' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

    private function saveAuction($consultationId, $userId,  $isNewAuction, $title, $startTime, $publishStatus){
        $database = &JFactory::getDBO();
        $now = new DateTime();
        date_default_timezone_set('UTC');
        $timestamp = DateTime::createFromFormat('Y/m/d H:i', $startTime, new DateTimeZone('UTC'))->getTimestamp() - 24*60*60 * 5;
        if($timestamp < $now->getTimestamp()){
            return "Invalid Consutation date. Consultation should be scheduled at least 5 days before.";
        }
        $dateStr = date("Y-m-d H:i:s", $timestamp);
        if($isNewAuction){
        $database->setQuery("INSERT INTO #__bid_auctions
            (userid, published, title, cat, auction_type,automatic, initial_price, min_increase, quantity, start_date, end_date, consultationid)
            VALUES
            (".$database->quote($userId).", ".($publishStatus?"1":"0").", ".$database->quote($title).", 49, 1, 1, 50, 10, 1, UTC_TIMESTAMP(), ".$database->quote($dateStr).",".$database->quote($consultationId).")");
        }else{
            $database->setQuery("UPDATE #__bid_auctions
                SET title=".$database->quote($title).
                " , end_date=".$database->quote($dateStr).",
                published=".($publishStatus?"1":"0")."
                WHERE userid=".
                $database->quote($userId). " AND consultationid=".$database->quote($consultationId));
        }
        $database->query();
        return null;
    }

    private function deleteAuction($consultationId){
        $database = &JFactory::getDBO();
        $database->setQuery("DELETE FROM #__bid_auctions WHERE consultationid=".$database->quote($consultationId));
        $database->query();
    }
    private function publishAuction($consultationId, $userId, $publishStatus){
        $database = &JFactory::getDBO();
        $database->setQuery("UPDATE #__bid_auctions SET published=".($publishStatus?"1":"0").
            " WHERE  userid=".
            $database->quote($userId). " AND consultationid=".$database->quote($consultationId));
        $database->query();
    }

	/**
	 * @param  int          $state
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function stateconsultation( $state, $id, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin )
	{
		global $_CB_framework;

		$row						=	new cbconsultationsconsultationTable();

		$canAccess					=	false;

		if ( $row->load( (int) $id ) ) {
			if ( $row->get( 'id' ) && ( Application::User( (int) $user->get( 'id' ) )->isGlobalModerator() || ( ( $row->get( 'user' ) == $user->get( 'id' ) ) && ( ! $plugin->params->get( 'consultation_approval', 0 ) ) ) ) ) {
				$canAccess			=	true;
			}
		}

		$profileUrl					=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' );

		if ( $canAccess ) {
			$_POST['published']		=	(int) $state;

			if ( ! $row->bind( $_POST ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'consultation_STATE_FAILED_TO_BIND_ERROR_ERROR', 'consultation state failed to bind! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( ! $row->check() ) {
				cbRedirect( $profileUrl, CBTxt::T( 'consultation_STATE_FAILED_TO_VALIDATE_ERROR_ERROR', 'consultation state failed to validate! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( $row->getError() || ( ! $row->store() ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'consultation_STATE_FAILED_TO_SAVE_ERROR_ERROR', 'consultation state failed to save! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

            $this->publishAuction($id, $user->get('id'), $state);

			cbRedirect( $profileUrl, CBTxt::T( 'consultation state saved successfully!' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	/**
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function deleteconsultation( $id, $user, /** @noinspection PhpUnusedParameterInspection */ $model, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework;

		$row				=	new cbconsultationsconsultationTable();

		$canAccess			=	false;

		if ( $row->load( (int) $id ) ) {
			if ( $row->get( 'id' ) && ( ( $row->get( 'user' ) == $user->get( 'id' ) ) || Application::User( (int) $user->get( 'id' ) )->isGlobalModerator() ) ) {
				$canAccess	=	true;
			}
		}

		$profileUrl			=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' );

		if ( $canAccess ) {
			if ( ! $row->canDelete() ) {
				cbRedirect( $profileUrl, CBTxt::T( 'consultation_FAILED_TO_DELETE_ERROR_ERROR', 'consultation failed to delete! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( ! $row->delete( (int) $id ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'consultation_FAILED_TO_DELETE_ERROR_ERROR', 'consultation failed to delete! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

            $this->deleteAuction($id);

			cbRedirect( $profileUrl, CBTxt::T( 'consultation deleted successfully!' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}
}
