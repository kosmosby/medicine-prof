<?php
/**
 * @version 2.1
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

class groupsController extends JControllerLegacy
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}

	function cancel()
	{
		$this->setRedirect( 'index.php' );
	}

	function display($cachable = false) 
	{
		parent::display($cachable);
	}
	
	function remove() 
	{
		$db		=& JFactory::getDBO();
		$mainframe=JFactory::getApplication();
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		foreach ($cid as $id)
		{
//			$sql="select image from #__awd_groups where  group_id=".$id;
//			$db->setQuery( $sql );
//			$grpimg=$db->loadResult();
			
//			$path=JPATH_SITE. '/images/awdgrp_images/'.$id.'/original/';
//			$thumbpath=JPATH_SITE. '/images/awdgrp_images/'.$id.'/thumb/';
//
//			@unlink($path.$grpimg);
//			@unlink($thumbpath.$grpimg);
			
			//delete from group table
			$sql="delete from   #__awd_groups where  id=".$id;
			$db->setQuery( $sql );
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			
			//delete from group members table
			$sql="delete from   #__awd_groups_members where  group_id=".$id;
			$db->setQuery( $sql );
			$db->query();
			
			//delete from wall table
			$sql="delete from   #__awd_wall where  group_id=".$id;
			$db->setQuery( $sql );
			$db->query();
		}
		$mainframe->redirect( 'index.php?option=com_awdwall&controller=groups');
	}
}	
?>
