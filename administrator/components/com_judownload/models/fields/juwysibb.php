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

jimport('joomla.html.editor');

JFormHelper::loadFieldClass('editor');


class JFormFieldJUWysibb extends JFormField
{

	
	public $type = 'juwysibb';

	protected function getInput()
	{
		$document = JFactory::getDocument();
		$script   = '
			window.addEvent("domready", function () {
				document.formvalidator.setHandler("comment",
					function (value) {
						if(value.length <= 1){
							return false;
						}

						return true;
					});
			});
		';

		$document->addScriptDeclaration($script);

		$html = "<div class=\"clr\"></div><textarea id=\"" . $this->id . "\" name=\"" . $this->name . "\" class = \"required validate-comment comment-editor\">" . $this->value . "</textarea>";
		JUDownloadFrontHelperEditor::getWysibbEditor('.comment-editor');

		return $html;
	}
}