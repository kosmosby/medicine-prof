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

class oseMscControllerAddons extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function save()
	{
		$model = $this->getModel('addons');

		$post = JRequest::get('post');

		unset($post['option']);
		unset($post['controller']);
		unset($post['task']);

		$updated = $model->save($post);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = 'Save Successfully!';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = 'Fail Saving!';
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getList()
	{
		$model = $this->getModel('addons');

		$addonType = JRequest::getWord('addontype',null);

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$items = $model->getList($addonType,$start,$limit);

		$total = $model->getTotal();

		$result = array();

		if($total < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = $total;
			$result['results'] = $items;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function enableAddon()
	{
		$addon_id = JRequest::getInt('addon_id',0);
		$isBackend = JRequest::getBool('isBackend',false);
		$model = $this->getModel('addons');

		$updated = $model->enableAddon($addon_id,$isBackend);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = ' Successfully';
		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = ' Error';
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getAddon()
	{
		$addon_id = JRequest::getInt('addon_id',0);

		$model = $this->getModel('addons');

		$item = $model->getAddon($addon_id);

		$result = array();

		if(empty($item))
		{
			$result['success'] = true;
			$result['total'] = 0;
			$result['result'] = null;
		}
		else
		{
			$result['success'] = true;
			$result['total'] = 1;
			$result['result'] = $item;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getOrder()
	{
		//$addon_id = JRequest::getInt('addon_id',0);

		$type = JRequest::getString('type',null);

		$model = $this->getModel('addons');

		$items = $model->getOrder($type);

		$total = count($items);

		$result = array();

		if($total > 0)
		{
			$result['success'] = true;
			$result['total'] = $total;
			$result['results'] = $items;
		}
		else
		{
			$result['success'] = true;
			$result['total'] = 0;
			$result['results'] = null;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function updateOrder()
	{
		$model = $this->getModel('addons');

		$addon_id = JRequest::getInt('addon_id',0);
		$ordering = JRequest::getInt('ordering',0);

		$updated = $model->updateOrder($addon_id,$ordering);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = 'Save Successfully!';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = 'Fail Saving!';
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function remove()
	{
		$model = $this->getModel('addons');

		$addon_id = JRequest::getInt('addon_id',0);

		$updated = $model->remove($addon_id);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = 'Save Successfully!';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = 'Fail Saving!';
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getAddonTypes()
	{
		$model = $this->getModel('addons');

		$items = $model->getAddonTypes();

		$result = array();

		if(count($items) < 1)
		{
			$result['success'] = true;
			$result['total'] = 0;
			$result['results'] = null;
		}
		else
		{
			//$result['success'] = true;
			$result['total'] = count($items);
			$result['results'] = $items;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}
	function updateAddon()
	{
		oseMscAddon::updateAddonSimple();
	}
}


