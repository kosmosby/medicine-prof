<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 error_reporting(0);
class com_awdjomalbumInstallerScript
{
        /**
         * method to install the component
         *
         * @return void
         */
		function install($parent) 
		{
			$db =  JFactory::getDBO();
			
			$query = "SELECT COUNT(*) FROM #__awd_jomalbum_info_ques";
			$db->setQuery($query);
			$result = $db->loadResult();
			if(!$result){
				$query = "INSERT INTO `#__awd_jomalbum_info_ques` (`id`, `colname`, `value`) VALUES
				(1, 'col1', 'question1'),
				(2, 'col2', 'question2'),
				(3, 'col3', 'question3'),
				(4, 'col4', 'question4'),
				(5, 'col5', 'question5')";
				$db->setQuery($query);
				$db->query();
			}
	$pbfield=0;
	$query = "SHOW COLUMNS FROM #__awd_jomalbum_info_ques";
	$db->setQuery($query);
	$inforesults = $db->loadObjectList();
	foreach ($inforesults as $inforesult)
	{
		if($inforesult->Field=='published')
		{
			$pbfield=1;
		}
	}
	if($pbfield==0)
	{
		$query = "ALTER TABLE #__awd_jomalbum_info_ques ADD published TINYINT NOT NULL DEFAULT '0' ";
		$db->setQuery($query);
		$db->query();
	}

	$query = "SHOW COLUMNS FROM #__awd_jomalbum_userinfo";
	$db->setQuery($query);
	$results = $db->loadObjectList();
	foreach ($results as $result)
	{
		if($result->Field=='birthday')
		{
			$countbirthday=1;
		}
		if($result->Field=='display_currentcity')
		{
			$countdisplay_currentcity=1;
		}
		if($result->Field=='display_hometown')
		{
			$countdisplay_hometown=1;
		}
		if($result->Field=='display_languages')
		{
			$countdisplay_languages=1;
		}
		if($result->Field=='display_birthday')
		{
			$countdisplay_birthday=1;
		}
		if($result->Field=='display_aboutme')
		{
			$countdisplay_aboutme=1;
		}
		if($result->Field=='display_skype_user')
		{
			$countdisplay_skype_user=1;
		}
		if($result->Field=='facebook_user')
		{
			$countfacebook_user=1;
		}
		if($result->Field=='skype_user')
		{
			$countskype_user=1;
		}
		if($result->Field=='display_facebook_user')
		{
			$countdisplay_facebook_user=1;
		}
		if($result->Field=='twitter_user')
		{
			$counttwitter_user=1;
		}
		if($result->Field=='display_twitter_user')
		{
			$countdisplay_twitter_user=1;
		}
		
		
		if($result->Field=='display_twitter_post')
		{
			$countdisplay_twitter_post=1;
		}
		if($result->Field=='twitter_privacy')
		{
			$counttwitter_privacy=1;
		}
		if($result->Field=='latest_tweet_id')
		{
			$countlatest_tweet_id=1;
		}
		if($result->Field=='youtube_user')
		{
			$countyoutube_user=1;
		}
		if($result->Field=='display_youtube_user')
		{
			$countdisplay_youtube_user=1;
		}
		if($result->Field=='display_col1')
		{
			$countdisplay_col1=1;
		}
		if($result->Field=='display_col2')
		{
			$countdisplay_col2=1;
		}
		if($result->Field=='display_col3')
		{
			$countdisplay_col3=1;
		}
		if($result->Field=='display_col4')
		{
			$countdisplay_col4=1;
		}
		if($result->Field=='display_col5')
		{
			$countdisplay_col5=1;
		}
		
		if($result->Field=='workingat')
		{
			$countworkingat=1;
		}
		
		if($result->Field=='display_workingat')
		{
			$countdisplay_workingat=1;
		}
		
		if($result->Field=='studied')
		{
			$countstudied=1;
		}
		
		if($result->Field=='display_studied')
		{
			$countdisplay_studied=1;
		}
		
		if($result->Field=='livein')
		{
			$countlivein=1;
		}
		
		if($result->Field=='display_livein')
		{
			$countdisplay_livein=1;
		}
		
		if($result->Field=='phone')
		{
			$countphone=1;
		}
		
		
		if($result->Field=='phone')
		{
			$countphone=1;
		}
		if($result->Field=='display_phone')
		{
			$countdisplay_phone=1;
		}
		if($result->Field=='cell')
		{
			$countcell=1;
		}
		if($result->Field=='display_cell')
		{
			$countdisplay_cell=1;
		}
		if($result->Field=='maritalstatus')
		{
			$countmaritalstatus=1;
		}
		if($result->Field=='display_maritalstatus')
		{
			$countdisplay_maritalstatus=1;
		}
		if($result->Field=='userhighlightfields')
		{
			$countuserhighlightfields=1;
		}
		if($result->Field=='hide_birthyear')
		{
			$counthide_birthyear=1;
		}
		if($result->Field=='cbfields')
		{
			$cbfields=1;
		}
		
	}

	if($countbirthday!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `birthday` date NOT NULL AFTER `languages`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_currentcity!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_currentcity` INT NULL DEFAULT NULL AFTER `currentcity`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_hometown!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_hometown` INT NULL DEFAULT NULL AFTER `hometown`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_languages!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_languages` INT NULL DEFAULT NULL AFTER `languages`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_birthday!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_birthday` INT NULL DEFAULT NULL AFTER `birthday`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_aboutme!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_aboutme` INT NULL DEFAULT NULL AFTER `aboutme`";
		$db->setQuery($query);
		$db->query();
	}

	if($countskype_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `skype_user` VARCHAR( 222 ) NOT NULL AFTER `display_aboutme`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_skype_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_skype_user` INT NULL DEFAULT NULL AFTER `skype_user`";
		$db->setQuery($query);
		$db->query();
	}

	if($countfacebook_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `facebook_user` VARCHAR( 222 ) NOT NULL AFTER `display_skype_user`";
		$db->setQuery($query);
		$db->query();
	}

	if($countdisplay_facebook_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_facebook_user` INT NULL DEFAULT NULL AFTER `facebook_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($counttwitter_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `twitter_user` VARCHAR( 222 ) NOT NULL AFTER `display_facebook_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_twitter_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_twitter_user` INT NULL DEFAULT NULL AFTER `twitter_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_twitter_post!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_twitter_post` INT NULL DEFAULT NULL AFTER `display_twitter_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($counttwitter_privacy!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `twitter_privacy` INT NULL DEFAULT NULL AFTER `display_twitter_post`";
		$db->setQuery($query);
		$db->query();
	}
	if($countlatest_tweet_id!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `latest_tweet_id` VARCHAR( 222 ) NOT NULL AFTER `twitter_privacy`";
		$db->setQuery($query);
		$db->query();
	}
	if($countyoutube_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `youtube_user` VARCHAR( 222 ) NOT NULL AFTER `display_twitter_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_youtube_user!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_youtube_user` INT NULL DEFAULT NULL AFTER `youtube_user`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_col1!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_col1` INT NULL DEFAULT NULL AFTER `col1`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_col2!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_col2` INT NULL DEFAULT NULL AFTER `col2`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_col3!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_col3` INT NULL DEFAULT NULL AFTER `col3`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_col4!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_col4` INT NULL DEFAULT NULL AFTER `col4`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_col5!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_col5` INT NULL DEFAULT NULL AFTER `col5`";
		$db->setQuery($query);
		$db->query();
	}
	/* ****************************************** new added columns*/
	if($countworkingat!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `workingat` varchar(100) NOT NULL AFTER `display_col5`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_workingat!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_workingat` tinyint(4) NOT NULL DEFAULT '1' AFTER `workingat`";
		$db->setQuery($query);
		$db->query();
	}
	if($countstudied!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `studied` varchar(100) NOT NULL AFTER `display_workingat`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_studied!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_studied` tinyint(4) NOT NULL DEFAULT '1' AFTER `studied`";
		$db->setQuery($query);
		$db->query();
	}
	if($countlivein!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `livein` varchar(100) NOT NULL AFTER `display_studied`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_livein!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_livein` tinyint(4) NOT NULL DEFAULT '1' AFTER `livein`";
		$db->setQuery($query);
		$db->query();
	}
	if($countphone!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `phone` varchar(100) NOT NULL AFTER `display_livein`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_phone!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_phone` tinyint(4) NOT NULL DEFAULT '1' AFTER `phone`";
		$db->setQuery($query);
		$db->query();
	}
	if($countcell!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `cell` varchar(100) NOT NULL AFTER `display_phone`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_cell!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_cell` tinyint(4) NOT NULL DEFAULT '1' AFTER `cell`";
		$db->setQuery($query);
		$db->query();
	}

	if($countmaritalstatus!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `maritalstatus` varchar(100) NOT NULL AFTER `display_cell`";
		$db->setQuery($query);
		$db->query();
	}
	if($countdisplay_maritalstatus!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `display_maritalstatus` tinyint(4) NOT NULL DEFAULT '1' AFTER `maritalstatus`";
		$db->setQuery($query);
		$db->query();
	}

	if($countuserhighlightfields!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `userhighlightfields` varchar(100) NOT NULL AFTER `display_maritalstatus`";
		$db->setQuery($query);
		$db->query();
	}
	if($counthide_birthyear!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `hide_birthyear` tinyint(4) NOT NULL DEFAULT '0' AFTER `userhighlightfields`";
		$db->setQuery($query);
		$db->query();
	}
	if($cbfields!=1){
		$query = "ALTER TABLE #__awd_jomalbum_userinfo ADD `cbfields` text NOT NULL AFTER `hide_birthyear`";
		$db->setQuery($query);
		$db->query();
	}


	$query = "ALTER TABLE #__awd_jomalbum MODIFY `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE #__awd_jomalbum_comment MODIFY `cdate` varchar(50) NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE #__awd_jomalbum_photos MODIFY `upload_date` varchar(50) NOT NULL";
	$db->setQuery($query);
	$db->query();
 	
	echo "<div style='margin:0 auto;'><div style='width:40%; float:left; text-align:right;'><div style='padding:0 20px 0px 0px;'><img src='components/com_awdjomalbum/images/installmessage.jpg' /></div></div><div style='width:60%;float:right;'><div style='padding:0 20px 0px 0px;color:#656565;font-family:Arial; '><h1 style='font-size:36px; line-height:36px; font-family:Arial; font-weight:bold;'><font style='color:#454545;'>JomWALL</font> <font style='color:#2cbbe2;'>Gallery</font></h1><p style=' padding:0px; margin:0; font-size:20px;'>The component was <font style=' font-weight:bold;'>installed</font></p><p style=' padding:0px;  margin:0;font-size:20px; '>Visit us at <a href='http://jomwall.com/' target='_blank' style=' text-decoration:none;font-weight:bold;'>www.jomwall.com</a> for news,updates and more products <br /><br /><a href='index.php?option=com_awdjomalbum' style='color :#2cbbe2; font-weight:bold;font-size:18px; text-decoration:none;'>Control Panel</a></p><p style='text-align:right; font-size:12px;'>JomWALL - Real time Content Sharing &amp; Collaboration System<br />Copyright &copy; 2009 - ".date('Y')." <a href='http://jomwall.com/' target='_blank'>JomWALL</a>.com All Rights Reserved.</p></div></div></div>";

		
			
			
						
		}
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {
		

        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        function update($parent) 
        {
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
        }
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
        function postflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
        }
}
