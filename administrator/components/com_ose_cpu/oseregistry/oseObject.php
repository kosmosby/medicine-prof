<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die(";)");
class oseObject extends JObject {
	function __construct($p = array()) {
		parent::__construct($p);
		if (is_string($this->get('params'))) {
			$this->set('params', oseJson::decode($this->get('params')));
		}
	}
	protected function registerTask($task, $funcName) {
		$this->task[$task] = $funcName;
	}
	protected function registerInstance($task, $instanceName) {
		$this->instance[$task] = $instanceName;
	}
	function __call($name, $args) {
		if (isset($this->task[$name])) {
			return call_user_func_array(array($this, $this->task[$name]), $args);
		} else {
			$t = debug_backtrace(false);
			foreach ($t as $d) {
				if (isset($d['file'])) {
					echo $d['file'] . ' ' . $d['function'] . ' line:' . $d['line'];
					echo "<br \>";
				}
			}
			echo get_class($this) . '::' . $name . ' Error';
			oseExit();
		}
	}
	function getParams($item, $type = 'obj') {
		$mode = ($type == 'obj') ? false : true;
		$params = oseGetValue($item, 'params', '{}');
		$params = oseJson::decode($params, $mode);
		return $params;
	}
	function setParams($item, $params) {
		$itemParams = oseGetValue($item, 'params', '{}');
		$itemParams = oseJson::decode($itemParams);
		if (!is_Array($params)) {
			$params = (array) $params;
		}
		foreach ($params as $key => $value) {
			$itemParams = oseSetValue($itemParams, $key, $value);
		}
		$itemParams = oseJson::encode($itemParams);
		$item = oseSetValue($item, 'params', $itemParams);
		return $item;
	}
	private static function isObject($item) {
		if (is_object($item)) {
			return true;
		} else {
			return false; // array
		}
	}
	// value for example, 2 setting, int.1, int and 1
	function checkVarType($require, $args, $auto_correct = true) {
		foreach ($require as $key => $value) {
			$value = explode('.', $value);
			$value[1] = empty($value[1]) ? 0 : $value[1];
			$type = $value[0];
			$num = $value[1];
			$isType = call_user_method('is' . $type, $this, $args[$key], $num, false);
			if (!$isType) {
				return false;
			}
		}
		return true;
	}
	protected function isInt($value, $num, $auto_correct) {
		if (is_Numeric($value)) {
			$value = $value;
		} else {
			$value = false;
		}
		return $value;
	}
	protected function isString($value, $num, $auto_correct) {
		if (is_string($value)) {
			if (strlen($value) > $num) {
				if ($auto_correct) {
					$value = substr($value, 0, $num);
				}
			}
		} else {
			$value = false;
		}
		return $value;
	}
	protected function isFloat($value, $num, $auto_correct) {
		if (is_float($value)) {
			if (!empty($num)) {
				if (strpos($value, '.')) {
					$number = explode('.', $value);
					if (strlen($number[1]) > $num) {
						if ($auto_correct) {
							$value = round($value, $num);
						}
					}
				}
			}
		} else {
			$value = false;
		}
		return $value;
	}
	protected function isArray($value, $num, $auto_correct) {
		$value = (!is_array($value)) ? false : $value;
		return $value;
	}
	static function getValue($item, $key, $default = null) {
		return oseGetValue($item, $key, $default);
	}
	static function setValue(&$item, $key, $default = null) {
		return oseSetValue($item, $key, $default);
	}
}
/*
 *  for 1.5 to do js as 1.6
 */
class oseText {
	/*
	 * javascript strings
	 */
	static $strings = array();
	/**
	 * Translates a string into the current language.
	 *
	 * @param	string			The string to translate.
	 * @param	boolean|array	boolean: Make the result javascript safe. array an array of option as described in the JText::sprintf function
	 * @param	boolean			To interprete backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param	boolean			To indicate that the string will be push in the javascript language store
	 * @return	string			The translated string or the key is $script is true
	 * @example	<script>alert(Joomla.JText._('<?php echo JText::_("JDEFAULT", array("script"=>true));?>'));</script> will generate an alert message containing 'Default'
	 * @example	<?php echo JText::_("JDEFAULT");?> it will generate a 'Default' string
	 * @since	1.5
	 *
	 */
	public static function _($string, $jsSafe = false, $interpreteBackSlashes = true, $script = false) {
		$lang = JFactory::getLanguage();
		if (is_array($jsSafe)) {
			if (array_key_exists('interpreteBackSlashes', $jsSafe)) {
				$interpreteBackSlashes = (boolean) $jsSafe['interpreteBackSlashes'];
			}
			if (array_key_exists('script', $jsSafe)) {
				$script = (boolean) $jsSafe['script'];
			}
			if (array_key_exists('jsSafe', $jsSafe)) {
				$jsSafe = (boolean) $jsSafe['jsSafe'];
			} else {
				$jsSafe = false;
			}
		}
		if ($script) {
			self::$strings[$string] = $lang->_($string, $jsSafe);
			return $string;
		} else {
			return $lang->_($string, $jsSafe);
		}
	}
	public static function jsStrings() {
		return self::$strings;
	}
}
?>