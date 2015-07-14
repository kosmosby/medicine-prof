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
class TableVideoMtv
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
			$pattern =  "'<meta name=\"mtv_vt\" content=\"(.*?)\"/>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->title = $matches[1][0];
			}
			
			if(empty($this->title))
			{
				$pattern =  "'<title>(.*?)</title>'s";
				preg_match_all($pattern, $xmlContent, $matches);
				if($matches)
				{
					$this->title = $matches[1][0];
				}
			}
			
			//Get description
			$pattern =  "'<meta name=\"description\"\n?content=\"(.*?)\"/>'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->description = $matches[1][0];
			}
			//Get duration
		
			//Get thumbnail
			$pattern =  "'<meta name=\"thumbnail\"( )?(\n)?(content=\"(.*?)\"/>)'s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->thumbnail = $matches[4][0];
			}
			//echo $this->thumbnail;
			//exit;
			return true;
		}			
	}	
	
	function getId()
	{	
		preg_match('/videos\/(.*)/', $this->videoUrl , $matches);
	 	if (!empty($matches[1])){
			$videoId	= $matches[1];		
		}				
	   
       return $videoId;
        
	}
	
	function getType()
	{
		return 'mtv';
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
			$videoId	= $this->videoUrl;
		}
		$file	= 'http://www.mtv.com/videos/'.$videoId;
		$xmlContent = getContentFromUrl($file);
 		$videoPath	= explode( '/' , $videoId);
		
		$pattern =  "/http:\/\/media.mtvnservices.com\/mgid:uma:(.*?)\"/i";
		preg_match_all($pattern, $xmlContent, $matches);

		if( $matches[1][0] )
		{
			$path   = $matches[1][0];  
		    $getId	= explode( ':' , $matches[1][0]);
		}
	
		if($getId[0] == 'video')
		{
			$flashVars	= 'flashVars="configParams=vid=' . $getId[2];
		}
		else
		{
			$id	= explode( '=' , $videoPath[2]);
			$flashVars	= $videoPath[0]=='movie-trailers' ? NULL : 'flashVars="configParams=id=' . $id[1] . '"';

		}
		$embed	= '<embed src="http://media.mtvnservices.com/mgid:uma:' . $path . '" width="' . $videoWidth . '" height="' . $videoHeight . '" ' . $flashVars . '" type="application/x-shockwave-flash" allowFullScreen="true" allowScriptAccess="always" base="." wmode="transparent"></embed>';
		
		return $embed;

	}
	
	function getFeedUrl(){
		return 'http://www.mtv.com/videos/' .$this->videoId;
	}
	
}
