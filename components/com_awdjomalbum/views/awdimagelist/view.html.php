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

class AwdjomalbumViewawdimagelist extends JViewLegacy {

	function display($tpl = null) { 

	

		global $mainframe;
		$app = JFactory::getApplication('site');
		$albumModel = & $this->getModel('awdjomalbum');
		$Itemid=AwdwallHelperUser::getComItemId();
		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$albumid=$_REQUEST['albumid'];

		$pid=$_REQUEST['pid'];

		// checking privacy

		$albumview=isalbumviewable($albumid);	

		

		if(!$albumview)

		{
			$msg= JText::_('User has set privacy.');
			
			$app->Redirect(JRoute::_('index.php?option=com_awdwall&amp;view=awdwall&amp;layout=main&amp;Itemid='.$Itemid,false),$msg);

		}

		

		

		if($albumid!='' && $pid!='')

		{

			//load images related to one album id

			$albumid=$_REQUEST['albumid'];		

			$imagequery= 'SELECT * from #__awd_jomalbum_photos where albumid='.$albumid.' '.' and id='.$pid.' order by id';

		 	$db->setQuery($imagequery);

			$photorows=$db->loadObjectList();

			$photorow=$photorows[0];
			if(count($photorow)==0)
			{
				$msg="It seems that image deleted by user";
				$app->Redirect(JRoute::_('index.php?option=com_awdwall&amp;view=awdwall&amp;layout=main&amp;Itemid='.$Itemid,false),$msg);
 				exit;
			}
			$this->assignRef('photorow', $photorow);


			

			$sql= 'SELECT count(*) from #__awd_jomalbum_photos where albumid='.$albumid;

		 	$db->setQuery($sql);

			

			$totRecord=$db->loadResult();

			$this->assignRef('totRecord',$totRecord);

			

			

			$sql="select count(*) from #__awd_jomalbum_photos where id <= ".$pid." and albumid=".$albumid." order by id asc ";

			//echo $sql;

			$db->setQuery($sql);

			$curPosition=$db->loadResult();

			$this->assignRef('curPosition',$curPosition);

			

			//load user name and user id to one album id

			$sql="Select u.*,aj.name as albumname from #__users as u left join #__awd_jomalbum as aj on u.id=aj.userid where aj.id=".$albumid;

			$db->setQuery($sql);

			$userdatas=$db->loadObjectList(); 

			$userdata=$userdatas[0];

			

			$userid=$userdata->id;

			$username=$userdata->username;

			$albumname=$userdata->albumname;

			$this->assignRef('albumname', $albumname);  

			

			$this->assignRef('username', $username);  

			$this->assignRef('userid', $userid);  

		

		

			//set prev and next record

			

				$sql="SELECT  (SELECT MAX(id) FROM #__awd_jomalbum_photos ph1 WHERE ph1.id < ph2.id ) as PreviousID,  (SELECT MIN(id)    FROM #__awd_jomalbum_photos ph1  WHERE     ph1.id >  ph2.id ) as NextID FROM #__awd_jomalbum_photos  ph2 WHERE  id =".$pid;

			$db->setQuery($sql);

			$results=$db->loadobjectList();

			$result=$results[0];

			

			$next=$result->NextID;

			$prev=$result->PreviousID;

		

			$sql1="SELECT id from #__awd_jomalbum_photos where albumid=".$albumid." order by id ASC LIMIT 1";

			$db->setQuery($sql1);

			$firstRec=$db->loadResult();

			

			$sql2="SELECT * from #__awd_jomalbum_photos where albumid=".$albumid." order by id DESC LIMIT 1";

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

			

			

		//comments

		$commentqry='SELECT ajc.*, u.id as uid, u.name as cname,u.username  FROM  #__awd_jomalbum_comment as ajc left join #__users as u on ajc.userid=u.id where ajc.photoid='.$pid.' order by ajc.id desc limit 5';

	 	$db->setQuery($commentqry);

		$commentrows=$db->loadObjectList();

		$commentqry='SELECT ajc.*, u.id as uid, u.name as cname,u.username  FROM  #__awd_jomalbum_comment as ajc left join #__users as u on ajc.userid=u.id where ajc.photoid='.$pid.' order by ajc.id desc';

	 	$db->setQuery($commentqry);

		$totalcommentrows=$db->loadObjectList();

		

		$tagqry='SELECT * from #__awd_jomalbum_tags where photoid='.$pid;

	 	$db->setQuery($tagqry);

		$tagrows=$db->loadObjectList();

		
		
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		

		//print_r($commentrows);

		$this->assignRef('totalcommentrows', $totalcommentrows); 

		$this->assignRef('commentrows', $commentrows); 

		$this->assignRef('tagrows', $tagrows); 

		$this->assignRef('pid', $pid); 

		

		

			

			parent::display($tpl);

		}

		else

		{

			echo JText::_('Opps! You are in wrong page');

		}



    }

	

}

?>