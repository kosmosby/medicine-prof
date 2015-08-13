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

class HTML_cbgalleryFiles
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
	static public function showFiles( $rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayFiles', array( &$rows, $pageNav, $folder, $searching, $viewer, $user, $tab, $plugin ) );

		/** @var Registry $params */
		$params							=	$tab->params;
		$profileOwner					=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator					=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();

		$return							=	'<table class="filesItemsContainer table table-hover table-responsive">'
										.		'<thead>'
										.			'<tr>'
										.				'<th colspan="2">&nbsp;</th>'
										.				'<th style="width: 15%;" class="text-center">' . CBTxt::T( 'Type' ) . '</th>'
										.				'<th style="width: 15%;" class="text-left">' . CBTxt::T( 'Size' ) . '</th>'
										.				'<th style="width: 20%;" class="text-left hidden-xs">' . CBTxt::T( 'Date' ) . '</th>'
										.				'<th style="width: 1%;" class="text-right">&nbsp;</th>'
										.			'</tr>'
										.		'</thead>'
										.		'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			$extension					=	null;
			$size						=	0;
			$title						=	( $row->get( 'title' ) ? htmlspecialchars( $row->get( 'title' ) ) : $row->getFileName() );
			$item						=	$title;

			if ( $row->checkExists() ) {
				if ( $row->getLinkDomain() ) {
					$showPath			=	htmlspecialchars( $row->getFilePath() );
					$downloadPath		=	$showPath;
				} else {
					$showPath			=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					$downloadPath		=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'download', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
				}

				$extension				=	$row->getExtension();
				$size					=	$row->getFileSize();

				switch ( $extension ) {
					case 'txt':
					case 'pdf':
					case 'jpg':
					case 'jpeg':
					case 'png':
					case 'gif':
					case 'js':
					case 'css':
					case 'mp4':
					case 'mp3':
					case 'wav':
						$item			=	'<a href="' . $showPath . '" target="_blank">'
										.		$item
										.	'</a>';
						break;
					default:
						$item			=	'<a href="' . $downloadPath . '" target="_blank">'
										.		$item
										.	'</a>';
						break;
				}

				$download				=	'<a href="' . $downloadPath . '" target="_blank" title="' . htmlspecialchars( CBTxt::T( 'Click to Download' ) ) . '" class="filesItemsDownload btn btn-xs btn-default">'
										.		'<span class="fa fa-download"></span>'
										.	'</a>';
			} else {
				$download				=	'<button type="button" class="filesItemsDownload btn btn-xs btn-default disabled">'
										.		'<span class="fa fa-download"></span>'
										.	'</button>';
			}

			if ( $row->get( 'description' ) ) {
				$item					.=	' ' . cbTooltip( 1, $row->get( 'description' ), $title, 400, null, '<span class="fa fa-info-circle text-muted"></span>' );
			}

			$return						.=			'<tr>'
										.				'<td style="width: 1%;" class="text-center">' . $download . '</td>'
										.				'<td class="text-left">' . $item . '</td>'
										.				'<td style="width: 15%;" class="text-center"><span class="filesItemsType fa fa-' . htmlspecialchars( self::getFileIcon( $extension ) ) . '" title="' . htmlspecialchars( ( $extension ? strtoupper( $extension ) : CBTxt::T( 'Unknown' ) ) ) . '"></span></td>'
										.				'<td style="width: 15%;" class="text-left">' . $size . '</td>'
										.				'<td style="width: 20%;" class="text-left hidden-xs">'
										.					'<span title="' . htmlspecialchars( $row->get( 'date' ) ) . '">'
										.						cbFormatDate( $row->get( 'date' ), true, (int) $params->get( 'tab_files_items_time_display', 0 ), $params->get( 'tab_files_items_date_format', 'M j, Y' ), $params->get( 'tab_files_items_time_format', ' g:h A' ) )
										.					'</span>'
										.				'</td>';

			if ( $cbModerator || $profileOwner ) {
				$menuItems				=	'<ul class="galleryItemsMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">'
										.		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'edit', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';

				if ( ( $row->get( 'published' ) == -1 ) && $plugin->params->get( 'files_item_approval', 0 ) ) {
					if ( $cbModerator ) {
						$menuItems		.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Approve' ) . '</a></li>';
					}
				} elseif ( $row->get( 'published' ) > 0 ) {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this File?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'unpublish', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
				} else {
					$menuItems			.=		'<li class="galleryItemsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'publish', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
				}

				$menuItems				.=		'<li class="galleryItemsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this File?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'items', 'func' => 'delete', 'type' => 'files', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>'
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
		} else {
			$return						.=			'<tr>'
										.				'<td colspan="6" class="text-left">';

			if ( $searching ) {
				$return					.=					CBTxt::T( 'No file search results found.' );
			} else {
				if ( $folder ) {
					$return				.=					CBTxt::T( 'This folder has no files.' );
				} else {
					if ( $viewer->get( 'id' ) == $user->get( 'id' ) ) {
						$return			.=					CBTxt::T( 'You have no files.' );
					} else {
						$return			.=					CBTxt::T( 'This user has no files.' );
					}
				}
			}

			$return						.=				'</td>'
										.			'</tr>';
		}

		$return							.=		'</tbody>';

		if ( $params->get( ( $folder ? 'tab_files_folder_items_paging' : 'tab_files_items_paging' ), 1 ) && ( $pageNav->total > $pageNav->limit ) ) {
			$return						.=		'<tfoot>'
										.			'<tr>'
										.				'<td colspan="6" class="galleryItemsPaging text-center">'
										.					$pageNav->getListLinks()
										.				'</td>'
										.			'</tr>'
										.		'</tfoot>';
		}

		$return							.=	'</table>'
										.	$pageNav->getLimitBox( false );

		return $return;
	}

	/**
	 * Returns the fontawesome icon based off extension and that extensions mimetype
	 *
	 * @param string $extension
	 * @return string
	 */
	static public function getFileIcon( $extension )
	{
		$type							=	'file-o';

		if ( ! $extension ) {
			return $type;
		}

		static $cache					=	array();

		if ( ! isset( $cache[$extension] ) ) {
			$mimeParts					=	explode( '/', cbgalleryClass::getMimeTypes( $extension ) );

			switch ( $mimeParts[0] ) {
				case 'text':
					switch ( $extension ) {
						case 'csv':
							$type		=	'file-excel-o';
							break;
						case 'css':
						case 'html':
						case 'htm':
							$type		=	'file-code-o';
							break;
						default:
							$type		=	'file-text-o';
							break;
					}
					break;
				case 'video':
					$type				=	'file-video-o';
					break;
				case 'audio':
					$type				=	'file-audio-o';
					break;
				case 'image':
					$type				=	'file-image-o';
					break;
				default:
					switch ( $extension ) {
						case 'pdf':
							$type		=	'file-pdf-o';
							break;
						case 'zip':
						case '7z':
						case 'rar':
						case 'tar':
						case 'iso':
							$type		=	'file-archive-o';
							break;
						case 'js':
						case 'php':
						case 'xml':
						case 'java':
							$type		=	'file-code-o';
							break;
						case 'ods':
						case 'xls':
						case 'xlsx':
						case 'xlt':
							$type		=	'file-excel-o';
							break;
						case 'doc':
						case 'docx':
						case 'odt':
						case 'dot':
							$type		=	'file-word-o';
							break;
					}
					break;
			}

			$cache[$extension]			=	$type;
		}

		return $cache[$extension];
	}
}