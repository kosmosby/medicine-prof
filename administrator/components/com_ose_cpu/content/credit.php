<?php
/**
  * @version     1.0 +
  * @package     Open Source Credit Control - com_ose_credit
  * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author      Created on 17-May-2011
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
class oseContentCredit
{
	protected $table= '#__ose_credit_content';
	function getItem($cType, $content_type, $content_id, $entry_type= 'credit', $entry_id= 0, $status= null, $type= 'array')
	{
		$db= oseDB :: instance();
		$where= array();
		$where[]= "`content_id` = ".(int) $content_id;
		$where[]= "`content_type`= ".$db->Quote($content_type, true);
		$where[]= "`type` =  ".$db->Quote($cType, true);
		$where[]= "`entry_id` = ".(int) $entry_id;
		$where[]= "`entry_type` =  ".$db->Quote($entry_type, true);
		if(!empty($status) || $status == '0')
		{
			$where[]= "`status` = ".(int) $status;
		}
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT * FROM `{$this->table}`".$where;
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}
	function getItemSubmit($id, $entry_type= 'credit_submit', $type= 'array')
	{
		$db= oseDB :: instance();
		if(empty($id))
		{
			return false;
		}
		$where= array();
		$where[]= "`id` = ".(int) $id;
		$where[]= "`entry_type` =  ".$db->Quote($entry_type, true);
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT * FROM `{$this->table}`".$where;
		$db->setQuery($query);
		$item= oseDB :: loadItem($type);
		return $item;
	}
	function getList($cType, $content_type, $entry_id, $status= null, $entry_type= 'msc', $type= 'array')
	{
		$db= oseDB :: instance();
		$where= array();
		$where[]= "`content_type`= ".$db->Quote($content_type, true);
		$where[]= "`type` =  ".$db->Quote($cType, true);
		$where[]= "`entry_id` = ".(int) $entry_id;
		$where[]= "`entry_type` =  ".$db->Quote($entry_type, true);

		if(!empty($status) || $status == '0')
		{
			$where[]= "`status` = ".(int) $status;
		}
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT * FROM `{$this->table}`".$where;
		$db->setQuery($query);
		$items= oseDB :: loadList($type);
		return $items;
	}
	function update($item, $newStatus, $params= array())
	{
		$db= oseDB :: instance();
		$status= oseObject :: getValue($item, 'status');
		$content_id= oseObject :: getValue($item, 'content_id');
		$content_type= oseObject :: getValue($item, 'content_type');
		$type= oseObject :: getValue($item, 'type');
		$entry_id= oseObject :: getValue($item, 'entry_id', 0);
		$entry_type= oseObject :: getValue($item, 'entry_type', 'credit');
		$where= array();

		$where[]= "`content_id` = ".(int) $content_id;
		$where[]= "`content_type`= ".$db->Quote($content_type, true);
		$where[]= "`type` =  ".$db->Quote($type, true);
		$where[]= "`entry_id` = ".(int) $entry_id;
		$where[]= "`entry_type` =  ".$db->Quote($entry_type, true);

		$where= oseDB :: implodeWhere($where);

		if(!empty($params))
		{
			$credit = ",`credit` = {$params['credit_amount']}";
			$params= $db->Quote(oseJson :: encode($params));
			$sql= ",`params` = {$params}";
		}
		else
		{
			$sql= null;
		}
		$query= " UPDATE `{$this->table}`"." SET `status` = ". (int)$newStatus. "{$sql}"."{$credit}".$where;
		$db->setQuery($query);
		return oseDB :: query();
	}
	function updateSubmit($item, $args, $params= array())
	{
		$db= oseDB :: instance();
		$where= array();
		$where[]= "`id` = ".(int) $args['id'];
		$where[]= "`entry_type` = ".$db->Quote('credit_submit');
		$where= oseDB :: implodeWhere($where);

		$sql = array();

		foreach ($args as $key => $value)
		{
			if (!in_array($key, array('id', 'credit_amount')))
			{
				$sql[]=	"`".$key."`"."=".$db->Quote($value);
			}
		}
		if(!empty($params))
		{
			$params= $db->Quote(oseJson :: encode($params));
			$sql[]= "`params` = ".$params;
		}
		$sql = implode(",", $sql);

		$query= " UPDATE `{$this->table}`"." SET ".$sql.$where;
		$db->setQuery($query);
		return oseDB :: query();
	}
	function insert($type= 'joomla', $content_type, $content_id, $entry_type= 'credit', $entry_id= 0, $status= 0, $params= array())
	{
		$db= oseDB :: instance();
		$sql= array();
		$sql['content_id']= $content_id;
		$sql['content_type']= $content_type;
		$sql['type']= $type;
		$sql['entry_id']= $entry_id;
		$sql['entry_type']= $entry_type;
		$sql['status']= $status;
		if(!empty($params))
		{
			$sql['credit']= $params['credit_amount'];
			$sql['params']= oseJson :: encode($params);
		}
		$keys= array();
		$values= array();
		$result= oseDB :: insert($this->table, $sql);
		return $result;
	}
	function insertSubmit($args, $params= array())
	{
		$db= oseDB :: instance();
		$sql= $args;
		$sql['id']= null;
		$sql['entry_id']= null;
		$sql['entry_type']= 'credit_submit';
		$sql['status']= 1;

		if(!empty($params))
		{
			$sql['credit']= $params['credit_amount'];
			$sql['params']= oseJson :: encode($params);
		}

		$keys= array();
		$values= array();
		$inserted_id= oseDB :: insert($this->table, $sql);
		$args = array();
		$args['id']=$inserted_id;
		$args['content_id']=$inserted_id;
		$item = $this->getItemSubmit($inserted_id);
		$result = $this->updateSubmit($item, $args, $params= array());
		return $result;
	}
	function getGuestRestrictedContent($type= 'joomla', $content_type, $rType= 'array')
	{
		$db= oseDB :: instance();
		$where= array();
		$where[]= "type = '{$type}'";
		$where[]= "content_type = '{$content_type}'";
		$where[]= "status = '1'";
		$where= oseDB :: implodeWhere($where);
		$query= " SELECT *"." FROM `{$this->table}`".$where;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$objs= oseDB :: loadList($rType);
		return $objs;
	}
	function getMemberRestrictedContent($type= 'joomla', $content_type, $member_id, $rType= 'array')
	{
		$db= oseDB :: instance();
		$member= oseRegistry :: call('member');
		$member->instance($member_id);
		$mscs= $member->getAllOwnedMsc(false, 1, 'obj');
		// get restricted of status 1; Step 1 get the start date
		$mValues= array();
		$startDate= array();
		foreach($mscs as $msc)
		{
			$startDate[$msc->msc_id]= $msc->start_date;
			$mValues[]= $msc->msc_id;
		}
		if(!empty($mValues))
		{
			$mValues= '('.implode(',', $mValues).')';
			$cquery= " (child.entry_id IN {$mValues} AND child.entry_type = 'msc') "." OR "." (child.entry_id = '{$member_id}' AND child.entry_type = 'member') ";
			$bquery= " (entry_id IN {$mValues} AND entry_type = 'msc') "." OR "." (entry_id = '{$member_id}' AND entry_type = 'member') ";
		}
		else
		{
			$cquery= " (child.entry_id = '{$member_id}' AND child.entry_type = 'member') ";
			$bquery= " (entry_id = '{$member_id}' AND entry_type = 'member') ";
		}
		$cWhere= array();
		$cWhere[]= $cquery;
		$cWhere[]= "child.type = '{$type}'";
		$cWhere[]= "child.content_type = '{$content_type}'";
		$cWhere[]= "child.status = '1'";
		$cWhere= oseDB :: implodeWhere($cWhere);
		$pWhere= array();
		$pWhere[]= "parent.type = '{$type}'";
		$pWhere[]= "parent.content_type = '{$content_type}'";
		$pWhere[]= "parent.status = '1'";
		$pWhere= oseDB :: implodeWhere($pWhere);
		$query= " SELECT parent.* FROM `{$this->table}` AS parent"." WHERE parent.content_id NOT IN ("." 		SELECT DISTINCT child.content_id "." 		FROM `{$this->table}` AS child".$cWhere." )"." AND ".str_replace('WHERE', '', $pWhere);
		;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$objs= oseDB :: loadList($rType);
		// get restricted of status -1;
		$bWhere= array();
		$bWhere[]= $bquery;
		$bWhere[]= "type = '{$type}'";
		$bWhere[]= "content_type = '{$content_type}'";
		$bWhere[]= "status = '-1'";
		$bWhere= oseDB :: implodeWhere($bWhere);
		$query= " SELECT DISTINCT content_id "." FROM `{$this->table}` ".$bWhere;
		$db->setQuery($query);
		$banObj= oseDB :: loadList($rType);
		if(empty($objs))
		{
			$objs= array();
		}
		if(empty($banObj))
		{
			$banObj= array();
		}
		// get restricted of sequential, status = 1
		$query= " SELECT DISTINCT child.content_id, child.entry_id, child.params "." 	FROM `{$this->table}` AS child".$cWhere;
		$db->setQuery($query);
		$sqeObjs= oseDB :: loadList($rType);
		foreach($sqeObjs as $key => $sqeObj)
		{
			$params= oseJson :: decode(oseObject :: getValue($sqeObj, 'params', '{}'));
			$timeLength= oseObject :: getValue($params, 'time_length', false);
			if($timeLength)
			{
				// provided that entry type is msc
				$total= strtotime("+{$timeLength}  week", strtotime($startDate[oseObject :: getValue($sqeObj, 'entry_id')]));
				$cur= strtotime(oseHTML :: getDateTime());
				//oseExit($timeLength);
				if($cur > $total)
				{
					unset($sqeObjs[$key]);
				}
			}
			else
			{
				unset($sqeObjs[$key]);
			}
		}
		$objs= array_merge($objs, $banObj, $sqeObjs);
		//oseExit(oseDB::loadList($rType));
		return $objs;
	}
	function changeStatus($type, $content_type, $content_id, $status, $params= array())
	{
		$item= $this->getItem($type, $content_type, $content_id);
		if(empty($item))
		{
			$updated= $this->insert($type, $content_type, $content_id, 'credit', '0', $status, $params);
			if(!$updated)
			{
				$result= array();
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error';
				return false;
			}
		}
		else
		{
			//$status= $item->status;
			$updated= $this->update($item, $status, $params);
			if(!$updated)
			{
				$result= array();
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error';
				return false;
			}
		}
		return $updated;
	}
	function changeSubmitStatus($args= array())
	{
		$item= $this->getItemSubmit($args['id']);
		$status= 1;
		$params['credit_amount']= $args['credit'];
		if(empty($item))
		{
			$updated= $this->insertSubmit($args, $params);
			if(!$updated)
			{
				$result= array();
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error';
				return false;
			}
		}
		else
		{
			//$status= $item->status;
			$updated= $this->updateSubmit($item, $args, $params);
			if(!$updated)
			{
				$result= array();
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error';
				return false;
			}
		}
		return $updated;
	}
}
?>