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

class JUDownloadFieldBase
{
	
	protected $doc = null;
	
	protected $id = null;
	
	protected $field = null;
	
	
	protected $params = null;
	
	
	protected $output_attributes = null;
	protected $input_attributes = null;
	protected $search_attributes = null;
	protected $label_attributes = null;
	
	protected $regex = null;
	
	protected $filter = null;
	
	protected $errors = array();
	
	protected $fieldvalue_column = null;
	
	protected $fields_data = array();
	
	protected $is_new = null;
	
	protected $name = null;
	
	protected $id_suffix = null;
	
	protected static $cache = array();

	protected $valueCache = array();
	
	public $vars = array();

	
	public function __construct($field = null, $doc = null)
	{
		
		if (is_null($field))
		{
			$field = $this->field_name;
		}

		if (is_object($field))
		{
			
			JUDownloadFrontHelperField::getFieldById($field->id, $field);
		}
		else
		{
			
			$field = JUDownloadFrontHelperField::getFieldById($field);
		}

		if (!is_object($field))
		{
			
			return false;
		}

		$this->id = $field->id;

		$this->params = new JRegistry($field->params);

		
		if (is_null($this->fieldvalue_column))
		{
			
			if ($this->isCore())
			{
				$this->fieldvalue_column = "d." . $this->field_name;
			}
			else
			{
				$this->fieldvalue_column = "field_values_" . $this->id . ".value";
			}
		}

		
		if (!$this->isCore())
		{
			$this->loadLanguage($this->folder);
		}

		
		

		$this->loadDocument($doc);

		$this->name = "fields[" . $this->id . "]";

		if ($this->params->get('auto_suggest', 0))
		{
			$app = JFactory::getApplication();
			if (($app->isAdmin() && ($app->input->get('view', '') == 'document' || $app->input->get('view', '') == 'field')) || ($app->isSite() && $app->input->get('view', '') == 'form'))
			{
				$document = JFactory::getDocument();
				$document->addStyleSheet(JUri::root() . "components/com_judownload/assets/css/typeahead.css");

				JUDownloadFrontHelper::loadjQuery();
				$document->addScript(JUri::root() . "components/com_judownload/assets/js/handlebars.min.js");
				$document->addScript(JUri::root() . "components/com_judownload/assets/js/typeahead.bundle.min.js");
				$document->addScript(JUri::root() . "components/com_judownload/assets/js/typeahead.config.js");
				$script = "var JURI_ROOT = '" . JUri::root() . "';";
				$document->addScriptDeclaration($script);
			}
		}

		return true;
	}

	
	public function __get($property)
	{
		switch ($property)
		{
			case 'doc_id':
				if (isset($this->doc->id))
				{
					return $this->doc->id;
				}
				else
				{
					return null;
				}
				break;
			case 'params':
			case 'filter':
			case 'output_attributes':
			case 'input_attributes':
			case 'search_attributes':
			case 'label_attributes':
			case 'fields_data':
			case 'is_new':
			case 'doc':
			case 'name':
			case 'id_suffix':
				return $this->$property;
				break;
			case 'value':
				$storeId = md5("FieldValue::" . $this->doc_id);
				if (!isset($this->valueCache[$storeId]))
				{
					if ($this->doc_id)
					{
						$value       = $this->getValue();
						$this->value = $this->parseValue($value);
						unset($value);
					}
					
					else
					{
						$this->value = $this->getDefaultPredefinedValues();
					}

					$this->valueCache[$storeId] = $this->value;
				}

				$this->value = $this->valueCache[$storeId];

				return $this->value;

				break;
			default:
				
				if (isset($this->field->$property))
				{
					return $this->field->$property;
				}
				
				else
				{
					$field = JUDownloadFrontHelperField::getFieldById($this->id);
					if (isset($field->$property))
					{
						return $field->$property;
					}
					else
					{
						return null;
					}
				}
				break;
		}
	}

	
	public function __set($property, $value)
	{
		switch ($property)
		{
			case 'doc_id':
				$this->doc->id = (int) $value;
				break;
			case 'params':
			case 'filter':
			case 'output_attributes':
			case 'input_attributes':
			case 'search_attributes':
			case 'label_attributes':
			case 'value':
			case 'fields_data':
			case 'predefined':
			case 'is_new':
			case 'name':
			case 'id_suffix':
				$this->$property = $value;
				break;
			default:
				
				if (!is_object($this->field))
				{
					$this->field = new stdClass();
				}

				$this->field->$property = $value;
				break;
		}
	}

	
	public function __clone()
	{
		if (is_object($this->doc))
		{
			$this->doc = clone $this->doc;
		}

		if (is_object($this->field))
		{
			$this->field = clone $this->field;
		}

		if (is_object($this->params))
		{
			$this->params = clone $this->params;
		}
	}

	
	public function loadDocument($doc, $resetCache = false)
	{
		
		if (is_numeric($doc) && $doc > 0)
		{
			$doc = clone JUDownloadHelper::getDocumentById($doc, $resetCache);
		}

		if (is_object($doc) || is_null($doc))
		{
			$this->doc = $doc;
		}
	}

	protected function getValue()
	{
		$value = null;

		
		if (!$this->isCore())
		{
			
			$field_column = "field_values_" . $this->id;
			if (isset($this->doc->$field_column) && !is_null($this->doc->$field_column))
			{
				$fieldValue = $this->doc->$field_column;
				if ($this->params->get('is_numeric', 0))
				{
					$fieldValue = $this->numberFormat($fieldValue, $this->params->get("digits_in_total", 11), $this->params->get("digits_after_decimal", 2));
				}
			}
			else
			{
				$db = JFactory::getDbo();
				if ($this->params->get('is_numeric', 0))
				{
					$query = "SELECT CONVERT(value, DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("digits_after_decimal", 2) . ")) AS value FROM #__judownload_fields_values WHERE doc_id=" . (int) $this->doc_id . ' AND field_id=' . (int) $this->id;
				}
				else
				{
					$query = "SELECT value FROM #__judownload_fields_values WHERE doc_id=" . (int) $this->doc_id . ' AND field_id=' . (int) $this->id;
				}

				$db->setQuery($query);

				$fieldValue = $db->loadResult();
			}

