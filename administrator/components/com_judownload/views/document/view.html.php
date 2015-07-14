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


class JUDownloadViewDocument extends JUDLViewAdmin
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		
		$this->form   = $this->get('Form');
		$this->item   = $this->get('Item');
		$this->model  = $this->getModel();
		$this->app    = JFactory::getApplication();
		$cat_id       = $this->item->cat_id ? $this->item->cat_id : $this->app->input->get('cat_id');
		$this->params = JUDownloadHelper::getParams(null, $this->item->id);
		if ($cat_id == JUDownloadFrontHelperCategory::getRootCategory()->id && !$this->params->get('allow_add_doc_to_root', 0))
		{
			JError::raiseError(500, JText::_('COM_JUDOWNLOAD_CAN_NOT_ADD_DOCUMENT_TO_ROOT_CATEGORY'));

			return false;
		}

		if ($tempDocument = JUDownloadHelper::getTempDocument($this->item->id))
		{
			$editPendingDocLink = '<a href="index.php?option=com_judownload&task=document.edit&approve=1&id=' . $tempDocument->id . '">' . $tempDocument->title . '</a>';
			JError::raiseNotice('', JText::sprintf('COM_JUDOWNLOAD_THIS_DOCUMENT_HAS_PENDING_DOCUMENT_X_PLEASE_APPROVE_PENDING_DOCUMENT_FIRST', $editPendingDocLink));
		}

		if ($this->item->approved < 0)
		{
			$oriDocId       = abs($this->item->approved);
			$oriDocObj      = JUDownloadHelper::getDocumentById($oriDocId);
			$editOriDocLink = '<a href="index.php?option=com_judownload&task=document.edit&id=' . $oriDocId . '">' . $oriDocObj->title . '</a>';
			JError::raiseNotice('', JText::sprintf('COM_JUDOWNLOAD_ORIGINAL_DOCUMENT_X', $editOriDocLink));
		}

		$this->script                         = $this->get('Script');
		$this->plugins                        = $this->get('Plugins');
		$this->fieldsetDetails                = $this->model->getCoreFields('details');
		$this->fieldsetPublishing             = $this->model->getCoreFields('publishing');
		$this->fieldsetTemplateStyleAndLayout = $this->model->getCoreFields('template_style');
		$this->fieldsetMetadata               = $this->model->getCoreFields('metadata');
		$this->fieldCatid                     = JUDownloadFrontHelperField::getField('cat_id', $this->item);
		$this->fieldGallery                   = JUDownloadFrontHelperField::getField('gallery', $this->item);
		$this->files                          = $this->get('Files');
		$this->changeLogs                     = $this->get('ChangeLogs');
		$this->versions                       = $this->get('Versions');
		
		$this->extraFields      = $this->get('ExtraFields');
		$this->fieldsData       = $this->app->getUserState("com_judownload.edit.document.fieldsdata", array());
		$this->relatedDocuments = $this->get('RelatedDocuments');
		$this->canDo            = JUDownloadHelper::getActions('com_judownload', 'category', $this->item->cat_id);

		
		$this->addToolBar();

		
		$this->setDocument();

		
		parent::display($tpl);
	}

	
	protected function addToolBar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$isNew      = ($this->item->id == 0);
		$user       = JFactory::getUser();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo      = JUDownloadHelper::getActions('com_judownload', 'document', $this->item->id);
		JToolBarHelper::title(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_DOCUMENT' : ($isNew ? 'ADD_DOCUMENT' : 'EDIT_DOCUMENT'))), 'document-add');

		if ($isNew && $user->authorise('judl.document.create', 'com_judownload'))
		{
			JToolBarHelper::apply('document.apply');
			JToolBarHelper::save('document.save');
			JToolBarHelper::save2new('document.save2new');
			JToolBarHelper::cancel('document.cancel');
		}
		else
		{
			if ($app->input->get('approve') == 1)
			{
				JToolBarHelper::save('pendingdocument.save');
				JToolBarHelper::cancel('pendingdocument.cancel', 'JTOOLBAR_CLOSE');
			}
			else
			{
				if (!$checkedOut)
				{
					
					if ($canDo->get('judl.document.edit') || ($canDo->get('judl.document.edit.own') && $this->item->created_by == $userId))
					{
						JToolBarHelper::apply('document.apply');
						JToolBarHelper::save('document.save');
						
						if ($canDo->get('judl.document.create'))
						{
							JToolBarHelper::save2copy('document.save2copy');
							JToolBarHelper::save2new('document.save2new');
						}
					}
				}
				JToolBarHelper::cancel('document.cancel', 'JTOOLBAR_CLOSE');
			}
		}

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->addButtonPath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/button");
		$bar->appendButton('JUHelp', 'help', JText::_('JTOOLBAR_HELP'));
	}

	
	protected function setDocument()
	{
		$isNew      = ($this->item->id == 0);
		$userId     = JFactory::getUser()->id;
		$document   = JFactory::getDocument();
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$document->setTitle(JText::_('COM_JUDOWNLOAD_PAGE_' . ($checkedOut ? 'VIEW_DOCUMENT' : ($isNew ? 'ADD_DOCUMENT' : 'EDIT_DOCUMENT'))));

		JUDownloadFrontHelper::loadjQueryUI();

		$document->addStyleSheet(JUri::root() . "administrator/components/com_judownload/assets/css/approval.css");
		$document->addScript(JUri::root() . "components/com_judownload/assets/js/handlebars.min.js");

		$document->addStyleSheet(JUri::root() . 'components/com_judownload/assets/plupload/css/jquery.plupload.queue.css');
		$document->addScript(JUri::root() . "components/com_judownload/assets/plupload/js/plupload.full.min.js");
		$document->addScript(JUri::root() . "components/com_judownload/assets/plupload/js/jquery.plupload.queue.min.js");

		JUDownloadHelper::formValidation();
		$document->addScript(JUri::root() . $this->script);

		JText::script('COM_JUDOWNLOAD_INVALID_IMAGE');
		JText::script('COM_JUDOWNLOAD_INVALID_FILE_NAME');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_FILE_BECAUSE_IT_IS_EMPTY');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_THIS_FILE_PLEASE_RECHECK_MIMETYPE_FILE');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_TRANSFER_FILE');
		JText::script('COM_JUDOWNLOAD_OTHER_FILE_IS_UPLOADING_DO_YOU_WANT_TO_CANCEL_TO_UPLOAD_NEW_FILE');
		JText::script('COM_JUDOWNLOAD_YOU_HAVE_TO_UPLOAD_AT_LEAST_ONE_FILE');
		JText::script('COM_JUDOWNLOAD_YOU_HAVE_NOT_ENTERED_SOURCE_URL');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_UPLOAD_THIS_FILE_DOCUMENT_REACH_MAX_UPLOAD_FILES_N_FILES');
		JText::script('COM_JUDOWNLOAD_PLEASE_UPLOAD_A_FILE');
		JText::script('COM_JUDOWNLOAD_REMOVE');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_ADD_IMAGE_BECAUSE_MAX_NUMBER_OF_IMAGE_IS_N');
		JText::script('COM_JUDOWNLOAD_CAN_NOT_RESTORE_THIS_FILE_DOCUMENT_REACH_MAX_UPLOAD_N_FILES');
		JText::script('COM_JUDOWNLOAD_TOGGLE_TO_PUBLISH');
		JText::script('COM_JUDOWNLOAD_TOGGLE_TO_UNPUBLISH');
		JText::script('COM_JUDOWNLOAD_CLICK_TO_REMOVE');
		JText::script('COM_JUDOWNLOAD_YOU_MUST_UPLOAD_AT_LEAST_ONE_IMAGE');
		JText::script('COM_JUDOWNLOAD_FILE_TITLE');
		JText::script('COM_JUDOWNLOAD_FILE_NAME');
		JText::script('COM_JUDOWNLOAD_DESCRIPTION');
		JText::script('COM_JUDOWNLOAD_FIELD_TITLE');
		JText::script('COM_JUDOWNLOAD_FIELD_DESCRIPTION');
		JText::script('COM_JUDOWNLOAD_FIELD_PUBLISHED');
		JText::script('COM_JUDOWNLOAD_UPDATE');
		JText::script('COM_JUDOWNLOAD_CANCEL');
	}
}