<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
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
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
defined('_JEXEC') or die(";)");
abstract class abstractOseMember {
	/*
	 * Get general membership info of specific member. If no msc id, will return the first result
	 */
	abstract public function getMemberInfo($msc_id = 0, $type = 'array');
	/*
	 * Billing Info in the Old Version
	 */
	abstract public function getCompanyInfo($type = 'array');
	/*
	 * Get the amount of membership member owned
	 */
	abstract public function hasOwnMsc();
	abstract public function joinMsc($msc_id);
	abstract public function cancelMsc($msc_id);
	/*
	 * Check whether the user has membership
	 * @$msc_id
	 */
	abstract public function isMember($msc_id = 0);
	/*
	 * For telling the Member Status, if true, return member info. else return false;
	 * @$msc_id
	 * @$isActive mem.Status
	 * @$where special Clause
	 */
	abstract public function isSpecificMember($msc_id, $isActive = '-1', $where = array());
	/*
	 * For some special situation to prove have absoulute member function
	 */
	abstract public function hasMemberAuthority($msc_id);
}
require_once(dirname(__FILE__) . DS . 'member_j15.php');
require_once(dirname(__FILE__) . DS . 'member_j16.php');
class oseMember extends abstractOseMember {
	protected $member_id = null;
	function __construct() {
	}
	function __toString() {
		return get_class($this);
	}
	public function getInstance($type) {
		$className = "oseMem{$type}";
		if (class_exists($className)) {
			static $instance;
			if (!$instance instanceof $className) {
				$instance = new $className();
			}
			return $instance;
		} else {
			oseExit('Can Not Get the Instance of OSEFILE');
		}
	}
	function __call($name, $args) {
		if (isset($this->task[$name])) {
			return call_user_func_array(array($this, $this->task[$name]), $args);
		} else {
			oseExit($name . ' Error');
		}
	}
	public function getInstanceByVersion($params = array()) {
		static $instance;
		if (!empty($instance)) {
			return $instance;
		}
		jimport('joomla.version');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		if ($version == '1.5') {
			require_once(dirname(__FILE__) . DS . 'member_j15.php');
			$className = get_class($this) . '_J15';
			$instance = new $className($params);
		} elseif ($version == '1.6') {
			require_once(dirname(__FILE__) . DS . 'member_j16.php');
			$className = get_class($this) . '_J16';
			$instance = new $className($params);
		} else {
			require_once(dirname(__FILE__) . DS . 'member_j17.php');
			$className = get_class($this) . '_J17';
			$instance = new $className($params);
		}
		return $instance;
	}
	function getMscGlobalConfig($type = 'array') {
		$msc = oseRegistry::call('msc');
		return $msc->getConfig($type);
	}
	public function getMemberInfo($msc_id = 0, $type = 'array') {
		return null;
	}
	public function getCompanyInfo($type = 'array') {
		return null;
	}
	public function hasOwnMsc() {
		return null;
	}
	public function joinMsc($msc_id) {
		return null;
	}
	public function cancelMsc($msc_id) {
		return null;
	}
	/*
	 * Check whether the user has membership
	 * @$msc_id
	*/
	public function isMember($msc_id = 0) {
		return null;
	}
	/*
	 * For telling the Member Status, if true, return member info. else return false;
	 * @$msc_id
	 * @$isActive mem.Status
	 * @$where special Clause
	 */
	public function isSpecificMember($msc_id, $isActive = '-1', $where = array()) {
		return null;
	}
	public function hasMemberAuthority($msc_id) {
		return null;
	}
}
