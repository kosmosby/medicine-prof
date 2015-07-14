<?php
/**
 * @version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

class configViewconfig extends JViewLegacy
{

	function __construct( $config = array())
	{
		global $context;
		$context = 'configuration.list.';
		parent::__construct( $config );
	}
	
	function display($tpl = null)
	{	 
    	//global $mainframe, $context;
		$app = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Upload Templates') );
		?>

		<?php
		JToolBarHelper::title(JText::_('Upload Templates'), 'awdwallproconfig');
	 	JToolBarHelper::back('Home' , 'index.php?option=com_awdwall&controller=awdwall'); 		
		JToolBarHelper::save( 'save' );
		
		$db		=& JFactory::getDBO();
		//$query = "SELECT params FROM #__components WHERE link='option=com_awdwall' AND parent='0'";
		
	    parent::display($tpl);
  }
  
//  	protected function addToolbar()
//	{
////	    JToolBarHelper::title(   JText::_( 'JomWALL Configutation' ), 'awdwallproc' );
//	}

}
?>
