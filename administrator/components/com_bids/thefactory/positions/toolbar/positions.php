<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: positions
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPositionsToolbar
{
    function display($task=null)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_THEME_POSITIONS_MANAGEMENT' ),$appname);
        JTheFactoryPositionsSubmenu::subMenuListPages();
        switch($task)
        {
            default:
            case 'listpages':
                JToolBarHelper::title( JText::_( 'FACTORY_PAGES_LIST_TITLE' ),$appname);
        		JToolBarHelper::custom( 'positions.listpositions', 'edit.png', 'edit_f2.png', JText::_('FACTORY_EDIT_PAGE_POSITONS'), true );
            break;
            case 'listpositions':
                JToolBarHelper::title( JText::_( 'FACTORY_POSITIONS_LIST_TITLE' ),$appname);
        		JToolBarHelper::custom( 'positions.listfields', 'edit.png', 'edit_f2.png', JText::_('FACTORY_EDIT_FIELDS_IN_POSITON'), true );
            break;
            case 'listfields':
        		//JToolBarHelper::addNew( 'positions.assignfields',JText::_('FACTORY_ASSIGN_FIELDS') );
                JToolBarHelper::custom( 'positions.listpages','archive.png','',JText::_('FACTORY_PAGES_LIST'), false );
                JToolBarHelper::custom( 'positions.listpositions','archive.png','',JText::_('FACTORY_POSITIONS_LIST'), false );
            break;
            case 'assignfields':
        		JToolBarHelper::save( "positions.saveassigns",JText::_('FACTORY_SAVE_ASSIGNS') );
                JToolBarHelper::cancel( "positions.cancelassigns",JText::_('FACTORY_CANCEL') );
            break;
         }

    }
}
?>
