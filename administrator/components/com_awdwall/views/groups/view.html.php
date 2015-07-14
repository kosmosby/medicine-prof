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

jimport( 'joomla.application.component.view' );

class groupsViewgroups extends JViewLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $config = array())
	{
		parent::__construct( $config );
	}
	
	function display($tpl = null)
	{	 
    	$mainframe=JFactory::getApplication('site');
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Groups') );
	 
	    JToolBarHelper::title(   JText::_( 'Groups' ), 'awdwallprogrp' );
	    
	 	JToolBarHelper::back('Home' , 'index.php?option=com_awdwall&controller=awdwall'); 		
		JToolBarHelper::deleteList('Are you sure to delete the group(s)');
		
		$creator_id=$_REQUEST['creator_id'];
		
		$db		=& JFactory::getDBO();
		
		//group creator list
		$sql="select a.creator as user_id, b.username as username from #__awd_groups as a left join #__users as b on a.creator=b.id";
		$db->setQuery($sql);
		$test = $db->loadObjectList();
		$tmp = array ();

		foreach ($test as $row) 
			if (!in_array($row,$tmp)) array_push($tmp,$row);

		
		if(count($tmp))
		{
			$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select Creator' ) .' -' );
			foreach( $tmp as $obj )
			{
			$types[] = JHTML::_('select.option',  $obj->user_id, JText::_( $obj->username ) );
			}
			$lists['creatorlist'] 	= JHTML::_('select.genericlist',   $types, 'creator_id', 'class="inputbox" style="width:160px; "size="1" onchange="document.adminForm.submit();"', 'value', 'text', $creator_id );
		}
		
		//search group by title
		$grp_title=$_REQUEST['search'];
		if($grp_title)
		{
			$where=" where title like '%".$grp_title."%'";
		}
		if($creator_id)
		{
			$where=" where creator=".$creator_id;
		}
		if($grp_title && $creator_id)
		{
			$where=" where title like '%".$grp_title."%' and creator=".$creator_id;
		}
		
		$query = "select * from #__awd_groups".$where." order by id ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
//		echo '<pre>';
//		print_r($tmp);
//		echo '<pre>';
	    $this->assignRef('groups', $rows);	
	    $this->assignRef('lists', $lists);	
		
	    parent::display($tpl);
  }
}

?>
