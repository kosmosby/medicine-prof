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

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class AwdjomalbumViewInfo extends JViewLegacy {
    function display($tpl = null) {
	
		//global $mainframe, $option;
		global $app, $option;
		$db		=& JFactory::getDBO();
		
			$sql="select *  from   #__awd_jomalbum_info_ques order by id";
			$db->setQuery( $sql );
			$rows = $db->loadObjectList();
			
			$this->assignRef('rows',$rows);
        parent::display($tpl);
    }
}
?>