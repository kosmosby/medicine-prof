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


class JButtonJUHelp extends JButton
{
	
	protected $_name = 'JUHelp';

	
	public function fetchButton($type = 'JUHelp', $name = '', $text = '', $task = '', $list = true)
	{
		$i18n_text = JText::_($text);
		$class     = $this->fetchIconClass($name);
		$doTask    = $this->_getCommand($text, $task, $list);

		if (!JUDownloadHelper::isJoomla3x())
		{
			$html = "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
			$html .= "<span class=\"$class\">\n";
			$html .= "</span>\n";
			$html .= "$i18n_text\n";
			$html .= "</a>\n";
		}
		else
		{
			$html = "<button onclick=\"$doTask\" class=\"btn btn-small\">\n";
			$html .= "<span class=\"$class\">\n";
			$html .= "</span>\n";
			$html .= "$i18n_text\n";
			$html .= "</button>\n";
		}

		return $html;
	}

	
	public function fetchId($type = 'JUHelp', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $name;
	}

	
	protected function _getCommand($name, $task, $list)
	{
		$document = JFactory::getDocument();

		$juri               = JUri::getInstance();
		$options            = new stdClass();
		$options->component = $juri->getVar('option', '');
		$options->view      = $juri->getVar('view', '');
		$options->layout    = $juri->getVar('layout', '');
		$options->jversion  = JVERSION;
		$version            = '';
		if ($options->component)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT manifest_cache FROM #__extensions WHERE name = '" . $options->component . "'";
			$db->setQuery($query);
			$mainfest_cache = $db->loadResult();
			if ($mainfest_cache)
			{
				$mainfest_cache = json_decode($mainfest_cache);
				$version        = isset($mainfest_cache->version) ? $mainfest_cache->version : '';
			}
		}
		$options->version = $version;
		$options->url     = JUri::getInstance()->toString();

		$script = '
		var juloadhelper = false;
		function displayHelp(){
			var iframeUrl = "' . JUri::base() . '?option=com_judownload&task=help.display&settings=' . base64_encode(serialize($options)) . '";
			if(!juloadhelper){
				var iframe = \'<iframe src="\'+iframeUrl+\'" style="border: 0px; height: 100%; width: 100%;" border="no" scrolling="auto"></iframe>\';
				jQuery("#iframe-help").html(iframe);
				juloadhelper = true;
			}
			jQuery("#iframe-help").slideToggle( "slow");
		}';
		$document->addScriptDeclaration($script);
		$cmd = "return displayHelp();";

		return $cmd;
	}
}

class JToolbarButtonJUHelp extends JButtonJUHelp
{

}
