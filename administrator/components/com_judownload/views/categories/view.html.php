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


class JUDownloadViewCategories extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		
		$app = JFactory::getApplication();
		if ($app->input->get('layout') == null)
		{
			$app->redirect('index.php?option=com_judownload&view=listcats');
		}

		
		$this->state = $this->get('State');

		
		if ($app->input->get('layout') == 'copy')
		{
			JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_COPY_CATEGORIES'), 'copy-categories');
			JToolBarHelper::apply('categories.copyCats', 'JTOOLBAR_APPLY');
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
		}
		elseif ($app->input->get('layout') == 'move')
		{
			JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_MOVE_CATEGORIES'), 'move-categories');
			JToolBarHelper::apply('categories.moveCats', 'JTOOLBAR_APPLY');
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
		}

		
		parent::display($tpl);
	}
}
