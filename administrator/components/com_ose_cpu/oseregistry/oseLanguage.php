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
class oseLanguage extends JLanguage {
	protected $code = null;
	function __construct($lang = null, $debug = false) {
		parent::__construct($lang, $debug);
		$this->code = $this->getCode();
	}
	function getStrings() {
		return $this->strings;
	}
	function setCode($code) {
		$session = JFactory::getSession();
		$session->set('oseLang', $code);
	}
	static function getCode() {
		$session = JFactory::getSession();
		$code = $session->get('oseLang', 'en-GB');
		return $code;
	}
	function get($key, $default = null) {
		if (!isset($this->{$key})) {
			$this->{$key} = $default;
		}
		return $this->{$key};
	}
	function set($property, $value = null) {
		$this->{$property} = $value;
	}
	function update() {
		$session = JFactory::getSession();
		$lang = array();
		$session->set('oseLang', $this->code);
	}
}
?>