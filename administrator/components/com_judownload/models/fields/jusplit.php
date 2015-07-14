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

class JFormFieldJUSplit extends JFormField
{

	public $type = 'JUSplit';

	
	protected function getLabel()
	{
		return '';
	}

	
	protected function getInput()
	{
		$html = '<hr id="' . $this->id . '" class="jusplit ' . $this->element['class'] . '" />';

		return $html;
	}
}

?>