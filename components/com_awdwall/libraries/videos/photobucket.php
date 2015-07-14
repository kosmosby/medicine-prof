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
class TableVideoPhotobucket
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
			$pattern =  "'<h2 id=\"mediaTitle\">(.*?)<\/h2>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->title = $matches[1][0];
			}
			
			//Get description
			$pattern =  "'<meta name=\"description\" content=\"(.*?)\" \/>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->description = trim(strip_tags($matches[1][0]));
			}
			
			//Get duration
		
			//Get thumbnail
			$pattern =  "'<link rel=\"image_src\" href=\"(.*?)\" \/>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->thumbnail = $matches[1][0];
			}

			//echo $this->thumbnail;
			//exit;
			return true;
		}			
	}	
	
	function getId()
	{	
		
        $pattern    = '/http\:\/\/(media\.)?photobucket.com\/?(.*\/)video\/([a-zA-Z0-9][a-zA-Z0-9$_.+!*(),;\/\?:@&~=%-\s]*)/';
        preg_match( $pattern, $this->videoUrl, $match);
      
        return !empty($match[3]) ? $match[3] : null;
		
        
	}
	
	function getType()
	{
		return 'photobucket';
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
		$file 	= 'http://media.photobucket.com/video/'.str_ireplace( " ", "%20", $videoId );
		$xmlContent = getContentFromUrl($file);

		$pattern =  "'<link rel=\"video_src\" href=\"(.*?)\" \/>'s";
		preg_match_all($pattern, $xmlContent, $matches);
		if($matches)
		{
			$videoUrl= rawurldecode($matches[1][0]);
		}
		$embed = '<embed width="'.$videoWidth.'" height="'.$videoHeight.'" type="application/x-shockwave-flash" wmode="transparent" src="'.$videoUrl.'">';
		
		return $embed;

	}
	
	function getFeedUrl(){
		return 'http://media.photobucket.com/video/'.$this->videoId;
	}
	
}
