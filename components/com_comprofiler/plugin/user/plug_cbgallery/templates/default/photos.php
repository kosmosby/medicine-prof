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

class HTML_cbgalleryPhotos
{

	/**
	 * @param cbgalleryItemTable[]      $rows
	 * @param cbPageNav                 $pageNav
	 * @param cbgalleryFolderTable|null $folder
	 * @param bool                      $searching
	 * @param UserTable                 $viewer
	 * @param UserTable                 $user
	 * @param TabTable                  $tab
	 * @param cbTabHandler              $plugin
	 * @return string
	 */
	static public function showPhotos( $rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayPhotos', array( &$rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin ) );

		/** @var Registry $params */
		$params							=	$tab->params;
		$allowDownload					=	$params->get( 'tab_photos_download', 0 );
		$profileOwner					=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator					=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();

		if ( $rows ) {
			static $JS_LOADED			=	0;

			if ( ! $JS_LOADED++ ) {
				$js						=	"$( document ).on( 'click', '.galleryImageScrollLeftIcon', function() {"
										.		"var previous = $( this ).data( 'previous-photo' );"
										.		"if ( previous ) {"
										.			"$( previous ).find( '.galleryImageItem' ).click();"
										.		"}"
										.	"});"
										.	"$( document ).on( 'click', '.galleryImageScrollRightIcon', function() {"
										.		"var next = $( this ).data( 'next-photo' );"
										.		"if ( next ) {"
										.			"$( next ).find( '.galleryImageItem' ).click();"
										.		"}"
										.	"});";

				$_CB_framework->outputCbJQuery( $js );
			}
		}

		$return							=	'<div class="photosItemsContainer">';

		$i								=	0;

		if ( $rows ) foreach ( $rows as $row ) {
			$return						.=		'<div class="galleryContainer galleryContainer' . (int) $tab->get( 'tabid' ) . '_' . $i . ' img-thumbnail">';

			if ( $cbModerator || $profileOwner ) {
				$menuItems				=	'<ul class="galleryItemsMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">'
										.		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'edit', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

				if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( 'photos_item_approval', 0 ) ) {
					if ( $cbModerator ) {
						$menuItems		.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
					}
				} elseif ( $row->get( 'published' ) > 0 ) {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this Photo?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'unpublish', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
				} else {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
				}

				$menuItems				.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Photo?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'delete', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>'
										.	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=			'<div class="galleryContainerMenu">'
										.				'<div class="galleryItemsMenu btn-group">'
										.					'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.				'</div>'
										.			'</div>';
			}

			$title						=	( $row->get( 'title' ) ? htmlspecialchars( $row->get( 'title' ) ) : $row->getFileName() );
			$item						=	$title;
			$logo						=	null;

			if ( $row->checkExists() ) {
				if ( $row->getLinkDomain() ) {
					$showPath			=	htmlspecialchars( $row->getFilePath() );
					$previewPath		=	$showPath;
					$downloadPath		=	$showPath;
				} else {
					$showPath			=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					$previewPath		=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'preview', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					$downloadPath		=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'download', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
				}

				$image					=	'<div class="galleryImageContainer">';

				if ( $pageNav->total > 1 ) {
					$image				.=		'<div class="galleryImageScrollLeft">'
										.			'<table>'
										.				'<tr>'
										.					'<td>'
										.						'<span class="galleryImageScrollLeftIcon fa fa-chevron-left" data-previous-photo=".galleryContainer' . (int) $tab->get( 'tabid' ) . '_' . ( $i == 0 ? ( count( $rows ) - 1 ) : ( $i - 1 ) ) . '"></span>'
										.					'</td>'
										.				'</tr>'
										.			'</table>'
										.		'</div>';
				}

				$image					.=		'<div style="background-image: url(' . $showPath . ')" class="galleryImage"></div>'
										.		'<div class="galleryImageInfo">'
										.			'<div class="galleryImageInfoRow">'
										.				'<div class="galleryImageInfoTitle col-sm-8 text-left"><strong>' . $title . '</strong></div>'
										.				'<div class="galleryImageInfoOriginal col-sm-4 text-right">'
										.					'<a href="' . $showPath . '" target="_blank">'
										.						CBTxt::T( 'Original' )
										.					'</a>'
										.				'</div>'
										.			'</div>';

				if ( $row->get( 'description' ) || $allowDownload ) {
					$image				.=			'<div class="galleryImageInfoRow">'
										.				'<div class="galleryImageInfoDescription col-sm-8 text-left">' . htmlspecialchars( $row->get( 'description' ) ) . '</div>'
										.				'<div class="galleryImageInfoDownload col-sm-4 text-right">';

					if ( $allowDownload ) {
						$image			.=					'<a href="' . $downloadPath . '" target="_blank">'
										.						CBTxt::T( 'Download' )
										.					'</a>';
					}

					$image				.=				'</div>'
										.			'</div>';
				}

				$image					.=		'</div>';

				if ( $pageNav->total > 1 ) {
					$image				.=		'<div class="galleryImageScrollRight">'
										.			'<table>'
										.				'<tr>'
										.					'<td>'
										.						'<span class="galleryImageScrollRightIcon fa fa-chevron-right" data-next-photo=".galleryContainer' . (int) $tab->get( 'tabid' ) . '_' . ( isset( $rows[$i+1] ) ? ( $i + 1 ) : 0 ) . '"></span>'
										.					'</td>'
										.				'</tr>'
										.			'</table>'
										.		'</div>';
				}

				$image					.=	'</div>';

				$item					=	cbTooltip( 1, $image, null, array( '80%', '80%' ), null, $item, 'javascript: void(0);', 'class="galleryImageItem" data-cbtooltip-modal="true" data-cbtooltip-open-solo="document" data-cbtooltip-classes="galleryImageModal"' );
				$logo					=	cbTooltip( 1, $image, null, array( '80%', '80%' ), null, '<div style="background-image: url(' . $previewPath . ')" class="galleryContainerLogo"></div>', 'javascript: void(0);', 'class="galleryImageLogo" data-cbtooltip-modal="true" data-cbtooltip-open-solo="document" data-cbtooltip-classes="galleryImageModal"' );
			}

			$width						=	(int) $params->get( 'tab_photos_width', 200 );

			if ( ! $width ) {
				$width					=	200;
			} elseif ( $width < 100 ) {
				$width					=	100;
			}

			$return						.=			'<div class="galleryContainerInner" style="height: ' . $width . 'px; width: ' . $width . 'px;">'
										.				'<div class="galleryContainerTop" style="height: ' . ( $width - 40 ) . 'px;">'
										.					$logo
										.				'</div>'
										.				'<div class="galleryContainerBottom" style="height: 40px;">'
										.					'<div class="galleryContainerContent">'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">'
										.							'<strong>'
										.								$item
										.							'</strong>'
										.						'</div>'
										.						'<div class="galleryContainerContentRow text-nowrap text-overflow small">'
										.							'<span title="' . htmlspecialchars( $row->get( 'date' ) ) . '">'
										.								cbFormatDate( $row->get( 'date' ), true, (int) $params->get( 'tab_photos_items_time_display', 0 ), $params->get( 'tab_photos_items_date_format', 'M j, Y' ), $params->get( 'tab_photos_items_time_format', ' g:h A' ) )
										.							'</span>'
										.							( $row->get( 'description' ) ? '<div class="galleryContainerDescription">' . cbTooltip( 1, $row->get( 'description' ), $title, 400, null, '<span class="fa fa-info-circle text-muted"></span>' ) . '</div>' : null )
										.						'</div>'
										.					'</div>'
										.				'</div>'
										.			'</div>'
										.		'</div>';

			$i++;
		} else {
			$return						.=		'<div>';

			if ( $searching ) {
				$return					.=			CBTxt::T( 'No photos search results found.' );
			} else {
				if ( $folder ) {
					$return				.=			CBTxt::T( 'This album has no photos.' );
				} else {
					if ( $viewer->get( 'id' ) == $user->get( 'id' ) ) {
						$return			.=			CBTxt::T( 'You have no photos.' );
					} else {
						$return			.=			CBTxt::T( 'This user has no photos.' );
					}
				}
			}

			$return						.=		'</div>';
		}

		if ( $params->get( ( $folder ? 'tab_photos_folder_items_paging' : 'tab_photos_items_paging' ), 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=		'<div class="galleryItemsPaging text-center">'
										.			$pageNav->getListLinks()
										.		'</div>';
		}

		$return							.=	'</div>'
										.	$pageNav->getLimitBox( false );

		return $return;
	}
}