<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;
use CBLib\Database\Table\Table;
use CB\Database\Table\UserTable;
use CB\Database\Table\TabTable;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Input\Get;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'deleteItems', 'cbgalleryPlugin' );

class cbgalleryClass
{

	/**
	 * Checks if a user is create limited for a specific type
	 *
	 * @param UserTable $user
	 * @param string    $type
	 * @param bool      $folder
	 * @return bool
	 */
	static public function canUserCreate( $user, $type, $folder )
	{
		global $_CB_database, $_PLUGINS;

		$userId									=	(int) $user->get( 'id' );

		if ( ! $userId ) {
			return false;
		}

		static $mods							=	array();

		if ( ! isset( $mods[$userId] ) ) {
			$mods[$userId]						=	Application::User( (int) $userId )->isGlobalModerator();
		}

		if ( $mods[$userId] ) {
			return true;
		}

		static $params							=	null;

		if ( ! $params ) {
			$plugin								=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );
			$params								=	$_PLUGINS->getPluginParams( $plugin );
		}

		if ( ( ! $folder ) && ( ! $params->get( $type . '_item_upload', 1 ) ) && ( ! $params->get( $type . '_item_link', 0 ) ) ) {
			return false;
		}

		static $cache							=	array();

		if ( ! isset( $cache[$userId][$type][$folder] ) ) {
			$canCreate							=	false;

			if ( Application::User( (int) $userId )->canViewAccessLevel( (int) $params->get( $type . ( $folder ? '_folder_create_access' : '_item_create_access' ), 2 ) ) ) {
				$itemLimit						=	$params->get( $type . ( $folder ? '_folder_limit' : '_item_limit' ), null );

				if ( ! $itemLimit ) {
					$itemLimit					=	(int) $params->get( $type . ( $folder ? '_folder_limit_custom' : '_item_limit_custom' ), null );
				} else {
					$limitField					=	CBuser::getInstance( (int) $userId, false )->getField( $itemLimit, null, 'php', 'none', 'profile', 0, true );

					if ( is_array( $limitField ) ) {
						$itemLimit				=	array_shift( $limitField );

						if ( is_array( $itemLimit ) ) {
							$itemLimit			=	implode( '|*|', $itemLimit );
						}
					} else {
						$itemLimit				=	$user->get( $limitField, 0 );
					}

					$itemLimit					=	(int) $itemLimit;
				}

				if ( $itemLimit ) {
					$query						=	'SELECT COUNT(*)'
												.	"\n FROM " . $_CB_database->NameQuote( ( $folder ? '#__comprofiler_plugin_gallery_folders' : '#__comprofiler_plugin_gallery_items' ) )
												.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
												.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $userId;
					$_CB_database->setQuery( $query );
					$total						=	$_CB_database->loadResult();

					if ( $total < $itemLimit ) {
						$canCreate				=	true;
					}
				} else {
					$canCreate					=	true;
				}
			}

			$cache[$userId][$type][$folder]		=	$canCreate;
		}

