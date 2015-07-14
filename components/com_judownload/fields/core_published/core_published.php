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

class JUDownloadFieldCore_published extends JUDownloadFieldRadio
{
	protected $field_name = 'published';

	public function onSave($data)
	{
		return $data;
	}

	public function getDefaultPredefinedValues()
	{
		return $this->getPredefinedValues();
	}

	public function getPredefinedValuesHtml()
	{
		$options    = array();
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->addAttribute("class", "radio btn-group", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$html = "";
		$html .= "<fieldset id=\"" . $this->getId() . "\" " . $this->getAttribute(null, null, "input") . ">";

		if ($options)
		{
			$default_predefined = $this->getDefaultPredefinedValues();

			foreach ($options AS $key => $option)
			{
				if ($option->text == strtoupper($option->text))
				{
					$text = JText::_($option->text);
				}
				else
				{
					$text = $option->text;
				}
				$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

				$value   = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
				$checked = $option->value == $default_predefined ? 'checked="checked"' : '';
				$html .= "<input id=\"" . $this->getId() . $key . "\" type=\"radio\" name=\"jform[predefined_values]\" value=\"$value\" $checked /> <label for=\"" . $this->getId() . $key . "\">$text</label>";
			}
		}
		$html .= "</fieldset>";

		return $html;
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		$options    = array();
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->setAttribute("type", "radio", "input");
		$this->addAttribute("class", $this->getInputClass(), "input");

		$this->setVariable('value', $value);
		$this->setVariable('options', $options);

		return $this->fetch('input.php', __CLASS__);
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options    = array();
		$obj        = new stdClass();
		$obj->value = "";
		$obj->text  = JText::_("COM_JUDOWNLOAD_SELECT_OPTION");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->setVariable('value', $defaultValue);
		$this->setVariable('options', $options);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$where[] = $this->fieldvalue_column . " = " . (int) $search;
		}
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('value', $this->value);

		return $this->fetch('output.php', __CLASS__);
	}

	
	public function canSubmit($userID = null)
	{
		$app = JFactory::getApplication();

		if ($app->isSite())
		{
			return false;
		}
		else
		{

			
			if ($this->doc_id && is_object($this->doc) && $this->doc->approved <= 0)
			{
				return false;
			}

			
			return parent::canSubmit();
		}
	}

	
	public function canEdit($userID = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			return false;
		}
		else
		{
			
			if ($this->doc_id && is_object($this->doc) && $this->doc->approved <= 0)
			{
				return false;
			}

			return parent::canEdit($userID);
		}
	}

	public function storeValue($value, $type = 'default', $inputData = null)
	{
		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return false;
		}
		else
		{

			$approveOption      = $app->input->post->get("approval_option");
			$approveOptionArray = array("ignore", "approve", "delete");
			if (in_array($approveOption, $approveOptionArray))
			{
				return true;
			}

			return parent::storeValue($value, $type, $inputData);
		}
	}

}

?>