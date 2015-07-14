<?php
defined('_JEXEC') or die(";)");


class oseContentMsc
{
	function getItem($cType, $content_type, $content_id, $entry_type = 'msc', $entry_id, $status = null,  $type = 'array')
	{
		$db = oseDB::instance();

		$where = array();
		$where[] = "content_id = {$content_id}";
		$where[] = "content_type='{$content_type}'";
		$where[] = "type = '{$cType}'";
		$where[] = "entry_id = {$entry_id}";
		$where[] = "entry_type = '{$entry_type}'";

		if(!empty($status) || $status == '0')
		{
			$where[] = "status = {$status}";
		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_content`"
				. $where
				;
		$db->setQuery($query);

		$item = oseDB::loadItem($type);

		return $item;
	}

	function getList($cType, $content_type, $entry_id, $status = null, $entry_type = 'msc', $type = 'array')
	{
		$db = oseDB::instance();

		$where = array();
		$where[] = "content_type='{$content_type}'";
		$where[] = "type = '$cType'";
		$where[] = "entry_id = {$entry_id}";
		$where[] = "entry_type = {$entry_type}";

		if(!empty($status) || $status == '0')
		{
			$where[] = "status = {$status}";
		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_content`"
				. $where
				;
		$db->setQuery($query);

		$items = oseDB::loadList($type);

		return $items;
	}

	function update($item,$newStatus,$params = array())
	{
		$db = oseDB::instance();

		$status = oseObject::getValue($item,'status');
		$content_id = oseObject::getValue($item,'content_id');
		$content_type = oseObject::getValue($item,'content_type');
		$type = oseObject::getValue($item,'type');
		$entry_id = oseObject::getValue($item,'entry_id');
		$entry_type = oseObject::getValue($item,'entry_type');


		$where = array();
		$where[] = "content_id = {$content_id}";
		$where[] = "content_type='{$content_type}'";
		$where[] = "type = '$type'";
		$where[] = "entry_id = {$entry_id}";
		$where[] = "entry_type = ".$db->Quote($entry_type);

		$where = oseDB::implodeWhere($where);

		$query = " UPDATE `#__osemsc_content`"
				." SET status = '{$newStatus}' {$sql}"
				. $where
				;
		if(!empty($params))
		{
			$params = $db->Quote(oseJson::encode($params));
			$sql = ",`params` = {$params}";
		}
		else
		{
			$sql = null;
		}

		$query = " UPDATE `#__osemsc_content`"
				." SET status = '{$newStatus}' {$sql}"
				. $where
				;
		$db->setQuery($query);

		return  oseDB::query();

	}

	function insert($type = 'joomla', $content_type, $content_id, $entry_type = 'msc', $entry_id, $status = 0,$params = array())
	{
		$db = oseDB::instance();

		$sql = array();

		$sql['content_id'] = $content_id;
		$sql['content_type'] = $content_type;
		$sql['type'] = $type;
		$sql['entry_id'] = $entry_id;
		$sql['entry_type'] = $entry_type;
		$sql['status'] = $status;

		if(!empty($params))
		{
			$sql['params'] = oseJson::encode($params);
		}

		$keys = array();
		$values = array();

		foreach( $sql as $key => $value )
		{
			$keys[] = "`{$key}`";
			$values[] = $db->Quote($value);
		}


		$query = " INSERT INTO `#__osemsc_content`"
				." (".implode(',',$keys).")"
				." VALUES "
				." (".implode(',',$values).")"
				;
		$db->setQuery($query);

		return  oseDB::query();

	}

	function getGuestRestrictedContent($type = 'joomla', $content_type,$rType = 'array')
	{
		$db = oseDB::instance();

		$where = array();

		$where[] = "type = '{$type}'";
		$where[] = "content_type = '{$content_type}'";
		$where[] = "status = '1'";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT *"
				." FROM `#__osemsc_content`"
				. $where
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$objs = oseDB::loadList($rType);

		return $objs;
	}

	function getMemberRestrictedContent($type = 'joomla', $content_type, $member_id,$rType = 'array')
	{
		$db = oseDB::instance();

		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$mscs = $member->getAllOwnedMsc(false,1,'obj');

		// get restricted of status 1; Step 1 get the start date
		$mValues = array();
		$startDate = array();
		foreach($mscs as $msc)
		{
			$startDate[$msc->msc_id] = $msc->start_date;
			$mValues[] = $msc->msc_id;
		}

		if(!empty($mValues))
		{
			$mValues = '('.implode(',',$mValues).')';

			$cquery = " (child.entry_id IN {$mValues} AND child.entry_type = 'msc') "
					   ." OR "
					   ." (child.entry_id = '{$member_id}' AND child.entry_type = 'member') "
					   ;

			$bquery = " (entry_id IN {$mValues} AND entry_type = 'msc') "
					 ." OR "
					 ." (entry_id = '{$member_id}' AND entry_type = 'member') "
					 ;
		}
		else
		{
			$cquery =  " (child.entry_id = '{$member_id}' AND child.entry_type = 'member') "
					 ;

			$bquery = " (entry_id = '{$member_id}' AND entry_type = 'member') "
					 ;
		}


		$cWhere = array();

		$cWhere[] = $cquery;

		$cWhere[] = "child.type = '{$type}'";
		$cWhere[] = "child.content_type = '{$content_type}'";
		$cWhere[] = "child.status = '1'";

		$cWhere = oseDB::implodeWhere($cWhere);

		$pWhere = array();
		$pWhere[] = "parent.type = '{$type}'";
		$pWhere[] = "parent.content_type = '{$content_type}'";
		$pWhere[] = "parent.status = '1'";

		$pWhere = oseDB::implodeWhere($pWhere);

		$query = " SELECT parent.* FROM `#__osemsc_content` AS parent"
				." WHERE parent.content_id NOT IN ("
				." 		SELECT DISTINCT child.content_id "
				." 		FROM `#__osemsc_content` AS child"
				. $cWhere
				." )"
				." AND ". str_replace('WHERE','',$pWhere);
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$objs = oseDB::loadList($rType);

		// get restricted of status -1;
		$bWhere = array();

		$bWhere[] = $bquery;

		$bWhere[] = "type = '{$type}'";
		$bWhere[] = "content_type = '{$content_type}'";
		$bWhere[] = "status = '-1'";

		$bWhere = oseDB::implodeWhere($bWhere);

		$query = " SELECT DISTINCT content_id "
				." FROM `#__osemsc_content` "
				. $bWhere
				;
		$db->setQuery($query);
		$banObj = oseDB::loadList($rType);

		if(empty($objs))
		{
			$objs = array();
		}

		if(empty($banObj))
		{
			$banObj = array();
		}

		// get restricted of sequential, status = 1
		$query = " SELECT DISTINCT child.content_id, child.entry_id, child.params "
				." 	FROM `#__osemsc_content` AS child"
				. $cWhere
				;
		$db->setQuery($query);
		$sqeObjs = oseDB::loadList($rType);

		foreach ($sqeObjs as $key => $sqeObj)
		{
			$params = oseJson::decode(oseObject::getValue($sqeObj,'params','{}'));
			$timeLength = oseObject::getValue($params,'time_length',false);
			$time_unit = oseObject::getValue($params,'time_unit','week');
			if($timeLength)
			{
				// provided that entry type is msc
				$total = strtotime("+{$timeLength}  {$time_unit}",strtotime($startDate[oseObject::getValue($sqeObj,'entry_id')]));
				$cur = strtotime(oseHTML::getDateTime());
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

		$objs = array_merge( $objs, $banObj,$sqeObjs);
		
		
		
		//$objs = array_diff($objs,$aObj);
		//oseExit(oseDB::loadList($rType));
		return $objs;
	}

	function getSequentialMessage($type = 'joomla', $content_type, $content_id, $member_id,$rType = 'array')
	{
		$db = oseDB::instance();

		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$mscs = $member->getAllOwnedMsc(false,1,'obj');

		// get restricted of status 1; Step 1 get the start date
		$mValues = array();
		$startDate = array();
		foreach($mscs as $msc)
		{
			$startDate[$msc->msc_id] = $msc->start_date;
			$mValues[] = $msc->msc_id;
		}

		if(!empty($mValues))
		{
			$mValues = '('.implode(',',$mValues).')';

			$cquery = " (child.entry_id IN {$mValues} AND child.entry_type = 'msc') "
					   ." OR "
					   ." (child.entry_id = '{$member_id}' AND child.entry_type = 'member') "
					   ;
		}
		else
		{
			$cquery =  " (child.entry_id = '{$member_id}' AND child.entry_type = 'member') "
					 ;
		}

		$cWhere = array();

		$cWhere[] = $cquery;

		$cWhere[] = "child.type = '{$type}'";
		$cWhere[] = "child.content_type = '{$content_type}'";
		$cWhere[] = "child.content_id = ".(int)$content_id;
		$cWhere[] = "child.status = '1'";

		$cWhere = oseDB::implodeWhere($cWhere);

		// get restricted of sequential, status = 1
		$query = " SELECT DISTINCT child.content_id, child.entry_id, child.params "
				." 	FROM `#__osemsc_content` AS child"
				. $cWhere
				;
		$db->setQuery($query);
		$sqeObjs = oseDB::loadList($rType);

		$return = null;
		foreach ($sqeObjs as $key => $sqeObj)
		{
			$params = oseJson::decode(oseObject::getValue($sqeObj,'params','{}'));
			$timeLength = oseObject::getValue($params,'time_length',false);
			$time_unit = oseObject::getValue($params,'time_unit','week');

			if($timeLength)
			{
				// provided that entry type is msc
				$total = strtotime("+{$timeLength}  {$time_unit}",strtotime($startDate[oseObject::getValue($sqeObj,'entry_id')]));
				$cur = strtotime(oseHTML::getDateTime());
				//oseExit($timeLength);
				if($cur > $total)
				{
					unset($sqeObjs[$key]);
					$return = null;
				}
				else
				{
					$total = strtotime("+{$timeLength}  {$time_unit}",strtotime($startDate[oseObject::getValue($sqeObj,'entry_id')]));
					$globalConfig = oseRegistry::call('msc')->getConfig('global','obj');
					if(!empty($globalConfig->DateFormat))
					{
						$return= date($globalConfig->DateFormat, $total);
					}else{
						$return= date("Y-m-d h:i:s", $total);
					}
				}
			}
		}
		return $return;
	}
	
	function getMemberAccessContent($type = 'joomla', $content_type, $member_id,$rType = 'array')
	{
		$db = oseDB::instance();

		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$mscs = $member->getAllOwnedMsc(false,1,'obj');

		// get restricted of status 1; Step 1 get the start date
		$mValues = array();
		$startDate = array();
		foreach($mscs as $msc)
		{
			$startDate[$msc->msc_id] = $msc->start_date;
			$mValues[] = $msc->msc_id;
		}

		if(!empty($mValues))
		{
			$mValues = '('.implode(',',$mValues).')';


			$bquery = " (entry_id IN {$mValues} AND entry_type = 'msc') "
					 ." OR "
					 ." (entry_id = '{$member_id}' AND entry_type = 'member') "
					 ;
		}
		else
		{
			$bquery = " (entry_id = '{$member_id}' AND entry_type = 'member') "
					 ;
		}
		// filter the intersect access content
		$aWhere = array();
		$aWhere[] = $bquery;
		$aWhere[] = "type = '{$type}'";
		$aWhere[] = "content_type = '{$content_type}'";
		$aWhere[] = "status = '1'";
		$aWhere = oseDB::implodeWhere($aWhere);

		$query = " SELECT DISTINCT content_id, params, entry_id"
				." FROM `#__osemsc_content` "
				. $aWhere
				;
		$db->setQuery($query);
		$aObj = oseDB::loadList($rType);
		
		foreach ($aObj as $key => $sqeObj)
		{
			$params = oseJson::decode(oseObject::getValue($sqeObj,'params','{}'));
			$timeLength = oseObject::getValue($params,'time_length',false);
			$time_unit = oseObject::getValue($params,'time_unit','week');
			if($timeLength)
			{
				// provided that entry type is msc
				$total = strtotime("+{$timeLength}  {$time_unit}",strtotime($startDate[oseObject::getValue($sqeObj,'entry_id')]));
				$cur = strtotime(oseHTML::getDateTime());
				//oseExit($timeLength);
				if($cur < $total)
				{
					unset($aObj[$key]);
				}
			}
		}
		
		return $aObj;
	}
}
?>