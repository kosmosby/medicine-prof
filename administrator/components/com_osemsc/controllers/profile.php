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
defined('_JEXEC') or die('Restricted access');
class oseMscControllerProfile extends oseMscController {
	protected $link= null;
	function __construct() {
		parent :: __construct();
	}
	function getList() {
		$model= $this->getModel('profile');
		$items= $model->getList();
		$result= array();
		$result['total']= count($items);
		$result['results']= $items;
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function save() {
		$model= $this->getModel('profile');
		$updated= $model->save();
		$result= array();
		if($updated) {
			$result['success']= true;
			$result['title']= JText::_('DONE');
			$result['content']= JText :: _('SUCCESSFULLY');
			$result['id']= $updated;
		} else {
			$result['success']= false;
			$result['title']= JText::_('ERROR');
			$result['content']= JText :: _('ERROR');
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function getProfile() {
		$model= $this->getModel('profile');
		$prfile= $model->getProfile();
		$result= array();
		if(empty($prfile)) {
			$result['total']= 0;
			$result['result']= '';
		} else {
			$result['total']= 1;
			$result['result']= $prfile;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function remove() {
		$model= $this->getModel('profile');
		$updated= $model->remove();
		$result= array();
		if($updated) {
			$result['success']= true;
			$result['title']= JText::_('DONE');
			$result['content']= JText :: _('SUCCESSFULLY');
		} else {
			$result['success']= false;
			$result['title']= JText::_('ERROR');
			$result['content']= JText :: _('ERROR');
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function getOrder() {
		$model= $this->getModel('profile');
		$list= $model->getOrder();
		$result= array();
		$result['total']= count($list);
		$result['results']= $list;
		$result= oseJson :: encode($result);
		oseExit($result);
	}
	function getOptions() {
		$model= $this->getModel('profile');
		$list= $model->getOptions();
		$result= array();
		$result['total']= count($list);
		$result['results']= $list;
		$result= oseJson :: encode($result);
		oseExit($result);
	}
	function getMod()
    {
    	$result = array();
		$type = JRequest::getCmd('type',null);
		echo '<script type="text/javascript">'."\r\n";
		require_once(OSEMSC_B_VIEW.DS.$type.DS.'js'.DS.'ext.'.$type.'.js');
		echo "\r\n".'</script>';
		oseExit();
    }
}
?>