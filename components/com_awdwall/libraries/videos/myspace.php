<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die('Restricted access');
class TableVideoMyspace
{
	var $_videoUrl		= null;
	var $_videoId		= null;
	var $_thumbnail		= null;

	function init($url)
	{
		$this->videoUrl	= $url;
		$this->videoId 	= $this->getId();
	}
	
	function isValid()
	{
		$mainframe =& JFactory::getApplication();		
		
		$file 	= $this->getFeedUrl();
		$xmlContent = getContentFromUrl($file);
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
			
		if (empty($this->videoId))
		{
			$url		= $this->getFailedUrl();
			$message	= JText::_('Invalid video id');
			$mainframe->redirect( $url , $message );
		}
		elseif($xmlContent == FALSE)
		{				
			$url		= $this->getFailedUrl();
			$message	= JText::_('Error Fetching video');
			$mainframe->redirect( $url , $message );		
		}		
		else
		{		 
			$pattern =  "'<h1 id=\"tv_tbar_title\">(.*?)<\/h1>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches){
				$this->title = $matches[1][0];  
			} 
			// get description
			$pattern =  "'<b id=\"tv_vid_vd_truncdesc_text\">(.*?)<\/b>'s";	 
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches){
				$this->description = trim(strip_tags($matches[1][0]));	 
			}
			// get thumbnail 	 
			$pattern =  "'<link rel=\"image_src\" href=\"(.*?)\" \/>'s";		 
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches){
				$this->thumbnail = rawurldecode($matches[1][0]);	
			} 
			
			return true;
		}				
	}	
	
	function getId()
	{			
		//check for embed code format  
		$pos_u = strpos($this->videoUrl, "VideoID=");
		$pos_t = strpos($this->videoUrl, "videoid=");

		if ($pos_u === false && $pos_t === false) {
			
		} else if ($pos_u) {
			$code = strip_tags(substr($this->videoUrl, $pos_u + 8));									 	
		} else if ($pos_t) {
			$code = strip_tags(substr($this->videoUrl, $pos_t + 8));	 	
		}
									 
		return $code;
	}
	
	function getType()
	{
		return 'myspace';
	}
	
	function getViewHTML($videoId, $videoWidth='425' , $videoHeight='344')
	{	
		if(strpos($videoId, "&") == true){
			$videoId_tmp = substr($videoId, strpos($videoId, "&"));	
			$videoId     = JString::str_ireplace($videoId_tmp,"",$videoId);
			}
			
		$embedCode   = '<object width="'.$videoWidth.'px" height="'.$videoHeight.'px" ><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$videoId.',t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m='.$videoId.',t=1,mt=video" width="'.$videoWidth.'" height="'.$videoHeight.'" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"/></object>';

		return $embedCode;
	}
	
	function getFeedUrl(){
		$feedUrl = 'http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid='.$this->videoId;

		return $feedUrl;
	}
	
	function getDescription()
	{
		if(empty($this->description))
		{
			$this->description = JText::_('Not Available');
		}
		
		return $this->description;
	}
	
	function getDuration()
	{		
		if (empty($this->duration))
		{
			$this->duration = 0;
		}
		return $this->duration;
	}
	
	function getTitle()
	{		
		$this->title	= $this->title ? $this->title : JText::_('Untitled Video');
		
		return $this->title;
	}
	
	function getThumbnail()
	{
		return $this->thumbnail;
	}
}
