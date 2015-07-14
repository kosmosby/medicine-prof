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
class TableVideoMips
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
			$this->thumbnail=JURI::base().'components/com_awdwall/images/videothumb.jpg';

			
			return true;
		}			
	}	
	
	function getId()
	{	
		
		$pos_u = strpos($this->videoUrl, "mips.tv/channel.php?u=");
		if ($pos_u === false) {
			return null;
		} else if ($pos_u) {
			$pos_u_start = $pos_u + 22;

			$code = substr($this->videoUrl, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode("&", $code);
			$code = $code_tmp["0"];
		}
        return $code; 
		
	}
	
	function getType()
	{
		return 'mips';
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
		if(strstr($this->videoUrl,'u='))
		{
		  $videofile = explode('u=',$this->videoUrl);
		  if (strstr($videofile[1],"&")) {
			$videofile = explode("&",$videofile[1]);
			$videofile = "c=".$videofile[0]."&e=0";
		  } else {
			$videofile = $videofile[1];
		  }
		}
		
		$videofile = str_replace('&e=0','&e=1',$videofile);
			
        $embed = '<iframe allowtransparency="true" marginwidth="0" marginheight="0" src="http://www.mips.tv/player/embedplayer.php?id='.$videofile.'&amp;e=1&amp;width=475&amp;height=325" frameborder="0" height="325" width="475" scrolling="no"><embed></embed></iframe>';
		return $embed ;

	}
	
	function getFeedUrl(){
		return $this->videoUrl;
	}
	
}
