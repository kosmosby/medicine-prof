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

class JUDLViewAdmin extends JViewLegacy
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


		
		$jversion_arr = explode(".", JVERSION);
		$priVersion   = $jversion_arr[0];
		$subVersion   = $jversion_arr[1];

		
		$fileToFind      = $this->_createFileName('template', array('name' => $file . '.j' . $priVersion . $subVersion));
		$this->_template = JPath::find($this->_path['template'], $fileToFind);

		
		if ($this->_template == false)
		{
			$fileToFind      = $this->_createFileName('template', array('name' => $file . '.j' . $priVersion . 'x'));
			$this->_template = JPath::find($this->_path['template'], $fileToFind);
		}

		
		if ($this->_template == false)
		{
			$fileToFind      = $this->_createFileName('template', array('name' => $file));
			$this->_template = JPath::find($this->_path['template'], $fileToFind);
		}


		
		if ($this->_template == false)
		{
			
			$fileToFind      = $this->_createFileName('', array('name' => 'default.j' . $priVersion . $subVersion . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $fileToFind);

			
			if ($this->_template == false)
			{
				$fileToFind      = $this->_createFileName('', array('name' => 'default.j' . $priVersion . 'x' . (isset($tpl) ? '_' . $tpl : $tpl)));
				$this->_template = JPath::find($this->_path['template'], $fileToFind);
			}

			
			if ($this->_template == false)
			{
				
				$fileToFind      = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
				$this->_template = JPath::find($this->_path['template'], $fileToFind);
			}
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
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}
	}
} 
