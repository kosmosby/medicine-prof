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
class oseHTML {
	function __construct() {
	}
	public static function getInstance($type) {
		static $instance;
		$classname = 'oseHtml' . ucfirst($type);
		if (is_a($instance, $classname)) {
			if (strtolower(get_class($instance)) == strtolower($classname)) {
				return $instance;
			}
		}
		switch ($type) {
		case ('form'):
			require_once(dirname(__FILE__) . DS . 'html' . DS . 'form.php');
			return new $classname;
			break;
		default:
			require_once(dirname(__FILE__) . DS . 'html' . DS . strtolower($type) . ".php");
			return new $classname;
			break;
		}
	}
	public static function initScript($type = 'extjs') {
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		self::initCss();
		switch ($type) {
		case ('jquery'):
			self::script('media/system/js/core.js', $version);
			self::script('components/com_ose_cpu/jquery/jquery.min.js', $version);
			self::script('components/com_ose_cpu/extjs/adapter/jquery/ext-jquery-adapter.js', $version);
			self::script('components/com_ose_cpu/extjs/ext-all.js', $version);
			break;
		case ('extjs'):
		default:
			self::script('media/system/js/core.js', $version);
			self::script('components/com_ose_cpu/extjs/adapter/ext/ext-base.js', $version);
			self::script('components/com_ose_cpu/extjs/ext-all.js', $version);
			break;
		}
	}
	public static function initCss() {
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		self::stylesheet('components/com_ose_cpu/extjs/resources/css/ext-all.css', $version);
	}
	public static function script($file, $jVersion = '1.6', $framework = false, $relative = false, $path_only = false) {
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		$jVersion = $version;
		if ($jVersion >= '1.6') {
			if (!strstr($file, "http")) {
				$file = str_replace('//', '/', $file);
				$file = JURI::root() . $file;
			}
			JHTML::script($file, $framework, $relative, $path_only);
		} else {
			$fPath = dirname($file) . '/';
			$fName = basename($file);
			JHTML::script($fName, $fPath);
		}
	}
	public static function stylesheet($file, $jVersion = '1.6', $attribs = array(), $relative = false, $path_only = false) {
		$jVersion = self::getJoomlaVersion();
		if ($jVersion >= '1.6') {
			if (!strstr($file, "http")) {
				$file = str_replace('//', '/', $file);
				$file = JURI::root() . $file;
			}
			JHTML::stylesheet($file, $attribs = array(), $relative = false, $path_only = false);
		} else {
			$fPath = dirname($file) . '/';
			$fName = basename($file);
			JHTML::stylesheet($fName, $fPath);
		}
	}
	public static function getDateTime() {
		$date = JFactory::getDate();
		$jVersion = self::getJoomlaVersion();
		if (JOOMLA30 == true) {
			$date = JFactory::getDate(JHTML::Date($date, "Y-m-d H:i:s", false));
			return $date->toSQL();
		} elseif (JOOMLA16 == true) {
			$date = JFactory::getDate(JHTML::Date($date, "Y-m-d H:i:s", false));
			return $date->toMySQL();
		} else {
			$date = JFactory::getDate(JHTML::Date($date->_date, "%Y-%m-%d %H:%M:%S"));
			return $date->toMySQL();
		}
	}
	public static function route($link) {
		return JRoute::_($link);
	}
	public static function date($date) {
		return JHTML::date($date);
	}
	public static function getJoomlaVersion() {
		static $jVersion;
		if (!empty($jVersion)) {
			return $jVersion;
		} else {
			$version = new JVersion();
			$version = substr($version->getShortVersion(), 0, 3);
			$jVersion = $version;
			return $jVersion;
		}
	}
	public static function loadViewJs($type = null) {
		oseHTML::initScript($type);
		oseHTML::initCss();
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		$jVersion = self::getJoomlaVersion();
		if (($jVersion >= '1.6') == true) {
			oseHTML::script($com . '/ose/app.msg.js', '1.6');
			$lang = JRequest::getCmd('ose_lang', 'en');
			if (JFile::exists(OSECPU_F_PATH . DS . 'locale' . DS . "ext-lang-{$lang}.js")) {
				oseHTML::script($com . "/locale/ext-lang-{$lang}.js", '1.6');
			}
			oseHTML::script($com . '/ose/func.js', '1.6');
		} else {
			oseHTML::script($com . '/ose/app.msg.js');
			$lang = JRequest::getCmd('lang', 'en');
			oseHTML::script($com . '/ose/joomla.core.js', '1.5');
			$strings = oseJson::encode(oseText::jsStrings());
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('(function(){var strings=' . $strings . ';Joomla.JText.load(strings)})()');
			oseHTML::script($com . '/ose/func.js', '1.5');
		}
	}
	public function loadViewJs2() {
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		$lang = JRequest::getCmd('ose_lang', 'en');
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::stylesheet($com . '/resources/css/ext-all.css');
		self::stylesheet($com . '/ose/msg.css');
		self::script($com . '/ext-all.js');
		self::script($com . '/ose/msg.js');
		self::script($com . '/ose/func.js');
		if (!JFile::exists(JPATH_SITE . DS . 'media' . DS . 'system' . DS . 'js' . DS . 'core.js')) {
			self::script($com . '/ose/joomla.core.js');
		}
	}
	public function loadGridJs() {
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		oseHTML::script($com . '/grid/limit.js');
		oseHTML::script($com . '/grid/quickgrid.js');
		oseHTML::script($com . '/grid/SearchField.js');
	}
	public function loadTinyMCE() {
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		oseHTML::script($com . "/htmleditor/tiny_mce/tiny_mce.js", '1.6');
		oseHTML::script($com . "/htmleditor/TinyMCE.js", '1.6');
	}
	public static function loadTouchJs()
	{
		jimport( 'joomla.version' );
		$version = new JVersion();
		$version = substr($version->getShortVersion(),0,3);
		$lang = JRequest::getCmd('ose_lang','en');
		$com= OSECPU_PATH_JS.'com_ose_cpu/touch2';
		self::script($com.'/sencha-touch-all.js');
		self::stylesheet($com.'/resources/css/sencha-touch.css');
	
		self :: script($com.'/ose/func.js');
		if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'system'.DS.'js'.DS.'core.js'))
		{
				self :: script($com.'/ose/joomla.core.js');
		}
	}
	public function loadMultiSelect() {
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		oseHTML::script($com . '/multiselect/MultiSelect.js');
		oseHTML::stylesheet($com . '/multiselect/MultiSelect.css');
		oseHTML::script($com . '/multiselect/ItemSelector.js');
	}
	public function loadPMJs() {
		oseHtml::script('administrator/components/com_ose_commerce/helpers/js/pm.js', '1.6');
		oseHtml::script('administrator/components/com_ose_commerce/helpers/js/form.js', '1.6');
	}
	public function loadOrderJs() {
		oseHtml::script('administrator/components/com_ose_commerce/helpers/js/orders.js', '1.6');
	}
	static public function loadURIJs() {
		$document = JFactory::getDocument();
		$uri = JFactory::getURI();
		$url = $uri->root();
		$document->addScriptDeclaration('var getCurrentLocation = function() {return "' . $url . '"};');
	}
	static function matchScript($file, $app) {
		$mainframe = JFactory::getApplication();
		$path = ($mainframe->isSite() == true) ? JPATH_SITE : JPATH_ADMINISTRATOR;
		switch ($app) {
		case ('msc'):
			$com = 'com_ose' . $app;
			break;
		default:
			$com = 'com_ose_' . $app;
			break;
		}
		$replace = ($mainframe->isSite() == true) ? 'components/' . $com . '/' : 'administrator/components/' . $com . '/';
		$rFile = str_replace($replace, '', $file);
		$rFile = str_replace(array('/', '\\'), DS, $rFile);
		if (JFile::exists($path . DS . 'ose' . DS . $app . DS . $rFile)) {
			if ($mainframe->isSite() == true) {
				oseHTML::script("ose/{$app}/{$rFile}");
			} else {
				oseHTML::script("administrator/ose/{$app}/{$rFile}");
			}
		} else {
			if (JFile::exists(JPATH_SITE . DS . $file)) {
				oseHTML::script($file);
			}
		}
	}
}
class oseHTMLDraw extends oseInit {
	protected $html = null;
	protected $level = 0;
	function __construct() {
		$this->registerTask('output', 'toString');
	}
	function toString() {
		return $this->html;
	}
	function output() {
		$html = $this->html;
		$this->html = null;
		return $html;
	}
	function append($html) {
		$this->html .= $html;
	}
	function addBreak() {
		$this->append("\r\n");
	}
	function addLevel() {
		$this->level += 1;
	}
	function subLevel() {
		$this->level -= 1;
		if ($this->level < 0) {
			$this->level = 0;
		}
	}
	function setLevel($level) {
		$this->level = $level;
	}
	function addTab($num = 1) {
		$string = str_repeat("\t", $num);
		$this->append($string);
	}
	// shortcut
	function sc($event) {
		switch ($event) {
		case ('a'):
			$this->addLevel();
			break;
		case ('s'):
			$this->subLevel();
			break;
		case ('e'):
			$this->addBreak();
			break;
		case ('t'):
			$this->addTab($this->level);
			break;
		case ('et'):
			$this->sc('e');
			$this->sc('t');
			break;
		// et add level
		case ('eta'):
			$this->addLevel();
			$this->sc('e');
			$this->sc('t');
			break;
		// et sub level
		case ('ets'):
			$this->subLevel();
			$this->sc('e');
			$this->sc('t');
			break;
		}
	}
}
class oseHtml2 {
	public static function date($date) {
		return JHTML::date($date);
	}
	public static function getDateTime() {
		$date = JFactory::getDate();
		$jVersion = self::getJoomlaVersion();
		if (JOOMLA30 == true) {
			$date = JFactory::getDate(JHTML::Date($date, "Y-m-d H:i:s", false));
			return $date->toSQL();
		} elseif (JOOMLA16 == true) {
			$date = JFactory::getDate(JHTML::Date($date, "Y-m-d H:i:s", false));
			return $date->toMySQL();
		} else {
			$date = JFactory::getDate(JHTML::Date($date->_date, "%Y-%m-%d %H:%M:%S"));
			return $date->toMySQL();
		}
	}
	public static function getJoomlaVersion() {
		static $jVersion;
		if (!empty($jVersion)) {
			return $jVersion;
		} else {
			$version = new JVersion();
			$version = substr($version->getShortVersion(), 0, 3);
			$jVersion = $version;
			return $jVersion;
		}
	}
	public static function script($file, $framework = false, $relative = false, $path_only = false) {
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		$jVersion = $version;
		if ($jVersion >= '1.6') {
			JHTML::script($file, $framework, $relative, $path_only);
		} else {
			$fPath = dirname($file) . '/';
			$fName = basename($file);
			JHTML::script($fName, $fPath);
		}
	}
	public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = false) {
		$jVersion = self::getJoomlaVersion();
		if ($jVersion >= '1.6') {
			JHTML::stylesheet($file, $attribs = array(), $relative = false, $path_only = false);
		} else {
			$fPath = dirname($file) . '/';
			$fName = basename($file);
			JHTML::stylesheet($fName, $fPath);
		}
	}
	function matchSheet($file, $app) {
		$mainframe = JFactory::getApplication('CPU');
		$path = JPATH_SITE;
		switch ($app) {
		case ('msc'):
			$com = 'com_ose' . $app;
			break;
		default:
			$com = 'com_ose_' . $app;
			break;
		}
		$replace = ($mainframe->isSite() == true) ? 'components/' . $com . '/' : 'administrator/components/' . $com . '/';
		$rFile = preg_replace('/components\/com_[\w\d]*\//', "ose/{$app}/", $file);
		$rFile1 = str_replace(array('/', '\\'), DS, $rFile);
		if (JFile::exists($path . DS . $rFile1)) {
			oseHTML2::stylesheet($rFile);
		} else {
			if (JFile::exists(JPATH_SITE . DS . $file)) {
				oseHTML2::stylesheet($file);
			} elseif ('index' == substr($file, 0, 5)) {
				$document = JFactory::getDocument();
				$document->addStyleSheet($file);
			}
		}
	}
	static function matchScript($file, $app) {
		$mainframe = JFactory::getApplication();
		$path = JPATH_SITE;
		switch ($app) {
		case ('msc'):
			$com = 'com_ose' . $app;
			break;
		default:
			$com = 'com_ose_' . $app;
			break;
		}
		$replace = ($mainframe->isSite() == true) ? 'components/' . $com . '/' : 'administrator/components/' . $com . '/';
		$rFile = preg_replace('/components\/com_[\w\d]*\//', "ose/{$app}/", $file);
		$rFile1 = str_replace(array('/', '\\'), DS, $rFile);
		if (JFile::exists($path . DS . $rFile1)) {
			oseHTML2::script($rFile);
		} else {
			if (JFile::exists(JPATH_SITE . DS . $file)) {
				oseHTML2::script($file);
			} elseif ('index' == substr($file, 0, 5)) {
				$document = JFactory::getDocument();
				$document->addScript($file);
			}
		}
	}
	public function getInstance($type) {
		static $instance;
		$classname = 'oseHtml' . ucfirst($type);
		if (is_a($instance, $classname)) {
			if (strtolower(get_class($instance)) == strtolower($classname)) {
				return $instance;
			}
		}
		switch ($type) {
		case ('form'):
			require_once(dirname(__FILE__) . DS . 'html' . DS . 'form.php');
			return new $classname;
			break;
		default:
			require_once(dirname(__FILE__) . DS . 'html' . DS . strtolower($type) . ".php");
			return new $classname;
			break;
		}
	}
	public static function loadViewJs() {
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		$lang = JRequest::getCmd('ose_lang', 'en');
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::stylesheet($com . '/resources/css/ext-all.css');
		self::stylesheet($com . '/ose/msg.css');
		self::script($com . '/ext-all.js');
		if (!JFile::exists(JPATH_SITE . DS . 'media' . DS . 'system' . DS . 'js' . DS . 'core.js')) {
			self::script($com . '/ose/joomla.core.js');
		} else {
			self::script('media/system/js/core.js');
		}
		self::script($com . '/ose/msg.js');
		self::script($com . '/ose/func.js');
	}
	public static function loadGridJs($type = null) {
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::script($com . '/grid/limit.js');
		self::script($com . '/grid/SearchField.js');
		self::script($com . '/grid/oseGridPrototypeStore.js');
		switch ($type) {
		case ('RD'):
			self::script($com . '/grid/oseGridPrototypeRD.js');
			break;
		case ('AF'):
			self::script($com . '/grid/oseGridPrototypeAF.js');
			break;
		case ('1'):
			self::script($com . '/grid/oseGridPrototype1.js');
			break;
		case ('Modal'):
			self::script($com . '/grid/oseGridPrototypeModal.js');
			break;
		case ('AM'):
			self::script($com . '/grid/oseGridPrototypeAM.js');
			break;
		default:
			self::script($com . '/grid/oseGridPrototype.js');
			break;
		}
	}
	public function loadTinyMceJs() {
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::script($com . '/tinymce/ext.ux.tinymce.js');
	}
	public static function loadMSJS() {
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::script($com . '/layout/MultiSelect.js');
		self::script($com . '/field/MultiSelect.js');
	}
	public static function loadUsernameJs() {
		$com = OSECPU_PATH_JS . 'com_ose_cpu/extjs4';
		self::script($com . '/field/username.js');
	}
	public static function loadPMJs() {
		oseHtml::script('administrator/components/com_ose_commerce/helpers/js/grid/pm.js', '1.6');
	}
	public static function loadURIJs() {
		$document = JFactory::getDocument();
		$uri = JFactory::getURI();
		$url = $uri->root();
		$document->addScriptDeclaration('var getCurrentLocation = function() {return "' . $url . '"};');
	}
	public static function loadModJs($name, $app = 'default') {
		$path = "components/com_ose_cpu/extjs4/mod/{$name}.js";
		oseHTML::matchScript($path, $app);
	}
	public static function loadFieldJs($name, $app = 'default') {
		$path = "components/com_ose_cpu/extjs4/field/{$name}.js";
		oseHTML::matchScript($path, $app);
	}
}
