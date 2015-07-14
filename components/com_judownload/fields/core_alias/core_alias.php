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

class JUDownloadFieldCore_alias extends JUDownloadFieldText
{
	protected $field_name = 'alias';

	public function filterField($value)
	{
		if (trim($value) == '')
		{
			$fieldTitle = new JUDownloadFieldCore_title();
			$titleValue = $this->fields_data[$fieldTitle->id];
			$value      = $titleValue;
		}

		$value = JApplication::stringURLSafe($value);

		if (trim(str_replace('-', '', $value)) == '')
		{
			$value = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		return $value;
	}

	public function PHPValidate($values)
	{
		
		if (($values === "" || $values === null) && !$this->isRequired())
		{
			return true;
		}

		$fieldCategories = new JUDownloadFieldCore_categories();
		
		if (!isset($this->fields_data[$fieldCategories->id]))
		{
			return true;
		}
		else
		{
			$categoriesValue = $this->fields_data[$fieldCategories->id];
		}

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->SELECT('COUNT(*)');
		$query->FROM('#__judownload_documents AS d');
		$query->JOIN('', '#__judownload_documents_xref AS dxref ON dxref.doc_id = d.id');
		$query->JOIN('', '#__judownload_categories AS c ON dxref.cat_id = c.id');
		
		$query->WHERE('d.alias = ' . $db->quote($values));
		$query->WHERE('c.id = ' . (int) $categoriesValue['main']);

		if ($this->doc_id)
		{
			
			if ($this->doc->approved < 0)
			{
				$query->WHERE('d.id != ' . abs($this->doc->approved));
			}
			
			elseif ($this->doc->approved == 1)
			{
				$query->WHERE('d.approved != ' . (-$this->doc->id));
			}

			
			if ($this->doc->id)
			{
				$query->WHERE('d.id !=' . $this->doc->id);
			}
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			return JText::_('COM_JUDOWNLOAD_DOCUMENT_ALIAS_MUST_BE_UNIQUE');
		}

		return true;
	}
}

?>