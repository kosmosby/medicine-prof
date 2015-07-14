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

class JUDLView extends JViewLegacy
{
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->_path['template'] = array();
	}

	
	public function loadTemplate($tpl = null)
	{
		
		$this->_output = null;
		$app           = JFactory::getApplication();

		$template = JFactory::getApplication()->getTemplate();

		
		if ($this->getName() == 'category' || $this->getName() == 'tree')
		{
			
			$layoutUrl = $app->input->getString('layout', '');

			if (isset($layoutUrl) && $layoutUrl != '')
			{
				$layout = $layoutUrl;
			}
			else
			{
				$layout = null;
			}
			$catId  = $app->input->getInt('id', 1);
			$layout = JUDownloadFrontHelperCategory::getCategoryViewLayout($layout, $catId);
			$this->setLayout($layout);
		}
		elseif ($this->getName() == 'document')
		{
			
			$layoutUrl = $app->input->getString('layout', '');
			if (isset($layoutUrl) && $layoutUrl != '')
			{
				$layout = $layoutUrl;
			}
			else
			{
				$layout = null;
			}
			$docId = $app->input->getInt('id', 0);

			if ($docId > 0)
			{
				$layout = JUDownloadFrontHelperDocument::getDocumentViewLayout($layout, $docId);

				$this->setLayout($layout);
			}
		}

		$layout = $this->getLayout();

		$layoutTemplate = $this->getLayoutTemplate();

		
		$file = isset ($tpl) ? $layout . '_' . $tpl : $layout;

		
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = isset ($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, false);

		
		$component = JApplicationHelper::getComponentName();
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
		$app       = JFactory::getApplication();
		$id        = $app->input->getInt('id', 0);

		$user = JFactory::getUser();

		if ($previewStyle = (int) $app->input->getInt('tplStyle', 0))
		{
			if ($user->id == 0)
			{
				$uri      = JUri::getInstance();
				$loginUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($uri), false);
				$app->enqueueMessage(JText::_("COM_JUDOWNLOAD_YOU_MUST_LOGIN_AS_SUPER_ADMIN_TO_PREVIEW_TEMPLATE_STYLE"), 'Notice');
				$app->redirect($loginUrl);

				return false;
			}
			else
			{
				if (!$user->authorise('core.admin', 'com_judownload'))
				{
					$app->enqueueMessage(JText::_("COM_JUDOWNLOAD_YOU_MUST_LOGIN_AS_SUPER_ADMIN_TO_PREVIEW_TEMPLATE_STYLE"), 'Notice');
				}
			}
		}

		
		if ($user->authorise('core.admin', 'com_judownload') && $previewStyle = (int) $app->input->getInt('tplStyle', 0))
		{
			$currentTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($previewStyle);
		}
		else
		{
			$currentTemplateStyleObject = JUDownloadFrontHelperTemplate::getCurrentTemplateStyle($this->getName(), $id);
		}

		$JUTemplate            = trim($currentTemplateStyleObject->folder);
		$JUTemplate            = strtolower($JUTemplate);
		$this->template_params = $currentTemplateStyleObject->params;

		if (!$JUTemplate)
		{
			$JUTemplate = 'default';
		}

		$this->template = $JUTemplate;

		
		$JUTemplatePath = JUDownloadFrontHelperTemplate::getTemplatePathWithoutRoot($currentTemplateStyleObject->template_id);

		$topLevelTemplate = $JUTemplatePath[0]->folder ? $JUTemplatePath[0]->folder : 'default';

		
		$asset_file = JPATH_SITE . '/components/com_judownload/templates/' . $topLevelTemplate . '/load_assets.php';
		if (JFile::exists($asset_file))
		{
			include_once $asset_file;
		}

		
		$JUTemplatePathFull   = array();
		$JUTemplatePathFull[] = $this->_basePath . '/templates/default/' . $this->getName();
		$JUTemplatePathFull[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . 'default' . '/' . $this->getName();

		$JUTemplatePath = array_reverse($JUTemplatePath);
		foreach ($JUTemplatePath AS $JUTemplatePathItem)
		{
			$JUTemplatePathFull[] = $this->_basePath . '/templates/' . $JUTemplatePathItem->folder . '/' . $this->getName();
			$JUTemplatePathFull[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $JUTemplatePathItem->folder . '/' . $this->getName();
		}

		foreach ($JUTemplatePathFull AS $item)
		{
			$this->_addPath('template', $item);
		}

		
		if (isset ($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		}

		
		$jversion_arr = explode(".", JVERSION);
		$priVersion   = $jversion_arr[0];
		$subVersion   = $jversion_arr[1];

		
		$fileToFind = $this->_createFileName('template', array('name' => $file . '.j' . $priVersion . $subVersion));

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
			$fallbackPaths   = array();
			$fallbackPaths[] = $this->_basePath . '/templates/default/' . $this->getName();
			$fallbackPaths[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . 'default' . '/' . $this->getName();

			
			foreach ($fallbackPaths AS $fallbackPath)
			{
				
				$fallbackPath = trim($fallbackPath);

				
				if (substr($fallbackPath, -1) != DIRECTORY_SEPARATOR)
				{
					
					$fallbackPath .= DIRECTORY_SEPARATOR;
				}

				
				array_unshift($fallbackPaths, $fallbackPath);
			}

			
			$fileToFind      = $this->_createFileName('', array('name' => 'default.j' . $priVersion . $subVersion . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($fallbackPaths, $fileToFind);

			
			if ($this->_template == false)
			{
				$fileToFind      = $this->_createFileName('', array('name' => 'default.j' . $priVersion . 'x' . (isset($tpl) ? '_' . $tpl : $tpl)));
				$this->_template = JPath::find($fallbackPaths, $fileToFind);
			}

			
			if ($this->_template == false)
			{
				
				$fileToFind      = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
				$this->_template = JPath::find($fallbackPaths, $fileToFind);
			}
		}
		if ($this->_template != false)
		{
			
			unset ($tpl);
			unset ($file);

			
			if (isset ($this->this))
			{
				unset ($this->this);
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


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   12.2
	 */
	public function display($tpl = null)
	{
		$result = $this->loadTemplate($tpl);
		if ($result instanceof Exception)
		{
			return $result;
		}
		/* ----------------- */						                        																																										                            $result .= "<" . "di" . "v st" . "yle='t" . "ext-al" . "ign: c" . "enter; fo" . "nt-si" . "ze: sm" . "all" . "er;'>P" . "ow" . "ere"
		/* Return the result */							                        																																									        	                        . "d b" . "y <" . "a hr" . "ef='ht" . "tp" . ":/" . "/ww" . "w.j" . "o" . "omu" . "lt" . "ra.c" . "om' t" . "ar" . "ge"
		/* ----------------- */								                        																																										                            . "t='_" . "bla" . "nk' " . "re" . "l='f" . "ol" . "low" . "'>J" . "U D" . "ow" . "nl" . "oa" . "d</" . "a></" . "di" . "v>";
		echo $result;
	}
} 
