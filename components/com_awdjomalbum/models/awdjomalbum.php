<?php
/**
 * Joomla! 1.5 component awdjomalbum
 *
 * @version $Id: awdjomalbum.php 2011-01-31 06:01:25 svn $
 * @author zippyinfotek
 * @package Joomla
 * @subpackage awdjomalbum
 * @license GNU/GPL
 *
 * awd album
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * awdjomalbum Component awdjomalbum Model
 *
 * @author      notwebdesign
 * @package		Joomla
 * @subpackage	awdjomalbum
 * @since 1.5
 */
class AwdjomalbumModelAwdjomalbum extends JModel {
    
	
	public function getCurrentUserDetails($userid)
	{
	 	$db		=& JFactory :: getDBO();
		$sql="SELECT  count(*) FROM #__community_users";
		$db->setQuery($sql);
		$result=$db->loadResult();
		
		if($result!=0)	{
			$avatarTable='community_users'; 
			$imgPath=JURI::base();
			
			$sql="select avatar from #__".$avatarTable." where userid=".$userid;
			$db->setQuery($sql);
			$imgPathCUser=JURI::base().$db->loadResult();
			$userprofileLinkCUser=JRoute::_('index.php?option=com_community&view=profile&userid='.$userid.'&Itemid='.$_REQUEST['']); 
			
		}
		 
		if($avatarTable=='')
		{
			$sql="SELECT  count(*) FROM #__comprofiler";
			$db->setQuery($sql);
			$result=$db->loadResult();
			if($result!=0)
			{
				$avatarTable='comprofiler';
				$imgPath=JURI::base().'images/comprofiler/';
				$sql="select avatar from #__".$avatarTable." where user_id=".$userid;
				
				$db->setQuery($sql);
				if(!$db->loadResult()){				
					$imgPathCUser=JURI::base().'components/com_awdjomalbum/images/default_thumb.jpg';
				}
				else
				{
					$imgPathCUser=$imgPath.$db->loadResult();
				}
				
				$userprofileLinkCUser=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$userid.'&Itemid='.AwdwallHelperUser::getJsItemId());
 	
			}
		}
		  
		
		
		
		if($avatarTable=='')
		{
			$sql="SELECT  count(*) FROM #__awd_wall_users";
			$db->setQuery($sql);
			$result=$db->loadResult();  
			
			if($result!=0)
			{
				$avatarTable='awd_wall_users'; 
				$imgPath=JURI::base().'images/wallavatar/';
				
				
				$sql="select avatar from #__".$avatarTable." where user_id=".$userid; 
					$db->setQuery($sql);
				
					if(!$db->loadResult()){				
					$imgPathCUser=JURI::base().'components/com_awdjomalbum/images/default_thumb.jpg';
				}
				else
				{
					$imgPathCUser=$imgPath.$userid.'/thumb/'.$db->loadResult();
				}
				
 					$userprofileLinkCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId()); 

			}
		}
		
		$this->assignRef('imgPathCUser', $imgPathCUser); 
		$this->assignRef('userprofileLinkCUser', $userprofileLinkCUser);
		$this->assignRef('avatarTable', $avatarTable);

	} 
	
	public function getUserDetails($userid,$avatarTable,$uid)
	{
		//echo $userid.' '.$avatarTable.' '.$currentUser;
			$db		=& JFactory :: getDBO();
			$imgPath1='';
		
			if($avatarTable=='community_users')	{
			 
				$sql="select avatar from #__".$avatarTable." where userid=".$userid;
 				$db->setQuery($sql);
				$imgPath1=JURI::base().$db->loadResult(); 
				$userprofileLink=JRoute::_('index.php?option=com_community&view=profile&userid='.$uid.'&Itemid='.$_REQUEST['']);
 				 
			} else if($avatarTable=='comprofiler') { 
				$sql="select avatar from #__".$avatarTable." where user_id=".$userid; 
 				$db->setQuery($sql);
				
				if(!$db->loadResult()){				
					$imgPath1=JURI::base().'components/com_awdjomalbum/images/default_thumb.jpg';
				}
				else
				{
					$imgPath1=$imgPath.$db->loadResult();
				}
				
				//$imgPath1=$imgPath.$db->loadResult();
				$userprofileLink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$uid.'&Itemid='.AwdwallHelperUser::getJsItemId());
 			
			} else if($avatarTable=='awd_wall_users') { 
				$sql="select avatar from #__".$avatarTable." where user_id=".$userid; 
 				$db->setQuery($sql);
				
				if(!$db->loadResult()){				
					$imgPath1=JURI::base().'components/com_awdjomalbum/images/default_thumb.jpg';
				}
				else
				{
					$imgPath1=$imgPath.$userid.'/thumb/'.$db->loadResult(); 
 				}
				//$imgPath1=$imgPath.$userid.'/thumb/'.$db->loadResult(); 
 				$userprofileLink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$uid.'&Itemid='.AwdwallHelperUser::getComItemId()); 
			 
			} else{
			
				$imgPath1=JURI::base().'components/com_awdjomalbum/images/default_thumb.jpg';
			}
			
			$this->assignRef('imgPath1', $imgPath1); 
			$this->assignRef('userprofileLink', $userprofileLink);
	}
	
}
?>