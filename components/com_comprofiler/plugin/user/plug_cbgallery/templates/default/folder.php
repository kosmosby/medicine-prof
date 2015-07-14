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

class HTML_cbgalleryFolder
{

	/**
	 * @param cbgalleryFolderTable $row
	 * @param string               $type
	 * @param UserTable            $viewer
	 * @param UserTable            $user
	 * @param TabTable             $tab
	 * @param cbTabHandler         $plugin
	 * @return string
	 */
	static public function showFolder( $row, $type, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayFolder', array( &$row, $type, $viewer, $user, $tab, $plugin ) );

		/** @var Registry $params */
		$params							=	$tab->params;

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

		$profileOwner					=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator					=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$date							=	cbFormatDate( $row->get( 'date' ), true, (int) $params->get( 'tab_' . $type . '_folder_items_time_display', 0 ), $params->get( 'tab_' . $type . '_folder_items_date_format', 'F j, Y' ), $params->get( 'tab_' . $type . '_folder_items_time_format', ' g:h A' ) );

		$return							=	'<div class="galleryFolderTitle page-header clearfix">'
										.		'<h3 class="row">'
										.			'<div class="col-sm-8 text-left">'
										.				( $row->get( 'title' ) ? htmlspecialchars( $row->get( 'title' ) ) . ( $row->get( 'id' ) !== 0 ? '<div class="small" title="' . htmlspecialchars( $row->get( 'date' ) ) . '">' . $date . '</div>' : null ) : $date )
										.			'</div>'
										.			'<div class="col-sm-4 text-right">'
										.				'<small>'
										.					'<a href="' . $_CB_framework->userProfileUrl( (int) $user->get( 'id' ), true, (int) $tab->get( 'tabid' ) ) . '">'
										.						CBuser::getInstance( (int) $row->get( 'user_id' ), false )->getField( 'formatname', null, 'html', 'none', 'profile', 0, true )
										.					'</a>'
										.				'</small>';

		if ( ( $row->get( 'id' ) !== 0 ) && ( $cbModerator || $profileOwner ) ) {
			$menuItems					=	'<ul class="galleryFolderMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">'
										.		'<li class="galleryFolderMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'edit', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => true ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

			if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( $type . '_folder_approval', 0 ) ) {
				if ( $cbModerator ) {
					$menuItems			.=		'<li class="galleryFolderMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'publish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => true ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
				}
			} elseif ( $row->get( 'published' ) > 0 ) {
				$menuItems				.=		'<li class="galleryFolderMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'FOLDER_UNPUBLISH_TYPE', 'Are you sure you want to unpublish this [type]?', array( '[type]' => $typeTranslated ) ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'unpublish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => true ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
			} else {
				$menuItems				.=		'<li class="galleryFolderMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'publish', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => true ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
			}

			$menuItems					.=		'<li class="galleryFolderMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'FOLDER_DELETE_TYPE', 'Are you sure you want to delete this [folder_type] and all its [item_type]?', array( '[folder_type]' => $typeTranslated, '[item_type]' => $galleryType ) ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'folders', 'func' => 'delete', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>'
										.	'</ul>';

			$menuAttr					=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

			$return						.=				'<div class="folderMenu">'
										.					'<div class="galleryFolderMenu btn-group">'
										.						'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.					'</div>'
										.				'</div>';
		}

		$return							.=			'</div>'
										.		'</h3>'
										.	'</div>'
										.	( $row->get( 'description' ) ? '<div class="galleryFolderDescription well well-sm">' . htmlspecialchars( $row->get( 'description' ) ) . '</div>' : null );

		return $return;
	}
}