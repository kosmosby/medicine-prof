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
class TableVideoSoundcloud
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
			libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
			$doc = new DomDocument();
			$doc->loadHTML($xmlContent);
			$xpath = new DOMXPath($doc);
			$query = '//*/meta[starts-with(@property, \'og:\')]';
			$metas = $xpath->query($query);
			foreach ($metas as $meta) {
				$property = $meta->getAttribute('property');
				$content = $meta->getAttribute('content');
				$rmetas[$property] = $content;
			}
			//Get title
			if($rmetas['og:title'])
			$this->title = $rmetas['og:title'];
			//Get description
			if($rmetas['og:description'])
			$this->description = $rmetas['og:description'];
			
			//Get duration
		
			// get thumbnail
			if($rmetas['og:image'])
			$this->thumbnail = $rmetas['og:image'];

			if(empty($this->thumbnail))
			$this->thumbnail=JURI::base().'components/com_awdwall/images/videothumb.jpg';

			
			return true;
		}			
	}	
	
	function getId()
	{	
		$pos_u = strpos($this->videoUrl, "soundcloud.com/");
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
		return 'soundcloud';
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
	
	function getViewHTML($videoId, $videoWidth='400' , $player_height='81')
	{		
		$auto_play='false';
		$show_comments='false';
		$color='#ff7700';
		$theme_color='#CCCCCC';
		$url=urlencode($this->videoUrl);
		
			$embed = '<object height="'.$player_height.'" width="'.$videoWidth.'">
			<param name="movie" value="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'">
			</param>
			<param name="allowscriptaccess" value="always">
			</param>
			<embed allowscriptaccess="always" height="'.$player_height.'" src="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'" type="application/x-shockwave-flash" width="'.$videoWidth.'">
			</embed>
			</object>';
		
		return $embed ;

	}
	
	function getFeedUrl(){
		
		return $this->videoUrl;
	}
	
}
