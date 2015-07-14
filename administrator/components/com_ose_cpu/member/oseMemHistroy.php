<?php
defined('_JEXEC') or die(";)");

class oseMemHistory
{
	public static function record($msc_id,$member_id,$action)
	{
		$db = oseDB::instance();

		$date = oseHTML::getDateTime();
		$date = $db->Quote($date);

		$query = " INSERT INTO `#__osemsc_member_history` "
				." (`msc_id`,`member_id`,`action`,`date`,`accumulated`) "
				." VALUES "
				." ({$msc_id},{$member_id},'{$action}',{$date}, '0') "
				;
		$db->setQuery($query);

		if(oseDB::query())
		{
			return $db->insertid();
		}
		else
		{
			return false;
		}

	}

	public static function getHistory($msc_id,$member_id,$action = null)
	{
		$db = oseDB::instance();

		$where = array();

		if(!empty($action))
		{
			$where[] = " `action` = '{$action}'";
		}
		$where[] = " `msc_id` = '{$msc_id}'";
		$where[] = " `member_id` = '{$member_id}'";
		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_member_history` "
				. $where
				." ORDER BY date DESC"
				;
		$db->setQuery($query);

		$objs = oseDB::loadList('obj');

		return $objs;
	}

	public static function archive($msc_id,$member_id,$action)
	{
		$db = oseDB::instance();

		$date = oseHTML::getDateTime();
		$date = $db->Quote($date);

		$query = " INSERT INTO `#__osemsc_member_history` "
				." (`msc_id`,`member_id`,`action`,`date`, `accumulated`) "
				." VALUES "
				." ({$msc_id},{$member_id},'{$action}',{$date}, '0') "
				;
		$db->setQuery($query);

		if(oseDB::query())
		{
			return $db->insertid();
		}
		else
		{
			return false;
		}

	}
}
?>