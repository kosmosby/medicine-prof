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
 * Class HTML_cbhangoutBlogEdit
 * Template for CB Blogs Edit view
 */
class HTML_cbhangoutBlogEdit
{
	/**
	 * @param  OrderedTable  $row
	 * @param  string[]      $input
	 * @param  UserTable     $user
	 * @param  stdClass      $model
	 * @param  PluginTable   $plugin
	 */
	static function showBlogEdit( $row, $input, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin )
	{
		global $_CB_framework, $_PLUGINS, $_LANG;

		cbValidator::loadValidation();

		$blogMode			=	$plugin->params->get( 'hangout_mode', 1 );
		$pageTitle			=	( $row->get( 'id' ) ? $_LANG['Edit Hangout'] : $_LANG['Create Hangout'] );
		$cbModerator		=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$_CB_framework->setPageTitle( $pageTitle );
		$_CB_framework->appendPathWay( htmlspecialchars( $_LANG['Hangout'] ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbhangoutTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $pageTitle ), $_CB_framework->pluginClassUrl( $plugin->element, true, ( $row->get( 'id' ) ? array( 'action' => 'hangout', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) : array( 'action' => 'hangout', 'func' => 'new' ) ) ) );

		initToolTip();

		$return				=	'<div class="blogEdit">'
							.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'hangout', 'func' => 'save', 'id' => (int) $row->get( 'id' ) ) ) . '" method="post" enctype="multipart/form-data" name="blogForm" id="blogForm" class="cb_form blogForm form-auto cbValidation">'
							.			( $pageTitle ? '<div class="blogsTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null );

		if ( $cbModerator || ( ! $plugin->params->get( 'hangout_approval', 0 ) ) ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="published" class="col-sm-3 control-label">' . CBTxt::Th( 'Published' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['published']
							.					getFieldIcons( 1, 0, null, '' )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'hangout_category_config', 1 ) || $cbModerator ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="category" class="col-sm-3 control-label">' . CBTxt::Th( 'Category' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['category']
							.					getFieldIcons( 1, 0, null, '' )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'hangout_access_config', 1 ) || $cbModerator ) {
			$return			.=			'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
							.				'<label for="access" class="col-sm-3 control-label">' . CBTxt::Th( 'Access' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['access']
							.					getFieldIcons( 1, 0, null, '' )
							.				'</div>'
							.			'</div>';
		}

		$return				.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="title" class="col-sm-3 control-label">' . CBTxt::Th( 'Title' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['title']
							.					getFieldIcons( 1, 1, null, '' )
							.				'</div>'
							.			'</div>';

		if ( in_array( $blogMode, array( 1, 2 ) ) ) {
			$return			.=			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
							.				'<label for="hangout_intro" class="col-sm-3 control-label">' . ( $blogMode == 1 ? $_LANG['Text intro'] : $_LANG['Text intro'] ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['hangout_intro']
							.					getFieldIcons( 1, 0, null, '' )
							.				'</div>'
							.			'</div>';
		}

		if ( in_array( $blogMode, array( 1, 3 ) ) ) {
			$return			.=			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
							.				'<label for="hangout_full" class="col-sm-3 control-label">' . ( $blogMode == 1 ? $_LANG['Text full'] : $_LANG['Text full'] ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['hangout_full']
							.					getFieldIcons( 1, 0, null, '' )
							.				'</div>'
							.			'</div>';
                        
                        $return			.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="title" class="col-sm-3 control-label">' . $_LANG['Price'] . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['price']
							.					getFieldIcons( 1, 0, null, $_LANG['Input price']  )
							.				'</div>'
							.			'</div>';
                        
		}

		if ( $cbModerator ) {
			$return			.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user" class="col-sm-3 control-label">' . CBTxt::T( 'Owner' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['user']
							.					getFieldIcons( 1, 1, null, '' )
							.				'</div>'
							.			'</div>';
		}

		if ( $plugin->params->get( 'hangout_captcha', 0 ) && ( ! $cbModerator ) ) {
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
							.					'<input type="submit" value="' . htmlspecialchars( ( $row->get( 'id' ) ? $_LANG["Update Hangout"] : $_LANG["Create Hangout"] ) ) . '" class="blogsButton blogsButtonSubmit btn btn-primary"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
							.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="blogsButton blogsButtonCancel btn btn-default" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbhangoutTab' ) . '\'; }" />'
							.				'</div>'
							.			'</div>'
							.			cbGetSpoofInputTag( 'plugin' )
							.		'</form>'
							.	'</div>';

		echo $return;
	}
}
