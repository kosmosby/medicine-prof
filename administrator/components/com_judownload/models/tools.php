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


jimport('joomla.application.component.modellist');


class JUDownloadModelTools extends JModelList
{
	########################### RESIZE IMAGES FUNCTIONS ##################################
	
	public function resizeImages()
	{
		$app           = JFactory::getApplication();
		$limitStart    = $app->input->getInt('limitstart', '0');
		$limit         = $app->input->getInt('limit', '10');
		$resizeCatImg  = $app->input->getInt('category', '0');
		$resizeDocImg  = $app->input->getInt('document', '0');
		$resizeAvatar  = $app->input->getInt('avatar', '0');
		$resizeDocIcon = $app->input->getInt('docicon', '0');
		$resizeColIcon = $app->input->getInt('collection', '0');
		$catIdArr      = $app->input->get('catlist', array(), 'array');
		$rootCat       = JUDownloadFrontHelperCategory::getRootCategory();
		if (empty($catIdArr[0]) || in_array($rootCat->id, $catIdArr))
		{
			$allChildCats = 'all';
			$docIds       = 'all';
		}
		else
		{
			$allChildCats = $this->getAllChildCats($catIdArr);
			$docIds       = $this->getDocumentIdList($allChildCats);
		}

		
		if ($resizeAvatar == 1)
		{
			$this->resizeUserAvatar($limitStart, $limit);
		}

		if ($resizeColIcon == 1)
		{
			$this->resizeCollectionIcons($limitStart, $limit);
		}

		if ($resizeCatImg == 1)
		{
			$this->resizeCategoryImages($limitStart, $limit, $allChildCats);
		}

		if ($resizeDocImg == 1)
		{
			$this->resizeDocumentImages($limitStart, $limit, $docIds);
		}

		if ($resizeDocIcon == 1)
		{
			$this->resizeDocumentIcon($limitStart, $limit, $docIds);
		}

		
		if ($limitStart == 0)
		{
			$totalDocumentImages        = $totalCatImages = $totalAvatars = $totalDocumentIcons = $totalCollectionIcons = 0;
			$totalResizedImagesEachTime = 0;
			if ($resizeAvatar == 1)
			{
				$totalAvatars = $this->getTotalAvatars();
				$totalResizedImagesEachTime += 1;
			}

			if ($resizeColIcon == 1)
			{
				$totalCollectionIcons = $this->getTotalCollectionIcons();
				$totalResizedImagesEachTime += 1;
			}

			if ($resizeDocImg == 1)
			{
				$totalDocumentImages = $this->getTotalDocumentImages($docIds);
				$totalResizedImagesEachTime += 2;
			}

			if ($resizeDocIcon == 1)
			{
				$totalDocumentIcons = $this->getTotalDocumentIcons($docIds);
				$totalResizedImagesEachTime += 1;
			}

			if (in_array($rootCat->id, $catIdArr))
			{
				if ($resizeCatImg == 1)
				{
					$totalCatImages = $this->getTotalCategoryImages();
					$totalResizedImagesEachTime += 2;
				}
			}
			else
			{
				if ($resizeCatImg == 1)
				{
					$totalCatImages = count(explode(',', $allChildCats));
					$totalResizedImagesEachTime += 2;
				}
			}

			$totalImages = $totalAvatars + $totalDocumentImages + $totalCatImages + $totalDocumentIcons + $totalCollectionIcons;
			$app->setUserState('total-images', $totalImages);
			$app->setUserState('total-resized-images-each-time', $totalResizedImagesEachTime);
		}

		$totalImages                = ($limitStart == 0) ? $totalImages : $app->getUserState('total-images');
		$totalResizedImagesEachTime = ($limitStart == 0) ? $totalResizedImagesEachTime : $app->getUserState('total-resized-images-each-time');

		
		$percent = floor(($limitStart + ($totalResizedImagesEachTime * $limit)) / $totalImages * 100);
		if (($limitStart >= ($totalImages - $limit)) || $percent >= 100 || $totalImages == 0)
		{
			$percent = 100;
		}

		echo $percent;
	}

	
	public function resizeCategoryImages($limitStart, $limit, $listCat = 'all')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, images')
			->from('#__judownload_categories')
			
