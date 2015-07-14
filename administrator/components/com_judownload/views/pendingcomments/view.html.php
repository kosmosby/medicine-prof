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


class JUDownloadViewPendingComments extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		
		$this->items            = $this->get('Items');
		$this->pagination       = $this->get('Pagination');
		$this->state            = $this->get('State');
		$this->canDo            = JUDownloadHelper::getActions('com_judownload');
		$this->groupCanDoManage = JUDownloadHelper::checkGroupPermission("pendingcomment.edit");
		$this->groupCanDoDelete = JUDownloadHelper::checkGroupPermission("pendingcomments.delete");

		
		$this->addToolBar();

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}
		
		parent::display($tpl);

		
		$this->setDocument();
	}

	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PENDING_COMMENTS'), 'pending-comments');

		if ($this->groupCanDoManage)
		{
			if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
			{
				JToolBarHelper::editList('pendingcomment.edit', 'JTOOLBAR_EDIT');
			}

			JToolbarHelper::publish('pendingcomments.approve', 'COM_JUDOWNLOAD_APPROVE', true);
		}

		if ($this->groupCanDoDelete)
		{
			if ($this->canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_ITEMS', 'pendingcomments.delete', 'COM_JUDOWNLOAD_REJECT');
			}
		}


		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PENDING_COMMENTS'));
	}

	
	protected function getSortFields()
	{
		return array(
			'cm.id'               => JText::_('COM_JUDOWNLOAD_FIELD_ID'),
			'cm.title'            => JText::_('COM_JUDOWNLOAD_FIELD_TITLE'),
			'd.title'             => JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE'),
			'ua.username'         => JText::_('COM_JUDOWNLOAD_FIELD_USERNAME'),
			'cm.guest_name'       => JText::_('COM_JUDOWNLOAD_FIELD_GUEST_NAME'),
			'cm.parent_id'        => JText::_('COM_JUDOWNLOAD_FIELD_PARENT'),
			'cm.created'          => JText::_('COM_JUDOWNLOAD_FIELD_CREATED'),
			'total_reports'       => JText::_('COM_JUDOWNLOAD_FIELD_REPORTS'),
			'total_subscriptions' => JText::_('COM_JUDOWNLOAD_FIELD_SUBSCRIPTIONS')
		);
	}
}
