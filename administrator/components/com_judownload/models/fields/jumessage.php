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

jimport('joomla.form.formfield');

class JFormFieldJUMessage extends JFormField
{

	public $type = 'JUMessage';

	
	protected function getLabel()
	{
		return '';
	}

	
	protected function getInput()
	{
		$html        = '';
		$description = trim($this->element['description']);
		$onclick     = $this->element['onclick'] ? ' onclick="' . str_replace('"', '\"', $this->element['onclick']) . '"' : '';
		$onmouseover = $this->element['onmouseover'] ? ' onmouseover="' . str_replace('"', '\"', $this->element['onmouseover']) . '"' : '';
		$onmouseout  = $this->element['onmouseout'] ? ' onmouseout="' . str_replace('"', '\"', $this->element['onmouseout']) . '"' : '';

		$html .= '<div class="jumessage ' . $this->element['class'] . '" id="' . $this->id . '"' . $onclick . $onmouseover . $onmouseout . '>';
		$html .= JText::_($description);
		$html .= '</div>';

		return $html;
	}

}

?>