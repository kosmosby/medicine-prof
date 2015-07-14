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

class JTheFactoryGatewaysToolbar
{
    function display($task)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_PAYMENT_GATEWAYS_LIST' ),$appname);
        switch($task)
        {
            default:
            break;
            case 'install':
                JToolBarHelper::custom( 'gateways.doupload', 'upload.png', 'upload_f2.png', JText::_('FACTORY_UPLOAD_NEW_PAYMENT_GATEWAY'), false );
            break;
            case 'listing':
                JToolBarHelper::publish( 'gateways.toggle',  JText::_('FACTORY_TOGGLE_ENABLE'));
                JToolBarHelper::publish( 'gateways.setdefault',  JText::_('FACTORY_SET_DEFAULT_GATEWAY'));
        		JToolBarHelper::custom( 'gateways.install', 'upload.png', 'upload_f2.png', JText::_('FACTORY_INSTALL_NEW_ITEM'), false );
            break;
         }

    }
}
