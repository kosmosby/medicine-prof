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
use CB\Database\Table\TabTable;
use CBLib\Registry\Registry;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbgalleryFolders
{

	/**
	 * @param cbgalleryFolderTable[] $rows
	 * @param cbPageNav              $pageNav
	 * @param int                    $uncategorized
	 * @param string                 $type
	 * @param UserTable              $viewer
	 * @param UserTable              $user
	 * @param TabTable               $tab
	 * @param cbTabHandler           $plugin
	 * @return string
	 */
	static public function showFolders( $rows, $pageNav, $uncategorized, $type, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayFolders', array( &$rows, $pageNav, $uncategorized, $type, $viewer, $user, $tab, $plugin ) );

		switch( $type ) {
			case 'photos':
				$galleryType			=	CBTxt::T( 'Photos' );
				break;
			case 'files':
				$galleryType			=	CBTxt::T( 'Files' );
				break;
			case 'videos':
				$galleryType			=	CBTxt::T( 'Videos' );
				break;
			case 'music':
				$galleryType			=	CBTxt::T( 'Music' );
				break;
			default:
				$galleryType			=	CBTxt::T( 'Items' );
				break;
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$typeTranslated			=	CBTxt::T( 'Album' );
				break;
			default:
				$typeTranslated			=	CBTxt::T( 'Folder' );
				break;
		}

		/** @var Registry $params */
		$params							=	$tab->params;
		$profileOwner					=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator					=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();

		$return							=			'<div class="' . htmlspecialchars( $type ) . 'FoldersContainer" style="margin-bottom: 10px;">';

		if ( $uncategorized ) {
			switch( $type ) {
				case 'photos':
					$count				=	CBTxt::T( 'FOLDER_PHOTOS_COUNT', '%%COUNT%% Photo|%%COUNT%% Photos', array( '%%COUNT%%' => $uncategorized ) );
					break;
				case 'files':
					$count				=	CBTxt::T( 'FOLDER_FILES_COUNT', '%%COUNT%% File|%%COUNT%% Files', array( '%%COUNT%%' => $uncategorized ) );
					break;
				case 'videos':
					$count				=	CBTxt::T( 'FOLDER_VIDEOS_COUNT', '%%COUNT%% Video|%%COUNT%% Videos', array( '%%COUNT%%' => $uncategorized ) );
					break;
				case 'music':
					$count				=	CBTxt::T( 'FOLDER_MUSIC_COUNT', '%%COUNT%% Music|%%COUNT%% Music', array( '%%COUNT%%' => $uncategorized ) );
					break;
				default:
					$count				=	CBTxt::T( 'FOLDER_ITEM_COUNT', '%%COUNT%% Item|%%COUNT%% Items', array( '%%COUNT%%' => $uncategorized ) );
					break;
			}

			$return						.=		'<div class="galleryContainer img-thumbnail">'
										.			'<div class="galleryContainerInner" style="height: 100px; width: 100px;">'
										.				'<div class="galleryContainerTop" style="height: 60px">'
										.					'<div class="galleryContainerContent">'
										.						( $uncategorized ? '<span class="galleryFoldersNotEmpty fa fa-folder-open-o"></span>' : '<span class="galleryFoldersEmpty fa fa-folder-o"></span>' )
										.					'</div>'
										.				'</div>'
										.				'<div class="galleryContainerBottom" style="height: 40px">'
										.					'<div class="galleryContainerContent">'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">'
										.							'<strong>'
										.								'<a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => 0, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '">'
										.									CBTxt::T( 'Uncategorized' )
										.								'</a>'
										.							'</strong>'
										.						'</div>'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">' . $count . '</div>'
										.					'</div>'
										.				'</div>'
										.			'</div>'
										.		'</div>';
		}

		if ( $rows ) foreach ( $rows as $row ) {
			$return						.=		'<div class="galleryContainer img-thumbnail">';

			if ( $cbModerator || $profileOwner ) {
				$menuItems				=	'<ul class="galleryFoldersMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">'
										.		'<li class="galleryFoldersMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'edit', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

				if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( $type . '_folder_approval', 0 ) ) {
					if ( $cbModerator ) {
						$menuItems		.=		'<li class="galleryFoldersMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'publish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
					}
				} elseif ( $row->get( 'published' ) > 0 ) {
					$menuItems			.=		'<li class="galleryFoldersMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'FOLDER_UNPUBLISH_TYPE', 'Are you sure you want to unpublish this [type]?', array( '[type]' => $typeTranslated ) ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'unpublish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
				} else {
					$menuItems			.=		'<li class="galleryFoldersMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'publish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
				}

				$menuItems				.=		'<li class="galleryFoldersMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'FOLDER_DELETE_TYPE', 'Are you sure you want to delete this [folder_type] and all its [item_type]?', array( '[folder_type]' => $typeTranslated, '[item_type]' => $galleryType ) ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'delete', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>'
										.	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=			'<div class="galleryContainerMenu">'
										.				'<div class="galleryFoldersMenu btn-group">'
										.					'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.				'</div>'
										.			'</div>';
			}

			$title						=	( $row->get( 'title' ) ? htmlspecialchars( $row->get( 'title' ) ) : cbFormatDate( $row->get( 'date' ), true, (int) $params->get( 'tab_' . $type . '_folders_time_display', 0 ), $params->get( 'tab_' . $type . '_folders_date_format', 'M j, Y' ), $params->get( 'tab_' . $type . '_folders_time_format', ' g:h A' ) ) );

			switch( $type ) {
				case 'photos':
					$count				=	CBTxt::T( 'FOLDER_PHOTOS_COUNT', '%%COUNT%% Photo|%%COUNT%% Photos', array( '%%COUNT%%' => $row->countItems() ) );
					break;
				case 'files':
					$count				=	CBTxt::T( 'FOLDER_FILES_COUNT', '%%COUNT%% File|%%COUNT%% Files', array( '%%COUNT%%' => $row->countItems() ) );
					break;
				case 'videos':
					$count				=	CBTxt::T( 'FOLDER_VIDEOS_COUNT', '%%COUNT%% Video|%%COUNT%% Video', array( '%%COUNT%%' => $row->countItems() ) );
					break;
				case 'music':
					$count				=	CBTxt::T( 'FOLDER_MUSIC_COUNT', '%%COUNT%% Music|%%COUNT%% Music', array( '%%COUNT%%' => $row->countItems() ) );
					break;
				default:
					$count				=	CBTxt::T( 'FOLDER_ITEM_COUNT', '%%COUNT%% Item|%%COUNT%% Items', array( '%%COUNT%%' => $row->countItems() ) );
					break;
			}

			$return						.=			'<div class="galleryContainerInner" style="height: 100px; width: 100px;">'
										.				'<div class="galleryContainerTop" style="height: 60px">'
										.					'<div class="galleryContainerContent">'
										.						( $row->countItems() ? '<span class="galleryFoldersNotEmpty fa fa-folder-open-o"></span>' : '<span class="galleryFoldersEmpty fa fa-folder-o"></span>' )
										.					'</div>'
										.				'</div>'
										.				'<div class="galleryContainerBottom" style="height: 40px">'
										.					'<div class="galleryContainerContent">'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">'
										.							'<strong>'
										.								'<a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '" title="' . htmlspecialchars( $row->get( 'date' ) ) . '">'
										.									$title
										.								'</a>'
										.							'</strong>'
										.						'</div>'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">'
										.							$count
										.							( $row->get( 'description' ) ? '<div class="galleryContainerDescription">' . cbTooltip( 1, $row->get( 'description' ), $title, 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null )
										.						'</div>'
										.					'</div>'
										.				'</div>'
										.			'</div>'
										.		'</div>';
		}

		if ( $params->get( 'tab_' . $type . '_folders_paging', 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=		'<div class="galleryFoldersPaging text-center">'
										.			$pageNav->getListLinks()
										.		'</div>';
		}

		$return							.=	'</div>'
										.	$pageNav->getLimitBox( false );

		return $return;
	}
}