		return $cache[$userId][$type][$folder];
	}

	/**
	 * @param null|array $files
	 * @param bool       $loadGlobal
	 * @param bool       $loadHeader
	 */
	static public function getTemplate( $files = null, $loadGlobal = true, $loadHeader = true )
	{
		global $_CB_framework, $_PLUGINS;

		static $tmpl							=	array();

		if ( ! $files ) {
			$files								=	array();
		} elseif ( ! is_array( $files ) ) {
			$files								=	array( $files );
		}

		$id										=	md5( serialize( array( $files, $loadGlobal, $loadHeader ) ) );

		if ( ! isset( $tmpl[$id] ) ) {
			$plugin								=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );

			if ( ! $plugin ) {
				return;
			}

			$livePath							=	$_PLUGINS->getPluginLivePath( $plugin );
			$absPath							=	$_PLUGINS->getPluginPath( $plugin );
			$params								=	$_PLUGINS->getPluginParams( $plugin );

			$template							=	$params->get( 'general_template', 'default' );
			$paths								=	array( 'global_css' => null, 'php' => null, 'css' => null, 'js' => null, 'override_css' => null );

			foreach ( $files as $file ) {
				$file							=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $file );
				$globalCss						=	'/templates/' . $template . '/template.css';
				$overrideCss					=	'/templates/' . $template . '/override.css';

				if ( $file ) {
					$php						=	$absPath . '/templates/' . $template . '/' . $file . '.php';
					$css						=	'/templates/' . $template . '/' . $file . '.css';
					$js							=	'/templates/' . $template . '/' . $file . '.js';
				} else {
					$php						=	null;
					$css						=	null;
					$js							=	null;
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( ! file_exists( $absPath . $globalCss ) ) {
						$globalCss				=	'/templates/default/template.css';
					}

					if ( file_exists( $absPath . $globalCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $livePath . $globalCss );

						$paths['global_css']	=	$livePath . $globalCss;
					}
				}

				if ( $file ) {
					if ( ! file_exists( $php ) ) {
						$php					=	$absPath . '/templates/default/' . $file . '.php';
					}

					if ( file_exists( $php ) ) {
						require_once( $php );

						$paths['php']			=	$php;
					}

					if ( $loadHeader ) {
						if ( ! file_exists( $absPath . $css ) ) {
							$css				=	'/templates/default/' . $file . '.css';
						}

						if ( file_exists( $absPath . $css ) ) {
							$_CB_framework->document->addHeadStyleSheet( $livePath . $css );

							$paths['css']		=	$livePath . $css;
						}

						if ( ! file_exists( $absPath . $js ) ) {
							$js					=	'/templates/default/' . $file . '.js';
						}

						if ( file_exists( $absPath . $js ) ) {
							$_CB_framework->document->addHeadScriptUrl( $livePath . $js );

							$paths['js']		=	$livePath . $js;
						}
					}
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( file_exists( $absPath . $overrideCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $livePath . $overrideCss );

						$paths['override_css']	=	$livePath . $overrideCss;
					}
				}
			}

			$tmpl[$id]							=	$paths;
		}
	}

	/**
	 * Returns file size formatted from bytes
	 *
	 * @param int $bytes
	 * @return string
	 */
	static public function getFormattedFileSize( $bytes )
	{
		if ( $bytes >= 1099511627776 ) {
			$size							=	CBTxt::T( 'FILESIZE_FORMATTED_TB', '%%COUNT%% TB|%%COUNT%% TBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1099511627776, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1073741824 ) {
			$size							=	CBTxt::T( 'FILESIZE_FORMATTED_GB', '%%COUNT%% GB|%%COUNT%% GBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1073741824, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1048576 ) {
			$size							=	CBTxt::T( 'FILESIZE_FORMATTED_MB', '%%COUNT%% MB|%%COUNT%% MBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1048576, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1024 ) {
			$size							=	CBTxt::T( 'FILESIZE_FORMATTED_KB', '%%COUNT%% KB|%%COUNT%% KBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1024, 2, '.', '' ) ) );
		} else {
			$size							=	CBTxt::T( 'FILESIZE_FORMATTED_B', '%%COUNT%% B|%%COUNT%% Bs', array( '%%COUNT%%' => (float) number_format( $bytes, 2, '.', '' ) ) );
		}

		return $size;
	}

	/**
	 * Returns a list of extensions supported by the provided gallery type
	 *
	 * @param string $type
	 * @return array
	 */
	static public function getExtensions( $type )
	{
		global $_PLUGINS;

		switch( $type ) {
			case 'photos':
				$extensions		=	array( 'jpg', 'jpeg', 'gif', 'png' );
				break;
			case 'files':
				$plugin			=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );
				$params			=	$_PLUGINS->getPluginParams( $plugin );

				$extensions		=	explode( ',', $params->get( 'files_item_extensions', 'zip,rar,doc,pdf,txt,xls' ) );
				break;
			case 'videos':
				$extensions		=	array( 'mp4', 'ogv', 'ogg', 'webm', 'm4v' );
				break;
			case 'music':
				$extensions		=	array( 'mp3', 'oga', 'ogg', 'weba', 'wav', 'm4a' );
				break;
			default:
				$extensions		=	array();
				break;
		}

		return $extensions;
	}

	/**
	 * Returns a list of mimetypes based off extension
	 *
	 * @param array|string $extensions
	 * @return array|string
	 */
	static public function getMimeTypes( $extensions )
	{
		$mimeTypes				=	cbGetMimeFromExt( $extensions );

		if ( is_array( $extensions ) ) {
			if ( in_array( 'm4v', $extensions ) ) {
				$mimeTypes[]	=	'video/mp4';
			}
		} else {
			if ( $extensions == 'm4v' ) {
				$mimeTypes		=	'video/mp4';
			}
		}

		if ( is_array( $extensions ) ) {
			if ( in_array( 'mp3', $extensions ) ) {
				$mimeTypes[]	=	'audio/mp3';
			}
		} else {
			if ( $extensions == 'mp3' ) {
				$mimeTypes		=	'audio/mp3';
			}
		}

		if ( is_array( $extensions ) ) {
			if ( in_array( 'm4a', $extensions ) ) {
				$mimeTypes[]	=	'audio/mp4';
			}
		} else {
			if ( $extensions == 'm4a' ) {
				$mimeTypes		=	'audio/mp4';
			}
		}

		if ( is_array( $mimeTypes ) ) {
			$mimeTypes			=	array_unique( $mimeTypes );
		}

		return $mimeTypes;
	}
}

class cbgalleryItemTable extends Table
{
	public $id				=	null;
	public $user_id			=	null;
	public $type			=	null;
	public $value			=	null;
	public $file			=	null;
	public $folder			=	null;
	public $title			=	null;
	public $description		=	null;
	public $date			=	null;
	public $published		=	null;
	public $params			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_plugin_gallery_items';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'id';

	/**
	 * @return bool
	 */
	public function check()
	{
		global $_PLUGINS;

		$plugin								=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );
		$params								=	$_PLUGINS->getPluginParams( $plugin );

		$minFileSize						=	$params->get( $this->get( 'type' ) . '_item_min_size', 0 );
		$maxFileSize						=	$params->get( $this->get( 'type' ) . '_item_max_size', 1024 );

		switch( $this->get( 'type' ) ) {
			case 'photos':
				$type						=	CBTxt::T( 'Photo' );
				break;
			case 'files':
				$type						=	CBTxt::T( 'File' );
				break;
			case 'videos':
				$type						=	CBTxt::T( 'Video' );
				break;
			case 'music':
				$type						=	CBTxt::T( 'Music' );
				break;
			default:
				$type						=	CBTxt::T( 'Item' );
				break;
		}

		$extensions							=	cbgalleryClass::getExtensions( $this->get( 'type' ) );

		if ( $this->get( 'user_id' ) == '' ) {
			$this->setError( CBTxt::T( 'Owner not specified!' ) );

			return false;
		} elseif ( $this->get( 'type' ) == '' ) {
			$this->setError( CBTxt::T( 'Type not specified!' ) );

			return false;
		} elseif ( ( ! $this->get( 'id' ) ) && ( ( ! $this->get( 'value' ) ) && ( ( ! isset( $_FILES['file']['tmp_name'] ) ) || empty( $_FILES['file']['tmp_name'] ) ) ) ) {
			$this->setError( CBTxt::T( 'ITEM_NOT_SPECIFIED', '[type] not specified!', array( '[type]' => $type ) ) );

			return false;
		} elseif ( isset( $_FILES['file']['tmp_name'] ) && ( ! empty( $_FILES['file']['tmp_name'] ) ) ) {
			$fileExtension					=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ) );

			if ( ( ! $fileExtension ) || ( ! in_array( $fileExtension, $extensions ) ) ) {
				$this->setError( CBTxt::T( 'ITEM_UPLOAD_INVALID_EXT', 'Invalid file extension [ext]. Please upload only [exts]!', array( '[ext]' => $fileExtension, '[exts]' => implode( ', ', $extensions ) ) ) );

				return false;
			}

			$fileSize					=	$_FILES['file']['size'];

			if ( $minFileSize && ( ( $fileSize / 1024 ) < $minFileSize ) ) {
				$this->setError( CBTxt::T( 'ITEM_UPLOAD_TOO_SMALL', 'The file is too small, the minimum is [size]!', array( '[size]' => cbgalleryClass::getFormattedFileSize( $minFileSize * 1024 ) ) ) );

				return false;
			}

			if ( $maxFileSize && ( ( $fileSize / 1024 ) > $maxFileSize ) ) {
				$this->setError( CBTxt::T( 'ITEM_UPLOAD_TOO_LARGE', 'The file size exceeds the maximum of [size]!', array( '[size]' => cbgalleryClass::getFormattedFileSize( $maxFileSize * 1024 ) ) ) );

				return false;
			}
		} else {
			$linkDomain						=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $this->get( 'value' ), PHP_URL_HOST ) );

			if ( $linkDomain && ( ! ( in_array( $linkDomain, array( 'youtube', 'youtu' ) ) && ( $this->get( 'type' ) == 'videos' ) ) ) ) {
				$linkExists					=	false;

				try {
					$request				=	new GuzzleHttp\Client();

					$header					=	$request->head( $this->get( 'value' ) );

					if ( ( $header !== false ) && ( $header->getStatusCode() == 200 ) ) {
						$linkExists			=	true;
					}
				} catch( Exception $e ) {}

				if ( ! $linkExists ) {
					$this->setError( CBTxt::T( 'ITEM_LINK_INVALID_URL', 'Invalid file URL. Please ensure the URL exists!' ) );

					return false;
				}

				$linkExtension				=	strtolower( pathinfo( $this->get( 'value' ), PATHINFO_EXTENSION ) );

				if ( ( ! $linkExtension ) || ( ! in_array( $linkExtension, $extensions ) ) ) {
					if ( $this->get( 'type' ) == 'videos' ) {
						$extensions[]		=	'youtube';
					}

					$this->setError( CBTxt::T( 'ITEM_LINK_INVALID_EXT', 'Invalid file extension [ext]. Please upload only [exts]!', array( '[ext]' => $linkExtension, '[exts]' => implode( ', ', $extensions ) ) ) );

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param bool $updateNulls
	 * @return bool
	 */
	public function store( $updateNulls = false )
	{
		global $_CB_framework, $_PLUGINS;

		// TODO: Store the filename, filesize, extension, and mimetype to params
		// TODO: Also store height/width for image fields (add new functions for this as well)

		$new							=	( $this->get( 'id' ) ? false : true );

		if ( ! $new ) {
			$_PLUGINS->trigger( 'gallery_onBeforeUpdateItem', array( &$this ) );
		} else {
			$_PLUGINS->trigger( 'gallery_onBeforeCreateItem', array( &$this ) );
		}

		if ( isset( $_FILES['file']['tmp_name'] ) && ( ! empty( $_FILES['file']['tmp_name'] ) ) ) {
			$path						=	$_CB_framework->getCfg( 'absolute_path' );
			$indexPath					=	$path . '/components/com_comprofiler/plugin/user/plug_cbgallery/index.html';
			$filesPath					=	$path . '/images/comprofiler/plug_cbgallery';
			$filePath					=	$filesPath . '/' . (int) $this->get( 'user_id' ) . '/' . $this->get( 'type' );

			if ( ! is_dir( $filesPath ) ) {
				$oldMask				=	@umask( 0 );

				if ( @mkdir( $filesPath, 0755, true ) ) {
					@umask( $oldMask );
					@chmod( $filesPath, 0755 );

					if ( ! file_exists( $filesPath . '/index.html' ) ) {
						@copy( $indexPath, $filesPath . '/index.html' );
						@chmod( $filesPath . '/index.html', 0755 );
					}
				} else {
					@umask( $oldMask );
				}
			}

			if ( ! file_exists( $filesPath . '/.htaccess' ) ) {
				file_put_contents( $filesPath . '/.htaccess', 'deny from all' );
			}

			if ( ! is_dir( $filePath ) ) {
				$oldMask				=	@umask( 0 );

				if ( @mkdir( $filePath, 0755, true ) ) {
					@umask( $oldMask );
					@chmod( $filePath, 0755 );

					if ( ! file_exists( $filePath . '/index.html' ) ) {
						@copy( $indexPath, $filePath . '/index.html' );
						@chmod( $filePath . '/index.html', 0755 );
					}
				} else {
					@umask( $oldMask );
				}
			}

			$fileExtension				=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ) );
			$fileName					=	Get::clean( pathinfo( $_FILES['file']['name'], PATHINFO_FILENAME ), GetterInterface::STRING ) . '.' . $fileExtension;
			$fileId						=	uniqid();

			if ( $this->get( 'type' ) == 'photos' ) {
				$plugin					=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );
				$params					=	$_PLUGINS->getPluginParams( $plugin );

				$resample				=	$params->get( 'photos_resample', 1 );
				$aspectRatio			=	$params->get( 'photos_maintain_aspect_ratio', 1 );
				$imageHeight			=	(int) $params->get( 'photos_image_height', 640 );

				if ( ! $imageHeight ) {
					$imageHeight		=	640;
				}

				$imageWidth				=	(int) $params->get( 'photos_image_width', 1280 );

				if ( ! $imageWidth ) {
					$imageWidth			=	1280;
				}

				$thumbHeight			=	(int) $params->get( 'photos_thumbnail_height', 320 );

				if ( ! $thumbHeight ) {
					$thumbHeight		=	320;
				}

				$thumbWidth				=	(int) $params->get( 'photos_thumbnail_width', 640 );

				if ( ! $thumbWidth ) {
					$thumbWidth			=	640;
				}

				$conversionType			=	(int) ( isset( $ueConfig['conversiontype'] ) ? $ueConfig['conversiontype'] : 0 );
				$imageSoftware			=	( $conversionType == 5 ? 'gmagick' : ( $conversionType == 1 ? 'imagick' : 'gd' ) );

				try {
					$image				=	new \CBLib\Image\Image( $imageSoftware, $resample, $aspectRatio );

					$image->setName( $fileId );
					$image->setSource( $_FILES['file'] );
					$image->setDestination( $filePath . '/' );

					$image->processImage( $imageWidth, $imageHeight );

					$newFileName		=	$image->getCleanFilename();

					$image->setName( 'tn' . $fileId );

					$image->processImage( $thumbWidth, $thumbHeight );

					$this->set( 'value', $newFileName );
					$this->set( 'file', $fileName );
				} catch ( Exception $e ) {
					$this->setError( $e->getMessage() );

					return false;
				}
			} else {
				$newFileName			=	$fileId . '.' . $fileExtension;

				if ( ! move_uploaded_file( $_FILES['file']['tmp_name'], $filePath . '/' . $newFileName ) ) {
					$this->setError( CBTxt::T( 'ITEM_FILE_UPLOAD_FAILED', 'The file [file] failed to upload!', array( '[file]' => $newFileName ) ) );

					return false;
				} else {
					@chmod( $filePath . '/' . $newFileName, 0755 );
				}

				$this->set( 'value', $newFileName );
				$this->set( 'file', $fileName );
			}
		} elseif ( preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $this->get( 'value' ), PHP_URL_HOST ) ) )  {
			$this->set( 'file', '' );
		} elseif ( ! $this->get( 'file' ) ) {
			$this->set( 'file', $this->get( 'value' ) );
		}

		$this->set( 'date', $this->get( 'date', $_CB_framework->getUTCDate() ) );

		if ( ! parent::store( $updateNulls ) ) {
			return false;
		}

		if ( ! $new ) {
			$_PLUGINS->trigger( 'gallery_onAfterUpdateItem', array( $this ) );
		} else {
			$_PLUGINS->trigger( 'gallery_onAfterCreateItem', array( $this ) );
		}

		return true;
	}

	/**
	 * @param null|int $id
	 * @return bool
	 */
	public function delete( $id = null )
	{
		global  $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDeleteItem', array( &$this ) );

		if ( ( ! $this->getLinkDomain() ) && $this->checkExists() ) {
			@unlink( $this->getFilePath() );

			if ( $this->checkExists( true ) ) {
				@unlink( $this->getFilePath( true ) );
			}
		}

		if ( ! parent::delete( $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'gallery_onAfterDeleteItem', array( $this ) );

		return true;
	}

	/**
	 * Returns the domain if the item is a link
	 *
	 * @return string
	 */
	public function getLinkDomain()
	{
		static $cache		=	array();

		$id					=	$this->get( 'value' );

		if ( ! isset( $cache[$id] ) ) {
			$cache[$id]		=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $id, PHP_URL_HOST ) );
		}

		return $cache[$id];
	}

	/**
	 * Returns the clean absolute path to the items file
	 *
	 * @param bool $thumbnail
	 * @return null|string
	 */
	public function getFilePath( $thumbnail = false )
	{
		global $_CB_framework;

		$userId				=	(int) $this->get( 'user_id' );
		$type				=	$this->get( 'type' );
		$value				=	$this->get( 'value' );

		if ( ( ! ( $userId && $type && $value ) ) || ( $thumbnail && ( $type != 'photos' ) ) ) {
			return null;
		}

		static $cache		=	array();

		$id					=	$userId . $type . $value . $thumbnail;

		if ( ! isset( $cache[$id] ) ) {
			if ( $this->getLinkDomain() ) {
				$path		=	$value;
			} else {
				$path		=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/plug_cbgallery/' . (int) $userId . '/' . preg_replace( '/[^-a-zA-Z0-9_]/', '', $type ) . '/' . ( $thumbnail ? 'tn' : null ) . preg_replace( '/[^-a-zA-Z0-9_.]/', '', $value );
			}

			$cache[$id]		=	$path;
		}

		return $cache[$id];
	}

	/**
	 * Checks if the file exists
	 *
	 * @param bool $thumbnail
	 * @return bool
	 */
	public function checkExists( $thumbnail = false )
	{
		static $cache						=	array();

		$id									=	$this->getFilePath( $thumbnail );

		if ( ! isset( $cache[$id] ) ) {
			$exists							=	false;

			if ( $id ) {
				$domain						=	$this->getLinkDomain();

				if ( $domain ) {
					if ( in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
						$exists				=	true;
					} else {
						try {
							$request		=	new GuzzleHttp\Client();

							$header			=	$request->head( $id );

							if ( ( $header !== false ) && ( $header->getStatusCode() == 200 ) ) {
								$exists		=	true;
							}
						} catch( Exception $e ) {}
					}
				} else {
					$exists					=	file_exists( $id );
				}
			}

			$cache[$id]						=	$exists;
		}

		return $cache[$id];
	}

	/**
	 * Returns the file size raw or formatted to largest increment possible
	 *
	 * @param bool $raw
	 * @param bool $thumbnail
	 * @return string|int
	 */
	public function getFileSize( $raw = false, $thumbnail = false )
	{
		if ( Application::Cms()->getClientId() ) {
			$thumbnail						=	false;
		}

		static $cache						=	array();

		$id									=	$this->getFilePath( $thumbnail );

		if ( ! isset( $cache[$id] ) ) {
			$fileSize						=	0;

			if ( $this->checkExists( $thumbnail ) ) {
				$domain						=	$this->getLinkDomain();

				if ( $domain ) {
					if ( ! in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
						try {
							$request		=	new GuzzleHttp\Client();

							$header			=	$request->head( $id );

							if ( ( $header !== false ) && ( $header->getStatusCode() == 200 ) ) {
								$fileSize	=	(int) $header->getHeader( 'Content-Length' );
							}
						} catch( Exception $e ) {}
					}
				} else {
					$fileSize				=	@filesize( $id );
				}
			}

			$cache[$id]						=	$fileSize;
		}

		if ( ! $raw ) {
			return cbgalleryClass::getFormattedFileSize( $cache[$id] );
		}

		return $cache[$id];
	}

	/**
	 * Returns the file name cleaned of the unique id
	 *
	 * @return string
	 */
	public function getFileName()
	{
		static $cache				=	array();

		$id							=	$this->get( 'value' );

		if ( ! isset( $cache[$id] ) ) {
			$domain					=	$this->getLinkDomain();

			if ( $domain ) {
				if ( in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
					$name			=	preg_replace( '%^.*(?:v=|v/|/)([\w-]+).*%i', '$1', $id );
				} else {
					$name			=	pathinfo( $id, PATHINFO_FILENAME ) . '.' . $this->getExtension();
				}

				$cache[$id]			=	$name;
			} else {
				$extension			=	$this->getExtension();

				if ( $this->get( 'file' ) ) {
					$cache[$id]		=	Get::clean( pathinfo( $this->get( 'file' ), PATHINFO_FILENAME ), GetterInterface::STRING ) . '.' . $extension;
				} else {
					$cache[$id]		=	preg_replace( '/[^-a-zA-Z0-9_.]/', '', pathinfo( $id, PATHINFO_FILENAME ) ) . '.' . $extension;
				}
			}
		}

		return $cache[$id];
	}

	/**
	 * Returns the items file extension
	 *
	 * @return string|null
	 */
	public function getExtension()
	{
		static $cache			=	array();

		$id						=	$this->get( 'value' );

		if ( ! isset( $cache[$id] ) ) {
			$domain				=	$this->getLinkDomain();

			if ( $domain ) {
				if ( in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
					$extension	=	null;
				} else {
					$extension	=	strtolower( pathinfo( $id, PATHINFO_EXTENSION ) );
				}

				$cache[$id]		=	$extension;
			} else {
				$cache[$id]		=	strtolower( pathinfo( preg_replace( '/[^-a-zA-Z0-9_.]/', '', $id ), PATHINFO_EXTENSION ) );
			}
		}

		return $cache[$id];
	}

	/**
	 * Returns the files mimetype from extension
	 *
	 * @return string
	 */
	public function getMimeType()
	{

		$domain				=	$this->getLinkDomain();

		if ( $domain && in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
			return 'video/youtube';
		}

		static $cache		=	array();

		$id					=	$this->getExtension();

		if ( ! isset( $cache[$id] ) ) {
			$cache[$id]		=	cbgalleryClass::getMimeTypes( $id );
		}

		return $cache[$id];
	}

	/**
	 * Previews the item
	 *
	 * @param bool $thumbnail
	 * @return bool
	 */
	public function preview( $thumbnail = false )
	{
		if ( Application::Cms()->getClientId() ) {
			$thumbnail	=	false;
		}

		return $this->output( true, $thumbnail );
	}

	/**
	 * Downloads the item
	 *
	 * @return bool
	 */
	public function download()
	{
		return $this->output( false, false );
	}

	/**
	 * Outputs item to header
	 *
	 * @param bool $inline
	 * @param bool $thumbnail
	 * @return bool
	 */
	private function output( $inline = false, $thumbnail = false )
	{
		if ( ! $this->get( 'id' ) ) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		if ( $this->getLinkDomain() || ( ! $this->checkExists( $thumbnail ) ) ) {
			cbRedirect( $this->getFilePath( $thumbnail ) );
		}

		if ( ! $this->checkExists( $thumbnail ) ) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		$fileExtension		=	$this->getExtension();

		if ( ! $fileExtension ) {
			header( 'HTTP/1.0 406 Not Acceptable' );
			exit();
		}

		$fileName			=	$this->getFileName();

		if ( ! $fileName ) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		$fileMime			=	$this->getMimeType();

		if ( $fileMime == 'application/octet-stream' ) {
			header( 'HTTP/1.0 406 Not Acceptable' );
			exit();
		}

		$fileSize			=	$this->getFileSize( true, $thumbnail );
		$fileModifed		=	date( 'r', filemtime( $this->getFilePath( $thumbnail ) ) );

		while ( @ob_end_clean() );

		if ( ini_get( 'zlib.output_compression' ) ) {
			ini_set( 'zlib.output_compression', 'Off' );
		}

		if ( function_exists( 'apache_setenv' ) ) {
			apache_setenv( 'no-gzip', '1' );
		}

		header( "Content-Type: $fileMime" );
		header( 'Content-Disposition: ' . ( $inline ? 'inline' : 'attachment' ) . '; filename="' . $fileName . '"; modification-date="' . $fileModifed . '"; size=' . $fileSize . ';' );
		header( "Content-Transfer-Encoding: binary" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Pragma: public" );
		header( "Accept-Ranges: bytes" );

		$offset				=	0;
		$length				=	$fileSize;

		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			if ( ! preg_match( '/^bytes=\d*-\d*(,\d*-\d*)*$/i', $_SERVER['HTTP_RANGE'] ) ) {
				header( "HTTP/1.1 416 Requested Range Not Satisfiable" );
				header( "Content-Range: bytes */$fileSize" );
				exit();
			}

			$ranges			=	explode( ',', substr( $_SERVER['HTTP_RANGE'], 6 ) );

			foreach ( $ranges as $range ) {
				$parts		=	explode( '-', $range );
				$offset		=	(int) $parts[0];
				$length		=	(int) $parts[1];
			}

			if ( ! $length ) {
				$length		=	( $fileSize - 1 );
			}

			if ( $offset > $length ) {
				header( "HTTP/1.1 416 Requested Range Not Satisfiable" );
				header( "Content-Range: bytes */$fileSize" );
				exit();
			}

			header( "HTTP/1.1 206 Partial Content" );
			header( "Content-Range: bytes $offset-$length/$fileSize" );
			header( "Content-Length: " . ( ( $length - $offset ) + 1 ) );
		} else {
			header( "HTTP/1.0 200 OK" );
			header( "Content-Length: $fileSize" );
		}

		if ( ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		$file				=	fopen( $this->getFilePath( $thumbnail ), 'rb' );

		if ( $file === false ) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		fseek( $file, $offset );

		$buffer				=	( 1024 * 8 );

		while ( ( ! feof( $file ) ) && ( ( $pos = ftell( $file ) ) <= $length ) ) {
			if ( ( $pos + $buffer ) > $length ) {
				$buffer		=	( ( $length - $pos ) + 1 );
			}

			echo fread( $file, $buffer );
			@ob_flush();
			flush();
		}

		fclose( $file );

		exit();
	}
}

