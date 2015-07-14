<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

class wall_detailViewwall_detail extends JView
{
	function display($tpl = null)
	{
		$items		=& $this->get('Data');
		$isNew		= ($items->id < 1);		
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		
		JToolBarHelper::title(   JText::_( 'wall Contest' ).': <small><small>[ ' . $text.' ]</small></small>' );		
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {			
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

		JToolBarHelper::help( 'screen.awdwall.edit' );
		$this->assignRef('items', $items);
		parent::display($tpl);
	}
}
