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

	
	public function sendemail()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app  = JFactory::getApplication();
		$data = array();

		
		$data['from_name']  = $app->input->post->get('name', '', 'string');
		$data['from_email'] = $app->input->post->get('email', '', 'string');
		$data['to_email']   = $app->input->post->get('to_email', '', 'string');
		$data['doc_id']     = $app->input->getInt('id', 0);

		JUDownloadHelper::obCleanData();
		if (!JUDownloadFrontHelperMail::sendEmailByEvent('document.sendtofriend', $data['doc_id'], $data))
		{
			echo '<label class="control-label"></label><div class="controls"><span class="alert alert-error">' . JText::_('COM_JUDOWNLOAD_FAIL_TO_SEND_EMAIL') . '</span></div>';
			exit;
		}
		else
		{
			echo '<label class="control-label"></label><div class="controls"><span class="alert alert-success">' . JText::_('COM_JUDOWNLOAD_SEND_EMAIL_SUCCESSFULLY') . '</span></div>';
			exit;
		}
	}

	
	public function singleRating()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		
		$app        = JFactory::getApplication();
		$data       = $app->input->getArray($_POST);
		$documentId = $data['doc_id'];
		$params     = JUDownloadHelper::getParams(null, $documentId);

		JUDownloadHelper::obCleanData();

		
		$canVoteDocument = JUDownloadFrontHelperPermission::canRateDocument($documentId);

		if (!$canVoteDocument)
		{
			echo JText::_('COM_JUDOWNLOAD_YOU_CAN_NOT_VOTE_ON_THIS_DOCUMENT');
			exit;
		}

		
		if (($data['ratingValue'] <= 0) && ($data['ratingValue'] > 10))
		{
			echo JText::_('COM_JUDOWNLOAD_INVALID_RATING_VALUE');
			exit;
		}

		$inputCookie = $app->input->cookie;

		$ratingInterval = $params->get('rating_interval', 86400);
		$user           = JFactory::getUser();
		$timeNow        = JFactory::getDate()->toSql();
		$timeNowStamp   = strtotime($timeNow);
		if ($user->get('guest'))
		{
			
			$lastTimeRated = $inputCookie->get('judl-document-rated-' . $documentId, null);
			if ($lastTimeRated != null)
			{
				if ($timeNowStamp > $lastTimeRated)
				{
					if ($timeNowStamp - $lastTimeRated < $ratingInterval)
					{
						echo JText::_('COM_JUDOWNLOAD_YOU_ARE_ALREADY_VOTED_ON_THIS_DOCUMENT');
						exit;
					}
				}
			}
		}
		else
		{
			$lastTimeRated = JUDownloadFrontHelperRating::getLastTimeVoteDocumentOfUser($user->id, $documentId);
			if (!$lastTimeRated)
			{
				$lastTimeRated = 0;
			}
			$lastTimeRated = strtotime($lastTimeRated);
			if ($lastTimeRated > 0)
			{
				if ($timeNowStamp > $lastTimeRated)
				{
					if ($timeNowStamp - $lastTimeRated < $ratingInterval)
					{
						echo JText::_('COM_JUDOWNLOAD_YOU_ARE_ALREADY_VOTED_ON_THIS_DOCUMENT');
						exit;
					}
				}
			}
		}

		$dataValid['ratingValue'] = $data['ratingValue'];

		$model = $this->getModel();

		JUDownloadHelper::obCleanData();
		if ($model->saveRating($dataValid, $documentId))
		{
			echo JText::_('COM_JUDOWNLOAD_THANK_YOU_FOR_VOTING');
		}
		else
		{
			echo JText::_('COM_JUDOWNLOAD_VOTING_FAILED_PLEASE_CONTACT_ADMINISTRATOR');
		}
		exit;
	}

	
	public function multiRating()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app        = JFactory::getApplication();
		$data       = $app->input->getArray($_POST);
		$documentId = $data['doc_id'];
		$params     = JUDownloadHelper::getParams(null, $documentId);

		
		$canRateDocument = JUDownloadFrontHelperPermission::canRateDocument($documentId);

		JUDownloadHelper::obCleanData();

		if (!$canRateDocument)
		{
			echo JText::_('COM_JUDOWNLOAD_YOU_CAN_NOT_VOTE_ON_THIS_DOCUMENT');
			exit;
		}

		if (!JUDownloadHelper::hasMultiRating())
		{
			echo JText::_('COM_JUDOWNLOAD_MULTI_RATING_HAS_BEEN_DISABLED_PLEASE_CONTACT_ADMINISTRATOR');
			exit;
		}

		$inputCookie = $app->input->cookie;

		$ratingInterval = $params->get('rating_interval', 86400);
		$user           = JFactory::getUser();
		$timeNow        = JFactory::getDate()->toSql();
		$timeNowStamp   = strtotime($timeNow);
		if ($user->get('guest'))
		{
			
			$lastTimeRated = $inputCookie->get('judl-document-rated-' . $documentId, null);
			if ($lastTimeRated != null)
			{
				if ($timeNowStamp > $lastTimeRated)
				{
					if ($timeNowStamp - $lastTimeRated < $ratingInterval)
					{
						echo JText::_('COM_JUDOWNLOAD_YOU_ARE_ALREADY_VOTED_ON_THIS_DOCUMENT');
						exit;
					}
				}
			}
		}
		else
		{
			$lastTimeRated = JUDownloadFrontHelperRating::getLastTimeVoteDocumentOfUser($user->id, $documentId);
			if (!$lastTimeRated)
			{
				$lastTimeRated = 0;
			}
			$lastTimeRated = strtotime($lastTimeRated);
			if ($lastTimeRated > 0)
			{
				if ($timeNowStamp > $lastTimeRated)
				{
					if ($timeNowStamp - $lastTimeRated < $ratingInterval)
					{
						
						echo JText::_('COM_JUDOWNLOAD_YOU_ARE_ALREADY_VOTED_ON_THIS_DOCUMENT');
						exit;
					}
				}
			}
		}

		
		$dataValid     = array();
		$mainCatId     = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);
		$criteriaArray = JUDownloadFrontHelperCriteria::getCriteriasByCatId($mainCatId);
		$postCriteria  = $data['criteria'];

		if (count($criteriaArray) > 0)
		{
			foreach ($criteriaArray AS $key => $criteria)
			{
				if ($criteria->required)
				{
					if (isset($postCriteria[$criteria->id]) && $postCriteria[$criteria->id] > 0 && $postCriteria[$criteria->id] <= 10)
					{
						$criteria->value = $postCriteria[$criteria->id];
					}
					else
					{
						
						echo JText::_('Invalid Field ' . $criteria->title);
						exit;
					}
				}
				else
				{
					if (isset($postCriteria[$criteria->id]) && $postCriteria[$criteria->id] > 0 && $postCriteria[$criteria->id] <= 10)
					{
						$criteria->value = $postCriteria[$criteria->id];
					}
					else
					{
						unset($criteriaArray[$key]);
					}
				}
			}
		}
		else
		{
			echo JText::_('COM_JUDOWNLOAD_VOTING_FAILED_PLEASE_CONTACT_ADMINISTRATOR');
			exit;
		}

		$model = $this->getModel();
		JUDownloadHelper::obCleanData();
		if ($model->saveRating($dataValid, $documentId, $criteriaArray))
		{
			echo JText::_('COM_JUDOWNLOAD_THANK_YOU_FOR_VOTING');

		}
		else
		{
			echo JText::_('COM_JUDOWNLOAD_VOTING_FAILED_PLEASE_CONTACT_ADMINISTRATOR');
		}
		exit;
	}

	
	public function redirectUrl()
	{
		$app     = JFactory::getApplication();
		$fieldId = $app->input->getInt('field_id');
		$docId   = $app->input->getInt('doc_id');

		$field = JUDownloadFrontHelperField::getField($fieldId, $docId);
		$field->redirectUrl();
	}

	
	public function addComment()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$user  = JFactory::getUser();
		$model = $this->getModel();

		
		$rootComment = JUDownloadFrontHelperComment::getRootComment();

		
		$data = $_POST;

		
		$documentId = $data['doc_id'];
		$params     = JUDownloadHelper::getParams(null, $documentId);
		$parentId   = $data['parent_id'];

		
		$model->setSessionCommentForm($documentId);

		
		if (strlen($data['title']) < 6)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_TITLE'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

			return false;
		}

		
		if (strlen($data['guest_name']) < 1)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_NAME'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

			return false;
		}

		
		if (isset($data['guest_email']))
		{
			if (!preg_match('/^[\w\.-]+@[\w\.-]+\.[\w\.-]{2,6}$/', $data['guest_email']))
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_EMAIL'));
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}

		
		if (isset($data['website']))
		{
			if (!preg_match('/^(https?:\/\/)?([\w\.-]+)\.([\w\.-]{2,6})([\/\w \.-]*)*\/?$/i', $data['website']))
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_WEBSITE'));
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}

		
		if (isset($data['comment_language']))
		{
			$langArray = JHtml::_('contentlanguage.existing');
			$langKey   = array_keys($langArray);
			array_unshift($langKey, '*');
			if (!in_array($data['comment_language'], $langKey))
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_LANGUAGE'));
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}

		
		$minCharacter     = $params->get('min_comment_characters', 20);
		$maxCharacter     = $params->get('max_comment_characters', 1000);
		$comment          = $data['comment'];
		$comment          = JUDownloadFrontHelperComment::parseCommentText($comment, $documentId);
		$comment          = strip_tags($comment);
		$commentCharacter = strlen($comment);
		if ($commentCharacter < $minCharacter || $commentCharacter > $maxCharacter)
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_INVALID_COMMENT'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

			return false;
		}

		
		$showCaptcha = JUDownloadFrontHelperPermission::showCaptchaWhenComment($documentId);

		if ($showCaptcha)
		{
			$validCaptcha = JUDownloadFrontHelperCaptcha::checkCaptcha();
			
			if (!$validCaptcha)
			{
				if ($parentId == $rootComment->id)
				{
					$form = '#judl-comment-form';
				}
				else
				{
					$form = '#comment-reply-wrapper-' . $parentId;
				}

				$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_CAPTCHA'));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId . $form, false));

				return false;
			}
		}

		
		if ($user->get('guest'))
		{
			if (!$model->checkNameOfGuest($documentId))
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_COMMENT_ON_THIS_DOCUMENT'));
				$this->setMessage($model->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}

			if (!$model->checkEmailOfGuest())
			{
				$this->setMessage($model->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}

		if ($parentId == $rootComment->id)
		{
			
			$canComment = JUDownloadFrontHelperPermission::canComment($documentId, $data['guest_email']);
			if (!$canComment)
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_COMMENT_ON_THIS_DOCUMENT'));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}
		elseif ($parentId > 0 && $parentId != $rootComment->id)
		{
			
			$canReplyComment = JUDownloadFrontHelperPermission::canReplyComment($documentId, $parentId);
			if (!$canReplyComment)
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_REPLY_THIS_COMMENT'));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}
		else
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_DATA'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

			return false;
		}

		
		$dataValid = array();
		if ($parentId == $rootComment->id)
		{
			$canRateDocument = JUDownloadFrontHelperPermission::canRateDocument($documentId);
			if ($canRateDocument)
			{
				$dataValid = $this->validateCriteria($data, $parentId);
				if (!$dataValid)
				{
					$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_RATING_VALUE'));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

					return false;
				}
			}
		}

		$requiredPostNames = array('title', 'guest_name', 'guest_email', 'comment', 'parent_id', 'doc_id');

		if ($params->get('website_field_in_comment_form', 0) == 2)
		{
			array_push($requiredPostNames, 'website');
		}

		if ($parentId == $rootComment->id && $params->get('filter_comment_language', 0))
		{
			array_push($requiredPostNames, 'comment_language');
		}

		foreach ($requiredPostNames AS $requiredPostName)
		{
			if (trim($data[$requiredPostName]) == '')
			{
				$this->setError(JText::_('COM_JUDOWNLOAD_INVALID_INPUT_DATA'));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $documentId, false));

				return false;
			}
		}

		$acceptedPostNames = array('title', 'guest_name', 'guest_email', 'language', 'website', 'comment', 'parent_id', 'doc_id', 'subscribe');
		if ($params->get('website_field_in_comment_form', 0) == 2 || $params->get('website_field_in_comment_form', 0) == 1)
		{
			array_push($acceptedPostNames, 'website');
		}

		if ($params->get('filter_comment_language', 0))
		{
			array_push($acceptedPostNames, 'comment_language');
		}

		foreach ($acceptedPostNames AS $acceptedPostName)
		{
			if (isset($data[$acceptedPostName]))
			{
				$dataValid[$acceptedPostName] = $data[$acceptedPostName];
			}
		}

		$newCommentId = $model->saveComment($dataValid);
		if (!$newCommentId)
		{
			$this->setError($model->getError());
			$this->setMessage($this->getError(), 'error');
			$redirectUrl = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentId), false);
			$this->setRedirect($redirectUrl);

			return false;
		}

		
		$session                      = JFactory::getSession();
		$timeNow                      = JFactory::getDate()->toSql();
		$timeNowStamp                 = strtotime($timeNow);
		$sessionCommentOnDocumentTime = 'judl-commented-' . $documentId;
		$sessionCommentTime           = 'judl-commented';
		$session->set($sessionCommentOnDocumentTime, $timeNowStamp);
		$session->set($sessionCommentTime, $timeNowStamp);
		
		$session->clear('judownload_commentform_' . $documentId);

		
		$this->setMessage(JText::_('COM_JUDOWNLOAD_ADD_COMMENT_SUCCESSFULLY'));
		$redirectUrl = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentId) . '#comment-item-' . $newCommentId, false);
		$this->setRedirect($redirectUrl);

		return true;
	}

	
	public function deleteComment()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$app           = JFactory::getApplication();
		$commentId     = $app->input->getInt('comment_id', 0);
		$commentObject = JUDownloadFrontHelperComment::getCommentObject($commentId);
		$documentId    = $commentObject->doc_id;

		
		$canDeleteComment = JUDownloadFrontHelperPermission::canDeleteComment($commentId);
		if ($canDeleteComment)
		{
			$commentModel = $this->getModel('Modcomment', 'JUDownloadModel');

			if ($commentModel->delete($commentId))
			{
				$this->setMessage(JText::_('COM_JUDOWNLOAD_DELETE_COMMENT_SUCCESSFULLY'));
				$this->setRedirect(JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentId), false));

				return true;
			}
		}

		$this->setMessage(JText::_('COM_JUDOWNLOAD_DELETE_COMMENT_FAILED'), 'error');
		$this->setRedirect(JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentId), false));

		return false;
	}

	
	public function voteComment()
	{
		
		if (!JSession::checkToken('get'))
		{
			$return                = array();
			$return['message']     = JText::_('JINVALID_TOKEN');
			$return['like_system'] = null;
			$return['vote_type']   = null;

			JUDownloadHelper::obCleanData();
			echo json_encode($return);
			exit();
		}

		$model     = $this->getModel();
		$app       = JFactory::getApplication();
		$commentId = $app->input->getInt('id', 0);

		$model->voteComment($commentId);
	}

	
	public function quoteComment()
	{
		$app        = JFactory::getApplication();
		$commentId  = $app->input->getInt('comment_id', 0);
		$commentObj = JUDownloadFrontHelperComment::getCommentObject($commentId);

		JUDownloadHelper::obCleanData();
		$name = ($commentObj->user_id > 0) ? JFactory::getUser($commentObj->user_id)->name : $commentObj->guest_name;
		echo $quote = '[quote="' . $name . '"]' . $commentObj->comment . '[/quote]';
		exit();
	}

	
	public function updateComment()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$user  = JFactory::getUser();
		$model = $this->getModel();

		
		$app            = JFactory::getApplication();
		$data           = $app->input->getArray($_POST);
		$documentId     = $data['doc_id'];
		$commentId      = $data['comment_id'];
		$canEditComment = JUDownloadFrontHelperPermission::canEditComment($commentId);
		$redirectUrl    = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentId) . '#comment-item-' . $commentId);

		if (!$canEditComment)
		{
			$this->setMessage(JText::_('COM_JUDOWNLOAD_UPDATE_COMMENT_ERROR'));
			$this->setRedirect($redirectUrl);

			return false;
		}
		$params = JUDownloadHelper::getParams(null, $documentId);

		
		$ratingValue = $this->validateCriteria($data);
		if ($ratingValue)
		{
			$data = array_merge($data, $ratingValue);
		}
		else
		{
			$this->setMessage(JText::_('COM_JUDOWNLOAD_UPDATE_COMMENT_ERROR'));
			$this->setRedirect($redirectUrl);

			return false;
		}

		JUDownloadHelper::obCleanData();
		if ($model->updateComment($data, $params))
		{
			
			$logData = array(
				'user_id'   => $user->id,
				'event'     => 'comment.edit',
				'item_id'   => $commentId,
				'doc_id'    => $documentId,
				'value'     => 0,
				'reference' => '',
			);
			JUDownloadFrontHelperLog::addLog($logData);
			$this->setMessage(JText::_('COM_JUDOWNLOAD_UPDATE_COMMENT_SUCCESSFULLY'));
			$this->setRedirect($redirectUrl);

			return true;
		}
		else
		{
			$this->setMessage(JText::_('COM_JUDOWNLOAD_UPDATE_COMMENT_ERROR'));
			$this->setRedirect($redirectUrl);

			return false;
		}
	}

	
	public function validateCriteria($data)
	{
		$documentId = $data['doc_id'];
		$params     = JUDownloadHelper::getParams(null, $documentId);

		
		$dataValid       = array();
		$canRateDocument = JUDownloadFrontHelperPermission::canRateDocument($documentId);
		if ($canRateDocument && $params->get('enable_doc_rate_in_comment_form', 1))
		{
			$mainCatId     = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);
			$criteriaArray = JUDownloadFrontHelperCriteria::getCriteriasByCatId($mainCatId);
			$postCriteria  = $data['criteria'];
			if (count($criteriaArray) > 0)
			{
				foreach ($criteriaArray AS $key => $criteria)
				{
					if ($criteria->required)
					{
						if (isset($postCriteria[$criteria->id]) && $postCriteria[$criteria->id] > 0 && $postCriteria[$criteria->id] <= 10)
						{
							$criteria->value = $postCriteria[$criteria->id];
						}
						else
						{
							
							echo JText::_('Invalid Field ' . $criteria->title);
							exit;
						}
					}
					else
					{
						if (isset($postCriteria[$criteria->id]) && $postCriteria[$criteria->id] > 0 && $postCriteria[$criteria->id] <= 10)
						{
							$criteria->value = $postCriteria[$criteria->id];
						}
						else
						{
							unset($criteriaArray[$key]);
						}
					}
				}

				$dataValid['criteria_array'] = $criteriaArray;
			}
			else
			{
				
				if ($params->get('require_doc_rate_in_comment_form', 1))
				{
					if (($data['judl_comment_rating_single'] <= 0) && ($data['judl_comment_rating_single'] > 10))
					{
						return false;
					}

					$dataValid['ratingValue'] = $data['judl_comment_rating_single'];
				}
			}
		}

		return $dataValid;
	}
}
