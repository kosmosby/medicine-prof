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

class JUDownloadViewTree extends JUDLView
{
	public function display($tpl = null)
	{
		
		$model               = $this->getModel();
		$this->model         = $model;
		$this->state         = $this->get('State');
		$params              = $this->state->params;
		$this->params        = $params;
		$this->token         = JSession::getFormToken();
		$this->root_category = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryId          = $this->state->get('category.id', $this->root_category->id);

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		
		$error = array();
		if (!JUDownloadFrontHelperPermission::canDoCategory($categoryId, true, $error))
		{
			$user = JFactory::getUser();
			if ($user->id)
			{
				return JError::raiseError($error['code'], $error['message']);
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

		
		$topLevelCats = JUDownloadHelper::getCatsByLevel(1, $categoryId);
		if (is_array($topLevelCats) && count($topLevelCats) > 0)
		{
			$this->tl_catid = $topLevelCats[0]->id;
		}

		
		$this->category = JUDownloadFrontHelperCategory::getCategory($categoryId);

		
		$this->show_feed = JUDLPROVERSION ? $this->params->get('rss_display_icon', 1) : 0;
		$this->rss_link  = JRoute::_(JUDownloadHelperRoute::getCategoryRoute($this->category->id, null, true));

		
		if (isset($this->category->images) && !empty($this->category->images) && !empty($this->category->images->detail_image))
		{
			$this->category->images->detail_image_src = JUri::root(true) . '/' . JUDownloadFrontHelper::getDirectory('category_detail_image_directory', 'media/com_judownload/images/category/detail/', true) . $this->category->images->detail_image;
		}
		$this->category->images->detail_image_width  = (int) $this->params->get('category_image_width', 200);
		$this->category->images->detail_image_height = (int) $this->params->get('category_image_height', 200);

		
		if ($this->params->get('category_show_description', 1))
		{
			$this->category->description = $this->category->introtext . $this->category->fulltext;
		}
		else
		{
			$this->category->description = $this->category->fulltext;
		}

		
		$categoryDescLimit = (int) $this->params->get('category_desc_limit', 0);
		if ($categoryDescLimit > 0)
		{
			$this->category->description = JUDownloadFrontHelperString::truncateHtml($this->category->description, $categoryDescLimit);
		}

		
		if ($this->params->get('plugin_support', 0))
		{
			$this->category->description = JHtml::_('content.prepare', $this->category->description, '', 'com_judownload.category');
		}

		
		$this->category->class_sfx = htmlspecialchars($this->category->class_sfx);

		
		$relatedCatOrdering  = $this->params->get('related_category_ordering', 'crel.ordering');
		$relatedCatDirection = $this->params->get('related_category_direction', 'ASC');
		$this->related_cats  = $model->getRelatedCategories($this->category->id, $relatedCatOrdering, $relatedCatDirection);

		if (is_array($this->related_cats) && count($this->related_cats) > 0)
		{
			foreach ($this->related_cats AS $relatedCategory)
			{
				if (isset($relatedCategory->images->intro_image) && !empty($relatedCategory->images->intro_image))
				{
					$relatedCategory->images->intro_image_src = JUri::root(true) . '/' . JUDownloadFrontHelper::getDirectory('category_intro_image_directory', 'media/com_judownload/images/category/intro/', true) . $relatedCategory->images->intro_image;
				}
				$relatedCategory->images->intro_image_width  = (int) $this->params->get('related_category_intro_image_width', 200);
				$relatedCategory->images->intro_image_height = (int) $this->params->get('related_category_intro_image_height', 200);

				if ($this->params->get('related_category_show_introtext', 1))
				{
					$relatedCategoryDescLimit = (int) $this->params->get('related_category_introtext_character_limit', 500);
					if ($relatedCategoryDescLimit > 0)
					{
						$relatedCategory->introtext = JUDownloadFrontHelperString::truncateHtml($relatedCategory->introtext, $relatedCategoryDescLimit);
					}

					if ($params->get('plugin_support', 0))
					{
						$relatedCategory->introtext = JHtml::_('content.prepare', $relatedCategory->introtext, '', 'com_judownload.category');
					}
				}
				else
				{
					$relatedCategory->introtext = '';
				}
			}
		}

		
		$subCatOrdering      = $this->params->get('subcategory_ordering', 'title');
		$subCatDirection     = $this->params->get('subcategory_direction', 'ASC');
		$this->subcategories = $model->getSubCategories($this->category->id, $subCatOrdering, $subCatDirection);

		if (is_array($this->subcategories) && count($this->subcategories) > 0)
		{
			foreach ($this->subcategories AS $subCategory)
			{
				if (isset($subCategory->images->intro_image) && !empty($subCategory->images->intro_image))
				{
					$subCategory->images->intro_image_src = JUri::root(true) . '/' . JUDownloadFrontHelper::getDirectory('category_intro_image_directory', 'media/com_judownload/images/category/intro/', true) . $subCategory->images->intro_image;
				}
				$subCategory->images->intro_image_width  = (int) $this->params->get('subcategory_intro_image_width', 200);
				$subCategory->images->intro_image_height = (int) $this->params->get('subcategory_intro_image_height', 200);

				if ($this->params->get('subcategory_show_introtext', 1))
				{
					$subCategoryDescLimit = (int) $this->params->get('subcategory_introtext_character_limit', 500);
					if ($subCategoryDescLimit > 0)
					{
						$subCategory->introtext = JUDownloadFrontHelperString::truncateHtml($subCategory->introtext, $subCategoryDescLimit);
					}
					if ($this->params->get('plugin_support', 0))
					{
						$subCategory->introtext = JHtml::_('content.prepare', $subCategory->introtext, '', 'com_judownload.category');
					}
				}
				else
				{
					$subCategory->introtext = '';
				}
			}
		}

		$this->category->can_submit_doc = JUDownloadFrontHelperPermission::canSubmitDocument($this->category->id);
		if ($this->category->can_submit_doc && $this->params->get('show_submit_document_btn_in_category', 1))
		{
			$this->category->submit_doc_link = JUDownloadFrontHelperDocument::getAddDocumentLink($this->category->id);
		}

		
		$this->items = array();

		
		if ($this->category->category->show_item)
		{
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

				$results                        = $dispatcher->trigger('onContentAfterTitle', array($context, &$item, &$item->params, 0));
				$item->event->afterDisplayTitle = trim(implode("\n", $results));

				$results                           = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$item, &$item->params, 0));
				$item->event->beforeDisplayContent = trim(implode("\n", $results));

				$results                          = $dispatcher->trigger('onContentAfterDisplay', array($context, &$item, &$item->params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
			}

			$this->pagination = $this->get('Pagination');
		}

		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		
		$this->_prepareData();

		$this->_prepareDocument();

		$this->_setBreadcrumb();

		parent::display($tpl);
	}

	protected function _prepareData()
	{
		
		$this->related_category_columns = (int) $this->params->get('vcategory_related_category_columns', 2);
		if (!is_numeric($this->related_category_columns) || ($this->related_category_columns <= 0))
		{
			$this->related_category_columns = 1;
		}
		$this->related_category_row_class         = htmlspecialchars($this->params->get('vcategory_related_category_row_class', ''));
		$this->related_category_column_class      = htmlspecialchars($this->params->get('vcategory_related_category_column_class', ''));
		$this->related_category_bootstrap_columns = JUDownloadFrontHelper::getBootstrapColumns($this->related_category_columns);

		
		$this->subcategory_columns = (int) $this->params->get('vcategory_subcategory_columns', 2);
		if (!is_numeric($this->subcategory_columns) || ($this->subcategory_columns <= 0))
		{
			$this->subcategory_columns = 1;
		}
		$this->subcategory_row_class         = htmlspecialchars($this->params->get('vcategory_subcategory_row_class', ''));
		$this->subcategory_column_class      = htmlspecialchars($this->params->get('vcategory_subcategory_column_class', ''));
		$this->subcategory_bootstrap_columns = JUDownloadFrontHelper::getBootstrapColumns($this->subcategory_columns);

		
		$this->order_name_array = JUDownloadFrontHelperField::getFrontEndOrdering($this->category->id);
		$this->order_dir_array  = JUDownloadFrontHelperField::getFrontEndDirection();
		$this->document_order   = $this->escape($this->state->get('list.ordering'));
		$this->document_dir     = $this->escape($this->state->get('list.direction'));

		
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
		$canonicalLink = $domain . JRoute::_(JUDownloadHelperRoute::getCategoryRoute($this->category->id, null, false, $this->_layout), false);
		JUDownloadFrontHelper::setCanonical($canonicalLink);

		$this->item = $this->category;
		JUDownloadFrontHelperSeo::seo($this);
	}

	protected function _setBreadcrumb()
	{
		$app          = JFactory::getApplication();
		$pathway      = $app->getPathway();
		$pathwayArray = array();
		if ($this->category->id)
		{
			$pathwayArray = JUDownloadFrontHelperBreadcrumb::getBreadcrumbCategory($this->category->id);
		}
		else
		{
			$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();
		}

		$pathway->setPathway($pathwayArray);
	}

} 
