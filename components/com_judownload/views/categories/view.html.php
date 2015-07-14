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

class JUDownloadViewCategories extends JUDLView
{

	public function display($tpl = null)
	{
		$app        = JFactory::getApplication();
		$categoryId = $app->input->getInt('id', 1);

		
		$this->category = JUDownloadFrontHelperCategory::getCategory($categoryId);

		
		$error = array();
		if (!JUDownloadFrontHelperPermission::canDoCategory($this->category->id, true, $error))
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

		
		$model        = $this->getModel();
		$this->model  = $model;
		$this->state  = $this->get('State');
		$this->params = $this->state->params;

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		
		$this->parent_id = $this->category->id;

		
		$this->subcategory_level = (int) $this->params->get('all_categories_subcategory_level', -1);

		
		$this->all_categories = $model->getCategoriesRecursive($this->category->id, $this->subcategory_level);

		
		$firstCategory                = $this->all_categories[0];
		$this->category->total_childs = $firstCategory->total_childs;

		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		$this->_setBreadcrumb();

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$uri = clone JUri::getInstance();
		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$canonicalLink = $domain . JRoute::_(JUDownloadHelperRoute::getCategoriesRoute($this->category->id, true, $this->_layout), false);

		JUDownloadFrontHelper::setCanonical($canonicalLink);

		$seoData = array(
			"metatitle"       => JText::_('COM_JUDOWNLOAD_SEO_TITLE_CATEGORIES'),
			"metadescription" => "",
			"metakeyword"     => ""
		);
		JUDownloadFrontHelperSeo::seo($this, $seoData);
	}

	protected function _setBreadcrumb()
	{
		$app          = JFactory::getApplication();
		$pathway      = $app->getPathway();
		$pathwayArray = array();

		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();

		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem($this->getName());

		$pathway->setPathway($pathwayArray);
	}
} 
