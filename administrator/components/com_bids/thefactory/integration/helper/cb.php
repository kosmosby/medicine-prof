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

class JTheFactoryIntegrationCBHelper
{
    function _InstallCBPlugin($plugintitle,$tabtitle,$pluginname,$folder,$class)
    {
       $database = JFactory::getDBO();
       jimport('joomla.filesystem.file');
       jimport('joomla.filesystem.folder');

       $query = "SELECT id FROM #__comprofiler_plugin where element='$pluginname'";
       $database->setQuery($query);
       $plugid = $database->loadResult();

       if(!$plugid){
    		$query = "INSERT INTO #__comprofiler_plugin set
    			`name`='$plugintitle',
    			`element`='$pluginname',
    			`type`='user',
    			`folder`='$folder',
    			`ordering`=99,
    			`published`=1,
    			`iscore`=0
    		";
    		$database->setQuery( $query );
    		$database->query();

    		$plugid=$database->insertid();
       }
       $query = "SELECT count(*) FROM #__comprofiler_tabs where pluginid='{$plugid}'";
       $database->setQuery($query);
       $tabs = $database->loadResult();
       if(!$tabs){
        	$query = "INSERT INTO #__comprofiler_tabs set
        		`title`='$tabtitle',
        		`ordering`=999,
        		`enabled`=1,
        		`pluginclass`='$class',
        		`pluginid`='{$plugid}',
        		`fields`=0,
        		`displaytype`='tab',
        		`position`='cb_tabmain'

        	";
        	$database->setQuery( $query );
        	$database->query();
       }
        $pluginfolder=JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'plugin'.DS.'user'.DS.$folder;
        $pluginfile_source=JPATH_ROOT.DS."components".DS.APP_EXTENSION.DS."installer".DS."cb_plug".DS."$pluginname";

        if (!JFolder::exists($pluginfolder)) JFolder::create($pluginfolder);

        JFile::copy($pluginfile_source.'.php',$pluginfolder.DS."$pluginname.php");
        JFile::copy($pluginfile_source.'.xml',$pluginfolder.DS."$pluginname.xml");

        return $plugintitle;
    }

    function InstallCBPlugin($plugintitle,$tabtitle,$pluginname,$folder,$class)
    {
        $database = JFactory::getDBO();

        $query = "SELECT id FROM #__comprofiler_plugin where element='$pluginname'";
        $database->setQuery($query);
        $plugid = $database->loadResult();

        if(!$plugid) {
            $query = "INSERT INTO #__comprofiler_plugin SET
                `name`='$plugintitle',
                `element`='$pluginname',
                `type`='user',
                `folder`='$folder',
                `ordering`=99,
                `published`=1,
                `iscore`=0
            ";
            $database->setQuery( $query );
            $database->query();

            $plugid=$database->insertid();
        }

        $query = "SELECT COUNT(1) FROM #__comprofiler_tabs where pluginid='{$plugid}'";
        $database->setQuery($query);
        $tabs = $database->loadResult();
        if(!$tabs) {
            $query = "INSERT INTO #__comprofiler_tabs set
                `title`='$tabtitle',
                `ordering`=999,
                `enabled`=1,
                `pluginclass`='$class',
                `pluginid`='{$plugid}',
                `fields`=0,
                `displaytype`='tab',
                `position`='cb_tabmain'
            ";
            $database->setQuery( $query );
            $database->query();
        }

        $sourceFolder = JPATH_ROOT.DS."components".DS.APP_EXTENSION.DS."installer".DS."cb_plug".DS.$folder;
        $destinationFolder = JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'plugin'.DS.'user'.DS.$folder;
        JFolder::create($destinationFolder);
        JFolder::copy($sourceFolder,$destinationFolder,'',true);

        return "Installed CB plugin ".$plugintitle;
    }


}


?>
