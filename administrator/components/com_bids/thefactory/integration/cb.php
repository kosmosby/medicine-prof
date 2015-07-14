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

class JTheFactoryIntegrationCB extends JTheFactoryIntegration {

    var $mode = "cb";
    var $table = "#__comprofiler";
    var $keyfield = "user_id";

    function detectIntegration() {
        $database =  JFactory::getDBO();
        $database->setQuery("SELECT count(*) FROM #__extensions WHERE `element`='com_comprofiler'");
        $database->loadResult() ;
 
        return $database->loadResult() > 0;
    }

    function getUserProfile($userid=0) {

        $cbinclude = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_comprofiler" . DS . "plugin.foundation.php";
        if(!file_exists($cbinclude)) {
            JError::raiseNotice(1, JText::_('FACTORY_CB_NOT_INSTALLED'));
            return;

        }

        require_once($cbinclude);

        cbimport('cb.database');
        cbimport('cb.tables');

        $cbUser = CBUser::getInstance($userid);
        $userdata = $cbUser->getUserData();
        $userdata->userid=$userid;

        return get_object_vars($userdata);
    }

    function getIntegrationArray() {

        static $fieldmap = null;

        if(!$fieldmap) {
            $MyApp = JTheFactoryApplication::getInstance();
            $tablename = $MyApp->getIniValue("field_map_table", "profile-integration");


            $database =  JFactory::getDBO();
            $database->setQuery("SELECT `field`,`assoc_field` FROM `" . $tablename . "`");
            $r = $database->loadAssocList();

            for ($i = 0; $i < count($r); $i++) {
                $fieldmap[$r[$i]['field']] = $r[$i]['assoc_field'];
            }
        }

        return $fieldmap;
    }

    function getProfileLink($userid) {
        //abstract
        $link = JURI::root() . "index.php?option=com_comprofiler";
        if ($userid)
            $link .= "&user={$userid}&task=userprofile";
        return $link;
    }

    function _buildWhere($filters) {
        $integrationArray = $this->getIntegrationArray();
        $where = array();
        if (count($filters))
            foreach ($filters as $key => $value)
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (isset($integrationArray[$k]) && $integrationArray[$k]) {
                            if (strpos($v, '%') !== FALSE)
                                $w[] = 'profile.' . $integrationArray[$k] . " LIKE '$v'";
                            else
                                $w[] = 'profile.' . $integrationArray[$k] . "='$v'";
                        }
                        if ($k == 'username') {
                            if (strpos($v, '%') !== FALSE)
                                $w[] = "u.username LIKE '$v'";
                            else
                                $w[] = "u.username='$v'";
                        }
                    }
                    if (count($w))
                        $where[] = '(' . implode(' OR ', $w) . ')';
                }
                else {
                    if (isset($integrationArray[$key]) && $integrationArray[$key]) {
                        if (strpos($value, '%') !== FALSE)
                            $where[] = 'profile.' . $integrationArray[$key] . " LIKE '$value'";
                        else
                            $where[] = 'profile.' . $integrationArray[$key] . "='$value'";
                    }
                    if ($key == 'username') {
                        if (strpos($v, '%') !== FALSE)
                            $w[] = "u.username LIKE '$v'";
                        else
                            $w[] = "u.username='$v'";
                    }
                }
        return $where;
    }

    function getUserList($limitstart=0, $limit=30, $filters=null, $ordering=null) {
        $db = JFactory::getDBO();
        $integrationArray = $this->getIntegrationArray();
        $integrationFields = $this->getIntegrationFields();

        $colums = array('u.id as userid', 'u.username', 'profile.*');

        foreach ($integrationFields as $field)
            if ($integrationArray[$field])
                $colums[] = 'profile.' . $integrationArray[$field] . " as `{$field}`";
            else
                $colums[] = "'' as `{$field}`"; //field not defined, thus provided empty


            $query = "select " . implode(',', $colums);

        $query.=" from #__users u ";
        $query.=" left join #__comprofiler profile on u.id=profile.user_id ";

        $where = $this->_buildWhere($filters);

        if (count($where))
            $query.=" WHERE " . implode(' AND ', $where);

        if (count($ordering))
            $query.=" ORDER BY " . implode(',', $ordering);

        $db->setQuery($query, $limitstart, $limit);
        $list = $db->loadObjectList();
        return $list;
    }

    function getUserListCount($filters=null) {
        $db = JFactory::getDBO();
        $query = "select count(*) ";

        $query.=" from #__users u ";
        $query.=" left join #__comprofiler profile on u.id=profile.user_id ";

        $where = $this->_buildWhere($filters);

        if (count($where))
            $query.=" WHERE " . implode(',', $where);

        $db->setQuery($query);
        $total = $db->loadResult();

        return $total;
    }

    function checkProfile($userid=null) {
        return true;
    }
}
