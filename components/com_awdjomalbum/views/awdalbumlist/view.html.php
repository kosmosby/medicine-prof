<?php

/**
 * @version 2.4
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/




// no direct access

defined('_JEXEC') or die('Restricted access');



jimport( 'joomla.application.component.view');



/**

 * HTML View class for the awdjomalbum component

 */

class AwdjomalbumViewAwdalbumlist extends JViewLegacy {

	function display($tpl = null) {

	require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');

	

		$mainframe= JFactory::getApplication();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$display_name 	= $config->get('display_name', 1);		
		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$wuid=$_REQUEST['wuid'];
		if(empty($wuid))
		{
			if($user->id)
			{
				$wuid=$user->id;
			}
		}
		if($wuid!='')

		{ 

			$sql="Select * from #__users where id=".$wuid; 

			$db->setQuery($sql);

			$userdatas=$db->loadObjectList(); 

			$userdata=$userdatas[0];

			$userid=$userdata->id;

			if((int)$displayName == 1) {
			$username=$userdata->username;
			}else{
			$username=$userdata->name;
			 }
			$this->assignRef('username', $username);  

			$this->assignRef('userid', $userid);  

		

					

			$query ='Select aj.* from #__awd_jomalbum as aj where aj.userid='.$wuid.' order by aj.created_date desc';

			//echo $query;

			$db->setQuery($query);

			$rows=$db->loadObjectList();

			 

			$this->assignRef('rows', $rows);  

			

			//$query="Select awi.* from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid;

			$where = array();

			

			$showPhotos = 1;



			if((int)$privacy == 1){



				if((int)$wuid != (int)$user->id){



					$showPhotos = JsLib::isFriend($user->id, $wuid);

				}

			}

			

			if((int)$privacy == 2){



				if((int)$wuid != (int)$user->id){



					$showPhotos = JsLib::isFriendOfFriend($user->id, $wuid);	

					//echo $showPhotos;

				}



			}

			

			if((int)$wuid != (int)$user->id){

				

				$friends = JsLib::isFriend($user->id, $wuid);

					if($friends)

						$where[]= 'aw.id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy = "1")';

						

				$fofriends = JsLib::isFriendOfFriend($user->id, $wuid);	

				

				if($fofriends)

					$where[]= 'aw.id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy="2")';

				

					$where[]= 'aw.id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy = "0")';

			}

			if($where)

			$where = count($where) ? ' OR ' . implode( ' OR ', $where ) : '';

			

			$whr=" and aw.id NOT IN (select wall_id from #__awd_wall_privacy order by wall_id)";

			if($where)

			$query="Select awi.* from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid.$whr.$where;

			else

			$query="Select awi.* from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid;

		//	echo $query;


			$db->setQuery($query);

			$photorows=$db->loadObjectList();

			$this->assignRef('photorows', $photorows);  

		
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$display_group = $config->get('display_group', 1);
			$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
			$moderator_users = $config->get('moderator_users', '');
			$moderator_users=explode(',',$moderator_users);
			
			$this->assignRef('display_group', $display_group);
			$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
			$this->assignRef('moderator_users', $moderator_users);

			parent::display($tpl);

		}

		else

		{

			echo JText::_('Opps! You are in wrong page');

		}

		 

		

    }

	

	

	

}

?>