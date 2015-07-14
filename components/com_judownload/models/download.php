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

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_judownload/libs/zip.class.php';

class JUDownloadModelDownload extends JModelLegacy
{
	protected $cache;

	
	public function deleteExpiredTmpFiles()
	{
		$nowDate = JFactory::getDate()->toSql();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, file_path');
		$query->from('#__judownload_files_tmp');
		$query->where('removed <= ' . $db->quote($nowDate));
		$db->setQuery($query);
		$tmpFiles = $db->loadObjectList();

		foreach ($tmpFiles AS $tmpFile)
		{
			if ($tmpFile->file_path)
			{
				$fullFilePath = JPath::clean(JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory('download_directory', 'judownload/') . $tmpFile->file_path);
				$folderPath   = dirname($fullFilePath);
				
				$canDelete = $this->canDeleteFolder($folderPath);
				if ($canDelete)
				{
					if (JFolder::exists($folderPath))
					{
						$deleteTmpFileFolder = JFolder::delete($folderPath);
					}
					else
					{
						$deleteTmpFileFolder = true;
					}

					
					if ($deleteTmpFileFolder || !JFile::exists($fullFilePath))
					{
						$query = $db->getQuery(true);
						$query->delete('#__judownload_files_tmp');
						$query->where('id = ' . $db->quote($tmpFile->id));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}

	
	public function canDeleteFolder($dirPath)
	{
		

		
		if (strpos($dirPath, '../') !== false || strpos($dirPath, '..\\') !== false)
		{
			return false;
		}

		
		$judownloadPath = JPath::clean(JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory('download_directory', 'judownload/'));
		$dirPath        = JPath::clean($dirPath);
		if (strpos($dirPath, $judownloadPath) !== 0)
		{
			return false;
		}

		return true;
	}

	
	public function generalCheckDownload()
	{
		
		$user   = JFactory::getUser();
		$params = JUDownloadHelper::getParams();

		
		$maxDownloadTimesInDay = (int) $params->get('max_download_times_in_day', 30);
		
		$maxDownloadSizeInDayMb = (int) $params->get('max_download_size_in_day_mb', 500);
		
		$intervalConfigDownload = (int) $params->get('download_interval', 5);

		$serverTime      = JFactory::getDate()->toSql();
		$serverTimeStamp = strtotime($serverTime);
		
		if (!$user->get('guest'))
		{
			$startDay      = date('Y-m-d', strtotime($serverTime));
			$startDayStamp = strtotime($startDay);

			$endDayStamp      = $startDayStamp + (60 * 60 * 24) - 1;
			$timeTillTomorrow = ceil(($endDayStamp - $serverTimeStamp) / 60); 

			$downloadTimesInDay = $this->getDownloadTimesInDay();
			
			if ($downloadTimesInDay >= $maxDownloadTimesInDay)
			{
				$error = JText::sprintf('COM_JUDOWNLOAD_YOU_HAVE_REACHED_MAX_DOWNLOAD_TIME_N_TIMES_IN_A_DAY_PLEASE_WAIT_N_MINUTES_THEN_COUNTER_WILL_BE_RESET', $maxDownloadTimesInDay, $timeTillTomorrow);
				$this->setError($error);

				return false;
			}

			$downloadSizeInDay = $this->getTotalSizeMBDownloadInDay();
			
			if ($downloadSizeInDay >= $maxDownloadSizeInDayMb)
			{
				$error = JText::sprintf('COM_JUDOWNLOAD_YOU_HAVE_REACHED_MAX_N_MB_DOWNLOAD_IN_A_DAY_PLEASE_WAIT_N_MINUTES_THEN_COUNTER_WILL_BE_RESET', $maxDownloadSizeInDayMb, $timeTillTomorrow);
				$this->setError($error);

				return false;
			}

			$latestTimeDownload      = $this->getLatestTimeDownload();
			$latestTimeDownloadStamp = strtotime($latestTimeDownload);
			
			if (($serverTimeStamp - (int) $latestTimeDownloadStamp) < $intervalConfigDownload)
			{
				$timeToWait = $intervalConfigDownload - ($serverTimeStamp - (int) $latestTimeDownloadStamp);
				$error      = JText::sprintf('COM_JUDOWNLOAD_YOU_HAVE_TO_WAIT_N_SECONDS_TO_DOWNLOAD', $timeToWait);
				$this->setError($error);

				return false;
			}
		}

		
		$session = JFactory::getSession();
		if ($session->has('judl-last-download-time'))
		{
			$lastDownloadTime = $session->get('judl-last-download-time');
			if ($serverTimeStamp >= strtotime($lastDownloadTime))
			{
				$intervalDownload = $serverTimeStamp - strtotime($lastDownloadTime);
				if ($intervalDownload < $intervalConfigDownload)
				{
					$timeToWait = $intervalConfigDownload - $intervalDownload;
					$error      = JText::sprintf('COM_JUDOWNLOAD_YOU_HAVE_TO_WAIT_N_SECONDS_TO_DOWNLOAD', $timeToWait);
					$this->setError($error);

					return false;
				}
			}
		}

		
		$max_size_tmp_download_folder = (int) $params->get('max_size_tmp_download_folder', 3072);
		
		if ($this->getDownloadFolderSizeMb() > $max_size_tmp_download_folder)
		{
			$noticeFullDownloadFolder = JPATH_ADMINISTRATOR . "/components/com_judownload/sendmail.tmp";
			if (JFile::exists($noticeFullDownloadFolder))
			{
				$nowTime                     = time();
				$fileTime                    = filemtime($noticeFullDownloadFolder);
				$send_noticed_email_interval = $params->get("send_noticed_email_interval", 120) * 60;
				if ($nowTime - $fileTime >= $send_noticed_email_interval)
				{
					$buffer = '';
					JFile::write($noticeFullDownloadFolder, $buffer);
					JUDownloadFrontHelperMail::sendEmailByEvent('noticedemail.fulltmpdir');
				}
			}
			else
			{
				$buffer = '';
				JFile::write($noticeFullDownloadFolder, $buffer);
				JUDownloadFrontHelperMail::sendEmailByEvent('noticedemail.fulltmpdir');
			}
		}

		return true;
	}

	
	public function getTotalSizeMBDownloadInDay()
	{
		$user       = JFactory::getUser();
		$serverTime = JFactory::getDate()->toSql();
		$startDay   = date('Y-m-d', strtotime($serverTime));
		$db         = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('SUM(value)');
		$query->from('#__judownload_logs');
		$query->where('user_id = ' . $user->id);
		$query->where('date >= ' . $db->quote($startDay));
		$query->where('date < ' . $db->quote($serverTime));
		$query->where('event = ' . $db->quote('document.download'));
		$db->setQuery($query);

		$totalFileSize     = $db->loadResult();
		$totalFileSizeInMB = round($totalFileSize / 1024 / 1024, 2);

		return $totalFileSizeInMB;
	}

	
	public function getDownloadTimesInDay()
	{
		$user       = JFactory::getUser();
		$serverTime = JFactory::getDate()->toSql();
		$startDay   = date('Y-m-d', strtotime($serverTime));
		$db         = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_logs');
		$query->where('user_id = ' . $user->id);
		$query->where('date >= ' . $db->quote($startDay));
		$query->where('date < ' . $db->quote($serverTime));
		$query->where('event = ' . $db->quote('document.download'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public function getLatestTimeDownload()
	{
		$user       = JFactory::getUser();
		$serverTime = JFactory::getDate()->toSql();
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$query->select('MAX(date)');
		$query->from('#__judownload_logs');
		$query->where('user_id = ' . $user->id);
		$query->where('date <= ' . $db->quote($serverTime));
		$query->where('event = ' . $db->quote('document.download'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	public function getDownloadFolderSizeMb()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(file_size)');
		$query->from('#__judownload_files_tmp');
		$db->setQuery($query);
		$tmpFolderSize = $db->loadResult();
		$tmpFolderSize = round($tmpFolderSize / 1024 / 1024, 2);

		return $tmpFolderSize;
	}

	
	public function download($type, $itemIdArray, $parentId, $version = '')
	{
		$params                      = JUDownloadHelper::getParams();
		$downloadZippedFileMode      = $params->get('download_zipped_file_mode', 'temp');
		$downloadOneFileNoZippedMode = $params->get('download_one_file_no_zipped_mode', 'temp');

		
		if ($downloadZippedFileMode == 'temp' || $downloadOneFileNoZippedMode == 'temp')
		{
			$packageExisted = $this->isPackageExisted($type, $itemIdArray, $version);
			if ($packageExisted)
			{
				return $this->updateDownloadPackage($type, $itemIdArray, $parentId, $version);
			}
			else
			{
				return $this->createDownloadPackage($type, $itemIdArray, $parentId, $version);
			}
		}
		
		else
		{
			return $this->createDownloadPackage($type, $itemIdArray, $parentId, $version);
		}
	}

	
	public function isPackageExisted($type, $itemIdArray, $version)
	{
		if ($type == 'document')
		{
			$documentIdArray = $itemIdArray;

			$fileIdArray = array();
			foreach ($documentIdArray AS $documentId)
			{
				$fileObjectList = $this->getAllFilesOfDocument($documentId);
				foreach ($fileObjectList AS $fileObject)
				{
					$fileIdArray[] = $fileObject->id;
				}
			}
		}
		else
		{
			$fileIdArray = $itemIdArray;
		}

		
		sort($fileIdArray);
		$fileIdString = implode(',', $fileIdArray);
		if ($version)
		{
			$fileIdString .= ":" . $version;
		}

		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('file_path');
		$query->from('#__judownload_files_tmp');
		$query->where('user_id = ' . $user->id);
		$query->where('included_fileids = ' . $db->quote($fileIdString));
		$db->setQuery($query);
		$filePath = $db->loadResult();
		if ($filePath)
		{
			$filePath = JPath::clean(JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory('download_directory', 'judownload/') . $filePath);
			if (JFile::exists($filePath))
			{
				return true;
			}
		}

		return false;
	}

	
	public function updateDownloadPackage($type, $itemIdArray, $parentId, $version)
	{
		$app = JFactory::getApplication();
		
		sort($itemIdArray);
		$storeID = md5($type . serialize($itemIdArray) . $version);

		if ($type == 'document')
		{
			$params = JUDownloadHelper::getParams($parentId);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $parentId);
		}

		$noCountingDownloadTime = (int) $params->get('no_counting_download_time', 300);
		if ($noCountingDownloadTime > 0)
		{
			$valuesStoreId = (array) $app->getUserState('com_judownload.download.storeid');
		}
		else
		{
			$valuesStoreId = array();
		}

		$minDownloadSpeed = (int) $params->get('min_download_speed', 10);
		$minDownloadSpeed = $minDownloadSpeed > 0 ? $minDownloadSpeed : 10;
		$minDownloadSpeed = $minDownloadSpeed * 1024;

		$adjustFileLiveTime = (int) $params->get('adjust_file_live_time', 60);
		$adjustFileLiveTime = $adjustFileLiveTime >= 0 ? $adjustFileLiveTime : 60;

		if ($type == "document")
		{
			$documentIdArray = $itemIdArray;

			$fileIdArray = array();
			foreach ($documentIdArray AS $documentId)
			{
				$fileObjectList = $this->getAllFilesOfDocument($documentId);
				foreach ($fileObjectList AS $fileObject)
				{
					$fileIdArray[] = $fileObject->id;
				}
			}
		}
		else
		{
			$documentId  = $parentId;
			$fileIdArray = $itemIdArray;
		}

		sort($fileIdArray);
		$fileIdString = implode(',', $fileIdArray);

		$user = JFactory::getUser();
		$db   = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_files_tmp');
		$query->where('user_id = ' . $user->id);
		$query->where('included_fileids = ' . $db->quote($fileIdString));
		$db->setQuery($query);
		$tmpDownloadFile = $db->loadObject();

		
		$fileLiveTime = round($tmpDownloadFile->file_size / $minDownloadSpeed);

		$nowDate          = JFactory::getDate()->toSql();
		$removedTimeStamp = strtotime($nowDate) + $fileLiveTime + $adjustFileLiveTime;
		$removedTime      = date('Y-m-d H:i:s', $removedTimeStamp);

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_files_tmp');
		$query->set('removed = ' . $db->quote($removedTime));
		$query->where('user_id = ' . $user->id);
		$query->where('included_fileids = ' . $db->quote($fileIdString));
		$db->setQuery($query);
		$db->execute();

		$DBfilePath       = $tmpDownloadFile->file_path;
		$physicalFilePath = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory('download_directory', 'judownload/') . $DBfilePath;
		if ($DBfilePath && JFile::exists($physicalFilePath))
		{
			
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('judownload');

			
			if ($type == 'document')
			{
				$documentIdArray = $itemIdArray;

				foreach ($documentIdArray AS $documentId)
				{
					if (!in_array($storeID, $valuesStoreId))
					{
						$this->updateDocumentDownloadCounter($documentId);
					}

					$fileObjectList = $this->getAllFilesOfDocument($documentId);

					$documentSize          = 0;
					$fileIdArrayInDocument = array();
					foreach ($fileObjectList AS $fileObject)
					{
						$documentSize += $fileObject->size;
						$fileIdArrayInDocument[] = $fileObject->id;
					}

					$fileIdStringInDocument = '';
					if (is_array($fileIdArrayInDocument) && count($fileIdArrayInDocument))
					{
						sort($fileIdArrayInDocument);
						$fileIdStringInDocument = implode(",", $fileIdArrayInDocument);
					}

					$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArrayInDocument, $documentSize));

					
					$logData = array(
						'event'     => 'document.download',
						'item_id'   => $documentId,
						'doc_id'    => $documentId,
						'value'     => $documentSize,
						'reference' => $fileIdStringInDocument
					);

					if (!in_array($storeID, $valuesStoreId))
					{
						JUDownloadFrontHelperLog::addLog($logData);
					}
				}

				foreach ($fileIdArray AS $fileId)
				{
					if (!in_array($storeID, $valuesStoreId))
					{
						$this->updateFileDownloadCounter($fileId);
					}
				}
			}
			
			else
			{
				if (!in_array($storeID, $valuesStoreId))
				{
					$this->updateDocumentDownloadCounter($documentId);
				}

				$documentSize = 0;
				foreach ($fileIdArray AS $fileId)
				{
					if (!in_array($storeID, $valuesStoreId))
					{
						$this->updateFileDownloadCounter($fileId);
					}
					$fileObject = $this->getFileObject($fileId);
					$documentSize += $fileObject->size;
				}

				$fileIdStringInDocument = $fileIdString;

				$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArray, $documentSize));

				
				$logData = array(
					'event'     => 'document.download',
					'item_id'   => $documentId,
					'doc_id'    => $documentId,
					'value'     => $documentSize,
					'reference' => $fileIdStringInDocument
				);
				if (!in_array($storeID, $valuesStoreId))
				{
					JUDownloadFrontHelperLog::addLog($logData);
				}
			}

			
			$docIdArray = array();
			if ($type == 'document')
			{
				$docIdArray = $itemIdArray;
			}
			else
			{
				foreach ($itemIdArray AS $fileId)
				{
					$docIdArray[] = $this->getFileObject($fileId)->doc_id;
				}
			}

			$docIdArray = array_unique($docIdArray);

			foreach ($docIdArray AS $docId)
			{
				JUDownloadFrontHelperMail::sendEmailByEvent('document.download', $docId);
			}

			$serverTime      = JFactory::getDate()->toSql();
			$serverTimeStamp = strtotime($serverTime);

			$params                 = JUDownloadHelper::getParams();
			$noCountingDownloadTime = (int) $params->get('no_counting_download_time', 300);
			if ($noCountingDownloadTime > 0)
			{
				$valuesStoreId                   = (array) $app->getUserState('com_judownload.download.storeid');
				$valuesStoreId[$serverTimeStamp] = $storeID;
				$valuesStoreId                   = array_unique($valuesStoreId);
				$app->setUserState('com_judownload.download.storeid', $valuesStoreId);
			}

			$session = JFactory::getSession();
			$session->set('judl-last-download-time', $serverTime);

			
			$shortFileURL = str_replace("\\", "/", $tmpDownloadFile->file_path);
			$fileUrl      = JUri::root() . JUDownloadFrontHelper::getDirectory('download_directory', 'judownload/', true) . $shortFileURL;
			$app->redirect($fileUrl);

			return true;
		}

		$this->setError(JText::_('COM_JUDOWNLOAD_FILE_NOT_FOUND'));

		return false;
	}

	
	public function createDownloadPackage($type, $itemIdArray, $parentId, $version)
	{
		$app = JFactory::getApplication();
		// If set no_counting_download_time, storeId will be used to check if download file in "no counting download" period
		sort($itemIdArray);
		$storeId = md5($type . serialize($itemIdArray) . $version);

		if ($type == 'document')
		{
			$params = JUDownloadHelper::getParams($parentId);
		}
		else
		{
			$params = JUDownloadHelper::getParams(null, $parentId);
		}

		$noCountingDownloadSecond = (int) $params->get('no_counting_download_time', 300);
		if ($noCountingDownloadSecond > 0)
		{
			$storeIdArray = (array) $app->getUserState('com_judownload.download.storeid');
		}
		else
		{
			$storeIdArray = array();
		}

		$user = JFactory::getUser();

		$downloadZippedFileMode      = $params->get('download_zipped_file_mode', 'temp');
		$downloadOneFileNoZippedMode = $params->get('download_one_file_no_zipped_mode', 'temp');

		// Min download speed.
		$minDownloadSpeed = (int) $params->get('min_download_speed', 10);
		$minDownloadSpeed = $minDownloadSpeed > 0 ? $minDownloadSpeed : 10;
		$minDownloadSpeed = $minDownloadSpeed * 1024; //KBps

		// Min live time of download package.
		$adjustFileLiveTime = (int) $params->get('adjust_file_live_time', 60);
		$adjustFileLiveTime = $adjustFileLiveTime >= 0 ? $adjustFileLiveTime : 60;

		// Time download package created.
		$createdTimeDate  = JFactory::getDate()->toSql();
		$createdTimeStamp = strtotime($createdTimeDate);

		// Trigger JU Download after download
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('judownload');

		// Physical file path to download, to be used in temp folder mode
		$downloadFilePath = '';

		// True if file is zipped by zip class
		$zipFile = false;

		// Get comment for zip package.
		$zipCommentConfig = $params->get('zip_comment', '');

		// Zip comment parsed from $zipCommentConfig case by case
		$zipComment = '';

		if ($type == 'document')
		{
			// Get category id.
			$categoryId      = $parentId;
			$documentIdArray = $itemIdArray;
			// Download multi documents in the same cat
			if (count($documentIdArray) > 1)
			{
				// In this case : user downloading category.
				// Sort array document id.
				sort($documentIdArray);

				// Create zip package.
				$zip     = new Zip();
				$zipFile = true;

				// Parse zip comment
				$zipComment = $this->parseCommentTxt($zipCommentConfig, $categoryId);

				// File id array in all download documents to reference in tmp file table
				$fileIdArrayInTmpZip = array();
				foreach ($documentIdArray AS $documentId)
				{
					$documentObject = JUDownloadHelper::getDocumentById($documentId);
					$documentTitle  = $this->filterFileFolderName($documentObject->title);
					$documentTitle  = trim($documentTitle);
					$fileObjectList = $this->getAllFilesOfDocument($documentId);
					// If document has file, add document title as a folder contains files
					if (count($fileObjectList))
					{
						$zip->addDirectory($documentTitle);
					}

					// File id array in document to log document.download
					$fileIdArray  = array();
					$documentSize = 0;
					foreach ($fileObjectList AS $fileObject)
					{
						$physicalFilePath = $this->getPhysicalFilePath($fileObject->id);

						if (JFile::exists($physicalFilePath))
						{
							$filePathInZip = $documentTitle . '/' . $this->filterFileFolderName($fileObject->rename);

							// Add file extension to file path, if the extension is not the same original file
							$fileExtOri   = JFile::getExt($physicalFilePath);
							$fileExtInZip = JFile::getExt($filePathInZip);
							if ($fileExtInZip != $fileExtOri)
							{
								$filePathInZip = $filePathInZip . '.' . $fileExtOri;
							}
							$filePathInZip = trim($filePathInZip);

							$zip->addLargeFile($physicalFilePath, $filePathInZip);
							$documentSize += $fileObject->size;
							if (!in_array($storeId, $storeIdArray))
							{
								$this->updateFileDownloadCounter($fileObject->id);
							}
						}
						$fileIdArray[]         = $fileObject->id;
						$fileIdArrayInTmpZip[] = $fileObject->id;
					}

					if (!in_array($storeId, $storeIdArray))
					{
						$this->updateDocumentDownloadCounter($documentId);

						$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArray, $documentSize));

						// Add log when download
						$logData = array(
							'user_id'   => $user->id,
							'event'     => 'document.download',
							'item_id'   => $documentId,
							'doc_id'    => $documentId,
							'value'     => $documentSize,
							'reference' => implode(',', $fileIdArray)
						);

						JUDownloadFrontHelperLog::addLog($logData);
					}
				}
				// End - Zip file

				$categoryObject = JUDownloadHelper::getCategoryById($categoryId);
				$zipFileName    = $this->makeSafeFileName($categoryObject->title) . ".zip";
			}
			//Download one document
			else
			{
				/*
				 * Download document when not isset cat_id
				 * Download document we only get first element of documentIds array
				 */
				$documentId     = $documentIdArray[0];
				$documentObject = JUDownloadHelper::getDocumentById($documentId);

				$documentTitle  = $this->filterFileFolderName($documentObject->title);
				$fileObjectList = $this->getAllFilesOfDocument($documentId);

				// Zip file even document has only one file, we can change this later here...
				// Create zip download package.
				$zip     = new Zip();
				$zipFile = true;

				// Parse zip comment
				$zipComment = $this->parseCommentTxt($zipCommentConfig, $categoryId, $documentId);

				$documentTitle = trim($documentTitle);
				//Add document title as a folder contains files
				$zip->addDirectory($documentTitle);

				$fileIdArray  = array();
				$documentSize = 0;
				foreach ($fileObjectList AS $fileObject)
				{
					// One document allow to download by version
					$physicalFilePath = $this->getPhysicalFilePath($fileObject->id, $version);

					if (JFile::exists($physicalFilePath))
					{
						$filePathInZip = $documentTitle . '/' . $this->filterFileFolderName($fileObject->rename);

						// Add file extension to file path, if the extension is not the same original file
						$fileExtOri   = JFile::getExt($physicalFilePath);
						$fileExtInZip = JFile::getExt($filePathInZip);
						if ($fileExtInZip != $fileExtOri)
						{
							$filePathInZip = $filePathInZip . '.' . $fileExtOri;
						}
						$filePathInZip = trim($filePathInZip);

						$zip->addLargeFile($physicalFilePath, $filePathInZip);
						$documentSize += $fileObject->size;
						if (!in_array($storeId, $storeIdArray))
						{
							$this->updateFileDownloadCounter($fileObject->id, $version);
						}
					}
					$fileIdArray[] = $fileObject->id;
				}
				// End - Zip file

				if (!in_array($storeId, $storeIdArray))
				{
					$this->updateDocumentDownloadCounter($documentId);

					$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArray, $documentSize));

					// Add log
					$logData = array(
						'user_id'   => $user->id,
						'event'     => 'document.download',
						'item_id'   => $documentId,
						'doc_id'    => $documentId,
						'value'     => $documentSize,
						'reference' => implode(',', $fileIdArray) . ($version ? ':' . $version : '')
					);

					JUDownloadFrontHelperLog::addLog($logData);
				}

				$zipFileName = $this->makeSafeFileName($documentObject->title . " " . $version) . ".zip";
			}
		}
		elseif ($type == 'file')
		{
			$fileIdArray = $itemIdArray;
			$documentId  = $parentId;
			//Download multi files in one document
			if (count($fileIdArray) > 1)
			{
				$documentObject = JUDownloadHelper::getDocumentById($documentId);

				$documentTitle  = $this->filterFileFolderName($documentObject->title);
				$mainCategoryId = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);

				$zip     = new Zip();
				$zipFile = true;

				// Parse zip comment
				$zipComment = $this->parseCommentTxt($zipCommentConfig, $mainCategoryId, $documentId);

				$documentTitle = trim($documentTitle);
				$zip->addDirectory($documentTitle);

				$documentSize = 0;
				foreach ($fileIdArray AS $fileId)
				{
					// One document allow to download by version
					$physicalFilePath = $this->getPhysicalFilePath($fileId, $version);

					if (JFile::exists($physicalFilePath))
					{
						$fileObject    = $this->getFileObject($fileId);
						$filePathInZip = $documentTitle . '/' . $this->filterFileFolderName($fileObject->rename);

						// Add file extension to file path, if the extension is not the same original file
						$fileExtOri   = JFile::getExt($physicalFilePath);
						$fileExtInZip = JFile::getExt($filePathInZip);
						if ($fileExtInZip != $fileExtOri)
						{
							$filePathInZip = $filePathInZip . '.' . $fileExtOri;
						}
						$filePathInZip = trim($filePathInZip);

						$zip->addLargeFile($physicalFilePath, $filePathInZip);
						$documentSize += $fileObject->size;
						if (!in_array($storeId, $storeIdArray))
						{
							$this->updateFileDownloadCounter($fileId, $version);
						}
					}
				}
				// End - Zip file

				// Sort $fileIdArrayInTmpZip before add log and store it to tmp files table
				sort($fileIdArray);

				if (!in_array($storeId, $storeIdArray))
				{
					$this->updateDocumentDownloadCounter($documentId);

					$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArray, $documentSize));

					// Add log
					$logData = array(
						'user_id'   => $user->id,
						'event'     => 'document.download',
						'item_id'   => $documentId,
						'doc_id'    => $documentId,
						'value'     => $documentSize,
						'reference' => implode(',', $fileIdArray) . ($version ? ':' . $version : '')
					);

					JUDownloadFrontHelperLog::addLog($logData);
				}

