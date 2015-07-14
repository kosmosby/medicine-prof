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


class JUDownloadViewCriterias extends JUDLViewAdmin
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

		
		$this->items            = $this->get('Items');
		$this->pagination       = $this->get('Pagination');
		$this->state            = $this->get('State');
		$this->canDo            = JUDownloadHelper::getActions('com_judownload');
		$this->groupCanDoManage = JUDownloadHelper::checkGroupPermission("criteria.edit");
		$this->groupCanDoDelete = JUDownloadHelper::checkGroupPermission("criterias.delete");

		
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
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_MANAGER_CRITERIAS'), 'criterias');

		if ($this->groupCanDoManage)
		{
			if ($this->canDo->get('core.create'))
			{
				JToolBarHelper::addNew('criteria.add', 'JTOOLBAR_NEW');
			}

			if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
			{
				JToolBarHelper::editList('criteria.edit', 'JTOOLBAR_EDIT');
			}

			if ($this->canDo->get('core.edit.state'))
			{
				JToolbarHelper::publish('criterias.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('criterias.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($this->groupCanDoDelete)
		{
			if ($this->canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_ITEMS', 'criterias.delete', 'JTOOLBAR_DELETE');
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
		$document->setTitle(JText::_('COM_JUDOWNLOAD_MANAGER_CRITERIAS'));
	}

	
	protected function getSortFields()
	{
		return array(
			'id'         => JText::_('COM_JUDOWNLOAD_FIELD_ID'),
			'title'      => JText::_('COM_JUDOWNLOAD_FIELD_TITLE'),
			'group_name' => JText::_('COM_JUDOWNLOAD_FIELD_GROUP_NAME'),
			'weights'    => JText::_('COM_JUDOWNLOAD_FIELD_WEIGHTS'),
			'required'   => JText::_('COM_JUDOWNLOAD_FIELD_REQUIRED'),
			'ordering'   => JText::_('COM_JUDOWNLOAD_FIELD_ORDERING'),
			'published'  => JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED')
		);
	}
}