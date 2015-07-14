<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */


// No direct access to this file
defined('_JEXEC') or die ('Restricted access');

class plgExtensionJUDownloadInstallerScript
{
	public function postflight($type, $parent)
	{
		// Active plugin after installing
		$db = JFactory::getDbo();
		$query= $db->getQuery(true);
		$query->update('#__extensions')
			->set('enabled = 1')
			->where('type = "plugin"')
			->where('folder = "extension"')
			->where('element = "judownload"');
		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
