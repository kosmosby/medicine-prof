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

jimport( 'joomla.application.component.controller' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );
require_once( JPATH_COMPONENT.DS.'tables'.DS.'awdjomalbum.php' );
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_awdjomalbum'.DS.'tables');


class AwdjomalbumController extends JControllerLegacy {
    /**
     * Constructor
     * @access private
     * @subpackage awdjomalbum
     */
    function __construct() {
        //Get View
        if(JRequest::getCmd('view') == '') {
            JRequest::setVar('view', 'default');
        }
        $this->item_type = 'Default';
        parent::__construct();
    }
	function publish()
	{
		$db		=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'Nothing selected' ) );
		}
		foreach ($cid as $id){
			$sql="update #__awd_jomalbum_info_ques set published=1 where  id=".$id;	
			$db->setQuery( $sql );	
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		}
		$message="info published";
		$this->setMessage($message);
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=info');
	}	
	function unpublish()
	{
		$db		=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'Nothing selected' ) );
		}
		foreach ($cid as $id){
			$sql="update #__awd_jomalbum_info_ques set published=0 where  id=".$id;	
			$db->setQuery( $sql );	
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		}
		$message="info unpublished";
		$this->setMessage($message);
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=info');
	}	
	function deleteinfo()
	{
		$db		=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'Nothing selected' ) );
		}
		foreach ($cid as $id){
		
			$sql="delete from   #__awd_jomalbum_info_ques where  id=".$id;	
			$db->setQuery( $sql );	
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		}
		$message="fields deleted";
		$this->setMessage($message);
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=info');
	}	
	 	function save()
	{
		//global $mainframe, $option;
		global $app, $option;
		$db		=& JFactory::getDBO();
		$id=$_REQUEST['id'];
		//echo $id;
//		exit;
		$colname=$_REQUEST['colname'];
		$value=$_REQUEST['value'];
		$row = new TableAwd_jomalbum_info_ques($db);
		$post	= JRequest::get( 'post' );
		if (!$row->bind( $post )) {
			return JError::raiseError( 500, $db->stderr() );
		}
				 
		if (!$row->store()) {
			return JError::raiseError( 500, $db->stderr() );
		}
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=info&Itemid='.$_REQUEST['Itemid']);
	}

	
	function remove() 
	 {
	 
	
	
      // global $mainframe, $option;
	  global $app, $option;
		$db		=& JFactory::getDBO();
		//$imgname=$_REQUEST['imgname'];
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'No images selected' ) );
		}
		//print_r($cid);
//		exit;
		foreach ($cid as $id){
		
			$sql1="select image_name from   #__awd_jomalbum_photos where  id=".$id;
			
			$db->setQuery( $sql1 );
			$image_name = $db->loadResult();
//			print_r($images);
			
			//$image='images/awd_photo/awd_thumb_photo/'.$imgname;
			$thumbimage=JPATH_SITE.DS.'images/awd_photo/awd_thumb_photo/'.$image_name;
			$largeimage=JPATH_SITE.DS.'images/awd_photo/awd_large_photo/'.$image_name;
			$image=JPATH_SITE.DS.'images/awd_photo/'.$image_name;
			
			//echo $thumbimage.'<br>';
			$sql="delete from   #__awd_jomalbum_photos where  id=".$id;	
			$db->setQuery( $sql );	
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			//echo $sql.'<br>';
			//exit;
			$sql2="delete from   #__awd_jomalbum_comment where  photoid=".$id;	
			$db->setQuery( $sql2 );	
//			echo $sql2;
			//exit;
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}

			@unlink($thumbimage);
			@unlink($largeimage);
			@unlink($image);
			  
			//unlink($imgname);

			
	// echo $sql.'<br>';
		//	 exit;
		}
		
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=albumphotos');
    }
	
	function deletecomment() 
	 {
	 	$photoid=$_REQUEST['photoid'];
	    //global $mainframe, $option;
		global $app, $option;
		$db		=& JFactory::getDBO();
		//$imgname=$_REQUEST['imgname'];
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'No images selected' ) );
		}
		//print_r($cid);
//		exit;
		foreach ($cid as $id){
		
			
			$sql="delete from   #__awd_jomalbum_comment where  id=".$id;	
			$db->setQuery( $sql );		
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}

	// echo $sql.'<br>';
		//	 exit;
		}
		
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=albumphotos&layout=comments&photoid='.$photoid);
    }
	
	function deletewallphotos() 
	 {
	 
	
	
     //  global $mainframe, $option;
		global $app, $option;
		$db		=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'No images selected' ) );
		}
		//print_r($cid);
//		exit;
		foreach ($cid as $id){
		
			$sql2="select commenter_id from   #__awd_wall where id=".$id;
			$db->setQuery( $sql2 );
			$userid= $db->loadResult();
			//echo $userid;
			
			$sql1="select path from   #__awd_wall_images where  wall_id=".$id;
			$db->setQuery( $sql1 );
			$images = $db->loadResult();
			
			$thumbimage=JPATH_SITE.DS.'images'.DS.$userid.DS.'thumb'.DS.$images;
			$originalimage=JPATH_SITE.DS.'images'.DS.$userid.DS.'original'.DS.$images;
			//echo $thumbimage.'<br>';
//			echo $originalimage;
//			exit;
			$sql="delete from   #__awd_wall_images where  wall_id=".$id;	
			$db->setQuery( $sql );	
	
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			$sql3="delete from   #__awd_wall where  reply=".$id ." " . "or id=".$id;	
			//echo $sql3;
			//exit;
			$db->setQuery( $sql3 );	
			
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			@unlink($thumbimage);
			@unlink($originalimage);
	
		}
		//exit;
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=wallphotos');
    }
	
	function deletewallcomment() 
	 {
	 	$wallid=$_REQUEST['wallid'];
		$photoid=$_REQUEST['photoid'];
	    //global $mainframe, $option;
	    global $app, $option;
		$db		=& JFactory::getDBO();
		//$imgname=$_REQUEST['imgname'];
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		
		if (empty( $cid )) {
		return JError::raiseWarning( 500, JText::_( 'No images selected' ) );
		}
		//print_r($cid);
//		exit;
		foreach ($cid as $id){
		
			
			$sql="delete from   #__awd_wall where  reply=".$wallid. " " . "and id=".$id;	
			//echo $sql;
//			exit;
			$db->setQuery( $sql );		
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}

	// echo $sql.'<br>';
		//	 exit;
		}
		
		$this->setRedirect( 'index.php?option=com_awdjomalbum&view=wallphotos&layout=wallcomments&photoid='.$photoid.'&wallid='.$wallid);
		
		
    }
	
}
?>