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

class oseMemGroup
{
	static function getMscMembers($msc_id,$where = null,$start = 0,$limit = 20,$type = 'array')
	{
		$db = oseDB::instance();

		$query = " SELECT mem.* "
				." FROM `#__osemsc_member_view` AS mem "
				//." INNER JOIN `#__oselic_cs_keys_view` AS luv ON luv.user_id = mem.member_id"
				. $where
				;
		//oseExit($query);

		if($limit > 0)
		{
			$db->setQuery($query,$start,$limit);
		}
		else
		{
			$db->setQuery($query);
		}

		//oseExit($db->_sql.$start.$limit);
		$items = oseDB::loadList($type);

		return $items;
	}

	function getUsers($where = null,$start = 0,$limit = 20,$type = 'array')
	{
		$db = oseDB::instance();

		$query = " SELECT u.username,u.name,u.email,u.id FROM `#__users` AS u"
				. $where
				;
		$db->setQuery($query,$start,$limit);

		$items = oseDB::loadList($type);
		//oseExit($db->_sql);
		return $items;
	}
	
	function getUsersTotal($where = null)
	{

		$db = oseDB::instance();

		$query = " SELECT COUNT(*) FROM `#__users` AS u"
				. $where
				;
		$db->setQuery($query);

		$items = $db->loadResult();
		//oseExit($db->_sql);
		return $items;
	}
/*
	function getUsersTotal($search = null)
	{

		$db = oseDB::instance();

		$where = array();

		if (!empty($search))
		{
			$where[] = $search;
		}

		$whereQuery = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) FROM `#__users` AS u ".$whereQuery;

		$db->setQuery($query);

		$totalUsers = $db->loadResult();


		$where[] = " u.id = m.member_id ";
		$where[] = " m.status = 1 ";
		$whereQuery = oseDB::implodeWhere($where);

		$query = "SELECT count(*) FROM `#__users` AS u , `#__osemsc_member` AS m ".$whereQuery;

		$db->setQuery($query);

		$totalMembers = $db->loadResult();

		$totalNonMembers = $totalUsers - $totalMembers;

		return $totalNonMembers;
	}
*/

	static function getGroupTotal($msc_id,$where = null)
	{
		$db = oseDB::instance();

		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_member_view` AS mem "
				//." INNER JOIN `#__oselic_cs_keys_view` AS luv ON luv.user_id = mem.member_id"
				. $where
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		return $db->loadResult();
	}
}
?>