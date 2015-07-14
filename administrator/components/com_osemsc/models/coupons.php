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

class oseMscModelCoupons extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function

	function add($title)
	{
		$db = oseDB::instance();
		$params = array('range'=>'all');
		$params = $db->Quote(oseJson::encode($params));
		$query = " INSERT INTO `#__osemsc_coupon` (`title`,`params`) VALUES ('{$title}',{$params})";
		$db->setQuery($query);
		return oseDB::query();
	}

	function update($id,$params = array())
	{
		$db = oseDB::instance();

		foreach($params as $key => $param)
		{
			$set[] = "`{$key}`=".$db->Quote($param);
		}

		$set = implode(',',$set);

		$query = " UPDATE `#__osemsc_coupon` "
				." SET {$set}"
				." WHERE id = '{$id}'"
				;
		$db->setQuery($query);
		return oseDB::query();
	}

	function remove($id)
	{
		$db = oseDB::instance();

		$query = " DELETE FROM `#__osemsc_coupon` "
				." WHERE id = '{$id}'"
				;
		$db->setQuery($query);
		if (oseDB::query())
		{
			$query = " DELETE FROM `#__osemsc_coupon_user` "
				." WHERE coupon_id = '{$id}'"
				;
			$db->setQuery($query);
			return oseDB::query();
		}
		else
		{
			return false;
		}
	}

	function getInfo($id)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_coupon` "
				."WHERE `id` = ". $id
				;
		$db->setQuery($query);
		$item = oseDB::loadItem();
		$item['params'] = oseJson::decode($item['params']);

		$data = oseJson::decode($item['data'],true);

		if(empty($data))
		{
			$data = array();
		}
		//$item['data'] = $this->generateTable($data);

		$where = array();
		$where[] = "c.`coupon_id` = ". (int)$id;

		$where = oseDB::implodeWhere($where);

		$query = " SELECT Count(*) FROM `#__osemsc_coupon_user` AS c WHERE c.`paid` = '1' AND c.`coupon_id` = ". (int) $id;

		$db->setQuery($query);
		$used = $db->loadResult();
		$item['amount_left'] = ($item['amount'] >= $used)?$item['amount'] - $used:0;

		return $item;
	}

	function getUsersTable($id)
	{
		$db = oseDB::instance();

		$where = array();
		$where[] = "c.`coupon_id` = '{$id}'";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT c.* FROM `#__osemsc_coupon_user` AS c "
				//." INNER JOIN `#__users` AS u ON u.id = c.user_id"
				. $where
				;

		$db->setQuery($query);
		//oseExit($db->_sql);
		$items = oseDB::loadList();
		//oseExit($items);
		foreach($items as $key => $item)
		{
			$user_id = oseObject::getValue($item,'user_id');

			$query = " SELECT * FROM `#__users`"
					." WHERE `id` = '{$user_id}'"
					;
			$db->setQuery($query);

			$obj = oseDB::loadItem('obj');

			$item = oseObject::setValue($item,'username',oseObject::getValue($obj,'username'));
			$items[$key] = $item;
		}

		$html = $this->generateTable($items);

		return $html;
	}

	private function generateTable($items)
	{
		$table = array();
		$tHeader = array();
		$tBody = array();

		$tHeader[] = '<thead>';
		$tHeader[] = '<tr>';
		$tHeader[] = '<th>User ID</th>';
		$tHeader[] = '<th>User Name</th>';
		$tHeader[] = '<th>Paid</th>';
		$tHeader[] = '</tr>';
		$tHeader[] = '</thead>';

		$tBody[] = '<tbody>';
		foreach($items as $key => $item)
		{
			$paid = oseObject::getvalue($item,'paid');
			$paid = ($paid)?'Yes':'No';
			$userid = oseObject::getvalue($item,'user_id');
			$userid = (!empty($userid))?$userid:"n/a";
			$username = oseObject::getvalue($item,'username');
			$username = (!empty($username))?$username:JText::_("Guest");
			$tBody[] = '<tr>';
			$tBody[] = '<td>'.$userid.'</td>';
			$tBody[] = '<td>'.$username.'</td>';
			$tBody[] = '<td>'.$paid.'</td>';
			$tBody[] = '</tr>';
		}
		$tBody[] = '</tbody>';

		$table[] = '<table width="100%">';
		$table[] = implode("\r\n",$tHeader);
		$table[] = implode("\r\n",$tBody);
		$table[] = '</table>';

		$html = implode("\r\n",$table);

		return $html;
	}
}