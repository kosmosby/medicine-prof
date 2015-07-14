<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
error_reporting(0);
jimport( 'joomla.plugin.plugin' );


class plgUserplgawdwalldeletepost extends JPlugin
{
	function plgUserplgawdwalldeletepost( &$subject, $config )
	{
		

		$db = JFactory::getDBO();
		$query = "SELECT distinct user_id FROM #__awd_wall_content_comments ";
		$db->setQuery($query);
		$userids = $db->loadObjectList();
		foreach($userids as $t)
		{
			$uids[]=$t->user_id;
		}

		$query = "SELECT distinct user_id FROM #__awd_wall_comment_like ";
		$db->setQuery($query);
		$userids = $db->loadObjectList();
		foreach($userids as $t)
		{
			$uids[]=$t->user_id;
		}
		

		$query = "SELECT distinct userid FROM #__awd_wall_content_comment_like ";
		$db->setQuery($query);
		$userids = $db->loadObjectList();
		foreach($userids as $t)
		{
			$uids[]=$t->userid;
		}

		$query = "SELECT distinct user_id FROM #__awd_wall ";
		$db->setQuery($query);
		$userids = $db->loadObjectList();
		foreach($userids as $t)
		{
			$uids[]=$t->user_id;
		}
		
		$query = "SELECT distinct commenter_id FROM #__awd_wall ";
		$db->setQuery($query);
		$commenterids = $db->loadObjectList();
		foreach($commenterids as $t)
		{
			$uids[]=$t->commenter_id;
		}
		
		$uids=array_unique($uids);
		$query = "SELECT id FROM #__users ";
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		foreach($ids as $t)
		{
			$juids[]=$t->id;
		}
		for($i=0;$i<count($uids);$i++)
		{
			if(in_array($uids[$i],$juids))
			{
			 
			}
			else
			{
			 $deleteduserids[]=$uids[$i];
			}
		}
		for($i=0;$i<count($deleteduserids);$i++)
		{
			if(!empty($deleteduserids[$i]))
			{
			$query='select * FROM #__awd_wall WHERE user_id = ' .$deleteduserids[$i]. ' OR commenter_id = '.$deleteduserids[$i];
			$db->setQuery($query);
			$wids = $db->loadObjectList();
			foreach($wids as $wid)
			{

				$query 	= 'SELECT * FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$image = $db->loadObject();
				if($image)
				{
					@unlink('images/' . $wid->user_id . '/original/' . $image->path);
					@unlink('images/' . $wid->user_id . '/thumb/' . $image->path);
				}
				$query 	= 'DELETE FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				// delete videos and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$video = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($video->thumb)
					@unlink('images/' . $video->thumb);
				// delete mp3 and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$mp3 = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($mp3->path)
					@unlink('images' . DS . 'mp3' . DS . $wid->user_id . DS . $mp3->path);
				// delete link and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$link = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($link->path)
					@unlink('images' . DS . 'link' . DS . $wid->user_id . DS . $link->path);
				// delete file and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$file = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($file->path)
					@unlink('images' . DS . 'awdfiles' . DS . $wid->user_id . DS . $file->path);
				
				$query 	= 'DELETE FROM #__awd_wall_jing WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete events...
				$query 	= 'SELECT * FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$event = $db->loadObject();
				if($event->image)
				{
					@unlink('images/awd_events/' . $wid->user_id . '/original/' . $event->image);
					@unlink('images/awd_events/' . $wid->user_id . '/thumb/' . $event->image);
				}
				
				$query 	= 'DELETE FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				$query 	= 'DELETE FROM #__awd_wall_event_attend WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete articles...
				$query 	= 'SELECT * FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$article = $db->loadObject();
				if($article->image)
				{
					@unlink('images/awd_articles/' . $wid->user_id . '/original/' . $article->image);
					@unlink('images/awd_articles/' . $wid->user_id . '/thumb/' . $article->image);
				}
				$query 	= 'DELETE FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//Delete hwd wall videos
				$query 	= 'DELETE FROM #__awd_wall_videos_hwd WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				
				//Delete from privacy table
				$query 	= 'DELETE FROM #__awd_wall_privacy WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
			}
			$query= 'DELETE FROM #__awd_wall_content_comments WHERE user_id = '.$deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query= 'DELETE FROM #__awd_wall WHERE user_id = '.$deleteduserids[$i].' OR commenter_id = '.$deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_wall_comment_like WHERE user_id = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_connection WHERE connect_from = ' . $deleteduserids[$i].' or connect_to='. $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups WHERE creator = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups_members WHERE user_id = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment_like WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photos WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_like WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_wall_like WHERE 	userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_tags WHERE 	userid = ' . $deleteduserids[$i].' or taguserid='. $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_userinfo WHERE 	userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment WHERE 	userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment_like WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_tags WHERE userid = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_users WHERE user_id = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_facebook_users WHERE user_id = ' . $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
		
			//delete from notification table
			$query='DELETE FROM #__awd_wall_notification WHERE ncreator = ' . $deleteduserids[$i]. 'OR nuser='. $deleteduserids[$i];
			$db->setQuery($query);
			$db->query();
			}
		
	}
	parent::__construct( $subject, $config );
}
	
