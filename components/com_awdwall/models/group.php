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

class AwdwallModelGroup extends JModelLegacy
{
	public function getAllGrps($where, $limit, $offset = 0)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT ag.* FROM #__awd_groups AS ag '
				.'LEFT JOIN #__awd_groups_members AS agm ON agm.group_id = ag.id '
				.$where . ' ' 
				.'GROUP BY id '
				.'ORDER BY id DESC LIMIT ' . $offset . ', '  . $limit;
	
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getMyGrps($userId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT * FROM #__awd_groups '					
				 .'WHERE creator = ' . (int)$userId . ' '
				 .'ORDER BY title';
	
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function checkGrpOwner($userId, $groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT id FROM #__awd_groups '					
				 .'WHERE creator = ' . (int)$userId . ' AND id = ' . (int)$groupId
				 ;
		
		$db->setQuery($query);
		$result = $db->loadResult();
		if(isset($result) && (int)$result > 0)
			return true;
		else
			return false;		
	}
	
	public function getGroupInfo($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT * FROM #__awd_groups '					
				 .'WHERE id = ' . (int)$groupId
				 ;
	
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	public function isPriaveGrp($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT privacy FROM #__awd_groups '					
				 .'WHERE id = ' . (int)$groupId
				 ;
	
		$db->setQuery($query);
		$result = $db->loadResult();
		if($result && (int)$result == 2){
			return true;
		}else{
			return false;
		}
	}
	
	public function isMemberGrp($groupId, $userId)
	{
		
		$groupsUserIsIn = JAccess::getGroupsByUser($userId);
		if(in_array(7,$groupsUserIsIn) || in_array(8,$groupsUserIsIn))
		{
		 	return true;
		}
		
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT user_id FROM #__awd_groups_members '					
				 .'WHERE group_id = ' . (int)$groupId . ' AND user_id = ' . (int)$userId
				 .' AND status="1"'
				 ;
	
		$db->setQuery($query);
		$result = $db->loadResult();
		
		$config 	= &JComponentHelper::getParams('com_awdwall');
		$moderator_users 	= $config->get('moderator_users', '');
		$moderator_type 	= $config->get('moderator_type', '0');
		
		$moderator_users=explode(',',$moderator_users);
		if($moderator_type==0)
		{
			if(in_array($userId,$moderator_users))
			{
				return true;
			}
		}
		
		if($result && (int)$result > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function joinGroup($groupId, $userId)
	{
		$db = &JFactory::getDBO();
		$query = 'INSERT INTO #__awd_groups_members(group_id, user_id, status, created_date) VALUES('.$groupId.','.$userId. ', 1, "' . time() . '")';
		$db->setQuery($query);
		$db->query();
	}
	
	public function getAllMemberByGrp($groupId, $groupsLimit)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT * FROM #__awd_groups_members '					
				 .'WHERE group_id = ' . (int)$groupId . ' AND status = 1 LIMIT 0, ' . $groupsLimit
				 ;
	
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	//dangcv show member creator
	public function getMembercreatorByGrp($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= "SELECT * FROM #__awd_groups WHERE id = '$groupId'";
	
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function deleteGroup($groupId, $type)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT * FROM #__awd_groups_members '					
				 .'WHERE group_id = ' . (int)$groupId . ' LIMIT 0, ' . $groupsLimit
				 ;
		$db->setQuery($query);
	}
	
	public function countMemGrp($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT COUNT(*) FROM #__awd_groups_members '					
				 .'WHERE group_id = ' . (int)$groupId . ' AND status = 1'
				 ;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function countPostGrp($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT COUNT(*) FROM #__awd_wall '					
				 .'WHERE group_id = ' . (int)$groupId
				 ;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function getGroupsFromMem($userId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT group_id FROM #__awd_groups_members '					
				 .'WHERE user_id 	 = ' . (int)$userId . ' AND status = 1'
				 ;
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(count($ids)>0)
		{
			$arrTmp = array();
			if(isset($ids[0])){
				foreach($ids as $id){
					$arrTmp[] = $id->group_id;
				}
			}
		
		
			$db 	= &JFactory::getDBO();	
			$query 	= 'SELECT * FROM #__awd_groups '					
					 .'WHERE id IN (' . implode(',', $arrTmp) . ') '
					 .'ORDER BY title';
		
			$db->setQuery($query);
			
			return $db->loadObjectList();
		}
	}
	
	function getPendingApproval($userId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT ag.*, gm.user_id FROM #__awd_groups_members AS gm '
				 .'INNER JOIN #__awd_groups AS ag ON ag.id = gm.group_id '
				 .'WHERE gm.user_id = ' . (int)$userId . ' AND gm.status = 2'
				 ;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getGroupName($groupId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT title FROM #__awd_groups '					
				 .'WHERE id = ' . (int)$groupId
				 ;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function getGroupStatusofMem($groupId, $userId)
	{
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT status FROM #__awd_groups_members '					
				 .'WHERE user_id = ' . (int)$userId . ' AND group_id = ' . (int)$groupId
				 ;
		$db->setQuery($query);
		$result = $db->loadResult();
		
		if(isset($result)){
			if(($result) == 1)
				return 'invited';
			else
				return 'waiting';
		}else{
			return 'invite';
		}
	}
}