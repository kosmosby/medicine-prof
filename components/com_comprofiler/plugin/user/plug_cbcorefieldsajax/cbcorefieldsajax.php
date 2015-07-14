<?php
/**
 * Community Builder (TM)
 * @version $Id: $
 * @package CommunityBuilder
 * @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */
use CB\Database\Table\UserTable;
use CB\Database\Table\FieldTable;
use CBLib\Registry\Registry;
use CBLib\Registry\ParamsInterface;
use CBLib\Language\CBTxt;
use CBLib\Application\Application;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->loadPluginGroup( 'user', array( 1 ) );
$_PLUGINS->registerUserFieldParams();
$_PLUGINS->registerFunction( 'onBeforefieldClass', 'getAjaxResponse', 'CBfield_ajaxfields' );
$_PLUGINS->registerFunction( 'onBeforegetFieldRow', 'getAjaxDisplay', 'CBfield_ajaxfields' );

class CBfield_ajaxfields extends cbFieldHandler {

	private function canAjax( &$field, &$user, $output, $reason, $ignoreEmpty = false )
	{
		global $_CB_framework, $ueConfig;

		if ( ( $_CB_framework->getUi() == 1 ) && ( $output == 'html' ) && ( $reason == 'profile' ) && ( $field instanceof FieldTable ) && ( $user instanceof UserTable ) ) {
			if ( ! ( $field->params instanceof ParamsInterface ) ) {
				$params			=	new Registry( $field->params );
			} else {
				$params			=	$field->params;
			}

			$value				=	$user->get( $field->get( 'name' ) );
			$notEmpty			=	( ( ! ( ( $value === null ) || ( $value === '' ) ) ) || $ueConfig['showEmptyFields'] || cbReplaceVars( CBTxt::T( $field->params->get( 'ajax_placeholder' ) ), $user ) );
			$readOnly			=	$field->get( 'readonly' );

			if ( $field->get( 'name' ) == 'username' ) {
				if ( ! $ueConfig['usernameedit'] ) {
					$readOnly	=	true;
				}
			}

			if ( ( ! $field->get( '_noAjax', false ) ) && ( ! $readOnly ) && ( $notEmpty || $ignoreEmpty )
				 && $params->get( 'ajax_profile', 0 ) && Application::MyUser()->canViewAccessLevel( (int) $params->get( 'ajax_profile_access', 2 ) )
				 && ( ! cbCheckIfUserCanPerformUserTask( $user->get( 'id' ), 'allowModeratorsUserEdit' ) )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check well for the $reason ...
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable    $user
	 * @param  array                 $postdata
	 * @param  string                $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                            Expected output.
	 */
	public function getAjaxResponse( &$field, &$user, &$postdata, $reason )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS, $ueConfig;

		if ( ( cbGetParam( $_GET, 'function', null ) == 'savevalue' ) && $this->canAjax( $field, $user, 'html', $reason, true ) ) {
			$field->set( '_noAjax', true );

			if ( in_array( $field->get( 'name' ), array ( 'firstname', 'middlename', 'lastname' ) ) ) {
				if ( $field->get( 'name' ) != 'firstname' ) {
					$postdata['firstname']			=	$user->get( 'firstname' );
				}

				if ( $field->get( 'name' ) != 'middlename' ) {
					$postdata['middlename']			=	$user->get( 'middlename' );
				}

				if ( $field->get( 'name' ) != 'lastname' ) {
					$postdata['lastname']			=	$user->get( 'lastname' );
				}
			}

			$_PLUGINS->callField( $field->get( 'type' ), 'fieldClass', array( &$field, &$user, &$postdata, $reason ), $field );

			$oldUserComplete						=	new UserTable( $_CB_database );

			foreach ( array_keys( get_object_vars( $user ) ) as $k ) {
				if ( substr( $k, 0, 1 ) != '_' ) {
					$oldUserComplete->set( $k, $user->get( $k ) );
				}
			}

			$orgValue								=	$user->get( $field->get( 'name' ) );

			$_PLUGINS->callField( $field->get( 'type' ), 'prepareFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );

			$store									=	false;

			if ( ! count( $_PLUGINS->getErrorMSG( false ) ) ) {
				$_PLUGINS->callField( $field->get( 'type' ), 'commitFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );

				if ( ! count( $_PLUGINS->getErrorMSG( false ) ) ) {
					if ( $_CB_framework->myId() == $user->get( 'id' ) ) {
						$user->set( 'lastupdatedate', $_CB_framework->getUTCDate() );
					}

					$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$user, &$user, &$oldUserComplete, &$oldUserComplete ) );

					$clearTextPassword				=	null;

					if ( $field->get( 'name' ) == 'password' ) {
						$clearTextPassword			=	$user->get( 'password' );

						$user->set( 'password', $user->hashAndSaltPassword( $clearTextPassword ) );
					}

					$store							=	$user->store();

					if ( $clearTextPassword ) {
						$user->set( 'password', $clearTextPassword );
					}

					$_PLUGINS->trigger( 'onAfterUserUpdate', array( &$user, &$user, $oldUserComplete ) );
				} else {
					$_PLUGINS->callField( $field->get( 'type' ), 'rollbackFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );
					$_PLUGINS->trigger( 'onSaveUserError', array( &$user, $user->getError(), $reason ) );
				}
			}

			if ( ! $store ) {
				if ( $orgValue != $user->get( $field->get( 'name' ) ) ) {
					$user->set( $field->get( 'name' ), $orgValue );
				}
			}

			$return									=	null;

			switch ( $field->get( 'type' ) ) {
				case 'emailaddress';
					$value							=	$user->get( $field->get( 'name' ) );

					if ( $value ) {
						if ( $ueConfig['allow_email'] == 1 ) {
							$return					.=	'<a href="mailto:' . htmlspecialchars( $value ) . '"  target="_blank">' . htmlspecialchars( $value ) . '</a>';
						} else {
							$return					.=	htmlspecialchars( $value );
						}
					}
					break;
				case 'primaryemailaddress';
					$value							=	$user->get( $field->get( 'name' ) );

					if ( $value && ( $ueConfig['allow_email_display'] != 4 ) ) {
						switch ( $ueConfig['allow_email_display'] ) {
							case 1:
								$return				.=	htmlspecialchars( $value );
								break;
							case 2:
								$return				.=	'<a href="mailto:' . htmlspecialchars( $value ) . '">' . htmlspecialchars( $value ) . '</a>';
								break;
							case 3:
								$return				.=	'<a href="' . $_CB_framework->viewUrl( 'emailuser', true, array( 'uid' => (int) $user->get( 'id' ) ) ) . '" title="' . htmlspecialchars( CBTxt::T( 'UE_MENU_SENDUSEREMAIL_DESC', 'Send an Email to this user' ) ) . '">' . CBTxt::T( 'UE_SENDEMAIL', 'Send Email' ) . '</a>';
								break;
						}
					}
					break;
				default:
					$return							.=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$user, 'html', 'none', $reason, 0 ), $field );
					break;
			}

			$placeholder							=	cbReplaceVars( CBTxt::T( $field->params->get( 'ajax_placeholder' ) ), $user );
			$emptyValue								=	cbReplaceVars( $ueConfig['emptyFieldsText'], $user );

			if ( ( ( ! $return ) || ( $return == $emptyValue ) ) && $placeholder ) {
				$return								=	$placeholder;
			} elseif ( ( ! $return ) && ( ! $ueConfig['showEmptyFields'] ) ) {
				$return								=	$emptyValue;
			}

			$error									=	$this->getFieldAjaxError( $field, $user, $reason );
			$return									=	( $error ? '<div class="alert alert-danger">' . $error . '</div>' : null ) . $return;

			$field->set( '_noAjax', false );

			return $return;
		}

		return null;
	}

	/**
	 * Formatter:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting           'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getAjaxDisplay( &$field, &$user, $output, $formatting, $reason, $list_compare_types )
	{
		global $_CB_framework, $_PLUGINS, $ueConfig;

		if ( $formatting && ( $formatting != 'none' ) && $this->canAjax( $field, $user, $output, $reason ) ) {
			$field->set( '_noAjax', true );

			$hasEdit					=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$user, 'htmledit', 'none', 'edit', $list_compare_types ), $field );

			if ( trim( $hasEdit ) ) {
				$placeholder			=	cbReplaceVars( CBTxt::T( $field->params->get( 'ajax_placeholder' ) ), $user );

				$formatted				=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$user, $output, 'none', $reason, $list_compare_types ), $field );

				if ( ( ( ! $formatted ) || ( $formatted == $ueConfig['emptyFieldsText'] ) ) && $placeholder ) {
					$formatted			=	$placeholder;
				}

				$format					=	( $field->params->get( 'fieldVerifyInput', 0 ) ? 'div' : 'none' );

				if ( $format != 'none' ) {
					$edit				=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$user, 'htmledit', $format, 'edit', $list_compare_types ), $field );
				} else {
					$edit				=	$hasEdit;
				}

				if ( trim( $edit ) ) {
					static $JS_loaded	=	0;

					if ( ! $JS_loaded++ ) {
						cbValidator::loadValidation();

						$_CB_framework->document->addHeadStyleSheet( '/components/com_comprofiler/plugin/user/plug_cbcorefieldsajax/cbcorefieldsajax.css' );

						$_CB_framework->addJQueryPlugin( 'cbajaxfield', '/components/com_comprofiler/plugin/user/plug_cbcorefieldsajax/cbcorefieldsajax.js' );

						$_CB_framework->outputCbJQuery( "$( '.cbAjaxContainer' ).cbajaxfield();", array( 'cbajaxfield', 'form' ) );
					}

					$formUrl			=	$_CB_framework->viewUrl( 'fieldclass', true, array( 'field' => $field->get( 'name' ), 'function' => 'savevalue', 'user' => (int) $user->get( 'id' ), 'reason' => $reason ), 'raw' );
					$formId				=	( htmlspecialchars( $field->get( 'name' ) ) . '_' . (int) $user->get( 'id' ) ) . '_ajax';

					$return				=	'<div id="' . $formId . '_container" class="cbAjaxContainer">'
										.		'<form action="' . $formUrl .'" name="' . $formId . '" id="' . $formId . '" enctype="multipart/form-data" method="post" class="cbAjaxForm cbValidation cb_form form-auto hidden">'
										.			'<div class="cbAjaxInput form-group cb_form_line clearfix">'
										.				'<div class="cb_field">'
										.					$edit
										.				'</div>'
										.			'</div>'
										.			'<div class="cbAjaxButtons form-group cb_form_line clearfix">'
										.				'<input type="submit" class="cbAjaxSubmit btn btn-primary" value="' . htmlspecialchars( CBTxt::T( 'Update' ) ) . '" />'
										.				' <input type="button" class="cbAjaxCancel btn btn-default" value="' . htmlspecialchars( CBTxt::T( 'Cancel' ) ) . '" />'
										.			'</div>'
										.			cbGetSpoofInputTag( 'fieldclass' )
										.			cbGetRegAntiSpamInputTag()
										.		'</form>'
										.		'<div class="cbAjaxValue fa-before fa-pencil">'
										.			$formatted
										.		'</div>'
										.	'</div>';

					if ( $field->get( 'type' ) == 'editorta' ) {
						$js				=	"$( '#" . addslashes( $formId ) . "_container' ).on( 'cbajaxfield.serialize', function() {"
										.		$_CB_framework->saveCmsEditorJS( $field->get( 'name' ), 0, false )
										.	"});";

						$_CB_framework->outputCbJQuery( $js );
					}

					return $this->renderFieldHtml( $field, $user, $return, $output, $formatting, $reason, array() );
				}
			}

			$field->set( '_noAjax', false );
		}

		return null;
	}

	private function getFieldAjaxError( &$field, &$user, $reason  )
	{
		global $_PLUGINS;

		$errors	=	$_PLUGINS->getErrorMSG( false );
		$title	=	cbFieldHandler::getFieldTitle( $field, $user, 'text', $reason );

		if ( $errors ) foreach ( $errors as $error ) {
			if ( stristr( $error, $title ) ) {
				return str_replace( $title . ' : ', '', $error );
			}
		}

		return null;
	}
}
?>