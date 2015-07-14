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

class JUDLTemplateDefaultHelper
{
	protected $template;

	public function __construct($self_template)
	{
		$this->self_template = $self_template;
	}

	public function loadTooltipster()
	{
		$document = JFactory::getDocument();

		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/tooltipster/css/tooltipster.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_judownload/assets/tooltipster/themes/tooltipster-shadow.css");

		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/tooltipster/js/jquery.tooltipster.min.js");
		$document->addScript(JUri::root(true) . "/components/com_judownload/assets/tooltipster/js/tooltipster.config.js");
	}
}
?>