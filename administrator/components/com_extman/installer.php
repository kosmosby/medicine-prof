<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die;

jimport('joomla.installer.installer');

class ComExtmanInstaller extends JInstaller
{
	protected $_installer_error = '';

	public function getInstallerError()
	{
		return $this->_installer_error;
	}

	public function abort($msg=null, $type=null)
	{
		$this->_installer_error = $msg;

		return parent::abort(null, $type);
	}
}