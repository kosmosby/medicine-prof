<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

defined('_JEXEC') or die('Restricted access');
function genRandomFilename($directory, $filename = '' , $extension = '', $length = 11)
{
	if (strlen($directory) < 1)
		return false;

	$directory = JPath::clean($directory);
	
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');

	if (!JFile::exists($directory)){
		JFolder::create( $directory);
		JPath::setPermissions($directory, '0777');
	}

	if (strlen($filename) > 0)
		$filename	= JFile::makeSafe($filename);

	if (!strlen($extension) > 0)
		$extension	= '';

	$dotExtension 	= $filename ? JFile::getExt($filename) : $extension;
	$dotExtension 	= $dotExtension ? '.' . $dotExtension : '';

	$map			= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$len 			= strlen($map);
	$stat			= stat(__FILE__);
	$randFilename	= '';

	if(empty($stat) || !is_array($stat))
		$stat = array(php_uname());

	mt_srand(crc32(microtime() . implode('|', $stat)));
	for ($i = 0; $i < $length; $i ++) {
		$randFilename .= $map[mt_rand(0, $len -1)];
	}

	$randFilename .= $dotExtension;

	if (JFile::exists($directory . DS . $randFilename)) {
		genRandomFilename($directory, $filename, $extension, $length);
	}

	return $randFilename;
}