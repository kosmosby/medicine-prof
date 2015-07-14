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
class osemscControllerMemberships extends osemscController {
	function __construct() {
		parent::__construct();
		$this->registerTask('subscribe', 'addToCart');
		$this->registerTask('toPaymentPage', 'addToCart');
	}
	function getMemberships() {
		$model = $this->getModel('memberships');
		$memberships = $model->getMemberships();
		$result = array();
		$total = count($memberships);
		if ($total > 0) {
			$result['total'] = $total;
			$result['results'] = $memberships;
		} else {
			$result['total'] = $total;
			$result['results'] = '';
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getList() {
		$model = $this->getModel('memberships');
		$tree = $model->getFullTree();
		$total = count($tree);
		if ($total > 0) {
			$result['total'] = count($tree);
			$result['results'] = $tree;
		} else {
			$result['total'] = 0;
			$result['results'] = null;
		}
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function subscribe() {
		$user = JFactory::getUser();
		$return = JRequest::getVar('return', "");
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', 0);
		$payment_mode = JRequest::getWord('payment_mode', 'a');
		if (!empty($msc_id)) {
			$session = &JFactory::getSession();
			$oseMscPayment = array();
			$oseMscPayment['msc_id'] = $msc_id;
			$oseMscPayment['payment_mode'] = $payment_mode;
			$oseMscPayment['msc_option'] = $msc_option;
			$session->set('oseMscPayment', $oseMscPayment);
		}
		$result = array();
		$result['success'] = true;
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function viewMscList() {
		parent::display();
		exit;
	}
	public static function getPaymentMode() {
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc = oseRegistry::call('msc');
		$ext = $msc->getExtInfo($msc_id, 'payment', 'obj');
		$items = array();
		if ($ext->payment_mode == 'a') {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('Automatic Renewing'));
		} elseif ($ext->payment_mode == 'm') {
			$items[] = array('id' => 1, 'value' => 'm', 'text' => JText::_('Manual Renewing'));
		} else {
			$items[] = array('id' => 1, 'value' => 'a', 'text' => JText::_('Automatic Renewing'));
			$items[] = array('id' => 2, 'value' => 'm', 'text' => JText::_('Manual Renewing'));
		}
		$result = array();
		if (empty($items)) {
			$result['total'] = 0;
			$result['results'] = array();
		} else {
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		oseExit(oseJson::encode($result));
	}
	function addToCart() {
		$cart = oseRegistry::Call('payment')->getInstance('Cart');
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', null);
		$item = array('entry_id' => $msc_id, 'entry_type' => 'msc', 'msc_option' => $msc_option);
		$cart->addItem($item['entry_id'], $item['entry_type'], $item);
		$items = $cart->get('items');
		$cart->setCartItems($items, 'payment_mode', 'm');
		$cart->update();
		$session = JFactory::getSession();
		$session->set('ose_reg_step', 'cart');
		$result = array();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = JText::_('Added to Cart') . '!';
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function getMembershipCard() {
		$msc_id = JRequest::getInt('msc_id', 0);
		$model = $this->getModel('memberships');
		$cards = $model->getMembershipCard($msc_id);
		oseExit($cards[0]);
	}
}
