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

class JUDownloadViewPluginViewer extends JUDLViewAdmin
{
	public function loadTemplate($tpl = null)
	{
		
		$this->_output = null;

		$template       = JFactory::getApplication()->getTemplate();
		$layout         = $this->getLayout();
		$layoutTemplate = $this->getLayoutTemplate();

		
		$file = isset($tpl) ? $layout . '_' . $tpl : $layout;

		
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		}

		
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));

		
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$plugin_id = $input->getInt('id', 0);

		if ($plugin_id)
		{
			$model    = $this->getModel();
			$template = $model->getPluginTemplate($plugin_id);

			if ($template)
			{
				$this->_addPath('template', $template);
			}
		}

		$this->_template = JPath::find($this->_path['template'], $filetofind);

		
		if ($this->_template == false)
		{
			$filetofind      = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		if ($this->_template != false)
		{
			
			unset($tpl);
			unset($file);

			
			if (isset($this->this))
			{
				unset($this->this);
			}

			
			ob_start();

			
			
			include $this->_template;

			
			
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			return JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file));
		}
	}

	
	public function display($tpl = null)
	{
		
		$this->addToolBar();

		
		$this->setDocument();

		$result = $this->loadTemplate($tpl);

		if ($result instanceof Exception)
		{
			return $result;
		}

		echo $result;
	}

	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_PLUGIN_VIEWER'), 'plugin-viewer');
		JToolBarHelper::cancel('pluginviewer.cancel');
		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$script   = "
					Joomla.submitbutton = function (task) {
						if (task == 'pluginviewer.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
							Joomla.submitform(task, document.getElementById('adminForm'));
						}
					};";
		$document->addScriptDeclaration($script);
	}

}