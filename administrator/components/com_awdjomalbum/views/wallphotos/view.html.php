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

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class AwdjomalbumViewWallphotos extends JViewLegacy {
    function display($tpl = null) {
	
		$app =& JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db		=& JFactory::getDBO();
		$document	= & JFactory::getDocument();
		$task 		= strtolower(JRequest::getVar('task'));
		$userid=$_REQUEST['userid'];
		$sql = 'SELECT id AS value, username AS text'
		. ' FROM #__users ORDER BY username';
		$db->setQuery( $sql );
		$test=$db->loadObjectList();
		if(count($test))
		{
			$types2[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select User' ) .' -' );
			foreach( $db->loadObjectList() as $obj )
			{
			$types2[] = JHTML::_('select.option',  $obj->value, JText::_( $obj->text ) );
			}
			$lists['userlist'] 	= JHTML::_('select.genericlist',   $types2, 'userid', 'class="inputbox" style="width:150px;  font-size:14px; " size="1" onchange="document.adminForm.submit();"', 'value', 'text', "$userid" );
		}
	 
		$this->assignRef('lists',$lists);
		$post	= JRequest::get( 'post' );
		
		
		jimport('joomla.html.pagination');
	

			
			$limit = $app->getUserStateFromRequest($option.'.limit', 'limit', 10, 'int');
			$limitstart = $app->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
			
			$userid=$_REQUEST['userid'];
			$where='';
			if($userid!=0)
			$where=" where aw.user_id=".$userid;

			//$sql = "select count() awi.*,aw.* from #__awd_wall_images as awi left join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id='$userid'";
			$sql = "select count(*)  from #__awd_wall_images as awi inner join #__awd_wall as aw on awi.wall_id=aw.id".' '.$where;
			$db->setQuery( $sql );
			$total = $db->loadResult();
			$pageNav = new JPagination( $total, $limitstart, $limit );
		
		if($userid==0)
			$query="Select awi.*,aw.* from #__awd_wall_images as awi inner join #__awd_wall as aw on awi.wall_id=aw.id ";
		if($userid!=0)
			$query="Select awi.*,aw.* from #__awd_wall_images as awi inner join #__awd_wall as aw on awi.wall_id=aw.id where aw.user_id='$userid'";
			if($limit!=0)
			{
			$query=$query.' LIMIT  ' . $limitstart . ', '.$limit;
			}
			$db->setQuery($query);
			$photorows=$db->loadObjectList();
			//print_r($photorows);
			$this->assignRef('photorows', $photorows);  
			$this->assignRef('pageNav',$pageNav);
			parent::display($tpl);
    }
}
?>