			->where("LOWER(images) LIKE " . $db->quote("%.jpeg%") . " OR LOWER(images) LIKE " . $db->quote("%.jpg%") . " OR LOWER(images) LIKE " . $db->quote("%.gif%") . " OR LOWER(images) LIKE " . $db->quote("%.png%"));

		if ($listCat != 'all')
		{
			$query->where('id IN (' . $listCat . ')');
		}

		$db->setQuery($query, $limitStart, $limit);
		$categories = $db->loadObjectList();

		if (!empty($categories))
		{
			foreach ($categories AS $category)
			{

				$registry = new JRegistry;
				$registry->loadString($category->images);
				$catImg = $registry->toObject();

				if ($catImg->intro_image)
				{
					$intro_image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . 'original/' . $catImg->intro_image;
					if (JFile::exists($intro_image_path))
					{
						$image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . $catImg->intro_image;

						
						if (JFile::exists($image_path))
						{
							JFile::delete($image_path);
						}
						
						JUDownloadHelper::renderImages($intro_image_path, $image_path, 'category_intro', true, $category->id);
					}
				}

				if ($catImg->detail_image)
				{
					$detail_image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . "original/" . $catImg->intro_image;
					if (JFile::exists($detail_image_path))
					{
						$image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . $catImg->intro_image;
						
						if (JFile::exists($image_path))
						{
							JFile::delete($image_path);
						}
						
						JUDownloadHelper::renderImages($detail_image_path, $image_path, 'category_detail', true, $category->id);
					}
				}

			}
		}
	}

	
	public function resizeDocumentImages($limitStart, $limit, $docIds = 'all')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('img.doc_id,img.file_name,dx.cat_id')
			->from('#__judownload_images AS img')
			->join('', ' #__judownload_documents_xref AS dx ON img.doc_id = dx.doc_id')
			->where('dx.main = 1')
			->where("img.file_name != ''");

		if ($docIds != 'all' && $docIds)
		{
			$query->where('dx.doc_id IN (' . $docIds . ')');
		}
		$db->setQuery($query, $limitStart, $limit);
		$docs = $db->loadObjectList();

		if (!empty($docs))
		{
			foreach ($docs AS $doc)
			{
				$image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_original_image_directory", "media/com_judownload/images/gallery/original/") . $doc->doc_id . '/' . $doc->file_name;
				if (JFile::exists($image_path))
				{
					if ($doc->cat_id)
					{
						$image_path       = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_original_image_directory", "media/com_judownload/images/gallery/original/") . $doc->doc_id . '/' . $doc->file_name;
						$small_image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_small_image_directory", "media/com_judownload/images/gallery/small/") . $doc->doc_id . "/" . $doc->file_name;
						$full_image_path  = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_big_image_directory", "media/com_judownload/images/gallery/big/") . $doc->doc_id . "/" . $doc->file_name;
						
						if (JFile::exists($small_image_path))
						{
							JFile::delete($small_image_path);
						}

						if (JFile::exists($full_image_path))
						{
							JFile::delete($full_image_path);
						}

						
						JUDownloadHelper::renderImages($image_path, $small_image_path, 'document_small', true, $doc->cat_id);
						JUDownloadHelper::renderImages($image_path, $full_image_path, 'document_big', true, $doc->cat_id);
					}
				}
			}
		}
	}

	
	public function resizeDocumentIcon($limitStart, $limit, $docIds = 'all')
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		
		$query->select('d.icon, dx.cat_id')
			->from('#__judownload_documents AS d')
			->join('', '#__judownload_documents_xref AS dx ON d.id = dx.doc_id')
			->where('dx.main = 1')
			->where("d.icon != ''")
			->where("d.icon NOT LIKE 'default/%'");
		if ($docIds != 'all')
		{
			$query->where('dx.doc_id IN (' . $docIds . ')');
		}

		$db->setQuery($query, $limitStart, $limit);
		$icons        = $db->loadObjectList();
		$total_images = count($icons);

		if ($total_images)
		{
			foreach ($icons AS $icon)
			{
				$ori_image_dir = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/") . 'original/' . $icon->icon;
				if (JFile::exists($ori_image_dir))
				{
					$image_dir = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/") . $icon->icon;
					if (JFile::exists($image_dir))
					{
						JFile::delete($image_dir);
					}

					JUDownloadHelper::renderImages($ori_image_dir, $image_dir, 'document_icon', true, $icon->cat_id);
				}
			}
		}
	}

	
	public function resizeUserAvatar($limitStart, $limit)
	{
		$db    = JFactory::getDbo();
		$query = "SELECT avatar FROM #__judownload_users WHERE avatar != '' LIMIT $limitStart, $limit";
		$db->setQuery($query);
		$images = $db->loadObjectList();

		if (count($images))
		{
			foreach ($images AS $image)
			{
				$ori_image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("avatar_directory", "media/com_judownload/images/avatar/") . 'original/' . $image->avatar;
				if (JFile::exists($ori_image_path))
				{
					$image_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("avatar_directory", "media/com_judownload/images/avatar/") . $image->avatar;
					if (JFile::exists($image_path))
					{
						JFile::Delete($image_path);
					}

					JUDownloadHelper::renderImages($ori_image_path, $image_path, 'avatar');
				}
			}
		}
	}

	
	public function resizeCollectionIcons($limitStart, $limit)
	{
		$db    = JFactory::getDbo();
		$query = "SELECT icon FROM #__judownload_collections WHERE icon != ''";
		$db->setQuery($query, $limitStart, $limit);
		$images = $db->loadObjectList();

		if (count($images))
		{
			$collection_icon_path = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("collection_icon_directory", "media/com_judownload/images/collection/");
			foreach ($images AS $image)
			{
				$ori_image_path = $collection_icon_path . "original/" . $image->icon;

				if (JFile::exists($ori_image_path))
				{
					$image_path = $collection_icon_path . $image->icon;
					if (JFile::exists($image_path))
					{
						JFile::delete($image_path);
					}

					JUDownloadHelper::renderImages($ori_image_path, $image_path, 'collection');
				}
			}
		}
	}

	
	public function getTotalDocumentImages($docIds = 'all')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__judownload_images AS img')
			->join('', ' #__judownload_documents_xref AS dx ON img.doc_id = dx.doc_id')
			->where('dx.main = 1')
			->where("img.file_name != ''");

		if ($docIds != 'all')
		{
			$query->where('dx.doc_id IN (' . $docIds . ')');
		}
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public function getTotalCategoryImages()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__judownload_categories')
			
			->where("LOWER(images) LIKE " . $db->quote("%.jpeg%") . " OR LOWER(images) LIKE " . $db->quote("%.jpg%") . " OR LOWER(images) LIKE " . $db->quote("%.gif%") . " OR LOWER(images) LIKE " . $db->quote("%.png%"));
		$db->setQuery($query);
		$totalCats = $db->loadResult();

		return $totalCats;
	}

	
	public function getTotalAvatars()
	{
		$db    = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM #__judownload_users WHERE avatar != ''";
		$db->setQuery($query);
		$totalAvatars = $db->loadResult();

		return $totalAvatars;
	}

	
	public function getTotalDocumentIcons($docIds = 'all')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__judownload_documents')
			->where("icon != ''");
		if ($docIds != 'all')
		{
			$query->where('id IN (' . $docIds . ')');
		}
		$db->setQuery($query);

		$totalDocIcons = $db->loadResult();

		return $totalDocIcons;
	}

	
	public function getTotalCollectionIcons()
	{
		$db    = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM #__judownload_collections WHERE icon != ''";
		$db->setQuery($query);
		$totalCollectionIcons = $db->loadResult();

		return $totalCollectionIcons;
	}

	
	public function getCategoryList()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id', 'title'))
			->from('#__judownload_categories')
			->where('level = 1')
			->order('id ASC');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	
	public function getAllChildCats($catArr)
	{
		$childCatIds = array();
		$db          = JFactory::getDbo();
		foreach ($catArr AS $catId)
		{
			
			$catObject = JUDownloadHelper::getCategoryById($catId);

			
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__judownload_categories')
				->where('lft >= ' . $catObject->lft)
				->where('rgt <= ' . $catObject->rgt);
			$db->setQuery($query);
			$childCatIdArr = $db->loadColumn();

			$childCatIds = array_merge($childCatIds, $childCatIdArr);
		}

		$childCatIdStr = implode(',', $childCatIds);

		return $childCatIdStr;
	}

	
	public function getDocumentIdList($catListFull)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('doc_id');
		$query->from('#__judownload_documents_xref');
		$query->where('cat_id IN (' . $catListFull . ')');
		$db->setQuery($query);
		$docIdArr = $db->loadColumn();
		$docIds   = implode(',', $docIdArr);

		return $docIds;
	}

	########################### !RESIZE IMAGES FUNCTIONS  ##################################

	########################### REBUILD RATING FUNCTIONS ##################################

	
	public function getDocsForRating(array $cats, array $criteriaGroups, $limit, $start)
	{
		$docsCats = array();
		$db       = $this->getDbo();
		$rootCat  = JUDownloadFrontHelperCategory::getRootCategory();

		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_criterias_groups');
		$query->where('published != 1');
		$db->setQuery($query);
		$criteriaGroupsUnpublished = $db->loadColumn();

		if (empty($cats[0]) || in_array($rootCat->id, $cats))
		{
			$query = $db->getQuery(true);
			$query->select('id AS cat_id, parent_id, lft, rgt, level, selected_criteriagroup');
			$query->from('#__judownload_categories');

			if (!empty($criteriaGroups) && !empty($criteriaGroups[0]))
			{
				$query->where('criteriagroup_id IN (' . implode(',', $criteriaGroups) . ')');
			}

			if (!empty($criteriaGroupsUnpublished))
			{
				$query->where('criteriagroup_id NOT IN (' . implode(',', $criteriaGroupsUnpublished) . ')');
			}

			$db->setQuery($query);
			$groupCats = $db->loadObjectList();

			if (!empty($groupCats))
			{
				foreach ($groupCats AS $group)
				{
					$docsCats[] = $group->cat_id;
				}
			}
		}
		else
		{
			foreach ($cats AS $cat)
			{
				$query = $db->getQuery(true);
				$query->select('lft, rgt');
				$query->from('#__judownload_categories');
				$query->where('id = ' . $cat);
				$db->setQuery($query);
				$left_rigth = $db->loadObject();

				if ($left_rigth)
				{
					$query = $db->getQuery(true);
					$query->select('id');
					$query->from('#__judownload_categories');
					$query->where('lft >= ' . $left_rigth->lft);
					$query->where('rgt <= ' . $left_rigth->rgt);

					if (!empty($criteriaGroups) && !empty($criteriaGroups[0]))
					{
						$query->where('criteriagroup_id IN (' . implode(',', $criteriaGroups) . ')');
					}

					if (!empty($criteriaGroupsUnpublished))
					{
						$query->where('criteriagroup_id NOT IN (' . implode(',', $criteriaGroupsUnpublished) . ')');
					}

					$db->setQuery($query);
					$subCats = $db->loadColumn();

					if (!empty($subCats))
					{
						$docsCats = array_merge($docsCats, $subCats);
					}
				}
			}
		}

		if (!empty($docsCats))
		{
			$query = "SELECT doc_id FROM #__judownload_documents_xref WHERE cat_id IN (" . implode(',', $docsCats) . ") ORDER BY doc_id";

			
			if ($start == 0)
			{
				$db->setQuery($query);
				$allDocs = $db->loadColumn();

				$app = JFactory::getApplication();
				$app->setUserState('total_documents', count($allDocs));
			}

			$query .= " LIMIT $start,$limit";

			$db->setQuery($query);
			$docIds = $db->loadColumn();

			return $docIds;
		}
		else
		{
			return array();
		}

	}

	public function reBuildRating()
	{
		$app = JFactory::getApplication();

		$start = $app->input->getInt("start", 0);
		$limit = $app->input->getInt("limit", 5);

		
		if ($start == 0)
		{
			
			$cats           = $app->input->get('cats', array(), 'array');
			$criteriaGroups = $app->input->get('criteriagroups', array(), 'array');

			
			$app->setUserState('cats', $cats);
			$app->setUserState('criteria_groups', $criteriaGroups);
		}
		else
		{
			
			$cats           = $app->getUserState('cats', array());
			$criteriaGroups = $app->getUserState('criteria_groups', array());
		}

		
		$docIds = $this->getDocsForRating($cats, $criteriaGroups, $limit, $start);

		if (!empty($docIds))
		{
			foreach ($docIds AS $docId)
			{
				JUDownloadHelper::rebuildRating($docId);
			}

			$result = array(
				'processed' => count($docIds) + $start,
				'total'     => $app->getUserState('total_documents', 0)
			);
		}
		else
		{
			$result = array(
				'processed' => 100,
				'total'     => $app->getUserState('total_documents', 0)
			);
		}

		return json_encode($result);
	}

	########################### !REBUILD RATING FUNCTIONS  ##################################

	########################## IMPORT CSV FILE ###########################################
	
	

	
	public function loadCSVColumns()
	{
		$app = JFactory::getApplication();

		
		$path = $app->getUserState('file_path', '');
		
		$delimiter = $app->getUserState('delimiter', ',');
		
		$enclosure = $app->getUserState('enclosure', '"');

		if (!JFile::exists($path))
		{
			$this->setError(JText::sprintf("COM_JUDOWNLOAD_IMPORT_IMAGE_FILE_S_NOT_FOUND", $path));

			return false;
		}

		
		$rows = JUDownloadHelper::getCSVData($path, $delimiter, $enclosure, 'r+', 0, null, true);

		$csvColumnRows = array_shift($rows);

		$totalCsvRow = count($rows);
		$app->setUserState('csv_total_row', $totalCsvRow);

		return $csvColumnRows;
	}

	
	

	

	

	
	

	
	

	

	

	

	

	


	
	


	


	##################################### END OF IMPORT TOOL ######################################

	public function getCriteriaGroups()
	{
		$db = $this->getDbo();
		$db->setQuery("SELECT id, name FROM #__judownload_criterias_groups WHERE published = 1");
		$criteriaGroups = $db->loadObjectList();

		if (!empty($criteriaGroups))
		{
			foreach ($criteriaGroups AS $key => $criteriaGroup)
			{
				$criteriaGroups[$key] = "|—" . $criteriaGroup->name;
			}
			array_unshift($criteriaGroups, array('id' => '', 'name' => JText::_('JALL')));

			return $criteriaGroups;
		}

		return array();
	}

	public function deleteImportFilePath()
	{
		$app            = JFactory::getApplication();
		$importFilePath = $app->getUserState('file_path');
		if ($importFilePath && JFile::exists($importFilePath))
		{
			$folderPath = dirname($importFilePath);
			if (JFolder::exists($folderPath))
			{
				JFolder::delete($folderPath);
			}
		}
	}

	public function rebuildCommentTree()
	{
		$app        = JFactory::getApplication();
		$limit      = $app->input->get('limit', 10);
		$limitStart = $app->input->get('limitstart', 0);
		$left       = $app->input->get('lft', 2);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_comments');
		$query->order('lft');
		$query->where('level = 1');
		$db->setQuery($query, $limitStart, $limit);
		$commentIds = $db->loadColumn();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$table = JTable::getInstance('Comment', 'JUDownloadTable');
		foreach ($commentIds AS $commentId)
		{
			$left = $table->rebuild($commentId, $left, 1);
		}

		$return             = array();
		$totalCommentLevel1 = self::getTotalCommentLevel1();
		if (!$commentIds || ($limitStart + $limit) > $totalCommentLevel1)
		{
			$return['percent'] = 100;
			$return['lft']     = $left;
			if ($commentIds)
			{
				$query->clear();
				$query->update('#__judownload_comments');
				$query->set('rgt = ' . $left);
				$query->where('level = 0 AND id = 1');
				$db->setQuery($query);
				$db->execute();
			}
		}
		else
		{
			$return['percent'] = round((($limitStart + $limit) / $totalCommentLevel1) * 100);
			$return['lft']     = $left;
		}

		return json_encode($return);
	}

	public function getTotalCommentLevel1()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(1)');
		$query->from('#__judownload_comments');
		$query->order('lft');
		$query->where('level = 1');
		$db->setQuery($query);

		return $db->loadResult();
	}
}
