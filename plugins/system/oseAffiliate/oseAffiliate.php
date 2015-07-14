<?php
/**
 * @version		$Id: example.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');
/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgSystemOseAffiliate extends JPlugin {
	var $_db= null;
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgSystemOserouter(& $subject, $config) {
		parent :: __construct($subject, $config);
	}
	function onAfterInitialise()
	{
		$params = $this->params;
		$app = JFactory::getApplication();

		if($app->isAdmin())
		{
			return true;
		}

		if (file_exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php') && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php') && !file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'installer.dummy.ini'))
		{
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		}
		else
		{
			return false;
		}

		if($params->get('pap4_click_track_api'))
		{
			require_once(OSEMSC_B_LIB.DS.'PapApi.class.php');
			$oseMscConfig = oseRegistry::call('msc')->getConfig('thirdparty','obj');

			// init session for PAP
			$session = new Gpf_Api_Session($oseMscConfig->pap_url."/scripts/server.php");

			// register click
			$clickTracker = new Pap_Api_ClickTracker($session);
			$clickTracker->setAccountId(oseObject::getValue($oseMscConfig,'pap_account_id','default1'));
			try {
				$clickTracker->track();
				$clickTracker->saveCookies();//oseExit($_COOKIE);
			} catch (Exception $e) {

			}
		}
	}

	function onAfterRoute()
	{
		return true;
	}

	function onAfterRender()
	{
		return true;
	}
}
?>