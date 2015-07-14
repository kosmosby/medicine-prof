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
defined('_JEXEC') or die("Direct Access Not Allowed");
class oseMscControllerMember extends oseMscController {
	public function __construct() {
		parent::__construct();
	}
	public function getOwnMsc() {
		$model = $this->getModel('member');
		$items = $model->getOwnMsc();
		$total = count($items);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $items;
		} else {
			$result['total'] = 0;
			$result['results'] = null;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function isMaster() {
		$model = $this->getModel('member');
		$updated = $model->isMaster();
		if ($updated) {
			$result['success'] = true;
		} else {
			$result['success'] = false;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function getPaymentMode() {
		$model = $this->getModel('member');
		$msc_id = JRequest::getInt('msc_id', 0);
		$items = $model->getPaymentMode($msc_id);
		$total = count($items);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $items;
		} else {
			$result['total'] = 0;
			$result['results'] = array();
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function getAddons() {
		$model = $this->getModel('member');
		$addon_type = JRequest::getCmd('addon_type', null);
		$items = $model->getAddons($addon_type);
		$total = count($items);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $items;
		} else {
			$result['total'] = 0;
			$result['results'] = array();
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function getMod() {
		$result = array();
		$addon_name = JRequest::getCmd('addon_name', null);
		$type = JRequest::getCmd('addon_type', null);
		echo '<script type="text/javascript">' . "\r\n";
		require_once(JPATH_SITE . DS . oseMscMethods::getJsModPath($addon_name, $type));
		echo "\r\n" . '</script>';
		oseExit();
	}
	public function getAddon() {
		$result = array();
		$addon_name = JRequest::getCmd('addon_name', null);
		$type = JRequest::getCmd('addon_type', null);
		$type = 'member';
		echo '<script type="text/javascript">' . "\r\n";
		require_once(JPATH_SITE . DS . oseMscMethods::getAddonPath($addon_name . '.js', $type));
		echo "\r\n" . '</script>';
		oseExit();
	}
	public function uniqueUserName() {
		$username = JRequest::getString('username', null);
		$model = $this->getModel('member');
		$isValid = $model->uniqueUserName($username);
		$result = array();
		if ($isValid) {
			$result['result'] = $isValid;
		} else {
			$result['result'] = JText::_('This username has been registered by other user.');
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function getExtraJs() {
		$result = array();
		$addon_name = JRequest::getCmd('addon_name', null);
		$type = JRequest::getCmd('addon_type', null);
		echo '<script type="text/javascript">' . "\r\n";
		require_once(OSEMSC_F_VIEW . DS . $type . DS . 'js' . DS . "js.{$type}.{$addon_name}.js");
		echo "\r\n" . '</script>';
		oseExit();
	}
	public function toPayment() {
		$payment_mode = JRequest::getString('payment_mode', 'm');
		$msc_id = JRequest::getInt('msc_id', 0);
		$payment_method = JRequest::getString('payment_method', 'm');
		$result = array();
		if (empty($payment_method)) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('No Payment Method');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		if (empty($payment_mode)) {
			$result['success'] = false;
			$result['title'] = JText::_('Error!');
			$result['content'] = JText::_('Have Not Selected Payment Method');
			$result = oseJson::encode($result);
			oseExit($result);
		}
		if (!empty($msc_id)) {
			$session = JFactory::getSession();
			$oseMscPayment = array();
			$oseMscPayment['msc_id'] = $msc_id;
			$oseMscPayment['payment_mode'] = $payment_mode;
			$session->set('oseMscPayment', $oseMscPayment);
		}
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_('Done');
		$result['link'] = JRoute::_('index.php?option=com_osemsc&view=payment');
		$result = oseJson::encode($result);
		oseExit($result);
	}
	public function getUserInfo() {
		$user = JFactory::getUser();
		$userInfo = oseMscPublic::getUserInfo($user->id);
		$result = array();
		$result['total'] = 1;
		$result['result'] = $userInfo;
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
?>