				$zipFileName = $this->makeSafeFileName($documentObject->title . " " . $version) . ".zip";
			}
			//Download one file
			elseif (count($fileIdArray) == 1)
			{
				$zipOneFile = $params->get('zip_one_file', 0);

				$fileId     = $itemIdArray[0];
				$fileObject = $this->getFileObject($fileId);

				// One file allow to download by version
				$physicalFilePath = $this->getPhysicalFilePath($fileId, $version);
				$physicalFilePath = JPath::clean($physicalFilePath);

				$fileExtOri = JFile::getExt($physicalFilePath);

				$configAllowZipFile = $params->get('allow_zip_file', 1);

				// Download one file no zipped (File can be zip file or not, but we still using var $zipFileName for general download file name)
				if ($fileExtOri == "zip" || !$zipOneFile || !$configAllowZipFile)
				{
					$zipFile = false;

					// In this case, $zipFileName is file name(zipped or not)
					$zipFileName = $this->filterFileFolderName($fileObject->rename);
					$zipFileName = $this->makeSafeFileName(JFile::stripExt($zipFileName) . " " . $version) . "." . JFile::getExt($zipFileName);
					// Add file extension to file path, if the extension is not the same original file
					$fileExtInZip = JFile::getExt($zipFileName);
					if ($fileExtInZip != $fileExtOri)
					{
						$zipFileName = $zipFileName . '.' . $fileExtOri;
					}
					$zipFileName = trim($zipFileName);
				}
				// Download one file zipped
				else
				{
					// Initialize zip object.
					$zip     = new Zip();
					$zipFile = true;

					$mainCategoryId = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);
					// Parse zip comment
					$zipComment = $this->parseCommentTxt($zipCommentConfig, $mainCategoryId, $documentId);

					if (JFile::exists($physicalFilePath))
					{
						$filePathInZip = $this->filterFileFolderName($fileObject->rename);

						// Add file extension to file path, if the extension is not the same original file
						$fileExtInZip = JFile::getExt($filePathInZip);
						if ($fileExtInZip != $fileExtOri)
						{
							$filePathInZip = $filePathInZip . '.' . $fileExtOri;
						}
						$filePathInZip = trim($filePathInZip);

						$zip->addLargeFile($physicalFilePath, $filePathInZip);
					}
					// End - Zip file

					$zipFileName = $this->filterFileFolderName($fileObject->rename);
					$zipFileName = $this->makeSafeFileName(JFile::stripExt($zipFileName) . " " . $version) . ".zip";
					$zipFileName = trim($zipFileName);
				}

				if (!in_array($storeId, $storeIdArray))
				{
					$this->updateDocumentDownloadCounter($documentId, $version);
					$this->updateFileDownloadCounter($fileId, $version);

					$dispatcher->trigger('onAfterDownloadDocument', array($documentId, $fileIdArray, $fileObject->size));

					// Add log
					$logData = array(
						'user_id'   => $user->id,
						'event'     => 'document.download',
						'item_id'   => $documentId,
						'doc_id'    => $documentId,
						'value'     => $fileObject->size,
						'reference' => $fileId . ($version ? ':' . $version : '')
					);

					JUDownloadFrontHelperLog::addLog($logData);
				}
			}
		}
		// Only support download file/document, invalid type -> return false
		else
		{
			return false;
		}

		$serverTime      = JFactory::getDate()->toSql();
		$serverTimeStamp = strtotime($serverTime);

		// Store ID of download file(s) into session
		if ($noCountingDownloadSecond > 0)
		{
			$storeIdArray                   = (array) $app->getUserState('com_judownload.download.storeid');
			$storeIdArray[$serverTimeStamp] = $storeId;
			$storeIdArray                   = array_unique($storeIdArray);
			$app->setUserState('com_judownload.download.storeid', $storeIdArray);
		}

		// Last download time to calculate download interval
		$session = JFactory::getSession();
		$session->set('judl-last-download-time', $serverTime);

		// If use zip class to zip files, set comment then close the archive
		if ($zipFile)
		{
			// Set comment for zip file
			$zip->setComment($zipComment);

			// Close the archive
			$zip->finalize();
		}

		// Send email by event for each document when download
		$docIdArray = array();
		if ($type == 'file')
		{
			$docIdArray[] = $parentId;
		}
		else
		{
			$docIdArray = $itemIdArray;
		}

		$docIdArray = array_unique($docIdArray);
		//Send mail by event
		foreach ($docIdArray AS $docId)
		{
			JUDownloadFrontHelperMail::sendEmailByEvent('document.download', $docId);
		}

		// Download ZIPPED file
		if ($zipFile)
		{
			// Directly download(from zip resource by PHP)
			$resourceFilePath   = $zip->getZipFile();
			$transport          = 'php';
			$speed              = (int) $params->get('max_download_speed', 200);
			$resume             = $params->get('resume_download', 1);
			$downloadMultiParts = $params->get('download_multi_parts', 1);

			$downloadResult = JUDownloadHelper::downloadFile($resourceFilePath, $zipFileName, $transport, $speed, $resume, $downloadMultiParts);

			if ($downloadResult !== true)
			{
				$this->setError($downloadResult);

				return false;
			}
		}
		// Download ONE NO ZIPPED file, in this case $zipFileName is the download file name, file can be zip file or not
		else
		{
			// Directly download
			$transport          = $downloadOneFileNoZippedMode;
			$speed              = (int) $params->get('max_download_speed', 200);
			$resume             = $params->get('resume_download', 1);
			$downloadMultiParts = $params->get('download_multi_parts', 1);

			$downloadResult = JUDownloadHelper::downloadFile($physicalFilePath, $zipFileName, $transport, $speed, $resume, $downloadMultiParts);

			if ($downloadResult !== true)
			{
				$this->setError($downloadResult);

				return false;
			}
		}

		return true;
	}

	
	public function htaccessProtectFile($downloadFilePath, $params)
	{
		
		$folder       = dirname($downloadFilePath);
		$fileHtaccess = $folder . "/.htaccess";
		$fileHtaccess = JPath::clean($fileHtaccess);
		$fileName     = basename($downloadFilePath);
		
		$buffer = "<Files \"" . $fileName . "\">\n";
		if ($params->get('restrict_ip_download_file', 1))
		{
			
			$ipAddress = JUDownloadFrontHelper::getIpAddress();
			$buffer .= "Order deny,allow\n";
			$buffer .= "Deny from all\n";
			$buffer .= "Allow from " . $ipAddress . "\n";
		}
		else
		{
			
			$buffer .= "Order allow,deny\n";
			$buffer .= "Allow from all\n";
		}
		$buffer .= "</Files>\n";
		
		$buffer .= "<FilesMatch \"\\.(?:htaccess|htpasswd|ini|php|phtml|php3|php4|php5|php6|pl|py|jsp|asp|htm|shtml|bat|sh|cgi)$\">\n";
		$buffer .= "Order allow,deny\n";
		$buffer .= "Deny from all\n";
		$buffer .= "</FilesMatch>\n";
		
		$buffer .= "Order allow,deny\n";
		$buffer .= "Deny from all\n";
		JFile::write($fileHtaccess, $buffer);
	}

	
	public function filterFileFolderName($fileFolderName)
	{
		$removedCharacterArray = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
		$fileNameFiltered      = str_replace($removedCharacterArray, "", $fileFolderName);

		return $fileNameFiltered;
	}

	
	public function getAllFilesOfDocument($documentId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_files');
		$query->where('doc_id =' . $documentId);
		$query->where('published = 1');
		$query->order('ordering');
		$db->setQuery($query);
		$fileObjectList = $db->loadObjectList();

		return $fileObjectList;
	}

	public function updateDocumentDownloadCounter($documentId, $version = '')
	{
		$db = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_documents');
		$query->set('downloads = downloads + 1');
		$query->where('id = ' . $documentId);
		$db->setQuery($query);
		$db->execute();

		
		if (JUDLPROVERSION)
		{
			$versionTable = JTable::getInstance("Version", "JUDownloadTable");
			$documentObj  = JUDownloadHelper::getDocumentById($documentId);
			
			if ($version === '')
			{
				$version = $documentObj->version;
			}

			$version_downloads_check_arr = array('doc_id' => $documentId, 'file_id' => 0, 'version' => $version);
			if ($versionTable->load($version_downloads_check_arr))
			{
				$versionTable->date      = intval($documentObj->updated) ? $documentObj->updated : $documentObj->created;
				$versionTable->downloads = $versionTable->downloads + 1;
				$versionTable->store();
			}
			else
			{
				$versionTable->bind($version_downloads_check_arr);
				$versionTable->id        = 0;
				$versionTable->date      = intval($documentObj->updated) ? $documentObj->updated : $documentObj->created;
				$versionTable->downloads = 1;
				$versionTable->store();
			}
		}
	}

	public function updateFileDownloadCounter($fileId, $version = '')
	{
		$db = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_files');
		$query->set('downloads = downloads + 1');
		$query->where('id = ' . $fileId);
		$db->setQuery($query);
		$db->execute();

		
		if (JUDLPROVERSION)
		{
			$versionTable = JTable::getInstance("Version", "JUDownloadTable");
			$fileObj      = $this->getFileObject($fileId);
			$documentId   = $fileObj->doc_id;
			$documentObj  = JUDownloadHelper::getDocumentById($documentId);
			
			if ($version === '')
			{
				$version = $documentObj->version;
			}

			$version_downloads_check_arr = array('doc_id' => $documentId, 'file_id' => $fileId, 'version' => $version);
			if ($versionTable->load($version_downloads_check_arr))
			{
				$versionTable->date      = intval($documentObj->updated) ? $documentObj->updated : $documentObj->created;
				$versionTable->downloads = $versionTable->downloads + 1;
				$versionTable->store();
			}
			else
			{
				$versionTable->bind($version_downloads_check_arr);
				$versionTable->id        = 0;
				$versionTable->date      = intval($documentObj->updated) ? $documentObj->updated : $documentObj->created;
				$versionTable->downloads = 1;
				$versionTable->store();
			}
		}
	}

	
	public function makeSafeFileName($fileName = '')
	{
		$fileName = trim($fileName);
		JFilterOutput::stringURLSafe($fileName);

		
		$fileName = str_replace(" ", "_", $fileName);
		
		$fileName = JFile::makeSafe($fileName);

		if (trim(str_replace("_", "", $fileName)) == "")
		{
			$fileName = JHtml::date('now', 'Y-m-d-H-i-s', true);
		}

		return $fileName;
	}

	
	public function parseCommentTxt($comment, $catId = 0, $docId = 0)
	{
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			$userName = JText::_('COM_JUDOWNLOAD_GUEST');
		}
		else
		{
			$userName = $user->name;
		}

		$catTitle = $docTitle = '';

		
		if ($catId)
		{
			$catObj   = JUDownloadHelper::getCategoryById($catId);
			$catTitle = $catObj->title;
		}

		
		if ($docId)
		{
			$docObj   = JUDownloadHelper::getDocumentById($docId);
			$docTitle = $docObj->title;
		}

		$comment = str_replace("{user_id}", $user->id, $comment);
		if ($catTitle)
		{
			$comment = str_replace("{cat_title}", $catTitle, $comment);
		}
		else
		{
			$comment = str_replace("{cat_title}", '', $comment);
		}

		if ($docTitle)
		{
			$comment = str_replace("{doc_title}", $docTitle, $comment);
		}
		else
		{
			$comment = str_replace("{doc_title}", '', $comment);
		}

		$comment = str_replace("{user_name}", $userName, $comment);

		preg_match('/{date:?(.*?)}/', $comment, $matches);
		if (!empty($matches))
		{
			if (empty($matches[1]))
			{
				$dateFormat = 'Y-m-d H:i:s';
			}
			else
			{
				$dateFormat = $matches[1];
			}

			$timeNow = JHtml::date('now', 'Y-m-d H:i:s', true);
			$date    = date($dateFormat, strtotime($timeNow));
			$comment = str_replace($matches[0], $date, $comment);
		}

		return $comment;
	}

	
	public function getPhysicalFilePath($fileId, $version = '')
	{
		$fileDirectory  = JUDownloadFrontHelper::getDirectory('file_directory', 'media/com_judownload/files/');
		$fileObject     = $this->getFileObject($fileId);
		$documentObject = JUDownloadHelper::getDocumentById($fileObject->doc_id);
		
		if ($version === '' || $version === $documentObject->version)
		{
			$filePath = JPATH_SITE . '/' . $fileDirectory . $fileObject->doc_id . '/' . $fileObject->file_name;
			$filePath = JPath::clean($filePath);

			return $filePath;
		}
		else
		{
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('file_path')
				->from('#__judownload_versions')
				->where('file_id = ' . $fileId)
				->where('version = ' . $db->quote($version));
			$db->setQuery($query, 0, 1);
			$versionFilePath = $db->loadResult();
			
			if ($versionFilePath)
			{
				$filePath = JPATH_SITE . '/' . $fileDirectory . $fileObject->doc_id . '/' . $versionFilePath;
				$filePath = JPath::clean($filePath);

				return $filePath;
			}
			
			else
			{
				$query = $db->getQuery(true);
				$query->select('date')
					->from('#__judownload_versions')
					->where('doc_id = ' . $fileObject->doc_id)
					->where('version = ' . $db->quote($version));
				$db->setQuery($query, 0, 1);
				$versionDate = $db->loadResult();
				
				if ($versionDate)
				{
					
					$query = $db->getQuery(true);
					$query->select('file_path')
						->from('#__judownload_versions')
						->where('file_id = ' . $fileId)
						->where('date < ' . $db->quote($versionDate))
						->where('file_path != ""')
						->order('date DESC, version DESC');
					$db->setQuery($query, 0, 1);
					$nearestVersionFilePath = $db->loadResult();
					
					if ($nearestVersionFilePath)
					{
						$filePath = JPATH_SITE . '/' . $fileDirectory . $fileObject->doc_id . '/' . $nearestVersionFilePath;
						$filePath = JPath::clean($filePath);

						return $filePath;
					}
				}
			}
		}
	}

	
	public function getFileObject($fileId)
	{
		$storeId = md5(__METHOD__ . "::" . $fileId);
		if (!isset($this->cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_files');
			$query->where('id = ' . $fileId);
			$query->where('published = 1');
			$db->setQuery($query);
			$this->cache[$storeId] = $db->loadObject();
		}

		return $this->cache[$storeId];
	}

	
	public function canDownloadFile($fileId, $checkCanDownloadDoc = true)
	{
		$fileObject = $this->getFileObject($fileId);
		if (!is_object($fileObject))
		{
			return false;
		}

		$documentId = $fileObject->doc_id;
		if ($checkCanDownloadDoc)
		{
			$canDownloadDocument = $this->canDownloadDocument($documentId);
			if (!$canDownloadDocument)
			{
				return false;
			}
		}

		return true;
	}

	
	public function canDownloadDocument($documentId, $checkPassword = true)
	{
		$storeId = md5(__METHOD__ . "::$documentId::" . (int) $checkPassword);
		if (!isset($this->cache[$storeId]))
		{
			
			$mainCategoryId = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);
			$canDoCategory  = JUDownloadFrontHelperPermission::canDoCategory($mainCategoryId);
			if (!$canDoCategory)
			{
				$this->cache[$storeId] = false;

				return $this->cache[$storeId];
			}

			
			$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentId);
			if ($isDocumentOwner)
			{
				
				$asset = 'com_judownload.document.' . $documentId;
				$user  = JFactory::getUser();
				if ($user->authorise('judl.document.download.own.no_restrict', $asset))
				{
					$this->cache[$storeId] = true;

					return $this->cache[$storeId];
				}
			}

			
			$isModerator = JUDownloadFrontHelperModerator::isModerator();
			if ($isModerator)
			{
				$documentObject = JUDownloadHelper::getDocumentById($documentId);
				
				if ($documentObject->approved < 1)
				{
					
					$modCanApprove = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCategoryId, 'document_approve');
					if ($modCanApprove)
					{
						$this->cache[$storeId] = true;

						return $this->cache[$storeId];
					}
				}

				
				$modCanDownload = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCategoryId, 'document_download');
				if ($modCanDownload)
				{
					$this->cache[$storeId] = true;

					return $this->cache[$storeId];
				}
			}

			
			if ($isDocumentOwner)
			{
				$userCanDoDocument = true;
			}
			else
			{
				$userCanDoDocument = JUDownloadFrontHelperPermission::userCanDoDocument($documentId, true);
			}

			
			if (!$userCanDoDocument)
			{
				$this->cache[$storeId] = false;

				return $this->cache[$storeId];
			}

			
			if (!$isModerator || ($isModerator && !$modCanDownload))
			{
				
				$validDownloadRules = JUDownloadFrontHelperDocument::getDownloadRuleErrorMessages($documentId);

				
				if ($validDownloadRules !== true)
				{
					$message = array();
					$message = array_merge($message, $validDownloadRules);
					
					$this->setError(implode("<br/>", $message));

					$this->cache[$storeId] = false;

					return $this->cache[$storeId];
				}
			}

			$documentObject = JUDownloadHelper::getDocumentById($documentId);
			$hasPassword    = JUDownloadFrontHelperDocument::documentHasPassword($documentObject);
			
			if ($hasPassword && $checkPassword)
			{
				$validPassword = JUDownloadFrontHelperPassword::checkPassword($documentObject);

				
				if (!$validPassword)
				{
					$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_DOWNLOAD_PASSWORD'));

					$this->cache[$storeId] = false;

					return $this->cache[$storeId];
				}
			}

			$this->cache[$storeId] = true;

			return $this->cache[$storeId];
		}

		return $this->cache[$storeId];
	}

	public function checkPassword($docId, $password)
	{
		$session               = JFactory::getSession();
		$timeNow               = JFactory::getDate()->toSql();
		$timeNowStamp          = strtotime($timeNow);
		$ss_wrongPasswordTimes = 'judl-wrong-password-' . $docId;
		$ss_blockDownloadTime  = 'judl-block-download-time-' . $docId;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('download_password');
		$query->from('#__judownload_documents');
		$query->where('id =' . $docId);
		$db->setQuery($query);
		$DBPassword = $db->loadResult();

		
		if ($DBPassword === $password)
		{
			$session->set('judl-download-password-' . $docId, $DBPassword);
			$session->clear($ss_wrongPasswordTimes);
			$session->clear($ss_blockDownloadTime);

			return true;
		}
		
		else
		{
			$session->clear('judl-download-password-' . $docId);

			if ($session->has($ss_wrongPasswordTimes))
			{
				$i = $session->get($ss_wrongPasswordTimes, 0);
				$session->set($ss_wrongPasswordTimes, $i + 1);
				$session->set($ss_blockDownloadTime, $timeNowStamp);
			}
			else
			{
				$session->set($ss_wrongPasswordTimes, 1);
				$session->set($ss_blockDownloadTime, $timeNowStamp);
			}
		}

		return false;
	}

	
	public function getChildDocumentIds($categoryId)
	{
		$user         = JFactory::getUser();
		$levels       = $user->getAuthorisedViewLevels();
		$levelsString = implode(',', $levels);

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('d.id');
		$query->from('#__judownload_documents AS d');
		$query->join('', '#__judownload_documents_xref AS dx ON d.id = dx.doc_id');
		$query->where('dx.cat_id = ' . $categoryId);
		$query->where('d.published = 1');
		$query->where('d.approved = 1');
		$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');
		if ($user->get('guest'))
		{
			$query->where('d.access IN (' . $levelsString . ')');
		}
		else
		{
			$query->where('(d.access IN (' . $levelsString . ') OR (d.created_by = ' . $user->id . '))');
		}
		$db->setQuery($query);

		return $db->loadColumn();
	}
}