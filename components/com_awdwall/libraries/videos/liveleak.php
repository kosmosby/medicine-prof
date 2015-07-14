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
class TableVideoLiveleak
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
			$res = preg_match("/<title>LiveLeak.com - (.*)<\/title>/", $xmlContent, $title_matches);
			$this->title = $title_matches[1];
		
			//Get description
			$res = preg_match('/<meta property="og:description" content="(.*)"/', $xmlContent, $title_matches);
			$this->description = $title_matches[1];
			
			//Get duration
		
			//Get thumbnail
			//$noPreview  = 'http://209.197.7.204/e3m9u5m8/cds/u/nopreview.jpg';
			// get thumbnail
			$res = preg_match('/<meta property="og:image" content="(.*)"/', $xmlContent, $title_matches);
			$this->thumbnail = $title_matches[1];
			if(empty($this->thumbnail))
			$this->thumbnail=JURI::base().'components/com_awdwall/images/videothumb.jpg';

			
			return true;
		}			
	}	
	
	function getId()
	{	
		
        $pattern    = '/http\:\/\/(\w{3}\.)?liveleak.com\/view\?i\=([a-zA-Z0-9][a-zA-Z0-9$_.+!*(),;\/\?:@&~=%-]*)/';
        preg_match( $pattern, $this->videoUrl, $match );
        return !empty($match[2]) ? $match[2] : null; 
		
	}
	
	function getType()
	{
		return 'liveleak';
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
		return "<embed src=\"http://www.liveleak.com/e/".$videoId."\" width=\"".$videoWidth."\" height=\"".$videoHeight."\" wmode=\"transparent\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\"> </embed>";

	}
	
	function getFeedUrl(){
		return 'http://www.liveleak.com/view?i=' . $this->videoId;
		//return $this->videoUrl;
	}
	
}
