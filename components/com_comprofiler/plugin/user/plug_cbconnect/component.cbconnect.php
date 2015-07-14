<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\GetterInterface;
use CB\Database\Table\UserTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\FieldTable;
use CBLib\Registry\Registry;
use CBLib\Language\CBTxt;
use CBLib\Registry\ParamsInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBplug_cbconnect extends cbPluginHandler
{
	/** @var cbconnectHybrid The cbconnect hybrid object */
	private $_hybrid		=	null;
	/** @var Hybrid_Auth The hybridauth object */
	private $_hybridAuth	=	null;
	/** @var string The CB Connect provider */
	private $_provider		=	null;
	/** @var string The hybridauth provider */
	private $_providerId	=	null;
	/** @var string The provider CB fieldname */
	private $_providerField	=	null;
	/** @var string The provider CB translated name */
	private $_providerName	=	null;
	/** @var string The URL to return the user to */
	private $_returnUrl		=	null;

	/**
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param int       $ui
	 * @param array     $postdata
	 */
	public function getCBpluginComponent( /** @noinspection PhpUnusedParameterInspection */ $tab, /** @noinspection PhpUnusedParameterInspection */ $user, /** @noinspection PhpUnusedParameterInspection */ $ui, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		$returnUrl					=	$this->input( 'return', null, GetterInterface::BASE64 );

		if ( $returnUrl ) {
			$returnUrl				=	base64_decode( $returnUrl );
		}

		try {
			$hybrid					=	new cbconnectHybrid();
		} catch ( Exception $e ) {
			cbRedirect( ( $returnUrl ? $returnUrl : 'index.php' ), $e->getMessage(), 'error' );
			return;
		}

		$provider					=	$this->input( 'provider', null, GetterInterface::STRING );
		$providerId					=	null;

		if ( ! $provider ) {
			$providerId				=	$this->input( 'hauth_start', null, GetterInterface::STRING );

			if ( ! $providerId ) {
				$providerId			=	$this->input( 'hauth_done', null, GetterInterface::STRING );
			}

			$provider				=	$hybrid->getProviderFromId( $providerId );
		} else {
			$providerId				=	$hybrid->getIdFromProvider( $provider );
		}

		$action						=	$this->input( 'action', null, GetterInterface::STRING );
		$hybridAuth					=	null;
		$error						=	null;

		try {
			$hybridAuth				=	$hybrid->getHybridAuth();

			/** @var Hybrid_Storage $storage */
			$storage				=	$hybridAuth->storage();

			if ( $storage ) {
				if ( ! $returnUrl ) {
					$redirectUrl	=	$storage->get( 'redirect_url' );

					if ( $redirectUrl ) {
						$returnUrl	=	base64_decode( $redirectUrl );
					}
				} else {
					$storage->set( 'redirect_url', base64_encode( $returnUrl ) );
				}
			}
		} catch ( Exception $e ) {
			$error					=	$e->getMessage();
		}

		if ( ! $returnUrl ) {
			$returnUrl				=	'index.php';
		}

		if ( ( ! $hybridAuth ) || ( ! $this->params->get( $provider . '_enabled', false, GetterInterface::BOOLEAN ) ) ) {
			if ( ! $error ) {
				$error				=	CBTxt::T( 'PROVIDER_NOT_AVAILABLE', '[provider] is not available.', array( '[provider]' => $providerId ) );
			}

			cbRedirect( $this->_returnUrl, $error, 'error' );
			return;
		}

		$this->_hybrid				=	$hybrid;
		$this->_hybridAuth			=	$hybridAuth;
		$this->_provider			=	$provider;
		$this->_providerId			=	$providerId;
		$this->_providerField		=	$hybrid->getProviderField( $provider );
		$this->_providerName		=	$hybrid->getProviderName( $provider );
		$this->_returnUrl			=	$returnUrl;

		switch ( $action ) {
			case 'authenticate':
				$this->authenticate();
				break;
			case 'endpoint':
				$this->endpoint();
				break;
		}
	}

	/**
	 * Authorizes the provider, registers or links, then logs in as needed
	 */
	private function authenticate()
	{
		global $_CB_database;

		try {
			/** @var Hybrid_Provider_Adapter $adapter */
			$adapter				=	$this->_hybridAuth->authenticate( $this->_providerId );
		} catch ( Exception $e ) {
			cbRedirect( $this->_returnUrl, CBTxt::T( 'AUTH_TO_PROVIDER_FAILED', 'Authentication to [provider] failed. Error: [error]', array( '[provider]' => $this->_providerName, '[error]' => $e->getMessage() ) ), 'error' );
			return;
		}

		if ( $adapter ) {
			if ( ! $this->_hybridAuth->isConnectedWith( $this->_providerId ) ) {
				cbRedirect( $this->_returnUrl, CBTxt::T( 'CONNECTION_TO_PROVIDER_NOT_ESTABLISHED', 'Connection to [provider] not established.', array( '[provider]' => $this->_providerName ) ), 'error' );
				return;
			}

			try {
				/** @var Hybrid_User_Profile $profile */
				/** @noinspection PhpUndefinedMethodInspection */
				$profile			=	$adapter->getUserProfile();

				$this->profile( $profile );

				$myUser				=	CBuser::getMyUserDataInstance();

				$query				=	'SELECT ' . $_CB_database->NameQuote( 'id' )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
									.	"\n WHERE " . $_CB_database->NameQuote( $this->_providerField ) . " = " . $_CB_database->Quote( $profile->identifier );
				$_CB_database->setQuery( $query );
				$userId				=	(int) $_CB_database->loadResult();

				$user				=	CBuser::getUserDataInstance( $userId );

				if ( $myUser->get( 'id' ) ) {
					if ( ( ! $this->params->get( $this->_provider . '_link', true, GetterInterface::BOOLEAN ) ) && ( ! $myUser->get( $this->_providerField ) ) ) {
						cbRedirect( $this->_returnUrl, CBTxt::T( 'LINKING_FOR_PROVIDER_NOT_PERMITTED', 'Linking for [provider] is not permitted.', array( '[provider]' => $this->_providerName ) ), 'error' );
						return;
					}

					if ( ! $myUser->get( $this->_providerField ) ) {
						if ( $user->get( 'id' ) && ( $myUser->get( 'id' ) != $user->get( 'id' ) ) ) {
							cbRedirect( $this->_returnUrl, CBTxt::T( 'PROVIDER_ALREADY_LINKED', '[provider] account already linked to another user.', array( '[provider]' => $this->_providerName ) ), 'error' );
							return;
						}

						if ( ! $myUser->storeDatabaseValue( $this->_providerField, $profile->identifier ) ) {
							cbRedirect( $this->_returnUrl, CBTxt::T( 'PROVIDER_FAILED_TO_LINK', '[provider] account failed to link. Error: [error]', array( '[provider]' => $this->_providerName, '[error]' => $myUser->getError() ) ), 'error' );
							return;
						}

						cbRedirect( $this->_returnUrl, CBTxt::T( 'PROVIDER_LINKED_SUCCESSFULLY', '[provider] account linked successfully!', array( '[provider]' => $this->_providerName ) ) );
						return;
					}

					cbRedirect( $this->_returnUrl, CBTxt::T( 'ALREADY_LINKED_TO_PROVIDER', 'You are already linked to a [provider] account.', array( '[provider]' => $this->_providerName ) ), 'error' );
					return;
				} else {
					if ( ( ! $this->params->get( $this->_provider . '_register', true, GetterInterface::BOOLEAN ) ) && ( ! $user->get( 'id' ) ) ) {
						cbRedirect( $this->_returnUrl, CBTxt::T( 'SIGN_UP_WITH_PROVIDER_NOT_PERMITTED', 'Sign up with [provider] is not permitted.', array( '[provider]' => $this->_providerName ) ), 'error' );
						return;
					}

					$login			=	true;

					if ( ! $user->get( 'id' ) ) {
						$login		=	$this->register( $user, $profile );
					}

					if ( $login ) {
						$this->login( $user );
					}
				}
			} catch( Exception $e ) {
				cbRedirect( $this->_returnUrl, CBTxt::T( 'FAILED_TO_RETRIEVE_PROVIDER_PROFILE', 'Failed to retrieve [provider] profile. Error: [error]', array( '[provider]' => $this->_providerName, '[error]' => $e->getMessage() ) ), 'error' );
				return;
			}
		}
	}

	/**
	 * Process profile data
	 *
	 * @param Hybrid_User_Profile $profile
	 */
	private function profile( &$profile )
	{
		switch ( $this->_provider ) {
			case 'steam':
				$profile->identifier	=	str_replace( 'http://steamcommunity.com/openid/id/', '', $profile->identifier );
				break;
			case 'tumblr':
				$profile->identifier	=	str_replace( array( 'http://', '.tumblr.com/' ), '', $profile->identifier );
				break;
		}

		$birthdate						=	array(	( $profile->birthYear ? (string) $profile->birthYear : '0000' ),
													( $profile->birthMonth ? (string) $profile->birthMonth : '00' ),
													( $profile->birthDay ? (string) $profile->birthDay : '00' )
												);

		$profile->birthdate				=	implode( '-', $birthdate );
	}

	/**
	 * Registers a new user
	 *
	 * @param UserTable           $user
	 * @param Hybrid_User_Profile $profile
	 * @return bool
	 */
	private function register( $user, $profile )
	{
		global $_CB_framework, $_PLUGINS, $ueConfig;

		if ( ! $profile->identifier ) {
			cbRedirect( $this->_returnUrl, CBTxt::T( 'PROVIDER_PROFILE_MISSING', '[provider] profile could not be found.', array( '[provider]' => $this->_providerName ) ), 'error' );
			return false;
		}

		$mode						=	$this->params->get( $this->_provider . '_mode', 1, GetterInterface::INT );
		$approve					=	$this->params->get( $this->_provider . '_approve', 0, GetterInterface::INT );
		$confirm					=	$this->params->get( $this->_provider . '_confirm', 0, GetterInterface::INT );
		$usergroup					=	$this->params->get( $this->_provider . '_usergroup', null, GetterInterface::STRING );
		$approval					=	( $approve == 2 ? $ueConfig['reg_admin_approval'] : $approve );
		$confirmation				=	( $confirm == 2 ? $ueConfig['reg_confirmation'] : $confirm );
		$usernameFormat				=	$this->params->get( $this->_provider . '_username', null, GetterInterface::STRING );
		$username					=	null;
		$dummyUser					=	new UserTable();

		if ( $usernameFormat ) {
			$extras					=	array( 'provider' => $this->_provider, 'provider_id' => $this->_providerId, 'provider_name' => $this->_providerName );

			foreach ( (array) $profile as $k => $v ) {
				if ( ( ! is_array( $v ) ) && ( ! is_object( $v ) ) ) {
					$k				=	'profile_' . $k;

					$extras[$k]		=	$v;
				}
			}

			$username				=	preg_replace( '/[<>\\\\"%();&\']+/', '', trim( cbReplaceVars( $usernameFormat, $user, true, false, $extras, false ) ) );
		} else {
			if ( isset( $profile->username ) ) {
				$username			=	preg_replace( '/[<>\\\\"%();&\']+/', '', trim( $profile->username ) );
			}

			if ( ( ! $username ) || ( $username && $dummyUser->loadByUsername( $username ) ) ) {
				$username			=	preg_replace( '/[<>\\\\"%();&\']+/', '', trim( $profile->displayName ) );
			}
		}

		if ( ( ! $username ) || ( $username && $dummyUser->loadByUsername( $username ) ) ) {
			$username				=	(string) $profile->identifier;
		}

		if ( $mode == 2 ) {
			$user->set( 'email', $profile->email );
		} else {
			if ( $dummyUser->loadByUsername( $username ) ) {
				cbRedirect( $this->_returnUrl, CBTxt::T( 'UE_USERNAME_NOT_AVAILABLE', "The username '[username]' is already in use.", array( '[username]' =>  htmlspecialchars( $username ) ) ), 'error' );
				return false;
			}

			if ( ! $this->email( $user, $profile ) ) {
				return false;
			}

			if ( $dummyUser->loadByEmail( $user->get( 'email' ) ) ) {
				cbRedirect( $this->_returnUrl, CBTxt::T( 'UE_EMAIL_NOT_AVAILABLE', "The email '[email]' is already in use.", array( '[email]' =>  htmlspecialchars( $user->get( 'email' ) ) ) ), 'error' );
				return false;
			}

			$this->avatar( $user, $profile, $mode );

			if ( ! $usergroup ) {
				$gids				=	array( (int) $_CB_framework->getCfg( 'new_usertype' ) );
			} else {
				$gids				=	cbToArrayOfInt( explode( '|*|', $usergroup ) );
			}

			$user->set( 'gids', $gids );
			$user->set( 'sendEmail', 0 );
			$user->set( 'registerDate', $_CB_framework->getUTCDate() );
			$user->set( 'password', $user->hashAndSaltPassword( $user->getRandomPassword() ) );
			$user->set( 'registeripaddr', cbGetIPlist() );

			if ( $approval == 0 ) {
				$user->set( 'approved', 1 );
			} else {
				$user->set( 'approved', 0 );
			}

			if ( $confirmation == 0 ) {
				$user->set( 'confirmed', 1 );
			} else {
				$user->set( 'confirmed', 0 );
			}

			if ( ( $user->get( 'confirmed' ) == 1 ) && ( $user->get( 'approved' ) == 1 ) ) {
				$user->set( 'block', 0 );
			} else {
				$user->set( 'block', 1 );
			}
		}

		if ( $profile->firstName || $profile->lastName ) {
			$user->set( 'name', trim( $profile->firstName . ' ' . $profile->lastName ) );
		} elseif ( $profile->displayName ) {
			$user->set( 'name', trim( $profile->displayName ) );
		} else {
			$user->set( 'name', $username );
		}

		switch ( $ueConfig['name_style'] ) {
			case 2:
				$lastName			=	strrpos( $user->get( 'name' ), ' ' );

				if ( $lastName !== false ) {
					$user->set( 'firstname', substr( $user->get( 'name' ), 0, $lastName ) );
					$user->set( 'lastname', substr( $user->get( 'name' ), ( $lastName + 1 ) ) );
				} else {
					$user->set( 'firstname', '' );
					$user->set( 'lastname', $user->get( 'name' ) );
				}
				break;
			case 3:
				$middleName			=	strpos( $user->get( 'name' ), ' ' );
				$lastName			=	strrpos( $user->get( 'name' ), ' ' );

				if ( $lastName !== false ) {
					$user->set( 'firstname', substr( $user->get( 'name' ), 0, $middleName ) );
					$user->set( 'lastname', substr( $user->get( 'name' ), ( $lastName + 1 ) ) );

					if ( $middleName !== $lastName ) {
						$user->set( 'middlename', substr( $user->get( 'name' ), ( $middleName + 1 ), ( $lastName - $middleName - 1 ) ) );
					} else {
						$user->set( 'middlename', '' );
					}
				} else {
					$user->set( 'firstname', '' );
					$user->set( 'lastname', $user->get( 'name' ) );
				}
				break;
		}

		$user->set( 'username', $username );
		$user->set( $this->_providerField, $profile->identifier );

		$this->fields( $user, $profile, $mode );

		if ( $mode == 2 ) {
			foreach ( $user as $k => $v ) {
				$_POST[$k]			=	$v;
			}

			$emailPass				=	( isset( $ueConfig['emailpass'] ) ? $ueConfig['emailpass'] : '0' );
			$regErrorMSG			=	null;

			if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' ) && ( ( ! isset( $ueConfig['reg_admin_allowcbregistration'] ) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) ) ) {
				$msg				=	CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
			} else {
				$msg				=	null;
			}

			$_PLUGINS->loadPluginGroup( 'user' );

			$_PLUGINS->trigger( 'onBeforeRegisterFormRequest', array( &$msg, $emailPass, &$regErrorMSG ) );

			if ( $msg ) {
				$_CB_framework->enqueueMessage( $msg, 'error' );
				return false;
			}

			$fieldsQuery			=	null;
			$results				=	$_PLUGINS->trigger( 'onBeforeRegisterForm', array( 'com_comprofiler', $emailPass, &$regErrorMSG, $fieldsQuery ) );

			if ( $_PLUGINS->is_errors() ) {
				$_CB_framework->enqueueMessage( $_PLUGINS->getErrorMSG( '<br />' ), 'error' );
				return false;
			}

			if ( implode( '', $results ) != '' ) {
				$return				=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
									.			'<div>' . implode( '</div><div>', $results ) . '</div>'
									.		'</div>';

				echo $return;
				return false;
			}

			$_CB_framework->enqueueMessage( CBTxt::T( 'PROVIDER_SIGN_UP_INCOMPLETE', 'Your [provider] sign up is incomplete. Please complete the following.', array( '[provider]' => $this->_providerName ) ) );

			HTML_comprofiler::registerForm( 'com_comprofiler', $emailPass, $user, $_POST, $regErrorMSG );
			return false;
		} else {
			$_PLUGINS->trigger( 'onBeforeUserRegistration', array( &$user, &$user ) );

			if ( $user->store() ) {
				if ( $user->get( 'confirmed' ) == 0 ) {
					$user->store();
				}

				$messagesToUser		=	activateUser( $user, 1, 'UserRegistration' );

				$_PLUGINS->trigger( 'onAfterUserRegistration', array( &$user, &$user, true ) );

				if ( $user->get( 'block' ) == 1 ) {
					$return			=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
									.			'<div>' . implode( '</div><div>', $messagesToUser ) . '</div>'
									.		'</div>';

					echo $return;
				} else {
					return true;
				}
			}

			cbRedirect( $this->_returnUrl, CBTxt::T( 'SIGN_UP_WITH_PROVIDER_FAILED', 'Sign up with [provider] failed. Error: [error]', array( '[provider]' => $this->_providerName, '[error]' => $user->getError() ) ), 'error' );
			return false;
		}
	}

	/**
	 * Checks if an email address has been supplied by the provider or if email form needs to render
	 *
	 * @param UserTable           $user
	 * @param Hybrid_User_Profile $profile
	 * @return bool
	 */
	private function email( &$user, $profile )
	{
		global $_CB_framework;

		$email						=	$this->input( 'email', null, GetterInterface::STRING );
		$emailVerify				=	$this->input( 'email__verify', null, GetterInterface::STRING );

		if ( $email ) {
			if ( ! cbIsValidEmail( $email ) ) {
				$_CB_framework->enqueueMessage( sprintf( CBTxt::T( 'UE_EMAIL_NOVALID', 'This is not a valid email address.' ), htmlspecialchars( $email ) ), 'error' );

				$email				=	null;
			} else {
				$field				=	new FieldTable();

				$field->load( array( 'name' => 'email' ) );

				$field->set( 'params', new Registry( $field->get( 'params' ) ) );

				if ( $field->params->get( 'fieldVerifyInput', 0 ) && ( $email != $emailVerify ) ) {
					$_CB_framework->enqueueMessage( CBTxt::T( 'Email and verification do not match, please try again.' ), 'error' );

					$email			=	null;
				}
			}
		}

		if ( ! $email ) {
			$email					=	$profile->email;
		}

		if ( ! $email ) {
			$regAntiSpamValues		=	cbGetRegAntiSpams();

			outputCbTemplate();
			outputCbJs();
			cbValidator::loadValidation();

			$cbUser					=	CBuser::getInstance( null );

			$_CB_framework->enqueueMessage( CBTxt::T( 'PROVIDER_SIGN_UP_INCOMPLETE', 'Your [provider] sign up is incomplete. Please complete the following.', array( '[provider]' => $this->_providerName ) ) );

			$return					=	'<form action="' . $_CB_framework->pluginClassUrl( $this->element, false, array( 'provider' => $this->_provider, 'action' => 'authenticate', 'return' => base64_encode( $this->_returnUrl ) ) ) . '" method="post" enctype="multipart/form-data" name="adminForm" id="cbcheckedadminForm" class="cb_form form-auto cbValidation">'
									.		'<div class="cbRegistrationTitle page-header">'
									.			'<h3>' . CBTxt::T( 'Sign up incomplete' ) . '</h3>'
									.		'</div>'
									.		$cbUser->getField( 'email', null, 'htmledit', 'div', 'register', 0, true, array( 'required' => 1, 'edit' => 1, 'registration' => 1 ) )
									.		'<div class="form-group cb_form_line clearfix">'
									.			'<div class="col-sm-offset-3 col-sm-9">'
									.				'<input type="submit" value="Sign up" class="btn btn-primary cbRegistrationSubmit" data-submit-text="Loading...">'
									.			'</div>'
									.		'</div>'
									.		cbGetSpoofInputTag( 'plugin' )
									.		cbGetRegAntiSpamInputTag( $regAntiSpamValues )
									.	'</form>';

			echo $return;

			return false;
		}

		$user->set( 'email', $email );

		return true;
	}

	/**
	 * Parses profile data for an avatar and uploads it
	 *
	 * @param UserTable           $user
	 * @param Hybrid_User_Profile $profile
	 */
	private function avatar( &$user, $profile )
	{
		global $_CB_framework, $ueConfig;

		if ( $profile->photoURL ) {
			try {
				$field							=	new FieldTable();

				$field->load( array( 'name' => 'avatar' ) );

				$field->set( 'params', new Registry( $field->get( 'params' ) ) );

				$conversionType					=	(int) ( isset( $ueConfig['conversiontype'] ) ? $ueConfig['conversiontype'] : 0 );
				$imageSoftware					=	( $conversionType == 5 ? 'gmagick' : ( $conversionType == 1 ? 'imagick' : 'gd' ) );
				$tmpPath						=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp/';
				$imagePath						=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/';
				$fileName						=	uniqid( (string) $profile->identifier . '_' );
				$resize							=	$field->params->get( 'avatarResizeAlways', '' );

				if ( $resize == '' ) {
					if ( isset( $ueConfig['avatarResizeAlways'] ) ) {
						$resize					=	$ueConfig['avatarResizeAlways'];
					} else {
						$resize					=	1;
					}
				}

				$aspectRatio					=	$field->params->get( 'avatarMaintainRatio', '' );

				if ( $aspectRatio == '' ) {
					if ( isset( $ueConfig['avatarMaintainRatio'] ) ) {
						$aspectRatio			=	$ueConfig['avatarMaintainRatio'];
					} else {
						$aspectRatio			=	1;
					}
				}

				$image							=	new \CBLib\Image\Image( $imageSoftware, $resize, $aspectRatio );

				$avatar							=	$image->getImagine()->open( $profile->photoURL );

				if ( $avatar ) {
					/** @var GuzzleHttp\ClientInterface $client */
					$client						=	new GuzzleHttp\Client();
					$ext						=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $profile->photoURL, PATHINFO_EXTENSION ) ) );

					if ( ( ! $ext ) || ( ! in_array( $ext, array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) ) {
						try {
							/** @var GuzzleHttp\Message\Response $result */
							$result				=	$client->get( $profile->photoURL );

							if ( $result->getStatusCode() == 200 ) {
								$mime			=	$result->getHeader( 'Content-Type' );

								switch ( $mime ) {
									case 'image/jpeg':
										$ext	=	'jpg';
										break;
									case 'image/png':
										$ext	=	'png';
										break;
									case 'image/gif':
										$ext	=	'gif';
										break;
								}
							}
						} catch ( Exception $e ) {}
					}

					if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) {
						return;
					}

					$tmpAvatar					=	$tmpPath . $fileName . '.' . $ext;

					$avatar->save( $tmpAvatar );

					$image->setImage( $avatar );
					$image->setName( $fileName );
					$image->setSource( $tmpAvatar );
					$image->setDestination( $imagePath );

					$width						=	$field->params->get( 'avatarWidth', '' );

					if ( $width == '' ) {
						if ( isset( $ueConfig['avatarWidth'] ) ) {
							$width				=	$ueConfig['avatarWidth'];
						} else {
							$width				=	200;
						}
					}

					$height						=	$field->params->get( 'avatarHeight', '' );

					if ( $height == '' ) {
						if ( isset( $ueConfig['avatarHeight'] ) ) {
							$height				=	$ueConfig['avatarHeight'];
						} else {
							$height				=	500;
						}
					}

					$image->processImage( $width, $height );

					$user->set( 'avatar', $image->getCleanFilename() );

					$image->setName( 'tn' . $fileName );

					$thumbWidth					=	$field->params->get( 'thumbWidth', '' );

					if ( $thumbWidth == '' ) {
						if ( isset( $ueConfig['thumbWidth'] ) ) {
							$thumbWidth			=	$ueConfig['thumbWidth'];
						} else {
							$thumbWidth			=	60;
						}
					}

					$thumbHeight				=	$field->params->get( 'thumbHeight', '' );

					if ( $thumbHeight == '' ) {
						if ( isset( $ueConfig['thumbHeight'] ) ) {
							$thumbHeight		=	$ueConfig['thumbHeight'];
						} else {
							$thumbHeight		=	86;
						}
					}

					$image->processImage( $thumbWidth, $thumbHeight );

					unlink( $tmpAvatar );

					$approval					=	$field->params->get( 'avatarUploadApproval', '' );

					if ( $approval == '' ) {
						if ( isset( $ueConfig['avatarUploadApproval'] ) ) {
							$approval			=	$ueConfig['avatarUploadApproval'];
						} else {
							$approval			=	1;
						}
					}

					$user->set( 'avatarapproved', ( $approval ? 0 : 1 ) );
				}
			} catch ( Exception $e ) {}
		}
	}

	/**
	 * Maps profile fields to the user
	 *
	 * @param UserTable           $user
	 * @param Hybrid_User_Profile $profile
	 */
	private function fields( &$user, $profile )
	{
		foreach ( $this->params->subTree( $this->_provider . '_fields' ) as $field ) {
			/** @var ParamsInterface $field */
			$fromField		=	$field->get( 'from', null, GetterInterface::STRING );
			$toField		=	$field->get( 'to', null, GetterInterface::STRING );

			if ( $fromField && $toField && isset( $profile->$fromField ) ) {
				if ( ( ! is_array( $profile->$fromField ) ) && ( ! is_object( $profile->$fromField ) ) ) {
					$user->set( $toField, $profile->$fromField );
				}
			}
		}
	}

	/**
	 * Logs in a user
	 *
	 * @param UserTable $user
	 */
	private function login( $user )
	{
		$cbAuthenticate			=	new CBAuthentication();
		$messagesToUser			=	array();
		$alertMessages			=	array();
		$redirectUrl			=	null;
		$resultError			=	$cbAuthenticate->login( $user->get( 'username' ), false, 0, 1, $redirectUrl, $messagesToUser, $alertMessages, 1 );

		if ( $resultError || ( count( $messagesToUser ) > 0 ) ) {
			$error				=	null;

			if ( $resultError ) {
				$error			.=	$resultError;
			}

			if ( count( $messagesToUser ) > 0 ) {
				if ( $resultError ) {
					$error		.=	'<br />';
				}

				$error			.=	stripslashes( implode( '<br />', $messagesToUser ) );
			}

			cbRedirect( $this->_returnUrl, CBTxt::T( 'FAILED_TO_LOGIN_PROVIDER_ACCOUNT', 'Failed to login with [provider] account. Error: [error]', array( '[provider]' => $this->_providerName, '[error]' => $error ) ), 'error' );
			return;
		} else {
			$redirect			=	null;

			if ( ( ! $user->get( 'lastvisitDate' ) ) || ( $user->get( 'lastvisitDate' ) == '0000-00-00 00:00:00' ) ) {
				$redirect		=	$this->params->get( $this->_provider . '_firstlogin', true, GetterInterface::STRING );
			}

			if ( ! $redirect ) {
				$redirect		=	$this->params->get( $this->_provider . '_login', true, GetterInterface::STRING );
			}

			if ( ! $redirect ) {
				$redirect		=	$this->_returnUrl;
			}

			$message			=	( count( $alertMessages ) > 0 ? stripslashes( implode( '<br />', $alertMessages ) ) : null );

			cbRedirect( $redirect, $message, 'message' );
		}
	}

	/**
	 * Fires the hybridauth endpoint
	 */
	private function endpoint()
	{
		global $_CB_framework;

		/** @noinspection PhpIncludeInspection */
		require_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/hybridauth/Endpoint.php' );

		Hybrid_Endpoint::process( $this->getInput()->asArray() );
	}
}