class cbgalleryFolderTable extends Table
{
	public $id				=	null;
	public $user_id			=	null;
	public $type			=	null;
	public $title			=	null;
	public $description		=	null;
	public $date			=	null;
	public $published		=	null;
	public $params			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_plugin_gallery_folders';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'id';

	/**
	 * @return bool
	 */
	public function check()
	{
		if ( $this->get( 'user_id' ) == '' ) {
			$this->setError( CBTxt::T( 'Owner not specified!' ) );

			return false;
		} elseif ( $this->get( 'type' ) == '' ) {
			$this->setError( CBTxt::T( 'Type not specified!' ) );

			return false;
		}

		return true;
	}

	/**
	 * @param bool $updateNulls
	 * @return bool
	 */
	public function store( $updateNulls = false )
	{
		global $_CB_framework, $_PLUGINS;

		$new	=	( $this->get( 'id' ) ? false : true );

		if ( ! $new ) {
			$_PLUGINS->trigger( 'gallery_onBeforeUpdateFolder', array( &$this ) );
		} else {
			$_PLUGINS->trigger( 'gallery_onBeforeCreateFolder', array( &$this ) );
		}

		$this->set( 'date', $this->get( 'date', $_CB_framework->getUTCDate() ) );

		if ( ! parent::store( $updateNulls ) ) {
			return false;
		}

		if ( ! $new ) {
			$_PLUGINS->trigger( 'gallery_onAfterUpdateFolder', array( $this ) );
		} else {
			$_PLUGINS->trigger( 'gallery_onAfterCreateFolder', array( $this ) );
		}

		return true;
	}

