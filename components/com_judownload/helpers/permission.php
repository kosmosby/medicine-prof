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

class JUDownloadFrontHelperPermission
{
	
	protected static $cache = array();

	
	public static function canDoCategory($categoryId, $checkAccess = false, &$error = array())
	{
		if (!$categoryId)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::$categoryId::" . (int) $checkAccess);

		
		$storeId_AccessibleCategoryIds = md5(__CLASS__ . '::AccessibleCategoryIds');
		if (isset(self::$cache[$storeId_AccessibleCategoryIds]))
		{
			$categoryIdArrayCanAccess = self::$cache[$storeId_AccessibleCategoryIds];
			
			if (!empty($categoryIdArrayCanAccess) && in_array($categoryId, $categoryIdArrayCanAccess))
			{
				self::$cache[$storeId] = true;

				return self::$cache[$storeId];
			}
			else
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}
		}

		if (!isset(self::$cache[$storeId]))
		{
			
			if (!$categoryId)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$path = JUDownloadHelper::getCategoryPath($categoryId);

			
			if (!$path)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$user    = JFactory::getUser();
			$levels  = $user->getAuthorisedViewLevels();
			$nowDate = JFactory::getDate()->toSql();

			
			foreach ($path AS $category)
			{
				
				if ($category->published != 1)
				{
					$error                 = array("code" => 404, "message" => JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				if ($category->publish_up > $nowDate || (intval($category->publish_down) > 0 && $category->publish_down < $nowDate))
				{
					$error                 = array("code" => 404, "message" => JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				
				if ($checkAccess && !in_array($category->access, $levels))
				{
					$error                 = array("code" => 403, "message" => JText::_('JERROR_ALERTNOAUTHOR'));
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			self::$cache[$storeId] = true;
		}

		return self::$cache[$storeId];
	}

	
	public static function isDocumentOwner($documentId)
	{
		if (!$documentId)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::$documentId");
		if (!isset(self::$cache[$storeId]))
		{
			$documentObject = JUDownloadHelper::getDocumentById($documentId);
			
			if (!is_object($documentObject))
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$user = JFactory::getUser();
			if (!$user->get('guest') && $user->id == $documentObject->created_by)
			{
				self::$cache[$storeId] = true;
			}
			else
			{
				self::$cache[$storeId] = false;
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function canSubmitDocument($categoryId = null)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return true;
		}

		return false;
	}

	public static function canSubmitDocumentInCat($categoryId)
	{

		
		$canDoCategory = JUDownloadFrontHelperPermission::canDoCategory($categoryId, true);
		if (!$canDoCategory)
		{
			return false;
		}

		
		if ($categoryId == 1)
		{
			$params = JUDownloadHelper::getParams($categoryId);
			if (!$params->get('allow_add_doc_to_root'))
			{
				return false;
			}
		}

		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			$modCanSubmitDoc = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($categoryId, 'document_create');
			if ($modCanSubmitDoc)
			{
				return true;
			}
		}

		
		$user = JFactory::getUser();
		if ($user->authorise('judl.document.create', 'com_judownload.category.' . $categoryId))
		{
			return true;
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('judownload');
		$pluginTriggerResults = $dispatcher->trigger('canSubmitDocument', array($categoryId));

		if (in_array(true, $pluginTriggerResults, true))
		{
			return true;
		}
	}

	
	public static function canCheckInDocument($documentId)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
		$documentTable = JTable::getInstance('Document', 'JUDownloadTable');
		$documentTable->load($documentId);

		if (property_exists($documentTable, 'checked_out') && property_exists($documentTable, 'checked_out_time') && $documentTable->checked_out > 0)
		{
			$user            = JFactory::getUser();
			$isModerator     = JUDownloadFrontHelperModerator::isModerator();
			$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
			
			if ($isModerator || $isDocumentOwner || $documentTable->checked_out == $user->id)
			{
				$canEditDocument      = JUDownloadFrontHelperPermission::canEditDocument($documentId);
				$canEditStateDocument = JUDownloadFrontHelperPermission::canEditStateDocument($documentTable);
				if ($canEditDocument || $canEditStateDocument)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	public static function canEditDocument($documentId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		if (!is_object($documentObject))
		{
			return false;
		}

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return true;
		}

		return false;
	}

	
	public static function canEditStateDocument($documentObject)
	{
		if (!is_object($documentObject))
		{
			return false;
		}
		$documentId = $documentObject->id;

		if (!isset($documentObject->cat_id))
		{
			$documentObject = JUDownloadHelper::getDocumentById($documentId);
		}

		$mainCatId = $documentObject->cat_id;

		
		$userCanDoCategory = JUDownloadFrontHelperPermission::canDoCategory($mainCatId);
		if (!$userCanDoCategory)
		{
			return false;
		}

		if ($documentObject->id)
		{
			
			$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentObject->id);
			if ($isDocumentOwner)
			{
				$params                    = JUDownloadHelper::getParams($mainCatId);
				$ownerCanEditStateDocument = $params->get('document_owner_can_edit_state_document', 0);
				if ($ownerCanEditStateDocument)
				{
					return true;
				}
			}
		}

		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			$modCanEditState = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCatId, 'document_edit_state');
			if ($modCanEditState)
			{
				return true;
			}

			if ($documentObject->id && $documentObject->approved <= 0)
			{
				$modCanApprove = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCatId, 'document_approve');
				if ($modCanApprove)
				{
					return true;
				}
			}
		}

		
		$user = JFactory::getUser();
		if (!$user->get('guest'))
		{
			$corePublished = JUDownloadFrontHelperField::getField('published', $documentObject);
			if ($corePublished)
			{
				if ($documentObject->approved <= 0)
				{
					if ($corePublished->canSubmit())
					{
						return true;
					}
				}
				elseif ($documentObject->approved == 1)
				{
					if ($corePublished->canEdit())
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	
	public static function canDeleteDocument($documentId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		if (!is_object($documentObject))
		{
			return false;
		}

		
		$userCanDoCategory = JUDownloadFrontHelperPermission::canDoCategory($documentObject->cat_id);
		if (!$userCanDoCategory)
		{
			return false;
		}

		
		$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);

		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			$modCanDelete = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($documentObject->cat_id, 'document_delete');
			if ($modCanDelete)
			{
				return true;
			}

			if ($isDocumentOwner)
			{
				$modCanDeleteOwn = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($documentObject->cat_id, 'document_delete_own');
				if ($modCanDeleteOwn)
				{
					return true;
				}
			}
		}

		
		$user = JFactory::getUser();
		if (!$user->get('guest'))
		{
			$asset = 'com_judownload.document.' . $documentObject->id;
			
			if ($user->authorise('judl.document.delete', $asset))
			{
				return true;
			}

			
			if ($isDocumentOwner && $user->authorise('judl.document.delete.own', $asset))
			{
				return true;
			}
		}

		return false;
	}

	
	public static function userCanDoDocument($documentId, $checkAccess = false)
	{
		$nowDate = JFactory::getDate()->toSql();
		if (!$documentId)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::$documentId::" . (int) $checkAccess);
		if (!isset(self::$cache[$storeId]))
		{
			$documentObject = JUDownloadHelper::getDocumentById($documentId);

			if (!is_object($documentObject))
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->approved != 1)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->published != 1)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->publish_up > $nowDate)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->publish_down != '0000-00-00 00:00:00' && $documentObject->publish_down < $nowDate)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$user   = JFactory::getUser();
			$levels = $user->getAuthorisedViewLevels();

			if ($user->get('guest'))
			{
				if (!in_array($documentObject->access, $levels))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}
			else
			{
				if (!in_array($documentObject->access, $levels) && $documentObject->created_by != $user->id)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			$canDoCat = JUDownloadFrontHelperPermission::canDoCategory($documentObject->cat_id);
			if (!$canDoCat)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = true;
		}

		return self::$cache[$storeId];
	}

	
	public static function canViewDocument($documentId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		if (!is_object($documentObject))
		{
			return false;
		}

		$canDoCategory = JUDownloadFrontHelperPermission::canDoCategory($documentObject->cat_id, true);

		if (!$canDoCategory)
		{
			return false;
		}
		
		if ($documentObject->approved == 1)
		{
			$canEditDocument      = JUDownloadFrontHelperPermission::canEditDocument($documentId);
			$canEditStateDocument = JUDownloadFrontHelperPermission::canEditStateDocument($documentObject);
			$userCanDoDocument    = JUDownloadFrontHelperPermission::userCanDoDocument($documentId, true);
			if ($canEditDocument || $canEditStateDocument || $userCanDoDocument)
			{
				return true;
			}
		}

		$isDocumentPublished = JUDownloadFrontHelperDocument::isDocumentPublished($documentId);

		
		$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		if ($isDocumentOwner)
		{
			$params = JUDownloadHelper::getParams(null, $documentId);
			
			if ($documentObject->approved <= 0 || $isDocumentPublished || (!$isDocumentPublished && $params->get('document_owner_can_view_unpublished_document', 0)))
			{
				return true;
			}
		}

		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			if ($documentObject->approved == 1)
			{
				if ($isDocumentPublished)
				{
					
					$modCanViewDocument = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($documentObject->cat_id, 'document_view');
					if ($modCanViewDocument)
					{
						return true;
					}
				}
				else
				{
					
					$modCanViewDocument = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($documentObject->cat_id, 'document_view_unpublished');
					if ($modCanViewDocument)
					{
						return true;
					}
				}
			}
			else
			{
				
				$modCanViewDocument = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($documentObject->cat_id, 'document_approve');
				if ($modCanViewDocument)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	

	
	public static function canAutoApprovalDocumentWhenSubmit($mainCategoryId)
	{
		$user         = JFactory::getUser();
		$mainCategory = JUDownloadFrontHelperCategory::getCategory($mainCategoryId);
		if (!is_object($mainCategory))
		{
			return false;
		}

		
		if ($user->authorise('judl.document.create.auto_approval', 'com_judownload.category.' . $mainCategory->id))
		{
			return true;
		}

		
		
		if (!$user->get('guest'))
		{
			$params                        = JUDownloadHelper::getParams($mainCategoryId);
			$autoApprovalDocumentThreshold = (int) $params->get('auto_approval_document_threshold', 0);
			if ($autoApprovalDocumentThreshold > 0)
			{
				$totalApprovedDocumentsOfUser = JUDownloadFrontHelperDocument::getTotalDocumentsOfUserApprovedByMod($user->id);
				if ($totalApprovedDocumentsOfUser >= $autoApprovalDocumentThreshold)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	public static function canAutoApprovalDocumentWhenEdit($documentId, $newMainCategoryId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);

		if ($documentObject->approved == 1)
		{
			$originalDocumentId     = $documentObject->id;
			$originalDocumentObject = $documentObject;
			$mainCategoryId         = JUDownloadFrontHelperCategory::getMainCategoryId($originalDocumentObject->id);
			$params                 = JUDownloadHelper::getParams($newMainCategoryId);
		}
		elseif ($documentObject->approved < 0)
		{
			$tempDocumentObject     = $documentObject;
			$originalDocumentId     = abs($documentObject->approved);
			$originalDocumentObject = JUDownloadHelper::getDocumentById($originalDocumentId);
			$mainCategoryId         = JUDownloadFrontHelperCategory::getMainCategoryId($originalDocumentObject->id);
			$params                 = JUDownloadHelper::getParams($newMainCategoryId);
		}
		else
		{
			return false;
		}

		
		$isDocumentOwner              = JUDownloadFrontHelperPermission::isDocumentOwner($originalDocumentObject->id);
		$autoApprovalForDocumentOwner = $params->get('document_owner_can_edit_document_auto_approval', 1);
		if ($isDocumentOwner && $autoApprovalForDocumentOwner)
		{
			return true;
		}

		$user = JFactory::getUser();

		
		if ($mainCategoryId == $newMainCategoryId)
		{
			if ($user->authorise('judl.document.edit.auto_approval', 'com_judownload.category.' . $mainCategoryId))
			{
				return true;
			}
		}
		else
		{
			if ($user->authorise('judl.document.create.auto_approval', 'com_judownload.category.' . $newMainCategoryId))
			{
				return true;
			}
		}

		
		
		if (!$user->get('guest'))
		{
			$autoApprovalDocumentThreshold = (int) $params->get('auto_approval_document_threshold', 0);
			if ($autoApprovalDocumentThreshold > 0)
			{
				$totalApprovedDocumentsOfUser = JUDownloadFrontHelperDocument::getTotalDocumentsOfUserApprovedByMod($user->id);
				if ($totalApprovedDocumentsOfUser >= $autoApprovalDocumentThreshold)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	public static function canDownloadDocument($documentId, $checkPassword = true)
	{
		if (!$documentId)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::$documentId::" . (int) $checkPassword);
		if (!isset(self::$cache[$storeId]))
		{
			
			require_once JPATH_SITE . '/components/com_judownload/models/download.php';
			JModelLegacy::addIncludePath(JPath::clean(JPATH_SITE . '/components/com_judownload/models'));
			$downloadModel         = JModelLegacy::getInstance('Download', 'JUDownloadModel');
			self::$cache[$storeId] = $downloadModel->canDownloadDocument($documentId, $checkPassword);
		}

		return self::$cache[$storeId];
	}

	
	public static function canRateDocument($documentId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		if (!is_object($documentObject))
		{
			return false;
		}

		$params = JUDownloadHelper::getParams(null, $documentId);
		if (!$params->get('enable_document_rate', 1))
		{
			return false;
		}

		$userCanViewDocument = JUDownloadFrontHelperPermission::userCanDoDocument($documentId, true);
		if (!$userCanViewDocument)
		{
			return false;
		}

		$ratingField = new JUDownloadFieldCore_rating();
		if (!$ratingField->canView())
		{
			return false;
		}

		$user            = JFactory::getUser();
		$criteriaGroupId = JUDownloadFrontHelperCriteria::getCriteriaGroupIdByCategoryId($documentObject->cat_id);
		if ($criteriaGroupId == 0 || !JUDownloadHelper::hasMultiRating())
		{
			$assetName = 'com_judownload.category.' . $documentObject->cat_id;
			
			if ($user->authorise('judl.single.rate', $assetName) || (JUDownloadFrontHelperPermission::canDownloadDocument($documentId) && $params->get('can_download_can_rate', 0)))
			{
				if ($user->authorise('judl.single.rate.many_times', $assetName))
				{
					return true;
				}
				else
				{
					
					if ($user->get('guest'))
					{
						$session = JFactory::getSession();
						if (!$session->has('judl-document-rated-' . $documentId))
						{
							return true;
						}
					}
					
					else
					{
						$totalVoteTimes = JUDownloadFrontHelperRating::getTotalDocumentVotesOfUser($user->id, $documentId);
						if ($totalVoteTimes == 0)
						{
							return true;
						}
					}
				}
			}
		}
		else
		{
			$assetName = 'com_judownload.criteriagroup.' . $criteriaGroupId;
			
			if ($user->authorise('judl.criteria.rate', $assetName) || (JUDownloadFrontHelperPermission::canDownloadDocument($documentId) && $params->get('can_download_can_rate', 0)))
			{
				if ($user->authorise('judl.criteria.rate.many_times', $assetName))
				{
					return true;
				}
				else
				{
					
					if ($user->get('guest'))
					{
						$session = JFactory::getSession();
						if (!$session->has('judl-document-rated-' . $documentId))
						{
							return true;
						}
					}
					
					else
					{
						$totalVoteTimes = JUDownloadFrontHelperRating::getTotalDocumentVotesOfUser($user->id, $documentId);
						if ($totalVoteTimes == 0)
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	
	public static function canReportDocument($documentId)
	{
		return false;
	}

	
	public static function canContactDocument($documentId)
	{
		return false;
	}

	
	public static function canUploadFromUrl($documentId = null)
	{
		return false;
	}

	
	public static function canUpload(&$file, &$error = array(), $legal_extensions, $max_size = 0, $check_mime = false, $allowed_mime = '', $ignored_extensions = '', $image_extensions = 'bmp,gif,jpg,jpeg,png')
	{
		
		if (empty($file['name']))
		{
			isset($error['WARN_SOURCE']) ? $error['WARN_SOURCE']++ : $error['WARN_SOURCE'] = 1;

			return false;
		}

		jimport('joomla.filesystem.file');

		
		if (str_replace(' ', '', $file['name']) != $file['name'] || $file['name'] !== JFile::makeSafe($file['name']))
		{
			isset($error['WARN_FILENAME']) ? $error['WARN_FILENAME']++ : $error['WARN_FILENAME'] = 1;

			return false;
		}

		
		$executable = array(
			'php', 'js', 'exe', 'phtml', 'java', 'perl', 'py', 'asp', 'dll', 'go', 'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb', 'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);

		$legal_extensions   = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $legal_extensions))));
		$ignored_extensions = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $ignored_extensions))));

		$format = strtolower(JFile::getExt($file['name']));
		
		if ($format == '' || $format == false || (!in_array($format, $legal_extensions)) || in_array($format, $executable))
		{
			isset($error['WARN_FILETYPE']) ? $error['WARN_FILETYPE']++ : $error['WARN_FILETYPE'] = 1;

			return false;
		}

		
		if ($max_size > 0 && (int) $file['size'] > $max_size)
		{
			isset($error['WARN_FILETOOLARGE']) ? $error['WARN_FILETOOLARGE']++ : $error['WARN_FILETOOLARGE'] = 1;

			return false;
		}

		
		if ($check_mime)
		{
			$image_extensions = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $image_extensions))));

			
			if (in_array($format, $image_extensions))
			{
				
				
				if (!empty($file['tmp_name']))
				{
					if (($imginfo = getimagesize($file['tmp_name'])) === false)
					{
						isset($error['WARN_INVALID_IMG']) ? $error['WARN_INVALID_IMG']++ : $error['WARN_INVALID_IMG'] = 1;

						return false;
					}
				}
				else
				{
					isset($error['WARN_FILETOOLARGE']) ? $error['WARN_FILETOOLARGE']++ : $error['WARN_FILETOOLARGE'] = 1;

					return false;
				}

				$file['mime_type'] = $imginfo['mime'];
			}
			
			elseif (!in_array($format, $ignored_extensions))
			{
				
				$allowed_mime = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $allowed_mime))));

				if (function_exists('finfo_open'))
				{
					
					$finfo = finfo_open(FILEINFO_MIME);
					$type  = finfo_file($finfo, $file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime))
					{
						isset($error['WARN_INVALID_MIME']) ? $error['WARN_INVALID_MIME']++ : $error['WARN_INVALID_MIME'] = 1;

						return false;
					}
					$file['mime_type'] = $type;
					finfo_close($finfo);
				}
				elseif (function_exists('mime_content_type'))
				{
					
					$type = mime_content_type($file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime))
					{
						isset($error['WARN_INVALID_MIME']) ? $error['WARN_INVALID_MIME']++ : $error['WARN_INVALID_MIME'] = 1;

						return false;
					}
					$file['mime_type'] = $type;
				}
				
			}
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);

		$html_tags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink',
			'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del',
			'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
			'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
			'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object',
			'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar',
			'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
			'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--'
		);

		
		foreach ($html_tags AS $tag)
		{
			
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				isset($error['WARN_IEXSS']) ? $error['WARN_IEXSS']++ : $error['WARN_IEXSS'] = 1;

				return false;
			}
		}

		return true;
	}

	
	public static function isCommentOwner($commentId)
	{
		$commentObject = JUDownloadFrontHelperComment::getCommentObject($commentId);
		if (!is_object($commentObject))
		{
			return false;
		}

		$user = JFactory::getUser();
		
		if (!$user->get('guest') && $user->id == $commentObject->user_id)
		{
			return true;
		}

		return false;
	}

	
	public static function canComment($documentId, $email = '')
	{
		
		$canViewDocument = JUDownloadFrontHelperPermission::userCanDoDocument($documentId, true);
		if ($canViewDocument == false)
		{
			return false;
		}

		
		$userIdPassed = self::checkBlackListUserId();
		if (!$userIdPassed)
		{
			return false;
		}

		
		$userIpPassed = self::checkBlackListUserIP();
		if (!$userIpPassed)
		{
			return false;
		}

		
		$params = JUDownloadHelper::getParams(null, $documentId);
		$user   = JFactory::getUser();

		$isDocumentOwner           = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		$ownerCanCommentOnDocument = $params->get('document_owner_can_comment', 0);
		
		if ($isDocumentOwner && $ownerCanCommentOnDocument)
		{
			
			$ownerCanCommentManyTimes = $params->get('document_owner_can_comment_many_times', 0);
			if ($ownerCanCommentManyTimes)
			{
				return true;
			}
			else
			{
				$totalCommentsOnDoc = JUDownloadFrontHelperComment::getTotalCommentsOnDocumentOfUser($documentId, $user->id);
				if ($totalCommentsOnDoc == 0)
				{
					return true;
				}
			}
		}

		$asset = 'com_judownload.document.' . $documentId;

		if ($user->authorise('judl.comment.create', $asset) || (JUDownloadFrontHelperPermission::canDownloadDocument($documentId) && $params->get('can_download_can_comment', 0)))
		{
			
			if ($user->authorise('judl.comment.create.many_times', $asset))
			{
				return true;
			}
			else
			{
				if (!$user->get('guest'))
				{
					$totalCommentsOnDoc = JUDownloadFrontHelperComment::getTotalCommentsOnDocumentOfUser($documentId, $user->id);
					if ($totalCommentsOnDoc == 0)
					{
						return true;
					}
				}
				else
				{
					if ($email != '')
					{
						$totalCommentsPerOneDocumentForGuest = JUDownloadFrontHelperComment::getTotalCommentsOnDocumentForGuest($documentId, $email);
						if ($totalCommentsPerOneDocumentForGuest == 0)
						{
							return true;
						}
					}
					else
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	
	public static function checkBlackListUserId()
	{
		$params          = JUDownloadHelper::getParams();
		$userIdBlackList = $params->get('userid_blacklist', '');
		if ($userIdBlackList !== '')
		{
			$user               = JFactory::getUser();
			$userIdBlackListArr = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $userIdBlackList))));
			if (in_array($user, $userIdBlackListArr))
			{
				return false;
			}
		}

		return true;
	}

	
	public static function checkBlackListUserIP()
	{
		require_once JPATH_SITE . '/components/com_judownload/libs/ipblocklist.class.php';
		$params    = JUDownloadHelper::getParams();
		$app       = JFactory::getApplication();
		$is_passed = true;

		if ($app->isSite() && $params->get('block_ip', 0))
		{
			$ip_address  = JUDownloadFrontHelper::getIpAddress();
			$ipWhiteList = $params->get('ip_whitelist', '');
			$ipBlackList = $params->get('ip_blacklist', '');

			$checkIp   = new IpBlockList($ipWhiteList, $ipBlackList);
			$is_passed = $checkIp->ipPass($ip_address);
		}

		return $is_passed;
	}

	
	public static function canReplyComment($documentId, $commentId)
	{
		if (!$documentId || !$commentId)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::" . $documentId . "::" . $commentId);
		if (!isset(self::$cache[$storeId]))
		{
			$commentObject = JUDownloadFrontHelperComment::getCommentObject($commentId);
			if ($commentObject->published != 1 || $commentObject->approved != 1)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$params = JUDownloadHelper::getParams(null, $documentId);

			
			$isCommentOwner              = JUDownloadFrontHelperPermission::isCommentOwner($commentId);
			$commentOwnerCanReplyComment = $params->get('can_reply_own_comment', 0);
			if ($isCommentOwner && $commentOwnerCanReplyComment)
			{
				self::$cache[$storeId] = true;

				return self::$cache[$storeId];
			}

			
			$isDocumentOwner              = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
			$documentOwnerCanReplyComment = $params->get('document_owner_can_reply_comment', 1);
			if ($isDocumentOwner && $documentOwnerCanReplyComment)
			{
				self::$cache[$storeId] = true;

				return self::$cache[$storeId];
			}

			
			$user  = JFactory::getUser();
			$asset = 'com_judownload.document.' . $documentId;
			if ($user->authorise('judl.comment.reply', $asset))
			{
				self::$cache[$storeId] = true;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = false;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}


	
	public static function canAutoApprovalComment($documentId)
	{
		$params = JUDownloadHelper::getParams(null, $documentId);

		
		$isDocumentOwner                      = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		$autoApprovalWhenDocumentOwnerComment = $params->get('document_owner_auto_approval_when_comment', 0);

		if ($isDocumentOwner && $autoApprovalWhenDocumentOwnerComment)
		{
			return true;
		}

		
		$user  = JFactory::getUser();
		$asset = 'com_judownload.document.' . $documentId;
		if ($user->authorise('judl.comment.auto_approval', $asset))
		{
			return true;
		}

		
		
		if (!$user->get('guest'))
		{
			$autoApprovalCommentThreshold = (int) $params->get('auto_approval_comment_threshold', 0);
			if ($autoApprovalCommentThreshold > 0)
			{
				$totalApprovedCommentsOfUser = JUDownloadFrontHelperComment::getTotalApprovedCommentsOfUser($user->id);
				if ($totalApprovedCommentsOfUser >= $autoApprovalCommentThreshold)
				{
					return true;
				}
			}
		}

		return false;
	}


	
	public static function canAutoApprovalReplyComment($documentId)
	{
		$params = JUDownloadHelper::getParams(null, $documentId);

		
		$isDocumentOwner                    = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		$autoApprovalWhenDocumentOwnerReply = $params->get('document_owner_auto_approval_when_reply_comment', 0);
		if ($isDocumentOwner && $autoApprovalWhenDocumentOwnerReply)
		{
			return true;
		}

		
		$user  = JFactory::getUser();
		$asset = 'com_judownload.document.' . $documentId;
		if ($user->authorise('judl.comment.reply.auto_approval', $asset))
		{
			return true;
		}

		
		
		if (!$user->get('guest'))
		{
			$autoApprovalReplyThreshold = (int) $params->get('auto_approval_comment_reply_threshold', 0);
			if ($autoApprovalReplyThreshold > 0)
			{
				$totalApprovedRepliesOfUser = JUDownloadFrontHelperComment::getTotalApprovedRepliesOfUser($user->id);
				if ($totalApprovedRepliesOfUser >= $autoApprovalReplyThreshold)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	public static function canEditComment($commentId)
	{
		$commentObj = JUDownloadFrontHelperComment::getCommentObject($commentId);

		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			if ($commentObj->approved > 0)
			{
				$modCanEditComment = JUDownloadFrontHelperModerator::checkModeratorCanDoWithComment($commentId, 'comment_edit');

				if ($modCanEditComment)
				{
					return true;
				}
			}
			else
			{
				$modCanApproveComment = JUDownloadFrontHelperModerator::checkModeratorCanDoWithComment($commentId, 'comment_approve');

				if ($modCanApproveComment)
				{
					return true;
				}
			}

		}

		$isCommentOwner = JUDownloadFrontHelperPermission::isCommentOwner($commentId);
		if ($isCommentOwner)
		{
			$params = JUDownloadHelper::getParams(null, $commentObj->doc_id);

			$allowEditCommentWithin        = $params->get('allow_edit_comment_within', 600);
			$allowEditCommentWithinSeconds = $allowEditCommentWithin * 60;

			
			if ($allowEditCommentWithin == 0)
			{
				return true;
			}

			$inEditableTime = false;
			$timeNow        = strtotime(JHtml::date('now', 'Y-m-d H:i:s'));
			$commentCreated = strtotime($commentObj->created);
			
			if ($timeNow <= ($commentCreated + $allowEditCommentWithinSeconds))
			{
				$inEditableTime = true;
			}

			
			if ($inEditableTime)
			{
				return true;
			}
		}

		return false;
	}

	
	public static function canDeleteComment($commentId)
	{
		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			$modCanDeleteComment = JUDownloadFrontHelperModerator::checkModeratorCanDoWithComment($commentId, 'comment_delete');
			if ($modCanDeleteComment)
			{
				return true;
			}
		}

		$commentObj       = JUDownloadFrontHelperComment::getCommentObject($commentId, 'cm.doc_id');
		$params           = JUDownloadHelper::getParams(null, $commentObj->doc_id);
		$isCommentOwner   = JUDownloadFrontHelperPermission::isCommentOwner($commentId);
		$deleteOwnComment = $params->get('delete_own_comment', 0);
		if ($isCommentOwner && $deleteOwnComment)
		{
			return true;
		}

		return false;
	}

	
	public static function canVoteComment($documentId, $commentId)
	{
		$params = JUDownloadHelper::getParams(null, $documentId);

		if (!$params->get('allow_vote_comment', 1))
		{
			return false;
		}

		$commentObject = JUDownloadFrontHelperComment::getCommentObject($commentId);
		if ($commentObject->published != 1 || $commentObject->approved != 1)
		{
			return false;
		}

		$session = JFactory::getSession();
		
		if ($session->has('judl-comment-voted-' . $commentId))
		{
			return false;
		}

		
		$user              = JFactory::getUser();
		$enableVoteComment = $params->get('allow_vote_comment', 1);
		if (!$user->get('guest') && $enableVoteComment)
		{
			
			$isCommentOwner = JUDownloadFrontHelperPermission::isCommentOwner($commentId);
			if ($isCommentOwner)
			{
				$commentOwnerCanVoteOwnComment = $params->get('can_vote_own_comment', 0);
				if ($commentOwnerCanVoteOwnComment)
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			
			$isDocumentOwner             = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
			$documentOwnerCanVoteComment = $params->get('document_owner_can_vote_comment', 1);
			if ($isDocumentOwner && $documentOwnerCanVoteComment)
			{
				return true;
			}

			
			$asset = 'com_judownload.document.' . $documentId;
			if ($user->authorise('judl.comment.vote', $asset))
			{
				return true;
			}
		}

		return false;
	}

	
	public static function canReportComment($documentId, $commentId)
	{
		return false;
	}

	
	public static function canCheckInComment($commentId)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
		$commentTable = JTable::getInstance('Comment', 'JUDownloadTable');
		$commentTable->load($commentId);

		if (property_exists($commentTable, 'checked_out') && property_exists($commentTable, 'checked_out_time') && $commentTable->checked_out > 0)
		{
			$user           = JFactory::getUser();
			$isModerator    = JUDownloadFrontHelperModerator::isModerator();
			$isCommentOwner = JUDownloadFrontHelperPermission::isCommentOwner($commentId);
			
			if ($isModerator || $isCommentOwner || $commentTable->checked_out == $user->id)
			{
				$canEditComment = JUDownloadFrontHelperPermission::canEditComment($commentId);
				if ($canEditComment)
				{
					return true;
				}
			}
		}

		return false;
	}

	
	public static function showCaptchaWhenReport($documentId, $reportComment = false)
	{
		if (!$documentId)
		{
			return false;
		}
		$params        = JUDownloadHelper::getParams(null, $documentId);
		$ownerDocument = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		if ($ownerDocument)
		{
			if (!$params->get('document_owner_use_captcha_when_report', 0))
			{
				return false;
			}
		}
		$user = JFactory::getUser();
		if ($reportComment)
		{
			$assetName = 'com_judownload.document.' . $documentId;
			if ($user->authorise('judl.comment.report.no_captcha', $assetName))
			{
				return false;
			}
		}
		else
		{
			$assetName = 'com_judownload.document.' . $documentId;
			if ($user->authorise('judl.document.report.no_captcha', $assetName))
			{
				return false;
			}
		}

		return true;
	}

	
	public static function showCaptchaWhenContactDocument($documentId)
	{
		if (!$documentId)
		{
			return false;
		}
		$user      = JFactory::getUser();
		$assetName = 'com_judownload.document.' . $documentId;
		if ($user->authorise('judl.document.contact.no_captcha', $assetName))
		{
			return false;
		}

		return true;
	}

	
	public static function showCaptchaWhenComment($documentId)
	{
		if (!$documentId)
		{
			return false;
		}
		$user          = JFactory::getUser();
		$params        = JUDownloadHelper::getParams(null, $documentId);
		$ownerDocument = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
		if ($ownerDocument && $params->get('document_owner_use_captcha_when_comment', 1))
		{
			return false;
		}
		$assetName = 'com_judownload.document.' . $documentId;
		if ($user->authorise('judl.comment.no_captcha', $assetName))
		{
			return false;
		}

		return true;
	}

	
	public static function canVoteCollection($collectionId)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$params = JUDownloadHelper::getParams();
		if (!$params->get('collection_allow_vote', 1))
		{
			return false;
		}

		$user = JFactory::getUser();
		
		if ($user->id == 0 && !$params->get('collection_allow_guest_vote', 1))
		{
			return false;
		}

		$session = JFactory::getSession();
		
		if ($session->has('judl-collection-voted-' . $collectionId))
		{
			return false;
		}

		
		if (!$user->get('guest'))
		{
			$collection = JTable::getInstance('Collection', 'JUDownloadTable');
			if ($collection->load($collectionId))
			{
				if ($user->id > 0 && $user->id == $collection->created_by && !$params->get('collection_allow_owner_vote', 0))
				{
					return false;
				}
			}
		}

		return true;
	}

	
	public static function isOwnDashboard()
	{
		$app  = JFactory::getApplication();
		$view = $app->input->getString('view', '');
		if ($view == 'modpermission')
		{
			return true;
		}
		$userId = $app->input->getInt('id', 0);
		$user   = JFactory::getUser();
		
		$isOwnDashboard = true;
		
		if ($userId > 0 && $userId != $user->id)
		{
			$isOwnDashboard = false;
		}

		

		return $isOwnDashboard;
	}

	public static function canViewDashboard()
	{
		$params                = JUDownloadHelper::getParams();
		$public_user_dashboard = $params->get("public_user_dashboard", 0);
		$user                  = JFactory::getUser();

		if ($public_user_dashboard)
		{
			$app    = JFactory::getApplication();
			$userId = $app->input->getInt('id', 0);
			
			if ($user->id == 0 && $userId == 0)
			{
				return false;
			}

			return true;
		}
		else
		{
			
			if ($user->id == 0)
			{
				return false;
			}
			
			else
			{
				$isOwnDashboard = JUDownloadFrontHelperPermission::isOwnDashboard();

				return $isOwnDashboard;
			}
		}
	}

}