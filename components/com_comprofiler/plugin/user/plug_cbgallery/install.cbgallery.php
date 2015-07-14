<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\Registry;
use CBLib\Database\Table\Table;
use CB\Database\Table\PluginTable;
use CB\Database\Table\FieldTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

function plug_cbgallery_install()
{
	global $_CB_framework, $_CB_database;

	$plugin								=	new PluginTable();

	if ( $plugin->load( array( 'element' => 'cb.profilegallery' ) ) ) {
		$path							=	$_CB_framework->getCfg( 'absolute_path' );
		$indexPath						=	$path . '/components/com_comprofiler/plugin/user/plug_cbgallery/index.html';
		$oldFilesPath					=	$path . '/images/comprofiler/plug_profilegallery';
		$newFilesPath					=	$path . '/images/comprofiler/plug_cbgallery';

		$query							=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plug_profilegallery' );
		$_CB_database->setQuery( $query );
		$rows							=	$_CB_database->loadObjectList( null, '\CBLib\Database\Table\Table', array( $_CB_database, '#__comprofiler_plug_profilegallery', 'id' ) );

		/** @var $rows Table[] */
		foreach ( $rows as $row ) {
			$oldFilePath				=	$oldFilesPath . '/' . (int) $row->get( 'userid' );

			if ( in_array( $row->get( 'pgitemtype' ), array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) {
				$type					=	'photos';
			} else {
				$type					=	'files';
			}

			$newFilePath				=	$newFilesPath . '/' . (int) $row->get( 'userid' ) . '/' . $type;

			if ( ( ! file_exists( $oldFilePath . '/' . $row->get( 'pgitemfilename' ) ) ) || ( ( $type == 'photos' ) && ( ! file_exists( $oldFilePath . '/tn' . $row->get( 'pgitemfilename' ) ) ) ) ) {
				continue;
			}

			$cleanFileName				=	str_replace( 'pg_', '', pathinfo( $row->get( 'pgitemfilename' ), PATHINFO_FILENAME ) );
			$newFileName				=	uniqid( $cleanFileName . '_' ) . '.' . strtolower( pathinfo( $row->get( 'pgitemfilename' ), PATHINFO_EXTENSION ) );

			if ( cbReadDirectory( $newFilePath, '^' . preg_quote( $cleanFileName ) ) ) {
				$query					=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $row->get( 'userid' )
										.	"\n AND " . $_CB_database->NameQuote( 'value' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $cleanFileName, true ) . '%', false );
				$_CB_database->setQuery( $query );
				if ( $_CB_database->loadResult() ) {
					continue;
				}
			}

			if ( ! is_dir( $newFilesPath ) ) {
				$oldMask				=	@umask( 0 );

				if ( @mkdir( $newFilesPath, 0755, true ) ) {
					@umask( $oldMask );
					@chmod( $newFilesPath, 0755 );

					if ( ! file_exists( $newFilesPath . '/index.html' ) ) {
						@copy( $indexPath, $newFilesPath . '/index.html' );
						@chmod( $newFilesPath . '/index.html', 0755 );
					}
				} else {
					@umask( $oldMask );
				}
			}

			if ( ! file_exists( $newFilesPath . '/.htaccess' ) ) {
				file_put_contents( $newFilesPath . '/.htaccess', 'deny from all' );
			}

			if ( ! is_dir( $newFilePath ) ) {
				$oldMask				=	@umask( 0 );

				if ( @mkdir( $newFilePath, 0755, true ) ) {
					@umask( $oldMask );
					@chmod( $newFilePath, 0755 );

					if ( ! file_exists( $newFilePath . '/index.html' ) ) {
						@copy( $indexPath, $newFilePath . '/index.html' );
						@chmod( $newFilePath . '/index.html', 0755 );
					}
				} else {
					@umask( $oldMask );
				}
			}

			if ( ! @copy( $oldFilePath . '/' . $row->get( 'pgitemfilename' ), $newFilePath . '/' . $newFileName ) ) {
				continue;
			} else {
				@chmod( $newFilePath . '/' . $newFileName, 0755 );
			}

			if ( $type == 'photos' ) {
				if ( ! @copy( $oldFilePath . '/tn' . $row->get( 'pgitemfilename' ), $newFilePath . '/tn' . $newFileName ) ) {
					continue;
				} else {
					@chmod( $newFilePath . '/tn' . $newFileName, 0755 );
				}
			}

			$item						=	new Table( null, '#__comprofiler_plugin_gallery_items', 'id' );

			$item->set( 'user_id', (int) $row->get( 'userid' ) );
			$item->set( 'type', $type );
			$item->set( 'value', $newFileName );
			$item->set( 'folder', 0 );
			$item->set( 'title', $row->get( 'pgitemtitle' ) );
			$item->set( 'description', $row->get( 'pgitemdescription' ) );
			$item->set( 'date', $row->get( 'pgitemdate' ) );
			$item->set( 'published', ( $row->get( 'pgitemapproved', 0 ) ? (int) $row->get( 'pgitempublished', 0 ) : -1 ) );

			if ( ! $item->store() ) {
				@unlink( $newFilePath . '/' . $newFileName );

				if ( $type == 'photos' ) {
					@unlink( $newFilePath . '/tn' . $newFileName );
				}
			}
		}

		$field							=	new FieldTable();

		if ( $field->load( array( 'name' => 'cb_pgtotalquotaitems' ) ) ) {
			$field->set( 'type', 'integer' );
			$field->set( 'tabid', 11 );
			$field->set( 'pluginid', 1 );
			$field->set( 'readonly', 1 );
			$field->set( 'calculated', 0 );
			$field->set( 'sys', 0 );

			$field->store();
		}

		$gallery						=	new PluginTable();

		if ( $gallery->load( array( 'element' => 'cbgallery' ) ) ) {
			$galleryParams				=	new Registry( $gallery->params );

			$galleryParams->set( 'photos_item_limit', 'cb_pgtotalquotaitems' );
			$galleryParams->set( 'files_item_limit', 'cb_pgtotalquotaitems' );

			$gallery->set( 'params', $galleryParams->asJson() );

			$gallery->store();
		}

		ob_start();
		$plgInstaller					=	new cbInstallerPlugin();

		$plgInstaller->uninstall( $plugin->id, 'com_comprofiler' );
		ob_end_clean();
	}
}