<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveGroup
{

	/**
	 * render frontend group
	 *
	 * @param GroupTable         $row
	 * @param mixed              $users
	 * @param mixed              $invites
	 * @param UserTable          $user
	 * @param CBplug_cbgroupjive $plugin
	 * @return string
	 */
	static function showGroup( $row, $users, $invites, $user, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_CB_framework->setPageTitle( htmlspecialchars( CBTxt::T( $row->get( 'name' ) ) ) );

		initToolTip();

		$js								=	"$( '.gjGroupTabsMenuNavBar' ).on( 'click', '.navbar-toggle', function() {"
										.		"if ( ! $( this ).hasClass( 'dropdown-toggle' ) ) {"
										.			"var navbar = $( this ).closest( '.gjGroupTabsMenuNavBar' ).find( '.navbar-collapse' );"
										.			"var toggle = $( this ).closest( '.gjGroupTabsMenuNavBar' ).find( '.navbar-toggle' );"
										.			"if ( toggle.hasClass( 'collapsed' ) ) {"
										.				"navbar.addClass( 'in' );"
										.				"toggle.removeClass( 'collapsed' );"
										.			"} else {"
										.				"navbar.removeClass( 'in' );"
										.				"toggle.addClass( 'collapsed' );"
										.			"}"
										.		"}"
										.	"}).find( '.cbScroller' ).cbscroller({"
										.		"height: false"
										.	"});"
										.	"$( '.gjGroupTabs' ).on( 'cbtabs.selected', function( e, event, cbtabs, tab ) {"
										.		"var dropdownNav = $( event.target ).closest( '.gjGroupTabsMenuNavBar' );"
										.		"if ( dropdownNav.length ) {"
										.			"var toggle = dropdownNav.find( '.navbar-toggle' );"
										.			"if ( ! toggle.hasClass( 'collapsed' ) ) {"
										.				"toggle.click();"
										.			"}"
										.		"}"
										.	"});";

		$_CB_framework->outputCbJQuery( $js, 'cbscroller' );

		$isModerator					=	CBGroupJive::isModerator( $user->get( 'id' ) );
		$isOwner						=	( $user->get( 'id' ) == $row->get( 'user_id' ) );
		$userStatus						=	CBGroupJive::getGroupStatus( $user, $row );
		$counters						=	array();
		$buttons						=	array();
		$menu							=	array();
		$tabs							=	new cbTabs( 1, 1 );
		$return							=	null;

		$integrations					=	$_PLUGINS->trigger( 'gj_onBeforeDisplayGroup', array( &$return, &$row, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user ) );

		$return							.=	'<div class="gjGroup">'
										.		'<div class="gjGroupCanvas gjPageHeader border-default">'
										.			'<div class="gjPageHeaderCanvas">'
										.				'<div class="gjPageHeaderCanvasBackground">'
										.					$row->canvas()
										.				'</div>'
										.				'<div class="gjPageHeaderCanvasLogo">'
										.					$row->logo()
										.				'</div>';

		if ( $isModerator || $isOwner || ( ( ! $isOwner ) && ( ( $userStatus === null ) || ( $userStatus === 0 ) || ( $userStatus >= 1 ) ) ) || $buttons || $menu ) {
			$return						.=				'<div class="gjPageHeaderCanvasButtons text-right">'
										.					( $buttons ? ' <span class="gjPageHeaderCanvasButton">' . implode( '</span> <span class="gjPageHeaderCanvasButton">', $buttons ) . '</span>' : null );

			if ( $isModerator && ( $row->get( 'published' ) == -1 ) && $plugin->params->get( 'groups_create_approval', 0 ) ) {
				$return					.=					' <span class="gjPageHeaderCanvasButton">'
										.						'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '\';" class="gjButton gjButtonApprove btn btn-xs btn-success">' . CBTxt::T( 'Approve' ) . '</button>'
										.					'</span>';
			} elseif ( ! $isOwner ) {
				if ( $userStatus === null ) {
					$return				.=					' <span class="gjPageHeaderCanvasButton">'
										.						( $row->get( '_invite_id' ) ? '<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to reject all invites to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'reject', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })" class="gjButton gjButtonReject btn btn-xs btn-danger">' . CBTxt::T( 'Reject' ) . '</button> ' : null )
										.						'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'join', 'id' => (int) $row->get( 'id' ) ) ) . '\';" class="gjButton gjButtonJoin btn btn-xs btn-success">' . ( $row->get( '_invite_id' ) ? CBTxt::T( 'Accept Invite' ) : CBTxt::T( 'Join' ) ) . '</button>'
										.					'</span>';
				} elseif ( $userStatus === 0 ) {
					$return				.=					' <span class="gjPageHeaderCanvasButton">'
										.						'<button type="button" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel your pending join request to this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'cancel', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })" class="gjButton gjButtonCancel btn btn-xs btn-danger">' . CBTxt::T( 'Cancel' ) . '</button> '
										.						'<span class="gjButton gjButtonPending btn btn-xs btn-warning disabled">' . CBTxt::T( 'Pending Approval' ) . '</span>'
										.					'</span>';
				}
			}

			if ( $isModerator || $isOwner || $menu || ( $userStatus >= 1) ) {
				$menuItems				=	'<ul class="gjGroupMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';

				if ( $isModerator || $isOwner ) {
					$menuItems			.=		'<li class="gjGroupMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

					if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( 'groups_create_approval', 0 ) ) {
						if ( $isModerator ) {
							$menuItems	.=		'<li class="gjGroupMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
						}
					} elseif ( $row->get( 'published' ) == 1 ) {
						$menuItems		.=		'<li class="gjGroupMenuItem"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'unpublish', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
					} else {
						$menuItems		.=		'<li class="gjGroupMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
					}
				}

				if ( $plugin->params->get( 'notifications', 1 ) && ( $isModerator || ( ( $row->get( 'published' ) == 1 ) && ( $isOwner || ( $userStatus >= 1 ) ) ) ) ) {
					$menuItems			.=		'<li class="gjGroupMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'notifications', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-envelope"></span> ' . CBTxt::T( 'Notifications' ) . '</a></li>';
				}

				if ( $isModerator || ( ( $row->get( 'published' ) == 1 ) && $plugin->params->get( 'groups_message', 0 ) && ( $isOwner || ( $userStatus >= 3 ) ) ) ) {
					$delay				=	false;

					if ( ( ! $isModerator ) && $row->params()->get( 'messaged' ) && $plugin->params->get( 'groups_message_delay', 60 ) ) {
						$seconds		=	(int) $plugin->params->get( 'groups_message_delay', 60 );

						if ( $seconds ) {
							$diff		=	Application::Date( 'now', 'UTC' )->diff( $row->get( 'messaged' ) );

							if ( ( $diff === false ) || ( $diff->s < $seconds ) ) {
								$delay	=	true;
							}
						}
					}

					if ( ! $delay ) {
						$menuItems		.=		'<li class="gjGroupMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'message', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-comment"></span> ' . CBTxt::T( 'Message' ) . '</a></li>';
					}
				}

				if ( ( ! $isOwner ) && ( $userStatus >= 1 ) ) {
					$menuItems			.=		'<li class="gjGroupMenuItem"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to leave this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'leave', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-minus-circle"></span> ' . CBTxt::T( 'Leave' ) . '</a></li>';
				}

				if ( $menu ) {
					$menuItems			.=		'<li class="gjGroupMenuItem">' . implode( '</li><li class="gjGroupMenuItem">', $menu ) . '</li>';
				}

				if ( $isModerator || $isOwner ) {
					$menuItems			.=		'<li class="gjGroupMenuItem"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Group?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>';
				}

				$menuItems				.=	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=					' <span class="gjPageHeaderCanvasButton">'
										.						'<div class="gjGroupMenu btn-group">'
										.							'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.						'</div>'
										.					'</span>';
			}

			$return						.=				'</div>';
		}

		$return							.=			'</div>'
										.			'<div class="gjPageHeaderBar border-default">'
										.				'<div class="gjPageHeaderBarTitle text-primary">'
										.					'<strong>' . htmlspecialchars( CBTxt::T( $row->get( 'name' ) ) ) . '</strong>'
										.				'</div>'
										.				'<div class="gjPageHeaderBarCounters text-muted small">';

		if ( $row->get( 'category' ) ) {
			$return						.=					'<span class="gjPageHeaderBarCounter"><span class="gjGroupCategoryIcon fa-before fa-folder">'
										.						' <a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'categories', 'func' => 'show', 'id' => (int) $row->get( 'category' ) ) ) . '">' . CBTxt::T( $row->category()->get( 'name' ) ) . '</a>'
										.					'</span></span>';
		}

		$return							.=					' <span class="gjPageHeaderBarCounter"><span class="gjGroupTypeIcon fa-before fa-globe"> ' . $row->type() . '</span></span>'
										.					' <span class="gjPageHeaderBarCounter"><span class="gjGroupUsersIcon fa-before fa-user"> ' . CBTxt::T( 'GROUP_USERS_COUNT', '%%COUNT%% User|%%COUNT%% Users', array( '%%COUNT%%' => (int) $row->get( '_users', 0 ) ) ) . '</span></span>'
										.					( $counters ? ' <span class="gjPageHeaderBarCounter">' . implode( '</span> <span class="gjPageHeaderBarCounter">', $counters ) . '</span>' : null )
										.				'</div>'
										.				( $row->get( 'description' ) ? ' <div class="gjPageHeaderBarDescription">' . cbTooltip( 1, CBTxt::T( $row->get( 'description' ) ), CBTxt::T( $row->get( 'name' ) ), 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null )
										.			'</div>'
										.		'</div>'
										.		'<div class="gjGroupTabs cbTabs cbTabsMenu" id="cbtabsgrouptab" data-cbtabs-use-cookies="true">'
										.			'<div class="gjGroupTabsMenuNavBar cbTabsMenuNavBar navbar navbar-default">'
										.				'<div class="container-fluid">'
										.					'<div class="navbar-header">'
										.						'<button type="button" class="gjGroupTabsMenuNavBarToggle cbTabsMenuNavBarToggle navbar-toggle collapsed">'
										.							'<span class="icon-bar"></span>'
										.							'<span class="icon-bar"></span>'
										.							'<span class="icon-bar"></span>'
										.						'</button>'
										.					'</div>'
										.					'<div class="collapse navbar-collapse cbScroller">'
										.						'<div class="cbScrollerLeft hidden">'
										.							'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
										.						'</div>'
										.						'<ul class="gjGroupTabsNav cbTabsNav cbTabsMenuNav nav navbar-nav cbScrollerContent"></ul>'
										.						'<div class="cbScrollerRight hidden">'
										.							'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
										.						'</div>'
										.					'</div>'
										.				'</div>'
										.			'</div>'
										.			'<div class="cbTabsContent cbTabsMenuContent tab-content">';

		foreach ( $integrations as $integration ) {
			if ( ( ! $integration ) || ( ! isset( $integration['id'] ) ) || ( ! isset( $integration['title'] ) ) || ( ! isset( $integration['content'] ) ) ) {
				continue;
			}

			$return						.=				$tabs->startTab( null, $integration['title'], 'grouptab' . $integration['id'], array( 'tab' => 'cbTabNavMenu', 'pane' => 'tab-pane cbTabPaneMenu', 'override' => true ) )
										.					'<div class="cb_tab_content cb_tab_menu">'
										.						$integration['content']
										.					'</div>'
										.				$tabs->endTab();
		}

		if ( $users ) {
			$return						.=				$tabs->startTab( null, CBTxt::T( 'GROUP_USERS', 'Users' ), 'grouptabusers', array( 'tab' => 'cbTabNavMenu', 'pane' => 'tab-pane cbTabPaneMenu', 'override' => true ) )
										.					'<div class="cb_tab_content cb_tab_menu">'
										.						$users
										.					'</div>'
										.				$tabs->endTab();
		}

		if ( $invites ) {
			$return						.=				$tabs->startTab( null, CBTxt::T( 'Invites' ), 'grouptabinvites', array( 'tab' => 'cbTabNavMenu', 'pane' => 'tab-pane cbTabPaneMenu', 'override' => true ) )
										.					'<div class="cb_tab_content cb_tab_menu">'
										.						$invites
										.					'</div>'
										.				$tabs->endTab();
		}

		$return							.=			'</div>'
										.		'</div>'
										.	'</div>';

		$_PLUGINS->trigger( 'gj_onAfterDisplayGroup', array( &$return, $row, $users, $invites, $user ) );

		$_CB_framework->setMenuMeta();

		echo $return;
	}
}