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


class oseMscControllerMembership extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function changeOrder()
	{

	}

	function save()
	{
		return true;
	}

	function getImg()
	{
		$result = array();
		$result['images'] = array(array('name'=>'test','url'=>JRoute::_(JURI::root().'administrator/components/com_osemsc/assets/images/accept.png')));

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function getTree()
	{
		$model = $this->getModel('membership');
		$list = $model->getTree();

		$result = array();

		$result['total'] = count($list);
		$result['results'] = $list;

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getProperty()
	{
		$model = $this->getModel('membership');

		$msc_id = JRequest::getInt('msc_id',0);
		$item = $model->getProperty($msc_id);

		$result = array();

		if(empty($item))
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $item;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getOrder()
	{
		$model = $this->getModel('membership');

		$msc_id = JRequest::getInt('msc_id',0);
		$list = $model->getOrder($msc_id);

		$result = array();

		$result['total'] = count($list);
		$result['results'] = $list;

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getloginRedirect()
	{
		$model = $this->getModel('membership');
		$msc_id = JRequest::getInt('msc_id',0);
		$list = $model->getloginRedirect($msc_id);
		$result = array();
		$result['total'] = count($list);
		$result['results'] = $list;
		$result = oseJson::encode($result);
		oseExit($result);
	}

	function getItem()
	{
		$model = $this->getModel('membership');

		$msc_id = JRequest::getInt('msc_id',0);
		$list = $model->getItem($msc_id);

		$total = empty($list)?0:1;

		$result = array();

		$result['total'] = $total;
		$result['result'] = $list;

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getExtItem()
	{
		$model = $this->getModel('membership');

		$msc_id = JRequest::getInt('msc_id',0);
		$type = JRequest::getCmd('type',null);
		$item = $model->getExtItem($msc_id,$type);

		$result = array();

		if(empty($item) || count($item) <= 1)
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $item;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function update()
	{
		$model = $this->getModel('membership');

		$post = JRequest::get('post');

		$updated = $model->update($post);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText :: _('DONE');
			$result['content'] = JText :: _('UPDATE_SUCCESSFULLY');
		}
		else
		{
			$result['title'] = JText :: _('ERROR');
			$result['content'] = JText :: _('FAIL_TO_UPDATE');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function action()
	{
		$actionName = JRequest::getString('action');

		//$model = $this->getModel('membership');

		parent::action();

		/*
		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = 'Update Successfully!';
		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = 'Fail to Update!';
		}

		$result = oseJson::encode($result);

		oseExit($result);
		*/
	}

	function preview()
	{
		$model = $this->getModel('membership');

		$post = JRequest::get('post');

		$updated = $model->preview();

		$result = oseJson::encode($updated);
		oseExit($result);
	}
}
?>