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

class JUDownloadFieldCore_reports extends JUDownloadFieldText
{
	protected $field_name = 'reports';
	protected $fieldvalue_column = "reports";

	protected function getValue()
	{
		$app = JFactory::getApplication();
		
		if ($app->isSite() && isset($this->doc->total_reports) && !is_null($this->doc->total_reports))
		{
			$value = $this->doc->total_reports;

		}
		else
		{
			$db    = JFactory::getDbo();
			$query = "SELECT count(*) FROM #__judownload_reports WHERE (item_id = " . $this->doc_id . " AND type = 'document')";
			$db->setQuery($query);
			$result = $db->loadResult();
			$value  = $result;
		}

		return $value;
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		return true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '</span>';
	}

	public function getBackendOutput()
	{
		$value = $this->value;

		return '<span class="reports"><a href="index.php?option=com_judownload&view=reports&doc_id=' . $this->doc_id . '" title="' . JText::_('COM_JUDOWNLOAD_VIEW_REPORTS') . '">' . JText::plural('COM_JUDOWNLOAD_N_REPORTS', $value) . '</a></span>';
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$query->where("(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type='document') = " . (int) $search);
		}
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($this->params->get("is_numeric", 0) && is_array($search) && !empty($search))
		{
			if ($search['from'] !== "" && $search['to'] !== "")
			{
				$from = (int) $search['from'];
				$to   = (int) $search['to'];
				if ($from > $to)
				{
					$this->swap($from, $to);
				}

				$where[] = "(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type='document') BETWEEN $from AND $to";
			}
			elseif ($search['from'] !== "")
			{
				$from = (int) $search['from'];

				$where[] = "(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type='document') >= $from";
			}
			elseif ($search['to'] !== "")
			{
				$to = (int) $search['to'];

				$where[] = "(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type='document') <= $to";
			}
		}
		else
		{
			$this->onSimpleSearch($query, $where, $search);
		}
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', '(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type="document") AS reports');

		return array('ordering' => 'reports', 'direction' => $this->priority_direction);
	}
}

?>