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

class JFormFieldJUFieldset extends JFormField
{

	public $type = 'JUFieldset';

	
	protected function getLabel()
	{
		return '';
	}

	
	protected function getInput()
	{
		$html = '';
		$html .= '<h4 id="' . $this->id . '" class="jufieldset ' . $this->element['class'] . '" data-fields="' . trim($this->element['fields']) . '">';
		$html .= JText::_($this->element['title']);
		$html .= '</h4>';

		return $html;
	}
}

?>