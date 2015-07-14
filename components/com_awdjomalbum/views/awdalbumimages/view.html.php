<?php

/**
 * @version 3.0
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

class AwdjomalbumViewAwdalbumimages extends JViewLegacy {

	function display($tpl = null) {

	require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');

	

		$mainframe= JFactory::getApplication();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$template = $config->get('temp', 'default');
		$width 	= $config->get('width', 725);
		$display_name 	= $config->get('display_name', 1);
		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$wuid=$_REQUEST['wuid'];
		$albumid=$_REQUEST['albumid'];

	$query ='Select aj.* from #__awd_jomalbum_photos as aj inner join  #__awd_jomalbum as al on aj.albumid =al.id where al.published=1 and al.id='.$albumid.' order by aj.id desc';
	//echo $query;
	$db->setQuery($query);
	$rows=$db->loadObjectList();

			 

			$this->assignRef('rows', $rows);  

			$sql="Select * from #__users where id=".$rows[0]->userid; 
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
			$sql="Select name from #__awd_jomalbum where id=".$albumid; 
			$db->setQuery($sql);
			$albumname=$db->loadResult(); 
			$this->assignRef('albumname', $albumname);
			$this->assignRef('albumid', $albumid);
			//$query="Select awi.* from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id=".$wuid;
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

	

	

	

}

?>