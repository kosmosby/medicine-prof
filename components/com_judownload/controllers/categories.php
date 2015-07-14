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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

class JUDownloadControllerCategories extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_CATEGORIES';

	
	public function loadCategories()
	{
		
		require_once JPATH_ADMINISTRATOR . '/components/com_judownload/models/category.php';
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/models');
		$backendCategoryModel = JModelLegacy::getInstance('Category', 'JUDownloadModel');
		$data                 = $backendCategoryModel->loadCategories();
		JUDownloadHelper::obCleanData();
		echo $data;
		exit();
	}

	
	public function docChangeCategory()
	{
		
		require_once JPATH_ADMINISTRATOR . '/components/com_judownload/models/category.php';
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/models');
		$backendCategoryModel = JModelLegacy::getInstance('Category', 'JUDownloadModel');
		$data                 = $backendCategoryModel->docChangeCategory();
		JUDownloadHelper::obCleanData();
		echo $data;
		exit();
	}
}
