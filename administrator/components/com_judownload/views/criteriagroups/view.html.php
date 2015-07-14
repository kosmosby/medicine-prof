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


class JUDownloadViewCriteriaGroups extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		if (!JUDownloadHelper::hasMultiRating())
		{
			JError::raiseError(500, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}

		
		$this->items            = $this->get('Items');
		$this->pagination       = $this->get('Pagination');
		$this->state            = $this->get('State');
		$this->canDo            = JUDownloadHelper::getActions('com_judownload');
		$this->groupCanDoManage = JUDownloadHelper::checkGroupPermission("criteriagroup.edit");
		$this->groupCanDoDelete = JUDownloadHelper::checkGroupPermission("criteriagroups.delete");

		
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
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_MANAGER_CRITERIA_GROUPS'), 'criteriagroups');

		if ($this->groupCanDoManage)
		{

			if ($this->canDo->get('core.create'))
			{
				JToolBarHelper::addNew('criteriagroup.add', 'JTOOLBAR_NEW');
			}

			if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
			{
				JToolBarHelper::editList('criteriagroup.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($this->groupCanDoDelete)
		{
			if ($this->canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('COM_JUDOWNLOAD_DELETE_CRITERIAGROUP_WARNING', 'criteriagroups.delete', 'JTOOLBAR_DELETE');
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
		$document->setTitle(JText::_('COM_JUDOWNLOAD_MANAGER_CRITERIA_GROUPS'));
	}

	
	protected function getSortFields()
	{
		return array(
			'id'              => JText::_('COM_JUDOWNLOAD_FIELD_ID'),
			'name'            => JText::_('COM_JUDOWNLOAD_FIELD_GROUP_NAME'),
			'total_criterias' => JText::_('COM_JUDOWNLOAD_FIELD_TOTAL_CRITERIAS')
		);
	}
}