	function onAfterDeleteUser($user, $succes, $msg)
	{
			$db = JFactory::getDBO();
			$query= 'DELETE FROM #__awd_wall_content_comments WHERE user_id = '.$user['id'];
			$db->setQuery($query);
			$db->query();
			$query='select * FROM #__awd_wall WHERE user_id = ' .$user['id']. ' OR commenter_id = '.$user['id'];
			$db->setQuery($query);
			$wids = $db->loadObjectList();
			foreach($wids as $wid)
			{

				$query 	= 'SELECT * FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$image = $db->loadObject();
				if($image)
				{
					@unlink('images/' . $wid->user_id . '/original/' . $image->path);
					@unlink('images/' . $wid->user_id . '/thumb/' . $image->path);
				}
				$query 	= 'DELETE FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				// delete videos and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$video = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($video->thumb)
					@unlink('images/' . $video->thumb);
				// delete mp3 and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$mp3 = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($mp3->path)
					@unlink('images' . DS . 'mp3' . DS . $wid->user_id . DS . $mp3->path);
				// delete link and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$link = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($link->path)
					@unlink('images' . DS . 'link' . DS . $wid->user_id . DS . $link->path);
				// delete file and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$file = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($file->path)
					@unlink('images' . DS . 'awdfiles' . DS . $wid->user_id . DS . $file->path);
				
				$query 	= 'DELETE FROM #__awd_wall_jing WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete events...
				$query 	= 'SELECT * FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$event = $db->loadObject();
				if($event->image)
				{
					@unlink('images/awd_events/' . $wid->user_id . '/original/' . $event->image);
					@unlink('images/awd_events/' . $wid->user_id . '/thumb/' . $event->image);
				}
				
				$query 	= 'DELETE FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				$query 	= 'DELETE FROM #__awd_wall_event_attend WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete articles...
				$query 	= 'SELECT * FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$article = $db->loadObject();
				if($article->image)
				{
					@unlink('images/awd_articles/' . $wid->user_id . '/original/' . $article->image);
					@unlink('images/awd_articles/' . $wid->user_id . '/thumb/' . $article->image);
				}
				$query 	= 'DELETE FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//Delete hwd wall videos
				$query 	= 'DELETE FROM #__awd_wall_videos_hwd WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
			}
			$query= 'DELETE FROM #__awd_wall WHERE user_id = '.$user['id'].' OR commenter_id = '.$user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_wall_comment_like WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_connection WHERE connect_from = ' . $user['id'].' or connect_to='. $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups WHERE creator = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups_members WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photos WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_wall_like WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_tags WHERE 	userid = ' . $user['id'].' or taguserid='. $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_userinfo WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_tags WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_users WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_facebook_users WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
	 	// only the $user['id'] exists and carries valid information
		
		

		// Call a function in the external app to delete the user
		// ThirdPartyApp::deleteUser($user['id']);
	}
	