	/**
	 * @param null|int $id
	 * @return bool
	 */
	public function delete( $id = null )
	{
		global $_CB_database, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeDeleteFolder', array( &$this ) );

		if ( $this->get( 'id' ) ) {
			$query		=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'folder' ) . " = " . (int) $this->get( 'id' );
			$_CB_database->setQuery( $query );
			$items		=	$_CB_database->loadObjectList( null, 'cbgalleryItemTable', array( $_CB_database ) );

			/** @var cbgalleryItemTable[] $items */
			foreach ( $items as $item ) {
				$item->delete();
			}
		}

		if ( ! parent::delete( $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'gallery_onAfterDeleteFolder', array( $this ) );

		return true;
	}

	/**
	 * Returns the number of items in this folder
	 *
	 * @return int
	 */
	public function countItems()
	{
		global $_CB_database;

		static $cache				=	array();

		$id							=	$this->get( 'id' );
		$userId						=	Application::MyUser()->getUserId();

		if ( ! isset( $cache[$id][$userId] ) ) {
			$query					=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $this->get( 'type' ) )
									.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $this->get( 'user_id' )
									.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = " . (int) $id
									.	( ( ( $userId != (int) $this->get( 'user_id' ) ) && ( ! Application::User( $userId )->isGlobalModerator() ) ) ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null );
			$_CB_database->setQuery( $query );

			$cache[$id][$userId]	=	(int) $_CB_database->loadResult();
		}

