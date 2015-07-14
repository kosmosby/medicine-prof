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

class JUDownloadViewLicense extends JUDLView
{
	public function display($tpl = null)
	{
		$app        = JFactory::getApplication();
		$licenseId  = $app->input->getInt('id', 0);
		$this->item = JUDownloadFrontHelper::getLicense($licenseId);
		if (!is_object($this->item))
		{
			return JError::raiseError(403, JText::_('COM_JUDOWNLOAD_LICENSE_NOT_FOUND'));
		}

		$this->params = JUDownloadHelper::getParams();

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$limitStart = $app->input->getUint('limitstart', 0);

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$this->item->event = new stdClass();
		$context           = 'com_judownload.license';

		$results                              = $dispatcher->trigger('onContentAfterTitle', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results                                 = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results                                = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this->item, &$this->item->params, $limitStart));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->_prepareDocument();

		$this->_setBreadcrumb();

		
		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$uri = clone JUri::getInstance();
		
		$domain        = $uri->toString(array('scheme', 'host', 'port'));
		$canonicalLink = $domain . JRoute::_(JUDownloadHelperRoute::getLicenseRoute($this->item->id, true, $this->_layout), false);
		JUDownloadFrontHelper::setCanonical($canonicalLink);

		
		JUDownloadFrontHelperSeo::seo($this);
	}

	protected function _setBreadcrumb()
	{
		$app          = JFactory::getApplication();
		$pathway      = $app->getPathway();
		$pathwayArray = array();

		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();

		$linkLicense    = JRoute::_(JUDownloadHelperRoute::getLicenseRoute($this->item->id));
		$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::createPathwayItem($this->item->title, $linkLicense);

		$pathway->setPathway($pathwayArray);
	}
}