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


class JUDownloadViewStyle extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		
		$this->form = $this->get('Form');

		$this->item = $this->get('Item');

		
		$this->addToolBar();

		
		$this->setDocument();

		
		parent::display($tpl);
	}

	
	protected function addToolBar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$isNew      = $this->item->id == 0;
		$canDo      = JUDownloadHelper::getActions('com_judownload');
		$user       = JFactory::getUser();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_TEMPLATE_STYLE' : ($isNew ? 'ADD_TEMPLATE_STYLE' : 'EDIT_TEMPLATE_STYLE'))), 'style-add');

		
		if ($isNew)
		{
			
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('style.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('style.save2new', 'JTOOLBAR_SAVE_AND_NEW');
				JToolBarHelper::save('style.save', 'JTOOLBAR_SAVE');
			}
			JToolBarHelper::cancel('style.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				
				JToolBarHelper::apply('style.apply', 'JTOOLBAR_APPLY');
				if ($canDo->get('core.create'))
				{
					JToolBarHelper::save('style.save2new', 'JTOOLBAR_SAVE_AND_NEW');
				}
				JToolBarHelper::save('style.save', 'JTOOLBAR_SAVE');
			}
			JToolBarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$isNew      = ($this->item->id == 0);
		$userId     = JFactory::getUser()->id;
		$document   = JFactory::getDocument();
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_TEMPLATE_STYLE' : ($isNew ? 'ADD_TEMPLATE_STYLE' : 'EDIT_TEMPLATE_STYLE'))));
	}
}
