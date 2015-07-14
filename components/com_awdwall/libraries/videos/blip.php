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
class TableVideoBlip
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
			
			$pattern =  "/<title>(.*)<\/title>/i";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->title = $matches[1][1];
							if(empty($this->title)){
								$this->title = $matches[1][2];
							}
			}
			//Get description
			$pattern =  "'<blip\:puredescription>(.*?)<\/blip\:puredescription>'s"; 
			preg_match_all($pattern, $xmlContent, $matches);  
			if($matches)
			{
				$this->description = str_ireplace( '&apos;' , "'" , $matches[1][0] );
				$this->description = str_ireplace( '<![CDATA[', '', $this->description );
				$this->description = str_ireplace( ']]>', '', $this->description );    
			}
			
			//Get duration
			$pattern =  "'<blip:runtime>(.*?)<\/blip:runtime>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->duration = $matches[1][0];
			}
		
			//Get thumbnail
			$pattern =  "'<media:thumbnail url=\"(.*?)\"'s";			 
			preg_match_all($pattern, $xmlContent, $matches); 
	
			if( !empty($matches[1][0]) )
			{
				$this->thumbnail = $matches[1][0];
			}
			else
			{     
				$this->thumbnail = 'http://a.blip.tv/skin/blipnew/placeholder_video.gif';
			}        
			
			return true;
		}			
	}	
	
	function getId()
	{	
		
		$file 	= $this->getFeedUrl();
		$xmlContent = getContentFromUrl($file);
		
		$pattern =  "/<blip:item_id>(.*)<\/blip:item_id>/";
		preg_match( $pattern, $xmlContent, $match );

		if( $match[1] ){
			$videoId    = $match[1];
		}
		
		if($videoId == ''){
			$id = explode('-',$this->url);
			$videoId = $id[count($id)-1];
		}
        return $videoId;
	}
	
	function getType()
	{
		return 'blip';
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
		if (!$videoId)
		{
			$videoId	= $this->videoId;
		}
		$file	= 'http://blip.tv/file/'.$videoId.'?skin=rss';
		$xmlContent = getContentFromUrl($file);
		
		// get embedFile
		$pattern	= "'<blip:embedLookup>(.*?)<\/blip:embedLookup>'s";
		$embedFile	= '';
		preg_match_all($pattern, $xmlContent, $matches);
		if($matches)
		{
			$embedFile = $matches[1][0];
		}	
		
		return '<object width="'.$videoWidth.'" height="'.$videoHeight.'"><param name="movie" value="http://blip.tv/play/'.$embedFile.'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://blip.tv/play/'.$embedFile.'" type="application/x-shockwave-flash" width="'.$videoWidth.'" height="'.$videoHeight.'" allowscriptaccess="always" allowfullscreen="true" wmode="transparent"></embed></object>';

	}
	
	function getFeedUrl(){
		return $this->videoUrl.'?skin=rss';
		//return $this->videoUrl;
	}
	
}
