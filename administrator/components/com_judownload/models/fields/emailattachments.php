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

class JFormFieldEmailAttachments extends JFormField
{
	protected $type = 'emailAttachments';

	protected function getInput()
	{
		$document    = JFactory::getDocument();
		$app         = JFactory::getApplication();
		$id          = $app->input->getInt('id', 0);
		$attachments = array();

		if ($this->value)
		{
			$registry = new JRegistry;
			$registry->loadString($this->value);
			$attachments = $registry->toObject();
		}

		$token = JSession::getFormToken();

		$script = "jQuery(document).ready(function($){
							
							$('#add_attachments').click(function() {
								$('<tr><td><input type=\"file\" name=\"attachmentfiles[]\" multiple /></td><td><a href=\"#\" class=\"remove_attachment\" onclick=\"return false;\">" . JText::_('COM_JUDOWNLOAD_REMOVE') . "</a></td></tr>').appendTo(\"#juemail table\");
							});
							
							$('#juemail').on('click', '.remove_attachment', function() {
								$(this).parent().parent().remove();
							});
							$(\"#email-lists\").dragsort({ dragSelector: \"li\", dragEnd: saveOrder, placeHolderTemplate: \"<li class='placeHolder'></li>\", dragSelectorExclude: \"input, textarea, span\"});
					        function saveOrder() {
								var data = $(\"#juemail li\").map(function() { return $(this).data(\"itemid\"); }).get();
					        };
						});";
		$document->addScriptDeclaration($script);

		$html = '<div id="juemail" class="juemail" style="float: left">';
		if ($attachments)
		{
			$html .= '<ul id="email-lists" class="email-lists">';
			foreach ($attachments AS $attachment)
			{
				$html .= '<li>';
				$html .= '<a class="drag-icon"></a>';
				$html .= '<input type="checkbox" name="' . $this->name . '[]" checked value="' . $attachment . '" />';
				$html .= '<a href="index.php?option=com_judownload&task=email.downloadattachment&id=' . $id . '&file=' . $attachment . '&' . $token . '=1"><span class="attachment">' . $attachment . '</span></a>';
				$html .= '</li>';
			}
			$html .= '</ul>';
		}

		$html .= '<table></table>';
		$html .= '<a href="#" class="btn btn-mini btn-primary add_attachments" id="add_attachments" onclick="return false;"><i class="icon-new"></i> ' . JText::_('COM_JUDOWNLOAD_ADD_ATTACHMENT') . '</a>';
		$html .= '</div>';

		return $html;
	}

	protected function getLabel()
	{
		return "";
	}
}

?>