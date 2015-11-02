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
use CB\Database\Table\PluginTable;
use CB\Plugin\GroupJive\Table\CategoryTable;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveModule
{

	/**
	 * render frontend categories module
	 *
	 * @param CategoryTable[]           $rows
	 * @param UserTable                 $user
	 * @param \Joomla\Registry\Registry $params
	 * @param PluginTable               $plugin
	 * @return string
	 */
	static function showCategories( $rows, $user, $params, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		initToolTip();

		$return			=	null;

		if ( $rows ) foreach ( $rows as $row ) {
			$counters	=	array();
			$content	=	null;

			$_PLUGINS->trigger( 'gj_onDisplayCategory', array( &$row, &$counters, &$content, $user ) );

			$return		.=	'<div class="gjModuleCategory gjContainerBox img-thumbnail">'
						.		'<div class="gjContainerBoxHeader">'
						.			'<div class="gjContainerBoxCanvas text-left">'
						.				$row->canvas( true, true )
						.			'</div>'
						.			'<div class="gjContainerBoxLogo text-center">'
						.				$row->logo( true, true, true )
						.			'</div>'
						.		'</div>'
						.		'<div class="gjContainerBoxBody text-left">'
						.			'<div class="gjContainerBoxTitle">'
						.				'<strong><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'categories', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) . '">' . CBTxt::T( $row->get( 'name' ) ) . '</a></strong>'
						.			'</div>'
						.			'<div class="gjContainerBoxCounters text-muted small row">'
						.				'<div class="gjContainerBoxCounter ' . ( $counters ? 'col-sm-6' : 'col-sm-12' ) . '"><span class="gjCategoryGroupsIcon fa-before fa-users"> ' . CBTxt::T( 'GROUPS_COUNT', '%%COUNT%% Group|%%COUNT%% Groups', array( '%%COUNT%%' => (int) $row->get( '_groups', 0 ) ) ) . '</span></div>'
						.				( $counters ? '<div class="gjContainerBoxCounter col-sm-6">' . implode( '</div><div class="gjContainerBoxCounter col-sm-6">', $counters ) . '</div>' : null )
						.			'</div>'
						.			( $content ? '<div class="gjContainerBoxContent">' . $content . '</div>' : null )
						.			( $row->get( 'description' ) ? '<div class="gjContainerBoxDescription">' . cbTooltip( 1, $row->get( 'description' ), $row->get( 'name' ), 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null )
						.		'</div>'
						.	'</div>';
		}

		return $return;
	}

	/**
	 * render frontend groups module
	 *
	 * @param GroupTable[]              $rows
	 * @param UserTable                 $user
	 * @param \Joomla\Registry\Registry $params
	 * @param PluginTable               $plugin
	 * @return string
	 */
	static function showGroups( $rows, $user, $params, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		initToolTip();

		$return					=	null;

		if ( $rows ) foreach ( $rows as $row ) {
			$userStatus			=	CBGroupJive::getGroupStatus( $user, $row );

			$counters			=	array();
			$content			=	null;
			$menu				=	array();

			$_PLUGINS->trigger( 'gj_onDisplayGroup', array( &$row, &$counters, &$content, &$menu, 7, $user ) );

			$return				.=	'<div class="gjModuleGroup gjContainerBox img-thumbnail">'
								.		'<div class="gjContainerBoxHeader">'
								.			'<div class="gjContainerBoxCanvas text-left">'
								.				$row->canvas( true, true )
								.			'</div>'
								.			'<div class="gjContainerBoxLogo text-center">'
								.				$row->logo( true, true, true )
								.			'</div>'
								.		'</div>'
								.		'<div class="gjContainerBoxBody text-left">'
								.			'<div class="gjContainerBoxTitle">'
								.				'<strong><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) . '">' . htmlspecialchars( CBTxt::T( $row->get( 'name' ) ) ) . '</a></strong>'
								.			'</div>';

			if ( $row->get( 'category' ) ) {
				$return			.=			'<div class="gjContainerBoxSubTitle small">'
								.				'<strong><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'categories', 'func' => 'show', 'id' => (int) $row->get( 'category' ) ) ) . '">' . htmlspecialchars( CBTxt::T( $row->get( '_category_name' ) ) ) . '</a></strong>'
								.			'</div>';
			}

			$return				.=			'<div class="gjContainerBoxCounters text-muted small row">'
								.				'<div class="gjContainerBoxCounter col-sm-6"><span class="gjGroupTypeIcon fa-before fa-globe"> ' . $row->type() . '</span></div>'
								.				'<div class="gjContainerBoxCounter col-sm-6"><span class="gjGroupUsersIcon fa-before fa-user"> ' . CBTxt::T( 'GROUP_USERS_COUNT', '%%COUNT%% User|%%COUNT%% Users', array( '%%COUNT%%' => (int) $row->get( '_users', 0 ) ) ) . '</span></div>'
								.				( $counters ? '<div class="gjContainerBoxCounter col-sm-6">' . implode( '</div><div class="gjContainerBoxCounter col-sm-6">', $counters ) . '</div>' : null )
								.			'</div>'
								.			( $content ? '<div class="gjContainerBoxContent">' . $content . '</div>' : null )
								.			( $row->get( 'description' ) ? '<div class="gjContainerBoxDescription">' . cbTooltip( 1, $row->get( 'description' ), $row->get( 'name' ), 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null );

			if ( $user->get( 'id' ) != $row->get( 'user_id' ) ) {
				if ( $userStatus === null ) {
					$return		.=			'<div class="gjContainerBoxButton text-right">'
								.				( $row->get( '_invite_id' ) ? '<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to reject all invites to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'reject', 'id' => (int) $row->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\'; })" class="gjButton gjButtonReject btn btn-xs btn-danger">' . CBTxt::T( 'Reject' ) . '</button> ' : null )
								.				'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'join', 'id' => (int) $row->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\';" class="gjButton gjButtonJoin btn btn-xs btn-success">' . ( $row->get( '_invite_id' ) ? CBTxt::T( 'Accept Invite' ) : CBTxt::T( 'Join' ) ) . '</button>'
								.			'</div>';
				} elseif ( $userStatus === 0 ) {
					$return		.=			'<div class="gjContainerBoxButton text-right">'
								.				'<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel your pending join request to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'cancel', 'id' => (int) $row->get( 'id' ), 'return' => CBGroupJive::getReturn() ) ) . '\'; })" class="gjButton gjButtonCancel btn btn-xs btn-danger">' . CBTxt::T( 'Cancel' ) . '</button> '
								.				'<span class="gjButton gjButtonPending btn btn-xs btn-warning disabled">' . CBTxt::T( 'Pending Approval' ) . '</span>'
								.			'</div>';
				}
			}

			$return				.=		'</div>'
								.	'</div>';
		}

		return $return;
	}
}