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
use CBLib\Registry\GetterInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbgalleryFolderEdit
{

	/**
	 * @param cbgalleryFolderTable $row
	 * @param array                $input
	 * @param string               $type
	 * @param TabTable             $tab
	 * @param UserTable            $user
	 * @param UserTable            $viewer
	 * @param cbPluginHandler      $plugin
	 */
	static public function showFolderEdit( $row, $input, $type, $tab, $user, $viewer, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$_PLUGINS->trigger( 'gallery_onBeforeFolderEdit', array( &$row, &$input, $type, $tab, $user, $viewer, $plugin ) );

		cbValidator::loadValidation();

		switch( $type ) {
			case 'photos':
				$galleryType		=	CBTxt::T( 'Photos' );
				break;
			case 'files':
				$galleryType		=	CBTxt::T( 'Files' );
				break;
			case 'videos':
				$galleryType		=	CBTxt::T( 'Videos' );
				break;
			case 'music':
				$galleryType		=	CBTxt::T( 'Music' );
				break;
			default:
				$galleryType		=	CBTxt::T( 'Items' );
				break;
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

		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$pageTitle					=	( $row->get( 'id' ) ? CBTxt::T( 'EDIT_FOLDER_TYPE', 'Edit [type]', array( '[type]' => $typeTranslated ) ) : CBTxt::T( 'NEW_FOLDER_TYPE', 'New [type]', array( '[type]' => $typeTranslated ) ) );

		if ( $plugin->input( 'folder', false, GetterInterface::BOOLEAN ) ) {
			$returnUrl				=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'show', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
			$formUrl				=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'save', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ), 'folder' => true ) );
		} else {
			$returnUrl				=	$_CB_framework->userProfileUrl( (int) $user->get( 'id' ), true, $tab->get( 'tabid' ) );
			$formUrl				=	$_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'folders', 'func' => 'save', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) );
		}

		$_CB_framework->setPageTitle( $pageTitle );
		$_CB_framework->appendPathWay( htmlspecialchars( $galleryType ), $returnUrl );
		$_CB_framework->appendPathWay( htmlspecialchars( $pageTitle ), $_CB_framework->pluginClassUrl( $plugin->element, true, ( $row->get( 'id' ) ? array( 'action' => 'folders', 'func' => 'edit', 'type' => $type, 'id' => (int) $row->get( 'id' ), 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) : array( 'action' => 'folders', 'func' => 'new', 'type' => $type, 'user' => (int) $user->get( 'id' ), 'tab' => (int) $tab->get( 'tabid' ) ) ) ) );

		initToolTip();

		$return						=	'<div class="' . htmlspecialchars( $type ) . 'FolderEdit">'
									.		'<form action="' . $formUrl . '" method="post" enctype="multipart/form-data" name="' . htmlspecialchars( $type ) . 'FolderForm" id="' . htmlspecialchars( $type ) . 'FolderForm" class="cb_form ' . htmlspecialchars( $type ) . 'FolderForm galleryFolderForm form-auto cbValidation">'
									.			( $pageTitle ? '<div class="galleryFolderTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null );

		if ( $cbModerator || ( ! $plugin->params->get( $type . '_folder_approval', 0 ) ) || ( $row->get( 'id' ) && ( $row->get( 'published' ) != -1 ) ) ) {
			$return					.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
									.				'<label for="published" class="col-sm-3 control-label">' . CBTxt::T( 'Published' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['published']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'FOLDER_PUBLISHED_DESCRIPTION', 'Select publish status of the [type]. If unpublished the [type] will not be visible to the public.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';
		}

		$return						.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
									.				'<label for="title" class="col-sm-3 control-label">' . CBTxt::T( 'Title' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['title']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'FOLDER_TITLE_DESCRIPTION', 'Optionally input a title. If no title is provided the date will be displayed as the title.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>'
									.			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
									.				'<label for="description" class="col-sm-3 control-label">' . CBTxt::T( 'Description' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['description']
									.					getFieldIcons( 1, 0, null, CBTxt::T( 'FOLDER_DESCRIPTION_DESCRIPTION', 'Optionally input a description.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';

		if ( $cbModerator ) {
			$return					.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
									.				'<label for="user_id" class="col-sm-3 control-label">' . CBTxt::T( 'Owner' ) . '</label>'
									.				'<div class="cb_field col-sm-9">'
									.					$input['user_id']
									.					getFieldIcons( 1, 1, null, CBTxt::T( 'FOLDER_OWNER_DESCRIPTION', 'Input owner as single integer user_id.', array( '[type]' => $typeTranslated ) ) )
									.				'</div>'
									.			'</div>';
		}

		if ( $plugin->params->get( $type . '_folder_captcha', 0 ) && ( ! $cbModerator ) ) {
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
									.					'<input type="submit" value="' . htmlspecialchars( ( $row->get( 'id' ) ? CBTxt::T( 'UPDATE_FOLDER_TYPE', 'Update [type]', array( '[type]' => $typeTranslated ) ) : CBTxt::T( 'CREATE_FOLDER_TYPE', 'Create [type]', array( '[type]' => $typeTranslated ) ) ) ) . '" class="galleryButton galleryButtonSubmit btn btn-primary" ' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
									.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="galleryButton galleryButtonCancel btn btn-default" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ) ) { location.href = \'' . $returnUrl . '\'; }" />'
									.				'</div>'
									.			'</div>'
									.			cbGetSpoofInputTag( 'plugin' )
									.		'</form>'
									.	'</div>';

		echo $return;
	}
}