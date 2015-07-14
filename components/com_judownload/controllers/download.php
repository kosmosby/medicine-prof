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

jimport('joomla.application.component.controllerform');

class JUDownloadControllerDownload extends JControllerForm
{

	
	public function checkPasswordReload()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$app        = JFactory::getApplication();
		$documentId = $app->input->post->getInt('doc_id', 0);
		$password   = $app->input->post->get('download_password', null, 'string');

		
		$checkPassword = false;

		
		$model = $this->getModel();

		
		if (($documentId > 0) && $password)
		{
			$checkPassword = $model->checkPassword($documentId, $password);
		}

		
		if ($checkPassword)
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_JUDOWNLOAD_VALID_PASSWORD'), 'message');
		}
		else
		{
			
			$params = JUDownloadHelper::getParams(null, $documentId);

			
			$session               = JFactory::getSession();
			$maxWrongPasswordTimes = $params->get('max_wrong_password_times', 5);

			
			$ssNameWrongPasswordTimes = 'judl-wrong-password-' . $documentId;

			
			$wrongPasswordTimes = $session->get($ssNameWrongPasswordTimes, 0);

			$msg = JText::plural('COM_JUDOWNLOAD_YOU_HAVE_ENTERED_WRONG_PASSWORD_N_TIME', $wrongPasswordTimes);
			$msg .= '<br/>' . JText::plural('COM_JUDOWNLOAD_YOU_WILL_BE_LOCKED_OUT_AFTER_N_FAILED_ATTEMPT', $maxWrongPasswordTimes);
			$this->setRedirect($this->getReturnPage(), $msg, 'error');
		}
	}

	
	public function getAlert()
	{
		$app        = JFactory::getApplication();
		$documentId = $app->input->post->getInt('doc_id', 0);
		
		if ($documentId > 0)
		{
			
			$session = JFactory::getSession();

			
			$timeNow      = JFactory::getDate()->toSql();
			$timeNowStamp = strtotime($timeNow);

			
			$params                 = JUDownloadHelper::getParams(null, $documentId);
			$blockEnterPasswordTime = $params->get('block_enter_password_time', 600);
			$maxWrongPasswordTimes  = $params->get('max_wrong_password_times', 5);

			
			$ss_wrongPasswordTimes = 'judl-wrong-password-' . $documentId;
			$ss_blockDownloadTime  = 'judl-block-download-time-' . $documentId;

			
			$wrongPasswordTimes = $session->get($ss_wrongPasswordTimes, 0);

			$documentObject = JUDownloadHelper::getDocumentById($documentId);
			$error          = false;
			
			if (!is_object($documentObject))
			{
				$error = true;
			}

			
			$documentHasPassword = JUDownloadFrontHelperDocument::documentHasPassword($documentObject);
			if (!$documentHasPassword)
			{
				$error = true;
			}

			
			if ($error)
			{
				$html = '<div class="alert alert-error">';
				$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
				$html .= JText::_('COM_JUDOWNLOAD_INVALID_DOCUMENT');
				$html .= '</div>';
				JUDownloadHelper::obCleanData();
				echo $html;
				exit;
			}

			$checkPasswordStatus = true;

			if ($maxWrongPasswordTimes < 1)
			{
				$maxWrongPasswordTimes = 1;
			}

			
			if ($wrongPasswordTimes >= $maxWrongPasswordTimes)
			{
				if ($blockEnterPasswordTime == 0)
				{
					
					$checkPasswordStatus = false;
				}
				else
				{
					$lastTime = $session->get($ss_blockDownloadTime, 0);
					$interval = $timeNowStamp - $lastTime;
					if ($interval >= 0)
					{
						if ($interval <= $blockEnterPasswordTime)
						{
							$checkPasswordStatus = false;
						}
						else
						{
							
							$session->clear($ss_wrongPasswordTimes);
							$session->clear($ss_blockDownloadTime);
							$checkPasswordStatus = true;
						}
					}
					else
					{
						
						$session->clear($ss_wrongPasswordTimes);
						$session->clear($ss_blockDownloadTime);
						$checkPasswordStatus = true;
					}
				}
			}
			elseif ($wrongPasswordTimes > 0)
			{
				$checkPasswordStatus = false;
			}

			if ($checkPasswordStatus)
			{
				
				$html = '<div class="alert alert-info">';
				$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
				$html .= JText::_('COM_JUDOWNLOAD_PLEASE_ENTER_PASSWORD_TO_DOWNLOAD_DOCUMENT');
				$html .= '</div>';
			}
			else
			{
				
				$html = '<div class="alert alert-error">';
				$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
				$html .= JText::plural('COM_JUDOWNLOAD_YOU_HAVE_ENTERED_WRONG_PASSWORD_N_TIME', $wrongPasswordTimes);
				$html .= '<br/>' . JText::plural('COM_JUDOWNLOAD_YOU_WILL_BE_LOCKED_OUT_AFTER_N_FAILED_ATTEMPT', $maxWrongPasswordTimes);
				$html .= '</div>';
			}

			JUDownloadHelper::obCleanData();
			echo $html;
			exit;
		}
	}

	
	public function checkPasswordAjax()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$model = $this->getModel();
		
		$app = JFactory::getApplication();

		$documentId = $app->input->post->getInt('doc_id', 0);
		$password   = $app->input->post->get('download_password', null, null);

		
		if (($documentId > 0) && $password)
		{
			
			$session = JFactory::getSession();

			
			$timeNow      = JFactory::getDate()->toSql();
			$timeNowStamp = strtotime($timeNow);

			
			$params                 = JUDownloadHelper::getParams(null, $documentId);
			$blockEnterPasswordTime = $params->get('block_enter_password_time', 600);
			$maxWrongPasswordTimes  = $params->get('max_wrong_password_times', 5);

			if ($maxWrongPasswordTimes < 1)
			{
				$maxWrongPasswordTimes = 1;
			}

			
			$ss_wrongPasswordTimes = 'judl-wrong-password-' . $documentId;
			$ss_blockDownloadTime  = 'judl-block-download-time-' . $documentId;

			
			$wrongPasswordTimes = $session->get($ss_wrongPasswordTimes, 0);

			
			if ($wrongPasswordTimes >= $maxWrongPasswordTimes)
			{
				if ($blockEnterPasswordTime == 0)
				{
					$passwordStatus = -1;
				}
				else
				{
					$lastTime = $session->get($ss_blockDownloadTime, 0);
					$interval = $timeNowStamp - $lastTime;
					if ($interval >= 0)
					{
						
						if ($interval <= $blockEnterPasswordTime)
						{
							$passwordStatus = -1;
						}
						
						else
						{
							$session->clear($ss_wrongPasswordTimes);
							$session->clear($ss_blockDownloadTime);
							$checkPassword = $model->checkPassword($documentId, $password);
							if (!$checkPassword)
							{
								$passwordStatus = 0;
							}
							else
							{
								$passwordStatus = 1;
							}
						}
					}
					else
					{
						
						$session->clear($ss_wrongPasswordTimes);
						$session->clear($ss_blockDownloadTime);
						$passwordStatus = '';
					}
				}
			}
			
			else
			{
				$checkPassword = $model->checkPassword($documentId, $password);
				if (!$checkPassword)
				{
					$passwordStatus = 0;
				}
				else
				{
					$passwordStatus = 1;
				}
			}
		}
		else
		{
			
			$passwordStatus = '';
		}

		
		if ($passwordStatus == 1)
		{
			$html = '<div class="alert alert-success">';
			$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
			$html .= JText::_('COM_JUDOWNLOAD_VALID_PASSWORD');
			$html .= '</div>';
			$token          = JSession::getFormToken();
			$link           = JRoute::_('index.php?option=com_judownload&task=download.download&doc_id=' . $documentId . '&' . $token . '=1', false);
			$documentObject = JUDownloadHelper::getDocumentById($documentId);
			$result         = array('status' => '1', 'message' => $html, 'link' => $link, 'id' => $documentObject->id, 'title' => $documentObject->title, 'downloads' => $documentObject->downloads);
		}
		
		elseif ($passwordStatus == 0)
		{
			
			$wrongPasswordTimes = $session->get($ss_wrongPasswordTimes, 0);
			$html               = '<div class="alert alert-error">';
			$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
			$html .= JText::plural('COM_JUDOWNLOAD_YOU_HAVE_ENTERED_WRONG_PASSWORD_N_TIMES_PLEASE_TRY_AGAIN', $wrongPasswordTimes);
			$html .= '</div>';
			$result = array('status' => '0', 'message' => $html);
		}
		
		elseif ($passwordStatus == -1)
		{
			$html = '<div class="alert alert-error">';
			$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
			if ($blockEnterPasswordTime == 0)
			{
				$html .= JText::plural('COM_JUDOWNLOAD_YOU_HAVE_ENTERED_WRONG_PASSWORD_OVER_MAX_N_TIMES_YOU_HAVE_BEEN_LOCKED_OUT', $maxWrongPasswordTimes);
			}
			else
			{
				$html .= JText::plural('COM_JUDOWNLOAD_YOU_HAVE_ENTERED_WRONG_PASSWORD_OVER_MAX_N_TIMES_YOU_HAVE_BEEN_LOCKED_OUT_FOR_N_SECONDS', $maxWrongPasswordTimes, $blockEnterPasswordTime);
			}
			$html .= '</div>';
			$result = array('status' => '-1', 'message' => $html);
		}
		else
		{
			$result = null;
		}

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	
	public function download()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$model = $this->getModel();

		
		$model->deleteExpiredTmpFiles();

		
		$app = JFactory::getApplication();
		
		$submittedCategoryId = $app->input->getInt('cat_id', 0);
		$documentIds         = $app->input->get('doc_id', null, null);
		$fileIds             = $app->input->get('file_id', null, null);
		$version             = $app->input->get('version', '', 'string');

		$serverTime      = JFactory::getDate()->toSql();
		$serverTimeStamp = strtotime($serverTime);

		$valuesStoreId = (array) $app->getUserState('com_judownload.download.storeid');

		$params                 = JUDownloadHelper::getParams();
		$noCountingDownloadTime = (int) $params->get('no_counting_download_time', 300);
		
		if ($noCountingDownloadTime > 0)
		{
			if (!empty($valuesStoreId))
			{
				foreach ($valuesStoreId AS $keyStoreId => $valueStoreId)
				{
					if ($serverTimeStamp - $keyStoreId > $noCountingDownloadTime)
					{
						unset($valuesStoreId[$keyStoreId]);
					}
				}
			}

			$app->setUserState('com_judownload.download.storeid', $valuesStoreId);
		}

		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('judownload');

		
		if (isset($fileIds))
		{
			
			if (is_array($fileIds))
			{
				
				$fileIdArray = $fileIds;
			}
			else
			{
				
				$fileIdArray = explode(',', $fileIds);
			}

			
			if (count($fileIdArray) > 0)
			{
				
				if (count($fileIdArray) > 1)
				{
					
					$documentId = (int) $documentIds;
					if (!$documentIds)
					{
						$message = JText::_('COM_JUDOWNLOAD_NO_DOCUMENT_DETECTED');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}

					
					$fileObjectList   = $model->getAllFilesOfDocument($documentId);
					$validFileIdArray = array();
					foreach ($fileObjectList AS $fileObject)
					{
						if (in_array($fileObject->id, $fileIdArray))
						{
							$validFileIdArray[] = $fileObject->id;
						}
					}
				}
				
				else
				{
					$fileObject = $model->getFileObject($fileIdArray[0]);
					$documentId = $fileObject->doc_id;
					if (isset($documentIds))
					{
						$documentIdPost = (int) $documentIds;
						if ($documentIdPost != $documentId)
						{
							$message = JText::_('COM_JUDOWNLOAD_INVALID_DATA');
							$this->setRedirect($this->getReturnPage(), $message, 'error');

							return false;
						}
					}
					$validFileIdArray = $fileIdArray;
					
					$physicalFilePath = $model->getPhysicalFilePath($validFileIdArray[0]);
					$physicalFilePath = JPath::clean($physicalFilePath);
					if (!JFile::exists($physicalFilePath))
					{
						$message = JText::_('COM_JUDOWNLOAD_FILE_DOES_NOT_EXIST');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}
				}

				
				$canDownloadDocument = $model->canDownloadDocument($documentId);
				if ($canDownloadDocument)
				{
					if (count($validFileIdArray) > 0)
					{
						
						$externalField = new JUDownloadFieldCore_external_link();
						$document      = JUDownloadHelper::getDocumentById($documentId);
						if ($externalField->isPublished() && $document->external_link != '')
						{
							$dispatcher->trigger('onAfterDownloadDocument', array($documentId, array(), 0));

							
							$logData = array(
								'user_id'   => JFactory::getUser()->id,
								'event'     => 'document.download',
								'item_id'   => $documentId,
								'doc_id'    => $documentId,
								'value'     => 0,
								'reference' => 'external'
							);
							JUDownloadFrontHelperLog::addLog($logData);

							
							JUDownloadFrontHelperMail::sendEmailByEvent('document.download', $documentId);

							
							$model->updateDocumentDownloadCounter($documentId);

							
							$this->setRedirect(JRoute::_($document->external_link, false));

							return true;
						}

						if (count($validFileIdArray) > 1)
						{
							$params = JUDownloadHelper::getParams(null, (int) $documentId);
							
							if (!$params->get('allow_zip_file', 1))
							{
								$message = JText::_('COM_JUDOWNLOAD_INVALID_DOWNLOAD_DATA');
								$this->setRedirect($this->getReturnPage(), $message, 'error');

								return false;
							}
						}

						
						foreach ($validFileIdArray AS $validFileId)
						{
							$canDownloadFile = $model->canDownloadFile($validFileId, false);
							if (!$canDownloadFile)
							{
								$fileObject = $model->getFileObject($validFileId);
								$message    = JText::sprintf('COM_JUDOWNLOAD_YOU_CAN_NOT_DOWNLOAD_FILE_X', $fileObject->rename);
								$this->setRedirect($this->getReturnPage(), $message, 'error');

								return false;
							}
						}

						if ($noCountingDownloadTime > 0)
						{
							sort($validFileIdArray);
							$storeID = md5('file' . serialize($validFileIdArray) . $version);
							if (in_array($storeID, $valuesStoreId))
							{
								$generalCheck = true;
							}
							else
							{
								
								$generalCheck = $model->generalCheckDownload();
							}
						}
						else
						{
							$generalCheck = $model->generalCheckDownload();
						}

						if (!$generalCheck)
						{
							$message = $model->getError();
							$this->setRedirect($this->getReturnPage(), $message, 'error');

							return false;
						}

						if ($model->download('file', $validFileIdArray, $documentId, $version) === false)
						{
							$message = $model->getError();
							$this->setRedirect($this->getReturnPage(), $message, 'error');

							return false;
						}

					}
					
					else
					{
						$message = JText::_('COM_JUDOWNLOAD_INVALID_DOWNLOAD_DATA');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}
				}
				
				else
				{
					$message          = implode("<br/>", $model->getErrors());
					$params           = JUDownloadHelper::getParams(null, $documentId);
					$display_messages = $params->get('show_rule_messages', 'modal');
					if ($display_messages == "redirect")
					{
						$return = $app->input->get('return', null, 'base64');
						$this->setRedirect(JRoute::_('index.php?option=com_judownload&view=downloaderror&return=' . $return, false), $message, 'error');
					}
					else
					{
						$this->setRedirect($this->getReturnPage(), $message, 'error');
					}

					return false;
				}
			}
			
			else
			{
				$message = JText::_('COM_JUDOWNLOAD_NO_FILE_TO_DOWNLOAD');
				$this->setRedirect($this->getReturnPage(), $message, 'error');

				return false;
			}
		}
		
		else
		{
			
			if (is_array($documentIds))
			{
				$documentIdArray = $documentIds;
			}
			else
			{
				$documentIdArray = explode(',', $documentIds);
			}

			if (count($documentIdArray) > 0)
			{
				
				if (count($documentIdArray) > 1)
				{
					$categoryId = $submittedCategoryId;
					
					if (!$categoryId)
					{
						$message = JText::_('COM_JUDOWNLOAD_NO_CATEGORY_DETECTED');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}

					$params = JUDownloadHelper::getParams(null, $categoryId);
					
					if (!$params->get('allow_download_multi_docs', 0))
					{
						$message = JText::_('COM_JUDOWNLOAD_INVALID_DOWNLOAD_DATA');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}

					
					$validDocumentIdArray = array();

					
					$documentIdsInCat = $model->getChildDocumentIds($categoryId);

					foreach ($documentIdsInCat AS $documentIdInCat)
					{
						if (in_array($documentIdInCat, $documentIdArray))
						{
							$validDocumentIdArray[] = $documentIdInCat;
						}
					}
				}
				
				else
				{
					$documentId = $documentIdArray[0];
					$categoryId = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);

					$validDocumentIdArray = $documentIdArray;
					$documentIdInCat      = JUDownloadHelper::getDocumentById($documentId);
					$externalField        = new JUDownloadFieldCore_external_link();
					if ($externalField->isPublished() && $documentIdInCat->external_link != '')
					{
						$dispatcher->trigger('onAfterDownloadDocument', array($documentId, array(), 0));

						
						$logData = array(
							'user_id'   => JFactory::getUser()->id,
							'event'     => 'document.download',
							'item_id'   => $documentId,
							'doc_id'    => $documentId,
							'value'     => 0,
							'reference' => 'external'
						);
						JUDownloadFrontHelperLog::addLog($logData);

						
						JUDownloadFrontHelperMail::sendEmailByEvent('document.download', $documentId);

						
						$model->updateDocumentDownloadCounter($documentId);

						
						$this->setRedirect(JRoute::_($documentIdInCat->external_link, false));

						return true;
					}
				}

				if (count($validDocumentIdArray) > 1)
				{
					$params = JUDownloadHelper::getParams($categoryId);
					
					if (!$params->get('allow_zip_file', 1))
					{
						$message = JText::_('COM_JUDOWNLOAD_INVALID_DOWNLOAD_DATA');
						$this->setRedirect($this->getReturnPage(), $message, 'error');

						return false;
					}
				}
				elseif (count($validDocumentIdArray) == 1)
				{
					$filesInDocument = JUDownloadFrontHelperDocument::getFilesByDocumentId((int) $validDocumentIdArray[0]);
					if (count($filesInDocument) > 1)
					{
						if (!$params->get('allow_zip_file', 1))
						{
							$linkFiles = JUDownloadHelperRoute::getDocumentRoute((int) $validDocumentIdArray[0]);
							$linkFiles .= '#judl-files';
							$app->redirect(JRoute::_($linkFiles, false));
						}
					}
				}

				
				foreach ($validDocumentIdArray AS $documentId)
				{
					$canDownloadDocument = $model->canDownloadDocument($documentId);
					if (!$canDownloadDocument)
					{
						$message          = implode("<br/>", $model->getErrors());
						$params           = JUDownloadHelper::getParams(null, $documentId);
						$display_messages = $params->get('show_rule_messages', 'modal');
						if ($display_messages == "redirect")
						{
							$return = $app->input->get('return', null, 'base64');
							$this->setRedirect(JRoute::_('index.php?option=com_judownload&view=downloaderror&return=' . $return, false), $message, 'error');
						}
						else
						{
							$this->setRedirect($this->getReturnPage(), $message, 'error');
						}

						return false;
					}
				}

				
				if ($noCountingDownloadTime > 0)
				{
					sort($validDocumentIdArray);
					$storeID = md5('document' . serialize($validDocumentIdArray) . $version);
					if (in_array($storeID, $valuesStoreId))
					{
						$generalCheck = true;
					}
					else
					{
						
						$generalCheck = $model->generalCheckDownload();
					}
				}
				else
				{
					$generalCheck = $model->generalCheckDownload();
				}

				if (!$generalCheck)
				{
					$message = $model->getError();
					$this->setRedirect($this->getReturnPage(), $message, 'error');

					return false;
				}

				
				if (count($validDocumentIdArray) == 1)
				{
					if (count($filesInDocument) == 1)
					{
						
						if (!$params->get('allow_zip_file', 1))
						{
							$fileObject = $filesInDocument[0];
							$fileId     = $fileObject->id;
							if ($model->download('file', array($fileId), $validDocumentIdArray[0], $version) === false)
							{
								$message = $model->getError();
								$this->setRedirect($this->getReturnPage(), $message, 'error');

								return false;
							}
						}
					}
				}

				if ($model->download('document', $validDocumentIdArray, $categoryId, $version) === false)
				{
					$message = $model->getError();
					$this->setRedirect($this->getReturnPage(), $message, 'error');

					return false;
				}
			}
			
			else
			{
				$message = JText::_('COM_JUDOWNLOAD_NO_DOCUMENT_TO_DOWNLOAD');
				$this->setRedirect($this->getReturnPage(), $message, 'error');

				return false;
			}
		}
	}

	
	protected function getReturnPage()
	{
		$app    = JFactory::getApplication();
		$return = $app->input->get('return', null, 'base64');

		if (empty($return))
		{
			return JUri::base();
		}
		else
		{
			return urldecode(base64_decode($return));
		}
	}
} 