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


class oseMscControllerEmails extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function getList()
	{
		$model = $this->getModel('emails');

		$list = $model->getList();
		$total = $model->getTotal();

		$result = array();

		if($total > 0)
		{
			$result['total'] = $total;
			$result['results'] = $list;
		}
		else
		{
			$result['total'] = 0;
			$result['results'] = null;
		}

		$result = oseJSON::encode($result);

		echo $result;exit;
	}

	function getEmails()
	{
		$model = $this->getModel('emails');

		$result = $model->getEmails();


		if(count($result['results']) < 1)
		{
			$result['total'] = 0;
		}


		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function getItem()
	{
		$model = $this->getModel('emails');

		$id = JRequest::getint('id',0);

		$doc = $model->getItem($id);

		$result = array();

		if(empty($doc))
		{
			$result['total'] = 0;
			$result['result'] = '';
		}
		else
		{
			$result['total'] = 1;
			$result['result'] = $doc;
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function save()
	{
		$model = $this->getModel('emails');

		$updated = $model->save();

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SUCCESSFULLY');
			$result['id'] = $updated;
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function remove()
	{
		$model = $this->getModel('emails');

		$email_id = JRequest::getInt('email_id',0);

		$updated = $model->remove($email_id);

		$result = array();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function getEmailParams()
	{
		//$type = JRequest::getVar('type','wel_email');

		$model = $this->getModel('emails');

		$Params = $model->getEmailParams();

		$result = array();

		if(empty($Params))
		{
			$result = null;
		}
		else
		{
			foreach ($Params as $key => $value)
			{
				echo  '<div><div>['.$key.'] : '.$value.'</div></div>';
			}
		}

		//$result = oseJSON::encode($result);

		oseExit();
	}

	function loadEmailTemplate()
	{
		$model = $this->getModel('emails');
		$updated = $model-> loadEmailTemplate();
		if($updated)
		{
			$result['success'] = true;
			$result['status'] = JText::_('DONE');
			$result['result'] = JText::_('SAMPLE_TEMPLATES_ARE_LOADED_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['status'] = JText::_('ERROR');
			$result['result'] = JText::_('FAILED_LOADING_SAMPLE_TEMPLATES');
		}

		echo oseJSON::encode($result);
		exit;
	}
}


?>