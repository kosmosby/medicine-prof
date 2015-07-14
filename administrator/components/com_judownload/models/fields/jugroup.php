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

class JFormFieldJUGroup extends JFormField
{

	public $type = 'JUGroup';

	
	protected function getLabel()
	{
		return '';
	}

	
	protected function getInput()
	{
		$html        = '';
		$groupfields = trim($this->element['groupfields']);
		$html .= '<h4 id="' . $this->id . '" class="jugroup ' . $this->element['class'] . '" data-groupfields="' . trim($groupfields) . '">';
		$html .= '<span>' . JText::_($this->element['title']) . '</span>';
		$html .= '<a title="' . JText::_('CLICK_HERE_TO_EXPAND_OR_COLLAPSE') . '" class="toggle-btn">' . JText::_('Open') . '</a>';
		$html .= '</h4>';

		return $html;
	}

}

?>