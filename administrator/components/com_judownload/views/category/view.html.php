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


class JUDownloadViewCategory extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		
		$this->form    = $this->get('Form');
		$this->plugins = $this->get('Plugins');
		$this->model   = $this->getModel();
		$this->item    = $this->get('Item');
		$this->script  = $this->get('Script');
		$this->canDo   = JUDownloadHelper::getActions('com_judownload', 'category', $this->item->id);

		
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
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_CATEGORY' : ($isNew ? 'ADD_CATEGORY' : 'EDIT_CATEGORY'))), 'category-add');

		if ($isNew && $user->authorise('judl.category.create', 'com_judownload'))
		{
			JToolBarHelper::apply('category.apply');
			JToolBarHelper::save('category.save');
			JToolBarHelper::save2new('category.save2new');
			JToolBarHelper::cancel('category.cancel');
		}
		else
		{
			if (!$checkedOut)
			{
				
				if ($this->canDo->get('judl.category.edit') || ($this->canDo->get('judl.category.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('category.apply');
					JToolBarHelper::save('category.save');
					
					if ($this->canDo->get('judl.category.create'))
					{
						JToolBarHelper::save2new('category.save2new');
					}
				}
			}
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
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
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_CATEGORY' : ($isNew ? 'ADD_CATEGORY' : 'EDIT_CATEGORY'))));
		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/jusplit.js");
		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/jufieldset.js");

		$javascript = 'jQuery(document).ready(function($){
							$(".judownload-config dd.tabs > ul, .judownload-config .tab-pane").jusplit();
							$(".jufieldset").jufieldset();
						});';
		$document->addScriptDeclaration($javascript);

		JText::script('COM_JUDOWNLOAD_PLEASE_SELECT_A_CATEGORY');
		JText::script('COM_JUDOWNLOAD_INVALID_IMAGE');
		$document->addScript(JUri::root() . $this->script);
	}
}
