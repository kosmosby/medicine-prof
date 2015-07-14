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


class JUDownloadViewComment extends JUDLViewAdmin
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

		
		parent::display($tpl);

		
		$this->setDocument();
	}

	
	protected function addToolBar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);
		$user       = JFactory::getUser();
		$isNew      = $this->item->id == 0;
		$canDo      = JUDownloadHelper::getActions('com_judownload');
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_COMMENT' : ($isNew ? 'ADD_COMMENT' : 'EDIT_COMMENT'))), 'comment-add');

		if ($app->input->getInt('approve', 0) == 1)
		{

			
			if ($canDo->get('core.edit'))
			{
				JToolBarHelper::save('pendingcomment.save', 'JTOOLBAR_SAVE');
			}
			JToolBarHelper::cancel('pendingcomment.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			
			if ($isNew)
			{
				
				if ($user->authorise('core.create', 'com_judownload'))
				{
					JToolBarHelper::apply('comment.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('comment.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::save('comment.save2new', 'JTOOLBAR_SAVE_AND_NEW');
				}
				JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->user_id == $user->id))
				{
					
					JToolBarHelper::apply('comment.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('comment.save', 'JTOOLBAR_SAVE');
					if ($canDo->get('core.create'))
					{
						JToolBarHelper::save('comment.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
				JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CLOSE');
			}
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$isNew      = $this->item->id == 0;
		$document   = JFactory::getDocument();
		$user       = JFactory::getUser();
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_COMMENT' : ($isNew ? 'ADD_COMMENT' : 'EDIT_COMMENT'))));

		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/approval.css");
	}
}
