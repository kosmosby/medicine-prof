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
defined('_JEXEC') or die(";)");
class oseMscAddonActionContentJcontent extends oseMscAddon {
	public static function save($params) {}
	public static function delete($params) {}
	function getList() {
		$node= JRequest :: getString('node', null);
		switch(substr($node, 0, 4)) {
			case('sec-') :
				return $this->getCatList($node);
				break;
			case('cat-') :
				return $this->getArtList($node);
				break;
			case('art-') :
				return '';
				break;
			default :
				return $this->getSecList();
				break;
		}
	}
	function & getSecList() {
		$db= oseDB :: instance();
		$search= JRequest :: getString('search', null);
		$search= JString :: strtolower($search);
		$msc_id= JRequest :: getInt('msc_id', 0);
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		$where= array();
		$where=(count($where) ? ' WHERE ('.implode(') AND (', $where).')' : '');
		$query= "SELECT CONCAT('sec-',s.id) AS id,s.id AS secid, s.title FROM `#__sections` AS s ".$where." GROUP BY s.id";
		$db->setQuery($query);
		$rows= oseDB :: loadList('obj');
		$total= count($rows);
		foreach($rows as $item) {
			$obj= oseRegistry :: call('content')->getInstance('msc')->getItem('joomla', 'section', $item->secid, 'msc', $msc_id, null, 'obj');
			$controlled= empty($obj) ? 0 : $obj->status;
			if($controlled == '1') {
				$item->controlled= JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1') {
				$item->controlled= JText :: _('HIDE_TO_MEMBERS');
			} else {
				$item->controlled= JText :: _('SHOW_TO_ALL');
			}
			$item->iconCls= 'task-folder';
			$item->checked= false;
		}
		$result= array();
		$result['total']= $total;
		$result['results']= $rows;
		return $rows;
	}
	
	function & getCatList($node) 
	{
		$db= oseDB :: instance();
		
		//$sec_id= str_replace('sec-', '', $node);
		$search= JRequest :: getString('search', null);
		$search= JString :: strtolower($search);
		$msc_id= JRequest :: getInt('msc_id', 0);
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		
		
		$query = $db->getQuery(true);

		// Select the required fields from the table.

		$query->from('#__categories AS a');

		// Join over the language
		$query->select('a.id,a.title, a.level');
		//$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		//$query->select('uc.name AS editor');
		//$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		//$query->select('ag.title AS access_level');
		//$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		//$query->select('ua.name AS author_name');
		//$query->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');


		$query->where('a.published = 1' );
		$query->where('a.extension = '.$db->quote('com_content'));
		
		// Filter by search in title
		$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
		$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
		
		$query = $query->__toString(). 'ORDER BY a.lft ASC';
		$db->setQuery($query,$start,$limit);
		
		$rows = oseDB :: loadList('obj');
		$total = $this->getCatListTotal();
		
		foreach($rows as $item) 
		{
			$item->treename = str_repeat('<span class="gtr">|&mdash;</span>',$item->level-1).$item->title;
			$obj= oseRegistry :: call('content')->getInstance('msc')->getItem('joomla', 'category', $item->id, 'msc', $msc_id, null, 'obj');
			$controlled= empty($obj) ? 0 : $obj->status;
			if($controlled == '1') {
				$item->controlled= JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1') {
				$item->controlled= JText :: _('HIDE_TO_MEMBERS');
			} else {
				$item->controlled= JText :: _('SHOW_TO_ALL');
			}
			$item->iconCls= 'task-folder';
			
		}
		$result= array();
		$result['total']= $total;
		$result['results']= $rows;
		return $result;
	}
	
