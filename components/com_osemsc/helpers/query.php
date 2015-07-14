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
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class osemscHelperQuery
{
	function orderbyPrimary($orderby) {
		switch ($orderby) {
		case 'alpha':
			$orderby = 'cc.title, ';
			break;
		case 'ralpha':
			$orderby = 'cc.title DESC, ';
			break;
		case 'order':
			$orderby = 'cc.ordering, ';
			break;
		default:
			$orderby = '';
			break;
		}
		return $orderby;
	}
	function orderbySecondary($orderby) {
		switch ($orderby) {
		case 'date':
			$orderby = 'a.created';
			break;
		case 'rdate':
			$orderby = 'a.created DESC';
			break;
		case 'alpha':
			$orderby = 'a.title';
			break;
		case 'ralpha':
			$orderby = 'a.title DESC';
			break;
		case 'hits':
			$orderby = 'a.hits DESC';
			break;
		case 'rhits':
			$orderby = 'a.hits';
			break;
		case 'order':
			$orderby = 'a.ordering';
			break;
		case 'author':
			$orderby = 'a.created_by_alias, u.name';
			break;
		case 'rauthor':
			$orderby = 'a.created_by_alias DESC, u.name DESC';
			break;
		case 'front':
			$orderby = 'f.ordering';
			break;
		default:
			$orderby = 'a.ordering';
			break;
		}
		return $orderby;
	}
	function buildVotingQuery($params = null) {
		if (!$params) {
			$params = &JComponentHelper::getParams('com_osemsc');
		}
		$voting = $params->get('show_vote');
		if ($voting) {
			// calculate voting count
			$select = ' , ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count';
			$join = ' LEFT JOIN #__content_rating AS v ON a.id = v.content_id';
		} else {
			$select = '';
			$join = '';
		}
		$results = array('select' => $select, 'join' => $join);
		return $results;
	}
}
?>