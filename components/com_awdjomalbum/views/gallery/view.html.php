<?php
// no direct access

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');

class AwdjomalbumViewgallery extends JViewLegacy {

	function display($tpl = null) {

		require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');			
		$db		=& JFactory :: getDBO();
		$user =& JFactory::getUser();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$listingperpage 		= $config->get('listingperpage', 5);
		$imagelimit 		= $config->get('imagelimit',3);
		
		$privacy=$_REQUEST['privacy'];
		//$listingperpage=1;
		$page 			= JRequest::getCmd('awd_page', 0);
		$offset = $page*$listingperpage;
		if(empty($privacy))
		{
			$privacy=0;
		}
		
		if($privacy==0)
		{
		
//			$query= "SELECT DISTINCT (commenter_id) FROM #__awd_wall inner join #__awd_wall_privacy on  #__awd_wall.id=#__awd_wall_privacy.wall_id WHERE `type`='image' and privacy=".$privacy." order by id desc";
//			$db->setQuery($query);
//			$user_rows=$db->loadObjectList();
//			if(!empty($user_rows))
//			{
//			foreach($user_rows as $user_row)
//			{
//			$userids[]=$user_row->commenter_id;
//			}
//			}
			$query= "SELECT count(DISTINCT (#__awd_jomalbum.userid)) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." order by #__awd_jomalbum_photos.id desc ";
			$db->setQuery($query);
			$nooffeeds=$db->loadResult();

			$query= "SELECT DISTINCT (#__awd_jomalbum.userid) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." order by #__awd_jomalbum_photos.id desc LIMIT " . $offset . ", "  . $listingperpage;
			$db->setQuery($query);
			$user_rows1=$db->loadObjectList();
			if(!empty($user_rows1))
			{
				foreach($user_rows1 as $user_row)
				{
					$userids[]=$user_row->userid;
				}
			}
			//print_r($userids);
			if(!empty($userids))
			{
				$userids=array_unique($userids);
			
			}
		
		}
		
		if($privacy==1)
		{
			$query = 'SELECT connect_to FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '
					.'WHERE connect_from = ' . (int)$user->id . ' '
					.'AND status = 1 AND pending = 0 ';
			$db->setQuery($query);
			$friends=$db->loadObjectList();
			if(!empty($friends))
			{
				foreach($friends as $friend)
				{
					$useridsarray[]=$friend->connect_to;
				}
				$useridstr =implode(',',$useridsarray);
				
			$query= "SELECT count(DISTINCT (#__awd_jomalbum.userid)) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and #__awd_jomalbum.userid in (".$useridstr.") order by #__awd_jomalbum_photos.id desc";
			$db->setQuery($query);
			$nooffeeds=$db->loadResult();
				
				
				$query= "SELECT DISTINCT (#__awd_jomalbum.userid) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and #__awd_jomalbum.userid in (".$useridstr.") order by #__awd_jomalbum_photos.id desc  LIMIT " . $offset . ", "  . $listingperpage;
				//echo $query;
				$db->setQuery($query);
				$user_rows1=$db->loadObjectList();
				if(!empty($user_rows1))
				{
					foreach($user_rows1 as $user_row)
					{
						$userids[]=$user_row->userid;
					}
				}
				$this->assignRef('useridstr', $useridstr);

			}
			
			
		}
		if($privacy==2)
		{

			$query = 'SELECT connect_to FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '
					.'WHERE connect_from = ' . (int)$user->id . ' '.'AND status = 1 AND pending = 0 ';
			$db->setQuery($query);
			$friends=$db->loadObjectList();
			if(!empty($friends))
			{
				foreach($friends as $friend)
				{
					$friendsarray[]=$friend->connect_to;
					$fquery = 'SELECT connect_to FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '.' WHERE connect_from = ' . (int)$friend->connect_to . ' '.'AND status = 1 AND pending = 0 ';
					//echo $fquery;
					$db->setQuery($fquery);
					$ffriends=$db->loadObjectList();
					foreach($ffriends as $ffriend)
					{	if($ffriend->connect_to!=$user->id)
						{
							$friendsarray[]=$ffriend->connect_to;
						}
					}
					
				}
				$useridstr =implode(',',$friendsarray);
				
				$query= "SELECT count(DISTINCT (#__awd_jomalbum.userid)) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE privacy=1 and #__awd_jomalbum.userid in (".$useridstr.") order by #__awd_jomalbum_photos.id desc ";
			$db->setQuery($query);
			$nooffeeds=$db->loadResult();
				
				
				$query= "SELECT DISTINCT (#__awd_jomalbum.userid) FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE
privacy=1 and #__awd_jomalbum.userid in (".$useridstr.") order by #__awd_jomalbum_photos.id desc LIMIT " . $offset . ", "  . $listingperpage;
				//echo $query;
				$db->setQuery($query);
				$user_rows1=$db->loadObjectList();
				if(!empty($user_rows1))
				{
					foreach($user_rows1 as $user_row)
					{
						$userids[]=$user_row->userid;
					}
				}
				
				$this->assignRef('useridstr', $useridstr);

			}
			//print_r($userids);
		}
		
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		//print_r($userids);
		$this->assignRef('userids', $userids);
		$this->assignRef('privacy', $privacy);
		$this->assignRef('page', $page);
		$this->assignRef('nooffeeds', $nooffeeds);
		
        parent::display($tpl);

    }

}

?>
