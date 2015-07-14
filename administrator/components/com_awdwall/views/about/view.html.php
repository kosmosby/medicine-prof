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

class aboutViewabout extends JViewLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $config = array())
	{
		parent::__construct( $config );
	}
	
	function display($tpl = null)
	{
		JToolBarHelper::back('Home' , 'index.php?option=com_awdwall&controller=awdwall');
		JToolBarHelper::title(JText::_('JomWALL about' ), 'awdwallproabut');
		
		parent::display($tpl);
	}
}

?>