		return $cache[$id][$userId];
	}
}

class cbgalleryPlugin extends cbPluginHandler
{

	/**
	 * Deletes items when the user is deleted
	 *
	 * @param  UserTable $user
	 * @param  int       $status
	 */
	public function deleteItems( $user, $status )
	{
		global $_CB_database, $_PLUGINS;

		$plugin				=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgallery' );
		$params				=	$_PLUGINS->getPluginParams( $plugin );

		if ( $params->get( 'general_delete', 1 ) ) {
			$query			=	'SELECT *'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_folders' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$folders		=	$_CB_database->loadObjectList( null, 'cbgalleryFolderTable', array( $_CB_database ) );

			/** @var cbgalleryFolderTable[] $folders */
			foreach ( $folders as $folder ) {
				$folder->delete();
			}

			$query			=	'SELECT *'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$items			=	$_CB_database->loadObjectList( null, 'cbgalleryItemTable', array( $_CB_database ) );

			/** @var cbgalleryItemTable[] $items */
			foreach ( $items as $item ) {
				$item->delete();
			}
		}
	}
}

class cbgalleryTab extends cbTabHandler
{
	protected $tabPhotos		=	0;
	protected $tabFiles			=	0;
	protected $tabVideos		=	0;
	protected $tabMusic			=	0;

