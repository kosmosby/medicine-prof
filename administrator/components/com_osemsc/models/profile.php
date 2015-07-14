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
// no direct access
defined('_JEXEC') or die('Restricted access');
class oseMscModelProfile extends oseMscModel {
	var $_pagination= null;
	function __construct() {
		parent :: __construct();
	}
	function getList() {
		$db= JFactory :: getDBO();
		$query= "SELECT * FROM `#__osemsc_fields`";
		$db->setQuery($query);
		$list= $db->loadObjectList();
		$i = 0;
		$return = array ();
		foreach ($list as $item)
		{
			$item->published = ($item->published == true)? JText::_("Yes"): JText::_("No");
			$item->require = ($item->require == true)? JText::_("Yes"): JText::_("No");
			$return[$i] = $item;
			$i++;
		}
		return $return;
	}
	function save() {
		$db= JFactory :: getDBO();
		$post= JRequest :: get('post');
		$post['published']= empty($post['published']) ? 0 : 1;
		$post['require']= empty($post['require']) ? 0 : 1;
		$post['params']= empty($post['params']) ? null : $post['params'];
		$post['note']= empty($post['note']) ? null : $post['note'];
		if(empty($post['id'])) {
			$query= "SELECT max(ordering) FROM `#__osemsc_fields`";
			$db->setQuery($query);
			$Maxordering= $db->loadResult();
			$Newordering= $Maxordering +1;
			$query= " INSERT INTO `#__osemsc_fields`".
					" (`name`, `type`, `published`, `require`, `ordering`, `params`,`note`) VALUES ".
					" ('{$post['name']}', '{$post['type']}', '{$post['published']}', '{$post['require']}', '{$Newordering}', '{$post['params']}','{$post['note']}')";
		} else {
			self :: updateOrder($post['id'], $post['ordering']);
			$query= " UPDATE `#__osemsc_fields`".			" SET `name` = '{$post['name']}', `type` = '{$post['type']}', `published` = '{$post['published']}', `require` = '{$post['require']}', `ordering` = '{$post['ordering']}', `params` = '{$post['params']}', `note` = '{$post['note']}'".			" WHERE `id` = '{$post['id']}'";
		}
		$db->setQuery($query);
		if(!$db->query()) {
			return false;
		}
		if($post['type'] == 'fileuploadfield')
		{
			$path = $post['params'];
			if(!JFolder::exists(JPATH_SITE .DS. $path))
			{
				if(!JFolder::create(JPATH_SITE .DS. $path))
				{
					return false;
				}
			}
		}
		return true;
	}
	function getProfile() {
		$id= JRequest :: getInt('id', 0);
		$db= JFactory :: getDBO();
		$query= "SELECT * FROM `#__osemsc_fields` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		$profile= $db->loadObject();
		return $profile;
	}
	function remove() {
		$id= JRequest :: getInt('id', 0);
		$db= JFactory :: getDBO();
		$query= "SELECT ordering FROM `#__osemsc_fields` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		$order= $db->loadResult();
		$query= "DELETE FROM `#__osemsc_fields` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		if($db->query()) {
			$query= " SELECT * FROM `#__osemsc_fields` ".
					" WHERE `ordering` > '{$order}'";
			$db->setQuery($query);
			$objs= $db->loadObjectList();
			if(!empty($objs)) {
				foreach($objs as $obj) {
					$neworder= $obj->ordering - 1;
					$query= " UPDATE `#__osemsc_fields`".
							" SET `ordering` = '{$neworder}'".
							" WHERE `id` = '{$obj->id}'";
					$db->setQuery($query);
					if(!$db->query()) {
						return false;
					}
				}
			}
		}
		return true;
	}
	function getOrder() {
		$db= JFactory :: getDBO();
		$query= " SELECT ordering,CONCAT('(',ordering,')',name) AS displayText FROM `#__osemsc_fields`";
		$db->setQuery($query);
		$orders= $db->loadObjectList();
		return $orders;
	}
	function updateOrder($id, $neworder) {
		$db= JFactory :: getDBO();
		$query= "SELECT ordering FROM `#__osemsc_fields` WHERE `id` = '{$id}' ";
		$db->setQuery($query);
		$oldorder= $db->loadResult();
		if($oldorder == $neworder) {
			return true;
		}
		elseif($oldorder > $neworder) {
			$query= "SELECT * FROM `#__osemsc_fields` WHERE `ordering` >= '{$neworder}' AND `ordering` <= '{$oldorder}' AND `id` != '{$id}'";
			$db->setQuery($query);
			$orders= $db->loadObjectList();
		} else {
			$query= "SELECT * FROM `#__osemsc_fields` WHERE `ordering` >= '{$oldorder}' AND `ordering` <= '{$neworder}' AND `id` != '{$id}'";
			$db->setQuery($query);
			$orders= $db->loadObjectList();
		}
		foreach($orders as $order) {
			if($oldorder > $neworder) {
				$Neworder= $order->ordering + 1;
			} else {
				$Neworder= $order->ordering - 1;
			}
			$query= " UPDATE `#__osemsc_fields`".
					" SET `ordering` = '{$Neworder}'".
					" WHERE `id` = '{$order->id}'";
			$db->setQuery($query);
			if(!$db->query()) {
				return false;
			}
		}
		return true;
	}
	function getOptions() {
		$db= JFactory :: getDBO();
		$id= JRequest :: getInt('id', 0);
		//$type = JRequest::getVar('type',null);
		$query= "SELECT params FROM `#__osemsc_fields` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		$params= $db->loadResult();
		$params= explode(',', $params);
		foreach($params as $param) {
			$option['option']= $param;
			$options[]= $option;
		}
		return $options;
	}
}
?>