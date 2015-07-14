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

jimport('joomla.application.component.model'); 

class JTheFactoryModelMailman extends JModel
{
    var $table_name=null;
    
    function __construct()
    {
        $this->table_name='#__'.APP_PREFIX.'_mails';        
        parent::__construct();
    }
    function getMailList()
    {   
        $db= $this->getDBO();
        $db->setQuery("select * from `{$this->table_name}` ");
        return $db->loadObjectList();
        
    }
    function getShortcuts()
    {
        $myApp= JTheFactoryApplication::getInstance();
        $short=explode(',',$myApp->getIniValue('shortcuts','mail-settings'));
        $short_d=explode(',',$myApp->getIniValue('shortcuts_description','mail-settings'));
        $shortcuts=array();
        for($i=0;$i<count($short);$i++){
            $shortcuts[$short[$i]]=$short_d[$i];
        }
        return $shortcuts;
    }
    
}
