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


class JUDownloadViewGlobalconfig extends JUDLViewAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config = array('layout' => 'edit'));
	}

	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		JHtml::_('behavior.calendar');
		
		$this->form             = $this->get('Form');
		$this->item             = $this->get('Item');
		$this->script           = $this->get('Script');
		$this->canDo            = JUDownloadHelper::getActions('com_judownload');
		$this->groupCanDoManage = JUDownloadHelper::checkGroupPermission("globalconfig.save");
		$this->isJoomla3x       = JUDownloadHelper::isJoomla3x();
		
		$this->setDocument();
		
		$this->addToolBar();

		
		parent::display($tpl);
	}

	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_GLOBAL_CONFIG'), 'global-config');
		if ($this->canDo->get("core.edit") && $this->groupCanDoManage)
		{
			JToolBarHelper::apply('globalconfig.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('globalconfig.save', 'JTOOLBAR_SAVE');
		}
		JToolBarHelper::cancel('globalconfig.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		
		$bar->appendButton('Confirm', JText::_('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_RESET_GLOBAL_CONFIG_TO_DEFAULT'), 'save', JText::_('COM_JUDOWNLOAD_GLOBAL_CONFIG_RESET_DEFAULT'), 'globalconfig.resetDefault', false);
		if ($this->canDo->get('core.admin'))
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
		$document->setTitle(JText::_('COM_JUDOWNLOAD_GLOBAL_CONFIG'));
		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/jusplit.js");
		$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/jufieldset.js");

		$javascript = 'jQuery(document).ready(function($){
							$(".judownload-config dd.tabs > ul, .judownload-config .tab-pane").jusplit();
							$(".jufieldset").jufieldset();
						});';
		$document->addScriptDeclaration($javascript);
		$document->addScript(JUri::root() . $this->script);
	}
}
