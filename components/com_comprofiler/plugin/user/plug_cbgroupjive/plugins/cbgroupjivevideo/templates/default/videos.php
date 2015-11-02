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
use CB\Plugin\GroupJiveVideo\Table\VideoTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveVideo
{

	/**
	 * render frontend videos
	 *
	 * @param VideoTable[]    $rows
	 * @param cbPageNav       $pageNav
	 * @param bool            $searching
	 * @param array           $input
	 * @param array           $counters
	 * @param GroupTable      $group
	 * @param UserTable       $user
	 * @param cbgjVideoPlugin $plugin
	 * @return string
	 */
	static function showVideos( $rows, $pageNav, $searching, $input, &$counters, $group, $user, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_CB_framework->outputCbJQuery( "$( '.gjVideoPlayer' ).mediaelementplayer();", 'media' );

		$counters[]						=	'<span class="gjGroupVideoIcon fa-before fa-film"> ' . CBTxt::T( 'GROUP_VIDEOS_COUNT', '%%COUNT%% Video|%%COUNT%% Videos', array( '%%COUNT%%' => (int) $pageNav->total ) ) . '</span>';

		initToolTip();

		$isModerator					=	CBGroupJive::isModerator( $user->get( 'id' ) );
		$isOwner						=	( $user->get( 'id' ) == $group->get( 'user_id' ) );
		$userStatus						=	CBGroupJive::getGroupStatus( $user, $group );
		$canCreate						=	CBGroupJive::canCreateGroupContent( $user, $group, 'video' );
		$canSearch						=	( $plugin->params->get( 'groups_video_search', 1 ) && ( $searching || $pageNav->total ) );
		$return							=	null;

		$_PLUGINS->trigger( 'gj_onBeforeDisplayVideos', array( &$return, &$rows, $group, $user ) );

		$return							.=	'<div class="gjGroupVideo">'
										.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->_gjPlugin->element, true, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $group->get( 'id' ) ) ) . '" method="post" name="gjGroupVideoForm" id="gjGroupVideoForm" class="gjGroupVideoForm">';

		if ( $canCreate || $canSearch ) {
			$return						.=			'<div class="gjHeader gjGroupVideoHeader row">';

			if ( $canCreate ) {
				$return					.=				'<div class="' . ( ! $canSearch ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
										.					'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'video', 'func' => 'new', 'group' => (int) $group->get( 'id' ) ) ) . '\';" class="gjButton gjButtonNewVideo btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'New Video' ) . '</button>'
										.				'</div>';
			}

			if ( $canSearch ) {
				$return					.=				'<div class="' . ( ! $canCreate ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
										.					'<div class="input-group">'
										.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
										.						$input['search']
										.					'</div>'
										.				'</div>';
			}

			$return						.=			'</div>';
		}

		$return							.=			'<div class="gjGroupVideoRows">';

		if ( $rows ) foreach ( $rows as $row ) {
			$rowCounters				=	array();
			$content					=	null;
			$menu						=	array();

			$_PLUGINS->trigger( 'gj_onDisplayVideo', array( &$row, &$rowCounters, &$content, &$menu, $group, $user ) );

			$return						.=				'<div class="gjGroupVideoRow gjContainerBox img-thumbnail">'
										.					'<div class="gjContainerBoxHeader">'
										.						'<video width="640" height="360" style="width: 100%; height: 100%;" id="gjVideoPlayer' . (int) $row->get( 'id' ) . '" src="' . htmlspecialchars( $row->get( 'url' ) ) . '" type="' . htmlspecialchars( $row->mimeType() ) . '" controls="controls" preload="none" class="gjVideoPlayer"></video>'
										.					'</div>'
										.					'<div class="gjContainerBoxBody text-left">'
										.						'<div class="gjContainerBoxTitle">'
										.							'<a href="' . htmlspecialchars( $row->get( 'url' ) ) . '" target="_blank" rel="nofollow">' . htmlspecialchars( ( $row->get( 'title' ) ? $row->get( 'title' ) : $row->name() ) ) . '</a>'
										.						'</div>'
										.						'<div class="gjContainerBoxCounters text-muted small row">'
										.							'<div class="gjGroupVideoPublisher gjContainerBoxCounter col-sm-6">' . CBuser::getInstance( (int) $row->get( 'user_id' ), false )->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) . '</div>'
										.							'<div class="gjContainerBoxCounter col-sm-6 text-right">'
											.							'<span title="' . htmlspecialchars( $row->get( 'date' ) ) . '">'
											.								cbFormatDate( $row->get( 'date' ), true, false, CBTxt::T( 'GROUP_VIDEO_DATE_FORMAT', 'M j, Y' ) )
											.							'</span>'
										.							'</div>'
										.							( $rowCounters ? '<div class="gjContainerBoxCounter col-sm-6">' . implode( '</div><div class="gjContainerBoxCounter col-sm-6">', $rowCounters ) . '</div>' : null )
										.						'</div>'
										.						( $content ? '<div class="gjContainerBoxContent">' . $content . '</div>' : null )
										.						( $row->get( 'caption' ) ? '<div class="gjContainerBoxDescription">' . cbTooltip( 1, $row->get( 'caption' ), $row->name(), 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null );

			if ( ( $isModerator || $isOwner || ( $userStatus >= 2 ) ) && ( $row->get( 'published' ) == -1 ) && ( $group->params()->get( 'video', 1 ) == 2 ) ) {
				$return					.=						'<div class="gjContainerBoxButton text-right">'
										.							'<button type="button" onclick="window.location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'video', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '\';" class="gjButton gjButtonApprove btn btn-xs btn-success">' . CBTxt::T( 'Approve' ) . '</button>'
										.						'</div>';
			}

			$return						.=					'</div>';

			if ( $isModerator || $isOwner || ( $user->get( 'id' ) == $row->get( 'user_id' ) ) || ( $userStatus >= 2 ) || $menu ) {
				$menuItems				=	'<ul class="gjVideoMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';

				if ( $isModerator || $isOwner || ( $user->get( 'id' ) == $row->get( 'user_id' ) ) || ( $userStatus >= 2 ) ) {
					$menuItems			.=		'<li class="gjVideoMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'video', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

					if ( ( $row->get( 'published' ) == -1 ) && ( $group->params()->get( 'video', 1 ) == 2 ) ) {
						if ( $isModerator || $isOwner || ( $userStatus >= 2 ) ) {
							$menuItems	.=		'<li class="gjVideoMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'video', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
						}
					} elseif ( $row->get( 'published' ) > 0 ) {
						$menuItems		.=		'<li class="gjVideoMenuItem"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this Video?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'video', 'func' => 'unpublish', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
					} else {
						$menuItems		.=		'<li class="gjVideoMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'video', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
					}
				}

				if ( $menu ) {
					$menuItems			.=		'<li class="gjVideoMenuItem">' . implode( '</li><li class="gjVideoMenuItem">', $menu ) . '</li>';
				}

				if ( $isModerator || $isOwner || ( $user->get( 'id' ) == $row->get( 'user_id' ) ) || ( $userStatus >= 2 ) ) {
					$menuItems			.=		'<li class="gjVideoMenuItem"><a href="javascript: void(0);" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Video?' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'video', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>';
				}

				$menuItems				.=	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=					'<div class="gjContainerBoxMenu">'
										.						'<div class="gjVideoMenu btn-group">'
										.							'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.						'</div>'
										.					'</div>';
			}

			$return						.=				'</div>';
		} else {
			if ( $searching ) {
				$return					.=				CBTxt::T( 'No group video search results found.' );
			} else {
				$return					.=				CBTxt::T( 'This group currently has no videos.' );
			}
		}

		$return							.=			'</div>';

		if ( $plugin->params->get( 'groups_video_paging', 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=			'<div class="gjGroupVideoPaging text-center">'
										.				$pageNav->getListLinks()
										.			'</div>';
		}

		$return							.=			$pageNav->getLimitBox( false )
										.		'</form>'
										.	'</div>';

		$_PLUGINS->trigger( 'gj_onAfterDisplayVideos', array( &$return, $rows, $group, $user ) );

		return $return;
	}
}