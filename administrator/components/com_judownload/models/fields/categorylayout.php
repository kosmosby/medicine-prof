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


class JFormFieldCategoryLayout extends JFormField
{
	
	protected $type = 'CategoryLayout';

	
	protected function getInput()
	{
		

		
		$clientId = $this->element['client_id'];

		if (is_null($clientId) && $this->form instanceof JForm)
		{
			$clientId = $this->form->getValue('client_id');
		}
		$clientId = (int) $clientId;

		$client = JApplicationHelper::getClientInfo($clientId);

		
		$extn = (string) $this->element['extension'];

		if (empty($extn) && ($this->form instanceof JForm))
		{
			$extn = $this->form->getValue('extension');
		}

		$extn = preg_replace('#\W#', '', $extn);

		
		$template = (string) $this->element['template'];
		$template = preg_replace('#\W#', '', $template);

		
		if ($this->form instanceof JForm)
		{
			$template_style_id = $this->form->getValue('template_style_id');
		}
		$template_style_id = preg_replace('#\W#', '', $template_style_id);

		
		$view = (string) $this->element['view'];
		$view = preg_replace('#\W#', '', $view);

		
		if ($extn && $view && $client)
		{

			
			$lang = JFactory::getLanguage();
			$lang->load($extn . '.sys', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR . '/components/' . $extn, null, false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR . '/components/' . $extn, $lang->getDefault(), false, false);

			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			
			$query->select('e.element, e.name');
			$query->from('#__extensions AS e');
			$query->where('e.client_id = ' . (int) $clientId);
			$query->where('e.type = ' . $db->quote('template'));
			$query->where('e.enabled = 1');

			if ($template)
			{
				$query->where('e.element = ' . $db->quote($template));
			}

			if ($template_style_id)
			{
				$query->join('LEFT', '#__template_styles AS s on s.template=e.element');
				$query->where('s.id=' . (int) $template_style_id);
			}

			
			$db->setQuery($query);
			$templates = $db->loadObjectList('element');

			
			if ($db->getErrorNum())
			{
				JError::raiseWarning(500, $db->getErrorMsg());
			}

			
			$component_path = JPath::clean($client->path . '/components/' . $extn . '/views/' . $view . '/tmpl');
			
			$component_layouts = array();

			
			$groups = array();
			$items  = array();

			
			if ($this->element['useinherit'])
			{
				$groups['inherit']          = array();
				$groups['inherit']['id']    = $this->id . '_inherit';
				$groups['inherit']['text']  = '---' . JText::_('COM_JUDOWNLOAD_INHERIT') . '---';
				$groups['inherit']['items'] = array();
			}
			
			if (is_dir($component_path) && ($component_layouts = JFolder::files($component_path, '^[^_]*\.xml$', false, true)))
			{
				
				$groups['_']          = array();
				$groups['_']['id']    = $this->id . '__';
				$groups['_']['text']  = JText::sprintf('JOPTION_FROM_COMPONENT');
				$groups['_']['items'] = array();

				foreach ($component_layouts AS $i => $file)
				{
					
					if (!$xml = simplexml_load_file($file))
					{
						unset($component_layouts[$i]);

						continue;
					}

					
					if (!$menu = $xml->xpath('layout[1]'))
					{
						unset($component_layouts[$i]);

						continue;
					}

					$menu = $menu[0];

					
					$value                  = JFile::stripext(JFile::getName($file));
					$component_layouts[$i]  = $value;
					$text                   = isset($menu['option']) ? JText::_($menu['option']) : (isset($menu['title']) ? JText::_($menu['title']) : $value);
					$groups['_']['items'][] = JHtml::_('select.option', '_:' . $value, $text);
					$items['_'][$value]     = $text;
				}
			}

			
			

			if ($this->element['useinherit'])
			{
				$appendinherit = "";
				if ($this->form->getValue("parent_id"))
				{
					$appendinherit = $this->calculatorInheritLayout($items, $this->form->getValue("parent_id"));
				}
				$appendGlobal                 = $this->getLayout($items);
				$groups['inherit']['items'][] = JHtml::_('select.option', '-2', JText::_('COM_JUDOWNLOAD_GLOBAL') . $appendGlobal);
				$groups['inherit']['items'][] = JHtml::_('select.option', '-1', JText::_('COM_JUDOWNLOAD_INHERIT') . $appendinherit);
			}

			
			$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

			

			$selected = array($this->value);

			
			return JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected)
			);

		}
		else
		{
			return '';
		}
	}

	protected function getLayout($items, $layout = null)
	{
		if ($layout == null)
		{
			$params = JUDownloadHelper::getParams($this->form->getValue('id'));
			$layout = $params->get("layout_category", '_:default');
		}

		if ($layout)
		{
			$layout = explode(":", $layout);

			if ($layout[0] == "_")
			{
				return "(Component &gt; " . $items['_'][$layout[1]] . ")";
			}
			else
			{
				return "($layout[0] &gt; " . $items[$layout[0]][$layout[1]] . ")";
			}
		}
	}

	protected function calculatorInheritLayout($items, $cat_id)
	{
		do
		{
			$category = JUDownloadHelper::getCategoryById($cat_id);
			$layout   = $category->layout;
			$cat_id   = $category->parent_id;
		} while ($layout == -1);

		if ($layout == -2)
		{
			return $this->getLayout($items);
		}
		else
		{
			return $this->getLayout($items, $layout);
		}
	}
}
