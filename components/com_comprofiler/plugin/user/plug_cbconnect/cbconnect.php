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
use CB\Database\Table\FieldTable;
use CBLib\Language\CBTxt;
use CBLib\Application\Application;
use CBLib\Input\InputInterface;
use CBLib\Registry\Registry;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onAfterLoginForm', 'getButtons', 'cbconnectPlugin' );
$_PLUGINS->registerFunction( 'onAfterLogoutForm', 'getButtons', 'cbconnectPlugin' );
$_PLUGINS->registerUserFieldParams();
$_PLUGINS->registerUserFieldTypes( array( 'socialid' => 'cbconnectField' ) );

class cbconnectHybrid
{
	/** @var array  */
	private $providers		=	null;
	/** @var InputInterface  */
	private $input			=	null;
	/** @var cbPluginHandler  */
	private $plugin			=	null;
	/** @var Registry  */
	private $params			=	null;
	/** @var Hybrid_Auth  */
	private $hybridAuth		=	null;

	public function __construct()
	{
		global $_PLUGINS, $_CB_framework;

		if ( ! $this->input ) {
			$this->input				=	$_PLUGINS->getInput();
		}

		if ( ! $this->plugin ) {
			$this->plugin				=	$_PLUGINS->getLoadedPlugin( 'user', 'cbconnect' );
		}

		if ( ! $this->params ) {
			$this->params				=	$_PLUGINS->getPluginParams( $this->plugin );
		}

		if ( ! $this->providers ) {
			$this->providers			=	array(	'facebook'		=>	array(	'id' => 'Facebook',
																				'field' => 'fb_userid',
																				'icon' => 'facebook',
																				'button' => 'primary',
																				'name' => CBTxt::T( 'Facebook' ),
																				'profile' => 'https://www.facebook.com/profile.php?id={identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Facebook'
																			),
													'twitter'		=>	array(	'id' => 'Twitter',
																				'field' => 'twitter_userid',
																				'icon' => 'twitter',
																				'button' => 'default',
																				'name' => CBTxt::T( 'Twitter' ),
																				'profile' => 'https://twitter.com/intent/user?user_id={identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Twitter'
																			),
													'linkedin'		=>	array(	'id' => 'LinkedIn',
																				'field' => 'linkedin_userid',
																				'icon' => 'linkedin',
																				'button' => 'info',
																				'name' => CBTxt::T( 'LinkedIn' ),
																				'profile' => 'https://www.linkedin.com/profile/view?id={identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=LinkedIn'
																			),
													'windowslive'	=>	array(	'id' => 'Live',
																				'field' => 'windowslive_userid',
																				'icon' => 'windows',
																				'button' => 'default',
																				'name' => CBTxt::T( 'Windows Live' ),
																				'profile' => 'https://profile.live.com/{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php'
																			),
													'google'		=>	array(	'id' => 'Google',
																				'field' => 'google_userid',
																				'icon' => 'google-plus',
																				'button' => 'danger',
																				'name' => CBTxt::T( 'Google' ),
																				'profile' => 'https://plus.google.com/{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Google'
																			),
													'instagram'		=>	array(	'id' => 'Instagram',
																				'field' => 'instagram_userid',
																				'icon' => 'instagram',
																				'button' => 'warning',
																				'name' => CBTxt::T( 'Instagram' ),
																				'profile' => 'http://instagram.com/{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Instagram'
																			),
													'foursquare'	=>	array(	'id' => 'Foursquare',
																				'field' => 'foursquare_userid',
																				'icon' => 'foursquare',
																				'button' => 'info',
																				'name' => CBTxt::T( 'Foursquare' ),
																				'profile' => 'https://foursquare.com/user/{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Foursquare'
																			),
													'github'		=>	array(	'id' => 'GitHub',
																				'field' => 'github_userid',
																				'icon' => 'github',
																				'button' => 'default',
																				'name' => CBTxt::T( 'GitHub' ),
																				'profile' => null,
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=GitHub'
																			),
													'vkontakte'		=>	array(	'id' => 'Vkontakte',
																				'field' => 'vkontakte_userid',
																				'icon' => 'vk',
																				'button' => 'primary',
																				'name' => CBTxt::T( 'VKontakte' ),
																				'profile' => 'http://vk.com/id{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Vkontakte'
																			),
													'steam'			=>	array(	'id' => 'Steam',
																				'field' => 'steam_userid',
																				'icon' => 'steam',
																				'button' => 'success',
																				'name' => CBTxt::T( 'Steam' ),
																				'profile' => 'http://steamcommunity.com/profiles/{identifier}',
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Steam'
																			),
													'tumblr'		=>	array(	'id' => 'Tumblr',
																				'field' => 'tumblr_userid',
																				'icon' => 'tumblr',
																				'button' => 'primary',
																				'name' => CBTxt::T( 'Tumblr' ),
																				'profile' => null,
																				'callback' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php?hauth.done=Tumblr'
																			)
												);
		}

		if ( ! $this->hybridAuth ) {
			if ( ! class_exists( 'Hybrid_Auth' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/hybridauth/Auth.php' );
			}

			$facebookPerms				=	array( 'email', 'offline_access' );

			if ( $this->params->get( 'facebook_permissions', null, GetterInterface::STRING ) ) {
				$facebookPerms			=	array_filter( array_merge( $facebookPerms, explode( '|*|', $this->params->get( 'facebook_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$livePerms					=	array( 'wl.signin', 'wl.basic', 'wl.emails' );

			if ( $this->params->get( 'windowslive_permissions', null, GetterInterface::STRING ) ) {
				$livePerms				=	array_filter( array_merge( $livePerms, explode( '|*|', $this->params->get( 'windowslive_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$googlePerms				=	array( 'https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/plus.profile.emails.read' );

			if ( $this->params->get( 'google_permissions', null, GetterInterface::STRING ) ) {
				$googlePerms			=	array_filter( array_merge( $googlePerms, explode( '|*|', $this->params->get( 'google_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$instagramScope				=	array( 'basic' );

			if ( $this->params->get( 'instagram_permissions', null, GetterInterface::STRING ) ) {
				$instagramScope			=	array_filter( array_merge( $instagramScope, explode( '|*|', $this->params->get( 'instagram_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$githubScope				=	array();

			if ( $this->params->get( 'github_permissions', null, GetterInterface::STRING ) ) {
				$githubScope			=	array_filter( array_merge( $githubScope, explode( '|*|', $this->params->get( 'github_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$vkontakteScope				=	array( 'email', 'offline' );

			if ( $this->params->get( 'vkontakte_permissions', null, GetterInterface::STRING ) ) {
				$vkontakteScope			=	array_filter( array_merge( $githubScope, explode( '|*|', $this->params->get( 'vkontakte_permissions', null, GetterInterface::STRING ) ) ) );
			}

			$config						=	array(	'base_url' => $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/endpoint.php',
													'providers' => array(
																			'Facebook' => array(
																									'enabled' => $this->params->get( 'facebook_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'facebook_application_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'facebook_application_secret', null, GetterInterface::STRING )
																									),
																									'scope' => implode( ', ', $facebookPerms ),
																									'trustForwarded' => true,
																									'redirect_uri' => $this->getProviderCallbackURL( 'facebook' )
																			),
																			'Twitter' => array(
																									'enabled' => $this->params->get( 'twitter_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'key' => $this->params->get( 'twitter_consumer_key', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'twitter_consumer_secret', null, GetterInterface::STRING )
																									),
																									'redirect_uri' => $this->getProviderCallbackURL( 'twitter' )
																			),
																			'LinkedIn' => array(
																									'enabled' => $this->params->get( 'linkedin_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'key' => $this->params->get( 'linkedin_api_key', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'linkedin_secret_key', null, GetterInterface::STRING )
																									),
																									'redirect_uri' => $this->getProviderCallbackURL( 'linkedin' )
																			),
																			'Live' => array(
																									'enabled' => $this->params->get( 'windowslive_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'windowslive_client_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'windowslive_client_secret', null, GetterInterface::STRING )
																									),
																									'scope' => implode( ' ', $livePerms ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'windowslive' )
																			),
																			'Google' => array(
																									'enabled' => $this->params->get( 'google_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'google_client_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'google_client_secret', null, GetterInterface::STRING )
																									),
																									'scope' => implode( ' ', $googlePerms ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'google' )
																			),
																			'Instagram' => array(
																									'enabled' => $this->params->get( 'instagram_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'instagram_client_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'instagram_client_secret', null, GetterInterface::STRING )
																									),
																									'scope' => implode( '+', $instagramScope ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'instagram' )
																			),
																			'Foursquare' => array(
																									'enabled' => $this->params->get( 'foursquare_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'foursquare_client_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'foursquare_client_secret', null, GetterInterface::STRING )
																									),
																									'redirect_uri' => $this->getProviderCallbackURL( 'foursquare' )
																			),
																			'GitHub' => array(
																									'enabled' => $this->params->get( 'github_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'github_client_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'github_client_secret', null, GetterInterface::STRING )
																									),
																									'scope' => implode( ',', $githubScope ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'github' )
																			),
																			'Vkontakte' => array(
																									'enabled' => $this->params->get( 'vkontakte_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'id' => $this->params->get( 'vkontakte_application_id', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'vkontakte_secret_key', null, GetterInterface::STRING )
																									),
																									'scope' => implode( ',', $vkontakteScope ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'vkontakte' )
																			),
																			'Steam' => array(
																									'enabled' => $this->params->get( 'steam_enabled', false, GetterInterface::BOOLEAN ),
																									'redirect_uri' => $this->getProviderCallbackURL( 'steam' )
																			),
																			'Tumblr' => array(
																									'enabled' => $this->params->get( 'tumblr_enabled', false, GetterInterface::BOOLEAN ),
																									'keys' => array(
																														'key' => $this->params->get( 'tumblr_consumer_key', null, GetterInterface::STRING ),
																														'secret' => $this->params->get( 'tumblr_consumer_secret', null, GetterInterface::STRING )
																									),
																									'redirect_uri' => $this->getProviderCallbackURL( 'tumblr' )
																			)
													)
												);

			$this->hybridAuth			=	new Hybrid_Auth( $config );
		}
	}

	/**
	 * Returns the Hybrid_Auth object for authenticating social site connections
	 *
	 * @return Hybrid_Auth
	 */
	public function getHybridAuth()
	{
		return $this->hybridAuth;
	}

	/**
	 * Returns an array of supported providers
	 *
	 * @return array
	 */
	public function getProviders()
	{
		return $this->providers;
	}

	/**
	 * Returns params for CB Connect
	 *
	 * @return Registry
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Returns a providers adapter for making provider api calls
	 *
	 * @param $providerId
	 * @return Hybrid_Provider_Adapter|null
	 */
	public function getAdapter( $providerId )
	{
		$adapters					=	array();

		if ( ! isset( $adapters[$providerId] ) ) {
			$hybridAuth				=	$this->hybridAuth;
			$adapter				=	null;

			if ( $hybridAuth ) {
				$adapter			=	$hybridAuth->getAdapter( $providerId );
			}

			$adapters[$providerId]	=	$adapter;
		}

		return $adapters[$providerId];
	}

	/**
	 * Returns the CB Connect provider from providers field name
	 *
	 * @param string $fieldName
	 * @return null|string
	 */
	public function getProviderFromField( $fieldName )
	{
		$provider				=	null;

		if ( $fieldName ) {
			foreach ( $this->providers as $key => $value ) {
				if ( $value['field'] == $fieldName ) {
					$provider	=	$key;
					break;
				}
			}
		}

		return $provider;
	}

	/**
	 * Returns the CB Connect provider from HybridAuth provider id
	 *
	 * @param string $providerId
	 * @return null|string
	 */
	public function getProviderFromId( $providerId )
	{
		$provider				=	null;

		if ( $providerId ) {
			foreach ( $this->providers as $key => $value ) {
				if ( $value['id'] == $providerId ) {
					$provider	=	$key;
					break;
				}
			}
		}

		return $provider;
	}

	/**
	 * Returns the HybridAuth provider id from CB Connect provider
	 *
	 * @param string $provider
	 * @return null|string
	 */
	public function getIdFromProvider( $provider )
	{
		$providerId			=	null;

		if ( $provider && isset( $this->providers[$provider] ) ) {
			$providerId		=	$this->providers[$provider]['id'];
		}

		return $providerId;
	}

	/**
	 * Returns the CB Connect field for the CB Connect provider
	 *
	 * @param string $provider
	 * @return null|string
	 */
	public function getProviderField( $provider )
	{
		$fieldName			=	null;

		if ( $provider && isset( $this->providers[$provider] ) ) {
			$fieldName		=	$this->providers[$provider]['field'];
		}

		return $fieldName;
	}

	/**
	 * Returns the translated provider site name
	 *
	 * @param string $provider
	 * @return null|string
	 */
	public function getProviderName( $provider )
	{
		$name		=	null;

		if ( $provider && isset( $this->providers[$provider] ) ) {
			$name	=	$this->providers[$provider]['name'];
		}

		return $name;
	}

	/**
	 * Returns the provider profile URL for the given id
	 *
	 * @param string $provider
	 * @param mixed  $id
	 * @return null|string
	 */
	public function getProviderProfileURL( $provider, $id )
	{
		$url						=	null;

		switch( $provider ) {
			case 'instagram':
				$id					=	null;

				try {
					$adapter		=	$this->getAdapter( $this->getIdFromProvider( $provider ) );

					if ( $adapter ) {
						/** @noinspection PhpUndefinedMethodInspection */
						$data		=	$adapter->api()->api( 'users/' . $id . '/' );

						if ( isset( $data->data->username ) ) {
							$id		=	$data->data->username;
						}
					}
				} catch ( Exception $e ) {}
				break;
		}

		if ( $id && $provider && isset( $this->providers[$provider] ) ) {
			$url					=	str_replace( '{identifier}', $id, $this->providers[$provider]['profile'] );
		}

		return $url;
	}

	/**
	 * Returns the provider callback url
	 *
	 * @param string $provider
	 * @return null|string
	 */
	public function getProviderCallbackURL( $provider )
	{
		$url		=	null;

		if ( $provider && isset( $this->providers[$provider] ) ) {
			$url	=	$this->providers[$provider]['callback'];
		}

		return $url;
	}

	/**
	 * Returns a provider button
	 *
	 * @param string $provider
	 * @param int    $horizontal
	 * @return null|string
	 */
	public function getButton( $provider, $horizontal = 1 )
	{
		global $_CB_framework;

		if ( ! ( $provider && isset( $this->providers[$provider] ) ) ) {
			return null;
		}

		$fieldName					=	$this->providers[$provider]['field'];
		$siteName					=	$this->providers[$provider]['name'];
		$iconClass					=	$this->providers[$provider]['icon'];
		$buttonClass				=	$this->providers[$provider]['button'];
		$user						=	CBuser::getMyUserDataInstance();
		$style						=	(int) $this->params->get( $provider . '_button_style', 2, GetterInterface::INT );

		if ( $style == 1 ) {
			$horizontal				=	1;
		}

		static $returnUrl			=	null;

		if ( ! isset( $returnUrl ) ) {
			$returnUrl				=	$this->input->get( 'return', null, GetterInterface::BASE64 );

			if ( $returnUrl ) {
				$returnUrl			=	base64_decode( $returnUrl );
			} else {
				$isHttps			=	( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );
				$returnUrl			=	'http' . ( $isHttps ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];

				if ( ( ! empty( $_SERVER['PHP_SELF'] ) ) && ( ! empty( $_SERVER['REQUEST_URI'] ) ) ) {
					$returnUrl		.=	$_SERVER['REQUEST_URI'];
				} else {
					$returnUrl		.=	$_SERVER['SCRIPT_NAME'];

					if ( isset( $_SERVER['QUERY_STRING'] ) && ( ! empty( $_SERVER['QUERY_STRING'] ) ) ) {
						$returnUrl	.=	'?' . $_SERVER['QUERY_STRING'];
					}
				}
			}

			$returnUrl				=	cbUnHtmlspecialchars( preg_replace( '/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', preg_replace( '/eval\((.*)\)/', '', htmlspecialchars( urldecode( $returnUrl ) ) ) ) );

			if ( preg_match( '/index\.php\?option=com_comprofiler&view=login|index\.php\?option=com_comprofiler&view=pluginclass&plugin=cbconnect/', $returnUrl ) ) {
				$returnUrl			=	'index.php';
			}

			$returnUrl				=	base64_encode( $returnUrl );
		}

		$return						=	null;

		if ( $this->params->get( $provider . '_enabled', false, GetterInterface::BOOLEAN ) ) {
			if ( $user->get( 'id' ) ) {
				if ( $this->params->get( $provider . '_link', true, GetterInterface::BOOLEAN ) && ( ! $user->get( $fieldName ) ) ) {
					$link			=	$this->params->get( $provider . '_button_link', null, GetterInterface::STRING );

					$return			=	'<button class="cbConnectButton cbConnectButton' . ucfirst( $provider ) . ' btn btn-' . $buttonClass . ' btn-sm' . ( ! $horizontal ? ' btn-block' : null ) . '" onclick="window.location=\'' . $_CB_framework->pluginClassUrl( $this->plugin->element, false, array( 'provider' => $provider, 'action' => 'authenticate', 'return' => $returnUrl ) ) . '\'; return false;" title="' . htmlspecialchars( CBTxt::T( 'LINK_YOUR_SITENAME_ACCOUNT', 'Link your [sitename] account', array( '[sitename]' => $siteName ) ) ) . '">'
									.		( in_array( $style, array( 1, 2 ) ) ? '<span class="fa fa-' . $iconClass . ' fa-lg' . ( $style != 1 ? ' cbConnectButtonPrefix' : null ) . '"></span>' : null )
									.		( in_array( $style, array( 2, 3 ) ) ? ( $link ? $link : CBTxt::T( 'LINK_WITH_SITENAME', 'Link with [sitename]', array( '[sitename]' => $siteName ) ) ) : null )
									.	'</button>'
									.	( $horizontal ? ' ' : null );
				}
			} else {
				$signin				=	$this->params->get( $provider . '_button_signin', null, GetterInterface::STRING );

				$return				=	'<button class="cbConnectButton cbConnectButton' . ucfirst( $provider ) . ' btn btn-' . $buttonClass . ' btn-sm' . ( ! $horizontal ? ' btn-block' : null ) . '" onclick="window.location=\'' . $_CB_framework->pluginClassUrl( $this->plugin->element, false, array( 'provider' => $provider, 'action' => 'authenticate', 'return' => $returnUrl ) ) . '\'; return false;" title="' . htmlspecialchars( CBTxt::T( 'LOGIN_WITH_YOUR_SITENAME_ACCOUNT', 'Login with your [sitename] account', array( '[sitename]' => $siteName ) ) ) . '">'
									.		( in_array( $style, array( 1, 2 ) ) ? '<span class="fa fa-' . $iconClass . ' fa-lg' . ( $style != 1 ? ' cbConnectButtonPrefix' : null ) . '"></span>' : null )
									.		( in_array( $style, array( 2, 3 ) ) ? ( $signin ? $signin : CBTxt::T( 'SIGN_IN_WITH_SITENAME', 'Sign in with [sitename]', array( '[sitename]' => $siteName ) ) ) : null )
									.	'</button>'
									.	( $horizontal ? ' ' : null );
			}
		}

		return $return;
	}
}

class cbconnectPlugin extends cbPluginHandler
{

	/**
	 * Outputs the provider buttons to the login/logout form
	 *
	 * @param int       $nameLenght
	 * @param int       $passLenght
	 * @param int       $horizontal
	 * @param string    $classSfx
	 * @param JRegistry $params
	 * @return array|null|string
	 */
	public function getButtons( /** @noinspection PhpUnusedParameterInspection */ $nameLenght, /** @noinspection PhpUnusedParameterInspection */ $passLenght, $horizontal, /** @noinspection PhpUnusedParameterInspection */ $classSfx, /** @noinspection PhpUnusedParameterInspection */ $params )
	{
		global $_CB_framework;

		static $CSS		=	0;

		if ( ! $CSS++ ) {
			$_CB_framework->document->addHeadStyleSheet( $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbconnect/cbconnect.css' );
		}

		$hybrid			=	new cbconnectHybrid();
		$return			=	null;

		foreach ( array_keys( $hybrid->getProviders() ) as $provider ) {
			$return		.=	$hybrid->getButton( $provider, $horizontal );
		}

		if ( $return ) {
			$return		=	'<div class="cbConnectButtons cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
						.		$return
						.	'</div>';

			return array( 'afterButton' => $return );
		}

		return null;
	}
}

class cbconnectField extends cbFieldHandler
{

	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types )
	{
		$hybrid						=	new cbconnectHybrid();
		$fieldName					=	$field->get( 'name' );
		$provider					=	$hybrid->getProviderFromField( $fieldName );
		$providerName				=	$hybrid->getProviderName( $provider );
		$value						=	$user->get( $fieldName );
		$return						=	null;

		switch( $output ) {
			case 'htmledit':
				if ( $reason == 'search' ) {
					$return			=	$this->_fieldSearchModeHtml( $field, $user, $this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $value, null ), 'text', $list_compare_types );
				} else {
					if ( Application::Cms()->getClientId() ) {
						$return		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $value, null );
					} elseif ( $value && ( $user->get( 'id' ) == Application::MyUser()->get( 'id' ) ) ) {
						$values		=	array();
						$values[]	=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'UNLINK_PROVIDER_ACCOUNT', 'Unlink your [provider] account', array( '[provider]' => $providerName ) ) );

						$return		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'multicheckbox', null, null, $values );
					} elseif ( $value && ( ! Application::MyUser()->get( 'id' ) ) ) {
						$url		=	$hybrid->getProviderProfileURL( $provider, $value );

						if ( $url ) {
							$url	=	'<a href="' . $url . '" target="_blank">'
									.		CBTxt::T( 'PROVIDER_PROFILE', '[provider] profile', array( '[provider]' => $providerName ) )
									.	'</a>';
						}

						if ( ! $url ) {
							$url	=	CBTxt::T( 'PROVIDER_PROFILE_ID', '[provider] profile id [provider_id]', array( '[provider]' => $providerName, '[provider_id]' => $value ) );
						}

						$return		=	CBTxt::T( 'PROVIDER_PROFILE_LINKED_TO_ACCOUNT', 'Your [provider_profile] will be linked to this account.', array( '[provider]' => $providerName, '[provider_profile]' => $url, '[provider_id]' => $value ) )
									.	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'hidden', $value, null );
					}
				}
				break;
			case 'html':
			case 'rss':
				if ( $value ) {
					$url			=	$hybrid->getProviderProfileURL( $provider, $value );

					if ( $url ) {
						$value		=	'<a href="' . $url . '" target="_blank">'
									.		CBTxt::T( 'VIEW_PROVIDER_PROFILE', 'View [provider] Profile', array( '[provider]' => $providerName ) )
									.	'</a>';
					}
				}

				$return				=	$this->formatFieldValueLayout( $this->_formatFieldOutput( $field->get( 'name' ), $value, $output, false ), $reason, $field, $user, false );
				break;
			default:
				$return				=	$this->_formatFieldOutput( $field->get( 'name' ), $value, $output );
				break;
		}

		return $return;
	}

	/**
	 * Mutator:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
		$hybrid								=	new cbconnectHybrid();
		$fieldName							=	$field->get( 'name' );
		$provider							=	$hybrid->getProviderFromField( $fieldName );
		$providerId							=	$hybrid->getIdFromProvider( $provider );
		$currentValue						=	$user->get( $fieldName );
		$value								=	cbGetParam( $postdata, $fieldName );

		if ( $currentValue && ( $user->get( 'id' ) == Application::MyUser()->get( 'id' ) ) ) {
			if ( is_array( $value ) ) {
				if ( isset( $value[0] ) && ( $value[0] == 1 ) ) {
					$postdata[$fieldName]	=	'';
				}
			}

			$value							=	cbGetParam( $postdata, $fieldName );

			if ( $value === '' ) {
				try {
					$adapter				=	$hybrid->getAdapter( $providerId );

					if ( $adapter ) {
						switch( $provider ) {
							case 'facebook':
								/** @noinspection PhpUndefinedMethodInspection */
								$adapter->api()->api( '/me/permissions', 'DELETE' );
								break;
						}

						$adapter->logout();
					}
				} catch ( Exception $e ) {}
			}
		}

		if ( ( ! Application::Cms()->getClientId() ) && $user->get( 'id' ) && $currentValue && ( $value !== '' ) ) {
			$postdata[$fieldName]			=	$currentValue;
		}

		parent::prepareFieldDataSave( $field, $user, $postdata, $reason );
	}
}