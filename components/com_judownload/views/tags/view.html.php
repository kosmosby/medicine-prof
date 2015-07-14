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

JHtml::addIncludePath(JPATH_SITE . '/components/com_judownload/helpers/html');

class JUDownloadViewTags extends JUDLView
{

	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->state      = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->params     = JUDownloadHelper::getParams();

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->listOrder = $this->escape($this->state->get('list.ordering', 'tag.title'));
		$this->listDirn  = $this->escape($this->state->get('list.direction', 'DESC'));

		$this->order_name_array = array(
			'tag.id'       => JText::_('COM_JUDOWNLOAD_FIELD_ID'),
			'tag.title'    => JText::_('COM_JUDOWNLOAD_FIELD_TITLE'),
			'tag.created'  => JText::_('COM_JUDOWNLOAD_FIELD_CREATED'),
			'tag.ordering' => JText::_('COM_JUDOWNLOAD_FIELD_ORDERING')
		);

		$this->order_dir_array = array('ASC'  => JText::_('COM_JUDOWNLOAD_ASC'),
		                               'DESC' => JText::_('COM_JUDOWNLOAD_DESC'));

		$this->_prepareDocument();
		$this->_setBreadcrumb();

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$uri = clone JUri::getInstance();
		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$linkCanonical = $domain . JRoute::_(JUDownloadHelperRoute::getTagsRoute(true, $this->_layout), false);
		JUDownloadFrontHelper::setCanonical($linkCanonical);

		$seoData = array(
			"metatitle"       => JText::_('COM_JUDOWNLOAD_SEO_TITLE_TAGS'),
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

		$linkTags       = JRoute::_(JUDownloadHelperRoute::getTagsRoute());
		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem($this->getName(), $linkTags);

		$pathway->setPathway($pathwayArray);
	}
}
