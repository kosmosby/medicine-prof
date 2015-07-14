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
class TableVideoDailymotion
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
		
		$file 	= 'http://www.dailymotion.com/video/'.$this->videoId ;
		
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
		$xmlContent = getContentFromUrl($file);
	
		
		if (empty($this->videoId))
		{
			$url		= $this->getFailedUrl();
			$message	= JText::_('Invalid Video Id');
			$mainframe->redirect( $url , $message );
		}
		
		elseif($xmlContent == 'Invalid id')
		{
			$url		= $this->getFailedUrl();
			$message	= JText::_('Invalid Video Id');
			$mainframe->redirect( $url , $message );
		}
	
		elseif($xmlContent == false)
		{
			$url		= $this->getFailedUrl();
			$message	= JText::_('Error fetching video');
			$mainframe->redirect( $url , $message );
		}
		else
		{
			
			//Get title
			$pattern =  "/<h1 class=\"dmco_title\">(.*)(<\/h1>)?(<\/span>)/i";
			preg_match_all($pattern,  $xmlContent, $matches);
	
			if( $matches && !empty($matches[1][0]) )
			{
				$this->title = strip_tags($matches[1][0]);
			}
			
			//Get description
			$pattern =  "/<meta name=\"description\" lang=\"en\" content=\"(.*)\" \/>/i";
			preg_match_all($pattern, $xmlContent, $matches);
			
			if( $matches && !empty($matches[1][0]) )
			{
				$this->description = trim(strip_tags($matches[1][0],'<br /><br>'));
			}
			
			//Get duration
			
			$pattern =  "'DMDURATION=(.*?)&'s";			 
			preg_match_all($pattern, $xmlContent, $matches);
		
			if( $matches && !empty($matches[1][0]) )
			{
				$this->duration   = $matches[1][0];
			}
			
			//Get thumbnail
			$pattern =  "'<meta property=\"og:image\" content=\"(.*?)\"'s"; 
			preg_match_all($pattern, $xmlContent, $matches);
			if( $matches && !empty($matches[1][0]) )
			{					
				$this->thumbnail = urldecode($matches[1][0]);			
			}
			
			//echo $this->thumbnail;
			//exit;
			return true;
		}
	}
	
	function getFeedUrl(){
		return 'http://www.dailymotion.com/video/'.$this->getId();
	}

	function getId()
	{
		
        $pattern    = '/dailymotion.com\/?(.*)\/video\/(.*)/';
        preg_match( $pattern, $this->videoUrl, $match);
        return !empty($match[2]) ? $match[2] : null;
		
	}

	function getType()
	{
		return 'dailymotion';
	}
	
	function getThumbnail()
	{
		return $this->thumbnail;
	}
	
	
	function getViewHTML($videoId, $videoWidth='475' , $videoHeight='325')
	{
		$embedCode = "<embed src=\"http://www.dailymotion.com/swf/".$videoId."\" width=\"".$videoWidth."\" height=\"".$videoHeight."\" wmode=\"transparent\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\"> </embed>";
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
}