	/**
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param int       $ui
	 * @return null|string
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		if ( ! ( $tab->params instanceof ParamsInterface ) ) {
			$tab->params	=	new Registry( $tab->params );
		}

		$photosEnabled		=	$tab->params->get( 'tab_photos', $this->tabPhotos );
		$filesEnabled		=	$tab->params->get( 'tab_files', $this->tabFiles );
		$videosEnabled		=	$tab->params->get( 'tab_videos', $this->tabVideos );
		$musicEnabled		=	$tab->params->get( 'tab_music', $this->tabMusic );
		$return				=	null;

		if ( $photosEnabled || $filesEnabled || $videosEnabled || $musicEnabled ) {
			$viewer			=	CBuser::getMyUserDataInstance();

			outputCbJs( 1 );
			outputCbTemplate( 1 );
			cbimport( 'cb.pagination' );

			cbgalleryClass::getTemplate( 'tab' );

			$photos			=	null;

			if ( $photosEnabled ) {
				$photos		=	$this->getGallery( 'photos', $tab, $user, $viewer );
			}

			$files			=	null;

			if ( $filesEnabled ) {
				$files		=	$this->getGallery( 'files', $tab, $user, $viewer );
			}

			$videos			=	null;

			if ( $videosEnabled ) {
				$videos		=	$this->getGallery( 'videos', $tab, $user, $viewer );
			}

			$music			=	null;

			if ( $musicEnabled ) {
				$music		=	$this->getGallery( 'music', $tab, $user, $viewer );
			}

			if ( $photos || $files || $videos || $music ) {
				$class		=	$this->params->get( 'general_class', null );

				$return		=	'<div id="cbGallery" class="cbGallery' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
							.		'<div id="cbGalleryInner" class="cbGalleryInner">'
							.			HTML_cbgalleryTab::showTab( $photos, $files, $videos, $music, $viewer, $user, $tab, $this )
							.		'</div>'
							.	'</div>';
			}
		}

		return $return;
	}

	/**
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 * @param bool|int  $start
	 * @return null|string
	 */
	private function getFolders( $type, $tab, $user, $viewer, $start = false )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		/** @var Registry $params */
		$params							=	$tab->params;
		$tabPrefix						=	'tab_' . (int) $tab->get( 'tabid' ) . '_';
		$publishedOnly					=	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) );
		$input							=	array();

		// Folders:
		$typePrefix						=	$tabPrefix . $type . '_folders_';
		$limit							=	(int) $params->get( 'tab_' . $type . '_folders_limit', 15 );
		$limitstart						=	( $start !== false ? (int) $start : $_CB_framework->getUserStateFromRequest( $typePrefix . 'limitstart{com_comprofiler}', $typePrefix . 'limitstart' ) );
		$search							=	$_CB_framework->getUserStateFromRequest( $typePrefix . 'search{com_comprofiler}', $typePrefix . 'search' );
		$where							=	null;

		if ( $search && $params->get( 'tab_' . $type . '_folders_search', 1 ) ) {
			$where						.=	"\n AND ( " . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
										.	" OR " . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$foldersSearching				=	( $where ? true : false );

		$query							=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_folders' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where;
		$_CB_database->setQuery( $query );
		$total							=	(int) $_CB_database->loadResult();

		if ( $total <= $limitstart ) {
			$limitstart					=	0;
		}

		$foldersPageNav					=	new cbPageNav( $total, $limitstart, $limit );

		$foldersPageNav->setInputNamePrefix( $typePrefix );

		$orderBy						=	$params->get( 'tab_' . $type . '_folders_orderby', 'date_desc' );

		if ( ! $orderBy ) {
			$orderBy					=	'date_desc';
		}

		$orderBy						=	explode( '_', $orderBy );

		$query							=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_folders' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where
										.	"\n ORDER BY " . $_CB_database->NameQuote( $orderBy[0] ) . " " . strtoupper( $orderBy[1] );
		if ( $params->get( 'tab_' . $type . '_folders_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $foldersPageNav->limitstart, $foldersPageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$folders						=	$_CB_database->loadObjectList( null, 'cbgalleryFolderTable', array( $_CB_database ) );
		$foldersCount					=	count( $folders );

		$_PLUGINS->trigger( 'gallery_onLoadFolders', array( &$folders, $user ) );

		if ( $foldersCount && ( ! count( $folders ) ) ) {
			return $this->getFolders( $type, $tab, $user, $viewer, ( $limitstart + $limit ) );
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$placeholder			=	CBTxt::T( 'Search Albums...' );
				break;
			case 'files':
				$placeholder			=	CBTxt::T( 'Search Folders...' );
				break;
			default:
				$placeholder			=	CBTxt::T( 'Search...' );
				break;
		}

		$input['search_folders']		=	'<input type="text" name="' . htmlspecialchars( $typePrefix . 'search' ) . '" value="' . htmlspecialchars( $search ) . '" onchange="document.' . htmlspecialchars( $type ) . 'ItemsForm.submit();" placeholder="' . htmlspecialchars( $placeholder ) . '" class="form-control" />';

		return array( $folders, $foldersPageNav, $foldersSearching, $input );
	}

	/**
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 * @param bool|int  $start
	 * @return null|string
	 */
	private function getItems( $type, $tab, $user, $viewer, $start = false )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		/** @var Registry $params */
		$params							=	$tab->params;
		$tabPrefix						=	'tab_' . (int) $tab->get( 'tabid' ) . '_';
		$publishedOnly					=	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) );
		$input							=	array();

		// Items:
		$typePrefix						=	$tabPrefix . $type . '_items_';
		$limit							=	(int) $params->get( 'tab_' . $type . '_items_limit', 15 );
		$limitstart						=	( $start !== false ? (int) $start : $_CB_framework->getUserStateFromRequest( $typePrefix . 'limitstart{com_comprofiler}', $typePrefix . 'limitstart' ) );
		$search							=	$_CB_framework->getUserStateFromRequest( $typePrefix . 'search{com_comprofiler}', $typePrefix . 'search' );
		$where							=	null;

		if ( $search && $params->get( 'tab_' . $type . '_items_search', 1 ) ) {
			$where						.=	"\n AND ( " . $_CB_database->NameQuote( 'value' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
										.	" OR " . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
										.	" OR " . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$itemsSearching					=	( $where ? true : false );

		$query							=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = 0"
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where;
		$_CB_database->setQuery( $query );
		$total							=	(int) $_CB_database->loadResult();

		if ( $total <= $limitstart ) {
			$limitstart					=	0;
		}

		$itemsPageNav					=	new cbPageNav( $total, $limitstart, $limit );

		$itemsPageNav->setInputNamePrefix( $typePrefix );

		$orderBy						=	$params->get( 'tab_' . $type . '_items_orderby', 'date_desc' );

		if ( ! $orderBy ) {
			$orderBy					=	'date_desc';
		}

		$orderBy						=	explode( '_', $orderBy );

		$query							=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = 0"
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where
										.	"\n ORDER BY " . $_CB_database->NameQuote( $orderBy[0] ) . " " . strtoupper( $orderBy[1] );
		if ( $params->get( 'tab_' . $type . '_items_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $itemsPageNav->limitstart, $itemsPageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$items							=	$_CB_database->loadObjectList( null, 'cbgalleryItemTable', array( $_CB_database ) );
		$itemsCount						=	count( $items );

		$_PLUGINS->trigger( 'gallery_onLoadItems', array( &$items, $user ) );

		if ( $itemsCount && ( ! count( $items ) ) ) {
			return $this->getItems( $type, $tab, $user, $viewer, ( $limitstart + $limit ) );
		}

		switch( $type ) {
			case 'photos':
				$placeholder			=	CBTxt::T( 'Search Photos...' );
				break;
			case 'files':
				$placeholder			=	CBTxt::T( 'Search Files...' );
				break;
			case 'videos':
				$placeholder			=	CBTxt::T( 'Search Videos...' );
				break;
			case 'music':
				$placeholder			=	CBTxt::T( 'Search Music...' );
				break;
			default:
				$placeholder			=	CBTxt::T( 'Search...' );
				break;
		}

		$input['search_items']			=	'<input type="text" name="' . htmlspecialchars( $typePrefix . 'search' ) . '" value="' . htmlspecialchars( $search ) . '" onchange="document.' . htmlspecialchars( $type ) . 'ItemsForm.submit();" placeholder="' . htmlspecialchars( $placeholder ) . '" class="form-control" />';

		return array( $items, $itemsPageNav, $itemsSearching, $input );
	}

	/**
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 * @return null|string
	 */
	private function getGallery( $type, $tab, $user, $viewer )
	{
		list( $folders, $foldersPageNav, $foldersSearching, $foldersInput )	=	$this->getFolders( $type, $tab, $user, $viewer );
		list( $items, $itemsPageNav, $itemsSearching, $itemsInput )			=	$this->getItems( $type, $tab, $user, $viewer );

		$input																=	array_merge( $foldersInput, $itemsInput );

		$showEmpty															=	(int) ( isset( $ueConfig['showEmptyTabs'] ) ? $ueConfig['showEmptyTabs'] : 1 );

		cbgalleryClass::getTemplate( array( 'items', 'folder', 'folders', $type ) );

		if ( ( ! $showEmpty ) && ( ! ( $folders || cbgalleryClass::canUserCreate( $viewer, $type, true ) ) ) && ( ! ( $items || cbgalleryClass::canUserCreate( $viewer, $type, false ) ) ) ) {
			return null;
		}

		return HTML_cbgalleryItems::showItems( $folders, $foldersPageNav, $foldersSearching, $items, $itemsPageNav, $itemsSearching, $type, $input, $viewer, $user, $tab, $this );
	}
}

class cbgalleryTabPhotos extends cbgalleryTab
{

	/**
	 * Constructor to set the default display mode
	 */
	public function __construct()
	{
		parent::__construct();

		$this->tabPhotos	=	1;
	}
}

class cbgalleryTabFiles extends cbgalleryTab
{

	/**
	 * Constructor to set the default display mode
	 */
	public function __construct()
	{
		parent::__construct();

		$this->tabFiles	=	1;
	}
}

class cbgalleryTabVideos extends cbgalleryTab
{

	/**
	 * Constructor to set the default display mode
	 */
	public function __construct()
	{
		parent::__construct();

		$this->tabVideos	=	1;
	}
}

class cbgalleryTabMusic extends cbgalleryTab
{

	/**
	 * Constructor to set the default display mode
	 */
	public function __construct()
	{
		parent::__construct();

		$this->tabMusic	=	1;
	}
}