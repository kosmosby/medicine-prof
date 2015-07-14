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

class JFormFieldFieldPreview extends JFormField
{

	
	protected $type = 'fieldPreview';

	
	protected function getInput()
	{
		$html    = '<div id="field-preview">';
		$type_id = $this->form->getValue("plugin_id");
		$id      = $this->form->getValue("id");
		if ($id && $type_id)
		{
			$field = $this->getField($id, $type_id);
			if ($field)
			{
				$field->required = false;
				$html .= $field->getModPrefixText();
				$html .= $field->getInput();
				$html .= $field->getModSuffixText();
			}
		}
		$html .= '</div>';

		return $html;
	}

	protected function getLabel()
	{
		$html    = "";
		$type_id = $this->form->getValue("plugin_id");
		$id      = $this->form->getValue("id");
		if ($id && $type_id)
		{
			$field = $this->getField($id, $type_id);
			if ($field)
			{
				$html .= $field->getLabel();
			}
		}

		return $html;
	}

	protected function getField($id, $type_id)
	{
		$db    = JFactory::getDbo();
		$query = "SELECT folder FROM #__judownload_plugins WHERE id = " . $type_id;
		$db->setQuery($query);
		$folder = $db->loadResult();
		$class  = "JUDownloadField" . ucfirst($folder);
		if (class_exists($class))
		{
			$obj = new $class($id, 0);

			return $obj;
		}
		else
		{
			return false;
		}
	}
}
