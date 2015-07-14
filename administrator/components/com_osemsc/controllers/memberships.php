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


class oseMscControllerMemberships extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function reorder()
	{

	}


	function delete()
	{

	}



	function getFullTree()
	{
		$model = $this->getModel('memberships');

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$tree = $model->getFullTree($start,$limit);

		//$tree = $model->toTree($list);
		/*
		$result['total'] = $tree['total'];
		$result['results'] = $tree[''];
		*/
		$result = oseJson::encode($tree);

		oseExit($result);
	}

	function getList()
	{
		$model = $this->getModel('memberships');

		$msc_id = JRequest::getInt('msc_id',0);


		$list = $model->getList($msc_id);

		$tree = $model->toTree($list);

		//$result['total'] = count($tree);
		//$result['results'] = $tree;

		$result = oseJson::encode($tree);

		oseExit($result);
	}

	function add()
	{
		$model = $this->getModel('memberships');

		$result = array();

		$title = JRequest::getString('title');

		$id = $model->add($title);

		if($id)
		{
			$result['success'] = true;
			$result['title'] = JText :: _('DONE');
			$result['content'] = JText :: _('CREATE_SUCCESSFULLY');
			$result['id'] = $id;
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText :: _('ERROR');
			$result['content'] = JText :: _('FAIL_TO_CREATE');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function extend()
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$ordering = JRequest::getInt('ordering',999);

		$model = $this->getModel('memberships');

		$id = $model->extend($msc_id,$ordering);
		if( $id )
		{
			$result['success'] = true;
			$result['title'] = JText :: _('DONE');
			$result['content'] = JText :: _('CREATE_SUCCESSFULLY');
			$result['id'] = $id;
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText :: _('ERROR');
			$result['content'] = JText :: _('FAIL_TO_CREATE');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function remove()
	{
		$msc_ids = JRequest::getVar('msc_ids',array(),'post','array');

		$model = $this->getModel('memberships');

		if( $model->remove($msc_ids) )
		{
			$result['success'] = true;
			$result['title'] = JText :: _('DONE');
			$result['content'] = JText :: _('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText :: _('ERROR');
			$result['content'] = JText :: _('FAIL_TO_REMOVE');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}


	function getAddon()
	{
		$result = array();

		$addon_name = JRequest::getCmd('addon_name',null);
		$type = JRequest::getCmd('addon_type',null);

		if($type == 'content' && ($addon_name == 'jcontent' || $addon_name == 'jdownloads'|| $addon_name == 'sobipro'))
		{
			$com = OSECPU_F_PATH.DS.'extjs';
			echo '<style type="text/css">'."\r\n";
			require_once($com.'/treegrid/treegrid.css');
			echo "\r\n".'</style>';

			echo '<script type="text/javascript">'."\r\n";
			require_once($com."/treegrid/TreeGridSorter.js");
	        require_once($com."/treegrid/TreeGridColumnResizer.js");
	        require_once($com."/treegrid/TreeGridNodeUI.js");
	        require_once($com."/treegrid/TreeGridLoader.js");
	        require_once($com."/treegrid/TreeGridColumns.js");
	        require_once($com."/treegrid/TreeGrid.js");

			echo "\r\n".'</script>';
		}

		if($type == 'panel' && $addon_name == 'basic')
		{
			/*
			$com = OSECPU_F_PATH.DS.'extjs';
			 //oseHTML::script($com."/htmleditor/tiny_mce/tiny_mce.js",'1.5');
        //oseHTML::script($com."/htmleditor/TinyMCE.js",'1.5');

			echo '<script type="text/javascript">'."\r\n";
			//require_once($com."/htmleditor/tiny_mce/tiny_mce.js");
	        require_once($com."/htmleditor/TinyMCE.js");
			echo "\r\n".'</script>';
			*/
		}

		if($type == 'bridge' && $addon_name == 'docman')
		{
			$com = OSECPU_F_PATH.DS.'extjs';
			echo '<script type="text/javascript">'."\r\n";
			require_once($com.'/multiselect/MultiSelect.js');
			echo "\r\n".'</script>';
			echo '<style type="text/css"> '."\r\n";
			require_once($com.'/multiselect/MultiSelect.css');
			echo "\r\n".'</style>';
		}
		parent::getAddon();
	}
}

?>