	function & getArtList() 
	{
		$db= oseDB :: instance();
		$cat_id = JRequest :: getInt('cat_id', 0);
		$search = JRequest :: getString('search', null);
		$search = JString :: strtolower($search);
		
		$msc_id= JRequest :: getInt('msc_id', 0);
		
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		
		$where= array();
		
		if($cat_id) {
			$where[]= 'c.catid = '.$cat_id;
		}
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT COUNT(*)"
				." FROM `#__content` AS c "
				. $where
				." ORDER BY c.title"
				;
		$db->setQuery($query);
		$total= $db->loadResult();
		
		$query = " SELECT c.id,c.title"
				." FROM `#__content` AS c "
				. $where
				." ORDER BY c.id"
				;
		$db->setQuery($query,$start,$limit);
		$rows= oseDB :: loadList('obj');
		
		foreach($rows as $item) 
		{
			$item->treename = $item->title;
			$obj= oseRegistry :: call('content')->getInstance('msc')->getItem('joomla', 'article', $item->id, 'msc', $msc_id, null, 'obj');
			$controlled= empty($obj) ? 0 : $obj->status;
			if($controlled == '1') {
				$item->controlled= JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1') {
				$item->controlled= JText :: _('HIDE_TO_MEMBERS');
			} else {
				$item->controlled= JText :: _('SHOW_TO_ALL');
			}
			$item->leaf= true;
			$item->iconCls= 'task';
			$item->checked= false;
			//$item->controlled = $controlled;
		}
		
		$result= array();
		$result['total']= $total;
		$result['results']= $rows;
		
		return $result;
	}
	function changeStatus() 
	{
		$db= oseDB :: instance();
		
		$msc_id= JRequest :: getInt('msc_id', 0);
		
		$content_ids= JRequest :: getVar('jc_ids', array());
		$timeLength= JRequest :: getVar('time_length', array());
		$timeUnit= JRequest :: getVar('time_unit', array());
		$content_type = JRequest :: getCmd('content_type', null);
		//oseExit($content_ids);
		$newStatus= JRequest :: getInt('status', 0);
		$result= array();
		$result['success']= true;
		$result['title']= JText::_('DONE');
		$result['content']= JText::_('SUCCESSFULLY');
		if(empty($content_ids)) {
			return $result;
		}
		foreach($content_ids as $key => $content_id) {
			//$node= explode('-', $content_id);
			//$content_type= $node[0];
			//$content_id= $node[1];
			switch($content_type) {
				case('sec') :
					$updated= $this->changeSecStatus($content_id, $msc_id, $newStatus);
					break;
				case('cat') :
					$ItemParams = array();
					$ItemParams['time_length'] = $timeLength[$key];
					$ItemParams['time_unit'] = 'week';//$timeUnit[$key];
					$updated= $this->changeCatStatus($content_id, $msc_id, $newStatus,$ItemParams);
					break;
				case('art') :
					$updated= $this->changeArtStatus($content_id, $msc_id, $newStatus);
					break;
				default :
					$updated= array('success' => true);
					break;
			}
		}
		if(!$updated['success']) {
			$result['success']= false;
			$result['title']= JText::_('ERROR');
			$result['content']= JText::_('ERROR');
		}
		return $result;
	}
	function changeArtStatus($content_id, $msc_id, $newStatus) {
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= 'Successfully';
		$db= oseDB :: instance();
		$content= oseRegistry :: call('content')->getInstance('msc');
		$item= $content->getItem('joomla', 'article', $content_id, 'msc', $msc_id, '', 'obj');
		if(empty($item)) {
			$updated= $content->insert('joomla', 'article', $content_id, 'msc', $msc_id, $newStatus);
			if(!$updated) {
				$result= array();
				$result['success']= false;
				$result['title']= JText::_('ERROR');;
				$result['content']= JText::_('ERROR');;
				return $result;
			}
		} else {
			$status= $item->status;
			//if($status != $newStatus) {
				$updated= $content->update($item, $newStatus);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');;
					$result['content']= JText::_('ERROR');;
					return $result;
				}
			//}
		}
		return $result;
	}
	
	function changeCatStatus($content_id, $msc_id, $newStatus,$params = array()) 
	{
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= 'Successfully';
		$db= oseDB :: instance();
		$content= oseRegistry :: call('content')->getInstance('msc');
		
		$where[] = "node.lft BETWEEN parent.lft AND parent.rgt";
		$where[] = "node.lft BETWEEN sub_parent.lft AND sub_parent.rgt";
		$where[] = "sub_parent.id = sub_tree.id";
		
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT node.*, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth"
				." FROM `#__categories` AS node,"
				." `#__categories` AS parent,"
				." `#__categories` AS sub_parent,"
				."("
				."		SELECT node.id, (COUNT(parent.id) - 1) AS depth"
				." 		FROM `#__categories` AS node,"
				."		`#__categories` AS parent"
				."		WHERE node.lft BETWEEN parent.lft AND parent.rgt"
				."		AND node.parent_id = {$content_id}"
				."		GROUP BY node.id"
				."		ORDER BY node.lft"
				.") AS sub_tree"
				.$where 
				." GROUP BY node.id"
				." HAVING depth <= 99"
				." ORDER BY node.lft;"
				;
				
		$db->setQuery($query);
		//oseExit($db->getQuery());
		$objs = oseDB::loadList('obj');
		
		$contentIds = array();
		$contentIds[] = $content_id;
		foreach($objs as  $obj)
		{
			$contentIds[] = $obj->id;
		}
		
		foreach($contentIds as  $content_id)
		{
			$item= $content->getItem('joomla', 'category', $content_id, 'msc', $msc_id, '', 'obj');
			if(empty($item)) {
				$updated= $content->insert('joomla', 'category', $content_id, 'msc', $msc_id, $newStatus,$params);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');;
					$result['content']= JText::_('ERROR');;
					return $result;
				}
			} else {
				$status= $item->status;
				//if($status != $newStatus) {
					$updated= $content->update($item, $newStatus,$params);
					if(!$updated) {
						$result= array();
						$result['success']= false;
						$result['title']= JText::_('ERROR');;
						$result['content']= JText::_('ERROR');;
						return $result;
					}
				//}
			}
			$query= ' SELECT * FROM `#__content` '.' WHERE catid = '.$content_id;
			$db->setQuery($query);
			$objs= oseDB :: loadList('obj');
			foreach($objs as $obj) {
				$updated= $this->changeArtStatus($obj->id, $msc_id, $newStatus,$params);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');;
					$result['content']= JText::_('ERROR');;
					return $result;
				}
			}
		}
		
		return $result;
	}
	
	function & getCatListTotal()
	{
		$db= oseDB :: instance();
		
		//$sec_id= str_replace('sec-', '', $node);
		$search= JRequest :: getString('search', null);
		$search= JString :: strtolower($search);
		$msc_id= JRequest :: getInt('msc_id', 0);
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		
		
		$query = $db->getQuery(true);

		// Select the required fields from the table.

		$query->from('#__categories AS a');

		// Join over the language
		$query->select('COUNT(*)');
		

		$query->where('a.published = 1' );

		// Filter by search in title
		$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
		$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
		
		$query = $query->__toString(). 'ORDER BY a.lft ASC';
		$db->setQuery($query);
		$total= $db->loadResult();

		return $total;
	} 
	
	function & getSequentialCatList($node) 
	{
		$db= oseDB :: instance();
		
		//$sec_id= str_replace('sec-', '', $node);
		$search= JRequest :: getString('search', null);
		$search= JString :: strtolower($search);
		$msc_id= JRequest :: getInt('msc_id', 0);
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		$maxLevel = JRequest :: getInt('maxLevel', 0);
		
		$query = $db->getQuery(true);

		// Select the required fields from the table.

		$query->from('#__categories AS a');

		// Join over the language
		$query->select('a.id,a.title, a.level');
		//$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		//$query->select('uc.name AS editor');
		//$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		//$query->select('ag.title AS access_level');
		//$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		//$query->select('ua.name AS author_name');
		//$query->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');


		$query->where('a.published = 1' );
		//$query->where('a.level = 1' );
		$query->where('a.extension = '.$db->quote('com_content'));
		
		// Filter by search in title
		if(!empty($search))
		{
			$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
			$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
			
		}
					
		if(!empty($maxLevel))
		{
			$query->where('a.level <='.$maxLevel);
		}
		
		$query1 = $query->__toString(). ' ORDER BY a.lft ASC';
		$db->setQuery($query1,$start,$limit);
		$rows = oseDB :: loadList('obj');
		
		$query->clear('select');
		$query->select('COUNT(*)');
		$query2 = $query->__toString();
		$db->setQuery($query2);
		$total = $db->loadResult();
		
		
		foreach($rows as $item) 
		{
			$item->treename = str_repeat('<span class="gtr">|&mdash;</span>',$item->level-1).$item->title;
			$obj= oseRegistry :: call('content')->getInstance('msc')->getItem('joomla', 'category', $item->id, 'msc', $msc_id, null, 'obj');
			$controlled= empty($obj) ? 0 : $obj->status;
			if($controlled == '1') {
				$item->controlled= JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1') {
				$item->controlled= JText :: _('HIDE_TO_MEMBERS');
			} else {
				$item->controlled= JText :: _('SHOW_TO_ALL');
			}
			$item->status = $controlled;
			$item->iconCls= 'task-folder';
			
			$params = oseJson::decode(oseObject::getValue($obj,'params','{}'));
			
			if(!empty($params->time_length))
			{
				$item->time_length = $params->time_length;
				$item->time_unit = $params->time_unit;
			}
		}
		$result= array();
		$result['total']= $total;
		$result['results']= $rows;
		return $result;
	}
	
	function updateParams()
	{
		$msc_id = JRequest :: getInt('msc_id', 0);
		$jc_id = JRequest :: getInt('jc_id', 0);
		$field = JRequest :: getCmd('field');
		$value = JRequest :: getInt('value', 0);
		$status = JRequest :: getInt('status', 0);
		
		$ItemParams = array();
		$ItemParams[$field] = $value;
		//$ItemParams['time_unit'] = 'week';//$timeUnit[$key];
		$updated= $this->changeCatStatus($jc_id, $msc_id, $status,$ItemParams);
		exit;
	}
}
?>