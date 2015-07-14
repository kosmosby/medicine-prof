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
class TableVideoHowcast
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
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
		$xmlContent = getContentFromUrl($file);
		
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
			// get title
			$pattern =  "'<title>(.*?)<\/title>'s";		 
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches){
				$this->title = $matches[1][0];
			}
					
			// get description 
			$pattern =  "'<description>(.*?)<\/description>'s";		 
			preg_match_all($pattern, $xmlContent, $matches);
			
			if($matches){
				$this->description = $matches[0][0];
				$this->description = str_replace('<![CDATA[', '', $this->description);
				$this->description = str_replace(']]>', '', $this->description);
				$this->description = str_replace('<description>', '', $this->description);
				$this->description = trim(str_replace('</description>', '', $this->description));
			}
			// get thumbnail
			$pattern =  "'<thumbnail-url>(.*?)<\/thumbnail-url>'s";		 
			preg_match_all($pattern, $xmlContent, $matches);
			
			if($matches){
				$this->thumbnail = $matches[0][0];		
				$this->thumbnail = str_replace('<thumbnail-url>', '', $this->thumbnail);
				$this->thumbnail = trim(str_replace('</thumbnail-url>', '', $this->thumbnail));
			}
			// get duration
			$this->duration = $videoObj->duration;
			return true;
		}				
	}	
	
	function getId()
	{			
		$arrUrl = explode('/', $this->videoUrl);
		$arrId = explode('-', $arrUrl[4]);
									
		return $arrId[0];
	}
	
	function getFeedUrl(){	
		$feedUrl = 'http://api.howcast.com/videos/' . $this->videoId . '.xml';

		return $feedUrl;
	}
	
	function getType()
	{
		return 'howcast';
	}
	
	function getViewHTML($videoId, $videoWidth='425' , $videoHeight='344')
	{	
		if(strpos($videoId, "&") == true){
			$videoId_tmp = substr($videoId, strpos($videoId, "&"));	
			$videoId     = JString::str_ireplace($videoId_tmp,"",$videoId);
			}
			
		$embedCode   = '<object width="'.$videoWidth.'px" height="'.$videoHeight.'px" ><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://www.howcast.com/flash/howcast_player.swf?file='.$videoId.'&theme=black"/><embed src="http://www.howcast.com/flash/howcast_player.swf?file='.$videoId.'&theme=black" width="'.$videoWidth.'" height="'.$videoHeight.'" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"/></object>';

		return $embedCode;
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

?>