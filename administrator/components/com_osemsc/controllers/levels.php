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


class oseMscControllerLevels extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function add()
	{
		$model = $this->getModel('levels');

		$level_name = JRequest::getString('level_name',null);


		$updated = $model->add($level_name);

		$result = $updated;


		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getList()
	{
		$model = $this->getModel('levels');

		$results = $model->getList();

		$result = array();

		if(count($results) > 0)
		{
			$result['total'] = count($results);
			$result['results'] = $results;
		}
		else
		{
			$result['total'] = 0;
			$result['results'] = array();
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}
}


