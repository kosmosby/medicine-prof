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
class TableVideoYoutube
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
		
		$file 	= 'http://gdata.youtube.com/feeds/api/videos/' .$this->videoId ;
		
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
			$pattern =  "/<title type='text'>(.*?)<\/title>/i";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
			    $this->title = $matches[1][0];
			}
			
			//Get description
			$pattern =  "/<content type='text'>(.*?)<\/content>/s";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
			    $this->description = $matches[1][0];
			}
			
			//Get duration
			$pattern =  "/seconds='(.+?)'/i";
			preg_match_all($pattern, $xmlContent, $matches);
			if($matches)
			{
				$this->duration = $matches[1][0];
			}
			
			return true;
		}
	}
	
	function getId()
	{
		//check for embed code format
		$pos_e = strpos($this->videoUrl, "youtube.com/v/");
		$pos_u = strpos($this->videoUrl, "watch?v=");
		
		if ($pos_e === false && $pos_u === false) {
			return null;
		} else if ($pos_e) {
			$pos_e_start = $pos_e + 14;

			$code = substr($this->videoUrl, $pos_e_start, 11);
			$code = strip_tags($code );
			$code = preg_replace("/[^a-zA-Z0-9s_-]/", "", $code);
		} else if ($pos_u) {
			$pos_u_start = $pos_u + 8;

			$code = substr($this->videoUrl, $pos_u_start, 11);
			$code = strip_tags($code );
			$code = preg_replace("/[^a-zA-Z0-9s_-]/", "", $code);
		}
		
		return $code;
	}

	function getType()
	{
		return 'youtube';
	}
	
	function getThumbnail()
	{
		$videoThumbnailUrl 	= "http://img.youtube.com/vi/".$this->videoId."/default.jpg";
		return $videoThumbnailUrl;
	}
	
	function getViewHTML($videoId, $videoWidth='425' , $videoHeight='344')
	{
		$embedCode = "<object width=\"".$videoWidth."\" height=\"".$videoHeight."\"><param name=\"movie\" value=\"http://www.youtube.com/v/" .$videoId. "&hl=en&fs=1&hd=1&showinfo=0&rel=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/" .$videoId. "&hl=en&fs=1&hd=1&showinfo=0&rel=0\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".$videoWidth."\" height=\"".$videoHeight. "\" wmode=\"transparent\"></embed></object>";
		
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
