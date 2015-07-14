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

class JUDownloadViewTag extends JUDLView
{
	public function display($tpl = null)
	{
		$this->model  = $this->getModel();
		$this->state  = $this->get('State');
		$this->params = $this->state->params;
		$this->tag    = $this->get('Tag');

		if (!is_object($this->tag))
		{
			JError::raiseError(404, JText::_('COM_JUDOWNLOAD_TAG_NOT_FOUND'));

			return false;
		}

		$user        = JFactory::getUser();
		$uri         = JUri::getInstance();
		$this->items = $this->get('Items');
		foreach ($this->items as $item)
		{
			$item->report_link = JRoute::_(JUDownloadHelperRoute::getReportDocumentRoute($item->id));

			
			if ($item->checked_out > 0 && $item->checked_out != $user->get('id'))
			{
				if (JUDownloadFrontHelperPermission::canCheckInDocument($item->id))
				{
					$item->checkin_link = JRoute::_('index.php?option=com_judownload&task=forms.checkin&id=' . $item->id . '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode(urlencode($uri)));
				}
			}
			else
			{
				$item->edit_link = JRoute::_('index.php?option=com_judownload&task=form.edit&id=' . $item->id . '&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($item->id));

				if ($item->published == 1)
				{
					$item->editstate_link = JRoute::_('index.php?option=com_judownload&task=forms.unpublish&id=' . $item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($item->id));
				}
				else
				{
					$item->editstate_link = JRoute::_('index.php?option=com_judownload&task=forms.publish&id=' . $item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($item->id));
				}
			}

			$item->delete_link = JRoute::_('index.php?option=com_judownload&task=forms.delete&id=' . $item->id . '&return=' . base64_encode(urlencode($uri)) . '&' . JSession::getFormToken() . '=1&Itemid=' . JUDownloadHelperRoute::findItemIdOfDocument($item->id));

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$item->event = new stdClass();
			$context     = 'com_judownload.document_list';

			$results                        = $dispatcher->trigger('onContentAfterTitle', array($context, &$this->item, &$this->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results                           = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this->item, &$this->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results                          = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this->item, &$this->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}
		$this->pagination = $this->get('Pagination');
		$this->token      = JSession::getFormToken();

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		
		$this->show_feed = JUDLPROVERSION ? $this->params->get('rss_display_icon', 1) : 0;

		$rssLink        = JRoute::_(JUDownloadHelperRoute::getTagRoute($this->tag->id, false, true));
		$this->rss_link = JRoute::_($rssLink, false);

		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareData();
		$this->_prepareDocument();
		$this->_setBreadcrumb();

		parent::display($tpl);
	}

	protected function _prepareData()
	{
		
		$this->order_name_array = JUDownloadFrontHelperField::getFrontEndOrdering();
		$this->order_dir_array  = JUDownloadFrontHelperField::getFrontEndDirection();
		$this->document_order   = $this->escape($this->state->get('list.ordering'));
		$this->document_dir     = $this->escape($this->state->get('list.direction', 'ASC'));
		
		$this->document_columns = (int) $this->params->get('document_columns', 2);
		if (!is_numeric($this->document_columns) || ($this->document_columns <= 0))
		{
			$this->document_columns = 1;
		}
		$this->document_bootstrap_columns = JUDownloadFrontHelper::getBootstrapColumns($this->document_columns);
		$this->document_row_class         = htmlspecialchars($this->params->get('document_row_class', ''));
		$this->document_column_class      = htmlspecialchars($this->params->get('document_column_class', ''));

		
		$this->display_download_rule_msg     = $this->params->get('show_rule_messages', 'modal');
		$this->external_download_link_target = $this->params->get('external_download_link_target', '_blank');

		
		$this->load_bootstrap = $this->params->get('load_bootstrap', 2);
		if ($this->load_bootstrap)
		{
			$this->document_bootstrap_columns = JUDownloadFrontHelper::getBootstrapColumns($this->document_columns);
		}

		
		$this->allow_user_select_view_mode = $this->params->get('allow_user_select_view_mode', 1);

		if ($this->allow_user_select_view_mode && isset($_COOKIE['judl-view-mode']) && !empty($_COOKIE['judl-view-mode']))
		{
			$viewMode = $_COOKIE['judl-view-mode'] == 'judl-view-grid' ? 2 : 1;
		}
		else
		{
			$viewMode = $this->params->get('default_view_mode', 2);
		}

		$this->view_mode = $viewMode;
	}

	protected function _prepareDocument()
	{
		$uri = clone JUri::getInstance();
		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$linkCanonical = $domain . JRoute::_(JUDownloadHelperRoute::getTagRoute($this->tag->id, true, false, $this->_layout), false);
		JUDownloadFrontHelper::setCanonical($linkCanonical);

		
		$this->item = $this->tag;
		JUDownloadFrontHelperSeo::seo($this);
	}

	protected function _setBreadcrumb()
	{
		$app          = JFactory::getApplication();
		$pathway      = $app->getPathway();
		$pathwayArray = array();

		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();

		$linkTags       = JRoute::_(JUDownloadHelperRoute::getTagsRoute());
		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem('tags', $linkTags);

		$linkTag        = JRoute::_(JUDownloadHelperRoute::getTagRoute($this->tag->id));
		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem($this->tag->title, $linkTag);

		$pathway->setPathway($pathwayArray);
	}
}
