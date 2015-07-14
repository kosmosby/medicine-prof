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

class JFormFieldModal_Comment extends JFormField
{

	protected $type = 'Modal_Comment';

	protected function getInput()
	{
		$app = JFactory::getApplication();

		JHtml::_('behavior.modal', 'a.modal');

		$id = $this->form->getValue('id', null, 0);
		
		$script   = array();
		$script[] = 'function commentURL() {
				     	var commenturl = document.id("commenturl").getAttribute("href");
						commenturl = commenturl + "&doc_id="+document.id(jform_doc_id).value;
						document.id("commenturl").setAttribute("href", commenturl);
						return true;
			  		}';
		$script[] = 'function jSelectComment_' . $this->id . '(id, title, level) {
			        	if(id != document.id("' . $this->id . '").value){
							document.id("' . $this->id . '").value = id;
		        			document.id("' . $this->id . '_name").value = title;
		        			level = parseInt(level) + 1
		        			document.id("jform_level").value = level;
				        	
		        		}
				        SqueezeBox.close();
					}';

		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		
		$html = array();

		$link  = 'index.php?option=com_judownload&amp;view=comments&amp;layout=modal&amp;tmpl=component&amp;id=' . $id . '&amp;function=jSelectComment_' . $this->id;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title');
		$query->from('#__judownload_comments');
		$query->where('id = ' . (int) $this->value);
		$db->setQuery($query);
		$title = $db->loadResult();

		if (empty($title))
		{
			$rootComment = JUDownloadFrontHelperComment::getRootComment();
			$title       = $rootComment->title;
			$value       = $rootComment->id;
		}
		else
		{
			$value = $this->value;
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		
		$joomla_version_arr = explode(".", JVERSION);
		$priVersion         = $joomla_version_arr[0];

		if ($priVersion == 3)
		{
			
			$html[] = '<span class="' . ($app->isAdmin() ? 'input-append' : '') . '">';
			$html[] = '<input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="' . $this->element['size'] . '" />';
			if ($app->isAdmin())
			{
				$html[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_JUDOWNLOAD_SELECT_COMMENT') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-list"></i> ' . JText::_('COM_JUDOWNLOAD_SELECT') . '</a>';
			}
			$html[] = '</span>';
		}
		else
		{
			
			$html[] = '<div class="fltlft">';
			$html[] = '<input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="' . $this->element['size'] . '" />';
			$html[] = '</div>';

			if ($app->isAdmin())
			{
				
				$html[] = '<div class="button2-left">';
				$html[] = '<div class="blank">';
				$html[] = '<a onclick="commentURL();" id="commenturl" class="modal" title="' . JText::_('COM_JUDOWNLOAD_SELECT_COMMENT') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('COM_JUDOWNLOAD_SELECT_COMMENT') . '</a>';
				$html[] = '</div>';
				$html[] = '</div>';
			}
		}

		
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}