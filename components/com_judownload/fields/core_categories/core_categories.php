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

class JUDownloadFieldCore_categories extends JUDownloadFieldBase
{
	protected $field_name = 'cat_id';
	protected $fieldvalue_column = 'c.title';

	protected function getValue()
	{
		$app = JFactory::getApplication();
		
		if (isset($this->doc->cat_ids) && isset($this->doc->cat_titles) && !is_null($this->doc->cat_ids) && !is_null($this->doc->cat_titles))
		{
			$categories = array();

			$catIdArr    = explode(",", $this->doc->cat_ids);
			$catTitleArr = explode("|||", $this->doc->cat_titles);
			foreach ($catIdArr AS $key => $catId)
			{
				$category        = new stdClass();
				$category->id    = $catIdArr[$key];
				$category->title = $catTitleArr[$key];
				if ($key == 0)
				{
					$category->main = 1;
				}
				else
				{
					$category->main = 0;
				}
				$categories[] = $category;
			}
			$value = $categories;
		}
		else
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select("c.id, c.title, c.parent_id, dxref.main");
			$query->from("#__judownload_categories AS c");
			$query->join("", "#__judownload_documents_xref AS dxref ON (c.id = dxref.cat_id)");
			$query->where("dxref.doc_id = " . $this->doc_id);
			if ($app->isSite())
			{
				
				$categoryIdArrayCanAccess = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
				if (is_array($categoryIdArrayCanAccess) && count($categoryIdArrayCanAccess) > 0)
				{
					$query->where('c.id IN(' . implode(",", $categoryIdArrayCanAccess) . ')');
				}
				else
				{
					$query->where('c.id IN("")');
				}
			}
			$query->order("dxref.main DESC, dxref.ordering ASC");
			$db->setQuery($query);
			$categories = $db->loadObjectList();
			$value      = $categories;
		}

