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
class osemscModelMemberships extends oseMscModel {
	function __construct() {
		parent::__construct();
	}
	function getFullTree() {
		$msc = oseRegistry::call('msc');
		$items = $msc->retrieveTree();
		foreach ($items as $key => $item) {
			if (!empty($item['image'])) {
				$items[$key]['image'] = JURI::root() . $item['image'];
			}
		}
		return $items;
	}
	function getMemberships() {
		$items = oseMscTree::getSubTreeDepth(0, 0, 'obj');
		$filter = array();
		$mainframe = JFactory::getApplication();
		$params = clone ($mainframe->getParams('com_osemsc'));
		$mscIDs = $params->get('msc_ids');
		if (empty($mscIDs)) {
			$config = oseRegistry::call('msc')->getConfig('register', 'obj');
			$show_default_memberships = oseObject::getValue($config, 'default_memberships', 'all');
			if ($show_default_memberships == 'none') {
				return array();
			} elseif ($show_default_memberships == 'all') {
				foreach ($items as $key => $item) {
					if ($item->published)
						$filter[$key] = $item;
				}
				return $filter;
			} else {
				$menu = JSite::getMenu();
				$menuParams = $menu->getParams('55');//->get('msc_ids');
				$mscIDs = $menuParams->get('msc_ids');
			}
		}
		if (!is_array($mscIDs)) {
			$mscIDs = array($mscIDs);
		}
		if (!empty($mscIDs)) {
			foreach ($items as $key => $item) {
				if ($item->published && in_array($item->id, $mscIDs)) {
					$filter[$key] = $item;
				}
			}
		} else {
			foreach ($items as $key => $item) {
				if ($item->published) {
					$filter[$key] = $item;
				}
			}
		}
		return $filter;
	}
	function drawMscList($osePaymentCurrency, $items, $type) {
		$cards = array();
		foreach ($items as $item) {
			$msc_id = $item->id;
			switch ($type) {
			case ('os'):
				$obj = $this->drawMscListOS($msc_id, $osePaymentCurrency);
				break;
			case ('cart'):
				$obj = $this->drawMscListCart($msc_id, $osePaymentCurrency);
				break;
			}
			$cards[] = $obj;
		}
		return $cards;
	}
	private function drawMscListOS($msc_id, $osePaymentCurrency) {
		$buildList = oseRegistry::call('payment')->getInstance('MscList');
		$buildList->set('currency', $osePaymentCurrency);
		$tree = oseMscTree::retrieveTree($msc_id, 'obj');
		$first = $tree[0];
		unset($tree[0]);
		if (isset($first->image)) {
			$root = trim(JURI::root(), "/");
			$first->image = $root . $first->image;
		}
		if (isset($first->description)) {
			$first->description = str_replace('src="../', "src=\"" . JURI::root(), $first->description);
		}
		if (count($tree) > 0) {
			$firstNode = $buildList->drawParent($first);
			$firstNode = $buildList->drawFirst($firstNode);
			$subTree = $buildList->drawTree($first->id);
			$subTree = $buildList->drawSecond($subTree);
			$row = $this->drawRow(array($firstNode, $subTree));
		} else {
			$firstNode = $buildList->drawLeaf($first);
			$msc = oseRegistry::call('msc');
			$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
			$paymentAdvInfos = $msc->getExtInfo($msc_id, 'paymentAdv');
			$options = oseMscPublic::generatePriceOption($first, $paymentInfos, $osePaymentCurrency);
			if (!empty($options)) {
				$option = array();
				foreach ($options as $obj) {
					$visible = $this->checkVisibility($msc_id, $obj['id'], $paymentAdvInfos);
					if ($visible == true) {
						$oAttr = array();
						$oAttr['option.value'] = 'value';
						$oAttr['option.attr'] = 'attr';
						$attrStr = 'id="%s" title="%s" trial_price="%s" trial_recurrence="%s" standard_price="%s" standard_recurrence="%s" has_trial="%s"';
						$oAttr['attr'] = sprintf($attrStr, $obj['id'], $obj['title'], oseObject::getValue($obj, 'trial_price', 0),
								oseObject::getValue($obj, 'trial_recurrence', 0), $obj['standard_price'], $obj['standard_recurrence'], oseObject::getValue($obj, 'has_trial', 0));
						$o = oseHTML::getInstance('Select')->option(oseObject::getValue($obj, 'id'), oseObject::getValue($obj, 'title'), $oAttr);
						$option[] = $o;
					}
				}
				$basic = $options[0];
				$listAttr = array();
				$listAttr['list.attr'] = ' class="msc_options"  size="1" style="width:200px"';
				$listAttr['list.translate'] = false;
				$listAttr['option.key'] = 'value';
				$listAttr['id'] = 'msc_option_' . oseObject::getValue($first, 'id');
				$listAttr['option.text'] = 'text';
				$listAttr['list.select'] = oseObject::getValue($basic, 'id');
				$listAttr['option.attr'] = 'attr';
				$combo = oseHTML::getInstance('Select')->genericlist($option, 'msc_option', $listAttr);
				$firstNode['price'] = "<div class='msc-price-box'><span>" . JText::_('Options') . ":</span>" . $buildList->drawPrice($combo . '<div></div>') . '</div>';
			}
			$firstNode = $buildList->drawFirst($firstNode);
			$row = $buildList->drawRow($firstNode);
		}
		$image = $buildList->getImage($first);
		$image = $buildList->drawImage($image);
		if (!empty($option)) {
			return $buildList->drawCard(array($image, $row));
		} else {
			return false;
		}
	}
	function checkVisibility($msc_id, $objID, $paymentAdvInfos) {
		if (isset($paymentAdvInfos[$objID])) {
			$mscids = $this->getUserMsc();
			switch ($paymentAdvInfos[$objID]['option_visibility']) {
			case 1:
				if (!empty($mscids)) {
					if ($paymentAdvInfos[$objID]['nosamemembership']) {
						if (in_array($msc_id, $mscids)) {
							return false;
						} else {
							return true;
						}
					} else {
						return true;
					}
				} else {
					return false;
				}
				break;
			case 0:
				return true;
				break;
			case -1:
				if (!empty($mscids)) {
					return false;
				} else {
					return true;
				}
				break;
			}
		} else {
			return true;
		}
	}
	function getUserMsc() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$query = " SELECT `msc_id` FROM `#__osemsc_member` " . " WHERE `member_id` = " . (int) $user->id . " AND status = 1 ";
		$db->setQuery($query);
		$results = $db->loadResultArray();
		return $results;
	}
	private function drawMscListCart($msc_id, $osePaymentCurrency) {
		$buildList = oseRegistry::call('payment')->getInstance('MscList');
		$buildList->set('currency', $osePaymentCurrency);
		$tree = oseMscTree::retrieveTree($msc_id, 'obj');
		$first = $tree[0];
		unset($tree[0]);
		if (count($tree) > 0) {
			$firstNode = $buildList->drawParent($first);
			$firstNode = $buildList->drawFirst($firstNode);
			$subTree = $buildList->drawTree($first->id);
			$subTree = $buildList->drawSecond($subTree);
			$row = $this->drawRow(array($firstNode, $subTree));
		} else {
			$firstNode = $buildList->drawLeaf($first);
			$msc = oseRegistry::call('msc');
			$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
			$options = oseMscPublic::generatePriceOption($first, $paymentInfos, $osePaymentCurrency);
			if (!empty($options)) {
				$option = array();
				foreach ($options as $obj) {
					$oAttr = array();
					$oAttr['option.value'] = 'value';
					$oAttr['option.attr'] = 'attr';
					$attrStr = 'title="%s" trial_price="%s" trial_recurrence="%s" standard_price="%s" standard_recurrence="%s" has_trial="%s"';
					$oAttr['attr'] = sprintf($attrStr, $obj['title'], oseObject::getValue($obj, 'trial_price', 0), oseObject::getValue($obj, 'trial_recurrence', 0),
							$obj['standard_price'], $obj['standard_recurrence'], oseObject::getValue($obj, 'has_trial', 0));
					$o = oseHTML::getInstance('Select')->option(oseObject::getValue($obj, 'id'), oseObject::getValue($obj, 'title'), $oAttr);
					$option[] = $o;
				}
				$basic = $options[0];
				$listAttr = array();
				$listAttr['list.attr'] = ' class="msc_options"  size="1" style="width:200px"';
				$listAttr['list.translate'] = false;
				$listAttr['option.key'] = 'value';
				$listAttr['id'] = 'msc_option_' . oseObject::getValue($first, 'id');
				$listAttr['option.text'] = 'text';
				$listAttr['list.select'] = oseObject::getValue($obj, 'id');
				$listAttr['option.attr'] = 'attr';
				$combo = oseHTML::getInstance('Select')->genericlist($option, 'msc_option', $listAttr);
				$firstNode['price'] = "<div class='msc-price-box'><span>" . JText::_('Options') . ":</span>" . $buildList->drawPrice($combo . '<div></div>') . '</div>';
			}
			$hackback = JText::_('Subscribe');
			$needle = JText::_('Add');
			if (isset($firstNode['button'])) {
				$firstNode['button'] = preg_replace("/{$hackback}/", $needle, $firstNode['button'], 1);
			}
			$firstNode = $buildList->drawFirst($firstNode);
			$row = $buildList->drawRow($firstNode);
		}
		$image = $buildList->getImage($first);
		$image = $buildList->drawImage($image);
		return $buildList->drawCard(array($image, $row));
	}
	function getMembershipCard($msc_id) {
		$item = oseRegistry::call('msc')->getInfo($msc_id, 'obj');
		$cart = oseMscPublic::getCart();
		$osePaymentCurrency = $cart->get('currency');
		$items = array($item);
		$register_form = oseRegistry::call('msc')->getConfig('register', 'obj')->register_form;
		if (empty($register_form) || $register_form == 'default') {
			$cards = $this->drawMscList($osePaymentCurrency, $items, 'cart');
		} else {
			$cards = $this->drawMscList($osePaymentCurrency, $items, 'os');
		}
		return $cards;
	}
}
?>