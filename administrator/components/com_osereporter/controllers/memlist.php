<?php
/**
  * @version       1.0 +
  * @package       Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Reporter - com_osereporter
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 24-May-2011
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
class osereporterControllerMemlist extends osereporterController
{
	function __construct()
	{
		parent :: __construct();
	}
	function getList()
	{
		$model= $this->getModel('memlist');
		$result= $model->getList();
		if(count($result['results']) < 1)
		{
			$result['total']= 0;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function getMscList()
	{
		$model= $this->getModel('memlist');
		$result= $model->getMscList();
		if(count($result['results']) < 1)
		{
			$result['total']= 0;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function getStartDateList()
	{
		$model= $this->getModel('memlist');
		$result= $model->getStartDateList();
		if(count($result['results']) < 1)
		{
			$result['total']= 0;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function getEndDateList()
	{
		$model= $this->getModel('memlist');
		$result= $model->getEndDateList();
		if(count($result['results']) < 1)
		{
			$result['total']= 0;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	function exportCsv()
	{
		$model= $this->getModel('memlist');
		$model->exportCsv();
		oseExit();
	}
	function exportCsvAll()
	{
		$model= $this->getModel('memlist');
		$model->exportCsvAll();
		oseExit();
	}
}
?>