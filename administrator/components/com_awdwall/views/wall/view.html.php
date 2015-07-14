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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
 
class wallViewwall extends JViewLegacy
{

	function __construct( $config = array())
	{
		parent::__construct( $config );
	}
    
	function display($tpl = null)
	{	 
    	//global $mainframe, $context;
		global  $context;
		$app = &JFactory::getApplication();
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('JomWALL Messages') );

	    JToolBarHelper::title(   JText::_( 'JomWALL Messages' ), 'awdwallpromessage' );
	    JToolBarHelper::back('Home' , 'index.php?option=com_awdwall&controller=awdwall'); 	
		JToolBarHelper::deleteList('Are you sure to delete the item(s)');
	
		$uri	=& JFactory::getURI();	
//		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'ordering' );
//		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );	
		$filter_order     = $app->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'ordering' );
		$filter_order_Dir = $app->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		
	
	
		$lists['order'] = $filter_order;  
		$lists['order_Dir'] = $filter_order_Dir;

		$items			= & $this->get( 'Data');
		$total			= & $this->get( 'Total');
		
		$pagination = & $this->get( 'Pagination' );
		
	    $this->assignRef('user',		JFactory::getUser());	
	    $this->assignRef('lists',		$lists);    
	  	$this->assignRef('items',		$items); 		
	    $this->assignRef('pagination',	$pagination);
	    $this->assignRef('request_url',	$uri->toString());
		
	    parent::display($tpl);
  }
}
?>
