<?php




// no direct access

defined('_JEXEC') or die('Restricted access');



jimport( 'joomla.application.component.view');



/**

 * HTML View class for the awdjomalbum component

 */

class AwdjomalbumViewAwd_addphoto extends JViewLegacy {

	function display($tpl = null) {

	$app = JFactory::getApplication('site');

	$db =& JFactory :: getDBO();	

	$id=$_REQUEST['id'];

	

	$user =& JFactory::getUser();



	if(empty($user->id))

	{  

		$returnurl=JRoute::_('index.php?option=com_awdjomalbum&view=awd_addphoto&id='.$_REQUEST['id'].'&Itemid='.AwdwallHelperUser::getComItemId(),false);

		$return=base64_encode($returnurl);

		$app->Redirect( JRoute::_('index.php?option=com_users&view=login&return='.$return.'&Itemid='.AwdwallHelperUser::getComItemId(),false));		

	}	

	

	$sql='Select * from #__awd_jomalbum where id='.$id;

	$db->setQuery($sql);

	$rows=$db->loadObjectList();

	$row=$rows[0];

	//print_r($row);


	if($row->userid!=$user->id)
	{
		$app ->Redirect( JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid='.AwdwallHelperUser::getComItemId(),false),JText::_('WRONGUSERGALLERY'));		
	}


	$sql='Select * from #__awd_jomalbum_photos where albumid='.$id;

	$db->setQuery($sql);

	$photoRows=$db->loadObjectList();

	$this->assignRef('photoRows', $photoRows);

	 
	
	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');
	$display_group = $config->get('display_group', 1);
	$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
	$moderator_users = $config->get('moderator_users', '');
	$moderator_users=explode(',',$moderator_users);
	
	$this->assignRef('display_group', $display_group);
	$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
	$this->assignRef('moderator_users', $moderator_users);
	

	$this->assignRef('row', $row);

			

        parent::display($tpl);

    }

}

?>