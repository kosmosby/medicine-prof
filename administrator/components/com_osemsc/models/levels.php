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


class oseMscModelLevels extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function

	function add($level_name)
	{
		$db = oseDB::instance();

		$level_name = $db->Quote($level_name);
		$query = "SELECT * FROM `#__groups` WHERE `name` = " . $level_name;

		$db->setQuery($query);

		$item = $db->loadObject();

		$result = array();

		if (empty ($item))
		{
			$query = " SELECT id FROM `#__groups` "
					." ORDER BY id DESC"
					;
			$db->setQuery($query);
			$oid = $db->loadResult();
			$nid = $oid + 1;

			$query = "INSERT INTO `#__groups` (`id`,`name`) VALUES ({$nid}, {$level_name});";

			$db->setQuery($query);

			if (oseDB::query()) {
				$result['success'] = true;
				$result['title'] = 'Done';
				$result['content'] = JText :: _("Added successfully");
			}
			else
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = JText :: _("Failed adding ACL group.");
			}

		}
		else
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _("Failed adding ACL group.");
		}

		return $result;
	}

	function getList()
	{
		$db	= oseDB::instance();

		$query='SELECT * FROM `#__groups` AS a ORDER by a.id ASC';

		$db->setQuery($query);

		$results = oseDB::loadList();

		return $results;
	}


	function update_useraid() {

		$user = & JFactory :: getUser();

		$db = & JFactory :: getDBO();

		$query = "SELECT msc_id FROM `#__osemsc_member` WHERE `member_id` = " . (int) $user->id;

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$mscids = array ();

		$query = "SELECT id FROM `#__viewlevels`";

		$db->setQuery($query);

		$results2 = $db->loadObjectList();

		if (!empty ($results)) {

			$aid = array ();

			foreach ($results as $result) {

				foreach ($results2 as $result2) {

					$viewarray = OSEJACCESS :: get_viewaccess($result2->id);



					if (in_array($result->msc_id, $viewarray)) {

						$aid[] = $result2->id;

					}

				}

			}

			if (!empty($aid))

			{

				return max($aid);

			}

			else

			{

				return false;

			}

		}

	}



function get_msc_aid($msc_id) {

		$user = & JFactory :: getUser();

		$db = & JFactory :: getDBO();



		$query = "SELECT id FROM `#__viewlevels`";

		$db->setQuery($query);

		$results2 = $db->loadObjectList();



		if (!empty ($results2)) {

			$aid = array ();

			foreach ($results2 as $result2) {

				$viewarray = OSEJACCESS :: get_viewaccess($result2->id);

				if (in_array($msc_id, $viewarray)) {

						$aid[] = $result2->id;

				}

			}

			if (!empty($aid))

			{return max($aid);}

			else

			{return "2";}

		}

	}

	function remove_group($id) {

		$db = & JFactory :: getDBO();

		$query = "DELETE FROM `#__viewlevels` WHERE `id` = " . (int) $id;

		$db->setQuery($query);

		$db->query();

		$query = "DELETE FROM `#__groups` WHERE `id` = " . (int) $id;

		$db->setQuery($query);

		if ($db->query()) {

			echo JText :: _("Deleting groups successfully");

			exit;

		} else {

			echo JText :: _("Failed deleting groups.");

			exit;

		}

	}

	function get_viewaccess($acl_id) {

		$db = & JFactory :: getDBO();

		$query = "SELECT rules FROM `#__viewlevels` WHERE `id`= " . (int) $acl_id;

		$db->setQuery($query);

		$results = $db->loadResult();

		$arrays = array ();

		if (!empty ($results)) {

			$arrays = substr($results, 1, -1);

			$arrays = explode(",", $arrays);

		}

		return $arrays;

	}



	function update_viewaccess($msc_id, $acl_id) {

		$cr_mscids = OSEJACCESS :: get_viewaccess($acl_id);

		$db = & JFactory :: getDBO();

		if (empty ($cr_mscids)) {

			$query = "INSERT INTO `#__viewlevels` (`id` ,`title` ,`ordering` ,`rules`)

			VALUES ('{$acl_id}', '', '0', '[{$msc_id[0]}]');";

			$db->setQuery($query);



			if ($db->query()) {

				echo JText :: _("Successfully added the membership to the access level");

				exit;

			}

		} else {

			if (!in_array($msc_id[0], $cr_mscids)) {

				if (count($cr_mscids) == 1 && $cr_mscids[0] == "") {

					$newmscids = $msc_id[0];

				} else {

					$newmscids = array_merge($cr_mscids, $msc_id);

					$newmscids = implode(",", $newmscids);

				}

				$query = "UPDATE `#__viewlevels` SET `rules` = '[{$newmscids}]' WHERE `id` ={$acl_id};";

				$db->setQuery($query);

				if ($db->query()) {

					echo JText :: _("Successfully updated the membership to the access level");

					exit;

				}

			} else {

				$newmscids = array_diff($cr_mscids, $msc_id);

				$newmscids = implode(",", $newmscids);

				$query = "UPDATE `#__viewlevels` SET `rules` = '[{$newmscids}]' WHERE `id` ={$acl_id};";

				$db->setQuery($query);

				if ($db->query()) {

					echo JText :: _("Successfully updated the membership to the access level");

					exit;

				}

			}

		}



	}



	function get_componentacl($option, $user_id)

	{

		$db=&JFactory::getDBO();



		if (!class_exists("OSEMEMBER"))

		{

		    require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."warehouse".DS."ExtAPIs".DS."member.php");

		}

		$query = "SELECT msc_id FROM `#__osemsc_content_basic` as a LEFT JOIN `#__components` as b ON a.content_id = b.id WHERE b.option='{$option}'  AND a.content_type='com'";

		$db->setQuery($query);

		$results = $db->loadObjectList();

		if (!empty($results))

		{       $return = false;

			foreach ($results as $result)

			{

			   $member_info = OSEMEMBER::get_member_info($result->msc_id, $user_id);

			   if (!empty($member_info))

			   {

			   $return=true; break;

			   }



			}

		       return $return;

	       }

 	       else {return true;}

	}




}


