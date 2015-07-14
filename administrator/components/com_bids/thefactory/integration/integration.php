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

class JTheFactoryIntegration{
    var $mode=null;
    var $table=null;
    var $keyfield=null;
    
     
    function detectIntegration()
    {
        //abstract
        return false;
    }
    
    function getUserProfile($userid)
    {
        //abstract
        return false;

    }
     
    function getIntegrationFields()
    {
        $MyApp = JTheFactoryApplication::getInstance();
    	$flist=$MyApp->getIniValue("fields_list","profile-integration");
        if ($flist)
    	   return explode(",",$flist);
        else
            return array();
        
    }

    function getIntegrationArray()
    {
        //abstract
        return array();
    }
    function getProfileLink($userid)
    {
        //abstract
        return null;
    }
    function getUserList($limitstart=0,$limit=30,$filters=null,$ordering=null)
    {
        return null;
    }
    function getUserListCount($filters=null)
    {
        return 0;
    }
    function checkProfile($userid=null)
    {
        return true;
    }    
    function setGMapCoordinates($userid,$x,$y)
    {
        $arr=$this->getIntegrationArray();
        $xfield=$arr['googleMaps_x'];
        $yfield=$arr['googleMaps_y'];
        if (!$xfield || !$yfield )
            return false;
        $db= JFactory::getDBO();
        $query="update `{$this->table}` profile 
            set profile.`{$xfield}`='$x',
                profile.`{$yfield}`='$y'
            where profile.`{$this->keyfield}`='{$userid}'";
        $db->setQuery($query);
        return $db->query();
    }
}

?>
