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
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');

class JUDownloadViewCSVProcess extends JUDLViewAdmin
{
	public $fieldsOption = array();

	public function display($tpl = null)
	{
		JHtml::_('behavior.calendar');
		$this->addToolBar();
		$this->model = $this->getModel();

		
		if ($this->getLayout() == "fields_mapping")
		{
			
			$this->fieldsOption[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('COM_JUDOWNLOAD_DEFAULT'));
			$this->fieldsOption[] = JHTML::_('select.option', 'ignore', JText::_("COM_JUDOWNLOAD_IGNORE"));

			
			$this->fieldsOption[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('COM_JUDOWNLOAD_CORE_FIELDS'));

			$coreFieldsHaveFieldClass = $this->model->getFieldsHaveFieldClass('core');

			foreach ($coreFieldsHaveFieldClass AS $field)
			{
				$this->fieldsOption[] = JHTML::_('select.option', $field->id, $field->caption);
			}

			$coreFieldsHaveNoFieldClass = $this->model->getDocumentTableFieldsName($coreFieldsHaveFieldClass);
			if (!empty($coreFieldsHaveNoFieldClass))
			{
				foreach ($coreFieldsHaveNoFieldClass AS $field)
				{
					$this->fieldsOption[] = JHTML::_('select.option', $field, ucfirst(str_replace('_', ' ', $field)));
				}
			}
			
			$this->fieldsOption[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('COM_JUDOWNLOAD_EXTRA_FIELDS'));
			$extraFields          = $this->model->getFieldsHaveFieldClass('extra');

			foreach ($extraFields AS $field)
			{
				$this->fieldsOption[] = JHTML::_('select.option', $field->id, $field->caption);
			}

			$this->fieldsOption[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('COM_JUDOWNLOAD_OTHER_FIELDS'));
			$this->fieldsOption[] = JHTML::_('select.option', 'main_cat', JText::_('COM_JUDOWNLOAD_FIELD_MAIN_CATEGORY'));
			$this->fieldsOption[] = JHTML::_('select.option', 'secondary_cats', JText::_('COM_JUDOWNLOAD_FIELD_SECONDARY_CATEGORIES'));
			$this->fieldsOption[] = JHTML::_('select.option', 'gallery', JText::_('COM_JUDOWNLOAD_FIELD_GALLERY'));
			$this->fieldsOption[] = JHTML::_('select.option', 'files', JText::_('COM_JUDOWNLOAD_FIELD_FILES'));
			$this->fieldsOption[] = JHTML::_('select.option', 'related_docs', JText::_('COM_JUDOWNLOAD_FIELD_RELATED_DOCUMENTS'));

		}

		
		if ($this->getLayout() == 'config')
		{
			$this->form = $this->get('Form');

			$this->save = "<select id='save_options' name='save_options'>";
			$this->save .= "<option value='keep' selected>" . JText::_("COM_JUDOWNLOAD_KEEP_BOTH") . "</option>";
			$this->save .= "<option value='skip'>" . JText::_("COM_JUDOWNLOAD_SKIP_EXISTED_DOCUMENTS") . "</option>";
			$this->save .= "<option value='replace'>" . JText::_("COM_JUDOWNLOAD_REPLACE_EXISTED_DOCUMENTS") . "</option>";
			$this->save .= "</select>";
		}

		
		if ($this->getLayout() == 'review')
		{
			if (isset($this->review['config']['default_icon']))
			{
				$this->review['config']['default_icon'] = str_replace(array(JPATH_ROOT . '\\', "\\"), array(JUri::root(), '/'), $this->review['config']['default_icon']);
			}
		}
		$this->isJoomla3x = JUDownloadHelper::isJoomla3x();
		
		if ($this->getLayout() == "export")
		{
			$this->exportForm = $this->get("ExportForm");
		}

		parent::display($tpl);

		
		$this->setDocument();
	}

	
	protected function setDocument()
	{
		if ($this->getLayout() == 'processing')
		{
			JText::script('COM_JUDOWNLOAD_IMPORT_CSV_FINISHED');

			$document = JFactory::getDocument();
			$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/import-csv-ajax.js");
		}

	}

	public function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_CSV_PROCESS'), 'csv-process');

		$task = "csvprocess.load";

		switch ($this->getLayout())
		{
			case 'fields_mapping':
				$task = "csvprocess.config";
				break;
			case 'config':
				$task = "csvprocess.review";
				break;
			case 'review':
				$task = "csvprocess.import";
				break;
		}

		if ($this->getLayout() == 'export')
		{
			JToolBarHelper::custom('csvprocess.export', 'export', 'export', 'Export', false);
		}

		if ($this->getLayout() != "default" && $this->getLayout() != 'export' && $this->getLayout() != 'processing')
		{
			JToolBarHelper::custom($task, 'next', 'next', 'Next', false);
		}

		if ($this->getLayout() != "default")
		{
			JToolBarHelper::cancel('csvprocess.back', 'JTOOLBAR_CANCEL');
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}
}