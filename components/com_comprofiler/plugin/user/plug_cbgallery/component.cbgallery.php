<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\UserTable;
use CB\Database\Table\TabTable;
use CBLib\Application\Application;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBplug_cbgallery extends cbPluginHandler
{

	/**
	 * @param  TabTable   $tab       Current tab
	 * @param  UserTable  $user      Current user
	 * @param  int        $ui        1 front, 2 admin UI
	 * @param  array      $postdata  Raw unfiltred POST data
	 * @return string                HTML
	 */
	public function getCBpluginComponent( $tab, $user, $ui, $postdata )
	{
		global $_CB_framework;

		$format							=	$this->input( 'format', null, GetterInterface::STRING );

		if ( $format != 'raw' ) {
			outputCbJs( 1 );
			outputCbTemplate( 1 );
		}

		$action							=	$this->input( 'action', null, GetterInterface::STRING );
		$function						=	$this->input( 'func', null, GetterInterface::STRING );
		$type							=	$this->input( 'type', null, GetterInterface::STRING );
		$id								=	(int) $this->input( 'id', null, GetterInterface::INT );
		$userId							=	(int) $this->input( 'user', null, GetterInterface::INT );
		$tabId							=	(int) $this->input( 'tab', null, GetterInterface::INT );

		if ( ! $tabId ) {
			switch( $type ) {
				case 'photos':
					$tabId				=	'cbgalleryTabPhotos';
					break;
				case 'files':
					$tabId				=	'cbgalleryTabFiles';
					break;
				case 'videos':
					$tabId				=	'cbgalleryTabVideos';
					break;
				case 'music':
					$tabId				=	'cbgalleryTabMusic';
					break;
			}
		}

		$viewer							=	CBuser::getMyUserDataInstance();

		if ( $userId ) {
			$user						=	CBuser::getUserDataInstance( (int) $userId );
		} else {
			$user						=	CBuser::getMyUserDataInstance();
		}

		$profileUrl						=	$_CB_framework->userProfileUrl( (int) $user->get( 'id' ), false, $tabId );

		if ( ! in_array( $type, array( 'photos', 'files', 'videos', 'music' ) ) ) {
			if ( ( $action == 'items' ) && in_array( $function, array( 'download', 'preview', 'show' ) ) ) {
				header( 'HTTP/1.0 401 Unauthorized' );
				exit();
			} else {
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
			}
		}

		$tab							=	new TabTable();

		$tab->load( ( is_integer( $tabId ) ? $tabId : array( 'pluginclass' => $tabId ) ) );

		if ( ! ( $tab->get( 'enabled' ) && Application::User( (int) $viewer->get( 'id' ) )->canViewAccessLevel( $tab->get( 'viewaccesslevel' ) ) ) ) {
			if ( ( $action == 'items' ) && in_array( $function, array( 'download', 'preview', 'show' ) ) ) {
				header( 'HTTP/1.0 401 Unauthorized' );
				exit();
			} else {
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
			}
		}

		if ( ! ( $tab->params instanceof ParamsInterface ) ) {
			$tab->params				=	new Registry( $tab->params );
		}

		if ( $format != 'raw' ) {
			ob_start();
		}

		switch ( $action ) {
			case 'items':
				switch ( $function ) {
					case 'download':
						$this->outputItem( false, false, $id, $type, $tab, $user, $viewer );
						break;
					case 'edit':
						$this->showItemEdit( $id, $type, $tab, $user, $viewer );
						break;
					case 'new':
						$this->showItemEdit( null, $type, $tab, $user, $viewer );
						break;
					case 'save':
						cbSpoofCheck( 'plugin' );
						$this->saveItemEdit( $id, $type, $tab, $user, $viewer );
						break;
					case 'publish':
						$this->stateItem( 1, $id, $type, $tab, $user, $viewer );
						break;
					case 'unpublish':
						$this->stateItem( 0, $id, $type, $tab, $user, $viewer );
						break;
					case 'delete':
						$this->deleteItem( $id, $type, $tab, $user, $viewer );
						break;
					case 'preview':
						$this->outputItem( true, true, $id, $type, $tab, $user, $viewer );
						break;
					case 'show':
					default:
						$this->outputItem( true, false, $id, $type, $tab, $user, $viewer );
						break;
				}
				break;
			case 'folders':
				if ( ! $tab->params->get( 'tab_' . $type . '_folders', 1 ) ) {
					cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
				}

				switch ( $function ) {
					case 'edit':
						$this->showFolderEdit( $id, $type, $tab, $user, $viewer );
						break;
					case 'new':
						$this->showFolderEdit( null, $type, $tab, $user, $viewer );
						break;
					case 'save':
						cbSpoofCheck( 'plugin' );
						$this->saveFolderEdit( $id, $type, $tab, $user, $viewer );
						break;
					case 'publish':
						$this->stateFolder( 1, $id, $type, $tab, $user, $viewer );
						break;
					case 'unpublish':
						$this->stateFolder( 0, $id, $type, $tab, $user, $viewer );
						break;
					case 'delete':
						$this->deleteFolder( $id, $type, $tab, $user, $viewer );
						break;
					case 'show':
					default:
						$this->showFolder( $id, $type, $tab, $user, $viewer );
						break;
				}
				break;
			default:
				cbRedirect( 'index.php', CBTxt::T( 'Not authorized.' ), 'error' );
				break;
		}

		if ( $format != 'raw' ) {
			$html						=	ob_get_contents();
			ob_end_clean();

			$class						=	$this->params->get( 'general_class', null );

			$return						=	'<div id="cbGallery" class="cbGallery' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
										.		'<div id="cbGalleryInner" class="cbGalleryInner">'
										.			$html
										.		'</div>'
										.	'</div>';

			echo $return;
		}
	}

	/**
	 * Outputs the header for an item
	 *
	 * @param bool      $inline
	 * @param bool      $thumbnail
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function outputItem( $inline, $thumbnail, $id, $type, $tab, $user, $viewer )
	{
		global $_PLUGINS;

		$row	=	new cbgalleryItemTable();

		$row->load( (int) $id );

		$items	=	array( $row );

		$_PLUGINS->trigger( 'gallery_onLoadItems', array( &$items, $user ) );

		if ( ( empty( $items ) ) || ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( ! $row->get( 'published' ) ) && ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) || ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) ) ) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		if ( $inline ) {
			$row->preview( $thumbnail );
		} else {
			$row->download();
		}
	}

	/**
	 * Displays item create/edit page
	 *
	 * @param int         $id
	 * @param string      $type
	 * @param TabTable    $tab
	 * @param UserTable   $user
	 * @param UserTable   $viewer
	 * @param null|string $message
	 * @param null|string $messageType
	 */
	public function showItemEdit( $id, $type, $tab, $user, $viewer, $message = null, $messageType = 'error' )
	{
		global $_CB_framework, $_CB_database;

		/** @var Registry $params */
		$params								=	$tab->params;

		$row								=	new cbgalleryItemTable();

		$row->load( (int) $id );

		if ( ! $row->get( 'id' ) ) {
			$row->set( 'folder', $this->input( 'folder', 0, GetterInterface::INT ) );
		}

		$cbModerator						=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canAccess							=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( ( $user->get( 'id' ) != $viewer->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$user						=	$viewer;
			}

			$canAccess						=	cbgalleryClass::canUserCreate( $viewer, $type, false );
		} elseif ( ( $row->get( 'type' ) == $type ) && ( $cbModerator || ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) ) ) {
			$canAccess						=	true;
		}

		if ( $row->get( 'folder' ) ) {
			$returnUrl						=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl						=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ! $canAccess ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		$minFileSize						=	$this->params->get( $type . '_item_min_size', 0 );
		$maxFileSize						=	$this->params->get( $type . '_item_max_size', 1024 );

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

		$extLimit							=	cbgalleryClass::getExtensions( $type );

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

		cbgalleryClass::getTemplate( 'item_edit' );

		$input								=	array();

		$publishedTooltip					=	cbTooltip( null, CBTxt::T( 'ITEM_PUBLISHED_DESCRIPTION', 'Select publish status of the [type]. If unpublished the [type] will not be visible to the public.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['published']					=	moscomprofilerHTML::yesnoSelectList( 'published', 'class="form-control"' . ( $publishedTooltip ? ' ' . $publishedTooltip : null ), (int) $this->input( 'post/published', $row->get( 'published', 1 ), GetterInterface::INT ) );

		$titleTooltip						=	cbTooltip( null, CBTxt::T( 'ITEM_TITLE_DESCRIPTION', 'Optionally input a title. If no title is provided the filename will be displayed as the title.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['title']						=	'<input type="title" id="title" name="title" value="' . htmlspecialchars( $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) ) . '" class="form-control" size="25"' . ( $titleTooltip ? ' ' . $titleTooltip : null ) . ' />';

		$listFolders						=	array();

		if ( cbgalleryClass::canUserCreate( $viewer, $type, true ) ) {
			$listFolders[]					=	moscomprofilerHTML::makeOption( -1, CBTxt::T( 'ITEM_NEW_FOLDER', 'New [type]', array( '[type]' => $folderType ) ) );
		}

		if ( $params->get( 'tab_' . $type . '_uncategorized', 1 ) ) {
			$listFolders[]					=	moscomprofilerHTML::makeOption( 0, CBTxt::T( 'Uncategorized' ) );
		}

		$query								=	'SELECT *'
											.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_folders' )
											.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
											.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $row->get( 'user_id', $user->get( 'id' ) )
											.	"\n ORDER BY " . $_CB_database->NameQuote( 'date' ) . " DESC";
		$_CB_database->setQuery( $query );
		$folders							=	$_CB_database->loadObjectList( null, 'cbgalleryFolderTable', array( $_CB_database ) );

		/** @var cbgalleryFolderTable[] $folders */
		foreach ( $folders as $folder ) {
			$listFolders[]					=	moscomprofilerHTML::makeOption( (int) $folder->get( 'id' ), ( $folder->get( 'title' ) ? $folder->get( 'title' ) : cbFormatDate( $folder->get( 'date' ), true, false ) ) );
		}

		$folderTooltip						=	cbTooltip( null, CBTxt::T( 'ITEM_FOLDER_DESCRIPTION', 'Select the [folder_type] for this [type].', array( '[folder_type]' => $folderType, '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['folder']					=	moscomprofilerHTML::selectList( $listFolders, 'folder', 'class="form-control"' . ( $folderTooltip ? ' ' . $folderTooltip : null ), 'value', 'text', $this->input( 'post/folder', $row->get( 'folder', 0 ), GetterInterface::INT ), 1, false, false );

		$allowUpload						=	$this->params->get( $type . '_item_upload', 1 );
		$allowLink							=	$this->params->get( $type . '_item_link', 0 );

		if ( $allowUpload && $allowLink ) {
			$uploadButton					=	CBTxt::T( 'UPLOAD_ITEM_TYPE', 'Upload [type]', array( '[type]' => $typeTranslated ) );
			$linkButton						=	CBTxt::T( 'LINK_ITEM_TYPE', 'Link [type]', array( '[type]' => $typeTranslated ) );

			$js								=	"$( '#method' ).on( 'change', function() {"
											.		"var value = $( this ).val();"
											.		"if ( value == 1 ) {"
											.			"$( '#itemUpload' ).removeClass( 'hidden' ).find( 'input' ).removeClass( 'cbValidationDisabled' );"
											.			"$( '#itemLink' ).addClass( 'hidden' ).find( 'input' ).addClass( 'cbValidationDisabled' );";

			if ( ! $row->get( 'id' ) ) {
				$js							.=			"$( '.galleryButtonSubmit' ).val( '" . addslashes( $uploadButton ) . "' );";
			}

			$js								.=		"} else if ( value == 2 ) {"
											.			"$( '#itemUpload' ).addClass( 'hidden' ).find( 'input' ).addClass( 'cbValidationDisabled' ).val( '' );"
											.			"$( '#itemLink' ).removeClass( 'hidden' ).find( 'input' ).removeClass( 'cbValidationDisabled' );";

			if ( ! $row->get( 'id' ) ) {
				$js							.=			"$( '.galleryButtonSubmit' ).val( '" . addslashes( $linkButton ) . "' );";
			}

			$js								.=		"} else {"
											.			"$( '#itemUpload' ).addClass( 'hidden' ).find( 'input' ).addClass( 'cbValidationDisabled' ).val( '' );"
											.			"$( '#itemLink' ).addClass( 'hidden' ).find( 'input' ).addClass( 'cbValidationDisabled' );"
											.		"}"
											.	"}).change();";

			$_CB_framework->outputCbJQuery( $js );

			$listMethods					=	array();

			if ( $row->get( 'id' ) ) {
				$listMethods[]				=	moscomprofilerHTML::makeOption( 0, CBTxt::T( 'No Change' ) );
			}

			if ( $allowUpload ) {
				$listMethods[]				=	moscomprofilerHTML::makeOption( 1, CBTxt::T( 'Upload' ) );
			}

			if ( $allowLink ) {
				$listMethods[]				=	moscomprofilerHTML::makeOption( 2, CBTxt::T( 'Link' ) );
			}

			$input['method']				=	moscomprofilerHTML::selectList( $listMethods, 'method', 'class="form-control"', 'value', 'text', $this->input( 'post/method', 0, GetterInterface::INT ), 1, false, false );
		} else {
			$input['method']				=	null;
		}

		$fileValidation						=	array();

		if ( $minFileSize || $maxFileSize ) {
			$fileValidation[]				=	cbValidator::getRuleHtmlAttributes( 'filesize', array( $minFileSize, $maxFileSize, 'KB' ) );
		}

		if ( $extLimit ) {
			$fileValidation[]				=	cbValidator::getRuleHtmlAttributes( 'extension', implode( ',', $extLimit ) );
		}

		if ( $allowUpload ) {
			$fileTooltip					=	cbTooltip( null, CBTxt::T( 'ITEM_UPLOAD_DESCRIPTION', 'Select the file to upload.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['upload']				=	'<input type="file" id="file" name="file" value="" class="form-control' . ( ! $row->get( 'id' ) ? ' required' : null ) . '"' . ( $fileTooltip ? ' ' . $fileTooltip : null ) . ( $fileValidation ? implode( ' ', $fileValidation ) : null ) . ' />';

			$input['upload_limits']			=	array();

			if ( $extLimit ) {
				$input['upload_limits'][]	=	CBTxt::T( 'ITEM_UPLOAD_LIMITS_EXT', 'Your file must be of [ext] type.', array( '[ext]' => implode( ', ', $extLimit ) ) );
			}

			if ( $minFileSize ) {
				$input['upload_limits'][]	=	CBTxt::T( 'ITEM_UPLOAD_LIMITS_MIN', 'Your file should exceed [size].', array( '[size]' => cbgalleryClass::getFormattedFileSize( $minFileSize * 1024 ) ) );
			}

			if ( $maxFileSize ) {
				$input['upload_limits'][]	=	CBTxt::T( 'ITEM_UPLOAD_LIMITS_MAX', 'Your file should not exceed [size].', array( '[size]' => cbgalleryClass::getFormattedFileSize( $maxFileSize * 1024 ) ) );
			}
		} else {
			$input['upload']				=	null;
			$input['upload_limits']			=	null;
		}

		if ( $allowLink ) {
			$linkTooltip					=	cbTooltip( null, CBTxt::T( 'ITEM_LINK_DESCRIPTION', 'Input the URL to the file to link.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['link']					=	'<input type="text" id="value" name="value" value="' . htmlspecialchars( $this->input( 'post/value', ( $row->getLinkDomain() ? $row->get( 'value' ) : null ), GetterInterface::STRING ) ) . '" size="40" class="form-control' . ( ! $row->get( 'id' ) ? ' required' : null ) . '"' . ( $linkTooltip ? ' ' . $linkTooltip : null ) . ' />';

			$input['link_limits']			=	array();

			if ( $extLimit ) {
				if ( $type == 'videos' ) {
					$extLimit[]				=	'youtube';
				}

				$input['link_limits'][]		=	CBTxt::T( 'ITEM_LINK_LIMITS_EXT', 'Your file must be of [ext] type.', array( '[ext]' => implode( ', ', $extLimit ) ) );
			}
		} else {
			$input['link']					=	null;
			$input['link_limits']			=	null;
		}

		$descriptionTooltip					=	cbTooltip( null, CBTxt::T( 'ITEM_DESCRIPTION_DESCRIPTION', 'Optionally input a description.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['description']				=	'<textarea id="description" name="description" class="form-control" cols="40" rows="5"' . ( $descriptionTooltip ? ' ' . $descriptionTooltip : null ) . '>' . htmlspecialchars( $this->input( 'post/description', $row->get( 'description' ), GetterInterface::STRING ) ) . '</textarea>';

		$ownerTooltip						=	cbTooltip( null, CBTxt::T( 'ITEM_OWNER_DESCRIPTION', 'Input owner as single integer user_id.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['user_id']					=	'<input type="text" id="user_id" name="user_id" value="' . (int) $this->input( 'post/user_id', $row->get( 'user_id', $user->get( 'id' ) ), GetterInterface::INT ) . '" class="digits required form-control" size="6"' . ( $ownerTooltip ? ' ' . $ownerTooltip : null ) . ' />';

		if ( $message ) {
			$_CB_framework->enqueueMessage( $message, $messageType );
		}

		HTML_cbgalleryItemEdit::showItemEdit( $row, $input, $type, $tab, $user, $viewer, $this );
	}

	/**
	 * Saves an item
	 *
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function saveItemEdit( $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		/** @var Registry $params */
		$params						=	$tab->params;

		$folderId					=	$this->input( 'post/folder', 0, GetterInterface::INT );

		$row						=	new cbgalleryItemTable();

		$row->load( (int) $id );

		if ( ! $row->get( 'id' ) ) {
			$row->set( 'folder', $folderId );
		}

		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canAccess					=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( ( $user->get( 'id' ) != $viewer->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$user				=	$viewer;
			}

			$canAccess				=	cbgalleryClass::canUserCreate( $viewer, $type, false );
		} elseif ( ( $row->get( 'type' ) == $type ) && ( $cbModerator || ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) ) ) {
			$canAccess				=	true;
		}

		if ( $row->get( 'folder' ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		$uploading					=	( ( ! isset( $_FILES['file']['tmp_name'] ) ) || empty( $_FILES['file']['tmp_name'] ) ? false : true );
		$linking					=	$this->input( 'post/value', null, GetterInterface::STRING );

		if ( ( ! $canAccess ) || ( $uploading && ( ! $this->params->get( $type . '_item_upload', 1 ) ) ) || ( $linking && ( ! $this->params->get( $type . '_item_link', 0 ) ) ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
				$typeTranslated		=	CBTxt::T( 'Photo' );
				break;
			case 'files':
				$typeTranslated		=	CBTxt::T( 'File' );
				break;
			case 'videos':
				$typeTranslated		=	CBTxt::T( 'Video' );
				break;
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Music' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Item' );
				break;
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$folderType			=	CBTxt::T( 'Album' );
				break;
			default:
				$folderType			=	CBTxt::T( 'Folder' );
				break;
		}

		$allowFolders				=	$params->get( 'tab_' . $type . '_folders', 1 );

		if ( $allowFolders ) {
			if ( $folderId > 0 ) {
				$query				=	'SELECT ' . $_CB_database->NameQuote( 'id' )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_folders' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
									.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $row->get( 'user_id', $user->get( 'id' ) );
				$_CB_database->setQuery( $query );
				$folderIds			=	$_CB_database->loadResultArray();

				if ( in_array( $folderId, $folderIds ) ) {
					$row->set( 'folder', $folderId );
				} else {
					$row->set( 'folder', 0 );
				}
			} else {
				$row->set( 'folder', 0 );
			}
		} else {
			$row->set( 'folder', $row->get( 'folder', 0 ) );
		}

		if ( $cbModerator || ( ! $this->params->get( $type . '_item_approval', 0 ) ) || ( $row->get( 'id' ) && ( $row->get( 'published' ) != -1 ) && ( ! ( $linking && ( $linking != $row->get( 'value' ) ) ) ) && ( ! $uploading ) ) ) {
			$row->set( 'published', $this->input( 'post/published', $row->get( 'published', 1 ), GetterInterface::INT ) );
		} else {
			$row->set( 'published', ( $this->params->get( $type . '_item_approval', 0 ) ? -1 : $row->get( 'published', 1 ) ) );
		}

		$row->set( 'type', $type );
		$row->set( 'title', $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) );
		$row->set( 'description', $this->input( 'post/description', $row->get( 'description' ), GetterInterface::STRING ) );

		if ( $linking ) {
			$row->set( 'value', $this->input( 'post/value', $row->get( 'value' ), GetterInterface::STRING ) );
		}

		if ( $cbModerator ) {
			$row->set( 'user_id', $this->input( 'post/user_id', $row->get( 'user_id', $viewer->get( 'id' ) ), GetterInterface::INT ) );
		} else {
			$row->set( 'user_id', $row->get( 'user_id', $viewer->get( 'id' ) ) );
		}

		if ( ( $row->get( 'folder' ) === 0 ) && ( ! $params->get( 'tab_' . $type . '_uncategorized', 1 ) ) ) {
			$row->setError( CBTxt::T( 'FOLDER_NOT_SPECIFIED', '[type] not specified!', array( '[type]' => $folderType ) ) );
		}

		if ( $this->params->get( $type . '_item_captcha', 0 ) && ( ! $cbModerator ) ) {
			$_PLUGINS->loadPluginGroup( 'user' );

			$_PLUGINS->trigger( 'onCheckCaptchaHtmlElements', array() );

			if ( $_PLUGINS->is_errors() ) {
				$row->setError( $_PLUGINS->getErrorMSG() );
			}
		}

		$new						=	( $row->get( 'id' ) ? false : true );

		if ( $row->getError() || ( ! $row->check() ) ) {
			$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'ITEM_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ) );
			return;
		}

		if ( $allowFolders && ( $folderId === -1 ) ) {
			$folder					=	new cbgalleryFolderTable();

			$folder->set( 'user_id', $row->get( 'user_id' ) );
			$folder->set( 'type', $type );
			$folder->set( 'date', $_CB_framework->getUTCDate() );
			$folder->set( 'published', ( $this->params->get( $type . '_folder_approval', 0 ) ? -1 : 1 ) );

			if ( ! cbgalleryClass::canUserCreate( $viewer, $type, true ) ) {
				$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'FOLDER_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $folderType, '[error]' => CBTxt::T( 'Not authorized.' ) ) ) );
				return;
			}

			if ( $folder->getError() || ( ! $folder->check() ) ) {
				$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'FOLDER_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $folderType, '[error]' => $folder->getError() ) ) );
				return;
			}

			if ( $folder->getError() || ( ! $folder->store() ) ) {
				$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'FOLDER_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $folderType, '[error]' => $folder->getError() ) ) );
				return;
			}

			$row->set( 'folder', (int) $folder->get( 'id' ) );

			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		}

		if ( $row->getError() || ( ! $row->store() ) ) {
			$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'ITEM_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ) );
			return;
		}

		if ( $row->get( 'published' ) == -1 ) {
			if ( ( $new || ( ( ! $new ) && ( ( $linking && ( $linking != $row->get( 'value' ) ) ) || $uploading ) ) ) && ( ! $cbModerator ) && $this->params->get( $type . '_item_approval_notify', 1 ) ) {
				$cbUser				=	CBuser::getInstance( (int) $row->get( 'user_id' ), false );

				if ( $row->getLinkDomain() ) {
					$itemUrl		=	htmlspecialchars( $row->getFilePath() );
				} else {
					$itemUrl		=	$_CB_framework->pluginClassUrl( $this->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $row->get( 'user_id' ), 'tab' => (int) $tab->get( 'tabid' ) ), 'raw', 0, true );
				}

				$extraStrings		=	array(	'item_id' => (int) $row->get( 'id' ),
												'item_value' => $row->get( 'value' ),
												'item_title' => ( $row->get( 'title' ) ? $row->get( 'title' ) : $row->get( 'value' ) ),
												'item_description' => $row->get( 'description' ),
												'item_date' => $row->get( 'date' ),
												'item_folder' => $row->get( 'folder' ),
												'item_url' => $itemUrl,
												'item_type' => $typeTranslated,
												'item_tab_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ),
												'gallery_photos_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabPhotos' ) ),
												'gallery_videos_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabVideos' ) ),
												'gallery_music_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabMusic' ) ),
												'gallery_files_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabFiles' ) ),
												'user_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ) ) )
											);

				$subject			=	$cbUser->replaceUserVars( CBTxt::T( 'NOTIFY_NEW_ITEM_CREATED_SUBJECT', 'Gallery - New [type] Created!', array( '[type]' => $typeTranslated ) ), false, true, $extraStrings, false );
				$message			=	$cbUser->replaceUserVars( CBTxt::T( 'NOTIFY_NEW_ITEM_CREATED_BODY', '<a href="[user_url]">[formatname]</a> created [item_type] <a href="[item_url]">[item_title]</a> and requires <a href="[item_tab_url]">approval</a>!' ), false, true, $extraStrings, false );

				$notifications		=	new cbNotification();

				$notifications->sendToModerators( $subject, $message, false, 1 );
			}

			cbRedirect( $returnUrl, CBTxt::T( 'ITEM_SAVED_SUCCESSFULLY_AND_AWAITING_APPROVAL', '[type] saved successfully and awaiting approval!', array( '[type]' => $typeTranslated ) ) );
		} else {
			cbRedirect( $returnUrl, CBTxt::T( 'ITEM_SAVED_SUCCESSFULLY', '[type] saved successfully!', array( '[type]' => $typeTranslated ) ) );
		}
	}

	/**
	 * Sets the published state of an item
	 *
	 * @param int       $state
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function stateItem( $state, $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework;

		$row						=	new cbgalleryItemTable();

		$row->load( (int) $id );

		if ( $row->get( 'folder' ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) || ( ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) && ( $row->get( 'published' ) == -1 ) && $this->params->get( $type . '_item_approval', 0 ) ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
				$typeTranslated		=	CBTxt::T( 'Photo' );
				break;
			case 'files':
				$typeTranslated		=	CBTxt::T( 'File' );
				break;
			case 'videos':
				$typeTranslated		=	CBTxt::T( 'Video' );
				break;
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Music' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Item' );
				break;
		}

		$row->set( 'published', (int) $state );

		if ( $row->getError() || ( ! $row->store() ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'ITEM_STATE_FAILED_TO_SAVE', '[type] state failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		cbRedirect( $returnUrl, CBTxt::T( 'ITEM_STATE_SAVED_SUCCESSFULLY', '[type] state saved successfully!', array( '[type]' => $typeTranslated ) ) );
	}

	/**
	 * Deletes an item
	 *
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function deleteItem( $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework;

		$row						=	new cbgalleryItemTable();

		$row->load( (int) $id );

		if ( $row->get( 'folder' ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
				$typeTranslated		=	CBTxt::T( 'Photo' );
				break;
			case 'files':
				$typeTranslated		=	CBTxt::T( 'File' );
				break;
			case 'videos':
				$typeTranslated		=	CBTxt::T( 'Video' );
				break;
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Music' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Item' );
				break;
		}

		if ( ! $row->canDelete() ) {
			cbRedirect( $returnUrl, CBTxt::T( 'ITEM_FAILED_TO_DELETE', '[type] failed to delete! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		if ( ! $row->delete() ) {
			cbRedirect( $returnUrl, CBTxt::T( 'ITEM_FAILED_TO_DELETE', '[type] failed to delete! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		cbRedirect( $returnUrl, CBTxt::T( 'ITEM_DELETED_SUCCESSFULLY', '[type] deleted successfully!', array( '[type]' => $typeTranslated ) ) );
	}

	/**
	 * Displays a folders items
	 *
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 * @param bool|int  $start
	 */
	private function showFolder( $id, $type, $tab, $user, $viewer, $start = false )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		/** @var Registry $params */
		$params							=	$tab->params;

		$row							=	new cbgalleryFolderTable();

		$profileUrl						=	$_CB_framework->userProfileUrl( (int) $user->get( 'id' ), false, $tab->get( 'tabid' ) );

		if ( $id !== 0 ) {
			$row->load( (int) $id );

			$folders					=	array( $row );

			$_PLUGINS->trigger( 'gallery_onLoadFolders', array( &$folders, $user ) );

			$profileUrl					=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );

			if ( ( empty( $folders ) ) || ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( ! $row->get( 'published' ) ) && ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) || ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
			}
		} else {
			$allowFolders				=	$params->get( 'tab_' . $type . '_folders', 1 );

			$query						=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = 0";
			$_CB_database->setQuery( $query );
			$uncategorized				=	(int) $_CB_database->loadResult();

			if ( ( ( ! $allowFolders ) || ( $allowFolders && $params->get( 'tab_' . $type . '_uncategorized', 1 ) ) ) || ( ! $uncategorized ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
			}

			$row->set( 'id', 0 );
			$row->set( 'user_id', (int) $user->get( 'id' ) );
			$row->set( 'title', CBTxt::T( 'Uncategorized' ) );
			$row->set( 'date', $_CB_framework->getUTCDate() );
		}

		$tabPrefix						=	'tab_' . (int) $tab->get( 'tabid' ) . '_';
		$publishedOnly					=	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) );

		$typePrefix						=	$tabPrefix . $type . '_folder_items_';
		$limit							=	(int) $params->get( 'tab_' . $type . '_folder_items_limit', 30 );
		$limitstart						=	( $start !== false ? (int) $start : $_CB_framework->getUserStateFromRequest( $typePrefix . 'limitstart{com_comprofiler}', $typePrefix . 'limitstart' ) );
		$search							=	$_CB_framework->getUserStateFromRequest( $typePrefix . 'search{com_comprofiler}', $typePrefix . 'search' );
		$where							=	null;

		if ( $search && $params->get( 'tab_' . $type . '_folder_items_search', 1 ) ) {
			$where						.=	"\n AND ( " . $_CB_database->NameQuote( 'value' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
										.	" OR " . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
										.	" OR " . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$itemsSearching					=	( $where ? true : false );

		$query							=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $row->get( 'user_id', $user->get( 'id' ) )
										.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = " . (int) $row->get( 'id' )
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where;
		$_CB_database->setQuery( $query );
		$total							=	(int) $_CB_database->loadResult();

		if ( $total <= $limitstart ) {
			$limitstart					=	0;
		}

		$itemsPageNav					=	new cbPageNav( $total, $limitstart, $limit );

		$itemsPageNav->setInputNamePrefix( $typePrefix );

		$orderBy						=	$params->get( 'tab_' . $type . '_folder_items_orderby', 'date_desc' );

		if ( ! $orderBy ) {
			$orderBy					=	'date_desc';
		}

		$orderBy						=	explode( '_', $orderBy );

		$query							=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_gallery_items' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $type )
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $row->get( 'user_id', $user->get( 'id' ) )
										.	"\n AND " . $_CB_database->NameQuote( 'folder' ) . " = " . (int) $row->get( 'id' )
										.	( $publishedOnly ? "\n AND " . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
										.	$where
										.	"\n ORDER BY " . $_CB_database->NameQuote( $orderBy[0] ) . " " . strtoupper( $orderBy[1] );
		if ( $params->get( 'tab_' . $type . '_folder_items_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $itemsPageNav->limitstart, $itemsPageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$items							=	$_CB_database->loadObjectList( null, 'cbgalleryItemTable', array( $_CB_database ) );
		$itemsCount						=	count( $items );

		$_PLUGINS->trigger( 'gallery_onLoadItems', array( &$items, $user ) );

		if ( $itemsCount && ( ! count( $items ) ) ) {
			$this->showFolder( $id, $type, $tab, $user, $viewer, ( $limitstart + $limit ) );
			return;
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

		$input							=	array();
		$input['search_items']			=	'<input type="text" name="' . htmlspecialchars( $typePrefix . 'search' ) . '" value="' . htmlspecialchars( $search ) . '" onchange="document.' . htmlspecialchars( $type ) . 'ItemsForm.submit();" placeholder="' . htmlspecialchars( $placeholder ) . '" class="form-control" />';

		cbgalleryClass::getTemplate( array( 'items', 'folder', 'folders', $type ) );

		echo HTML_cbgalleryItems::showItems( $row, null, false, $items, $itemsPageNav, $itemsSearching, $type, $input, $viewer, $user, $tab, $this );
	}

	/**
	 * Displays folder create/edit page
	 *
	 * @param int         $id
	 * @param string      $type
	 * @param TabTable    $tab
	 * @param UserTable   $user
	 * @param UserTable   $viewer
	 * @param null|string $message
	 * @param null|string $messageType
	 */
	public function showFolderEdit( $id, $type, $tab, $user, $viewer, $message = null, $messageType = 'error' )
	{
		global $_CB_framework;

		$row						=	new cbgalleryFolderTable();

		$row->load( (int) $id );

		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canAccess					=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( ( $user->get( 'id' ) != $viewer->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$user				=	$viewer;
			}

			$canAccess				=	cbgalleryClass::canUserCreate( $viewer, $type, true );
		} elseif ( ( $row->get( 'type' ) == $type ) && ( $cbModerator || ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) ) ) {
			$canAccess				=	true;
		}

		if ( $this->input( 'folder', false, GetterInterface::BOOLEAN ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ! $canAccess ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Album' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Folder' );
				break;
		}

		cbgalleryClass::getTemplate( 'folder_edit' );

		$input						=	array();

		$publishedTooltip			=	cbTooltip( null, CBTxt::T( 'FOLDER_PUBLISHED_DESCRIPTION', 'Select publish status of the [type]. If unpublished the [type] will not be visible to the public.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['published']			=	moscomprofilerHTML::yesnoSelectList( 'published', 'class="form-control"' . ( $publishedTooltip ? ' ' . $publishedTooltip : null ), (int) $this->input( 'post/published', $row->get( 'published', 1 ), GetterInterface::INT ) );

		$titleTooltip				=	cbTooltip( null, CBTxt::T( 'FOLDER_TITLE_DESCRIPTION', 'Optionally input a title. If no title is provided the date will be displayed as the title.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['title']				=	'<input type="title" id="title" name="title" value="' . htmlspecialchars( $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) ) . '" class="form-control" size="25"' . ( $titleTooltip ? ' ' . $titleTooltip : null ) . ' />';

		$descriptionTooltip			=	cbTooltip( null, CBTxt::T( 'FOLDER_DESCRIPTION_DESCRIPTION', 'Optionally input a description.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['description']		=	'<textarea id="description" name="description" class="form-control" cols="40" rows="5"' . ( $descriptionTooltip ? ' ' . $descriptionTooltip : null ) . '>' . htmlspecialchars( $this->input( 'post/description', $row->get( 'description' ), GetterInterface::STRING ) ) . '</textarea>';

		$ownerTooltip				=	cbTooltip( null, CBTxt::T( 'FOLDER_OWNER_DESCRIPTION', 'Input owner as single integer user_id.', array( '[type]' => $typeTranslated ) ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['user_id']			=	'<input type="text" id="user_id" name="user_id" value="' . (int) $this->input( 'post/user_id', $row->get( 'user_id', $user->get( 'id' ) ), GetterInterface::INT ) . '" class="digits required form-control" size="6"' . ( $ownerTooltip ? ' ' . $ownerTooltip : null ) . ' />';

		if ( $message ) {
			$_CB_framework->enqueueMessage( $message, $messageType );
		}

		HTML_cbgalleryFolderEdit::showFolderEdit( $row, $input, $type, $tab, $user, $viewer, $this );
	}

	/**
	 * Saves a folder
	 *
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function saveFolderEdit( $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework, $_PLUGINS;

		$row						=	new cbgalleryFolderTable();

		$row->load( (int) $id );

		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canAccess					=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( ( $user->get( 'id' ) != $viewer->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$user				=	$viewer;
			}

			$canAccess				=	cbgalleryClass::canUserCreate( $viewer, $type, true );
		} elseif ( ( $row->get( 'type' ) == $type ) && ( $cbModerator || ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) ) ) {
			$canAccess				=	true;
		}

		if ( $this->input( 'folder', false, GetterInterface::BOOLEAN ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ! $canAccess ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Album' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Folder' );
				break;
		}

		if ( $cbModerator || ( ! $this->params->get( $type . '_folder_approval', 0 ) ) || ( $row->get( 'id' ) && ( $row->get( 'published' ) != -1 ) ) ) {
			$row->set( 'published', $this->input( 'post/published', $row->get( 'published', 1 ), GetterInterface::INT ) );
		} else {
			$row->set( 'published', ( $this->params->get( $type . '_folder_approval', 0 ) ? -1 : $row->get( 'published', 1 ) ) );
		}

		$row->set( 'type', $type );
		$row->set( 'title', $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) );
		$row->set( 'description', $this->input( 'post/description', $row->get( 'description' ), GetterInterface::STRING ) );

		if ( $cbModerator ) {
			$row->set( 'user_id', $this->input( 'post/user_id', $row->get( 'user_id', $viewer->get( 'id' ) ), GetterInterface::INT ) );
		} else {
			$row->set( 'user_id', $row->get( 'user_id', $viewer->get( 'id' ) ) );
		}

		if ( $this->params->get( $type . '_folder_captcha', 0 ) && ( ! $cbModerator ) ) {
			$_PLUGINS->loadPluginGroup( 'user' );

			$_PLUGINS->trigger( 'onCheckCaptchaHtmlElements', array() );

			if ( $_PLUGINS->is_errors() ) {
				$row->setError( $_PLUGINS->getErrorMSG() );
			}
		}

		$new						=	( $row->get( 'id' ) ? false : true );

		if ( $row->getError() || ( ! $row->check() ) ) {
			$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'FOLDER_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ) );
			return;
		}

		if ( $row->getError() || ( ! $row->store() ) ) {
			$this->showItemEdit( $id, $type, $tab, $user, $viewer, CBTxt::T( 'FOLDER_FAILED_TO_SAVE', '[type] failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ) );
			return;
		}

		if ( $row->get( 'published' ) == -1 ) {
			if ( $new && ( ! $cbModerator ) && $this->params->get( $type . '_folder_approval_notify', 1 ) ) {
				$cbUser				=	CBuser::getInstance( (int) $row->get( 'user_id' ), false );

				$extraStrings		=	array(	'folder_id' => (int) $row->get( 'id' ),
												'folder_title' => ( $row->get( 'title' ) ? $row->get( 'title' ) : $row->get( 'date' ) ),
												'folder_description' => $row->get( 'description' ),
												'folder_date' => $row->get( 'date' ),
												'folder_url' => $_CB_framework->pluginClassUrl( $this->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $row->get( 'user_id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ),
												'folder_type' => $typeTranslated,
												'folder_tab_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ),
												'gallery_photos_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabPhotos' ) ),
												'gallery_videos_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabVideos' ) ),
												'gallery_music_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabMusic' ) ),
												'gallery_files_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ), 'tab' => 'cbgalleryTabFiles' ) ),
												'user_url' => $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user_id' ) ) )
											);

				$subject			=	$cbUser->replaceUserVars( CBTxt::T( 'NOTIFY_NEW_FOLDER_CREATED_SUBJECT', 'Gallery - New [type] Created!', array( '[type]' => $typeTranslated ) ), false, true, $extraStrings, false );
				$message			=	$cbUser->replaceUserVars( CBTxt::T( 'NOTIFY_NEW_FOLDER_CREATED_BODY', '<a href="[user_url]">[formatname]</a> created [folder_type] <a href="[folder_url]">[folder_title]</a> and requires <a href="[folder_tab_url]">approval</a>!' ), false, true, $extraStrings, false );

				$notifications		=	new cbNotification();

				$notifications->sendToModerators( $subject, $message, false, 1 );
			}

			cbRedirect( $returnUrl, CBTxt::T( 'FOLDER_SAVED_SUCCESSFULLY_AND_AWAITING_APPROVAL', '[type] saved successfully and awaiting approval!', array( '[type]' => $typeTranslated ) ) );
		} else {
			cbRedirect( $returnUrl, CBTxt::T( 'FOLDER_SAVED_SUCCESSFULLY', '[type] saved successfully!', array( '[type]' => $typeTranslated ) ) );
		}
	}

	/**
	 * Sets the published state of a folder
	 *
	 * @param int       $state
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function stateFolder( $state, $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework;

		$row						=	new cbgalleryFolderTable();

		$row->load( (int) $id );

		if ( $this->input( 'folder', false, GetterInterface::BOOLEAN ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $this->element, false, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );
		}

		if ( ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) || ( ( $viewer->get( 'id' ) == $row->get( 'user_id' ) ) && ( $row->get( 'published' ) == -1 ) && $this->params->get( $type . '_folder_approval', 0 ) ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Album' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Folder' );
				break;
		}

		$row->set( 'published', (int) $state );

		if ( $row->getError() || ( ! $row->store() ) ) {
			cbRedirect( $returnUrl, CBTxt::T( 'FOLDER_STATE_FAILED_TO_SAVE', '[type] state failed to save! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		cbRedirect( $returnUrl, CBTxt::T( 'FOLDER_STATE_SAVED_SUCCESSFULLY', '[type] state saved successfully!', array( '[type]' => $typeTranslated ) ) );
	}

	/**
	 * Deletes a folder
	 *
	 * @param int       $id
	 * @param string    $type
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param UserTable $viewer
	 */
	private function deleteFolder( $id, $type, $tab, $user, $viewer )
	{
		global $_CB_framework;

		$row						=	new cbgalleryFolderTable();

		$row->load( (int) $id );

		$profileUrl					=	$_CB_framework->userProfileUrl( (int) $row->get( 'user_id', $user->get( 'id' ) ), false, $tab->get( 'tabid' ) );

		if ( ( ! $row->get( 'id' ) ) || ( $row->get( 'type' ) != $type ) || ( ( $viewer->get( 'id' ) != $row->get( 'user_id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		switch( $type ) {
			case 'photos':
			case 'videos':
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Album' );
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Folder' );
				break;
		}

		if ( ! $row->canDelete() ) {
			cbRedirect( $profileUrl, CBTxt::T( 'FOLDER_FAILED_TO_DELETE', '[type] failed to delete! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		if ( ! $row->delete() ) {
			cbRedirect( $profileUrl, CBTxt::T( 'FOLDER_FAILED_TO_DELETE', '[type] failed to delete! Error: [error]', array( '[type]' => $typeTranslated, '[error]' => $row->getError() ) ), 'error' );
		}

		cbRedirect( $profileUrl, CBTxt::T( 'FOLDER_DELETED_SUCCESSFULLY', '[type] deleted successfully!', array( '[type]' => $typeTranslated ) ) );
	}
}