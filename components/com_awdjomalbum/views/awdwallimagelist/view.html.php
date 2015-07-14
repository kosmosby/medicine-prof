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

class AwdjomalbumViewawdwallimagelist extends JViewLegacy {

	function display($tpl = null) { 

	// require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');

		global $mainframe;

		$albumModel = & $this->getModel('awdjomalbum');

		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$wuid=$_REQUEST['wuid'];

		$pid=$_REQUEST['pid'];

		$config 		= &JComponentHelper::getParams('com_awdwall');

		$privacy 		= $config->get('privacy', 0);


		

		$friends = JsLib::isFriend($user->id, $wuid);

		$fofriends = JsLib::isFriendOfFriend($user->id, $wuid);	
		
		if($wuid!='' && $pid!='')
		{
			if($friends)
			$where= ' and wall_id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy = "0" or a.privacy = "1" or a.privacy = "2")';
			else if($fofriends)
			$where= ' and wall_id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy = "0" or a.privacy = "2")';
			else
			$where= ' and wall_id IN (select a.wall_id FROM #__awd_wall_privacy as a left join #__awd_wall as b on a.wall_id=b.id where b.user_id='.$wuid.' and a.privacy = "0")';
			if($user->id!=$wuid)
		 		$imagequery= 'SELECT * from #__awd_wall_images where id='.$pid.$where;
			else
		 		$imagequery= 'SELECT * from #__awd_wall_images where id='.$pid;
		 	$db->setQuery($imagequery);
			$photorows=$db->loadObjectList();
			$photorow=$photorows[0];
			$this->assignRef('photorow', $photorow);


			

			

			$sql="Select count(*) from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid;

			//echo $sql;

			 $db->setQuery($sql);

			$totRecord=$db->loadResult();

			$this->assignRef('totRecord',$totRecord);

			

			

			$sql="select count(*) from #__awd_wall_images as awi inner join #__awd_wall as aw on awi.wall_id=aw.id where awi.id <= ".$pid." and aw.user_id=".$wuid." order by awi.id asc ";

			//echo "<br>".$sql;

			$db->setQuery($sql);

			$curPosition=$db->loadResult();

			$this->assignRef('curPosition',$curPosition);

			

			//load user name and user id to one album id

			$sql="Select * from #__users where id=".$wuid; 

			$db->setQuery($sql);

			$userdatas=$db->loadObjectList(); 

			$userdata=$userdatas[0];

			$userid=$userdata->id;

			$username=$userdata->username;

			$this->assignRef('username', $username);  

			$this->assignRef('userid', $userid);

		

		

			//set prev and next record

			

				//$sql="SELECT  (SELECT MAX(id) FROM #__awd_wall_images ph1 WHERE ph1.id < ph2.id ) as PreviousID,  (SELECT MIN(id)    FROM #__awd_wall_images ph1  WHERE     ph1.id >  ph2.id ) as NextID FROM #__awd_wall_images  ph2 WHERE  id =".$pid;

			

		//	$sql="Select (SELECT MAX(ph1.id) FROM #__awd_wall_images as ph1 inner join #__awd_wall as aw on ph1.wall_id=aw.id  WHERE  ph1.id < ph2.id) FROM #__awd_wall_images as ph2 inner join #__awd_wall as aw on ph2.wall_id=aw.id WHERE  ph2.id =".$pid;

			

			//echo 	$sql."<br>";

			//$db->setQuery($sql);

		//	$results=$db->loadobjectList();

		//	$result=$results[0];

			//print_r($result);

			//exit;

			//$sql="SELECT  (SELECT MAX(id) FROM #__awd_wall_images ph1 WHERE ph1.id < ph2.id ) as PreviousID,  (SELECT MIN(id)    FROM #__awd_wall_images ph1  WHERE     ph1.id >  ph2.id ) as NextID FROM #__awd_wall_images  ph2 inner join #__awd_wall as aw on ph2.wall_id=aw.id WHERE  id =".$pid." and aw.user_id=".$wuid;

			$sql="SELECT  (SELECT MAX(ph1.id) FROM #__awd_wall_images  as ph1 inner join #__awd_wall as aw on ph1.wall_id=aw.id WHERE ph1.id < ph2.id and aw.user_id=".$wuid." ) as PreviousID,  (SELECT MIN(ph1.id) FROM #__awd_wall_images as ph1 inner join #__awd_wall as aw on ph1.wall_id=aw.id WHERE ph1.id >  ph2.id and aw.user_id=".$wuid."  ) as NextID FROM #__awd_wall_images as ph2 inner join #__awd_wall as aw on ph2.wall_id=aw.id WHERE  ph2.id =".$pid." and aw.user_id=".$wuid;

			

			//echo "<br>".$sql."<br>";

			$db->setQuery($sql);

			$results=$db->loadobjectList();

			$result=$results[0];

			

			  $next=$result->NextID;

			 

		 	 $prev=$result->PreviousID;

		

		

			//$sql="Select count(*) from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid;

			

			

			$sql1="SELECT awi.id from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid." order by awi.id ASC LIMIT 1";

		//	echo $sql1;

		//echo "<br>".$sql1;

			$db->setQuery($sql1);

			$firstRec=$db->loadResult();

		 

		 	$sql2="SELECT awi.id from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid." order by awi.id DESC LIMIT 1";

			//echo "<br>".$sql2;

			$db->setQuery($sql2);

			$lastRec=$db->loadResult();

 

 			if($pid==$firstRec)

			{

				$prev=$lastRec;

			}

			else if($pid==$lastRec)

			{

				$next=$firstRec;

			}

 			

			$this->assignRef('nextR', $next);  

			$this->assignRef('prevR', $prev);   

		//	echo "next=".$next."<br>";

		//	echo "prev=".$prev;

			

		//comments

		

		$query='SELECT wall_id FROM  #__awd_wall_images where id='.$pid;

		$db->setQuery($query);

		$wall_id=$db->loadResult();

		

		$sql="select  ajwc.*, u.id as uid, u.name as cname,u.username  from   #__awd_wall as ajwc left join #__users as u on ajwc.commenter_id=u.id where reply='".$wall_id."' order by wall_date desc limit 5";

		$db->setQuery( $sql );

		$commentrows = $db->loadObjectList();

		

		$sql="select  ajwc.*, u.id as uid, u.name as cname,u.username  from   #__awd_wall as ajwc left join #__users as u on ajwc.commenter_id=u.id where reply='".$wall_id."' order by wall_date desc ";

		$db->setQuery( $sql );

		$totalcommentrows = $db->loadObjectList();

		

		

		

		


		$tagqry='SELECT * from #__awd_jomalbum_wall_tags where photoid='.$pid;

	 	$db->setQuery($tagqry);

		$tagrows=$db->loadObjectList();


		$this->assignRef('totalcommentrows', $totalcommentrows); 

		$this->assignRef('commentrows', $commentrows); 

		$this->assignRef('tagrows', $tagrows); 

		$this->assignRef('pid', $pid); 

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

			echo JText::_( 'Opps! You are in wrong page');

		}



    }

	

}

?>