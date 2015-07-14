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
 * @subpackage: integration
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryIntegrationLoveToolbar
{
    function display($task=null)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_LOVE_FACTORY_INTEGRATION' ),$appname);
        switch($task)
        {
            default:
               JToolBarHelper::save('integrationlove.save',JText::_('FACTORY_SAVE'));
               JToolBarHelper::cancel('integration.display');
            break;
            case 'installPlugins':
               JToolBarHelper::customX('integrationlove.display','back','back',JText::_("FACTORY_BACK"),false);
            break;
         }      
        
    }  
}
?>
