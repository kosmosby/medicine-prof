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

class oseMscModelMemberships extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function

	function getItems()
	{
		$items = oseMscTree::getTreeByParentId();
		return $items;
	}
	function getFullTree($start,$limit)
	{
		$msc = oseRegistry::call('msc');
		$return = array();

		$items = array();
		$items = $msc->retrieveTree();
		$return['total'] = count($items);

		$list = array_slice( $items, $start, $limit );

		foreach($list as $key => $item)
		{
			$members = $msc->getMembers(oseObject::getValue($item,'id'),0,0,0,'obj');
			$totalExp = oseObject::getValue($members,'total',0);

			$members = $msc->getMembers(oseObject::getValue($item,'id'),1,0,0,'obj');
			$totalAct = oseObject::getValue($members,'total',0);

			$total = $totalAct + $totalExp;

			$item = oseObject::setValue($item,'total',$total);
			$item = oseObject::setValue($item,'totalExp',$totalExp);
			$item = oseObject::setValue($item,'totalAct',$totalAct);

			$list[$key] = $item;
		}
		$return['results'] = $list;

		return $return;
	}

	function add($title)
	{
		$msc = oseRegistry::call('msc');
		$msc_id = $msc->create();
		if($msc_id)
		{
			$var['id'] = $msc_id;
			$var['title'] = $title;
			$updated = $msc->update($var);
			return $updated;
		}
		else
		{
			return false;
		}	
	}

	function extend($msc_id,$ordering)
	{
		$msc = oseRegistry::call('msc');
		return $msc->create($msc_id,$ordering);
	}

	function remove($msc_ids)
	{
		$msc = oseRegistry::call('msc');

		foreach($msc_ids as $msc_id)
		{
			if(!$msc->remove($msc_id))
			{
				return false;
			}
		}

		return true;
	}
}
?>