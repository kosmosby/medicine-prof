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


class JFormFieldComponentTemplateStyle extends JFormField
{
	
	protected $type = 'ComponentTemplateStyle';

	
	public function getInput()
	{

		$db     = JFactory::getDbo();
		$groups = array();
		$app    = JFactory::getApplication();
		
		$appendInherit = '';
		if ($app->input->get("view") == "document")
		{
			if ($this->form->getValue("id"))
			{
				$doc           = JUDownloadHelper::getDocumentById($this->form->getValue("id"));
				$appendInherit = "(" . $this->calculatorInheritStyle($doc->cat_id) . ")";
			}
		}
		else
		{
			if ($this->form->getValue("parent_id"))
			{
				$appendInherit = "(" . $this->calculatorInheritStyle($this->form->getValue("parent_id")) . ")";
			}
		}

		$appendDefault = "(" . $this->getStyle() . ")";

		$groups['inherit']            = array();
		$groups['inherit']['id']      = $this->id . '_inherit';
		$groups['inherit']['text']    = '---' . JText::_('COM_JUDOWNLOAD_INHERIT') . '---';
		$groups['inherit']['items']   = array();
		$groups['inherit']['items'][] = JHtml::_('select.option', '-2', JText::_('COM_JUDOWNLOAD_DEFAULT') . ' ' . $appendDefault);
		$groups['inherit']['items'][] = JHtml::_('select.option', '-1', JText::_('COM_JUDOWNLOAD_INHERIT') . ' ' . $appendInherit);

		$query = $db->getQuery(true);
		$query->select('style.*');
		$query->select('plg.title AS template_title, plg.folder');
		$query->from('#__judownload_template_styles AS style');
		$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
		$query->where('style.lft > 0');
		$query->order('style.lft ASC');
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		
		for ($i = 0, $n = count($styles); $i < $n; $i++)
		{
			$styles[$i]->text = str_repeat('- ', $styles[$i]->level - 1) . $styles[$i]->title . " [ " . $styles[$i]->template_title . " ]";
		}

		$groups['style']          = array();
		$groups['style']['id']    = 'template_style';
		$groups['style']['text']  = JText::_('COM_JUDOWNLOAD_TEMPLATE_STYLES');
		$groups['style']['items'] = array();

		foreach ($styles AS $style)
		{
			$groups['style']['items'][] = JHtml::_('select.option', $style->id, $style->text);
		}

		$html = JHtml::_(
			'select.groupedlist', $groups, $this->name,
			array('id' => $this->id, 'group.id' => 'id', 'list.attr' => "", 'list.select' => $this->value)
		);

		return $html;
	}

	protected function calculatorInheritStyle($cat_id)
	{
		do
		{
			$category = JUDownloadHelper::getCategoryById($cat_id);
			$style_id = $category->style_id;
			$cat_id   = $category->parent_id;
		} while ($style_id == -1 && $cat_id != 0);

		if ($style_id == -2)
		{
			return $this->getStyle();
		}
		else
		{
			return $this->getStyle($style_id);
		}
	}

	protected function getStyle($id = null)
	{
		if ($id == null)
		{
			$where = "style.home = 1";
		}
		else
		{
			$where = "style.id = $id";
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('style.title, plg.title AS template_title');
		$query->from('#__judownload_template_styles AS style');
		$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
		$query->where($where);

		$db->setQuery($query);
		$result = $db->loadObject();
		if ($result)
		{
			return $result->title . " [ " . $result->template_title . " ]";
		}

		return '';
	}
}
