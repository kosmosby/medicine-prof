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

class AwdjomalbumViewUserinfo extends JViewLegacy {

	function display($tpl = null) {

	

	require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');

		global $mainframe;

		$db		=& JFactory :: getDBO();
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$displayName 	= $config->get('display_name', 1);
		$user =& JFactory::getUser();

		$sql="select * from #__awd_jomalbum_info_ques order by id";
		$db->setQuery($sql);
		$colrows=$db->loadObjectList();
		$this->assignRef('colrows', $colrows);
		//print_r($colrows);
		$wuid=$_REQUEST['wuid'];
		//get jomwall user gender and birthday

		$basicInfo 		= getUserInfo($wuid);

		$this->assignRef('basicInfo', $basicInfo);

		

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

		

			$query="Select * from #__awd_jomalbum_userinfo where userid=".$wuid; 

			$db->setQuery($query);

			$rows=$db->loadObjectList();

			 //print_r($rows);

			$this->assignRef('rows', $rows);  

		
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