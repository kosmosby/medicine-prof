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


jimport('joomla.application.component.controller');


class JUDownloadController extends JControllerLegacy
{

	
	public function display($cachable = false, $urlparams = false)
	{
		
		$app = JFactory::getApplication();
		$app->input->set('view', $app->input->get('view', 'dashboard'));

		$view   = $app->input->get('view', 'dashboard');
		$layout = $app->input->get('layout', 'default');
		$id     = $app->input->getInt('id', 0);

		switch ($view)
		{
			case 'category':
			case 'document':
				$redirect = 'listcats';
				break;
			case 'dashboard':
				$redirect = 'dashboard';
				break;
			default:
				$redirect = $view . 's';
				break;
		}
		
		if ($view && $layout == 'edit' && !$this->checkEditId('com_judownload.edit.' . $view, $id))
		{
			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_judownload&view=' . $redirect, false));

			return false;
		}

		parent::display($cachable, $urlparams);

		return $this;
	}
}
