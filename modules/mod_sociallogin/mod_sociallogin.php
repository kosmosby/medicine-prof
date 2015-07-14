<?php


/**
 * @version
 * @package
 * @subpackage
 * @copyright
 * @license
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$params->def('greeting', 1);

$document = &JFactory::getDocument();


$settings = modSocialLoginHelper::oa_social_login_render_login_form ('login');

$document->addScript( 'http://'.$settings['api_subdomain'].'.api.oneall.com/socialize/library.js?ver=3.3' );


//$document->addScript('administrator/components/com_sociallogin/js/jquery.js');

$type	= modSocialLoginHelper::getType();
$return	= modSocialLoginHelper::getReturnURL($params, $type);

$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_sociallogin', $params->get('layout', 'default'));
