<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbmedizdProductEdit
{

	/**
	 * @param cbinvitesInviteTable $row
	 * @param array                $input
	 * @param UserTable            $user
	 * @param cbPluginHandler      $plugin
	 */
	static function showProductEdit( $row, $input, $user, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		cbValidator::loadValidation();

		$cbModerator		=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();
		$pageTitle			=	( $row->get( 'id' ) ? CBTxt::T( 'MEDPR_EDIT_PRODUCT' ) : CBTxt::T( 'MEDPR_CREATE_PRODUCT' ) );

		$_CB_framework->setPageTitle( $pageTitle );
		$_CB_framework->appendPathWay( htmlspecialchars( CBTxt::T( 'MEDPR_MEDICINE_PRODUCT' ) ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbinvitesTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $pageTitle ), $_CB_framework->pluginClassUrl( $plugin->element, true, ( $row->get( 'id' ) ? array( 'action' => 'medizd', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) : array( 'action' => 'medizd', 'func' => 'new' ) ) ) );

		initToolTip();

		$return				=	'<div class="medizdEdit">'
							.		'<form action="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'medizd', 'func' => 'save', 'id' => (int) $row->get( 'id' ) ) ) . '" method="post" enctype="multipart/form-data" name="medizdForm" id="medizdForm" class="cb_form medizdForm form-auto cbValidation">'
							.			( $pageTitle ? '<div class="invitesTitle page-header"><h3>' . $pageTitle . '</h3></div>' : null )
							.			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="to" class="col-sm-3 control-label">' . CBTxt::T( 'MEDPR_CODE' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['code']
							.					getFieldIcons( 1, 1, null)
							.				'</div>'
							.			'</div>'
							.			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="subject" class="col-sm-3 control-label">' . CBTxt::T( 'Name' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['name']
							.					getFieldIcons( 1, 1, null)
							.				'</div>'
							.			'</div>'
							.			'<div class="cbft_textarea cbtt_textarea form-group cb_form_line clearfix">'
							.				'<label for="body" class="col-sm-3 control-label">' . CBTxt::T( 'MEDPR_DESCRIPTION' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['description']
							.					getFieldIcons( 1, 0, null )
							.				'</div>'
							.			'</div>';

		if ( $cbModerator ) {
			$return			.=			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user_id" class="col-sm-3 control-label">' . CBTxt::T( 'Category' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['category']
							.					getFieldIcons( 1, 1, null)
							.				'</div>'
							.			'</div>'
							.			'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user" class="col-sm-3 control-label">' . CBTxt::T( 'MEDPR_MANUFACTURE' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['proizvoditel']
							.					getFieldIcons( 1, 0, null)
							.				'</div>'
							.			'</div>'
                                                        .   '<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user" class="col-sm-3 control-label">' . CBTxt::T( 'MEDPR_COUNTRY' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['country']
							.					getFieldIcons( 1, 0, null)
							.				'</div>'
							.			'</div>'
                                                        .   '<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.				'<label for="user" class="col-sm-3 control-label">' . CBTxt::T( 'MEDPR_PRICE' ) . '</label>'
							.				'<div class="cb_field col-sm-9">'
							.					$input['price']
							.					getFieldIcons( 1, 0, null)
							.				'</div>'
							.			'</div>';
		}

		

		$return				.=			'<div class="form-group cb_form_line clearfix">'
							.				'<div class="col-sm-offset-3 col-sm-9">'
							.					'<input type="submit" value="' . htmlspecialchars( ( $row->get( 'id' ) ? CBTxt::T( 'MEDPR_UPDATE_PRODUCT' ) : CBTxt::T( 'MEDPR_SAVE_PRODUCT' ) ) ) . '" class="invitesButton invitesButtonSubmit btn btn-primary"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
							.					' <input type="button" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" class="invitesButton invitesButtonCancel btn btn-default" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to cancel? All unsaved data will be lost!' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbmedizdFormTab' ) . '\'; }" />'
							.				'</div>'
							.			'</div>'
							.			cbGetSpoofInputTag( 'plugin' )
							.		'</form>'
							.	'</div>';

		echo $return;
	}
}
?>