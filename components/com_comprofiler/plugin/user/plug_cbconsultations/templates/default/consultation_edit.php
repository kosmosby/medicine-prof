<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbconsultationsconsultationEdit
 * Template for CB consultations Edit view
 */
class HTML_cbconsultationsconsultationEdit
{
	/**
	 * @param  OrderedTable  $row
	 * @param  string[]      $input
	 * @param  UserTable     $user
	 * @param  stdClass      $model
	 * @param  PluginTable   $plugin
	 */
	static function showconsultationEdit( $row, $input, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		cbValidator::loadValidation();

		$consultationMode			=	$plugin->params->get( 'consultation_mode', 1 );
		$pageTitle			=	( $row->get( 'id' ) ? CBTxt::T( 'Edit consultation' ) : CBTxt::T( 'Create consultation' ) );
		$cbModerator		=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$_CB_framework->setPageTitle( $pageTitle );
		$_CB_framework->appendPathWay( htmlspecialchars( CBTxt::T( 'consultations' ) ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbconsultationsTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $pageTitle ), $_CB_framework->pluginClassUrl( $plugin->element, true, ( $row->get( 'id' ) ? array( 'action' => 'consultations', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) : array( 'action' => 'consultations', 'func' => 'new' ) ) ) );

		initToolTip();

		$return				=	'<div class="consultationEdit">'
							.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'consultations', 'func' => 'save', 'id' => (int) $row->get( 'id' ) ) ) . '" method="post" enctype="multipart/form-data" name="consultationForm" id="consultationForm" class="cb_form consultationForm form-auto cbValidation">'
							.			( $pageTitle ? '<div class="consultationsTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null );

		if ( $cbModerator || ( ! $plugin->params->get( 'consultation_approval', 0 ) ) ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="published" class="col-sm-3 control-label">' . CBTxt::Th( 'Published' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['published']
							.					getFieldIcons( 1, 0, null, CBTxt::T( 'Select publish status of the consultation. Unpublished consultations will not be visible to the public.' ) )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'consultation_category_config', 1 ) || $cbModerator ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="category" class="col-sm-3 control-label">' . CBTxt::Th( 'Category' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['category']
							.					getFieldIcons( 1, 0, null, CBTxt::T( 'Select consultation category. Select the category that best describes your consultation.' ) )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'consultation_access_config', 1 ) || $cbModerator ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="access" class="col-sm-3 control-label">' . CBTxt::Th( 'Access' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['access']
							.					getFieldIcons( 1, 0, null, CBTxt::T( 'Select access to consultation; all groups above that level will also have access to the consultation.' ) )
							.				'</div>'
							.			'</div>';
		}

        //Consultation date and time
        $document = JFactory::getDocument();
        $document->addScript('/media/widgetkit/js/jquery.js');
        $document->addScript('/js/jquery.datetimepicker.js');
        $document->addStyleSheet('/css/jquery.datetimepicker.css');
        $document->addScriptDeclaration('jQuery( document ).ready( function(){jQuery(\'#datetimepicker\').datetimepicker();});');
        $return				.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
            .				'<label for="datetime" class="col-sm-3 control-label">' . CBTxt::Th( 'Date and Time' ) . '</label>'
            .				'<div class="cb_field col-sm-9">'
            .				'<input type="text" name="datetime" id="datetimepicker"/>'
            .					getFieldIcons( 1, 1, null, CBTxt::T( 'Input consultation Date and Time' ) )
            .				'</div>'
            .			'</div>';


		$return				.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="title" class="col-sm-3 control-label">' . CBTxt::Th( 'Title' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['title']
							.					getFieldIcons( 1, 1, null, CBTxt::T( 'Input consultation title. This is the title that will distinguish this consultation from others. Suggested to input something unique and intuitive.' ) )
							.				'</div>'
							.			'</div>';

		if ( in_array( $consultationMode, array( 1, 2 ) ) ) {
			$return			.=			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
							.				'<label for="consultation_intro" class="col-sm-3 control-label">' . ( $consultationMode == 1 ? CBTxt::T( 'consultation Intro' ) : CBTxt::T( 'consultation' ) ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['consultation_intro']
							.					getFieldIcons( 1, 0, null, CBTxt::T( 'Input HTML supported consultation intro contents. Suggested to use minimal but well formatting for easy readability.' ) )
							.				'</div>'
							.			'</div>';
		}

		if ( in_array( $consultationMode, array( 1, 3 ) ) ) {
			$return			.=			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
							.				'<label for="consultation_full" class="col-sm-3 control-label">' . ( $consultationMode == 1 ? CBTxt::T( 'consultation Full' ) : CBTxt::T( 'consultation' ) ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['consultation_full']
							.					getFieldIcons( 1, 0, null, CBTxt::T( 'Input HTML supported consultation contents. Suggested to use minimal but well formatting for easy readability.' ) )
							.				'</div>'
							.			'</div>';
		}

		if ( $cbModerator ) {
			$return			.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user" class="col-sm-3 control-label">' . CBTxt::T( 'Owner' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['user']
							.					getFieldIcons( 1, 1, null, CBTxt::T( 'Input owner of consultation as single integer user_id.' ) )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'consultation_captcha', 0 ) && ( ! $cbModerator ) ) {
			$_PLUGINS->loadPluginGroup( 'user' );

			$captcha		=	$_PLUGINS->trigger( 'onGetCaptchaHtmlElements', array( false ) );

			if ( ! empty( $captcha ) ) {
				$captcha	=	$captcha[0];

				$return		.=			'<div class="form-group cb_form_line clearfix">'
							.				'<label class="col-sm-3 control-label">' . CBTxt::Th( 'Captcha' ) . '</label>'
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

		$return				.=			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					'<input type="submit" value="' . htmlspecialchars( ( $row->get( 'id' ) ? CBTxt::T( 'Update consultation' ) : CBTxt::T( 'Create consultation' ) ) ) . '" class="consultationsButton consultationsButtonSubmit btn btn-primary"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
							.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="consultationsButton consultationsButtonCancel btn btn-default" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbconsultationsTab' ) . '\'; }" />'
							.				'</div>'
							.			'</div>'
							.			cbGetSpoofInputTag( 'plugin' )
							.		'</form>'
							.	'</div>';

		echo $return;
	}
}
