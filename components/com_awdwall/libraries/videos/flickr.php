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
class TableVideoFlickr
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
			$videoId = explode("/",$this->videoId);
			$pattern =  "'<h1 id=\"title_div".$videoId[1]."\" property=\"dc:title\"( class=\"photo-title\")?\>(.*?)<\/h1>'s";
			preg_match_all($pattern, $xmlContent, $matches);
	
			if($matches)
			{
				$this->title = $matches[2][0];
			}
			
			//Get description
			$pattern =  "'<div id=\"description_div".$videoId[1]."\" class=\"(photoDescription|photo-desc)\">(.*?)<\/div>'s";
			preg_match_all($pattern, $xmlContent, $matches);  
			if($matches)
			{
				$this->description = trim(strip_tags($matches[2][0]));
			}			
			
			//Get duration
			$this->duration=0;
			//Get thumbnail
			$pattern =  "'<link rel=\"image_src\" href=\"(.*?)\"( \/)?(>)'s";
			preg_match_all($pattern, $xmlContent, $matches);
	
			if($matches)
			{
				$this->thumbnail = rawurldecode($matches[1][0]);
			}
			//echo $this->thumbnail;
			return true;
		}			
	}	
	
	function getId()
	{	
	
        $pattern    = '/http\:\/\/\w{3}\.?flickr.com\/photos\/(.*)/';
        preg_match( $pattern, $this->videoUrl, $match );
       
        return !empty($match[1]) ? $match[1] : null ;
	}
	
	function getType()
	{
		return 'flickr';
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
	
	function getViewHTML($videoId, $videoWidth='425' , $videoHeight='344')
	{		
		$file 	= $this->getFeedUrl();
		$xmlContent = getContentFromUrl($file);
		$pattern =  "'<link rel=\"video_src\" href=\"(.*?)\"( \/)?(>)'s";
		preg_match_all($pattern, $xmlContent, $matches);
		if($matches)
		{
			$videoUrl = rawurldecode($matches[1][0]);
		}

		return '<embed width="'.$videoWidth.'" height="'.$videoHeight.'" wmode="transparent" allowFullScreen="true" type="application/x-shockwave-flash" src="'.$videoUrl.'"/>';
	}
	
	function getFeedUrl(){
		return 'http://www.flickr.com/photos/'.$this->getId();
	}
	
}
