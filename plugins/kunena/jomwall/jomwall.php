<?php
/**
 * Kunena Plugin
 * @package Kunena.Plugins
 * @subpackage Jomwall
 *
 * @Copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class plgKunenaJomwall extends JPlugin {
	public function __construct(&$subject, $config) {
		// Do not load if Kunena version is not supported or Kunena is offline
		if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('2.0') && KunenaForum::installed())) return;

		// Do not load if jomwall is not installed
		$path = JPATH_ROOT . '/components/com_awdwall/models/wall.php';
		if (!is_file ( $path )) return;
		include_once ($path);
		include_once (JPATH_ROOT . '/components/com_awdwall/helpers/user.php');

		parent::__construct ( $subject, $config );

		$this->loadLanguage ( 'plg_kunena_jomwall.sys', JPATH_ADMINISTRATOR ) || $this->loadLanguage ( 'plg_kunena_jomwall.sys', KPATH_ADMIN );

		$this->path = dirname ( __FILE__ ) . '/jomwall';
	}


	/*
	 * Get Kunena avatar integration object.
	 *
	 * @return KunenaAvatar
	 */
	public function onKunenaGetAvatar() {
		if (!$this->params->get('avatar', 1)) return;

		require_once "{$this->path}/avatar.php";
		return new KunenaAvatarJomwall($this->params);
	}


	/*
	 * Get Kunena activity stream integration object.
	 *
	 * @return KunenaActivity
	 */
	public function onKunenaGetActivity() {
		if (!$this->params->get('activity', 1)) return;

		require_once "{$this->path}/activity.php";
		return new KunenaActivityJomwall($this->params);
	}
}