	function onLoginUser($user, $options)
	{
		// Initialize variables
		$success = true;
		// ThirdPartyApp::loginUser($user['username'], $user['password']);

		return $success;
	}
	
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$app = JFactory::getApplication();
		$db =& JFactory::getDBO();
			$query= 'DELETE FROM #__awd_wall_content_comments WHERE user_id = '.$user['id'];
			$db->setQuery($query);
			$db->query();
			$query='select * FROM #__awd_wall WHERE user_id = ' .$user['id']. ' OR commenter_id = '.$user['id'];
			$db->setQuery($query);
			$wids = $db->loadObjectList();
			foreach($wids as $wid)
			{

				$query 	= 'SELECT * FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$image = $db->loadObject();
				if($image)
				{
					@unlink('images/' . $wid->user_id . '/original/' . $image->path);
					@unlink('images/' . $wid->user_id . '/thumb/' . $image->path);
				}
				$query 	= 'DELETE FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				// delete videos and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$video = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($video->thumb)
					@unlink('images/' . $video->thumb);
				// delete mp3 and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$mp3 = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($mp3->path)
					@unlink('images' . DS . 'mp3' . DS . $wid->user_id . DS . $mp3->path);
				// delete link and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$link = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($link->path)
					@unlink('images' . DS . 'link' . DS . $wid->user_id . DS . $link->path);
				// delete file and forlder too
				$query 	= 'SELECT * FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid;
				$db->setQuery($query);
				$file = $db->loadObject();
				$query 	= 'DELETE FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				if($file->path)
					@unlink('images' . DS . 'awdfiles' . DS . $wid->user_id . DS . $file->path);
				
				$query 	= 'DELETE FROM #__awd_wall_jing WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete events...
				$query 	= 'SELECT * FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$event = $db->loadObject();
				if($event->image)
				{
					@unlink('images/awd_events/' . $wid->user_id . '/original/' . $event->image);
					@unlink('images/awd_events/' . $wid->user_id . '/thumb/' . $event->image);
				}
				
				$query 	= 'DELETE FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				$query 	= 'DELETE FROM #__awd_wall_event_attend WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//delete articles...
				$query 	= 'SELECT * FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$article = $db->loadObject();
				if($article->image)
				{
					@unlink('images/awd_articles/' . $wid->user_id . '/original/' . $article->image);
					@unlink('images/awd_articles/' . $wid->user_id . '/thumb/' . $article->image);
				}
				$query 	= 'DELETE FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
				//Delete hwd wall videos
				$query 	= 'DELETE FROM #__awd_wall_videos_hwd WHERE wall_id = ' . (int)$wid->id;
				$db->setQuery($query);
				$db->query();
				
			}
			$query= 'DELETE FROM #__awd_wall WHERE user_id = '.$user['id'].' OR commenter_id = '.$user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_wall_comment_like WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_connection WHERE connect_from = ' . $user['id'].' or connect_to='. $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups WHERE creator = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_groups_members WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_comment_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photos WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query 	= 'DELETE FROM #__awd_jomalbum_photo_wall_like WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_tags WHERE 	userid = ' . $user['id'].' or taguserid='. $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_userinfo WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment WHERE 	userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_comment_like WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_jomalbum_wall_tags WHERE userid = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_users WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
			$query='DELETE FROM #__awd_wall_facebook_users WHERE user_id = ' . $user['id'];
			$db->setQuery($query);
			$db->query();
		// only the $user['id'] exists and carries valid information

		// Call a function in the external app to delete the user
		// ThirdPartyApp::deleteUser($user['id']);
	}
	
	public function onUserLogin($user, $options)
	{
		// Initialise variables.
		$success = true;
		
		// ThirdPartyApp::loginUser($user['username'], $user['password']);

		return $success;
	}


	
}
