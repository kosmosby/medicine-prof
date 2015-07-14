<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class uploadthemesViewUploadthemes extends JView {

	function __construct( $config = array())
	{
		parent::__construct( $config );
	}
	
    function display($tpl = null) {
    	$mainframe=JFactory::getApplication('site');
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('upload_theme') );
	 
	    JToolBarHelper::title(   JText::_( 'Upload Themes' ), 'awdwallproc' );
		JToolBarHelper::save('upload_theme');
        parent::display($tpl);
    }
}
?>