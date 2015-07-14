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
 * @subpackage: mailman
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryMailmanToolbar
{
    function display($task=null)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_MAILS_MANAGEMENT' ),$appname);
        switch($task)
        {
            default:
        		JToolBarHelper::save( 'mailman.save',JText::_("FACTORY_SAVE"));
        		JToolBarHelper::back();
            break;
         }      
        
    }  
}
?>
