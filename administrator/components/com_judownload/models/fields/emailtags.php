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

JFormHelper::loadFieldClass('list');

class JFormFieldEmailTags extends JFormField
{
	public $type = 'emailTags';

	public function getLabel()
	{
		return '';
	}

	public function getInput()
	{
		$document = JFactory::getDocument();
		$script   = 'jQuery(document).ready(function($){
			$("#jform_event").change(function(){
				var event = $(this).val();
				$.ajax({
					  url: "index.php?option=com_judownload&task=email.getEmailTags&tmpl=component",
					  data : {"event" : event}
					}).done(function(data) {
					  $( "#email-tags" ).children("ul").html(data);
				});
			});
			$("#jform_event").trigger("change");
		});';
		$document->addScriptDeclaration($script);

		$html = '<div id="email-tags">';
		$html .= '<ul class="nav">';
		$html .= "</ul>";
		$html .= '</div>';

		return $html;
	}
}

?>