			$value = $fieldValue;
		}
		else
		{
			$field_name = $this->field_name;
			if (isset($this->doc->$field_name))
			{
				$value = $this->doc->$field_name;
			}
		}

		return $value;
	}

	
	public function parseValue($value)
	{
		
		

		

		return $value;
	}

	
	public function getCounter()
	{
		if ($this->doc_id)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT counter FROM #__judownload_fields_values WHERE field_id = " . $this->id . " AND doc_id = " . $this->doc_id;
			$db->setQuery($query);

			return $db->loadResult();
		}
		else
		{
			return null;
		}
	}

	
	public function redirectUrl()
	{
		if ($this->params->get("link_counter", 0) && $this->doc_id)
		{
			$db    = JFactory::getDbo();
			$query = "UPDATE #__judownload_fields_values SET counter = counter + 1 WHERE field_id = " . $this->id . " AND doc_id = " . $this->doc_id;
			$db->setQuery($query);
			$db->execute();
		}

		$app = JFactory::getApplication();
		$url = $this->value;
		$app->redirect($url);
	}

	
	protected function parseAttributes($attributes = '')
	{
		if ($attributes)
		{
			$attr_str      = html_entity_decode($attributes, ENT_QUOTES, 'UTF-8');
			$regex_pattern = "#\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))#msi";
			preg_match_all($regex_pattern, $attr_str, $matches);

			$attribute_array = array();
			if (count($matches))
			{
				for ($i = 0; $i < count($matches[1]); $i++)
				{
					$key                   = $matches[1][$i];
					$val                   = $matches[3][$i] ? $matches[3][$i] : ($matches[4][$i] ? $matches[4][$i] : $matches[5][$i]);
					$attribute_array[$key] = $val;
				}
			}

			$attribute_registry = new JRegistry($attribute_array);

			return $attribute_registry;
		}

		return new JRegistry;
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		
		if (!$this->doc_id)
		{
			return false;
		}

		$db = JFactory::getDbo();

		
		$value = $this->onSaveDocument($value);

		$result = true;

		if ($this->isCore())
		{
			$query = "UPDATE #__judownload_documents SET " . $db->quoteName($this->field_name) . " = " . $db->quote($value) . " WHERE id = " . $this->doc_id;
			$db->setQuery($query);
			$result = $db->execute();
		}
		else
		{
			$query = "SELECT COUNT(*) FROM #__judownload_fields_values WHERE field_id = " . $this->id . " AND doc_id = " . $this->doc_id;
			$db->setQuery($query);
			$countData = $db->loadResult();
			
			if ($countData > 0)
			{
				
				if ($value !== "" && !is_null($value))
				{
					$query = "UPDATE #__judownload_fields_values SET value=" . $db->quote($value) . " WHERE field_id = " . $this->id . " AND doc_id = " . $this->doc_id;
					$db->setQuery($query);
					$result = $db->execute();
				}
				
				else
				{
					$query = "DELETE FROM #__judownload_fields_values WHERE field_id = " . $this->id . " AND doc_id = " . $this->doc_id;
					$db->setQuery($query);
					$result = $db->execute();
				}
			}
			
			else
			{
				if ($value !== "" && !is_null($value))
				{
					$query = "INSERT INTO #__judownload_fields_values (field_id, doc_id, value, counter) VALUES ($this->id, $this->doc_id, " . $db->quote($value) . ", 0)";
					$db->setQuery($query);
					$result = $db->execute();
				}
			}
		}

		return $result;
	}

	
	public function getPredefinedValues($predefined_values_type = 'auto')
	{
		
		$storeId = md5(__METHOD__ . "::" . $this->id . "::" . $predefined_values_type);
		if (!isset(self::$cache[$storeId]))
		{
			
			if ($predefined_values_type == 1)
			{
				$predefinedValues = $this->predefined_values;
			}
			
			elseif ($predefined_values_type == 2)
			{
				$predefinedValues = $this->getPredefinedFunction();
			}
			
			else
			{
				
				if ($this->predefined_values_type == 1)
				{
					$predefinedValues = $this->predefined_values;
				}
				else
				{
					$predefinedValues = $this->getPredefinedFunction();
				}
			}

			self::$cache[$storeId] = $this->parsePredefinedValues($predefinedValues);
		}

		return self::$cache[$storeId];
	}

	
	protected function parsePredefinedValues($predefinedValues)
	{
		if ($predefinedValues === "")
		{
			return "";
		}
		elseif (is_numeric($predefinedValues))
		{
			return $predefinedValues;
		}
		elseif (is_string($predefinedValues))
		{
			if (json_decode($predefinedValues))
			{
				return json_decode($predefinedValues);
			}
			elseif (strpos($predefinedValues, "|"))
			{
				return explode("|", $predefinedValues);
			}
			
			else
			{
				return $predefinedValues;
			}
		}
		
		else
		{
			return $predefinedValues;
		}
	}

	
	public function getPredefinedFunction()
	{
		$phpCode = $this->php_predefined_values;
		if (trim($phpCode))
		{
			return eval($phpCode);
		}

		return null;
	}

	
	public function getDefaultPredefinedValues()
	{
		$values = $this->getPredefinedValues();

		
		return $values;
	}

	
	public function getPredefinedValuesHtml()
	{
		$default_predefined = $this->getDefaultPredefinedValues();
		$html               = "<input type=\"text\" name=\"jform[predefined_values]\" value=\"" . @htmlspecialchars($default_predefined, ENT_COMPAT, 'UTF-8') . "\">";

		return $html;
	}

	
	public function getName()
	{
		return $this->name;
	}

	
	public function getId()
	{
		return 'field_' . $this->id . $this->id_suffix;
	}

	
	public function isCore()
	{
		if ($this->field_name != "")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function isRequired()
	{
		if ($this->required)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function hasCaption()
	{
		if (!$this->caption || $this->hide_caption)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	
	public function getCaption($forceShow = false)
	{
		if (!$this->isPublished())
		{
			return null;
		}

		if ($this->hide_caption && !$forceShow)
		{
			return null;
		}
		else
		{
			
			if ($this->caption == strtoupper($this->caption))
			{
				return JText::_($this->caption);
			}

			return (string) $this->caption;
		}
	}

	
	protected function initAttribute($type = 'output')
	{
		$attributesProperty = $type . '_attributes';

		switch ($type)
		{
			
			case 'output':
				$field                     = JUDownloadFrontHelperField::getFieldById($this->id);
				$this->$attributesProperty = $this->parseAttributes($field->attributes);
				break;

			
			case 'input':
				$this->$attributesProperty = $this->parseAttributes($this->params->get('input_attributes', ''));
				break;

			
			case 'search':
				$this->$attributesProperty = $this->parseAttributes($this->params->get('search_attributes', ''));
				break;

			default:
				$this->$attributesProperty = new JRegistry;
				break;
		}
	}

	
	public function setAttribute($name, $value, $type = 'output')
	{
		$name              = strtolower($name);
		$ignoredAttributes = array('id', 'name');
		if (in_array($name, $ignoredAttributes))
		{
			return false;
		}

		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		
		if (is_null($value))
		{
			$attributeArray = $this->$attributesProperty->toArray();
			unset($attributeArray[$name]);

			$this->$attributesProperty = new JRegistry($attributeArray);

			return true;
		}
		else
		{
			$value = trim($value);

			return $this->$attributesProperty->set($name, $value);
		}
	}

	
	public function addAttribute($name, $value, $type = 'output')
	{
		$name              = strtolower($name);
		$ignoredAttributes = array('id', 'name');
		if (in_array($name, $ignoredAttributes))
		{
			return false;
		}

		$value = trim($value);
		if (!$value)
		{
			return true;
		}

		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		$currentAttribute = trim($this->$attributesProperty->get($name, ""));

		if ($currentAttribute)
		{
			if ($name == 'style')
			{
				if (substr($value, -1) != ";")
				{
					$currentAttribute .= ";";
				}
			}

			$newAttribute = implode(" ", array($currentAttribute, $value));
		}
		else
		{
			$newAttribute = $value;
		}

		return $this->$attributesProperty->set($name, $newAttribute);
	}

	
	public function getAttribute($name = null, $default = null, $type = 'output', $returnType = 'string')
	{
		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		$ignoredAttributes = array('id', 'name');

		if ($name)
		{
			$name = strtolower($name);

			if (in_array($name, $ignoredAttributes))
			{
				return null;
			}

			return $this->$attributesProperty->get($name, $default);
		}
		else
		{
			if ($returnType == 'registry')
			{
				return $this->$attributesProperty;
			}
			elseif ($returnType == 'array')
			{
				return $this->$attributesProperty->toArray();
			}
			else
			{
				return $this->$attributesProperty->toString('ini');
			}
		}
	}

	
	public function getInputClass()
	{
		$class = array();

		if ($this->isRequired())
		{
			$class[] = 'required';
		}

		if ($this->getRegex())
		{
			$class[] = 'validate-' . $this->getId();
			$this->JSValidate();
		}

		if ($this->params->get('auto_suggest', 0))
		{
			$class[] = 'autosuggest';
		}

		if ($class)
		{
			return implode(' ', $class);
		}
		else
		{
			return "";
		}
	}

	
	public function getModPrefixText($wrap = true)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$prefix_text_mod = $this->prefix_text_mod;
		if (empty($prefix_text_mod))
		{
			return "";
		}
		else
		{
			if ($wrap)
			{
				return '<span class="prefix_mod">' . $prefix_text_mod . '</span>';
			}
			else
			{
				return $prefix_text_mod;
			}
		}
	}

	
	public function getModSuffixText($wrap = true)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$suffix_text_mod = $this->suffix_text_mod;
		if (empty($suffix_text_mod))
		{
			return false;
		}
		else
		{
			if ($wrap)
			{
				return '<span class="suffix_mod">' . $suffix_text_mod . '</span>';
			}
			else
			{
				return $suffix_text_mod;
			}
		}
	}

	
	public function getDisplayPrefixText($wrap = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$prefix_text_display = $this->prefix_text_display;
		if (empty($prefix_text_display))
		{
			return '';
		}
		else
		{
			if (is_null($wrap))
			{
				$wrap = $this->prefix_suffix_wrapper;
			}

			if ($wrap)
			{
				return '<span class="prefix_display">' . $prefix_text_display . '</span>';
			}
			else
			{
				return $prefix_text_display;
			}
		}
	}

	
	public function getDisplaySuffixText($wrap = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$suffix_text_display = $this->suffix_text_display;
		if (empty($suffix_text_display))
		{
			return '';
		}
		else
		{
			if (is_null($wrap))
			{
				$wrap = $this->prefix_suffix_wrapper;
			}

			if ($wrap)
			{
				return '<span class="suffix_display">' . $suffix_text_display . '</span>';
			}
			else
			{
				return $suffix_text_display;
			}
		}
	}

	
	protected function getRegex()
	{
		$regex = $this->params->get('regex', '');

		if ($regex == "custom")
		{
			$regex = trim($this->params->get('custom_regex', ''));
		}

		if (!$regex)
		{
			$regex = $this->regex;
		}

		return $regex;
	}

	
	protected function JSValidate()
	{
		$regex = $this->getRegex();

		if (!$regex)
		{
			return false;
		}

		$invalid_message = (string) $this->params->get('invalid_message');

		if ($invalid_message)
		{
			$invalid_message = JText::sprintf($invalid_message, $this->getCaption(true));
		}
		else
		{
			$invalid_message = JText::sprintf('COM_JUDOWNLOAD_FIELD_VALUE_IS_INVALID', $this->getCaption(true));
		}

		$invalid_message = htmlspecialchars($invalid_message, ENT_COMPAT, 'UTF-8');
		$validate_id     = $this->getId();
		$document        = JFactory::getDocument();

		$script = "window.addEvent('domready', function() {
			jQuery('#" . $this->getId() . "-lbl').data(\"invalid_message\",\"" . $invalid_message . "\" )
			document.formvalidator.setHandler('" . $validate_id . "',
				function (value) {
					if(value=='') {
						return true;
					}
					var regex = " . $regex . ";
					return regex.test(value);
				});
			});";

		$document->addScriptDeclaration($script);

		return true;
	}

	
	public function PHPValidate($values)
	{
		
		if (($values === "" || $values === null) && !$this->isRequired())
		{
			return true;
		}

		

		$validate = (string) $this->params->get("validate", "");

		
		if (strpos($validate, '::') !== false && is_callable(explode('::', $validate)))
		{
			
		}
		
		elseif (function_exists($validate))
		{
			return call_user_func($validate, $values);
		}

		if ($values === "")
		{
			if ($this->isRequired())
			{
				return JText::sprintf('COM_JUDOWNLOAD_FIELD_IS_REQUIRED', $this->getCaption(true));
			}
			else
			{
				return true;
			}
		}
		else
		{
			
			$regex = $this->getRegex();

			if (!$regex)
			{
				return true;
			}

			if (preg_match($regex, $values))
			{
				return true;
			}
			else
			{
				
				$message = (string) $this->params->get('invalid_message');

				if ($message)
				{
					return JText::sprintf($message, $this->getCaption(true));
				}
				else
				{
					return JText::sprintf('COM_JUDOWNLOAD_FIELD_VALUE_IS_INVALID', $this->getCaption(true));
				}
			}
		}

		return true;
	}

	
	public function setError($error)
	{
		array_push($this->errors, $error);
	}

	
	public function getError($i = null, $toString = true)
	{
		
		if ($i === null)
		{
			
			$error = end($this->errors);
		}
		elseif (!array_key_exists($i, $this->errors))
		{
			
			return false;
		}
		else
		{
			$error = $this->errors[$i];
		}

		
		if ($error instanceof Exception && $toString)
		{
			return (string) $error;
		}

		return $error;
	}

	
	public function getErrors()
	{
		return $this->errors;
	}

	
	protected function getFilter()
	{
		$filter = $this->params->get('filter', '');
		if ($filter)
		{
			return $filter;
		}
		else
		{
			return $this->filter;
		}
	}

	
	public function filterField($value)
	{
		
		$filter = (string) $this->getFilter();

		
		$return = null;

		switch (strtoupper($filter))
		{
			
			case 'UNSET':
				break;

			
			case 'RAW':
				$return = $value;
				break;

			
			case 'INT_ARRAY':
				
				if (is_object($value))
				{
					$value = get_object_vars($value);
				}
				$value = is_array($value) ? $value : array($value);

				JArrayHelper::toInteger($value);
				$return = $value;
				break;

			
			case 'SAFEHTML':
				$return = JFilterInput::getInstance(null, null, 1, 1)->clean($value, 'string');
				break;

			
			case 'SERVER_UTC':
				if (intval($value) > 0)
				{
					
					$offset = JFactory::getConfig()->get('offset');

					
					$return = JFactory::getDate($value, $offset)->toSql();
				}
				else
				{
					$return = '';
				}
				break;

			
			case 'USER_UTC':
				if (intval($value) > 0)
				{
					
					$offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));

					
					$return = JFactory::getDate($value, $offset)->toSql();
				}
				else
				{
					$return = '';
				}
				break;

			
			
			case 'URL':
				if (empty($value))
				{
					return false;
				}
				$value = JFilterInput::getInstance()->clean($value, 'html');
				$value = trim($value);

				
				$value = str_replace(array('<', '>', '"'), '', $value);

				
				$protocol = parse_url($value, PHP_URL_SCHEME);

				if (!$protocol)
				{
					$host = JUri::getInstance('SERVER')->gethost();

					
					if (substr($value, 0) == $host)
					{
						$value = 'http://' . $value;
					}
					
					else
					{
						$value = JUri::root() . $value;
					}
				}

				$return = $value;
				break;

			case 'TEL':
				$value = trim($value);
				
				if (preg_match('/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/', $value) == 1)
				{
					$number = (string) preg_replace('/[^\d]/', '', $value);
					if (substr($number, 0, 1) == 1)
					{
						$number = substr($number, 1);
					}
					if (substr($number, 0, 2) == '+1')
					{
						$number = substr($number, 2);
					}
					$result = '1.' . $number;
				}
				
				elseif (preg_match('/^\+(?:[0-9] ?){6,14}[0-9]$/', $value) == 1)
				{
					$countrycode = substr($value, 0, strpos($value, ' '));
					$countrycode = (string) preg_replace('/[^\d]/', '', $countrycode);
					$number      = strstr($value, ' ');
					$number      = (string) preg_replace('/[^\d]/', '', $number);
					$result      = $countrycode . '.' . $number;
				}
				
				elseif (preg_match('/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/', $value) == 1)
				{
					if (strstr($value, 'x'))
					{
						$xpos  = strpos($value, 'x');
						$value = substr($value, 0, $xpos);
					}
					$result = str_replace('+', '', $value);

				}
				
				elseif (preg_match('/[0-9]{1,3}\.[0-9]{4,14}$/', $value) == 1)
				{
					$result = $value;
				}
				
				else
				{
					$value = (string) preg_replace('/[^\d]/', '', $value);
					if ($value != null && strlen($value) <= 15)
					{
						$length = strlen($value);
						
						if ($length <= 12)
						{
							$result = '.' . $value;

						}
						else
						{
							
							$cclen  = $length - 12;
							$result = substr($value, 0, $cclen) . '.' . substr($value, $cclen);
						}
					}
					
					else
					{
						$result = '';
					}
				}
				$return = $result;
				break;
			default:
				
				if (strpos($filter, '::') !== false && is_callable(explode('::', $filter)))
				{
					$return = call_user_func(explode('::', $filter), $value);
				}
				
				elseif (function_exists($filter))
				{
					$return = call_user_func($filter, $value);
				}
				
				else
				{
					$return = JFilterInput::getInstance()->clean($value, $filter);
				}
				break;
		}

		return $return;
	}

	
	public function getLabel($required = true)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if ($required && $this->isRequired())
		{
			$this->addAttribute("class", "required", "label");
		}

		$this->setVariable('required', $required);

		return $this->fetch('label.php');
	}

	
	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ($this->getAttribute("type", "", "input") == "")
		{
			$this->setAttribute("type", "text", "input");
		}

		$this->addAttribute("class", $this->getInputClass(), "input");

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	
	public function getCountryFlag()
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$lang = $this->language;
		$flag = '';
		if ($lang != '*' && $lang != '')
		{
			$lang_arr     = explode('-', $lang);
			$country_code = strtolower($lang_arr[0]);
			$flag         = '<span class="flag flag-' . $country_code . '"><img src="' . JUri::root() . '/media/mod_languages/images/' . $country_code . '.gif" alt="' . $lang . '"/></span>';
		}

		return $flag;
	}

	
	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('values', $this->value);
		$this->setVariable('options', $options);

		return $this->fetch('output.php', __CLASS__);
	}

	
	public function getBackendOutput()
	{
		$values = $this->value;
		
		if (is_array($values))
		{
			$html = '';
			if ($values)
			{
				$html = '<ul class="nav">';
				foreach ($values AS $value)
				{
					$html .= '<li>' . $value . '</li>';
				}
				$html .= '</ul>';
			}
		}
		
		else
		{
			if ($this->params->get("is_numeric", 0))
			{
				$totalNumbers  = $this->params->get("digits_in_total", 11);
				$decimals      = $this->params->get("digits_after_decimal", 2);
				$dec_point     = $this->params->get("dec_point", ".");
				$thousands_sep = $this->params->get("use_thousands_sep", 0) ? $this->params->get("thousands_sep", ",") : "";
				
				$values = $this->numberFormat($values, $totalNumbers, $decimals, $dec_point, $thousands_sep);
			}

			$html = $values;
		}

		return $html;
	}

	
	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ($this->getAttribute("type", "", "search") == "")
		{
			$this->setAttribute("type", "text", "search");
		}

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "search");
		}

		$this->setVariable('defaultValue', $defaultValue);

		return $this->fetch('searchinput.php');
	}

	
	public function loadDefaultAssets($loadJS = true, $loadCSS = true)
	{
		static $loaded = array();

		if ($this->folder && !isset($loaded[$this->folder]))
		{
			$document = JFactory::getDocument();
			
			if ($loadJS)
			{
				$js_path = JPATH_SITE . "/components/com_judownload/fields/" . $this->folder . "/" . $this->folder . ".js";
				if (JFile::exists(JPath::clean($js_path)))
				{
					$document->addScript(JUri::root() . "components/com_judownload/fields/" . $this->folder . "/" . $this->folder . ".js");
				}
			}

			
			if ($loadCSS)
			{
				$css_path = JPATH_SITE . "/components/com_judownload/fields/" . $this->folder . "/" . $this->folder . ".css";
				if (JFile::exists(JPath::clean($css_path)))
				{
					$document->addStyleSheet(JUri::root() . "components/com_judownload/fields/" . $this->folder . "/" . $this->folder . ".css");
				}
			}

			$loaded[$this->folder] = true;
		}
	}

	
	public function getRawData()
	{
		return null;
	}

	
	public function onSave($data)
	{
		return $data;
	}

	
	public function onSaveDocument($value = '')
	{
		if (is_array($value))
		{
			$value = implode("|", $value);
		}

		if (is_object($value))
		{
			$value = json_encode($value);
		}

		return $value;
	}

	
	public function onDelete($deleteAll = false)
	{
		if ($this->isCore())
		{
			return false;
		}

		$docIds = array();
		if ($this->doc_id)
		{
			$docIds = (array) $this->doc_id;
		}
		elseif ($deleteAll)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT doc_id FROM #__judownload_fields_values WHERE field_id = " . $this->id;
			$db->setQuery($query);
			$docIds = $db->loadColumn();
		}

		if (!$docIds)
		{
			return false;
		}

		foreach ($docIds as $docId)
		{
			$this->deleteExtraData($docId);

			
			$db    = JFactory::getDbo();
			$query = "DELETE FROM #__judownload_fields_values WHERE field_id = " . (int) $this->id . " AND doc_id = " . (int) $docId;
			$db->setQuery($query);

			$db->execute();
		}

		return true;
	}

	
	public function deleteExtraData($docId = null)
	{
		return true;
	}

	
	public function onCopy($toDocId, &$fieldData = array())
	{
		if ($this->isCore())
		{
			return false;
		}

		$docId = $this->doc_id;
		if (!$docId)
		{
			return false;
		}

		$this->copyExtraData($toDocId);

		
		$db    = JFactory::getDbo();
		$query = "INSERT INTO `#__judownload_fields_values` (field_id, doc_id, value, counter) SELECT field_id, $toDocId, value, counter FROM `#__judownload_fields_values` WHERE field_id = $this->id AND doc_id = $docId";
		$db->setQuery($query);

		return $db->execute();
	}

	
	public function copyExtraData($toDocId)
	{
		return true;
	}

	
	public function onSearch(&$query, &$where, $search)
	{
		if ($search === "")
		{
			return false;
		}

		if (!$this->isCore())
		{
			$query->join('LEFT', '#__judownload_fields_values AS field_values_' . $this->id . ' ON (d.id = field_values_' . $this->id . '.doc_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');
		}

		
		if (is_string($search))
		{
			
			if ($this->params->get("is_numeric", 0))
			{
				$search = (int) $search;

				$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("digits_after_decimal", 2) . ") ) = $search )";
			}
			
			else
			{
				$db = JFactory::getDbo();

				$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
			}
		}
		
		elseif (is_array($search))
		{
			
			if ($this->params->get("is_numeric", 0))
			{
				if ($search['from'] !== "" && $search['to'] !== "")
				{
					$from = (int) $search['from'];
					$to   = (int) $search['to'];
					if ($from > $to)
					{
						$this->swap($from, $to);
					}

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("digits_after_decimal", 2) . ") ) BETWEEN $from AND $to )";
				}
				elseif ($search['from'] !== "")
				{
					$from = (int) $search['from'];

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("digits_after_decimal", 2) . ") ) >= $from )";
				}
				elseif ($search['to'] !== "")
				{
					$to = (int) $search['to'];

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("digits_after_decimal", 2) . ") ) <= $to )";
				}
			}
			
			else
			{
				$db     = JFactory::getDbo();
				$_where = array();
				foreach ($search AS $value)
				{
					if ($value !== "")
					{
						
						$_where[] = "( " . $this->fieldvalue_column . " = " . $db->quote($value) .
							" OR " . $this->fieldvalue_column . " LIKE '" . $db->escape($value, true) . "|%'" .
							" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($value, true) . "|%'" .
							" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($value, true) . "' )";
					}
				}

				if (!empty($_where))
				{
					
					$search_operator = " " . $this->params->get("search_operator", "OR") . " ";
					$where[]         = "(" . implode($search_operator, $_where) . ")";
				}
			}
		}
	}

	
	public function onSimpleSearch(&$query, &$where, $search)
	{
		
		if (is_string($search))
		{
			$search = JUDownloadFrontHelper::UrlDecode($search);
		}

		$this->onSearch($query, $where, $search);
	}

	
	public function onTagSearch(&$query, &$where, $tag = null)
	{
		
		if (!$this->params->get("tag_search", 0))
		{
			return false;
		}

		if (is_null($tag))
		{
			$app = JFactory::getApplication();
			$tag = $app->input->get("tag", "", "string");
		}

		if (!$this->isCore())
		{
			$query->join('', '#__judownload_fields_values AS field_values_' . $this->id . ' ON (d.id = field_values_' . $this->id . '.doc_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');
		}

		if ($tag !== "")
		{
			
			$tag = JUDownloadFrontHelper::UrlDecode($tag);

			
			$db = JFactory::getDbo();
			
			$_where = "(( " . $this->fieldvalue_column . " = " . $db->quote($tag) .
				" OR " . $this->fieldvalue_column . " LIKE '" . $db->escape($tag, true) . "|%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($tag, true) . "|%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($tag, true) . "' )";
			$_where .= " OR ( " . $this->fieldvalue_column . " LIKE '" . $db->escape($tag, true) . ",%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%," . $db->escape($tag, true) . ",%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%," . $db->escape($tag, true) . "' ))";
			$where[] = $_where;
		}
	}

	
	public function getTextByValue($value)
	{
		$options = $this->getPredefinedValues();
		if (is_array($options))
		{
			foreach ($options AS $option)
			{
				if ($option->value == $value)
				{
					return $option->text;
				}
			}
		}

		return $value;
	}

	
	public function onAutoSuggest($string)
	{
		
		if (!$this->params->get('auto_suggest', 0))
		{
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($this->isCore())
		{
			$query->select('DISTINCT ' . $db->quoteName($this->field_name));
			$query->from('#__judownload_documents');
			$query->where($db->quoteName($this->field_name) . " LIKE '%" . $db->escape($string, true) . "%'");
		}
		else
		{
			$query->select('DISTINCT value');
			$query->from('#__judownload_fields_values');
			$query->where('field_id = ' . $this->id);
			$query->where("value LIKE '%" . $db->escape($string, true) . "%'");
		}

		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	
	public function isPublished()
	{
		$storeId = md5(__METHOD__ . "::" . $this->id);
		if (!isset(self::$cache[$storeId]))
		{
			if (!$this->published)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$date = JFactory::getDate();
			if (intval($this->publish_down) > 0 && $this->publish_down <= $date->toSql())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($this->publish_up > $date->toSql())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$fieldGroupObj = JUDownloadFrontHelperField::getFieldGroupById($this->group_id);
			if (!$fieldGroupObj->published)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = true;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	public function isDetailsView($options = array())
	{
		
		if (is_object($options))
		{
			$view = $options->get('view');
			if (isset($view))
			{
				if ($view == 'details')
				{
					return true;
				}

				return false;
			}
			else
			{
				
				$app  = JFactory::getApplication();
				$view = $app->input->get('view', '');
				if (strtolower($view) == 'document')
				{
					return true;
				}

				return false;
			}
		}
		else
		{
			if (isset($options['view']))
			{
				if ($options['view'] == 'details')
				{
					return true;
				}

				return false;
			}
			else
			{
				$app  = JFactory::getApplication();
				$view = $app->input->get('view', '');
				if (strtolower($view) == 'document')
				{
					return true;
				}

				return false;
			}
		}
	}

	
	public function canView($options = array())
	{
		$storeId = md5(__METHOD__ . "::" . $this->doc_id . "::" . $this->id . "::" . serialize($options));

		if (!isset(self::$cache[$storeId]))
		{
			
			if (!$this->isPublished())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$app            = JFactory::getApplication();
			$languageFilter = $app->getLanguageFilter();
			if ($languageFilter)
			{
				$languageTag = JFactory::getLanguage()->getTag();
				if (($this->language != $languageTag && $this->language != '*' && $this->language != ''))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			if ($this->doc_id)
			{
				$document_display_params = JUDownloadFrontHelperDocument::getDocumentDisplayParams($this->doc_id);
				$document_display_fields = $document_display_params->get('fields');
			}

			$options = (array) $options;

			$field_name = $this->field_name ? $this->field_name : $this->id;
			if ($this->isDetailsView($options))
			{
				
				if (isset($document_display_fields->$field_name) && isset($document_display_fields->$field_name->details_view))
				{
					if (!$document_display_fields->$field_name->details_view)
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
				
				elseif (!$this->details_view)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}
			else
			{
				
				if (isset($document_display_fields->$field_name) && isset($document_display_fields->$field_name->list_view))
				{
					if (!$document_display_fields->$field_name->list_view)
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
				
				elseif (!$this->list_view)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			
			if (isset($this->doc) && $this->doc->cat_id)
			{
				$params = JUDownloadHelper::getParams($this->doc->cat_id);
			}
			else
			{
				$params = JUDownloadHelper::getParams(null, $this->doc_id);
			}

			
			$show_empty_field = $params->get('show_empty_field', 0);
			if ($this->doc_id && !$show_empty_field)
			{
				$field_value = $this->value;

				if (is_null($field_value))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				if (is_string($field_value) && trim($field_value) === '')
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				if (is_array($field_value) && count($field_value) == 0)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			$user = JFactory::getUser();
			if ($user)
			{
				$viewLevels    = JAccess::getAuthorisedViewLevels($user->id);
				$fieldGroupObj = JUDownloadFrontHelperField::getFieldGroupById($this->group_id);
				
				if (!in_array($fieldGroupObj->access, $viewLevels))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
				else
				{
					if (in_array($this->access, $viewLevels))
					{
						self::$cache[$storeId] = true;

						return self::$cache[$storeId];
					}
					else
					{
						
						if ($this->doc_id && $this->who_can_download_can_access)
						{
							$canDownloadDocument = JUDownloadFrontHelperPermission::canDownloadDocument((int) $this->doc_id, true);

							return $canDownloadDocument;
						}
					}
				}
			}

			self::$cache[$storeId] = false;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	public function canSubmit($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		$app = JFactory::getApplication();

		
		if ($app->isAdmin())
		{
			return true;
		}
		else
		{
			if ($userID)
			{
				$user = JFactory::getUser($userID);
			}
			else
			{
				$user = JFactory::getUser();
			}

			
			if ($app->input->getInt('approve', 0) == 1)
			{
				if (is_object($this->doc) && $this->doc->approved <= 0)
				{
					
					$modCanApprove = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($this->doc->cat_id, 'document_approve');
					if ($modCanApprove)
					{
						return true;
					}
				}
			}

			
			$approvalOption      = $app->input->post->get("approval_option");
			$approvalOptionArray = array("ignore", "approve", "delete");
			if (in_array($approvalOption, $approvalOptionArray))
			{
				if (is_object($this->doc) && $this->doc->approved <= 0)
				{
					$categoriesField = new JUDownloadFieldCore_categories();
					$newMainCategory = $this->fields_data[$categoriesField->id]['main'];
					if ($newMainCategory)
					{
						$modCanApprove = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($newMainCategory, 'document_approve');
						if ($modCanApprove)
						{
							return true;
						}
					}
				}
			}

			if ($user)
			{
				$assetName = 'com_judownload.field.' . (int) $this->id;

				return $user->authorise("judl.field.value.submit", $assetName);
			}
		}

		return false;
	}

	
	public function canEdit($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		$app = JFactory::getApplication();
		
		if ($app->isAdmin())
		{
			return true;
		}
		else
		{
			if ($userID)
			{
				$user = JFactory::getUser($userID);
			}
			else
			{
				$user = JFactory::getUser();
			}

			if ($user)
			{
				$assetName = 'com_judownload.field.' . (int) $this->id;

				$canEdit = $user->authorise("judl.field.value.edit", $assetName);

				if ($canEdit)
				{
					return true;
				}
				else
				{
					if (!$user->get('guest'))
					{
						if (is_object($this->doc) && $this->doc->approved == 1)
						{
							if ($user->id == $this->doc->created_by)
							{
								return $user->authorise("judl.field.value.edit.own", $assetName);
							}
						}
					}
				}
			}
		}

		return false;
	}

	
	public function canSearch($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		if (!$this->advanced_search && !$this->filter_search)
		{
			return false;
		}

		if ($userID)
		{
			$user = JFactory::getUser($userID);
		}
		else
		{
			$user = JFactory::getUser();
		}

		if ($user)
		{
			$assetName = 'com_judownload.field.' . (int) $this->id;

			return $user->authorise("judl.field.value.search", $assetName);
		}

		return false;
	}

	
	public function setVariable($variable, $value)
	{
		$this->vars[$variable] = $value;
	}

	
	protected function getTmplFile($file = 'output.php', $class = null)
	{
		if (is_null($class))
		{
			$class = 'JUDownloadFieldBase';
		}

		$folder = str_replace('judownloadfield', '', strtolower($class));

		
		$templatePaths   = array();
		$templatePaths[] = JPATH_SITE . '/components/com_judownload/fields/' . $folder . '/tmpl/';
		$app             = JFactory::getApplication();
		if ($app->isSite())
		{
			$currentTemplateStyleObject = JUDownloadFrontHelperTemplate::getCurrentTemplateStyle();
			$JUTemplatePath             = JUDownloadFrontHelperTemplate::getTemplatePathWithoutRoot($currentTemplateStyleObject->template_id);
			if ($JUTemplatePath)
			{
				foreach ($JUTemplatePath as $template)
				{
					$templatePaths[] = JPATH_SITE . '/components/com_judownload/templates/' . $template->folder . '/fields/' . $folder . '/';
					$templatePaths[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_judownload/' . $template->folder . '/fields/' . $folder . '/';
				}
			}
		}

		$templatePaths = array_reverse($templatePaths);
		foreach ($templatePaths AS $templatePath)
		{
			$path = $templatePath . $file;
			if (JFile::exists($path))
			{
				return $path;
			}
		}

		return $file;
	}

	
	public function fetch($file = 'output.php', $class = null)
	{
		if (!JFile::exists($file))
		{
			$file = $this->getTmplFile($file, $class);
		}

		
		unset($class);

		if ($this->vars)
		{
			extract($this->vars);
		}

		ob_start();

		if (JFile::exists($file))
		{
			include($file);
		}
		else
		{
			echo JText::sprintf('Template file not found: %s', $file);
		}

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	
	public function orderingPriority(&$query = null)
	{
		if ($this->isCore())
		{
			return array('ordering' => 'd.' . $this->field_name, 'direction' => $this->priority_direction);
		}
		else
		{
			if ($this->params->get('is_numeric', 0))
			{
				$this->appendQuery($query, "select", "CONVERT(field_values_" . $this->id . ".value, DECIMAL(" . $this->params->get("digits_in_total", 6) . "," . $this->params->get("digits_after_decimal", 2) . ")) AS field_value_" . $this->id);
			}
			else
			{
				$this->appendQuery($query, "select", "field_values_" . $this->id . ".value AS field_value_" . $this->id);
			}

			$this->appendQuery($query, "left join", '#__judownload_fields_values AS field_values_' . $this->id . ' ON (d.id = field_values_' . $this->id . '.doc_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');

			return array('ordering' => 'field_value_' . $this->id, 'direction' => $this->priority_direction);
		}
	}

	
	protected function appendQuery(&$query, $type, $element)
	{
		switch (strtolower($type))
		{
			case 'select':
				if (!$this->checkQueryExists($element, $query->select->getElements()))
				{
					$query->select($element);
				}
				break;
			case 'where':
				if (!$this->checkQueryExists($element, $query->where->getElements()))
				{
					$query->where($element);
				}
				break;

			case 'join':
			case 'left join':
			case 'right join':
				$append = true;
				foreach ($query->join AS $join)
				{
					if ($this->checkQueryExists($element, $join->getElements()))
					{
						$append = false;
						break;
					}
				}

				if ($append == true)
				{
					if ($type == 'join')
					{
						$query->join('', $element);
					}
					elseif ($type == 'left join')
					{
						$query->join('LEFT', $element);
					}
					else
					{
						$query->join('RIGHT', $element);
					}
				}
				break;
		}
	}

	
	protected function checkQueryExists($needle, $haystack)
	{
		if (!$needle)
		{
			return true;
		}

		if (!$haystack)
		{
			return false;
		}

		$needle = strtolower(preg_replace('/\s+/', ' ', trim($needle)));

		foreach ($haystack AS $element)
		{
			$element = strtolower(preg_replace('/\s+/', ' ', trim($element)));
			if ($element == $needle)
			{
				return true;
			}
		}

		return false;
	}

	
	public function loadLanguage($fieldFolder)
	{
		
		$storeId = md5(__METHOD__ . "::" . $fieldFolder);

		if (!isset(self::$cache[$storeId]))
		{
			$fieldXmlPath = JPATH_SITE . '/components/com_judownload/fields/' . $fieldFolder . '/' . $fieldFolder . '.xml';

			if (JFile::exists($fieldXmlPath))
			{
				$field_xml = JFactory::getXML($fieldXmlPath, true);

				
				if ($field_xml->languages->count())
				{
					foreach ($field_xml->languages->children() AS $language)
					{
						$languageFile = (string) $language;
						
						$first_pos       = strpos($languageFile, '.');
						$last_pos        = strrpos($languageFile, '.');
						$languageExtName = substr($languageFile, $first_pos + 1, $last_pos - $first_pos - 1);

						
						$client = JApplicationHelper::getClientInfo((string) $language->attributes()->client, true);
						$path   = isset($client->path) ? $client->path : JPATH_BASE;

						JUDownloadFrontHelperLanguage::loadLanguageFile($languageExtName, $path);
					}
				}
			}

			self::$cache[$storeId] = true;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	protected function numberFormat($number, $totalNumbers = 11, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
	{
		$number         = (float) $number;
		$int            = $totalNumbers - $decimals;
		$number         = number_format($number, $decimals);
		$number         = preg_replace("/[^0-9\.]+/", "", $number);
		$numberArr      = explode(".", $number);
		$spNumberArray0 = str_split($numberArr[0]);
		if (count($spNumberArray0) > $int)
		{
			$spNumberArray0 = array();
			for ($i = 0; $i < $int; $i++)
			{
				$spNumberArray0[] = 9;
			}
		}

		$integerArr    = array_reverse($spNumberArray0);
		$newIntegerArr = array();
		for ($i = 0; $i < count($integerArr); $i++)
		{
			$newIntegerArr[] = $integerArr[$i];
			if (($i + 1) % 3 == 0 && $i < count($integerArr) - 1)
			{
				$newIntegerArr[] = $thousands_sep;
			}
		}

		$number = implode("", array_reverse($newIntegerArr));
		if (isset($numberArr[1]))
		{
			if (count(str_split($numberArr[0])) > $int)
			{
				$number = $number . $dec_point . str_repeat(9, count($numberArr[1]) + 1);
			}
			else
			{

				$number = $number . $dec_point . $numberArr[1];
			}
		}

		return $number;
	}

	
	public function swap(&$value1, &$value2)
	{
		if ($value1 > $value2)
		{
			$temp   = $value1;
			$value1 = $value2;
			$value2 = $temp;
		}
	}

	
	public function onImportDocument($value)
	{
		return $value;
	}
}