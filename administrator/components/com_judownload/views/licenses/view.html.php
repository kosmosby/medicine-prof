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


class JUDownloadViewLicenses extends JUDLViewAdmin
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
		$this->groupCanDoManage = JUDownloadHelper::checkGroupPermission("license.edit");
		$this->groupCanDoDelete = JUDownloadHelper::checkGroupPermission("licenses.delete");

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}

		
		$this->addToolBar();

		
		parent::display($tpl);

		
		$this->setDocument();
	}

	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_MANAGER_LICENSES'), 'licenses');

		if ($this->groupCanDoManage)
		{
			if ($this->canDo->get('core.create'))
			{
				JToolBarHelper::addNew('license.add');
			}

			if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
			{
				JToolBarHelper::editList('license.edit');
			}

			if ($this->canDo->get('core.edit.state'))
			{
				JToolbarHelper::publish('licenses.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('licenses.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($this->canDo->get('core.delete') && $this->groupCanDoDelete)
		{
			JToolBarHelper::deleteList('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_ITEMS', 'licenses.delete');
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JUDOWNLOAD_MANAGER_LICENSES'));
	}

	
	protected function getSortFields()
	{
		return array(
			'l.id'            => JText::_('COM_JUDOWNLOAD_FIELD_ID'),
			'l.title'         => JText::_('COM_JUDOWNLOAD_FIELD_TITLE'),
			'l.ordering'      => JText::_('COM_JUDOWNLOAD_FIELD_ORDERING'),
			'l.published'     => JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'),
			'total_documents' => JText::_('COM_JUDOWNLOAD_FIELD_TOTAL_DOCUMENTS')
		);
	}
}