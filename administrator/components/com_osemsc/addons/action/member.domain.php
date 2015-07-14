<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberDomain
{
	var $table = '#__osetickets_domains';
	public function getList()
	{
		$db= oseDB :: instance();

		$search= JRequest :: getString('search', null);
		$search= JString :: strtolower($search);

		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		$dir = JRequest :: getCmd('dir', 'ASC');
		$sortField = JRequest :: getCmd('sort', 'domain');

		$msc_id = JRequest :: getInt('msc_id');
		$user_id = JRequest :: getInt('user_id');

		$where = array();
		$where[] = "`mscID` = '{$msc_id}'";
		$where[] = "`userID` = '{$user_id}'";
		if(!empty($search))
		{
			$searchQuery = $db->Quote('%'.$search.'%');
			$where[] = "`domain` LIKE {$searchQuery}";
		}

		$where = oseDB :: implodeWhere($where);

		$query = " SELECT COUNT(*) "
				." FROM `#__osetickets_domains` "
				. $where
				;
		$db->setQuery($query);
		$total= $db->loadResult();

		$query = " SELECT *"
				." FROM `#__osetickets_domains` "
				. $where
				." ORDER BY {$sortField} {$dir}"
				;

		if($start >= 0 && $limit >= 0)
		{
			$db->setQuery($query,$start,$limit);
		}
		else
		{
			$db->setQuery($query);
		}

		$items = oseDB :: loadList('obj');

		$list = array();
		foreach($items as $key => $item)
		{
			$list[$key] = $item;
		}

		$resul = array();
		$result['total'] = $total;
		$result['results'] = $list;
		oseExit(oseJson::encode($result));
	}

	function save()
	{
		$id = JRequest::getInt('id',0);

		//$domain = JRequest::getString('domain','','post', JREQUEST_ALLOWRAW);
		$domain = JRequest::getString('domain');
		$user_id = JRequest::getInt('user_id');
		$msc_id = JRequest::getInt('msc_id');
		$start_date = JRequest::getString('start_date');
		//$start_date = date('Y-m-d H:i:s',strtotime($start_date));;
		$end_date = JRequest::getString('end_date');
		//$end_date = date('Y-m-d H:i:s',strtotime($end_date));;

		//$data = JRequest::getString('data');

		$vals = array();
		$vals['id'] = $id;
		$vals['userID'] = $user_id;
		$vals['domain'] = $domain;
		$vals['mscID'] = $msc_id;
		$vals['start_date'] = $start_date;
		$vals['end_date'] = $end_date;

		if(empty($id))
		{
			unset($vals['id']);
			$updated = oseDB::insert($this->table,$vals);
		}
		else
		{
			$updated = oseDB::update($this->table,'id',$vals);
		}

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('Saved');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('Fail Saving');
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function remove()
	{
		$ids = JRequest::getVar('ids',array(),'post');

		foreach($ids as $id)
		{
			$updated = oseDB::delete($this->table,array('id'=>$id));
			if(!$updated)
			{
				break;
			}
		}

		$result = array();
		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('Saved');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('Fail Saving');
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}
}

?>