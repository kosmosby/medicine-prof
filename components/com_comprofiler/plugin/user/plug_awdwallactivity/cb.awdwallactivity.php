<?php

/**

 * @version 2.1

 * @package AWDwall

 * @author   AWDsolution.com

 * @link http://www.AWDsolution.com

 * @copyright Copyright (C) 2010 AWDsolution.com. All rights reserved.

*/



if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }
$awdlang =& JFactory::getLanguage();
$awdlang->load('com_awdwall', JPATH_SITE, 'en-GB', true);
$awdlang->load('com_awdwall', JPATH_SITE, $awdlang->getDefault(), true);
$awdlang->load('com_awdwall', JPATH_SITE, null, true);
global $_PLUGINS;

$_PLUGINS->registerFunction( 'onAfterUserUpdate', 'awdProfileUpdate', 'awdactivityPlugin' );

$_PLUGINS->registerFunction( 'onAfterUserAvatarUpdate', 'awdProfileAvatarUpdate', 'awdactivityPlugin' );

$_PLUGINS->registerFunction( 'onAfterAcceptConnection', 'awdProfileConnetion', 'awdactivityPlugin' );







class awdactivityPlugin extends cbPluginHandler {

	

	// Establish the trigger function:

	function awdProfileUpdate()

	{

		global $mainframe, $option;

		$db		=& JFactory::getDBO();

		$user=&JFactory::getUser();

		$target=$user->id;

		$commenter_id=$user->id;

		$newtitle=JText::_('UPDATED PROFILE');

		$type='text';



		$walldate=strtotime(date("Y-m-d H:i:s"));

			$sql="INSERT INTO  #__awd_wall (`user_id`,`commenter_id` ,`message`,`type`,`wall_date`)VALUES ('".$target."','".$commenter_id."', '".$newtitle."','".$type."','".$walldate."');";

			$db->setQuery( $sql );

			if (!$db->query())

			{

				return JError::raiseWarning( 500, $db->getError() );

			}

		

	}

	function awdProfileAvatarUpdate()

	{

		global $mainframe, $option;

		$db		=& JFactory::getDBO();

		$user=&JFactory::getUser();

		$target=$user->id;

		$commenter_id=$user->id;

		$newtitle=JText::_('UPDATED AVATAR');

		$type='text';

		$walldate=strtotime(date("Y-m-d H:i:s"));

		

			$sql="INSERT INTO  #__awd_wall (`user_id`,`commenter_id` ,`message`,`type`,`wall_date`)VALUES ('".$target."','".$commenter_id."', '".$newtitle."','".$type."','".$walldate."');";

			$db->setQuery( $sql );

			if (!$db->query())

			{

				return JError::raiseWarning( 500, $db->getError() );

			}

		

	}

	function awdProfileConnetion($userid,$connectionid)

	{

		global $mainframe, $option;

		$db		=& JFactory::getDBO();

		$user=&JFactory::getUser();

		$target=$connectionid;

		$commenter_id=$userid;

		$newtitle='';

		$type='friend';

		$walldate=strtotime(date("Y-m-d H:i:s"));

		$sql = 'UPDATE #__awd_connection SET status = 1, created = "' . time() . '" WHERE connect_from = ' . (int)$commenter_id . ' AND connect_to = ' . (int)$target;
		$db->setQuery($sql);
		$db->query();

		$sql = 'UPDATE #__awd_connection SET pending = 0 WHERE connect_from = ' . (int)$target . ' AND connect_to = ' . (int)$commenter_id;
		$db->setQuery($sql);
		$db->query();

		
		if($target!=$commenter_id)
		{
			$sql="INSERT INTO  #__awd_wall (`user_id`,`commenter_id` ,`message`,`type`,`wall_date`)VALUES ('".$target."','".$commenter_id."', '".$newtitle."','".$type."','".$walldate."');";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}

		}

		

	}



}







