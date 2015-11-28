<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveActivity
{

	/**
	 * render frontend group activity
	 *
	 * @param CB\Plugin\Activity\Table\ActivityTable $row
	 * @param string                                 $title
	 * @param string                                 $message
	 * @param CB\Plugin\Activity\Activity            $stream
	 * @param GroupTable                             $group
	 * @param cbgjPlugin                             $plugin
	 * @return string
	 */
	static function showActivity( $row, &$title, &$message, $stream, $group, $plugin )
	{
		global $_CB_framework;

		initToolTip();

		$message				=	null;

		switch( $row->get( 'subtype' ) ) {
			case 'group.join':
				$title			=	CBTxt::T( 'joined a group' );
				break;
			case 'group.leave':
				$title			=	CBTxt::T( 'left a group' );
				break;
			case 'group':
				$title			=	CBTxt::T( 'created a group' );
				break;
		}

		$user					=	CBuser::getMyUserDataInstance();
		$isModerator			=	CBGroupJive::isModerator( $user->get( 'id' ) );
		$groupOwner				=	( $user->get( 'id' ) == $group->get( 'user_id' ) );
		$userStatus				=	CBGroupJive::getGroupStatus( $user, $group );

		$return					=	'<div class="gjActivity">'
								.		'<div class="gjGroupCanvas gjPageHeader border-default">'
								.			'<div class="gjPageHeaderCanvas">'
								.				'<div class="gjPageHeaderCanvasBackground">'
								.					$group->canvas()
								.				'</div>'
								.				'<div class="gjPageHeaderCanvasLogo">'
								.					$group->logo( false, true, true )
								.				'</div>';

		if ( $isModerator || $groupOwner || ( ( ! $groupOwner ) && ( ( $userStatus === null ) || ( $userStatus === 0 ) || ( $userStatus >= 1 ) ) ) ) {
			$return				.=				'<div class="gjPageHeaderCanvasButtons text-right">';

			if ( $isModerator && ( $group->get( 'published' ) == -1 ) && $plugin->params->get( 'groups_create_approval', 0 ) ) {
				$return			.=					' <span class="gjPageHeaderCanvasButton">'
								.						'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'publish', 'id' => (int) $group->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\';" class="gjButton gjButtonApprove btn btn-xs btn-success">' . CBTxt::T( 'Approve' ) . '</button>'
								.					'</span>';
			} elseif ( ! $groupOwner ) {
				if ( $userStatus === null ) {
					$return		.=					' <span class="gjPageHeaderCanvasButton">'
								.						( $group->get( '_invite_id' ) ? '<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to reject all invites to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'reject', 'id' => (int) $group->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\'; })" class="gjButton gjButtonReject btn btn-xs btn-danger">' . CBTxt::T( 'Reject' ) . '</button> ' : null )
								.						'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'join', 'id' => (int) $group->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\';" class="gjButton gjButtonJoin btn btn-xs btn-success">' . ( $group->get( '_invite_id' ) ? CBTxt::T( 'Accept Invite' ) : CBTxt::T( 'Join' ) ) . '</button>'
								.					'</span>';
				} elseif ( $userStatus === 0 ) {
					$return		.=					' <span class="gjPageHeaderCanvasButton">'
								.						'<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel your pending join request to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'cancel', 'id' => (int) $group->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\'; })" class="gjButton gjButtonCancel btn btn-xs btn-danger">' . CBTxt::T( 'Cancel' ) . '</button> '
								.						'<span class="gjButton gjButtonPending btn btn-xs btn-warning disabled">' . CBTxt::T( 'Pending Approval' ) . '</span>'
								.					'</span>';
				}
			}

			$return				.=				'</div>';
		}

		$return					.=			'</div>'
								.			'<div class="gjPageHeaderBar border-default">'
								.				'<div class="gjPageHeaderBarTitle text-primary">'
								.					'<strong><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $group->get( 'id' ) ) ) . '">' . htmlspecialchars( CBTxt::T( $group->get( 'name' ) ) ) . '</a></strong>'
								.				'</div>'
								.				'<div class="gjPageHeaderBarCounters text-muted small">';

		if ( $group->get( 'category' ) ) {
			$return				.=					'<span class="gjPageHeaderBarCounter"><span class="gjGroupCategoryIcon fa-before fa-folder">'
								.						' <a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'categories', 'func' => 'show', 'id' => (int) $group->get( 'category' ) ) ) . '">' . CBTxt::T( $group->category()->get( 'name' ) ) . '</a>'
								.					'</span></span>';
		}

		$return					.=					' <span class="gjPageHeaderBarCounter"><span class="gjGroupTypeIcon fa-before fa-globe"> ' . $group->type() . '</span></span>'
								.					' <span class="gjPageHeaderBarCounter"><span class="gjGroupUsersIcon fa-before fa-user"> ' . CBTxt::T( 'GROUP_USERS_COUNT', '%%COUNT%% User|%%COUNT%% Users', array( '%%COUNT%%' => (int) $group->get( '_users', 0 ) ) ) . '</span></span>'
								.				'</div>'
								.				( $group->get( 'description' ) ? ' <div class="gjPageHeaderBarDescription">' . cbTooltip( 1, CBTxt::T( $group->get( 'description' ) ), CBTxt::T( $group->get( 'name' ) ), 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null )
								.			'</div>'
								.		'</div>'
								.	'</div>';

		return $return;
	}
}