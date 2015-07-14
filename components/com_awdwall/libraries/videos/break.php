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
class TableVideoBreak
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
			$message	= JText::_('INVALID VIDEO ID');
			$mainframe->redirect( $url , $message );
		}
		elseif($xmlContent == FALSE)
		{				
			$url		= $this->getFailedUrl();
			$message	= JText::_('ERROR FETCHING VIDEO');
			$mainframe->redirect( $url , $message );		
		}		
		else
		{
			//Get title
			$pattern =  "'<meta name=\"embed_video_title\" id=\"vid_title\" content=\"(.*?)\"( \/)?(>)'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->title = $matches[1][0];
			}
			
			
			//Get description
			
			$pattern =  "'<meta name=\"embed_video_description\" id=\"vid_desc\" content=\"(.*?)\"( \/)?(>)'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->description = $matches[1][0];
			}
			
			//Get duration
		
			//Get thumbnail
			$pattern =  "'<meta name=\"embed_video_thumb_url\" content=\"(.*?)\"( \/)?(>)'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->thumbnail = $matches[1][0];
			}
			
			return true;
		}			
	}	
	
	function getId()
	{	
		
	   $pattern    = '/http\:\/\/(\w{3}\.)?break.com\/(.*)/';
	   preg_match( $pattern, $this->videoUrl, $match );
	   
       return !empty( $match[2] ) ? $match[2] : null;
        
	}
	
	function getType()
	{
		return 'break';
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
	
	function getViewHTML($videoId, $videoWidth='475' , $videoHeight='325')
	{		
 		if (!$videoId)
		{
			$videoId	= $this->videoId;
		}
		$file	= 'http://www.break.com/'.$videoId;
		$xmlContent = getContentFromUrl($file);
 		
		$pattern =  "'<meta name=\"embed_video_url\" content=\"(.*?)\"( /)?(>)'s";
		preg_match_all($pattern, $xmlContent, $matches);
		if($matches)
		{
				$videoUrl = $matches[1][0];
		}
		
		$embed="<embed src=\"".$videoUrl."\" width=\"".$videoWidth."\" height=\"".$videoHeight."\" wmode=\"transparent\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\"> </embed>";
		
		return $embed;

	}
	
	function getFeedUrl(){
		return 'http://www.break.com/' . $this->getId();
		//return $this->videoUrl;
	}
	
}
