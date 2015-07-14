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

jimport('joomla.html.html');
JHtml::addIncludePath(JPATH_SITE . '/administrator/components/com_judownload/helpers/html');

class JUDownloadFieldDateTime extends JUDownloadFieldBase
{
	public function getDefaultPredefinedValues()
	{
		$app   = JFactory::getApplication();
		$value = $this->getPredefinedValues();

		
		if ($app->input->get('view') != 'field' && strtoupper(trim($value)) == "NOW")
		{
			$date  = JFactory::getDate();
			$value = $date->toSql();
		}

		return $value;
	}

	public function parseValue($value)
	{
		if (!$this->isPublished())
		{
			return null;
		}

		$config = JFactory::getConfig();
		$user   = JFactory::getUser();
		
		$filter = strtoupper((string) $this->getFilter());
		switch ($filter)
		{
			case 'SERVER_UTC':
				
				if (intval($value))
				{
					
					$date = JFactory::getDate($value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					
					$value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;

			case 'USER_UTC':
				
				if (intval($value))
				{
					
					$date = JFactory::getDate($value, 'UTC');
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					
					$value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		return $value;
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		
		$format = '%Y-%m-%d %H:%M:%S';

		
		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('value', $value);
		$this->setVariable('format', $format);

		return $this->fetch('input.php', __CLASS__);
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "search");
		}

		$this->setAttribute("class", "input-medium", "search");

		$this->setVariable('value', $defaultValue);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (intval($this->value) == 0)
		{
			return $this->value;
		}

		$format = $this->params->get('dateformat', "DATE_FORMAT_LC1");
		if ($format == "custom")
		{
			$format = $this->params->get('custom_dateformat', "") ? $this->params->get('custom_dateformat', "") : "DATE_FORMAT_LC1";
		}

		$this->setVariable('value', $this->value);
		$this->setVariable('format', $format);

		return $this->fetch('output.php', __CLASS__);
	}

	public function getPredefinedValuesHtml()
	{
		
		$attributes = array();
		if ($this->params->get('size'))
		{
			$attributes['size'] = (int) $this->params->get('size');
		}

		$default_predefined = $this->getDefaultPredefinedValues();

		$html = JHtml::_('judownloadadministrator.calendar', $default_predefined, "jform[predefined_values]", "jform_predefined_values", "%Y-%m-%d %H:%M:%S", $attributes);

		return $html;
	}

	public function onSearch(&$query, &$where, $search)
	{
		if (is_array($search) && !empty($search))
		{
			$db = JFactory::getDbo();
			$query->join('LEFT', '#__judownload_fields_values AS field_values_' . $this->id . ' ON (d.id = field_values_' . $this->id . '.doc_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');
			if ($search['from'] !== "" && $search['to'] !== "")
			{
				$from = $db->quote($search['from']);
				$to   = $db->quote($search['to']);
				if ($from > $to)
				{
					$this->swap($from, $to);
				}

				$where[] = $this->fieldvalue_column . " BETWEEN $from AND $to";
			}
			elseif ($search['from'] !== "")
			{
				$from = $db->quote($search['from']);

				$where[] = $this->fieldvalue_column . " >= $from";
			}
			elseif ($search['to'] !== "")
			{
				$to = $db->quote($search['to']);

				$where[] = $this->fieldvalue_column . " <= $to";
			}
		}
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search !== '')
		{
			$db = JFactory::getDbo();
			$query->join('LEFT', '#__judownload_fields_values AS field_values_' . $this->id . ' ON (d.id = field_values_' . $this->id . '.doc_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');
			$where[] = "(" . $this->fieldvalue_column . " = " . $db->quote($search) . ")";
		}
	}
}

?>