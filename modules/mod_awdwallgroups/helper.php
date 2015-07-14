<?php
/**
* @version 1.5.0
* @package JomWALLVideoList
* @author  AWDsolution.com. All rights reserved.
* @link http://www.AWDsolution.com
* @Copyrighted Commercial Software by  AWDsolution.com
* @license Proprietary (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
 
class AwdWallGroupsHelper
{
	
	function getComItemId()
	{
		$db 	=JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published=1 ";
		$db->setQuery($query);
		return $db->loadResult();
	}
	function getList(&$params)
	{ 
 	 
		$db			=JFactory::getDBO();
		$user		=JFactory::getUser();
		$userId		= (int) $user->get('id');
		//$Itemid		=AwdWallGroupsHelper::getComItemId();
        $Itemid = 677;
		$count		= (int) $params->get('count', 5);
		$ordering		= trim( $params->get('ordering1') );
	 	if(!empty($user->id))
		{
			$uid=$user->id;
			//$sql="Select * from #__awd_groups ";
            $sql="Select * from #__groupjive_groups ";
						
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
					
						$sql="select group from #__groupjive_users where user_id=".$uid."  and group=".$row->id;
						 
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
			$sql="Select * from #__groupjive_groups ";
			
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
				if($row->image!='') {
					$lists[$i]->thumb = 'images/comprofiler/plug_cbgroupjive/2/'.$row->id.'/'.$row->image;
				}
				else
				{
					$lists[$i]->thumb = 'components/com_awdwall/images/group_thumb.jpg';
				}
				
				$lists[$i]->thumb = AwdwallHelperUser::getBigGrpImg133($row->logo,$row->id);
				$lists[$i]->title = $row->name;
				$lists[$i]->descr= $row->description;
				
				//$lists[$i]->link= 'index.php?option=com_awdwall&task=viewgroup&groupid='.$row->id.'&Itemid='.$Itemid;
                $lists[$i]->link= 'index.php?option=com_comprofiler&task=pluginclass&plugin=cbgroupjive&action=groups&func=show&grp='.$row->id.'&Itemid='.$Itemid;
					$i++;
			}
		}
		
		return $lists;
	}
}
