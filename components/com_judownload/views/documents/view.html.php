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


class JUDownloadViewDocuments extends JUDLView
{
	
	public function display($tpl = null)
	{
		$this->user = JFactory::getUser();

		
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->authors    = $this->get('Authors');
		$this->params     = JUDownloadHelper::getParams();

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$levelOptions       = array();
		$levelOptions[]     = JHtml::_('select.option', 5, 5);
		$levelOptions[]     = JHtml::_('select.option', 10, 10);
		$levelOptions[]     = JHtml::_('select.option', 15, 15);
		$levelOptions[]     = JHtml::_('select.option', 20, 20);
		$levelOptions[]     = JHtml::_('select.option', 25, 25);
		$levelOptions[]     = JHtml::_('select.option', 30, 30);
		$this->levelOptions = $levelOptions;

		$this->listOrder = $this->escape($this->state->get('list.ordering'));
		$this->listDirn  = $this->escape($this->state->get('list.direction'));

		$app            = JFactory::getApplication();
		$this->function = $app->input->get('function', 'jSelectDocument');
		
		$this->totalDocs = $this->get('Total');

		$this->_prepareDocument();

		
		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$uri = clone JUri::getInstance();
		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$canonicalLink = $domain . JRoute::_(JUDownloadHelperRoute::getDocumentsRoute(true), false);
		JUDownloadFrontHelper::setCanonical($canonicalLink);

		$seoData = array(
			"metatitle"       => JText::_('COM_JUDOWNLOAD_SEO_TITLE_DOCUMENTS'),
			"metadescription" => "",
			"metakeyword"     => ""
		);
		JUDownloadFrontHelperSeo::seo($this, $seoData);
	}
}
