<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
 //no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
 
class awdwallController extends JControllerLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		$this->registerTask('save',	'saveconfig');
	}

	function display($cachable = false) 
	{
		parent::display($cachable);
	}
	function upload_theme()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.path');

		if($_FILES["zip_file"]["name"]) {
			$filename = $_FILES["zip_file"]["name"];
			$source = $_FILES["zip_file"]["tmp_name"];
			$type = $_FILES["zip_file"]["type"];
			$name = explode(".", $filename);
			$foldername=$name[0];
			$accepted_types = array('application/x-zip','application/zip','application/octet-stream','application/x-zip-compressed','multipart/x-zip','application/x-compressed');
            
			if(in_array($type,$accepted_types))
			{
			 // clean up filename to get rid of strange characters like spaces etc
			  $filename = JFile::makeSafe($_FILES["zip_file"]["name"]);
			  // folder where unzipped contents will go
			  $destination = JPATH_SITE.DS. 'components'.DS.'com_awdwall'.DS.'images';  
			  // make the folder to stored the extracted files
			  JFolder::create($destination, 0777);
			  // place zip file in new directory
			  if (!JFile::upload($_FILES["zip_file"]['tmp_name'], JPATH_SITE.DS.'tmp'.DS.$filename)) JError::raiseError(500, 'Error uploading file to '.JPATH_SITE.DS.'tmp');
			  chmod(JPATH_SITE.DS.'tmp'.DS.$filename, 0777);
			  // unzip file
			  $result = JArchive::extract(JPath::clean(JPATH_SITE.DS.'tmp'.DS.$filename), JPath::clean($destination));
			  if ($result === false) JError::raiseError(500, 'Error unzipping file.');
			
			$message = "Your theme was uploaded and unpacked.";
			}
			else
			{
				$message = "The file you are trying to upload is not a zip file. Please try again.";
			}
			
		}else
		{
				$message = "The file you are trying to upload is not a zip file. Please try again.";
		}	
		
		$this->setMessage($message);
		$this->setRedirect( 'index.php?option=com_awdwall&controller=config');
		
	}
	
	
	function saveconfig()
	{
		//global $mainframe;
		$app = JFactory::getApplication();
		$task = JRequest::getVar('task');	
//		echo "i am here";
//		exit;	
		
		$params = JRequest::getVar( 'params', array(), 'post', 'array' );
		if (is_array( $params ))
		{
//			$txt = array();
			$check = 0;
			foreach ( $params as $k=>$v)
			{
				if($k == 'color1'){
					$check = 1;
				}
			}			
//			$params = implode( "\n", $txt );	
		    $paramsString = json_encode( $params );	
			$db =& JFactory::getDBO();
			$params = $db->Quote($paramsString);	
			$linkc='index.php?option=com_awdwall&controller=colors';					
			if($check > 0){
				$query = "UPDATE #__menu SET `params` =".$params." WHERE `link`='".$linkc."'";
//				echo $query;exit;
				$msg	= JText::_( 'Colors is saved' );
				$link	= 'index.php?option=com_awdwall&controller=awdwall';

			}
			else{
				//$query = "UPDATE #__components SET params=".$params." WHERE link='option=com_awdwall' AND parent=0";
				$query = "UPDATE #__extensions SET params=".$params." WHERE `element`='com_awdwall' AND `type`='component'";
				$msg	= JText::_( 'Wall configuration is saved' );
				$link	= 'index.php?option=com_awdwall&controller=awdwall';
//				echo $query;exit;
			}			
			$db->setQuery($query);
			$db->query();
		}
		if($check > 0){
			$msg	= JText::_( 'Colors is saved' );
			$link	= 'index.php?option=com_awdwall&controller=awdwall';
		}
		else{
			$msg	= JText::_( 'Wall configuration is saved' );
			$link	= 'index.php?option=com_awdwall&controller=awdwall';
		}		
		//$mainframe->redirect( $link, $msg );
		$app->redirect( $link, $msg );

	}
}	
