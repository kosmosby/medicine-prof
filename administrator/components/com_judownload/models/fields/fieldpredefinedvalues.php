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

class JFormFieldFieldPredefinedValues extends JFormField
{
	
	protected $type = 'FieldPredefinedValues';

	
	protected function getInput()
	{
		if ($this->form->getValue("id") && $this->form->getValue("plugin_id"))
		{
			$fieldObj = JUDownloadFrontHelperField::getField($this->form->getValue("id"));
			$html     = $fieldObj->getPredefinedValuesHtml();
		}
		else
		{
			$html = '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NO_VALUE') . '</span>';
		}

		return $html;
	}
}
