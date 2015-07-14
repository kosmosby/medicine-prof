<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

class AVideo
{
	var $videoRoot			= '';
	var $videoHome			= '';
	var $videoSize			= '';
	var $thumbSize			= '';
	var $videoUser			= '';
	var $thumbfolder		= '';

	var $videoRootHome		= '';
	var $videoRootOrig		= '';
	var $videoRootHomeUser	= '';
	var $videoRootOrigUser	= '';
	var $videoHomeUser		= '';	
	var $videoRootHomeUserThumb	= '';
	var $videoHomeUserThumb	= '';
	var $videoThumbHeight	= 0;
	var $videoThumbWidth	= 0;
	
	/**
	 * Constructor
	 */
	function AVideo($userId)
	{
		$user	 = &JFactory::getUser();
		$this->videoUser	= $userId;
				
		$this->videofolder	= 'images';
		$this->videofolder	= JString::trim($this->videofolder);
		$this->videofolder	= JString::trim($this->videofolder, '/');
		$this->videoRoot	= JPATH_ROOT . DS . $this->videofolder;
		
		$this->videoHome	= 'videos';
		$this->videoThumb	= 'thumbs';		
		$this->videoSize	= '400x300';
		$this->thumbSize	= '112x84';

		$this->videoRootHome		= $this->videoRoot . DS . $this->videoHome;
		$this->videoRootOrig		= $this->videoRoot . DS . $this->videoOrig;
		$this->videoRootHomeUser	= $this->videoRootHome . DS . $this->videoUser;	
		$this->videoHomeUser		= $this->videoHome . DS . $this->videoUser;		
		$this->videoRootHomeUserThumb = $this->videoRootHomeUser . DS . $this->videoThumb;
		$this->videoHomeUserThumb	= $this->videoHomeUser . DS . $this->videoThumb;

		$arrThumbSize				= explode('x', $this->thumbSize, 2);
		$this->videoThumbWidth		= intval($arrThumbSize[0]);
		$this->videoThumbHeight		= intval($arrThumbSize[1]);
		
	}

	// return the video provider object
	function getProvider($videoLink)
	{
		$mainframe	=& JFactory::getApplication();
		$videoObj	= null;		
//		$videoLink = 'http://'.JString::str_ireplace( 'http://' , '' , $videoLink );		
		$parsedVideoLink	= parse_url($videoLink);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		$user	  = &JFactory::getUser();
		//verify domain
		if (empty($domain)){
			$redirect = JRoute::_('index.php?option=com_awdwall&&view=awdwall', false);
			$message	= JText::_('Invalid video Url');
			$mainframe->redirect($redirect , $message, 'error');				
		}
		
		//verify url
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
		if(getContentFromUrl($videoLink) == false){
			
			$url = JRoute::_('index.php?option=com_awdwall&&view=awdwall', false);
			$message	= JText::_('Error fetching video');
			$mainframe->redirect( $url , $message );
		}		
	
		$provider		= explode('.', $domain);
		$providerName	= JString::strtolower($provider[0]);
		$libraryPath	= JPATH_COMPONENT . DS . 'libraries' . DS . 'videos' . DS . $providerName . '.php';	

		jimport('joomla.filesystem.file');
		if (!JFile::exists($libraryPath)){	
			$redirect = JRoute::_('index.php?option=com_awdwall&&view=awdwall', false);
			$message	= JText::_('Video Provider is not supported');
			$mainframe->redirect($redirect , $message, 'error');
		}
		
		$db =& JFactory::getDBO();
		require_once($libraryPath);
		$className		= 'TableVideo' . JString::ucfirst($providerName);
		$videoObj		= new $className($db);
		$videoObj->init($videoLink);

		return $videoObj;
	}
}