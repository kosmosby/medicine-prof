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

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbgalleryTab
{

	/**
	 * @param string       $photos
	 * @param string       $files
	 * @param string       $videos
	 * @param string       $music
	 * @param UserTable    $viewer
	 * @param UserTable    $user
	 * @param TabTable     $tab
	 * @param cbTabHandler $plugin
	 * @return string
	 */
	static public function showTab( $photos, $files, $videos, $music, $viewer, $user, $tab, $plugin )
	{
		global $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDisplayTab', array( &$photos, &$files, &$videos, &$music, $viewer, $user, $tab, $plugin ) );

		$tabs				=	new cbTabs( 1, 1 );
		$return				=	null;
		$count				=	0;

		if ( $photos ) {
			$count++;
		}

		if ( $files ) {
			$count++;
		}

		if ( $videos ) {
			$count++;
		}

		if ( $music ) {
			$count++;
		}

		$tabbed				=	( $count > 1 );

		if ( $tabbed ) {
			$return			.=	$tabs->startPane( 'galleryTabs' );
		}

		if ( $photos ) {
			if ( $tabbed ) {
				$return		.=		$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'TAB_PHOTOS', 'Photos' ) ), 'galleryTabPhotos' )
							.			$photos
							.		$tabs->endTab();
			} else {
				$return		.=	$photos;
			}
		}

		if ( $files ) {
			if ( $tabbed ) {
				$return		.=		$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'TAB_FILES', 'Files' ) ), 'galleryTabFiles' )
							.			$files
							.		$tabs->endTab();
			} else {
				$return		.=	$files;
			}
		}

		if ( $videos ) {
			if ( $tabbed ) {
				$return		.=		$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'TAB_VIDEOS', 'Videos' ) ), 'galleryTabVideos' )
							.			$videos
							.		$tabs->endTab();
			} else {
				$return		.=	$videos;
			}
		}

		if ( $music ) {
			if ( $tabbed ) {
				$return		.=		$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'TAB_MUSIC', 'Music' ) ), 'galleryTabMusic' )
							.			$music
							.		$tabs->endTab();
			} else {
				$return		.=	$music;
			}
		}

		if ( $tabbed ) {
			$return			.=	$tabs->endPane();
		}

		return $return;
	}
}