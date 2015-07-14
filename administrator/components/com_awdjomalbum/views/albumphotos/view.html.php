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
class AwdjomalbumViewAlbumphotos extends JViewLegacy {
    function display($tpl = null) {
	
		$app = &JFactory::getApplication();
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
		$javascript		= 'onchange="this.form.submit()" ';
			$lists['userlist'] 	= JHTML::_('select.genericlist',   $types2, 'userid', 'class="inputbox" style="width:150px; font-size:14px; " size="1" '.$javascript.'', 'value', 'text', "$userid" );
		}
	
		$this->assignRef('lists',$lists);
		$post	= JRequest::get( 'post' );
		
			jimport('joomla.html.pagination');
			
			
			$limit = $app->getUserStateFromRequest($option.'.limit', 'limit', 10, 'int');
			$limitstart = $app->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
			
			$userid=$_REQUEST['userid'];
			$where='';
			if($userid!=0)
			$where="where userid=".$userid;
			$query = "select count(*) from   #__awd_jomalbum_photos ".''.$where;
			$db->setQuery( $query );
			$total = $db->loadResult();
			
			$pageNav = new JPagination( $total, $limitstart, $limit );
			if($userid==0)
		    $sql="select *  from   #__awd_jomalbum_photos order by id";
			if($userid!=0)
			$sql="select * from #__awd_jomalbum_photos where userid='$userid' ";
			if($limit!=0)
			{
			$sql=$sql.' LIMIT  ' . $limitstart . ', '.$limit;
			}
			$db->setQuery( $sql );
			$rows = $db->loadObjectList();
			$this->assignRef('imgrows',$rows);
			$this->assignRef('pageNav',$pageNav);
			
	
			$photoid=$_REQUEST['photoid'];
			if($photoid)
			{
			$commentQuery="select *  from   #__awd_jomalbum_comment where photoid='$photoid'";
			$db->setQuery( $commentQuery );
			$commentrows = $db->loadObjectList();
			
			$sql2="select image_name from   #__awd_jomalbum_photos where  id=".$photoid;
			$db->setQuery( $sql2 );
			$image = $db->loadResult();
			$path=JURI::base();			
			$path=substr(" $path", 0, -14); 
			$imgpath=$path."images/awd_photo/awd_thumb_photo/".$image;
			$this->assignRef('commentrows',$commentrows);
			}
        parent::display($tpl);
    }
	
}
?>