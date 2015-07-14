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
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryCurrenciesToolbar
{
    function display($task)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_CURRENCY_MANAGER' ),$appname);
        switch ($task)
        {
            default:
            case "listing":
                JToolBarHelper::apply( 'currencies.refreshconversions',JText::_("FACTORY_REFRESH_CONVERSION_RATES"));
                JToolBarHelper::addNew( 'currencies.newitem');
                JToolBarHelper::editList( 'currencies.edit');
                JToolBarHelper::deleteList(JText::_( 'FACTORY_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_CURRENCIES' ) ,'currencies.delete');
            break;
            case "edit":
            case "newitem":
                JToolBarHelper::save2new( 'currencies.saveadd');
                JToolBarHelper::save( 'currencies.save');
                JToolBarHelper::cancel( 'currencies.listing');
            break;
            case "refreshconversions":
                JToolBarHelper::custom( 'currencies.listing',"back","back",JText::_("FACTORY_BACK"),false);
            break;
        }
    }
}

