<?php
/**
 *
 * $Id: helper.php 1.0.0 2013-01-15 13:13:58 AWDsolution.com $
 * @package	    JomWALL Mini Profile 
 * @subpackage	jomwallminiprofile
 * @version     1.0.0
 * @description This module display a small snap of jomwall profile.
 * @copyright	  Copyright © 2013 - All rights reserved.
 * @license		  GNU General Public License v2.0
 * @author		  AWDsolution.com
 * @author mail	support@awdsolution.com
 * @website		  AWDsolution.com
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
 
class ModJomwallminiprofileHelper
{
    public function countwallpost($userId)
    {
		$db		= &JFactory::getDBO();
		$query 	= 'SELECT COUNT(*) FROM #__awd_wall where wall_date IS NOT NULL and group_id IS NULL and commenter_id ='.$userId;
		$db->setQuery($query);
		return $db->loadResult();
		
    } 
    public function countalbumphotos($userId)
    {
		$db		= &JFactory::getDBO();
		$query 	= 'SELECT COUNT(*) FROM #__awd_jomalbum_photos where published=1 and userid ='.$userId;
		$db->setQuery($query);
		return $db->loadResult();
		
    } 
    public function countwallphotos($userId)
    {
		$db		= &JFactory::getDBO();
		$sql="Select awi.*,aw.* from #__awd_wall_images as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.commenter_id=".$userId."  and aw.wall_date IS NOT NULL order by aw.id desc ";
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		return count($rows);
    } 
	
	
 
} 

?>