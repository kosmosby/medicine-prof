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
class oseMscListDraw {
	function set($name, $value) {
		$this->{$name} = $value;
	}
	function drawDiv($class_name, $id_name = null) {
		if (empty($id_name)) {
			$div = '<div class="%s">%%s</div>';
			$div = sprintf($div, $class_name);
		} else {
			if ($class_name == 'msc-sub') {
				$div = '<div class="%s" id="%s">%%s</div><div class="clr"></div>';
			} else {
				$div = '<div class="%s" id="%s">%%s</div>';
			}
			$div = sprintf($div, $class_name, $id_name);
		}
		return $div;
	}
	function drawCard($content) {
		if (is_array($content)) {
			$content = implode("\r\n", $content);
		}
		$card = self::drawDiv('msc-card');
		$card = sprintf($card, "\r\n" . $content . "\r\n");
		return $card;
	}
	function drawIntro($content) {
		$html = self::drawDiv('msc-desc');
		$content = explode('{readmore}', $content);
		$html = sprintf($html, $content[0]);
		return $html;
	}
	function drawDesc($content) {
		$content = str_replace('{readmore}', ' ', $content);
		$html = self::drawDiv('msc-desc');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawImage($content) {
		$html = self::drawDiv('msc-image');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawFirst($content) {
		if (is_array($content)) {
			$content = implode("\r\n", $content);
		}
		$title = self::drawDiv('msc-first');
		$title = sprintf($title, $content);
		return $title;
	}
	function drawFirstTitle($content, $isLeaf = true) {
		if ($isLeaf) {
			$html = self::drawDiv('msc-first-title-leaf');
			$html = sprintf($html, $content);
		} else {
			$html = self::drawDiv('msc-first-title');
			$html = sprintf($html, $content);
		}
		return $html;
	}
	function drawFirstImage($content) {
		$html = self::drawDiv('msc-first-image');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawSecond($content) {
		if (is_array($content)) {
			$content = implode("\r\n", $content);
		}
		$html = '<div class="msc-second">%s</div>';
		$html = sprintf($html, $content);
		return $html;
	}
	function drawSub($content) {
		$html = self::drawDiv('msc-sub');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawSubTitle($content) {
		$html = self::drawDiv('msc-sub-title');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawPrice($content, $type = null) {
		switch ($type) {
		case ('m'):
			$html = '<ul class="msc-price-m"><li>%s</li></ul>';
			$html = sprintf($html, $content);
			break;
		case ('a'):
			$html = '<ul class="msc-price-a"><li>%s</li></ul>';
			$html = sprintf($html, $content);
			break;
		default:
			$html = '<div class="msc-price">%s</div>';
			$html = sprintf($html, $content);
			break;
		}
		return $html;
	}
	function drawRecurrence($content) {
		if (is_array($content)) {
			$content = implode("\r\n", $content);
		}
		$html = '<div class="msc-period">%s</div>';
		$html = sprintf($html, $content);
		return $html;
	}
	function drawRow($content) {
		if (is_array($content)) {
			$content = implode("\r\n", $content);
		}
		$html = self::drawDiv('msc-row');
		$html = sprintf($html, $content);
		return $html;
	}
	function drawTree($msc_id) {
		return true;
	}
}
class oseMscList extends oseMscListDraw {
	function drawMscList($msc_id) {
		$tree = oseMscTree::retrieveTree($msc_id, 'obj');
		$first = $tree[0];
		unset($tree[0]);
		if (count($tree) > 0) {
			$firstNode = $this->drawParent($first);
			$firstNode = $this->drawFirst($firstNode);
			$subTree = $this->drawTree($first->id);
			$subTree = $this->drawSecond($subTree);
			$row = $this->drawRow(array($firstNode, $subTree));
		} else {
			$firstNode = $this->drawLeaf($first);
			$firstNode = $this->drawFirst($firstNode);
			$row = $this->drawRow($firstNode);
		}
		$image = $this->getImage($first);
		$image = $this->drawImage($image);
		return $this->drawCard(array($image, $row));
	}
	function drawParent($node) {
		return '';
	}
	function drawLeaf($node) {
		return '';
	}
	function drawTree($msc_id) {
		$html = array();
		$tree = oseMscTree::getSubTreeDepth($msc_id, 0, 'obj');
		foreach ($tree as $nKey => $node) {
			if ($node->leaf) {
				$leaf = $this->drawSubLeaf($node);
				$html[] = $this->drawSub($leaf);
			} else {
				$subTitle = $this->drawSubTitle('|__' . oseObject::getValue($node, 'title'));
				$iterate = $this->drawTree(oseObject::getValue($node, 'id'));
				$iterate = implode("\r\n", $iterate);
				$html[] = $this->drawSub($subTitle . "\r\n" . $iterate);
			}
		}
		return $html;
	}
	function drawSubLeaf($node) {
		return;
	}
	function generateFirstMsc($msc) {
		return '';
	}
	function generateSubMsc($node) {
		return '';
	}
	function getTitle($node, $first = true) {
		$title = oseObject::getValue($node, 'title');
		return $title;
	}
	function getImage($node) {
		$img_path = oseObject::getValue($node, 'image');
		$frontURL = OSEMSC_F_URL;
		$root = JURI::root();
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			if (!strstr($frontURL, "https")) {
				$frontURL = str_replace("http", "https", $frontURL);
			}
			if (!strstr($img_path, "https")) {
				$img_path = str_replace("http", "https", $img_path);
			}
		}
		if (empty($img_path)) {
			$img_path = $frontURL . '/assets/images/defaultMscImage.png';
		} else {
			$img_path = $img_path;
		}
		$html = '<img src="%s">';
		$html = sprintf($html, $img_path);
		return $html;
	}
	function getStandardPrice($node) {
		return oseObject::getValue($node, 'standard_price');
	}
	function getTrialPrice($node) {
		return oseObject::getValue($node, 'trial_price');
	}
	function getRecurrence($node, $type) {
		return oseObject::getValue($node, $type . '_recurrence');
	}
	function getDesc($node) {
		return oseObject::getValue($node, 'description');
	}
	function getLevel($node) {
		return oseObject::getValue($node, 'level');
	}
}
?>
