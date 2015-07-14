<?php
/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

class TableVideo extends JTable 
{
	//Table's field
	var $id 			= null;
	var $wall_id		= null;
	var $title 			= null;
  	var $type 			= null;
	var $video_id 	    = null;
  	var $description 	= null;
  	var $creator 		= null;  
	var $created 		= null;		
	var $published		= null;
	var $featured		= null;
	var $duration 		= null;	
	var $thumb			= null;
	var $path			= null;	
	
	function __construct(&$db)
	{
		parent::__construct( '#__awd_wall_videos', 'id', $db );
	}
}