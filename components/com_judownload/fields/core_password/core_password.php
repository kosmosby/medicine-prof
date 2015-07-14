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

class JUDownloadFieldCore_password extends JUDownloadFieldText
{
	protected $field_name = 'download_password';

	
	public function getInput($fieldValue = null)
	{
		$app = JFactory::getApplication();
		if (($app->isSite() && ($this->params->get('hidden_password', 0) == 'frontend' || $this->params->get('hidden_password', 0) == 'both'))
			|| ($app->isAdmin() && ($this->params->get('hidden_password', 0) == 'backend' || $this->params->get('hidden_password', 0) == 'both'))
		)
		{
			$this->setAttribute("type", "password", "input");
		}

		return parent::getInput($fieldValue);
	}
}

?>