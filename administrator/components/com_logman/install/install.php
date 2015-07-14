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
	global $installer, $type, $component;

	$installer = $this->parent;
	$type = $created ? 'install' : 'update';
	$manifest = simplexml_load_file($installer->getPath('manifest'));
	$component = str_replace('com:', '', (string)$manifest->identifier);

	function com_install()
	{
		global $installer, $type, $component;

		$class = 'com_'.$component.'InstallerScript';

		$script = new $class($installer);
		$events = array('preflight', $type, 'postflight');
		foreach ($events as $event)
		{
			if (method_exists($script, $event))
			{
				if ($event === $type) {
					$result = $script->$event($installer);
				} else {
					$result = $script->$event($type, $installer);
				}

				if ($result === false) {
					return $result;
				}
			}
		}

		return true;
	}
}