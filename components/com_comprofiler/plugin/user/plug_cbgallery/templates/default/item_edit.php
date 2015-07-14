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

class HTML_cbgalleryItemEdit
{

	/**
	 * @param cbgalleryItemTable $row
	 * @param array              $input
	 * @param string             $type
	 * @param TabTable           $tab
	 * @param UserTable          $user
	 * @param UserTable          $viewer
	 * @param cbPluginHandler    $plugin
	 */
	static public function showItemEdit( $row, $input, $type, $tab, $user, $viewer, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeItemEdit', array( &$row, &$input, $type, $tab, $user, $viewer, $plugin ) );

		/** @var Registry $params */
		$params						=	$tab->params;

		cbValidator::loadValidation();

		switch( $type ) {
			case 'photos':
				$typeTranslated		=	CBTxt::T( 'Photo' );
				$galleryType		=	CBTxt::T( 'Photos' );
				break;
			case 'files':
				$typeTranslated		=	CBTxt::T( 'File' );
				$galleryType		=	CBTxt::T( 'Files' );
				break;
			case 'videos':
				$typeTranslated		=	CBTxt::T( 'Video' );
				$galleryType		=	CBTxt::T( 'Videos' );
				break;
			case 'music':
				$typeTranslated		=	CBTxt::T( 'Music' );
				$galleryType		=	$typeTranslated;
				break;
			default:
				$typeTranslated		=	CBTxt::T( 'Item' );
				$galleryType		=	CBTxt::T( 'Items' );
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

		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$pageTitle					=	( $row->get( 'id' ) ? CBTxt::T( 'EDIT_ITEM_TYPE', 'Edit [type]', array( '[type]' => $typeTranslated ) ) : CBTxt::T( 'NEW_ITEM_TYPE', 'New [type]', array( '[type]' => $typeTranslated ) ) );

		if ( $row->get( 'folder' ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'folder' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $user->get( 'id' ), true, $tab->get( 'tabid' ) );
		}

		$_CB_framework->setPageTitle( $pageTitle );
		$_CB_framework->appendPathWay( htmlspecialchars( $galleryType ), $returnUrl );
		$_CB_framework->appendPathWay( htmlspecialchars( $pageTitle ), $_CB_framework->pluginClassUrl( $plugin->element, true, ( $row->get( 'id' ) ? array( 'action' => 'items', 'func' => 'edit', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) : array( 'action' => 'items', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) ) );

		initToolTip();

		$return						=	'<div class="' . htmlspecialchars( $type ) . 'ItemEdit">'
									.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'save', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) . '" method="post" enctype="multipart/form-data" name="' . htmlspecialchars( $type ) . 'ItemForm" id="' . htmlspecialchars( $type ) . 'ItemForm" class="cb_form ' . htmlspecialchars( $type ) . 'ItemForm galleryItemForm form-auto cbValidation">'
									.			( $pageTitle ? '<div class="galleryItemTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null );

		if ( $cbModerator || ( ! $plugin->params->get( $type . '_item_approval', 0 ) ) || ( $row->get( 'id' ) && ( $row->get( 'published' ) != -1 ) ) ) {
			$return					.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
									.				'<label for="published" class="col-sm-3 control-label">' . CBTxt::T( 'Published' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['published']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'ITEM_PUBLISHED_DESCRIPTION', 'Select publish status of the [type]. If unpublished the [type] will not be visible to the public.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';
		}

		$return						.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
									.				'<label for="title" class="col-sm-3 control-label">' . CBTxt::T( 'Title' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['title']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'ITEM_TITLE_DESCRIPTION', 'Optionally input a title. If no title is provided the filename will be displayed as the title.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';

		if ( $params->get( 'tab_' . $type . '_folders', 1 ) ) {
			$return					.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
									.				'<label for="folder" class="col-sm-3 control-label">' . $folderType . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['folder']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'ITEM_FOLDER_DESCRIPTION', 'Select the [folder_type] for this [type].', array( '[folder_type]' => $folderType, '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';
		}

		if ( $row->get( 'id' ) && $row->checkExists() ) {
			$domain					=	$row->getLinkDomain();

			if ( $domain ) {
				$downloadPath		=	htmlspecialchars( $row->getFilePath() );
			} else {
				$downloadPath		=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'download', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
			}

			$return					.=			'<div class="cbft_delimiter form-group cb_form_line clearfix">'
									.				'<div class="cb_field col-sm-offset-3 col-sm-9">'
									.					'<a href="' . $downloadPath . '" target="_blank">'
									.						$row->getFileName()
									.					'</a>'
									.					( ! in_array( $domain, array( 'youtube', 'youtu' ) ) ? ' (' . $row->getFileSize() . ')' : null )
									.				'</div>'
									.			'</div>';

			switch( $type ) {
				case 'photos':
					if ( $domain ) {
						$photoSrc	=	htmlspecialchars( $row->getFilePath() );
					} else {
						$photoSrc	=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'preview', 'type' => 'photos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					}

					$itemDisplay	=	'<img src="' . $photoSrc . '" type="' . htmlspecialchars( $row->getMimeType() ) . '" class="itemPhotoPreview img-responsive" />';
					break;
				case 'videos':
					$_CB_framework->outputCbJQuery( "$( '#itemVideoPlayer' ).mediaelementplayer({ isVideo: true });", 'media' );

					if ( $domain ) {
						$videoSrc	=	htmlspecialchars( $row->getFilePath() );
					} else {
						$videoSrc	=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => 'videos', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					}

					$itemDisplay	=	'<video width="640" height="360" style="width: 100%; height: 100%;" src="' . $videoSrc . '" type="' . htmlspecialchars( $row->getMimeType() ) . '" id="itemVideoPlayer"></video>';
					break;
				case 'music':
					$_CB_framework->outputCbJQuery( "$( '#itemMusicPlayer' ).mediaelementplayer({ isVideo: false });", 'media' );

					if ( $domain ) {
						$audioSrc	=	htmlspecialchars( $row->getFilePath() );
					} else {
						$audioSrc	=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'items', 'func' => 'show', 'type' => 'music', 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'v' => uniqid() ), 'raw', 0, true );
					}

					$itemDisplay	=	'<audio width="640" style="width: 100%;" src="' . $audioSrc . '" type="' . htmlspecialchars( $row->getMimeType() ) . '" id="itemMusicPlayer"></audio>';
					break;
				default:
					$itemDisplay	=	null;
					break;
			}

			if ( $itemDisplay ) {
				$return				.=			'<div class="cbft_delimiter form-group cb_form_line clearfix">'
									.				'<div class="cb_field col-sm-offset-3 col-sm-9">'
									.					$itemDisplay
									.				'</div>'
									.			'</div>';
			}
		}

		if ( $input['method'] ) {
			$return					.=			'<div id="itemMethod" class="cbft_select cbtt_select form-group cb_form_line clearfix">'
									.				'<label for="method" class="col-sm-3 control-label">' . $typeTranslated . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['method']
									.				'</div>'
									.			'</div>';
		}

		$newButton					=	null;

		if ( $input['upload'] ) {
			$newButton				=	CBTxt::T( 'UPLOAD_ITEM_TYPE', 'Upload [type]', array( '[type]' => $typeTranslated ) );

			$return					.=			'<div id="itemUpload" class="cbft_file cbtt_input form-group cb_form_line clearfix' . ( $input['method'] ? ' hidden' : null ) . '">'
									.				( ! $input['method'] ? '<label for="file" class="col-sm-3 control-label">' . $typeTranslated . '</label>' : null )
									.				'<div class="cb_field' . ( $input['method'] ? ' col-sm-offset-3' : null ) . ' col-sm-9">'
									.					$input['upload']
									.					getFieldIcons( 1, ( ! $row->get( 'id' ) ? 1 : 0 ), null, CBTxt::T( 'ITEM_UPLOAD_DESCRIPTION', 'Select the file to upload.', array( '[type]' => $typeTranslated ) ) )
									.					( $input['upload_limits'] ? '<div class="help-block">' . implode( ' ', $input['upload_limits'] ) . '</div>' : null )
									.				'</div>'
									.			'</div>';
		}

		if ( $input['link'] ) {
			if ( ! $newButton ) {
				$newButton			=	CBTxt::T( 'LINK_ITEM_TYPE', 'Link [type]', array( '[type]' => $typeTranslated ) );
			}

			$return					.=			'<div id="itemLink" class="cbft_text cbtt_input form-group cb_form_line clearfix' . ( $input['method'] ? ' hidden' : null ) . '">'
									.				( ! $input['method'] ? '<label for="value" class="col-sm-3 control-label">' . $typeTranslated . '</label>' : null )
									.				'<div class="cb_field' . ( $input['method'] ? ' col-sm-offset-3' : null ) . ' col-sm-9">'
									.					$input['link']
									.					getFieldIcons( 1, ( ! $row->get( 'id' ) ? 1 : 0 ), null, CBTxt::T( 'ITEM_LINK_DESCRIPTION', 'Input the URL to the file to link.', array( '[type]' => $typeTranslated ) ) )
									.					( $input['link_limits'] ? '<div class="help-block">' . implode( ' ', $input['link_limits'] ) . '</div>' : null )
									.				'</div>'
									.			'</div>';
		}

		if ( ! $newButton ) {
			$newButton				=	CBTxt::T( 'CREATE_ITEM_TYPE', 'Create [type]', array( '[type]' => $typeTranslated ) );
		}

		$return						.=			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
									.				'<label for="description" class="col-sm-3 control-label">' . CBTxt::T( 'Description' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['description']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'ITEM_DESCRIPTION_DESCRIPTION', 'Optionally input a description.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';

		if ( $cbModerator ) {
			$return					.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
									.				'<label for="user_id" class="col-sm-3 control-label">' . CBTxt::T( 'Owner' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['user_id']
									.					getFieldIcons( 1, 1, null, CBTxt::T( 'ITEM_OWNER_DESCRIPTION', 'Input owner as single integer user_id.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';
		}

		if ( $plugin->params->get( $type . '_item_captcha', 0 ) && ( ! $cbModerator ) ) {
			$_PLUGINS->loadPluginGroup( 'user' );

			$captcha				=	$_PLUGINS->trigger( 'onGetCaptchaHtmlElements', array( false ) );

			if ( ! empty( $captcha ) ) {
				$captcha			=	$captcha[0];

				$return				.=			'<div class="form-group cb_form_line clearfix">'
									.				'<label class="col-sm-3 control-label">' . CBTxt::T( 'Captcha' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					( isset( $captcha[0] ) ? $captcha[0] : null )
									.				'</div>'
									.			'</div>'
									.			'<div class="form-group cb_form_line clearfix">'
									.				'<div class="cb_field col-sm-offset-3 col-sm-9">'
									.					str_replace( 'inputbox', 'form-control', ( isset( $captcha[1] ) ? $captcha[1] : null ) )
									.					getFieldIcons( 1, 1, null )
									.				'</div>'
									.			'</div>';
			}
		}

		$return						.=			'<div class="form-group cb_form_line clearfix">'
									.				'<div class="col-sm-offset-3 col-sm-9">'
									.					'<input type="submit" value="' . htmlspecialchars( ( $row->get( 'id' ) ? CBTxt::T( 'UPDATE_ITEM_TYPE', 'Update [type]', array( '[type]' => $typeTranslated ) ) : $newButton ) ) . '" class="galleryButton galleryButtonSubmit btn btn-primary" ' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
									.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="galleryButton galleryButtonCancel btn btn-default" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ) ) { location.href = \'' . $returnUrl . '\'; }" />'
									.				'</div>'
									.			'</div>'
									.			cbGetSpoofInputTag( 'plugin' )
									.		'</form>'
									.	'</div>';

		echo $return;
	}
}