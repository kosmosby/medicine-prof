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
class TableVideoYahoo
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
			$pattern =  "'<meta property=\"og:title\" content=\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
			$this->title = $matches[1][0];
			if($this->title == ''){
				$pattern =  "'name=\"context_title\" value=\"(.*?)\"'s"; 
				preg_match_all($pattern, $xmlContent, $matches);
				$this->title = $matches[1][0];	
			}
			
			
			//Get description
			$pattern =  "'<meta property=\"og:description\" content=\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
			$this->description = $matches[1][0];
			if($this->description == ''){
				$pattern =  "'desc\":\"(.*?)\"'s"; 
				preg_match_all($pattern, $xmlContent, $matches);
				$this->description = stripslashes($matches[1][0]);
			}
			
			//Get duration
			$pattern =  "'x-duration=\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
			$this->duration = $matches[1][0];
			if($this->duration == ''){
				$pattern =  "'durtn\":\"(.*?)\"'s"; 
				preg_match_all($pattern, $xmlContent, $matches);
								
				$duration = $matches[1][0];
			}
			if($this->duration != ''){
				$sec = 0;
				$time = explode(':',$this->duration);
				if($time[0] > 0){
					$sec = $time[0]*60;
				}
				$this->duration = $sec + $time[1];
			}else{
				$this->duration = false;
			}
			
		$duration = null;
		
		// Get description
		$pattern =  "'x-duration=\"(.*?)\"'s"; 
		preg_match_all($pattern, $xmlContent, $matches);
						
		$duration = $matches[1][0];
		
		if($duration == ''){
			$pattern =  "'durtn\":\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
							
			$duration = $matches[1][0];
		}
		
		if($duration != ''){
			$sec = 0;
			$time = explode(':',$duration);
			if($time[0] > 0){
				$sec = $time[0]*60;
			}
			$duration = $sec + $time[1];
		}else{
			$duration = false;
		}
			$this->duration=$duration;
			//Get thumbnail
			$pattern =  "'thmb_url\":\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
			$this->thumbnail = stripslashes($matches[1][0]);
			if($this->thumbnail == ''){
				$pattern =  "'<meta property=\"og:image\" content=\"(.*?)\"'s"; 
				preg_match_all($pattern, $xmlContent, $matches);
				if( $matches && !empty($matches[1][0]) )
				{					
					$this->thumbnail = urldecode($matches[1][0]);			
				}
			}
			return true;
		}			
	}	
	
	function getId()
	{	
		
		parse_str( parse_url( $this->videoUrl, PHP_URL_QUERY ), $result );
		$videoId = $result['vid'];
		
		if( empty($videoId) )
		{
			$id = explode('-', $this->videoUrl);
			$id = $id[count($id)-1];
			$id = explode('.',$id);
			$videoId = $id[0];
		}
        return $videoId;
	}
	
	function getType()
	{
		return 'yahoo';
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

		$embedCode ='<embed type="application/x-shockwave-flash" src="http://d.yimg.com/nl/cbe/paas/player.swf" width="'.$videoWidth.'" height="'.$videoHeight.'" style="undefined" id="yppVideoPlayer22353" name="yppVideoPlayer22353" bgcolor="#000000" quality="high" allowfullscreen="true" allowscriptaccess="always" wmode="transparent" tabindex="999" flashvars="eventHandler=window.YEP36272.playerEvents&amp;autoPlay=true&amp;infoScreenUI=show&amp;shareScreenUI=hide&amp;startScreenCarouselUI=hide&amp;embedCode=on&amp;vid='.$videoId.'&&amp">';
		return $embedCode;
	}
	
	function getFeedUrl(){
		return $this->videoUrl;
	}
	
}
