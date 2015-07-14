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


class JUDownloadControllerDocument extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_DOCUMENT';

	protected $context = 'document';

	protected $view_list = 'listcats';

	
	protected function allowAdd($data = array())
	{
		
		if (empty($data))
		{
			$catId = JFactory::getApplication()->input->get('cat_id');
			if (!JUDownloadFrontHelperPermission::canSubmitDocument($catId))
			{
				return false;
			}
		}

		return true;
	}

	
	protected function allowEdit($data = array(), $key = 'id')
	{
		
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');

		
		if ($user->authorise('judl.document.edit', 'com_judownload.document.' . $recordId))
		{
			return true;
		}

		
		
		if ($user->authorise('judl.document.edit.own', 'com_judownload.document.' . $recordId))
		{
			$ownerId = 0;

			if ($recordId)
			{
				
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		
		return parent::allowEdit($data, $key);
	}

	
	protected function getRedirectToListAppend()
	{
		$app             = JFactory::getApplication();
		$tmpl            = $app->input->get('tmpl');
		$append          = '';
		$rootCategory    = JUDownloadFrontHelperCategory::getRootCategory();
		$categoriesField = new JUDownloadFieldCore_categories();
		$postValue       = $app->input->getArray($_POST);
		$cat_id          = $postValue['fields'][$categoriesField->id]['main'];

		if (!$cat_id)
		{
			$cat_id = $rootCategory->id;
		}

		if ($cat_id)
		{
			$append .= '&cat_id=' . $cat_id;
		}

		
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		return $append;
	}

	
	public function save($key = null, $urlVar = null)
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app     = JFactory::getApplication();
		$lang    = JFactory::getLanguage();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task    = $this->getTask();

		
		$data              = $app->input->post->get('jform', array(), 'array');
		$fieldsData        = $app->input->post->get('fields', array(), 'array');
		$files             = array_values($app->input->post->get("judlfiles", array(), 'array'));
		$changelogs        = array_values($app->input->post->get("changelogs", array(), 'array'));
		$versions          = $app->input->post->get("versions", array(), 'array');
		$related_documents = array_values($app->input->post->get("related_documents", array(), 'array'));

		
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $app->input->getInt($urlVar, 0);

		if (!$this->checkEditId($context, $recordId))
		{
			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		$data[$key] = $recordId;

		
		if ($task == 'save2copy')
		{
			
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			
			$docArr                = array($data[$key]);
			$currentDocumentObject = JUDownloadHelper::getDocumentById($data[$key]);
			$catArr                = array($currentDocumentObject->cat_id);
			$copyOptionsArr        = array('copy_downloads', 'copy_rates', 'copy_hits', 'copy_permission', 'copy_extra_fields',
				'copy_files', 'copy_changelogs', 'copy_related_documents', 'copy_comments', 'copy_reports', 'copy_subscriptions', 'copy_logs');
			
			$documentCopyMappedId = $model->copyAndMap($docArr, $catArr, $copyOptionsArr, $files, $versions, 'save2copy', $fieldsData);

			$data[$key] = $documentCopyMappedId;
			$save2copy  = true;
			$task       = 'apply';
		}

		
		if (!$this->allowSave($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		
		$form = $model->getForm($data, false);
		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		
		$validData       = $model->validate($form, $data);
		$validFieldsData = $model->validateFields($fieldsData, $data[$key]);
		$validFiles      = $model->validateFiles($files, $data[$key]);

		
		if ($validData === false || $validFieldsData === false || $validFiles === false)
		{
			
			$errors = $model->getErrors();

			
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			
			$app->setUserState($context . '.data', $data);
			$app->setUserState($context . '.fieldsdata', $fieldsData);
			$app->setUserState($context . '.files', $files);
			$app->setUserState($context . '.changelogs', $changelogs);
			$app->setUserState($context . '.versions', $versions);
			$app->setUserState($context . '.related_documents', $related_documents);

			if (isset($save2copy) && $save2copy)
			{
				$model->delete($data[$key]);
			}

			
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		
		$data['data'] = $validData;
		
		$data['data'][$key]        = $data[$key];
		$data['fieldsData']        = $validFieldsData;
		$data['files']             = $validFiles;
		$data['changelogs']        = $changelogs;
		$data['versions']          = $versions;
		$data['related_documents'] = $related_documents;

		$categoriesField = new JUDownloadFieldCore_categories();

		
		if (($model->getDocumentSubmitType($data['data'][$key]) == 'submit' && !$categoriesField->canSubmit())
			|| ($model->getDocumentSubmitType($data['data'][$key]) == 'edit' && !$categoriesField->canEdit())
		)
		{
			$documentObjectDb = JUDownloadHelper::getDocumentById($data['data'][$key]);
			if ($documentObjectDb)
			{
				
				$data['fieldsData'][$categoriesField->id]['main'] = $documentObjectDb->cat_id;
			}
			
			else
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_DOCUMENT'));
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}
		}

		
		if (!$model->save($data))
		{
			
			$app->setUserState($context . '.data', $validData);
			$app->setUserState($context . '.fieldsdata', $validFieldsData);
			$app->setUserState($context . '.changelogs', $changelogs);
			$app->setUserState($context . '.versions', $versions);
			$app->setUserState($context . '.files', $files);
			$app->setUserState($context . '.related_documents', $related_documents);


			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}


		
		if ($table->load($recordId))
		{
			if ($table->id > 0)
			{
				
				if ($checkin && $model->checkin($recordId) === false)
				{
					
					$app->setUserState($context . '.data', $validData);
					$app->setUserState($context . '.fieldsdata', $validFieldsData);
					$app->setUserState($context . '.files', $files);
					$app->setUserState($context . '.changelogs', $changelogs);
					$app->setUserState($context . '.versions', $versions);
					$app->setUserState($context . '.related_documents', $related_documents);


					
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend($recordId, $urlVar), false
						)
					);

					return false;
				}
			}
		}

		$this->setMessage(
			JText::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		
		switch ($task)
		{
			case 'apply':
				
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$app->setUserState($context . '.fieldsdata', null);
				$app->setUserState($context . '.files', null);
				$app->setUserState($context . '.changelogs', null);
				$app->setUserState($context . '.versions', null);
				$app->setUserState($context . '.related_documents', null);


				$model->checkout($recordId);

				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
				break;

			case 'save2new':
				
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$app->setUserState($context . '.fieldsdata', null);
				$app->setUserState($context . '.files', null);
				$app->setUserState($context . '.changelogs', null);
				$app->setUserState($context . '.versions', null);
				$app->setUserState($context . '.related_documents', null);


				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend(null, $urlVar), false
					)
				);
				break;

			default:
				
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$app->setUserState($context . '.fieldsdata', null);
				$app->setUserState($context . '.files', null);
				$app->setUserState($context . '.changelogs', null);
				$app->setUserState($context . '.versions', null);
				$app->setUserState($context . '.related_documents', null);


				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);
				break;
		}

		
		$this->postSaveHook($model, $validData);

		return true;
	}

	
	public function edit($key = null, $urlVar = null)
	{
		
		$app     = JFactory::getApplication();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$cid     = $app->input->get('cid', array(), 'array');
		$context = "$this->option.edit.$this->context";

		
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		
		$recordId = (int) (count($cid) ? $cid[0] : $app->input->getInt($urlVar, 0));
		$checkin  = property_exists($table, 'checked_out');

		
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		if ($checkin && !$model->checkout($recordId))
		{
			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}
		else
		{
			
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);
			$app->setUserState($context . '.fieldsdata', null);
			$app->setUserState($context . '.changelogs', null);
			$app->setUserState($context . '.versions', null);
			$app->setUserState($context . '.files', null);
			$app->setUserState($context . '.related_documents', null);

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return true;
		}
	}

	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$app    = JFactory::getApplication();
		$tmpl   = $app->input->get('tmpl');
		$layout = $app->input->get('layout', 'edit');
		$append = '';

		if ($this->view_list == 'listcats')
		{
			$cat_id = $app->input->get->getInt('cat_id', 0);
			if ($cat_id)
			{
				$append .= '&cat_id=' . $cat_id;
			}
			else
			{
				$categoriesField = new JUDownloadFieldCore_categories();
				$postValue       = $app->input->getArray($_POST);
				$cat_id          = $postValue['fields'][$categoriesField->id]['main'];

				if ($cat_id)
				{
					$append .= '&cat_id=' . $cat_id;
				}
			}
		}
		elseif ($this->view_list == 'pendingdocuments')
		{
			$append .= '&approve=1';
		}

		
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		if ($app->input->getInt('approve', 0) == 1)
		{
			$append .= '&approve=1';
		}

		return $append;
	}

	
	public function cancel($key = null)
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app    = JFactory::getApplication();
		$doc_id = $app->input->getInt('id', 0);
		
		if ($doc_id)
		{
			$documentObject = JUDownloadHelper::getDocumentById($doc_id);
			$cat_id         = $documentObject->cat_id;
		}
		
		else
		{
			$fieldCategory = JUDownloadFrontHelperField::getField('cat_id');
			$fieldData     = $app->input->post->get('fields', array(), 'array');
			$cat_id        = $fieldData[$fieldCategory->id]['main'];
		}

		if (!$cat_id)
		{
			$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
			$cat_id       = $rootCategory->id;
		}

		$files   = $app->input->get("judlfiles", array(), 'array');
		$context = $this->option . ".edit." . $this->context;

		if (isset($files))
		{
			$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
			$file_directory_tmp = $file_directory . "tmp/";
			$files              = array_values($files);
			foreach ($files AS $file)
			{
				
				if (isset($file['file_name']) && $file['file_name'])
				{
					$file_path = $file_directory_tmp . $file['file_name'];
					if (JFile::exists($file_path))
					{
						JFile::delete($file_path);
					}
				}

				
				if (isset($file['replace']) && $file['replace'])
				{
					$file_path = $file_directory_tmp . $file['replace'];
					if (JFile::exists($file_path))
					{
						JFile::delete($file_path);
					}
				}
			}
		}
		$app->setUserState($context . '.data', null);
		$app->setUserState($context . '.fieldsdata', null);
		$app->setUserState($context . '.changelogs', null);
		$app->setUserState($context . '.versions', null);
		$app->setUserState($context . '.files', null);
		$app->setUserState($context . '.related_documents', null);

		if ($doc_id)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT cat_id FROM #__judownload_documents_xref WHERE doc_id = $doc_id AND main = 1";
			$db->setQuery($query);
			$cat_id = $db->loadResult();
		}

		parent::cancel($key = null);

		if ($this->view_list == "pendingdocuments")
		{
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);
		}
		else
		{
			$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id");
		}
	}

	
	public function add()
	{
		$context = "$this->option.edit.$this->context";
		$app     = JFactory::getApplication();
		$app->setUserState($context . '.data', null);
		$app->setUserState($context . '.fieldsdata', null);
		$app->setUserState($context . '.changelogs', null);
		$app->setUserState($context . '.versions', null);
		$app->setUserState($context . '.files', null);
		$app->setUserState($context . '.related_documents', null);

		return parent::add();
	}

	
	public function remoteFile()
	{
		$model = $this->getmodel();
		$model->remoteFile();
		exit;
	}

	public function downloadFile()
	{
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
		$app    = JFactory::getApplication();
		$fileId = $app->input->get('fileId', 0);
		$db     = JFactory::getDbo();
		$query  = 'SELECT `doc_id`, `file_name`, `rename` FROM #__judownload_files WHERE id = ' . $fileId;
		$db->setQuery($query);
		$file = $db->loadObject();
		if ($file)
		{
			$documentObject = JUDownloadHelper::getDocumentById($file->doc_id);
			$fileDirectory  = JUDownloadFrontHelper::getDirectory('file_directory', 'media/com_judownload/files/');

			$version = $app->input->get('version', '');
			
			if (!$version || $version === $documentObject->version)
			{
				$filePath = JPATH_SITE . '/' . $fileDirectory . $file->doc_id . '/' . $file->file_name;
			}
			
			else
			{
				$query = "SELECT file_path FROM #__judownload_versions WHERE file_id = " . $fileId . " AND version = " . $db->quote($version);
				$db->setQuery($query);
				$versionFilePath = $db->loadResult();
				if (!$versionFilePath)
				{
					return false;
				}
				$filePath = JPATH_SITE . '/' . $fileDirectory . $file->doc_id . '/' . $versionFilePath;
			}

			$filePath       = JPath::clean($filePath);
			$downloadResult = JUDownloadHelper::downloadFile($filePath, $file->rename, 'php', '2048', true, true);
			if ($downloadResult !== true)
			{
				$this->setError($downloadResult);

				return false;
			}
		}

		return true;
	}
}
