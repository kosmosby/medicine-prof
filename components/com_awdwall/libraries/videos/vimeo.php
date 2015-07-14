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
class TableVideoVimeo
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
		
		$file 	= $this->videoUrl ;
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
		$xmlContent = getContentFromUrl($file);				

		if(empty($this->videoId))
		{
			$url		= $this->getFailedUrl();
			$message	= JText::_('Invalid Video Id');
			$mainframe->redirect( $url , $message );
		}
		elseif($xmlContent == FALSE)
		{				
			$url		= $this->getFailedUrl();
			$message	= JText::_('Error fetching video');
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
		
		
			
//		$xml = simplexml_load_file($file);
//		$this->title	= 	$xml->video->caption;
//		$this->duration = 	$xml->video->duration;
//		$this->thumbnail=	$xml->video->thumbnail;
//		echo "thumb=".$this->thumbnail;
//		exit;
//			$parser = JFactory::getXMLParser('Simple');
//
//			$parser->loadString($xmlContent);
//			
//			$videoElement = $parser->document;
//			
//			//get Video Title
//			$element =$videoElement->getElementByPath('video/caption');
//			$this->title = $element->data();
//						
//			//Get Video duration
//			$element =$videoElement->getElementByPath('video/duration');
//			$this->duration = $element->data();		
//			
//			//Get Video duration
//			$element =$videoElement->getElementByPath('video/thumbnail');
//			$this->thumbnail = $element->data();
			
			return true;
		}				
	}

	function getId()
	{		
	    $pattern = '/vimeo.com\/(hd#)?(channels\/[a-zA-Z0-9]*#)?(\d*)/';
	    preg_match($pattern, $this->videoUrl, $match);

            if(!empty($match[3]))
            {
                return $match[3];
            }
            else
            {
               return !empty( $match[2] ) ? $match[2] : null;
            }
		
	}
	
	function getType()
	{
		return 'vimeo';
	}
	
	function getDescription()
	{
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
 		$str = getContentFromUrl($this->videoUrl);
        
 		if ( strpos($str, "<div id=\"description\">") != 0)
 		{
	 		$pos 	= strpos($str, "<div id=\"description\">")+22;
			$post 	= strpos($str, "</div>", $pos)-$pos;
	        $itemcomment = substr($str, $pos, $post);
	        $videoDescription = trim($itemcomment);

			$videoDescription = strip_tags($videoDescription);
 		}
 		else
 		{
 			$videoDescription = JText::_('Not Available');
 		}

        return $videoDescription;
	}
	
	function getViewHTML($videoId, $videoWidth='427' , $videoHeight='347')
	{
		$embedCode = "<object width=\"".$videoWidth."\" height=\"".$videoHeight."\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"allowscriptaccess\" value=\"always\" /><param name=\"movie\" value=\"http://vimeo.com/moogaloop.swf?clip_id=".$videoId."&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=ff0179&amp;fullscreen=1\" /><embed src=\"http://vimeo.com/moogaloop.swf?clip_id=" .$videoId. "&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=ff0179&amp;fullscreen=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowscriptaccess=\"always\" width=\"".$videoWidth."\" height=\"".$videoHeight."\" wmode=\"transparent\" ></embed></object>";
					
		return $embedCode;
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
