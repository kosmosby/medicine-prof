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

class JUDownloadViewListCats extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$app                       = JFactory::getApplication();
		$rootCat                   = JUDownloadFrontHelperCategory::getRootCategory();
		$fastAddError              = $app->getUserState('com_judownload.categories.fastadderror');
		$fastAddSuccess            = $app->getUserState('com_judownload.categories.fastaddsuccess');
		$this->cat_id              = $app->input->getInt('cat_id', $rootCat->id);
		$this->params              = JUDownloadHelper::getParams($this->cat_id);
		$this->canDoCat            = JUDownloadHelper::getActions('com_judownload', 'category', $this->cat_id);
		$this->rootCat             = JUDownloadFrontHelperCategory::getRootCategory();
		$this->allowAddDoc         = (($this->params->get('allow_add_doc_to_root', 0) && $this->cat_id == $this->rootCat->id) || $this->cat_id != $this->rootCat->id);
		$this->docGroupCanDoManage = $this->groupCanDoCatManage = JUDownloadHelper::checkGroupPermission("document.edit");
		$this->docGroupCanDoDelete = $this->groupCanDoCatDelete = JUDownloadHelper::checkGroupPermission("documents.delete");
		$this->catGroupCanDoManage = $this->groupCanDoCatManage = JUDownloadHelper::checkGroupPermission("category.edit");
		$this->catGroupCanDoDelete = $this->groupCanDoCatDelete = JUDownloadHelper::checkGroupPermission("categories.delete");
		//
		if ($fastAddSuccess)
		{
			$app->enqueueMessage($fastAddSuccess);
			$app->setUserState('com_judownload.categories.fastaddsuccess', '');
		}

		if ($fastAddError)
		{
			$app->enqueueMessage($fastAddError, 'error');
			$app->setUserState('com_judownload.categories.fastadderror', '');
		}

		
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->model      = $this->getModel();

		
		$this->addToolBar();

		
		$this->setDocument();

		
		parent::display($tpl);
	}

	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_MANAGER'), 'manager');

		if ($this->docGroupCanDoDelete)
		{
			if ($this->canDoCat->get('judl.document.delete') || $this->canDoCat->get('judl.document.delete.own'))
			{
				JToolBarHelper::custom($task = 'documents.delete', $icon = 'delete', $iconOver = 'delete', $alt = JText::_('COM_JUDOWNLOAD_DELETE_DOCS_BTN'), $listSelect = false, $x = false);
			}
		}

		if ($this->docGroupCanDoManage)
		{
			if ($this->canDoCat->get('judl.document.create'))
			{
				JToolBarHelper::custom($task = 'documents.copyDocuments', $icon = 'copy', $iconOver = 'copy', $alt = JText::_('COM_JUDOWNLOAD_COPY_DOCS_BTN'), $listSelect = false, $x = false);
			}

			if (($this->canDoCat->get('judl.document.edit') || $this->canDoCat->get('judl.document.edit.own')) && $this->canDoCat->get('judl.document.create'))
			{
				JToolBarHelper::custom($task = 'documents.moveDocuments', $icon = 'move', $iconOver = 'move', $alt = JText::_('COM_JUDOWNLOAD_MOVE_DOCS_BTN'), $listSelect = false, $x = false);
			}
		}

		JToolBarHelper::divider();

		if ($this->catGroupCanDoDelete)
		{
			if ($this->canDoCat->get('judl.category.delete') || $this->canDoCat->get('judl.category.delete.own'))
			{
				JToolBarHelper::custom($task = 'categories.delete', $icon = 'delete', $iconOver = 'delete', $alt = JText::_('COM_JUDOWNLOAD_DELETE_CATS_BTN'), $listSelect = true, $x = false);
			}
		}

		if ($this->catGroupCanDoManage)
		{
			if ($this->canDoCat->get('judl.category.create'))
			{
				JToolBarHelper::custom($task = 'categories.copycats', $icon = 'copy', $iconOver = 'copy', $alt = JText::_('COM_JUDOWNLOAD_COPY_CATS_BTN'), $listSelect = true, $x = false);
			}

			if (($this->canDoCat->get('judl.category.edit') || $this->canDoCat->get('judl.category.edit.own')) && $this->canDoCat->get('judl.category.create'))
			{
				JToolBarHelper::custom($task = 'categories.movecats', $icon = 'move', $iconOver = 'move', $alt = JText::_('COM_JUDOWNLOAD_MOVE_CATS_BTN'), $listSelect = true, $x = false);
			}
		}

		if ($this->canDoCat->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_judownload');
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JUDOWNLOAD_MANAGER'));
		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/styles.css");
		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/reset_css.css");
		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/bootstrap-multiselect.css");
		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/prettify.css");
		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/jquery-spliter.css");

		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/bootstrap-multiselect.js");
		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/jquery.splitter.js");
		$multiSelect =
			'jQuery(document).ready(function($) {
					$("#category-fields, #fields").multiselect({
						numberDisplayed: 3,
						nonSelectedText: "' . JText::_('COM_JUDOWNLOAD_SELECT_DISPLAYED_FIELDS') . '",
						nSelectedText: "' . JText::_('COM_JUDOWNLOAD_FIELDS_SELECTED') . '",
						allSelectedText: "' . JText::_('COM_JUDOWNLOAD_ALL_FIELDS_SELECTED') . '",
						buttonClass: "btn btn-mini",
						buttonContainer: "<div class=\"select-fields btn-group pull-left\" />",
						maxHeight: 250,
						includeSelectAllOption: true,
						selectAllText: "' . JText::_('COM_JUDOWNLOAD_SELECT_ALL') . '",
						selectAllValue: "0",
						enableFiltering: true,
						filterBehavior: "both",
						enableCaseInsensitiveFiltering: true,
						filterPlaceholder: "' . JText::_('COM_JUDOWNLOAD_SEARCH') . '",
						templates: {
							filter: \'<li class="multiselect-item filter"><div class="input-group input-append"><input class="form-control multiselect-search input-mini" type="text"></div></li>\',
							filterClearBtn: \'<button class="btn btn-default multiselect-clear-filter" type="button"><i class="icon-remove"></i></button>\'
						}
					});

					$("#search-in").multiselect({
						buttonClass: "btn btn-mini",
						buttonContainer: "<div class=\"select-fields btn-group pull-left\" />",
						maxHeight: 250,
						enableFiltering: false
					});
				});
			';
		$splitter    = '
			jQuery(document).ready(function($) {
				$("#splitterContainer").splitter({name: "judownload", minAsize:150, maxAsize:500, splitVertical:true, A:$("#leftPane"), B:$("#rightPane"), slave:$("#rightSplitterContainer"), closeableto:0});
			});
		';
		$document->addScriptDeclaration($multiSelect);
		$document->addScriptDeclaration($splitter);
	}
}
