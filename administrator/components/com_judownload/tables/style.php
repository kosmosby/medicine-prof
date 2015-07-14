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

jimport('joomla.database.tablenested');

class JUDownloadTableStyle extends JTableNested
{

	
	public function __construct(&$db)
	{
		parent::__construct('#__judownload_template_styles', 'id', $db);
	}

	public function bind($array, $ignore = array())
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	public function delete($pk = null)
	{
		$db = JFactory::getDbo();

		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		$this->load($pk);

		if ($this->home == 1 || $this->default == 1)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_CAN_NOT_DELETE_DEFAULT_TEMPLATE_STYLE'));

			return false;
		}

		$defaultStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();

		

		
		
		$query = $db->getQuery(true);
		$query->update('#__judownload_categories');
		$query->set('style_id = -2');
		if ($defaultStyleObject->template_id != $this->template_id)
		{
			$query->set('template_params = ""');
		}
		$query->where('parent_id = 0');
		$query->where('style_id = ' . $pk);
		$db->setQuery($query);
		$db->execute();

		
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_categories');
		$query->where('style_id = ' . $pk);
		$db->setQuery($query);
		$categoryArrayAssignedToStyle = $db->loadColumn();

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_categories');
		$query->set('style_id = -1');
		$query->where('parent_id != 0');
		$query->where('style_id = ' . $pk);
		$db->setQuery($query);
		$db->execute();

		foreach ($categoryArrayAssignedToStyle AS $categoryIdAssignedToStyle)
		{
			$styleObjectOfCategory = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($categoryIdAssignedToStyle);
			if ($styleObjectOfCategory->template_id != $this->template_id)
			{
				$query = $db->getQuery(true);
				$query->update('#__judownload_categories');
				$query->set('template_params = ""');
				$query->where('id = ' . $categoryIdAssignedToStyle);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->update('#__judownload_documents AS d');
				$query->set('d.template_params = ""');
				$query->join('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id AND dxref.main = 1');
				$query->where('d.style_id = -1');
				$query->where('dxref.cat_id = ' . $categoryIdAssignedToStyle);
				$db->setQuery($query);
				$db->execute();

				JUDownloadFrontHelperTemplate::removeTemplateParamsOfInheritedStyleCatDoc($categoryIdAssignedToStyle);
			}
		}

		
		$query = $db->getQuery(true);
		$query->select('d.id');
		$query->select('dxref.cat_id AS cat_id');
		$query->from('#__judownload_documents AS d');
		$query->join('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id AND dxref.main = 1');
		$query->where('d.style_id = ' . $pk);
		$db->setQuery($query);
		$documentObjectListAssignedToStyle = $db->loadObjectList();

		$catArrayResetTemplateParams = array();
		foreach ($documentObjectListAssignedToStyle AS $documentObject)
		{
			$styleObjectOfCategory = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($documentObject->cat_id);
			if ($styleObjectOfCategory->template_id != $this->template_id)
			{
				$catArrayResetTemplateParams[] = $documentObject->cat_id;
			}
		}

		$catArrayResetTemplateParams = array_unique($catArrayResetTemplateParams);

		if (is_array($catArrayResetTemplateParams) && count($catArrayResetTemplateParams))
		{
			$query = $db->getQuery(true);
			$query->update('#__judownload_documents AS d');
			$query->join('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id AND dxref.main = 1');
			$query->set('d.template_params = ""');
			$query->set('d.style_id = -1');
			$query->where('dxref.cat_id IN (' . implode(',', $catArrayResetTemplateParams) . ')');
			$query->where('d.style_id = ' . $pk);
			$db->setQuery($query);
			$db->execute();
		}

		return parent::delete($pk);
	}
}