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

class HTML_cbgalleryVideos
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
	static public function showVideos( $rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayVideos', array( &$rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin ) );

		/** @var Registry $params */
		$params							=	$tab->params;
		$allowDownload					=	$params->get( 'tab_videos_download', 0 );
		$profileOwner					=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator					=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$return							=	null;

		if ( $rows ) {
			$js							=	"var videosPlayer = null;"
										.	"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . "' ).on( 'click', function( event ) {"
										.		"event.preventDefault();"
										.		"if ( $( this ).hasClass( 'videosItemPlaying' ) ) {"
										.			"if ( videosPlayer != null ) {"
										.				"videosPlayer.pause();"
										.			"}"
										.		"} else if ( $( this ).hasClass( 'videosItemPaused' ) ) {"
										.			"if ( videosPlayer != null ) {"
										.				"videosPlayer.play();"
										.			"}"
										.		"} else {"
										.			"$( '.videosItemsPlayer" . (int) $tab->get( 'tabid' ) . "Container' ).hide();"
										.			"if ( videosPlayer != null ) {"
										.				"videosPlayer.remove();"
										.				"$( '.videosItemsPlayer" . (int) $tab->get( 'tabid' ) . "Container > .mejs-offscreen' ).remove();"
										.			"}"
										.			"$( '#videosItemsPlayer" . (int) $tab->get( 'tabid' ) . "' ).attr( 'src', $( this ).attr( 'href' ) ).attr( 'type', $( this ).data( 'mimetype' ) ).attr( 'controls', 'controls' ).attr( 'autoplay', 'autoplay' ).attr( 'preload', 'none' );"
										.			"videosPlayer = new MediaElementPlayer( '#videosItemsPlayer" . (int) $tab->get( 'tabid' ) . "', {"
										.				"isVideo: true,"
										.				"success: function( media ) {"
										.					"media.addEventListener( 'play', function() {"
										.						"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . ".active' ).removeClass( 'videosItemPaused' ).addClass( 'videosItemPlaying' ).find( '.fa' ).removeClass( 'fa-play' ).addClass( 'fa-pause' );"
										.					"}, false );"
										.					"media.addEventListener( 'pause', function() {"
										.						"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . ".active' ).removeClass( 'videosItemPlaying' ).addClass( 'videosItemPaused' ).find( '.fa' ).removeClass( 'fa-pause' ).addClass( 'fa-play' );"
										.					"}, false );"
										.				"}"
										.			"});"
										.			"$( '.videosItemsPlayer" . (int) $tab->get( 'tabid' ) . "Container' ).slideDown();"
										.			"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . "' ).find( '.fa' ).removeClass( 'fa-play fa-pause' ).addClass( 'fa-play' );"
										.			"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . "' ).removeClass( 'active videosItemPlaying videosItemPaused' );"
										.			"$( '.videosItemPlay" . (int) $tab->get( 'tabid' ) . "' ).closest( 'tr' ).removeClass( 'active' );"
										.			"$( this ).addClass( 'active videosItemPaused' );"
										.			"$( this ).closest( 'tr' ).addClass( 'active' );"
										.			"videosPlayer.play();"
										.		"}"
										.	"});";

			$_CB_framework->outputCbJQuery( $js, 'media' );

			$width						=	(int) $params->get( 'tab_videos_width', 0 );

			$return						.=	'<div class="videosItemsPlayer' . (int) $tab->get( 'tabid' ) . 'Container text-center" style="display: none; margin: 0 auto 10px auto;' . ( $width ? ' max-width: ' . $width . 'px;' : null ) . '">'
										.		'<video width="640" height="360" style="width: 100%; height: 100%;" id="videosItemsPlayer' . (int) $tab->get( 'tabid' ) . '" controls="controls" autoplay="autoplay" preload="none"></video>'
										.	'</div>';
		}

		$return							.=	'<table class="videosItemsContainer table table-hover table-responsive">'
										.		'<thead>'
										.			'<tr>'
										.				'<th style="width: 1%;" class="text-left">#</th>'
										.				'<th colspan="' . ( $allowDownload ? 3 : 2 ) . '">&nbsp;</th>'
										.				'<th style="width: 20%;" class="text-left hidden-xs">' . CBTxt::T( 'Date' ) . '</th>'
										.				'<th style="width: 1%;" class="text-right">&nbsp;</th>'
										.			'</tr>'
										.		'</thead>'
										.		'<tbody>';

		$i								=	0;

		if ( $rows ) foreach ( $rows as $row ) {
			$exists						=	$row->checkExists();
			$title						=	( $row->get( 'title' ) ? htmlspecialchars( $row->get( 'title' ) ) : $row->getFileName() );
			$item						=	$title;

			if ( $exists ) {
				if ( $row->getLinkDomain() ) {
					$showPath			=	htmlspecialchars( $row->getFilePath() );
					$downloadPath		=	$showPath;
				} else {
					$showPath			=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					$downloadPath		=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'download', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
				}

				$play					=	'<a href="' . $showPath . '" title="' . htmlspecialchars( CBTxt::T( 'Click to Play' ) ) . '" class="videosItemsPlay videosItemPlay' . (int) $tab->get( 'tabid' ) . ' btn btn-xs btn-default" data-mimetype="' . htmlspecialchars( $row->getMimeType() ) . '">'
										.		'<span class="fa fa-play"></span>'
										.	'</a>';

				$item					=	'<a href="' . $showPath . '" target="_blank">'
										.		$item
										.	'</a>';

				$download				=	'<a href="' . $downloadPath . '" target="_blank" title="' . htmlspecialchars( CBTxt::T( 'Click to Download' ) ) . '" class="videosItemsDownload btn btn-xs btn-default">'
										.		'<span class="fa fa-download"></span>'
										.	'</a>';
			} else {
				$play					=	'<button type="button" class="videosItemsPlay btn btn-xs btn-default disabled">'
										.		'<span class="fa fa-play"></span>'
										.	'</button>';

				$download				=	'<button type="button" class="videosItemsDownload btn btn-xs btn-default disabled">'
										.		'<span class="fa fa-download"></span>'
										.	'</button>';
			}

			if ( $row->get( 'description' ) ) {
				$item					.=	' ' . cbTooltip( 1, $row->get( 'description' ), $title, 400, null, '<span class="fa fa-info-circle text-muted"></span>' );
			}

			$return						.=			'<tr' . ( $exists ? ' class="videosItemPlayable"' : null ) . '>'
										.				'<td style="width: 1%;" class="text-center">' . ( $i + 1 ) . '</td>'
										.				'<td style="width: 1%;" class="text-center">' . $play . '</td>'
										.				( $allowDownload ? '<td style="width: 1%;" class="text-center">' . $download . '</td>' : null )
										.				'<td class="text-left">' . $item . '</td>'
										.				'<td style="width: 20%;" class="text-left hidden-xs">'
										.					'<span title="' . htmlspecialchars( $row->get( 'date' ) ) . '">'
										.						cbFormatDate( $row->get( 'date' ), true, (int) $params->get( 'tab_videos_items_time_display', 0 ), $params->get( 'tab_videos_items_date_format', 'M j, Y' ), $params->get( 'tab_videos_items_time_format', ' g:h A' ) )
										.					'</span>'
										.				'</td>';

			if ( $cbModerator || $profileOwner ) {
				$menuItems				=	'<ul class="galleryItemsMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">'
										.		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'edit', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

				if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( 'videos_item_approval', 0 ) ) {
					if ( $cbModerator ) {
						$menuItems		.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
					}
				} elseif ( $row->get( 'published' ) > 0 ) {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this Video?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'unpublish', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
				} else {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
				}

				$menuItems				.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Video?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'delete', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>'
										.	'</ul>';

				$menuAttr				=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return					.=				'<td style="width: 1%;" class="text-right">'
										.					'<div class="galleryItemsMenu btn-group">'
										.						'<button type="button" ' . trim( $menuAttr ) . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
										.					'</div>'
										.				'</td>';
			} else{
				$return					.=				'<td style="width: 1%;"></td>';
			}

			$return						.=			'</tr>';

			$i++;
		} else {
			$return						.=			'<tr>'
										.				'<td colspan="' . ( $allowDownload ? 6 : 5 ) . '" class="text-left">';

			if ( $searching ) {
				$return					.=					CBTxt::T( 'No videos search results found.' );
			} else {
				if ( $folder ) {
					$return				.=					CBTxt::T( 'This album has no videos.' );
				} else {
					if ( $viewer->get( 'id' ) == $user->get( 'id' ) ) {
						$return			.=					CBTxt::T( 'You have no videos.' );
					} else {
						$return			.=					CBTxt::T( 'This user has no videos.' );
					}
				}
			}

			$return						.=				'</td>'
										.			'</tr>';
		}

		$return							.=		'</tbody>';

		if ( $params->get( ( $folder ? 'tab_videos_folder_items_paging' : 'tab_videos_items_paging' ), 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=		'<tfoot>'
										.			'<tr>'
										.				'<td colspan="' . ( $allowDownload ? 6 : 5 ) . '" class="galleryItemsPaging text-center">'
										.					$pageNav->getListLinks()
										.				'</td>'
										.			'</tr>'
										.		'</tfoot>';
		}

		$return							.=	'</table>'
										.	$pageNav->getLimitBox( false );

		return $return;
	}
}