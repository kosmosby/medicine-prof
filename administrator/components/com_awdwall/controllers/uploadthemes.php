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

class uploadthemesController extends JController
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

	function display() {
		parent::display();
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
		$this->setRedirect( 'index.php?option=com_awdwall&controller=uploadthemes');
		
	}
}	
?>
