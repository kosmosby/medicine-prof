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
class TableVideoLivestream
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
				$this->title = $matches[1][0];
			}
		
			//Get description
			$pos_u = strpos($xmlContent, '<meta name="description" content="');
			$pos_u_start = $pos_u + 34;

			$code = substr($xmlContent, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode('"', $code);
			$code = $code_tmp["0"];
			$this->description= nl2br($code);
			
			//Get duration
		
			//Get thumbnail
			$pos_u = strpos($xmlContent, '<link rel="image_src" href="');
			$pos_u_start = $pos_u + 28;

			$code = substr($xmlContent, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode('"', $code);
			$code = $code_tmp["0"];
			$this->thumbnail=$code;

			
			return true;
		}			
	}	
	
	function getId()
	{	
		$pos_u = strpos($this->videoUrl, "livestream.com/");

		////TODO: User regular expression instead
		if ($pos_u === false) {
			return null;
		} else if ($pos_u) {
			$pos_u_start = $pos_u + 8;

			$code = substr($this->videoUrl, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode("&", $code);
			$code = $code_tmp["0"];
		}
	   
        return $code; 
		
	}
	
	function getType()
	{
		return 'livestream';
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
		$file 	= $this->getFeedUrl();
		$xmlContent = getContentFromUrl($file);
		if(strstr($xmlContent,'<link rel="video_src"'))
		{
		  preg_match_all('/<link rel="video_src".*?href\s*=\s*["\'](.+?)["\']/im', $xmlContent, $images);
		  $videofile = $images[1][0];
		}
		
        $embed = '<embed name="lsplayer" wmode="transparent" src="'.$videofile.'" width="'.$videoWidth.'" height="'.$videoHeight.'" allowScriptAccess="always" allowFullScreen="true" type="application/x-shockwave-flash" />';
		echo  $embed;

	}
	
	function getFeedUrl(){
		return $this->videoUrl;
	}
	
}
