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


jimport('joomla.application.component.controllerform');


class JUDownloadControllerPlugin extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_PLUGIN';

	public function add()
	{
		$model = $this->getModel();
		$model->checkJUDownloadExtensionPlugin();

		$app = JFactory::getApplication();
		$app->redirect("index.php?option=com_judownload&view=plugin&layout=install");

		return true;
	}

	
	public function install()
	{
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php';

		$lang = JFactory::getLanguage();
		$lang->load('com_installer');

		$model = new InstallerModelInstall();

		if ($model->install())
		{
			$cache = JFactory::getCache('mod_menu');
			$cache->clean();
			
		}

		$app          = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_installer.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_judownload&view=plugins', false);
		}
		else
		{
			
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}
}
