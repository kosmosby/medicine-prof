<?php
/**
 * @version 3.0
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filter.input');

class TableAwd_jomalbum extends JTable {

	var $id = null;
	var $userid=null;
	var $name = null;
	var $descr = null;
	var $privacy = null;
	var $location=null;
	var $published = null;
	var $created_date = null;
	function __construct(& $db) {
		parent::__construct('#__awd_jomalbum', 'id', $db);
	}

	function check() {
		return true;
	}

}



class TableAwd_add_photos extends JTable {
	var $id = null;
	var $albumid = null;
	var $image_name = null;
	var $title = null;
	var $published=null;
	var $upload_date = null;
	function __construct(& $db) {
		parent::__construct('#__awd_jomalbum_photos', 'id', $db);
	}

	function check() {
		return true;
	}
}
class TableAwd_jomalbum_userinfo extends JTable {
	var $id = null;
	var $userid = null;
	var $currentcity = null;
	var $hometown = null;
	var $languages=null;
	var $aboutme = null;
	var $gender = null;
	var $birthday = null;
	var $skype_user = null;
	var $facebook_user = null;
	var $twitter_user = null;
	var $youtube_user = null;
	var $col1 = null;
	var $col2 = null;
	var $col3 = null;
	var $col4 = null;
	var $col5 = null;
	var $display_currentcity = 0;
	var $display_hometown = 0;
	var $display_languages=0;
	var $display_aboutme = 0;
	//var $display_gender = 0;
	var $display_birthday = 0;
	var $display_skype_user = 0;
	var $display_facebook_user = 0;
	var $display_twitter_user = 0;
	var $display_twitter_post = 0;
	var $latest_tweet_id=null;
	var $display_youtube_user = 0;
	var $display_col1 = 0;
	var $display_col2 = 0;
	var $display_col3 = 0;
	var $display_col4 = 0;
	var $display_col5 = 0;
	var $twitter_privacy=0;
	var $display_workingat=0;
	var $display_studied=0;
	var $display_livein=0;
	var $display_phone=0;
	var $display_cell=0;
	var $display_maritalstatus=0;
	
	
	
	var $workingat = null;
	var $studied = null;
	var $livein = null;
	var $phone = null;
	var $cell = null;
	var $maritalstatus = null;
	
	
	var $userhighlightfields= null;
	var $hide_birthyear=0;
	var $cbfields=null;
	function __construct(& $db) {
		parent::__construct('#__awd_jomalbum_userinfo', 'id', $db);
	}

	function check() {
		return true;
	}
}
class TableAwd_jomalbum_info_ques extends JTable {
	var $id = null;
	var $colname = null;
	var $value = null;
	  
	function __construct(& $db) {
		parent::__construct('#__awd_jomalbum_info_ques', 'id', $db);
	}

	function check() {
		return true;
	}
}
?>