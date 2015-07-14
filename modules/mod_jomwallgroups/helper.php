<?php
/**
* @version 1.5.0
* @package JOMWall Groups
* @author  AWDsolution.com. All rights reserved.
* @link http://www.AWDsolution.com
* @Copyrighted Commercial Software by  AWDsolution.com
* @license Proprietary (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
 
class JomWallGroupsHelper
{
	
	function getComItemId()
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published=1 ";
		$db->setQuery($query);
		return $db->loadResult();
	}
	function getList(&$params)
	{ 
 	 
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$userId		= (int) $user->get('id');
		$Itemid		=JomWallGroupsHelper::getComItemId();
		$count		= (int) $params->get('count', 5);
		$ordering		= trim( $params->get('ordering1') );
		$topgroup=$params->get('topgroup', '');
	  $groupModel = new AwdwallModelGroup();
	 	if(!empty($user->id))
		{
			$uid=$user->id;
			$sql="Select * from #__awd_groups ";
			if($topgroup)
			{
				$sql=$sql." where id!=".$topgroup." ";
			}			
				if($ordering==0)
				{
				$order=" Order by id desc";
				
				} else {
				
				$order=" Order by rand()";
				
				}
			 $sql.=$order;
			 $db->setQuery($sql);
			 $grows = $db->loadObjectList();
			
			 $ids=array();
				foreach($grows as $row)
				{
					if($row->privacy==1)
					{
						$ids[]=$row->id;		
					}
					else
					{
					
						$sql="select group_id from #__awd_groups_members where user_id=".$uid." and status=1 and group_id=".$row->id;
						 
						$db->setQuery($sql);
						$gid = $db->loadResult();
						if(!empty($gid))
						{
							$ids[]=$row->id;
							 
						}
					}
				}
			
			 if(count($ids>0))
			 {
			 	$gids=implode(",",$ids);
				if($ordering==0)
				{
				$order=" Order by id desc";
				
				} else {
				
				$order=" Order by rand()";
				
				}
	 			$sql="Select * from #__awd_groups where id in (".$gids.") ".$order;
				 $db->setQuery($sql, 0, $count);
				 $rows = $db->loadObjectList();

			 }
			
		}
		else
		{
			$sql="Select * from #__awd_groups where privacy=1 ";
			if($topgroup)
			{
				$sql=$sql." and id!=".$topgroup." ";
			}			
			if($ordering==0)
			{
				$order=" Order by id desc";
			
			} else {
			
			$order=" Order by rand()";
			
			}
			 $sql.=$order;
			 $db->setQuery($sql, 0, $count);
			 $rows = $db->loadObjectList();
		}
		 
		
		$i		= 0;
		$lists	= array();
		if($rows)
		{
			foreach ( $rows as $row )
			{
				$nofPosts = $groupModel->countPostGrp($row->id);
				$nofMembers = $groupModel->countMemGrp($row->id) + 1;
				$lists[$i]->thumb = AwdwallHelperUser::getBigGrpImg133($row->image,$row->id); 
				$lists[$i]->title = $row->title;
				$lists[$i]->id = $row->id;
				$lists[$i]->descr= $row->description;
				$lists[$i]->nofPosts= $nofPosts;
				$lists[$i]->nofMembers= $nofMembers;
				$lists[$i]->created_date= $row->created_date;
				$lists[$i]->link= 'index.php?option=com_awdwall&task=viewgroup&groupid='.$row->id.'&Itemid='.$Itemid;
				$lists[$i]->memberlink= 'index.php?option=com_awdwall&task=grpmembers&groupid='.$row->id.'&Itemid='.$Itemid;
					$i++;
			}
		}
		
		return $lists;
	}
}
