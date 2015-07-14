<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die;

if (version_compare(JVERSION, '1.6', '<'))
{
	require_once dirname(__FILE__).'/script.php';

	// We need to carry these into com_install
	global $installer, $component;

	$installer = $this->parent;
	$component = str_replace('com:', '', (string)$this->manifest->getElementByPath('identifier')->data());

	function com_uninstall()
	{
		global $installer, $component;

		$class = 'com_'.$component.'InstallerScript';
		$script = new $class($installer);

		if (method_exists($script, 'uninstall')) {
			return $script->uninstall($installer);
		}

		return true;
	}
}