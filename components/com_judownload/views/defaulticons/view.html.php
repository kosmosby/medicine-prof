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


class JUDownloadViewDefaultIcons extends JViewLegacy
{
	
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$app = JFactory::getApplication();

		$this->type = $app->input->get('type', '');

		if (!$this->type || $this->type == 'icon')
		{
			$this->icon_url       = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("document_image_directory", "media/com_judownload/images/document/", true) . "default/";
			$this->icon_directory = JPath::clean(JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_image_directory", "media/com_judownload/images/document/") . "default/");
		}
		elseif ($this->type == 'marker')
		{
			$this->icon_url       = JUri::root(true) . "/media/com_judownload/markers/";
			$this->icon_directory = JPath::clean(JPATH_ROOT . "/media/com_judownload/images/markers/");
		}

		$this->folders = $this->getFolders();
		$this->folder  = $app->input->get('folder', '', 'string');
		
		if (!in_array($this->folder, $this->folders))
		{
			$this->folder = '';
		}

		$this->icons = $this->getIcons();

		

		$this->setDocument();

		
		parent::display($tpl);
	}

	public function getIcons()
	{
		$current_directory = JPath::clean($this->icon_directory . $this->folder . "/");
		$filenames         = array();
		foreach (glob($current_directory . "{*.png,*.gif,*.jpg,*.bmp}", GLOB_BRACE) as $filename)
		{
			$filenames[] = str_replace(array($this->icon_directory, "\\"), array("", "/"), JPath::clean($filename));
		}

		return $filenames;
	}

	public function getFolders()
	{
		$folders = array(array('value' => '', 'text' => '/'));
		foreach (glob($this->icon_directory . '*', GLOB_ONLYDIR) as $filename)
		{
			$folder    = str_replace($this->icon_directory, "", JPath::clean($filename));
			$folders[] = array('value' => $folder, 'text' => "/" . $folder);
		}

		return $folders;
	}

	
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root(true) . '/components/com_judownload/assets/css/defaulticons.css');
	}

}
