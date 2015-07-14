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

class JUDownloadControllerHelp extends JControllerAdmin
{
	public function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();
		$app->input->set('tmpl', 'component');
		$settings = $app->input->get('settings', '', 'string');
		
		$settings = unserialize(base64_decode($settings));
		$view     = $this->getView('help', 'html');
		$view->assignRef('settings', $settings);
		$view->display();
	}
}