		return $value;
	}

	public function getLabel($required = true)
	{
		parent::getLabel();

		return $this->fetch('label.php', __CLASS__);
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (!$this->value)
		{
			return "";
		}

		$this->setVariable('value', $this->value);

		return $this->fetch('output.php', __CLASS__);
	}

	public function getBackendOutput()
	{
		$categories = $this->value;
		$html       = array();
		if ($categories)
		{
			foreach ($categories AS $category)
			{
				$html[] = '<a href="index.php?option=com_judownload&view=listcats&cat_id=' . $category->id . '">' . $category->title . '</a>';
			}
		}

		return implode(", ", $html);
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '</span>';
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if ((JUDownloadHelper::getDocumentSubmitType($this->doc_id) == 'submit' && $this->canSubmit())
			|| (JUDownloadHelper::getDocumentSubmitType($this->doc_id) == 'edit' && $this->canEdit())
		)
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$document = JFactory::getDocument();
		$rootCat  = JUDownloadFrontHelperCategory::getRootCategory();
		JText::script('COM_JUDOWNLOAD_TOTAL_CATS_OVER_MAXIMUM_N_CATS');
		JText::script('COM_JUDOWNLOAD_CATEGORY_X_ALREADY_EXIST');
		$app = JFactory::getApplication();
		
		if (isset($this->doc) && $this->doc->cat_id)
		{
			$params = JUDownloadHelper::getParams($this->doc->cat_id);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $this->doc_id);
		}

		$db              = JFactory::getDbo();
		$docId           = $this->doc_id;
		$documentObject  = JUDownloadHelper::getDocumentById($docId);
		$secondaryCatIds = $secondaryCatIdsStr = "";

		
		if ($fieldValue && !empty($fieldValue['main']))
		{
			$categoryId = (int) $fieldValue['main'];
			if ($fieldValue['secondary'])
			{
				$secondaryCatIdsStr = $fieldValue['secondary'];
				$secondaryCatIds    = explode(",", $secondaryCatIdsStr);
			}
			$query = $db->getQuery(true);
			$query->select("c.id, c.parent_id");
			$query->from("#__judownload_categories AS c");
			$query->select("field_group.id AS fieldgroup_id, field_group.name AS fieldgroup_name");
			$query->join("LEFT", "#__judownload_fields_groups AS field_group ON (field_group.id = c.fieldgroup_id AND field_group.published = 1)");
			$query->where("c.id = " . $categoryId);
			$db->setQuery($query);
			$mainCategory = $db->loadObject();
		}
		
		elseif ($docId)
		{
			$categories = $this->value;
			foreach ($categories AS $category)
			{
				if ($category->main == 1)
				{
					$mainCategory = $category;

					$query = $db->getQuery(true);
					$query->select("field_group.id, field_group.name");
					$query->from("#__judownload_fields_groups AS field_group");
					$query->join("", "#__judownload_categories AS c on c.fieldgroup_id = field_group.id");
					$query->where("c.id = " . $mainCategory->id);
					$query->where("field_group.published = 1");
					$db->setQuery($query);
					$fieldGroup = $db->loadObject();
					if (is_object($fieldGroup))
					{
						$mainCategory->fieldgroup_name = $fieldGroup->name;
						$mainCategory->fieldgroup_id   = $fieldGroup->id;
					}
					else
					{
						$mainCategory->fieldgroup_name = null;
						$mainCategory->fieldgroup_id   = null;
					}
				}
				else
				{
					$secondaryCatIds[] = $category->id;
				}
			}

			if ($secondaryCatIds)
			{
				$secondaryCatIdsStr = implode(",", $secondaryCatIds);
			}
		}
		
		elseif ($app->input->getInt('cat_id'))
		{
			$categoryId   = $app->input->getInt('cat_id');
			$mainCategory = JUDownloadHelper::getCategoryById($categoryId);
			$query        = "SELECT id, name FROM #__judownload_fields_groups WHERE id= " . $mainCategory->fieldgroup_id . " AND published = 1";
			$db->setQuery($query);
			$fieldGroup = $db->loadObject();
			if (is_object($fieldGroup))
			{
				$mainCategory->fieldgroup_name = $fieldGroup->name;
				$mainCategory->fieldgroup_id   = $fieldGroup->id;
			}
			else
			{
				$mainCategory->fieldgroup_name = null;
				$mainCategory->fieldgroup_id   = null;
			}
		}
		
		else
		{
			$mainCategory                  = new stdClass();
			$mainCategory->id              = '';
			$mainCategory->parent_id       = $rootCat->id;
			$mainCategory->fieldgroup_name = null;
			$mainCategory->fieldgroup_id   = null;
		}

		$document->addStyleSheet(JUri::root() . "components/com_judownload/fields/" . $this->folder . "/core_categories.css");

		if (!$disabled)
		{
			$document->addScript(JUri::root() . "components/com_judownload/fields/" . $this->folder . "/core_categories.js");

			if (JUDownloadHelper::isJoomla3x())
			{
				$jsIsJoomla3x = 1;
			}
			else
			{
				$jsIsJoomla3x = 0;
			}

			$script = "jQuery(document).ready(function($){
								$('.category_selection').docChangeCategory({
									doc_id: '" . $docId . "',
									is_joomla_3x: '" . $jsIsJoomla3x . "',
									main_cat_id: '" . $mainCategory->id . "',
									fieldgroup_id: '" . $mainCategory->fieldgroup_id . "',
									fieldgroup_name : '" . $mainCategory->fieldgroup_name . "',
									max_cats : " . (int) 1 . "
								});
						});";

			$document->addScriptDeclaration($script);
		}

		$this->addAttribute("class", "categories", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('mainCategory', $mainCategory);
		$this->setVariable('secondaryCatIds', $secondaryCatIds);
		$this->setVariable('documentObject', $documentObject);
		$this->setVariable('disabled', $disabled);
		$this->setVariable('secondaryCatIdsStr', $secondaryCatIdsStr);
		$this->setVariable('rootCat', $rootCat);
		$this->setVariable('params', $params);

		return $this->fetch('input.php', __CLASS__);
	}

	public function PHPValidate($values)
	{
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		
		if (isset($this->doc) && $this->doc->cat_id)
		{
			$params = JUDownloadHelper::getParams($this->doc->cat_id);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $this->doc_id);
		}

		$mainCatId       = $values['main'];
		$secondaryCatIds = array_filter(explode(",", $values['secondary']));

		if (!$mainCatId)
		{
			return JText::_("COM_JUDOWNLOAD_PLEASE_SELECT_A_CATEGORY");
		}

		if ($mainCatId == $rootCat->id && !$params->get('allow_add_doc_to_root', 0))
		{
			return JText::_("COM_JUDOWNLOAD_CAN_NOT_ADD_DOCUMENT_TO_ROOT_CATEGORY");
		}

		if (!JUDownloadHelper::getCategoryById($mainCatId))
		{
			return JText::_("COM_JUDOWNLOAD_INVALID_CATEGORY");
		}

		if (1 && (count($secondaryCatIds) + 1 > 1))
		{
			return JText::sprintf("COM_JUDOWNLOAD_NUMBER_OF_CATEGORY_OVER_MAX_N_CATEGORIES", 1);
		}

		if (!$this->doc_id)
		{
			
			if (!JUDownloadFrontHelperPermission::canSubmitDocument($mainCatId))
			{
				$category = JUDownloadHelper::getCategoryById($mainCatId);

				return JText::sprintf("COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_SUBMIT_DOCUMENT_TO_THIS_CATEGORY", $category->title);
			}
		}
		else
		{
			$mainCatIdDB = JUDownloadFrontHelperCategory::getMainCategoryId($this->doc_id);

			
			if ($mainCatId != $mainCatIdDB)
			{
				
				if (!JUDownloadFrontHelperPermission::canSubmitDocument($mainCatId))
				{
					$category = JUDownloadHelper::getCategoryById($mainCatId);

					return JText::sprintf("COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_SUBMIT_DOCUMENT_TO_THIS_CATEGORY", $category->title);
				}
			}

			$app = JFactory::getApplication();
			
			if ($app->isSite())
			{
				
				if ($mainCatId != $mainCatIdDB)
				{
					if (!$params->get('can_change_main_category', 1))
					{
						return false;
					}
				}

				
				if (!$params->get('can_change_secondary_categories', 1))
				{
					$secondaryCatIdsDB = $this->getSecondaryCategoryIds($this->doc_id);
					if (count($secondaryCatIds) && count($secondaryCatIdsDB))
					{
						if (array_diff($secondaryCatIds, $secondaryCatIdsDB) || array_diff($secondaryCatIdsDB, $secondaryCatIds))
						{
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public function filterField($value)
	{
		$secondaryCatIds = explode(",", $value['secondary']);
		$secondaryCatIds = array_unique($secondaryCatIds);

		
		if (is_array($secondaryCatIds) && count($secondaryCatIds) > 0)
		{
			$secondaryCatIdsDB = $this->getSecondaryCategoryIds($this->doc_id);

			foreach ($secondaryCatIds AS $i => $secondaryCatId)
			{
				
				if (!in_array($secondaryCatId, $secondaryCatIdsDB))
				{
					
					if (!JUDownloadFrontHelperPermission::canSubmitDocument($secondaryCatId))
					{
						unset($secondaryCatIds[$i]);
					}
				}
			}
		}

		$value['secondary'] = implode(",", $secondaryCatIds);

		return $value;
	}

	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$db = JFactory::getDbo();

		$mainCatId       = $value['main'];
		$secondaryCatIds = explode(",", $value['secondary']);
		$secondaryCatIds = array_unique($secondaryCatIds);

		if (is_array($secondaryCatIds) && count($secondaryCatIds) > 0)
		{
			foreach ($secondaryCatIds AS $i => $secondaryCatId)
			{
				if (!is_numeric($secondaryCatId) || $secondaryCatId <= 0 || $secondaryCatId == $mainCatId)
				{
					unset($secondaryCatIds[$i]);
				}
			}
		}

		if (!isset($this->doc->cat_id) || !$this->doc->cat_id)
		{
			
			$saveMainCat = JUDownloadHelper::addCategory($this->doc_id, $mainCatId, 1);
			if (!$saveMainCat)
			{
				return false;
			}
		}
		else
		{
			
			if ($this->doc->cat_id != $mainCatId)
			{
				$query = "UPDATE #__judownload_documents_xref SET cat_id = " . $mainCatId . " WHERE doc_id= " . $this->doc_id . " AND main = 1";
				$db->setQuery($query);
				$db->execute();
			}

			
			
			$secondaryCatIdsDB     = $this->getSecondaryCategoryIds($this->doc_id);
			$removeSecondaryCatIds = array_diff($secondaryCatIdsDB, $secondaryCatIds);
			if ($removeSecondaryCatIds)
			{
				$query = "DELETE FROM #__judownload_documents_xref WHERE (doc_id= " . $this->doc_id . " AND main=0 AND cat_id IN (" . implode(",", $removeSecondaryCatIds) . "))";
				$db->setQuery($query);
				$db->execute();
			}
		}

		
		foreach ($secondaryCatIds AS $key => $catId)
		{
			$query = "SELECT id FROM #__judownload_documents_xref WHERE doc_id = " . $this->doc_id . " AND cat_id = $catId AND main = 0";
			$db->setQuery($query);
			$itemId = $db->loadResult();
			if ($itemId)
			{
				$query = "UPDATE #__judownload_documents_xref SET ordering = " . ($key + 1) . " WHERE id = " . $itemId;
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				
				JUDownloadHelper::addCategory($this->doc_id, $catId, 0, $key + 1);
			}
		}

		

		return true;
	}

	
	public function canSubmit($userID = null)
	{
		return true;
	}

	

	protected function getTotalCategories()
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		if ($app->isSite())
		{
			$query = "SELECT COUNT(*) FROM #__judownload_categories WHERE published = 1";
		}
		else
		{
			$query = "SELECT COUNT(*) FROM #__judownload_categories";
		}
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	protected function getSecondaryCategoryIds($documentId)
	{
		if (!$documentId)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('cat_id');
		$query->from('#__judownload_documents_xref');
		$query->where('doc_id = ' . $documentId);
		$query->where('main = 0');
		$db->setQuery($query);
		$catIds = $db->loadColumn();
		if ($catIds)
		{
			return $catIds;
		}
		else
		{
			return array();
		}
	}

	protected function getChildCategoryOptions($parentCatId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title, id, published, parent_id');
		$query->from('#__judownload_categories');
		$query->where('parent_id = ' . (int) $parentCatId);
		$query->order('lft');
		$db->setQuery($query);
		$categoryObjectList = $db->loadObjectList();
		foreach ($categoryObjectList AS $key => $cat)
		{
			$canSubmitDocument = JUDownloadFrontHelperPermission::canSubmitDocument($cat->id);

			
			if (!$canSubmitDocument)
			{
				unset($categoryObjectList[$key]);
				continue;
			}

			
			if ($cat->published != 1)
			{
				$categoryObjectList[$key]->title = "[" . $cat->title . "]";
			}
		}

		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();

		
		if (isset($this->doc) && $this->doc->cat_id)
		{
			$params = JUDownloadHelper::getParams($this->doc->cat_id);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $this->doc_id);
		}

		if ($parentCatId != 0 && ($parentCatId != $rootCat->id || ($parentCatId == $rootCat->id && $params->get('allow_add_doc_to_root', 0))))
		{
			$catParent = JUDownloadHelper::getCategoryByID($parentCatId);
			array_unshift($categoryObjectList, JHtml::_('select.option', $catParent->parent_id, JText::_('COM_JUDOWNLOAD_BACK_TO_PARENT_CATEGORY'), 'id', 'title'));
		}

		return $categoryObjectList;
	}

	protected function getAllCategoryOptions()
	{
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
		$categoryTable = JTable::getInstance('Category', 'JUDownloadTable');
		$categoryTree  = $categoryTable->getTree($rootCat->id);

		foreach ($categoryTree AS $key => $cat)
		{
			$canSubmitDocument = JUDownloadFrontHelperPermission::canSubmitDocument($cat->id);

			
			if (!$canSubmitDocument)
			{
				unset($categoryTree[$key]);
				continue;
			}

			
			if ($cat->published != 1)
			{
				$categoryTree[$key]->title = "[" . $cat->title . "]";
			}

			$categoryTree[$key]->title = str_repeat('|—', $cat->level) . $categoryTree[$key]->title;
		}

		
		if (isset($this->doc) && $this->doc->cat_id)
		{
			$params = JUDownloadHelper::getParams($this->doc->cat_id);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $this->doc_id);
		}

		if ($params->get('allow_add_doc_to_root', 0))
		{
			array_unshift($categoryTree, JHtml::_('select.option', $rootCat->id, JText::_('COM_JUDOWNLOAD_ROOT'), 'id', 'title'));
		}

		return $categoryTree;
	}

	public function orderingPriority(&$query = null)
	{
		return array('ordering' => 'c.title', 'direction' => $this->priority_direction);
	}
}

?>