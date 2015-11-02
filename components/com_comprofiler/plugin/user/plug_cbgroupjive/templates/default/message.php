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
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\CBGroupJive;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveMessage
{

	/**
	 * render frontend group message
	 *
	 * @param GroupTable         $row
	 * @param array              $input
	 * @param UserTable          $user
	 * @param CBplug_cbgroupjive $plugin
	 * @return string
	 */
	static function showMessage( $row, $input, $user, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$pageTitle					=	CBTxt::T( 'Message' );

		$_CB_framework->setPageTitle( $pageTitle );

		cbValidator::loadValidation();
		initToolTip();

		$return						=	null;

		$_PLUGINS->trigger( 'gj_onBeforeDisplayMessage', array( &$return, &$row, &$input, $user ) );

		$return						.=	'<div class="gjMessage">'
									.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'groups', 'func' => 'send', 'id' => (int) $row->get( 'id' ) ) ) . '" method="post" enctype="multipart/form-data" name="gjMessageForm" id="gjMessageForm" class="cb_form gjMessageForm form-auto cbValidation">'
									.			( $pageTitle ? '<div class="gjMessageTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null )
									.			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix cbtwolines">'
									.				'<div class="cb_field col-sm-12">'
									.					$input['message']
									.					getFieldIcons( null, 0, null, CBTxt::T( 'Input a message to send to this groups users.' ) )
									.				'</div>'
									.			'</div>';

		if ( ( ! CBGroupJive::isModerator( $user->get( 'id' ) ) ) && $plugin->params->get( 'groups_message_captcha', 0 ) ) {
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
									.					getFieldIcons( null, 1, null )
									.				'</div>'
									.			'</div>';
			}
		}

		$return						.=			'<div class="form-group cb_form_line clearfix cbtwolines">'
									.				'<div class="col-sm-12">'
									.					'<input type="submit" value="' . htmlspecialchars( CBTxt::T( 'Send Message' ) ) . '" class="gjButton gjButtonSubmit btn btn-primary" ' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
									.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="gjButton gjButtonCancel btn btn-default" onclick="cbjQuery.cbconfirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ).done( function() { window.location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) . '\'; })" />'
									.				'</div>'
									.			'</div>'
									.			cbGetSpoofInputTag( 'plugin' )
									.		'</form>'
									.	'</div>';

		$_PLUGINS->trigger( 'gj_onAfterDisplayMessage', array( &$return, $row, $input, $user ) );

		$_CB_framework->setMenuMeta();

		echo $return;
	}
}