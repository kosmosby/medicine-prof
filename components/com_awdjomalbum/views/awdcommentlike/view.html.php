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

class AwdjomalbumViewawdcommentlike extends JViewLegacy {

	function display($tpl = null) { 

	

		global $mainframe;

		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$commentID=$_REQUEST['commentID'];

		$uid=$_REQUEST['uID'];

	 	

		$sql="select count(*) from #__awd_jomalbum_comment_like  where commentid=".$commentID." and userid=".$uid;

		$db->setQuery($sql);

		$totRec=$db->loadResult();

		

		if($totRec==0)

		{		

			$sql="insert into #__awd_jomalbum_comment_like(commentid,userid) values($commentID,$uid)";

			$db->setQuery($sql);

			if (!$db->query()) {

			return JError::raiseWarning( 500, $db->getError() );

			}	

		}

		

		

		$sql="select * from #__awd_jomalbum_comment_like where commentid=".$commentID." order by id desc Limit 5";

		$db->setQuery($sql);

		$rows=$db->loadObjectList();

	

		$sql="select count(*) from #__awd_jomalbum_comment_like where commentid=".$commentID;

		$db->setQuery($sql);

		$totLike=$db->loadResult();

	

		

		$this->assignRef('totLike', $totLike);  

		$this->assignRef('rows', $rows);  

		

		parent::display($tpl);

	}

}

?>