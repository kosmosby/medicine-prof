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
use CB\Database\Table\TabTable;
use CBLib\Registry\Registry;
use CBLib\Application\Application;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbgalleryItems
{

	/**
	 * @param cbgalleryFolderTable|cbgalleryFolderTable[] $folders        cbgalleryFolderTable: we're IN a folder; cbgalleryFolderTable[]: we're on profile
	 * @param null|cbPageNav                              $foldersPageNav Null: we're IN a folder; cbPageNav: we're on profile
	 * @param bool                                        $foldersSearching
	 * @param cbgalleryItemTable[]                        $items
	 * @param cbPageNav                                   $itemsPageNav
	 * @param bool                                        $itemsSearching
	 * @param string                                      $type
	 * @param array                                       $input
	 * @param UserTable                                   $viewer
	 * @param UserTable                                   $user
	 * @param TabTable                                    $tab
	 * @param cbTabHandler                                $plugin
	 * @return string
	 */
	static public function showItems( $folders, $foldersPageNav, $foldersSearching, $items, $itemsPageNav, $itemsSearching, $type, $input, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayItems', array( &$folders, $foldersPageNav, $foldersSearching, &$items, $itemsPageNav, $itemsSearching, $type, &$input, $viewer, $user, $tab, $plugin ) );

		switch( $type ) {
			case 'photos':
				$typeTranslated				=	CBTxt::T( 'Photo' );
				break;
			case 'files':
				$typeTranslated				=	CBTxt::T( 'File' );
				break;
			case 'videos':
				$typeTranslated				=	CBTxt::T( 'Video' );
				break;
			case 'music':
				$typeTranslated				=	CBTxt::T( 'Music' );
				break;
			default:
				$typeTranslated				=	CBTxt::T( 'Item' );
				break;
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$folderType					=	CBTxt::T( 'Album' );
				break;
			default:
				$folderType					=	CBTxt::T( 'Folder' );
				break;
		}

		/** @var Registry $params */
		$params								=	$tab->params;
		$folder								=	( is_object( $folders ) ? $folders : null );
		$allowFolders						=	( $params->get( 'tab_' . $type . '_folders', 1 ) && ( ! $folder ) );
		$showFolders						=	( $allowFolders && $folders );
		$showItems							=	( ( ! $allowFolders ) || ( $allowFolders && $params->get( 'tab_' . $type . '_uncategorized', 1 ) ) );
		$canSearchFolders					=	( $showFolders ? ( $params->get( 'tab_' . $type . '_folders_search', 1 ) && ( $foldersSearching || $foldersPageNav->total ) ) : false );
		$canSearchItems						=	( $showItems ? ( $params->get( ( $folder ? 'tab_' . $type . '_folder_items_search' : 'tab_' . $type . '_items_search' ), 1 ) && ( $itemsSearching || $itemsPageNav->total ) ) : false );
		$profileOwner						=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator						=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canCreateFolders					=	( ( $profileOwner || $cbModerator ) && $allowFolders ? cbgalleryClass::canUserCreate( $viewer, $type, true ) : false );
		$canCreateItems						=	( $profileOwner || $cbModerator ? cbgalleryClass::canUserCreate( $viewer, $type, false ) : false );

		if ( $folder ) {
			$formUrl						=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $folders->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$formUrl						=	$_CB_framework->userProfileUrl( (int) $user->get( 'id' ), true, (int) $tab->get( 'tabid' ) );
		}

		$return								=	'<div class="' . htmlspecialchars( $type ) . 'ItemsTab">'
											.		'<form action="' . $formUrl . '" method="post" name="' . htmlspecialchars( $type ) . 'ItemsForm" id="' . htmlspecialchars( $type ) . 'ItemsForm" class="' . htmlspecialchars( $type ) . 'ItemsForm galleryItemsForm">';

		if ( $folder ) {
			$return							.=			HTML_cbgalleryFolder::showFolder( $folders, $type, $viewer, $user, $tab, $plugin );
		}

		if ( ( $showFolders && ( $canCreateFolders || $canSearchFolders ) ) || ( ( ! $showItems ) && $canCreateItems ) ) {
			$return							.=			'<div class="galleryItemsHeader row" style="margin-bottom: 10px;">';

			if ( $canCreateFolders || ( ( ! $showItems ) && $canCreateItems ) ) {
				$return						.=				'<div class="' . ( ! $canSearchFolders ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
											.					'<div class="btn-group">'
											.						( ( ! $showItems ) && $canCreateItems ? '<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => ( $folder ? (int) $folders->get( 'id' ) : 0 ) ) ) . '\';" class="galleryButton galleryButtonNewItem btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'NEW_ITEM_TYPE', 'New [type]', array( '[type]' => $typeTranslated ) ) . '</button>' : null )
											.						( $canCreateFolders ? '<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\';" class="galleryButton galleryButtonNewItem btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'NEW_FOLDER_TYPE', 'New [type]', array( '[type]' => $folderType ) ) . '</button>' : null )
											.					'</div>'
											.				'</div>';
			}

			if ( $canSearchFolders ) {
				$return						.=				'<div class="' . ( ! ( $canCreateFolders || ( ( ! $showItems ) && $canCreateItems ) ) ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
											.					'<div class="input-group">'
											.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
											.						$input['search_folders']
											.					'</div>'
											.				'</div>';
			}

			$return							.=			'</div>';
		} elseif ( ( ! $showFolders ) && ( $canCreateFolders || $canCreateItems || $canSearchItems ) ) {
			$return							.=			'<div class="galleryItemsHeader row" style="margin-bottom: 10px;">';

			if ( $canCreateFolders || $canCreateItems ) {
				$return						.=				'<div class="' . ( ! $canSearchItems ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
											.					'<div class="btn-group">'
											.						( $canCreateItems ? '<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => ( $folder ? (int) $folders->get( 'id' ) : 0 ) ) ) . '\';" class="galleryButton galleryButtonNewItem btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'NEW_ITEM_TYPE', 'New [type]', array( '[type]' => $typeTranslated ) ) . '</button>' : null )
											.						( $canCreateFolders ? '<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\';" class="galleryButton galleryButtonNewItem btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'NEW_FOLDER_TYPE', 'New [type]', array( '[type]' => $folderType ) ) . '</button>' : null )
											.					'</div>'
											.				'</div>';
			}

			if ( $canSearchItems ) {
				$return						.=				'<div class="' . ( ! ( $canCreateFolders || $canCreateItems ) ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
											.					'<div class="input-group">'
											.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
											.						$input['search_items']
											.					'</div>'
											.				'</div>';
			}

			$return							.=			'</div>';
		}

		if ( $showFolders || ( ! $showItems ) ) {
			$return							.=			HTML_cbgalleryFolders::showFolders( $folders, $foldersPageNav, ( ! $showItems ? $itemsPageNav->total : 0 ), $type, $viewer, $user, $tab, $plugin );
		}

		if ( $showItems ) {
			if ( $showFolders && ( $canCreateItems || $canSearchItems ) ) {
				$return						.=			'<div class="galleryItemsHeader row" style="margin-bottom: 10px;">';

				if ( $canCreateItems ) {
					$return					.=				'<div class="' . ( ! $canSearchItems ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
											.					'<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => ( $folder ? (int) $folders->get( 'id' ) : 0 ) ) ) . '\';" class="galleryButton galleryButtonNewItem btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'NEW_ITEM_TYPE', 'New [type]', array( '[type]' => $typeTranslated ) ) . '</button>'
											.				'</div>';
				}

				if ( $canSearchItems ) {
					$return					.=				'<div class="' . ( ! $canCreateItems ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
											.					'<div class="input-group">'
											.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
											.						$input['search_items']
											.					'</div>'
											.				'</div>';
				}

				$return						.=			'</div>';
			}

			switch( $type ) {
				case 'photos':
					$return					.=			HTML_cbgalleryPhotos::showPhotos( $items, $itemsPageNav, $folder, $itemsSearching, $viewer, $user, $tab, $plugin );
					break;
				case 'files':
					$return					.=			HTML_cbgalleryFiles::showFiles( $items, $itemsPageNav, $folder, $itemsSearching, $viewer, $user, $tab, $plugin );
					break;
				case 'videos':
					$return					.=			HTML_cbgalleryVideos::showVideos( $items, $itemsPageNav, $folder, $itemsSearching, $viewer, $user, $tab, $plugin );
					break;
				case 'music':
					$return					.=			HTML_cbgalleryMusic::showMusic( $items, $itemsPageNav, $folder, $itemsSearching, $viewer, $user, $tab, $plugin );
					break;
			}
		}

		$return								.=		'</form>'
											.	'</div>';

		return $return;
	}
}