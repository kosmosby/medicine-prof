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
 * @subpackage: category
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryCategoryToolbar
{
    function display($task=null)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_CATEGORY_MANAGEMENT' ),$appname);
        switch($task)
        {
            default:
            case 'categories':
        		JToolBarHelper::custom( 'category.newcat', 'new.png', 'new_f2.png', JText::_('FACTORY_NEW_CATEGORY'), false );
        		JToolBarHelper::custom( 'category.editcat', 'edit.png', 'edit_f2.png', JText::_('FACTORY_EDIT_CATEGORIES'), true );
        		JToolBarHelper::custom( 'category.showmovecategories', 'move.png', 'move_f2.png', JText::_('FACTORY_MOVE_CATEGORIES'), true );
        		JToolBarHelper::deleteList("","category.delcategories");
            break;
            case 'newcat':
            case 'editcat':
        		JToolBarHelper::save( "category.savecat" );
        		JToolBarHelper::back();
            break;
            case 'showmovecategories':
        		JToolBarHelper::custom( 'category.doMoveCategories', 'save.png', 'save_f2.png', JText::_('FACTORY_MOVE_TO_SELECTED_CATEGORY'), false );
            
            break;
         }      
        
    }  
}
?>
