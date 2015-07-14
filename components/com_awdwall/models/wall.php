<?php
/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

class AwdwallModelWall extends JModelLegacy
{
	public function getAllMsg($limit, $where, $offset = 0, $privacy = 0)
	{
		$db 	= &JFactory::getDBO();
		$query  = '';
		if(!(int)$privacy){
			$query 	= 'SELECT * FROM #__awd_wall '					
					.$where . ' '
					.'ORDER BY id DESC LIMIT ' . $offset . ', '  . $limit;
		}else{
			$query 	= 'SELECT * FROM #__awd_wall '					
					.$where . ' '
					.'ORDER BY id DESC LIMIT ' . $offset . ', '  . $limit;
		}
		//echo $query;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function countMsg($where, $privacy = 0)
	{
		$db		= &JFactory::getDBO();
		$query 	= 'SELECT COUNT(*) FROM #__awd_wall'
				.$where;								
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getwallpostowner($wallId)
	{
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT  commenter_id FROM #__awd_wall where id='.$wallId;	
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getAllFriends($limit, $where, $offset = 0)
	{
//		$db 	= &JFactory::getDBO();
//		$query 	= 'SELECT * FROM #__comprofiler_members '				
//				.$where . ' '
//				.'ORDER BY membersince DESC LIMIT ' . $offset . ', '  . $limit;
//		$db->setQuery($query);
//		return $db->loadObjectList();
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '				
				.$where . ' '
				.'ORDER BY connection_id DESC LIMIT ' . $offset . ', '  . $limit;
		$db->setQuery($query);
		return $db->loadObjectList();

	}
	
	public function countFriends($where)
	{
//		$db		= &JFactory::getDBO();
//		$query 	= 'SELECT COUNT(*) FROM #__comprofiler_members '
//				.$where;								
//		$db->setQuery($query);
//		return $db->loadResult();

		$db		= &JFactory::getDBO();
		$query 	= 'SELECT COUNT(*) FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '
				.$where;								
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function getAllCommentOfMsg($limit, $wallId, $offset = 0)
	{		
		$where = array();
		$where[] = 'reply = ' . (int)$wallId;
		$where[] = 'type = "text"';
		$where[] = 'is_pm = 0';
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall '					
				.$where . ' '
				.'ORDER BY id DESC LIMIT ' . $offset . ', '  . $limit;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getrealtimecomment($wallId)
	{		
		$currenttime=time();
		$currenttime=$currenttime-14;
		$user = &JFactory::getUser();
		$where = array();
		$where[] = 'reply = ' . (int)$wallId;
		$where[] = 'type = "text"';
		$where[] = 'is_pm = 0';
		$where[] = 'is_pm = 0';
		$where[] = "wall_date >= '".$currenttime."'";
		$where[] = "commenter_id != '".$user->id."'";
		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall '					
				.$where . ' '
				.'ORDER BY id DESC ';
				//echo $query ;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function countComment($wallId)
	{
		$db		= &JFactory::getDBO();
		$where = array();
		$where[] = 'reply = ' . (int)$wallId;
		$where[] = 'type = "text"';
		$where[] = 'is_pm = 0';		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$query 	= 'SELECT COUNT(*) FROM #__awd_wall'
				.$where;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function deleteMsg($wid)
	{
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall'	
				.' WHERE id = ' . (int)$wid
				;
		$db->setQuery($query);
		$wall = $db->loadObject();
		
		$query 	= 'DELETE FROM #__awd_wall WHERE id = ' . (int)$wid . ' OR reply = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		// delete images
		$query 	= 'SELECT * FROM #__awd_wall_images'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$image = $db->loadObject();
		@unlink('images/' . $wall->user_id . '/original/' . $image->path);
		@unlink('images/' . $wall->user_id . '/thumb/' . $image->path);
		$query 	= 'DELETE FROM #__awd_wall_images WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		// delete videos and forlder too
		$query 	= 'SELECT * FROM #__awd_wall_videos'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$video = $db->loadObject();
		$query 	= 'DELETE FROM #__awd_wall_videos WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		@unlink('images/' . $video->thumb);
		// delete mp3 and forlder too
		$query 	= 'SELECT * FROM #__awd_wall_mp3s'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$mp3 = $db->loadObject();
		$query 	= 'DELETE FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		@unlink('images' . DS . 'mp3' . DS . $wall->user_id . DS . $mp3->path);
		// delete link and forlder too
		$query 	= 'SELECT * FROM #__awd_wall_links'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$link = $db->loadObject();
		$query 	= 'DELETE FROM #__awd_wall_links WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		@unlink('images' . DS . 'link' . DS . $wall->user_id . DS . $link->path);
		// delete file and forlder too
		$query 	= 'SELECT * FROM #__awd_wall_files'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$file = $db->loadObject();
		$query 	= 'DELETE FROM #__awd_wall_files WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		@unlink('images' . DS . 'awdfiles' . DS . $wall->user_id . DS . $file->path);
		
		$query 	= 'DELETE FROM #__awd_wall_jing WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		
		//delete events...
		$query 	= 'SELECT * FROM #__awd_wall_events'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$event = $db->loadObject();
		@unlink('images/awd_events/' . $wall->user_id . '/original/' . $event->image);
		@unlink('images/awd_events/' . $wall->user_id . '/thumb/' . $event->image);
		$query 	= 'DELETE FROM #__awd_wall_events WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		$query 	= 'DELETE FROM #__awd_wall_event_attend WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		
		//delete articles...
		$query 	= 'SELECT * FROM #__awd_wall_article'	
				.' WHERE wall_id = ' . (int)$wid
				;
		$db->setQuery($query);
		$article = $db->loadObject();
		@unlink('images/awd_articles/' . $wall->user_id . '/original/' . $article->image);
		@unlink('images/awd_articles/' . $wall->user_id . '/thumb/' . $article->image);
		$query 	= 'DELETE FROM #__awd_wall_article WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		//Delete article from joomla 		
//		$query 	= 'DELETE FROM #__content WHERE id = ' . (int)$article->article_id;
//		$db->setQuery($query);
//		$db->query();
		
		
		$query 	= 'DELETE FROM #__awd_wall_videos_hwd WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		
		//delete trail
		$query 	= 'DELETE FROM #__awd_wall_trail WHERE wall_id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
		
		$query 	= 'DELETE FROM #__awd_wall_privacy WHERE wall_id = '  . (int)$wid;
		$db->setQuery($query);
		$db->query();
		
		//Delete from notification table
		$query = "DELETE FROM #__awd_wall_notification WHERE nwallid=".(int)$wid;
		$db->setQuery($query);
		$db->query();
		
		//Delete from wall_tweets table
		$query = "DELETE FROM #__awd_wall_tweets WHERE wall_id=".(int)$wid;
		$db->setQuery($query);
		$db->query();

	}
	
	public function deleteComment($wid)
	{
		$db 	= &JFactory::getDBO();
		$query 	= 'DELETE FROM #__awd_wall WHERE id = ' . (int)$wid;
		$db->setQuery($query);
		$db->query();
	}
	
	public function countpm($userId)
	{
		$db		= &JFactory::getDBO();
		$query 	='SELECT COUNT(*) FROM #__awd_wall_notification WHERE ntype="pm" AND nuser='.(int)$userId;
		//echo $query ;exit;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function getLikeOfMsgOfUser($wid,$user_id)
	{		
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;	
		$where[] = 'user_id = ' . (int)$user_id;	
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT count(*) as count FROM #__awd_wall_comment_like '					
				.$where ;
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getLikeOfMsg($wid)
	{		
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;	
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_comment_like '					
				.$where . ' '
				.'ORDER BY user_id DESC';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getAttendOfMsg($wid)
	{		
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;	
		$where[] = 'status = 1';	
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_event_attend '					
				.$where . ' '
				.'ORDER BY user_id DESC';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getEventOfMsgOfUser($wid,$user_id)
	{		
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;	
		$where[] = 'user_id = ' . (int)$user_id;	
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT count(*) as count FROM #__awd_wall_event_attend '					
				.$where ;
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getLatestPostByUserId($userId, $groupid)
	{
		$where = array();
		$where[] = 'commenter_id = ' . (int)$userId;
		$where[] = 'user_id = ' . (int)$userId;
		$where[] = 'is_pm = 0';
		$where[] = 'reply = 0';
		$where[] = 'type = "text"';
		if($groupid){
			$where[] = 'group_id = '. (int)$groupid;
		}else{
			$where[] = 'group_id is NULL';
		}
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall'					
				.$where . ' '
				.'ORDER BY id DESC LIMIT 1';
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	//dangcv show status group
	public function statusgroup($groupid, $userid){
		$db 	= &JFactory::getDBO();
		//$query 	= "SELECT #__awd_wall.* FROM #__awd_wall, #__awd_groups_members, #__awd_groups WHERE #__awd_wall.group_id = '$groupid' AND #__awd_groups_members.group_id = '$groupid' AND #__awd_wall.user_id = #__awd_groups_members.user_id AND #__awd_groups.id = '$groupid' AND creator = '$userid' ORDER BY #__awd_wall.id DESC LIMIT 1";
		//$query 	= "SELECT jos_awd_wall.* FROM jos_awd_wall, jos_awd_groups_members, jos_awd_groups WHERE jos_awd_wall.group_id = '$groupid' AND jos_awd_groups_members.group_id = '$groupid' AND jos_awd_wall.user_id = '$userid' AND jos_awd_groups_members.user_id = '$userid' AND jos_awd_groups.id = '$groupid' AND creator = '$userid' ORDER BY jos_awd_wall.id DESC LIMIT 1";
		$query 	= "SELECT #__awd_wall.* FROM #__awd_wall, #__awd_groups WHERE #__awd_wall.group_id = '$groupid' AND #__awd_wall.user_id = '$userid' AND #__awd_groups.id = '$groupid' AND creator = '$userid' ORDER BY #__awd_wall.id DESC LIMIT 1";
		$db->setQuery($query);
		return $db->loadObject();
	}
	public function showstatus($groupid){
		$db 	= &JFactory::getDBO();
		$query 	= "SELECT #__awd_wall.* FROM #__awd_wall, #__awd_groups WHERE group_id='$groupid' AND #__awd_groups.id='$groupid' AND creator=user_id AND type = 'text' ORDER BY #__awd_wall.id DESC LIMIT 1";
		$db->setQuery($query);
		return $db->loadObject();
	}
	//dangcv end
	
	public function getVideoInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_videos '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function getImageInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_images '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function getMp3InfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_mp3s '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function getLinkInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_links '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function getFileInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_files '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	public function getTrailInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_trail '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	public function getJingInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_jing '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function getEventInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_events '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	public function getArticleInfoByWid($wid)
	{
		$where = array();
		$where[] = 'wall_id = ' . (int)$wid;		
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall_article '					
				.$where;		
		$db->setQuery($query);
		return $db->loadObject();
	}
	public function getAllGrpMsg($limit, $where, $offset = 0)
	{
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT * FROM #__awd_wall '					
				.$where . ' '
				.'ORDER BY id DESC LIMIT ' . $offset . ', '  . $limit;
	
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function countGrpMsg($where)
	{
		$db		= &JFactory::getDBO();
		$query 	= 'SELECT COUNT(*) FROM #__awd_wall'
				.$where;								
		$db->setQuery($query);
		return $db->loadResult();
	}
}

class TableWall extends JTable 
{
	//Table's fields
	var $id 			= null;
	var $user_id		= null;
	var $group_id		= null;
	var $type 			= null;
  	var $commenter_id 	= null;
	var $user_name 	    = null;
  	var $avatar 		= null;
  	var $message 		= null;  
	var $reply 			= null;		
	var $is_read		= null;
	var $is_pm			= null;
	var $is_reply 		= null;	
	var $posted_id		= null;
	var $wall_date		= null;	
	
	function __construct(&$db)
	{
		parent::__construct('#__awd_wall', 'id', $db);
	}
}

class TableWallImage extends JTable 
{
	//Table's fields
	var $id 			= null;
	var $wall_id		= null;
	var $name 			= null;
  	var $path 			= null;
	var $description 	= null;	
	
	function __construct(&$db)
	{
		parent::__construct('#__awd_wall_images', 'id', $db);
	}
}