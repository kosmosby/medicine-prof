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
class TableVideoMetacafe
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
			$options['rssUrl'] = $file;
			$rssDoc=& JFactory::getXMLParser('RSS',$options);

				foreach ($rssDoc->get_items() as $item):
					$this->title = $item->get_title();
					$this->description = $item->get_description();
				endforeach;

			return true;
		}			
	}	
	
	function getId()
	{	
		$pos_u = strpos($this->videoUrl, "watch/");

		if ($pos_u === false) {
			return null;
		} else if ($pos_u) {
			$pos_u_start = $pos_u + 6;

			$code = substr($this->videoUrl, $pos_u_start);
			$code = strip_tags($code );
			$code_tmp = explode("/", $code);
			if(!empty($code_tmp["1"])){
				$code = $code_tmp["0"].'/'.$code_tmp["1"];
			}else{
				$code = $code_tmp["0"].'/';
			}			
		}
						
		return $code;
	}
	
	function getType()
	{
		return 'metacafe';
	}
	
	function getDescription()
	{		
		$pattern =  "'<p>(.*?)<br>'s";			 
		preg_match_all($pattern, $this->description, $matches);
			if($matches){
				$videoDescription = trim($matches[1][0]);
			}
		
		if(empty($videoDescription))
			$videoDescription = JText::_('NOT AVAILABLE');
		
		return $videoDescription;
	}
	
	function getDuration()
	{	
		$pattern =  "'</a> \((.*?)\)<br/>'s";			 
		preg_match_all($pattern, $this->description, $matches);
			if($matches){
				$duration = trim($matches[1][0]);
			}
		$duration = explode(":",$duration);
		$duration = ($duration[0]*60) + ($duration[1]);

		if (empty($duration))			
			$duration = 0;
					
		return $duration;
	}

	function getThumbnail()
	{			
		$pattern =  "'<img src=\"(.*?)\"'s";			 
		preg_match_all($pattern, $this->description, $matches);
		if($matches){
			$videoThumbnailUrl = trim($matches[1][0]);
		}		
		return $videoThumbnailUrl;		
	}
	
	function getViewHTML($videoId, $videoWidth='425' , $videoHeight='344')
	{		
		$embedCode = '<object width="'.$videoWidth.'" height="'.$videoHeight.'"><param name="movie" value="http://www.metacafe.com/fplayer/'.$videoId.'.swf"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.metacafe.com/fplayer/'.$videoId.'.swf" width="'.$videoWidth.'" height="'.$videoHeight.'" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowFullScreen="true" wmode="transparent"> </embed>';
		
		return $embedCode;
	}
	
	function getFeedUrl(){
		$videoId = explode("/", $this->videoId);	 
		$feedUrl = 'http://www.metacafe.com/api/item/' . $videoId[0].'/';

		return $feedUrl;
	}
	
	function getTitle()
	{		
		$this->title	= $this->title ? $this->title : JText::_('Untitled Video');
		
		return $this->title;
	}	
}
