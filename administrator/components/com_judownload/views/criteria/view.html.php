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


class JUDownloadViewCriteria extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		if (!JUDownloadHelper::hasMultiRating())
		{
			JError::raiseError(500, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		JHtml::_('behavior.modal');
		JHtml::_('behavior.calendar');

		
		$this->form   = $this->get('Form');
		$this->item   = $this->get('Item');
		$this->canDo  = JUDownloadHelper::getActions('com_judownload', 'criteriagroup', $this->item->group_id);
		$this->script = $this->get('Script');

		
		$this->addToolBar();

		
		parent::display($tpl);

		
		$this->setDocument();
	}

	
	protected function addToolBar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$isNew      = ($this->item->id == 0);
		$user       = JFactory::getUser();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_CRITERIA' : ($isNew ? 'ADD_CRITERIA' : 'EDIT_CRITERIA'))), 'criteria-add');

		if ($isNew && $user->authorise('core.create', 'com_judownload'))
		{
			JToolBarHelper::apply('criteria.apply');
			JToolBarHelper::save('criteria.save');
			JToolBarHelper::save2new('criteria.save2new');
			JToolBarHelper::cancel('criteria.cancel');
		}
		else
		{
			if (!$checkedOut)
			{
				
				if ($this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('criteria.apply');
					JToolBarHelper::save('criteria.save');
					
					if ($this->canDo->get('core.create'))
					{
						JToolBarHelper::save2new('criteria.save2new');
					}
				}
			}
			JToolBarHelper::cancel('criteria.cancel', 'JTOOLBAR_CLOSE');
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
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_CRITERIA' : ($isNew ? 'ADD_CRITERIA' : 'EDIT_CRITERIA'))));

		JText::script('COM_JUDOWNLOAD_WEIGHT_MUST_BE_GREATER_THAN_ZERO');
		$document->addScript(JUri::root() . $this->script);
	}
}