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


class JFormFieldModal_License extends JFormField
{
	
	protected $type = 'Modal_License';

	
	protected function getInput()
	{
		
		JHtml::_('behavior.modal', 'a.modal');

		
		$script   = array();
		$script[] = '	function jSelectLicense_' . $this->id . '(id, title, object) {';
		$script[] = '		document.id("' . $this->id . '_id").value = id;';
		$script[] = '		document.id("' . $this->id . '_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		
		$html = array();
		$link = 'index.php?option=com_judownload&amp;view=licenses&amp;layout=modal&amp;tmpl=component&amp;function=jSelectLicense_' . $this->id;

		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT title' .
			' FROM #__judownload_licenses' .
			' WHERE id = ' . (int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
		}

		if (empty($title))
		{
			$title = JText::_('COM_JUDOWNLOAD_SELECT_LICENSE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		
		$joomla_version_arr = explode(".", JVERSION);
		$priVersion         = $joomla_version_arr[0];

		if ($priVersion == 3)
		{
			
			$html[] = '<span class="input-append">';
			$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
			$html[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_JUDOWNLOAD_CHANGE_LICENSE') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_JUDOWNLOAD_CHANGE_LICENSE') . '</a>';

		}
		else
		{
			
			$html[] = '<div class="fltlft">';
			$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
			$html[] = '</div>';

			
			$html[] = '<div class="button2-left">';
			$html[] = '  <div class="blank">';
			$html[] = '	<a class="modal" title="' . JText::_('COM_JUDOWNLOAD_CHANGE_LICENSE') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('COM_JUDOWNLOAD_CHANGE_LICENSE') . '</a>';
			$html[] = '  </div>';
			$html[] = '</div>';
		}

		
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
