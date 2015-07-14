<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 error_reporting(0);
class com_awdwallInstallerScript
{
		function install($parent) 
		{
			$db =  JFactory::getDBO();
			
			
	$countlink=0;
	$countgid=0;
	$query = "SHOW COLUMNS FROM #__awd_wall";
	$db->setQuery($query);
	$results = $db->loadObjectList();
	foreach ($results as $result)
	{
		if($result->Field=='group_id')
		{
			$countgid=1;
			break; 
		}
	}
	
	if($countgid==0)
	{
			$query = "ALTER TABLE #__awd_wall ADD group_id INT NULL DEFAULT NULL AFTER user_id";
			$db->setQuery($query);
			$db->query();
	}
//************** link image attachment field in #__awd_wall_links *************************/
	$query = "SHOW COLUMNS FROM #__awd_wall_links";
	$db->setQuery($query);
	$results = $db->loadObjectList();
	foreach ($results as $result)
	{
		if($result->Field=='link_img')
		{
			$countlink=1;
			break; 
		}
	}
	if($countlink==0)
	{
			$query = "ALTER TABLE #__awd_wall_links ADD link_img TEXT NULL DEFAULT AFTER user_id ";
			$db->setQuery($query);
			$db->query();
	}
	$query = "CREATE TABLE IF NOT EXISTS `#__jconnector_ids` (`user_id` INT NOT NULL ,`facebook_id` VARCHAR(50) NOT NULL);";
	$db->setQuery($query);
	$db->query();
//************** code end here *************************/
		$link='index.php?option=com_awdwall&controller=colors';
	   $db->setQuery('SELECT params FROM #__extensions WHERE element = "com_awdwall" and type="component"');
	   $params = json_decode( $db->loadResult(), true );
		if(empty($params))
		{
		   $params['temp'] = 'default';
		   $params['width'] = '725';
		   $params['email_auto'] = '0';
		   $params['video_lightbox'] = '0';
		   $params['image_lightbox'] = '1';
		   $params['display_name'] = '1';
		   $params['nof_post'] = '15';
		   $params['nof_comment'] = '3';
		   $params['bg_color'] = '#FFFFFF';
		   $params['image_ext'] = 'gif,png,jpg,jpge';
		   $params['file_ext'] = 'doc,docx,pdf,xls,txt';
		   $params['privacy'] = '0';
		   $params['nof_friends'] = '4';
		   $params['timestamp_format'] = '1';
		   $params['access_level'] = '1';
		   $params['display_online'] = '0';
		   $params['seo_format'] = '0';
		   $params['display_video'] = '1';
		   $params['display_image'] = '1';
		   $params['display_music'] = '1';
		   $params['display_link'] = '1';
		   $params['display_file'] = '1';
		   $params['display_trail'] = '0';
		   $params['display_pm'] = '1';
		   $params['dt_format'] = 'g:i A l, j-M-y';
		   $params['nof_groups'] = '4';
		   $params['nof_invite_members'] = '10';
		   $params['display_hightlightbox'] = '0';
		   // store the combined result
		   $paramsString = json_encode( $params );
		   $db->setQuery('UPDATE #__extensions SET params = ' .$db->quote( $paramsString ) .' WHERE element = "com_awdwall" and type="component" ' );
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		
		
		}
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$colorparams = json_decode( $db->loadResult(), true );
		if(empty($colorparams))
		{
		   $awdparams['color1'] = 'FFFFFF';
		   $awdparams['color2'] = '111111';
		   $awdparams['color3'] = '333333';
		   $awdparams['color4'] = '8C8C8C';
		   $awdparams['color5'] = 'EAE7E0';
		   $awdparams['color6'] = '111111';
		   $awdparams['color7'] = 'FFFFFF';
		   $awdparams['color8'] = '111111';
		   $awdparams['color9'] = 'EAE7E0';
		   $awdparams['color10'] = 'FFFFFF';
		   $awdparams['color11'] = '475875';
		   $awdparams['color12'] = 'FFFFFF';
		   $awdparams['color13'] = 'B0C3C5';
		   $awdparams['color14'] = 'E1DFD9';
		   // store the combined result
		   $awdparamsString = json_encode( $awdparams );
		   $db->setQuery('UPDATE #__menu SET params = ' .$db->quote( $awdparamsString ) .' WHERE link = "'.$link.'"' );
		 //  echo 'UPDATE #__menu SET params = ' .$db->quote( $paramsString ) .' WHERE link = "'.$link.'"';
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		 }
		
//************** code end here *************************/	
echo "<div style='margin:0 auto;'><div style='width:40%; float:left; text-align:right;'><div style='padding:0 20px 0px 0px;'><img src='components/com_awdwall/images/installmessage.jpg' /></div></div><div style='width:60%;float:right;'><div style='padding:0 20px 0px 0px;color:#656565;font-family:Arial; '><h1 style='font-size:36px; line-height:36px; font-family:Arial; font-weight:bold;'><font style='color:#454545;'>JomWALL</font> <font style='color:#2cbbe2;'>Framework</font></h1><p style=' padding:0px; margin:0; font-size:20px;'>The component was <font style=' font-weight:bold;'>installed</font></p><p style=' padding:0px;  margin:0;font-size:20px; '>Visit us at <a href='http://jomwall.com/' target='_blank' style=' text-decoration:none;font-weight:bold;'>www.jomwall.com</a> for news,updates and more products <br /><br /><a href='index.php?option=com_awdwall&amp;controller=awdwall' style='color :#2cbbe2; font-weight:bold;font-size:18px; text-decoration:none;'>Control Panel</a></p><p style='text-align:right; font-size:12px;'>JomWALL - Real time Content Sharing &amp; Collaboration System<br />Copyright &copy; 2009 - ".date('Y')." <a href='http://jomwall.com/' target='_blank'>JomWALL</a>.com All Rights Reserved.</p></div></div></div>";

		
			
			
						
		}
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {
		
			$db =& JFactory::getDBO();
            $manifest = $parent->get("manifest");
            $parent = $parent->getParent();
            $source = $parent->getPath("source");
             
            $installer = new JInstaller();
            
            foreach($manifest->plugins->plugin as $plugin) {
                $attributes = $plugin->attributes();
				$query = "SELECT extension_id FROM #__extensions WHERE `element`='".$attributes['plugin']."'";
				$db->setQuery($query);
				$id = $db->loadResult();
              	$installer->uninstall('plugin',$id);
            }

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
