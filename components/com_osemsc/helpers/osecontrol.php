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
class OSEcontrol
{
	var $art_ids = array();
	var $user_art_ids = array();
	var $edit_art_ids = array();
	var $view_art_ids = array();
	function OSEcontrol() {
		$this->_user = &JFactory::getUser();
		$this->_db = &JFactory::getDBO();
	}
	function oseWhere() {
		$auth = '';
		$osewhere = '';
		$auth = $this->check_auth();
		switch ($auth) {
		case ('admin'):
			$osewhere = '';
			break;
		case ('guest'):
			$this->getAllRestrictedArticles();
			$this->get_view_articles($this->process_view());
			$result = $this->filter_guest_art_id($this->art_ids, $this->view_art_ids);
			$art_ids = implode('\', \'', $result);
			$osewhere = ' AND a.id NOT IN ( \'' . $art_ids . '\' )';
			break;
		case ('user'):
			$this->getAllRestrictedArticles();
			$this->getUserRestrictedArticles();
			$this->get_view_articles($this->process_view());
			$result = $this->filter_user_art_id($this->user_art_ids, $this->art_ids, $this->
							view_art_ids);
			$art_ids = implode('\', \'', $result);
			$osewhere = ' AND a.id NOT IN ( \'' . $art_ids . '\' )';
			break;
		}
		return $osewhere;
	}
	//********************************************************************
	//
	//
	//
	//********************************************************************
	function fetch_art_id($art_id) {
		$this->art_ids[] = $art_id;
	}
	function fetch_user_art_id($art_id) {
		$this->user_art_ids[] = $art_id;
	}
	function fetch_view_art_id($art_id) {
		$this->view_art_ids[] = $art_id;
	}
	function fetch_edit_art_id($art_id) {
		$this->edit_art_ids[] = $art_id;
	}
	function unique_art_ids($var_name) {
		if (!empty($this->{$var_name})) {
			$this->{$var_name} = array_unique($this->{$var_name});
		}
	}
	//********************************************************************
	//
	//
	//
	//********************************************************************
	function check_auth() {
		if ($this->_user->get('gid') == 24 || $this->_user->get('gid') == 25) {
			return 'admin';
		} elseif ($this->_user->guest) {
			return 'guest';
		}
		else {
			return 'user';
		}
	}
	function process_view() {
		$view = JRequest::getVar('view');
		$array = array('section' => 'sec', 'category' => 'cat', 'article' => 'art', 'frontpage' => 'fro');
		$content_type = $array[$view];
		return $content_type;
	}
	//********************************************************************
	//
	//
	//
	//********************************************************************
	function getAllRestrictedArticles() {
		$query = "SELECT DISTINCT a.content_id, a.content_type FROM `#__osemsc_content_basic` as a "
				. " LEFT JOIN `#__osemsc_acl` as b ON a.msc_id = b.id "
				. " LEFT JOIN `#__osemsc_member` as c ON a.msc_id = c.msc_id "
				. " WHERE a.content_type != 'men' AND a.content_type != 'mod' AND b.r = 1 and a.content_id != 0 "
				. " ORDER BY a.content_type";
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		$this->tran2arts($results, false);
		$this->unique_art_ids('art_ids');
	}
	function getUserRestrictedArticles() {
		$query = "SELECT DISTINCT a.msc_id, a.content_id, a.content_type FROM `#__osemsc_content_basic` as a "
				. " LEFT JOIN `#__osemsc_acl` as b ON a.msc_id = b.id "
				. " LEFT JOIN `#__osemsc_member` as c  ON a.msc_id = c.msc_id "
				. " WHERE  a.content_type!='men' and a.content_type != 'mod' AND b.r=1 and c.member_id = {$this->_user->id} and a.content_id!=0 "
				. " ORDER BY a.content_type";
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		$this->tran2arts($results, true);
		$this->unique_art_ids('user_art_ids');
	}
	function tran2arts($array, $user_exist = true) {
		foreach ($array as $arr) {
			switch ($arr->content_type) {
			case "art":
				$this->sort_article($user_exist, $arr->content_id);
				break;
			case "cat":
				$query = "SELECT id FROM #__content WHERE catid = {$arr->content_id}";
				break;
			case "sec":
				$query = "SELECT id FROM #__content WHERE sectionid = {$arr->content_id}";
				break;
			}
			if (!empty($query)) {
				$this->_db->setQuery($query);
				$content_ids = $this->_db->loadObjectList();
				foreach ($content_ids as $content_id) {
					$this->sort_article($user_exist, $content_id->id);
				}
			}
		}
	}
	function sort_article($user_exist, $value) {
		if ($user_exist) {
			$this->fetch_user_art_id($value);
		}
		else {
			$this->fetch_art_id($value);
		}
	}
	function get_view_articles($view) {
		$id = JRequest::getVar('id', 0, '', 'int');
		switch ($view) {
		case ('sec'):
			$query = "SELECT id FROM #__content WHERE sectionid = {$id}";
			break;
		case ('cat'):
			$query = "SELECT id FROM #__content WHERE catid = {$id}";
			break;
		case ('art'):
			$query = "SELECT id FROM #__content WHERE id = {$id}";
			break;
		case "fro":
			$query = "SELECT content_id FROM #__content_frontpage";
			break;
		}
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		foreach ($results as $result) {
			if ($view != 'fro') {
				$this->fetch_view_art_id($result->id);
			}
			else {
				$this->fetch_view_art_id($result->content_id);
			}
		}
	}
	function filter_user_art_id($user_art_ids, $art_ids, $view_art_ids) {
		$diff = array_diff($art_ids, $user_art_ids);
		$result = array_intersect($view_art_ids, $diff);
		return $result;
	}
	function filter_guest_art_id($art_ids, $view_art_ids) {
		$result = array_intersect($art_ids, $view_art_ids);
		return $result;
	}
	function getUserEditArticles() {
		$results = '';
		$query = "SELECT DISTINCT a.msc_id, a.content_id, a.content_type FROM `#__osemsc_content_basic` as a " . "LEFT JOIN `#__osemsc_acl` as b ON a.msc_id = b.id "
				. "LEFT JOIN `#__osemsc_member` as c  ON a.msc_id = c.msc_id " . "WHERE " . "a.content_type != 'men' AND a.content_type != 'mod' AND a.content_id != 0 "
				. "AND b.u = 1 " . "AND c.member_id = {$this->_user->id} " . "ORDER BY a.content_type";
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		$this->edit_tran2arts($results);
		$this->unique_art_ids('edit_art_ids');
		$art_ids = implode('\', \'', $this->edit_art_ids);
		if ($this->_user->get('gid') == 19) {
			$osewhere = ' WHERE id IN ( \'' . $art_ids . '\' ) AND created_by=' . $this->_user->id . ' AND state=0 ';
		}
		elseif ($this->_user->get('gid') == 20) {
			$osewhere = ' WHERE id IN ( \'' . $art_ids . '\' ) AND state=0 ';
		}
		else {
			$osewhere = ' WHERE id IN ( \'' . $art_ids . '\' )';
		}
		$query = "SELECT id,created_by,state FROM `#__content` " . $osewhere;
		//echo $query;
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		return $results;
	}
	function edit_tran2arts($array) {
		foreach ($array as $arr) {
			switch ($arr->content_type) {
			case "art":
				$this->fetch_edit_art_id($arr->content_id);
				break;
			case "cat":
				$query = "SELECT id FROM #__content WHERE catid = {$arr->content_id}";
				break;
			case "sec":
				$query = "SELECT id FROM #__content WHERE sectionid = {$arr->content_id}";
				break;
			}
			if (!empty($query)) {
				$this->_db->setQuery($query);
				$content_ids = $this->_db->loadObjectList();
				foreach ($content_ids as $content_id) {
					$this->fetch_edit_art_id($content_id->id);
				}
			}
		}
	}
}
?>