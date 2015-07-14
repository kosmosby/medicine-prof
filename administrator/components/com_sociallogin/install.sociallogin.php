<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$dbo = &JFactory::getDBO();
//$menutype = JRequest::getVar("menutype");

    $query = "SELECT * ".
        " FROM #__modules".
        " WHERE id = 16 AND module = 'mod_login'";
    $dbo->setQuery($query);
    $modpar = $dbo->loadObject();

    $query = "SELECT * ".
        " FROM #__modules_menu".
        " WHERE moduleid = 16";
    $dbo->setQuery($query);
    $modmenupar = $dbo->loadObjectList();

    $query = "SELECT * ".
        " FROM #__modules".
        " WHERE module = 'mod_sociallogin'";
    $dbo->setQuery($query);
    $module_id = $dbo->loadResult();

    if($modpar->position && $modpar->ordering && $module_id) {
        $query = "UPDATE ".
            " #__modules ".
            " SET `position` = '".$modpar->position."', `published` = '1', `ordering` = '".$modpar->ordering."' WHERE `id` =".$module_id." ";
        $dbo->setQuery($query);
        $dbo->query();
    }

    for($i=0;$i<count($modmenupar);$i++) {
        $sql = "INSERT INTO #__modules_menu ( moduleid, menuid )" .
            " VALUES ( ".$module_id.", ".$modmenupar[$i]->menuid." )";
        $db->setQuery($sql);
        $db->query();
    }

    $query = "UPDATE ".
        " #__modules ".
        " SET `published` = '0' WHERE `id` =16 ";
    $dbo->setQuery($query);
    $dbo->query();


?>