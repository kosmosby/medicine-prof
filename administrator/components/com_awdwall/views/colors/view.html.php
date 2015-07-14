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

class colorsViewcolors extends JViewLegacy
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
    	//global $mainframe, $context;
		$app = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		jimport( 'joomla.html.parameter' );
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Colors') );
	 
		//$this->colors = $this->get('colors');
	    JToolBarHelper::title(   JText::_( 'Colors' ), 'awdwallprocolor' );			
		$this->addToolbar();

		$db		=& JFactory::getDBO();

		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$colorparams = json_decode( $db->loadResult(), true );
		if(empty($colorparams))
		{
		   $awdparams['color1'] = '868686';
		   $awdparams['color2'] = '32AED2';
		   $awdparams['color3'] = '333333';
		   $awdparams['color4'] = '8C8C8C';
		   $awdparams['color5'] = 'F6F6F6';
		   $awdparams['color6'] = '44C0E0';
		   $awdparams['color7'] = 'F6F6F6';
		   $awdparams['color8'] = '44C0E0';
		   $awdparams['color9'] = 'D6EEF8';
		   $awdparams['color10'] = 'AED7E6';
		   $awdparams['color11'] = '475875';
		   $awdparams['color12'] = 'E7F2F6';
		   $awdparams['color13'] = 'B0C3C5';
		   $awdparams['color14'] = 'E2EFF5';
		   // store the combined result
		   $awdparamsString = json_encode( $awdparams );
		   $db->setQuery('UPDATE #__menu SET params = ' .$db->quote( $awdparamsString ) .' WHERE link = "'.$link.'"' );
		 //  echo 'UPDATE #__menu SET params = ' .$db->quote( $paramsString ) .' WHERE link = "'.$link.'"';
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			
		}
		$query = "SELECT params FROM #__menu WHERE `link`='".$link."'";

		$db->setQuery($query);

		$awdParams = $db->loadResult();
//print_r($awdParams);
	
//		$file 	= JPATH_ADMINISTRATOR .'/components/com_awdwall/colors.xml';
//
//		$params = new JParameter($awdParams, $file, '');
		$awdParams = str_replace('{', '', $awdParams);

		$awdParams = str_replace('}', '', $awdParams);

		$awdParams = str_replace('"', '', $awdParams);

		$awdParams = explode(",", $awdParams);
		
		//jimport('joomla.html.pane');

		//$pane = &JPane::getInstance('sliders', array('allowAllClose' => true)); 
	    $this->assignRef('params', $awdParams);	

	   // $this->assignRef('pane', $pane);

			    

		

	    parent::display($tpl);

	}

	protected function addToolbar()

	{		

		JToolBarHelper::back('Home' , 'index.php?option=com_awdwall&controller=awdwall'); 		

		JToolBarHelper::makeDefault('Default', 'Default');

		JToolBarHelper::save('save', 'JTOOLBAR_SAVE');		

	}

}



?>
