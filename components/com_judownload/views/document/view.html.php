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

JHtml::addIncludePath(JPATH_SITE . '/components/com_judownload/helpers');

class JUDownloadViewDocument extends JUDLView
{
	public function display($tpl = null)
	{
		$this->item = $this->get('Item');

		
		if (!JUDownloadFrontHelperPermission::canViewDocument($this->item->id))
		{
			$user = JFactory::getUser();
			if ($user->id)
			{
				return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}
			else
			{
				$uri      = JUri::getInstance();
				$loginUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($uri), false);
				$app      = JFactory::getApplication();
				$app->redirect($loginUrl, JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE'), 'warning');

				return false;
			}
		}

		
		$app          = JFactory::getApplication();
		$this->user   = JFactory::getUser();
		$this->print  = $app->input->getBool('print', false);
		$this->model  = $this->getModel();
		$this->state  = $this->get('State');
		$this->params = $this->state->get('params');

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$limitStart = $app->input->getUint('limitstart', 0);

		$config        = JFactory::getConfig();
		$cookie_domain = $config->get('cookie_domain', '');
		$cookie_path   = $config->get('cookie_path', '/');

		
		if (isset($_COOKIE['judl_recently_viewed_documents']))
		{
			$recently_viewed_doc_array = explode(',', $_COOKIE['judl_recently_viewed_documents']);
			$recently_viewed_doc_array = array_unique($recently_viewed_doc_array);
			$key                       = array_search($this->item->id, $recently_viewed_doc_array);
			if ($key !== false)
			{
				unset($recently_viewed_doc_array[$key]);
			}
			else
			{
				
				if ($limitStart == 0)
				{
					$this->model->updateHits($this->item->id);
				}
			}

			$maxDocuments = $this->params->get('max_recently_viewed_documents', 12);
			if (count($recently_viewed_doc_array) >= $maxDocuments)
			{
				$recently_viewed_doc_array = array_slice($recently_viewed_doc_array, 0, $maxDocuments - 1);
			}
			array_unshift($recently_viewed_doc_array, $this->item->id);
			$recently_viewed_doc_array = implode(',', $recently_viewed_doc_array);
			setcookie('judl_recently_viewed_documents', $recently_viewed_doc_array, time() + (3600 * 24 * 15), $cookie_path, $cookie_domain);
		}
		else
		{
			
			if ($limitStart == 0)
			{
				$this->model->updateHits($this->item->id);
			}

			setcookie('judl_recently_viewed_documents', $this->item->id, time() + (3600 * 24 * 15), $cookie_path, $cookie_domain);
		}

		
		$topLevelCats = JUDownloadHelper::getCatsByLevel(1, $this->item->cat_id);
		if ($topLevelCats)
		{
			$this->tl_catid = $topLevelCats[0]->id;
		}

		
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

		$this->item->class_sfx = htmlspecialchars($this->item->class_sfx);

		
		$this->collection_popup = false;
		if (JUDLPROVERSION && $this->user->id > 0)
		{
			$this->collection_popup = true;

			$this->collections = $this->model->getCollectionPopup($this->item->id);
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$this->item->event = new stdClass();
		$context           = 'com_judownload.document';

		$results                              = $dispatcher->trigger('onContentAfterTitle', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results                                 = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results                                = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		$results                                     = $dispatcher->trigger('onBeforeDisplayJUDLComment', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->beforeDisplayJUDLComment = trim(implode("\n", $results));

		$results                                    = $dispatcher->trigger('onAfterDisplayJUDLComment', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->afterDisplayJUDLComment = trim(implode("\n", $results));

		$this->_prepareData();
		$this->_prepareDocument();

		$this->_setBreadcrumb();

		parent::display($tpl);
	}

	protected function _prepareData()
	{
		
		$this->token = JSession::getFormToken();

		
		$this->session = JFactory::getSession();

		
		$this->item->changelogs = JUDownloadFrontHelper::getChangeLogField($this->item->id);

		
		$this->item->versions = $this->model->getVersions($this->item->id);

		
		$this->item->related_documents = $this->model->getRelatedDocuments($this->item->id);
		if (count($this->item->related_documents))
		{
			foreach ($this->item->related_documents AS $documentRelated)
			{
				$documentRelated->link = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($documentRelated->id));
				$documentRelated->icon = JUDownloadHelper::getDocumentIcon($documentRelated->icon);
			}
		}

		
		$this->item->files               = JUDownloadFrontHelperDocument::getFilesByDocumentId($this->item->id);
		$this->can_download_document     = JUDownloadFrontHelperPermission::canDownloadDocument($this->item->id, false);
		$this->display_download_rule_msg = $this->params->get('show_rule_messages', 'modal');

		
		$additionFields = array();
		if (trim($this->item->external_link))
		{
			$additionFields[] = 'external_link';
		}

		if ($this->item->license_id && $this->item->confirm_license)
		{
			$additionFields[] = 'license_id';
			$additionFields[] = 'confirm_license';
		}

		$this->item->fieldGallery = JUDownloadFrontHelperField::getField('gallery', $this->item);
		$this->item->fields       = JUDownloadFrontHelperField::getFields($this->item, 2, array(), array('gallery'), $additionFields);

		
		$this->item->license = JUDownloadFrontHelper::getLicense($this->item->license_id);

		$user = JFactory::getUser();
		$uri  = JUri::getInstance();
		
		if ($this->item->checked_out > 0 && $this->item->checked_out != $user->get('id'))
		{
			if (JUDownloadFrontHelperPermission::canCheckInDocument($this->item->id))
			{
				$this->item->checkin_link = JRoute::_('index.php?option=com_judownload&task=forms.checkin&id=' . $this->item->id . '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode(urlencode($uri)));
			}
		}
		else
		{
			$this->item->edit_link = JRoute::_('index.php?option=com_judownload&task=form.edit&id=' . $this->item->id . '&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($this->item->id));

			if ($this->item->published == 1)
			{
				$this->item->editstate_link = JRoute::_('index.php?option=com_judownload&task=forms.unpublish&id=' . $this->item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($this->item->id));
			}
			else
			{
				$this->item->editstate_link = JRoute::_('index.php?option=com_judownload&task=forms.publish&id=' . $this->item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($this->item->id));
			}
		}

		$this->item->delete_link = JRoute::_('index.php?option=com_judownload&task=forms.delete&id=' . $this->item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($this->item->id));

		
		if ($this->item->is_subscriber)
		{
			$this->item->unsubscribe_link = $this->model->getUnsubscribeLink($this->item->id);
		}
		else
		{
			$this->item->subscribe_link = $this->model->getSubscribeLink($this->item->id);
		}

		$this->item->contact_link = JRoute::_(JUDownloadHelperRoute::getContactRoute($this->item->id));

		$this->item->report_link = JRoute::_(JUDownloadHelperRoute::getReportDocumentRoute($this->item->id));

		$this->item->print_link = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id) . '&layout=print&tmpl=component&print=1');

		
		$this->item->comment = new stdClass();
		
		$this->item->comment->total_comments_no_filter = $this->model->getTotalCommentsOfDocument($this->item->id);

		
		$this->root_comment = JUDownloadFrontHelperComment::getRootComment();

		
		$langArray   = JHtml::_('contentlanguage.existing');
		$JAll        = new JObject();
		$JAll->text  = JText::_('JALL');
		$JAll->value = '*';
		array_unshift($langArray, $JAll);
		$this->langArray = $langArray;

		
		$this->item->comment->items = $this->get('Items');
		foreach ($this->item->comment->items AS $comment)
		{
			if (JUDownloadFrontHelperPermission::canCheckInComment($comment->id))
			{
				$uri                    = JUri::getInstance();
				$comment->checkout_link = 'index.php?option=com_judownload&task=modcomments.checkin&cid=' . $comment->id . '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode(urlencode($uri));
			}
			else
			{
				$comment->checkout_link = '';
			}
		}

		$this->item->comment->pagination = $this->get('Pagination');

		
		$this->item->comment->total_comments = $this->get('Total');

		
		$this->item->comment->parent_id = $this->root_comment->id;

		
		$this->website_field_in_comment_form = $this->params->get('website_field_in_comment_form', 0);
		$this->min_comment_characters        = $this->params->get('min_comment_characters', 20);
		$this->max_comment_characters        = $this->params->get('max_comment_characters', 1000);

		
		$this->allow_vote_comment      = $this->params->get('allow_vote_comment', 1);
		$this->allow_vote_down_comment = $this->params->get('allow_vote_down_comment', 1);

		
		$this->order_comment_name_array = $this->model->getCommentOrderingOptions();

		
		$this->order_comment_dir_array = array(
			'ASC'  => JText::_('COM_JUDOWNLOAD_ASC'),
			'DESC' => JText::_('COM_JUDOWNLOAD_DESC')
		);

		
		$this->total_stars          = $this->params->get('number_rating_stars', 5);
		$this->filter_comment_stars = array('' => JText::_('COM_JUDOWNLOAD_ANY_STAR'));
		for ($i = 0; $i <= $this->total_stars; $i++)
		{

			$score                              = ($i == 0) ? 0 : round((($i - 1) * 10) / $this->total_stars, 5) . ',' . round(($i * 10) / $this->total_stars, 5);
			$this->filter_comment_stars[$score] = JText::plural('COM_JUDOWNLOAD_N_STAR', $i);
		}

		
		if ($this->params->get('filter_comment_language', 0))
		{
			$this->list_lang_comment = $this->escape($this->state->get('list.lang'));
		}
		$this->list_order_comment  = $this->escape($this->state->get('list.ordering'));
		$this->list_dir_comment    = $this->escape($this->state->get('list.direction'));
		$this->filter_comment_star = $this->state->get('list.star_filter', '');

		
		$this->website  = $this->title = $this->email = $this->comment = $this->name = '';
		$this->language = '*';
		$form           = $this->session->get('judownload_commentform_' . $this->item->id, null);
		$this->form     = $form;
		if (!empty($form) && $form['parent_id'] == $this->root_comment->id)
		{
			$this->title    = $form['title'];
			$this->name     = $form['guest_name'];
			$this->email    = $form['guest_email'];
			$this->comment  = $form['comment'];
			$this->website  = (isset($form['website'])) ? $form['website'] : '';
			$this->language = $form['comment_language'];
		}
	}

	protected function _prepareDocument()
	{
		$document = JFactory::getDocument();
		$uri      = clone JUri::getInstance();

		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$canonicalLink = $domain . JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id, $this->_layout), false);

		JUDownloadFrontHelper::setCanonical($canonicalLink);

		$imageUrl = JUDownloadHelper::getDocumentIcon($this->item->icon);
		
		$document->addCustomTag('<meta property="og:title" content="' . $this->item->title . '" />');
		$document->addCustomTag('<meta property="og:type" content="website" />');
		if ($imageUrl)
		{
			$document->addCustomTag('<meta property="og:image" content="' . JUri::root(true) . "/media/com_judownload/images/document/" . $this->item->icon . '" />');
		}
		$document->addCustomTag('<meta property="og:url" content="' . $canonicalLink . '" />');

		JUDownloadFrontHelperSeo::seo($this);
	}

	protected function _setBreadcrumb()
	{
		$categoryId = JUDownloadFrontHelperCategory::getMainCategoryId($this->item->id);

		$app          = JFactory::getApplication();
		$pathway      = $app->getPathway();
		$pathwayArray = array();
		if ($categoryId)
		{
			$pathwayArray = JUDownloadFrontHelperBreadcrumb::getBreadcrumbCategory($categoryId);
		}
		else
		{
			$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();
		}

		$linkDocument   = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id));
		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem($this->item->title, $linkDocument);

		if ($this->_layout == 'changelogs')
		{
			$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem('CHANGELOGS');
		}
		elseif ($this->_layout == 'versions')
		{
			$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem('VERSIONS');
		}
		elseif ($this->_layout == 'print')
		{
			$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem('PRINT');

			$document = JFactory::getDocument();
			$document->setMetaData('robots', 'noindex, nofollow');
		}

		$pathway->setPathway($pathwayArray);
	}
}
