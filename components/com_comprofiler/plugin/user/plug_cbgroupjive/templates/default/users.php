<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveUsers
{

	/**
	 * render frontend group users
	 *
	 * @param CB\Plugin\GroupJive\Table\UserTable[] $rows
	 * @param cbPageNav                             $pageNav
	 * @param bool                                  $searching
	 * @param array                                 $input
	 * @param GroupTable                            $group
	 * @param UserTable                             $user
	 * @param CBplug_cbgroupjive                    $plugin
	 * @return string
	 */
	static function showUsers( $rows, $pageNav, $searching, $input, $group, $user, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		initToolTip();

		$isModerator					=	CBGroupJive::isModerator( $user->get( 'id' ) );
		$isOwner						=	( $user->get( 'id' ) == $group->get( 'user_id' ) );
		$userStatus						=	CBGroupJive::getGroupStatus( $user, $group );
		$canSearch						=	( $plugin->params->get( 'groups_users_search', 0 ) && ( $searching || $pageNav->total ) );
		$return							=	null;

		$_PLUGINS->trigger( 'gj_onBeforeDisplayUsers', array( &$return, &$rows, $group, $user ) );

		$return							.=	'<div class="gjGroupUsers">'
										.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $group->get( 'id' ) ) ) . '" method="post" name="gjGroupUsersForm" id="gjGroupUsersForm" class="gjGroupUsersForm">';

		if ( $canSearch ) {
			$return						.=			'<div class="gjHeader gjGroupUsersHeader row">'
										.				'<div class="col-sm-offset-8 col-sm-4 text-right">'
										.					'<div class="input-group">'
										.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
										.						$input['search']
										.					'</div>'
										.				'</div>'
										.			'</div>';
		}

		$return							.=			'<div class="gjGroupUsersRows">';

		if ( $rows ) foreach ( $rows as $row ) {
			$canModerate				=	( ( $userStatus >= 2 ) && ( $userStatus > $row->get( 'status' ) ) && ( $user->get( 'id' ) != $row->get( 'user_id' ) ) );

			$counters					=	array();
			$content					=	null;
			$menu						=	array();

			$_PLUGINS->trigger( 'gj_onDisplayUser', array( &$row, &$counters, &$content, &$menu, $group, $user ) );

			$cbUser						=	CBuser::getInstance( (int) $row->get( 'user_id' ), false );

			switch ( (int) $row->get( 'status' ) ) {
				case -1:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-ban text-danger"></span> ';
					break;
				case 0:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-warning text-warning"></span> ';
					break;
				case 1:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-user"></span> ';
					break;
				case 2:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-gavel text-success"></span> ';
					break;
				case 3:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-gavel text-success"></span> ';
					break;
				case 4:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-star text-primary"></span> ';
					break;
				default:
					$icon				=	'<span class="gjGroupUserStatusIcon fa fa-question text-warning"></span> ';
					break;
			}

			$return						.=				'<div class="gjGroupUsersUser gjContainerBox img-thumbnail">'
										.					'<div class="gjContainerBoxHeader">'
										.						'<div class="gjContainerBoxCanvas text-left">'
										.							$cbUser->getField( 'canvas', null, 'html', 'none', 'profile', 0, true )
										.						'</div>'
										.						'<div class="gjContainerBoxLogo text-left">'
										.							$cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true )
										.						'</div>'
										.					'</div>'
										.					'<div class="gjContainerBoxBody text-left">'
										.						'<div class="gjContainerBoxTitle">'
										.							$cbUser->getField( 'onlinestatus', null, 'html', 'none', 'list', 0, true, array( '_imgMode' => 1 ) )
										.							' <strong>' . $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</strong>'
										.						'</div>'
										.						'<div class="gjContainerBoxCounters text-muted small row">'
										.							( $isModerator || $isOwner || $canModerate || ( $row->get( 'status' ) != 1 ) ? '<div class="gjContainerBoxCounter col-sm-6">' . $icon . $row->status() . '</div>' : null )
										.							( $counters ? '<div class="gjContainerBoxCounter col-sm-6">' . implode( '</div><div class="gjContainerBoxCounter col-sm-6">', $counters ) . '</div>' : null )
										.						'</div>'
										.						( $content ? '<div class="gjContainerBoxContent">' . $content . '</div>' : null );

			if ( ( $isModerator || $isOwner || $canModerate ) && ( $row->get( 'status' ) == 0 ) && ( $group->get( 'type' ) == 2 ) ) {
				$return					.=						'<div class="gjContainerBoxButton text-right">'
										.							'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'users', 'func' => 'active', 'id' => (int) $row->get( 'id' ) ) ) . '\';" class="gjButton gjButtonApprove btn btn-xs btn-success">' . CBTxt::T( 'Approve' ) . '</button>'
										.						'</div>';
			}

			$return						.=					'</div>';

			if ( ( $row->get( 'status' ) != 4 ) && ( $isModerator || $isOwner || $canModerate || $menu ) ) {
				$menuItems				=	'<ul class="gjUserMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';

				if ( $isModerator || $isOwner ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to promote this user to Owner?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'owner', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-thumbs-o-up"></span> ' . CBTxt::T( 'Promote to Owner' ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) <= 2 ) && ( $isModerator || $isOwner ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to promote this user to Admin?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'admin', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-thumbs-o-up"></span> ' . CBTxt::T( 'Promote to Admin' ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) <= 1 ) && ( $isModerator || $isOwner || ( $canModerate && ( $userStatus >= 3 ) ) ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to promote this user to Moderator?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'moderator', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-thumbs-o-up"></span> ' . CBTxt::T( 'Promote to Moderator' ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) <= 0 ) && ( $isModerator || $isOwner || ( $canModerate && ( $userStatus >= 2 ) ) ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'users', 'func' => 'active', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-thumbs-o-up"></span> ' . ( ( $row->get( 'status' ) == 0 ) && ( $group->get( 'type' ) == 2 ) ? CBTxt::T( 'Approve' ) : CBTxt::T( 'Promote to Active' ) ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) >= 3 ) && ( $isModerator || $isOwner ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to demote this user to Moderator?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'moderator', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-thumbs-o-down"></span> ' . CBTxt::T( 'Demote to Moderator' ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) >= 2 ) && ( $isModerator || $isOwner || ( $canModerate && ( $userStatus >= 3 ) ) ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to demote this user to Active?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'active', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-thumbs-o-down"></span> ' . CBTxt::T( 'Demote to Active' ) . '</a></li>';
				}

				if ( ( $row->get( 'status' ) >= 0 ) && ( $isModerator || $isOwner || ( $canModerate && ( $userStatus >= 2 ) ) ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to ban this User?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'ban', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-ban"></span> ' . CBTxt::T( 'Ban' ) . '</a></li>';
				}

				if ( $menu ) {
					$menuItems			.=		'<li class="gjUserMenuItems">' . implode( '</li><li class="gjGroupMenuItem">', $menu ) . '</li>';
				}

				if ( $isModerator || $isOwner || ( $canModerate && ( $userStatus >= 3 ) ) ) {
					$menuItems			.=		'<li class="gjUserMenuItems"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this User?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'users', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-trash-o"></span> ' . ( ( $row->get( 'status' ) == 0 ) && ( $group->get( 'type' ) == 2 ) ? CBTxt::T( 'Reject' ) : CBTxt::T( 'Delete' ) ) . '</a></li>';
				}

				$menuItems				.=	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=					'<div class="gjContainerBoxMenu">'
										.						'<div class="gjUserMenu btn-group">'
										.							'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.						'</div>'
										.					'</div>';
			}

			$return						.=				'</div>';
		} else {
			if ( $searching ) {
				$return					.=				CBTxt::T( 'No group user search results found.' );
			} else {
				$return					.=				CBTxt::T( 'This group currently has no users.' );
			}
		}

		$return							.=			'</div>';

		if ( $plugin->params->get( 'groups_users_paging', 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=			'<div class="gjGroupUsersPaging text-center">'
										.				$pageNav->getListLinks()
										.			'</div>';
		}

		$return							.=			$pageNav->getLimitBox( false )
										.		'</form>'
										.	'</div>';

		$_PLUGINS->trigger( 'gj_onAfterDisplayUsers', array( &$return, &$rows, $group, $user ) );

		return $return;
	}
}