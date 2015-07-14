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
class TableVideoTwitch
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
		//$xmlContent = getContentFromUrl($file);
		
		$url=$this->videoUrl	;
		try {
		$ch = curl_init($url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$xmlContent = curl_exec($ch);
		curl_close($ch);
		if (strstr($xmlContent,'not found')) $xmlContent="";
		} catch (Exception $e) {
		$xmlContent = @file_get_contents($url);
		}
		//echo 'data='.$xmlContent;
		//exit;
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
			 preg_match_all('/<meta property="og:description".*?content\s*=\s*["\'](.+?)["\']/im', $xmlContent, $description);
			if($description)
			{
				$this->description=  $description[1][0];
			}
			
			//Get duration
		
			//Get thumbnail
			$this->thumbnail=JURI::base().'components/com_awdwall/images/videothumb.jpg';
			
			return true;
		}			
	}	
	
	function getId()
	{	
		
		$pos_u = strpos($this->videoUrl, "twitch.tv/");
		if ($pos_u === false) {
			return null;
		} else if ($pos_u) {
			$pos_u_start = $pos_u + 10;

			$code = substr($this->videoUrl, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode("&", $code);
			$code = $code_tmp["0"];
		}

        return $code; 
		
	}
	
	function getType()
	{
		return 'twitch';
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
		if(strstr($xmlContent,'<meta property="og:video"'))
		{
		  preg_match_all('/<meta property="og:video".*?content\s*=\s*["\'](.+?)["\']/im', $xmlContent, $images);
		  $videofile = str_replace("facebook_","",$images[1][0]);
		}
	
        $embed = '<embed id="live_embed_player_flash" name="live_embed_player_flash" wmode="transparent" src="'.$videofile.'" width="'.$videoWidth.'" height="'.$videoHeight.'" allowScriptAccess="always" allowFullScreen="true" type="application/x-shockwave-flash" />';
		return $embed;
	}
	
	function getFeedUrl(){
		return $this->videoUrl;
	}
	
}
