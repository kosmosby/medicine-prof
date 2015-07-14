<?php
/** Delete later
 * @version     4.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence {@link 
http://www.opensource-excellence.co.uk}
 * @author        EasyJoomla {@link http://www.easy-joomla.org 
Easy-Joomla.org}
 * @author        SSRRN {@link http://www.ssrrn.com}
 * @author        Created on 15-Sep-2008
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
 *  @Copyright Copyright (C) 2010- ... author-name
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
// Component Helper
jimport('joomla.application.component.helper');
/**
 * Content Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class osemscHelperRoute
{
	/**
	
	 * @param	int	The route of the content item
	
	 */
	function getArticleRoute($id, $catid = 0, $sectionid = 0) {
		$needles = array('article' => (int) $id, 'category' => (int) $catid, 'section' => (int) $sectionid,
		);
		//Create the link
		$link = 'index.php?option=com_osemsc&view=article&id=' . $id;
		if ($catid) {
			$link .= '&catid=' . $catid;
		}
		if ($item = osemscHelperRoute::_findItem($needles)) {
			$link .= '&Itemid=' . $item->id;
		}
		;
		return $link;
	}
	function getSectionRoute($sectionid) {
		$needles = array('section' => (int) $sectionid
		);
		//Create the link
		$link = 'index.php?option=com_osemsc&view=section&id=' . $sectionid;
		if ($item = osemscHelperRoute::_findItem($needles)) {
			if (isset($item->query['layout'])) {
				$link .= '&layout=' . $item->query['layout'];
			}
			$link .= '&Itemid=' . $item->id;
		}
		;
		return $link;
	}
	function getCategoryRoute($catid, $sectionid) {
		$needles = array('category' => (int) $catid, 'section' => (int) $sectionid
		);
		//Create the link
		$link = 'index.php?option=com_osemsc&view=category&id=' . $catid;
		if ($item = osemscHelperRoute::_findItem($needles)) {
			if (isset($item->query['layout'])) {
				$link .= '&layout=' . $item->query['layout'];
			}
			$link .= '&Itemid=' . $item->id;
		}
		;
		return $link;
	}
	function _findItem($needles) {
		$component = &JComponentHelper::getComponent('com_content');
		$menus = &JApplication::getMenu('site', array());
		$items = $menus->getItems('componentid', $component->id);
		if (empty($items))
			return;
		$match = null;
		foreach ($needles as $needle => $id) {
			foreach ($items as $item) {
				if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
					$match = $item;
					break;
				}
			}
			if (isset($match)) {
				break;
			}
		}
		return $match;
	}
}
?>